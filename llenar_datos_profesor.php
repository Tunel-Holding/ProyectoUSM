<?php
// Configuración de seguridad
ini_set('display_errors', 0);
error_reporting(E_ALL);


// Headers de seguridad
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_PROFESOR);
include 'comprobar_sesion.php';
actualizar_actividad();

// Conexión a la base de datos
require "conexion.php";

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función para validar y sanitizar entrada
function validarEntrada($dato, $tipo = 'texto', $longitudMax = 255) {
    $dato = trim($dato);
    
    // Verificar longitud
    if (strlen($dato) > $longitudMax) {
        return false;
    }
    
    switch ($tipo) {
        case 'cedula':
            // Cedula: entre 2 y 8 dígitos
            return preg_match('/^\d{2,8}$/', $dato) ? $dato : false;
            
        case 'nombre':
            // Validar nombres (solo letras, espacios y algunos caracteres especiales)
            return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $dato) ? $dato : false;
            
        case 'telefono':
            // Teléfono: exactamente 11 dígitos
            return preg_match('/^\d{11}$/', $dato) ? $dato : false;
            
        case 'email':
            // Validar email
            return filter_var($dato, FILTER_VALIDATE_EMAIL) ? $dato : false;
            
        case 'sexo':
            // Validar sexo (solo valores permitidos)
            $valoresPermitidos = ['Masculino', 'Femenino'];
            return in_array($dato, $valoresPermitidos) ? $dato : false;
            
        case 'texto':
        default:
            // Sanitizar texto general
            return htmlspecialchars($dato, ENT_QUOTES, 'UTF-8');
    }
}

// Función para verificar si el usuario ya tiene datos
function usuarioTieneDatos($conn, $idusuario) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM datos_usuario WHERE usuario_id = ?");
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'] > 0;
}

function obtenerMail($conn, $idusuario) {
    $stmt = $conn->prepare("SELECT email FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return ($row && isset($row['email'])) ? $row['email'] : '';
}

$errores = [];
$datos = [];
$idusuario = $_SESSION['idusuario'] ?? null;

// Si el formulario ha sido enviado, procesa los datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errores[] = "Error de seguridad: Token CSRF inválido.";
    } else {
        actualizar_actividad();
        
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['idusuario'])) {
            $errores[] = "Error: Usuario no autenticado.";
        } else {
            $idusuario = (int)$_SESSION['idusuario'];
            
            // Verificar si ya tiene datos
            if (usuarioTieneDatos($conn, $idusuario)) {
                $errores[] = "Error: Ya existen datos para este usuario.";
            } else {
                // Validar y sanitizar cada campo
                $campos = [
                    'numero_cedula' => ['tipo' => 'cedula', 'requerido' => false],
                    'nombres' => ['tipo' => 'nombre', 'requerido' => true],
                    'apellidos' => ['tipo' => 'nombre', 'requerido' => true],
                    'sexo' => ['tipo' => 'sexo', 'requerido' => false],
                    'telefono' => ['tipo' => 'telefono', 'requerido' => false],
                    'direccion' => ['tipo' => 'texto', 'requerido' => false]
                ];
                
                foreach ($campos as $campo => $config) {
                    $valor = isset($_POST[$campo]) ? $_POST[$campo] : '';
                    
                    // Reemplazar "Ninguno" con cadena vacía
                    if ($valor === 'Ninguno') {
                        $valor = '';
                    }
                    
                    // Validar campo requerido
                    if ($config['requerido'] && empty($valor)) {
                        $errores[] = "El campo " . ucfirst(str_replace('_', ' ', $campo)) . " es requerido.";
                        continue;
                    }
                    
                    // Si no es requerido y está vacío, continuar
                    if (empty($valor)) {
                        $datos[$campo] = '';
                        continue;
                    }
                    
                    // Validar y sanitizar
                    $valorValidado = validarEntrada($valor, $config['tipo']);
                    if ($valorValidado === false) {
                        $errores[] = "El campo " . ucfirst(str_replace('_', ' ', $campo)) . " tiene un formato inválido.";
                    } else {
                        $datos[$campo] = $valorValidado;
                    }
                }
                
                // Si no hay errores, insertar en la base de datos
                if (empty($errores)) {
                    $stmt = $conn->prepare("INSERT INTO datos_usuario (usuario_id, cedula, nombres, apellidos, sexo, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $email = obtenerMail($conn, $idusuario);
                    if ($stmt) {
                        $stmt->bind_param("isssssss", 
                            $idusuario,
                            $datos['numero_cedula'],
                            $datos['nombres'],
                            $datos['apellidos'],
                            $datos['sexo'],
                            $datos['telefono'],
                            $email, // Usar el email de la sesión
                            $datos['direccion']
                        );
                        
                        if ($stmt->execute()) {
                            // Redirigir después de guardar exitosamente
                            header("Location: datos_profesor.php");
                            exit();
                        } else {
                            $errores[] = "Error al guardar los datos en la base de datos.";
                        }
                        $stmt->close();
                    } else {
                        $errores[] = "Error en la preparación de la consulta.";
                    }
                }
            }
        }
    }
}


actualizar_actividad();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Llenar Datos - UniHub</title>
    <script src="js/control_inactividad.js"></script>
    <style>
        /* Estilos generales del contenedor del formulario */
        .contenedor-principal {
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Alinea los elementos al inicio */
            padding: 20px;
            min-height: calc(100vh - 120px); /* Ajusta según la altura del encabezado/pie de página */
        }

        .wecontainer {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            width: 100%;
            border-top: 8px solid #26c8dd;
            border-bottom: 8px solid #26c8dd;
            box-sizing: border-box;
            margin-top: 30px;
            text-align: center; /* Centra el título */
        }

        .wecontainer h1 {
            text-align: center;
            color: #26c8dd
            margin-bottom: 30px;
            font-size: 2.2em;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
        }

        /* Estilos para el formulario GRID */
        .wecontainer .form-grid { /* Cambié a .form-grid para evitar conflicto con .form general */
            display: grid;
            grid-template-columns: 1fr 1fr; /* Dos columnas */
            gap: 20px 30px; /* Espacio vertical y horizontal entre elementos */
            align-items: center; /* Alinea verticalmente los elementos en cada fila */
            margin-bottom: 30px;
        }

        .form-group { /* Nuevo contenedor para label e input */
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Alinea la etiqueta a la izquierda */
        }

        .wecontainer label {
            color: #26c8dd;
            font-size: 0.95em;
            font-weight: 600;
            margin-bottom: 5px; /* Espacio entre etiqueta y campo */
            font-family: 'Poppins', sans-serif;
        }

        .wecontainer input[type="text"],
        .wecontainer input[type="tel"],
        .wecontainer select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            color: #333;
            background-color: #f8f9fa;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box; /* Asegura que padding y border se incluyan en el width */
        }

        .wecontainer input:focus,
        .wecontainer select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            outline: none;
        }

        .wecontainer .correo-display {
            padding: 10px 15px;
            background-color: #e9ecef; /* Un gris más claro para el correo no editable */
            border: 1px solid #ced4da;
            border-radius: 8px;
            color: #495057;
            font-size: 1em;
            text-align: left;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }


        /* Botón de enviar */
        .button {
            display: block;
            width: fit-content;
            margin: 0 auto;
            padding: 12px 30px;
            background-color: #ffd700;
            color: #004c97;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            text-align: center;
            font-size: 1.1em;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            font-family: 'Poppins', sans-serif;
        }

        .button:hover {
            background-color: #ffcc00;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #ffcdd2;
            text-align: left; /* Alinea el texto a la izquierda */
        }

        .error-message strong {
            display: block;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .error-message ul {
            margin: 0;
            padding-left: 25px;
            list-style-type: disc;
        }

        .error-message li {
            margin-bottom: 5px;
        }

        .required {
            color: #d32f2f;
            margin-left: 5px;
        }

        /* Ajustes Responsivos */
        @media (max-width: 768px) {
            .wecontainer .form-grid {
                grid-template-columns: 1fr; /* Una sola columna en pantallas más pequeñas */
            }

            .wecontainer {
                padding: 25px;
                margin-top: 20px;
            }

            .wecontainer h1 {
                font-size: 1.8em;
            }

            .button {
                padding: 10px 20px;
                font-size: 1em;
            }
        }

        @media (max-width: 480px) {
            .wecontainer {
                padding: 15px;
            }

            .wecontainer h1 {
                font-size: 1.5em;
            }
        }

        /* Modo oscuro */
        body.dark-mode .wecontainer {
            background-color: #292942;
            border-top: 8px solid #ffd700;
            border-bottom: 8px solid #ffd700;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
        }

        body.dark-mode .wecontainer h1 {
            color: #ffd700;
        }

        body.dark-mode .wecontainer label {
            color: #f0f0f0;
        }

        body.dark-mode .wecontainer input[type="text"],
        body.dark-mode .wecontainer input[type="tel"],
        body.dark-mode .wecontainer select {
            background-color: #3a3a55;
            border: 1px solid #555574;
            color: #d4d4d4;
        }

        body.dark-mode .wecontainer input:focus,
        body.dark-mode .wecontainer select:focus {
            border-color: #ffd700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.25);
        }

        body.dark-mode .wecontainer .correo-display {
            background-color: #3a3a55;
            border: 1px solid #555574;
            color: #d4d4d4;
        }
        
        body.dark-mode .button {
            background-color: #ffd700;
            color: #292942;
        }

        body.dark-mode .button:hover {
            background-color: #ffcc00;
        }

        body.dark-mode .error-message {
            background-color: #5a3e3f;
            color: #ffcdd2;
            border-color: #ff8a80;
        }

        body.dark-mode .error-message strong {
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="cabecera">
        <button type="button" id="logoButton">
            <img src="css/menu.png" alt="Menú" class="logo-menu">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>

    <?php include 'menu_profesor.php'; ?>

    <div class="contenedor-principal">
        <div class="wecontainer">
            <h1>Llenar Datos</h1>
            
            <?php if (!empty($errores)): ?>
                <div class="error-message">
                    <strong>Se encontraron los siguientes errores:</strong>
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-grid"> <div class="form-group">
                        <label for="numero_cedula">Número de Cédula:</label>
                        <input type="text" id="numero_cedula" name="numero_cedula" 
                                pattern="\d{2,8}"
                                title="La cédula debe tener entre 2 y 8 dígitos"
                                value="<?php echo isset($datos['numero_cedula']) ? htmlspecialchars($datos['numero_cedula']) : ''; ?>"
                                maxlength="8">
                    </div>

                    <div class="form-group">
                        <label for="nombres">Nombres: <span class="required">*</span></label>
                        <input type="text" id="nombres" name="nombres" 
                                required
                                pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                                title="Solo se permiten letras y espacios"
                                value="<?php echo isset($datos['nombres']) ? htmlspecialchars($datos['nombres']) : ''; ?>"
                                maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="apellidos">Apellidos: <span class="required">*</span></label>
                        <input type="text" id="apellidos" name="apellidos" 
                                required
                                pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                                title="Solo se permiten letras y espacios"
                                value="<?php echo isset($datos['apellidos']) ? htmlspecialchars($datos['apellidos']) : ''; ?>"
                                maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="sexo">Sexo:</label>
                        <select id="sexo" name="sexo">
                            <option value="" <?php if (isset($datos['sexo']) && $datos['sexo'] == '') echo 'selected'; ?>>Seleccione</option>
                            <option value="Masculino" <?php if (isset($datos['sexo']) && $datos['sexo'] == 'Masculino') echo 'selected'; ?>>Masculino</option>
                            <option value="Femenino" <?php if (isset($datos['sexo']) && $datos['sexo'] == 'Femenino') echo 'selected'; ?>>Femenino</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="tel" id="telefono" name="telefono" 
                                pattern="\d{11}"
                                title="El teléfono debe tener exactamente 11 dígitos"
                                value="<?php echo isset($datos['telefono']) ? htmlspecialchars($datos['telefono']) : ''; ?>"
                                maxlength="11">
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo:</label>
                        <div class="correo-display">
                            <?php echo htmlspecialchars(obtenerMail($conn, $idusuario) ?? 'No disponible'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" 
                                value="<?php echo isset($datos['direccion']) ? htmlspecialchars($datos['direccion']) : ''; ?>"
                                maxlength="200">
                    </div>
                </div>

                <input type="submit" class="button" value="Guardar cambios">
            </form>
        </div>
    </div>

    <?php $conn->close();?>
</body>

</html>