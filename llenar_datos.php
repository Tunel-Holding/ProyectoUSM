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
$auth->checkAccess(AuthGuard::NIVEL_USUARIO); // Asumo NIVEL_USUARIO para alumnos
include 'comprobar_sesion.php';
actualizar_actividad();
require "conexion.php";


// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función para validar y sanitizar entrada
function validarEntrada($dato, $tipo = 'texto', $longitudMax = 255) {
    $dato = trim($dato);
    if (strlen($dato) > $longitudMax) {
        return false;
    }
    switch ($tipo) {
        case 'cedula':
            // Cedula: entre 2 y 8 dígitos
            return preg_match('/^\d{2,8}$/', $dato) ? $dato : false;
        case 'nombre':
            // Permite letras, espacios, y caracteres acentuados comunes en español
            return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $dato) ? $dato : false;
        case 'telefono':
            // Teléfono: exactamente 11 dígitos
            return preg_match('/^\d{11}$/', $dato) ? $dato : false;
        case 'email':
            return filter_var($dato, FILTER_VALIDATE_EMAIL) ? $dato : false;
        case 'sexo':
            $valoresPermitidos = ['Masculino', 'Femenino'];
            return in_array($dato, $valoresPermitidos) ? $dato : false;
        case 'texto':
        default:
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
    return $row['email'];
}

$errores = [];
$datos = []; // Array para almacenar los datos del formulario si hay errores de validación
$idusuario = $_SESSION['idusuario'] ?? null;

// Si ya hay datos para el usuario, redirigir a la página de datos (o modificar datos)
// Esto evita que un usuario vuelva a 'llenar' si ya tiene datos
if ($idusuario && usuarioTieneDatos($conn, $idusuario)) {
    header("Location: datos.php"); // Redirige a donde el usuario vea sus datos, por ejemplo
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errores[] = "Error de seguridad: Token CSRF inválido.";
    } else {
        actualizar_actividad();
        if (!isset($_SESSION['idusuario'])) {
            $errores[] = "Error: Usuario no autenticado.";
        } else {
            $idusuario = (int)$_SESSION['idusuario'];
            // Re-check si el usuario tiene datos (puede haber ocurrido algo entre la carga de la página y el POST)
            if (usuarioTieneDatos($conn, $idusuario)) {
                $errores[] = "Error: Ya existen datos para este usuario. No se permite duplicar.";
            } else {
                $campos = [
                    'numero_cedula' => ['tipo' => 'cedula', 'requerido' => false, 'etiqueta' => 'Número de Cédula'],
                    'nombres' => ['tipo' => 'nombre', 'requerido' => true, 'etiqueta' => 'Nombres'],
                    'apellidos' => ['tipo' => 'nombre', 'requerido' => true, 'etiqueta' => 'Apellidos'],
                    'sexo' => ['tipo' => 'sexo', 'requerido' => false, 'etiqueta' => 'Sexo'],
                    'telefono' => ['tipo' => 'telefono', 'requerido' => false, 'etiqueta' => 'Teléfono'],
                    'direccion' => ['tipo' => 'texto', 'requerido' => false, 'etiqueta' => 'Dirección']
                ];
                foreach ($campos as $campo => $config) {
                    $valor = $_POST[$campo] ?? '';
                    // Si el campo es 'sexo' y se selecciona 'Seleccione', se considera vacío
                    if ($campo === 'sexo' && $valor === '') {
                        $valor = ''; // Asegura que se almacene vacío si no se elige una opción válida
                    }
                    
                    if ($config['requerido'] && empty($valor)) {
                        $errores[] = "El campo " . $config['etiqueta'] . " es requerido.";
                        $datos[$campo] = $valor; // Preservar el valor para mostrarlo en el formulario
                        continue;
                    }
                    
                    if (empty($valor)) {
                        $datos[$campo] = ''; // Almacenar vacío si no es requerido y está vacío
                        continue;
                    }
                    
                    $valorValidado = validarEntrada($valor, $config['tipo']);
                    if ($valorValidado === false) {
                        $errores[] = "El campo " . $config['etiqueta'] . " tiene un formato inválido.";
                        $datos[$campo] = $valor; // Preservar el valor con formato inválido para que el usuario lo corrija
                    } else {
                        $datos[$campo] = $valorValidado;
                    }
                }

                if (empty($errores)) {
                    $correo = obtenerMail($conn, $idusuario); // Obtener el correo antes de la inserción

                    $stmt = $conn->prepare("INSERT INTO datos_usuario (usuario_id, cedula, nombres, apellidos, sexo, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("isssssss",
                            $idusuario,
                            $datos['numero_cedula'],
                            $datos['nombres'],
                            $datos['apellidos'],
                            $datos['sexo'],
                            $datos['telefono'],
                            $correo,
                            $datos['direccion']
                        );
                        if ($stmt->execute()) {
                            $stmt->close();
                            $conn->close();
                            header("Location: datos.php"); // Redirigir a la página de datos después de guardar
                            exit();
                        } else {
                            $errores[] = "Error al guardar los datos en la base de datos: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $errores[] = "Error en la preparación de la consulta SQL: " . $conn->error;
                    }
                }
            }
        }
    }
}

actualizar_actividad();
$correo_usuario = obtenerMail($conn, $idusuario);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Llenar Datos - UniHub</title>
    <script src="js/control_inactividad.js"></script>
    <style>
        /* Variables CSS para el modo oscuro y colores base */
        :root {
            --background-color-light: #f4f7f6; /* Fondo general claro, más suave */
            --text-color-light: #333; /* Color de texto general claro */
            --form-background-light: #ffffff; /* Fondo del formulario claro */
            --primary-color: #004c97; /* Azul USM para títulos, labels y énfasis */
            --secondary-color: #ffd700; /* Amarillo USM para bordes de formulario, botones */
            --border-color-light: #dee2e6; /* Borde de inputs y campos de solo lectura claro */
            --input-bg-light: #f8f9fa; /* Fondo de input de solo lectura claro */
            --input-text-readonly-light: #6c757d; /* Texto de campos de solo lectura claro */
            --shadow-color-light: rgba(0, 0, 0, 0.15); /* Sombra suave para elementos */
            --error-bg-light: #ffebee; /* Fondo de error claro */
            --error-text-light: #c62828; /* Texto de error claro */
            --error-border-light: #ffcdd2; /* Borde de error claro */
        }

        /* Definiciones para el modo oscuro */
        body.dark-mode {
            --background-color: rgb(50, 50, 50); /* Fondo general oscuro */
            --text-color: white; /* Color de texto general oscuro */
            --form-background: rgb(80, 80, 80); /* Fondo del formulario oscuro, un poco más claro que el fondo general */
            --primary-color: #ffd700; /* Amarillo USM como primario en oscuro (para títulos, etc.) */
            --secondary-color: #004c97; /* Azul USM como secundario en oscuro (para bordes, acentos) */
            --border-color: #666; /* Borde de inputs y campos de solo lectura oscuro */
            --input-bg: #444; /* Fondo de input de solo lectura oscuro */
            --input-text-readonly: #cccccc; /* Texto de campos de solo lectura oscuro */
            --shadow-color-dark: rgba(0, 0, 0, 0.4); /* Sombra más pronunciada en oscuro */
            --error-bg-dark: rgba(220, 53, 69, 0.3); /* Fondo de error oscuro */
            --error-text-dark: #ffb3b3; /* Texto de error oscuro */
            --error-border-dark: #ffb3b3; /* Borde de error oscuro */
        }

        body {
            font-family: "Poppins", sans-serif; /* Asegura que la fuente se aplique globalmente */
            margin: 0;
            padding: 0;
        }

        .pagina {
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
            background-color: var(--background-color-light); /* Fondo general claro */
            transition: background-color 0.5s ease-in-out;
        }
        body.dark-mode .pagina {
            background-color: var(--background-color);
        }

        .wecontainer {
            max-width: 600px; /* Ajustado para un formulario de llenado de datos */
            width: 90%;
            background: var(--form-background-light);
            color: var(--primary-color);
            padding: 40px;
            box-shadow: 2px 4px 12px var(--shadow-color-light);
            border-radius: 10px;
            border-top: 10px solid var(--secondary-color);
            border-bottom: 10px solid var(--secondary-color);
            border-left: 1px solid var(--secondary-color); /* Borde lateral opcional */
            border-right: 1px solid var(--secondary-color); /* Borde lateral opcional */
            text-align: center;
            display: flex;
            flex-direction: column;
            transition: background 0.5s ease-in-out, box-shadow 0.5s ease, border-color 0.5s ease;
        }
        body.dark-mode .wecontainer {
            background: var(--form-background);
            color: var(--primary-color); /* Títulos, etc. en amarillo en dark mode */
            box-shadow: 2px 4px 12px var(--shadow-color-dark);
            border-color: var(--primary-color); /* Bordes en amarillo en dark mode */
        }

        .wecontainer h1 {
            margin-bottom: 25px;
            color: var(--primary-color);
            font-size: 2.2em;
            font-weight: 700;
        }
        body.dark-mode .wecontainer h1 {
            color: var(--primary-color); /* Amarillo en dark mode */
        }

        .wecontainer .form {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Dos columnas para campos */
            gap: 20px 30px; /* Espaciado entre filas y columnas */
            align-items: center;
            padding: 0 15px; /* Padding horizontal para el grid */
        }

        .wecontainer label {
            color: var(--primary-color);
            font-size: 1.15em;
            font-weight: 600;
            text-align: right;
            padding-right: 15px;
            box-sizing: border-box;
        }
        body.dark-mode .wecontainer label {
            color: var(--text-color);
        }

        .wecontainer input[type="text"],
        .wecontainer input[type="tel"],
        .wecontainer select {
            padding: 12px 15px;
            width: 100%;
            background: var(--form-background-light);
            border: 1px solid var(--border-color-light);
            border-radius: 8px;
            font-size: 1em;
            color: var(--text-color-light);
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.5s ease, color 0.5s ease;
            -webkit-appearance: none; /* Para customizar select en algunos navegadores */
            -moz-appearance: none;
            appearance: none;
        }

        body.dark-mode .wecontainer input[type="text"],
        body.dark-mode .wecontainer input[type="tel"],
        body.dark-mode .wecontainer select {
            background: var(--input-bg);
            border-color: var(--border-color);
            color: var(--text-color);
        }

        .wecontainer input:focus,
        .wecontainer select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 76, 151, 0.25);
        }
        body.dark-mode .wecontainer input:focus,
        body.dark-mode .wecontainer select:focus {
            border-color: var(--primary-color); /* Amarillo en dark mode */
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.25);
        }
        /* Estilo para inputs con error */
        .wecontainer input.error,
        .wecontainer select.error {
            border-color: var(--error-text-light);
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25);
        }
        body.dark-mode .wecontainer input.error,
        body.dark-mode .wecontainer select.error {
            border-color: var(--error-text-dark);
            box-shadow: 0 0 0 3px rgba(255, 179, 179, 0.25);
        }

        /* Estilo para el campo de correo no editable */
        .wecontainer .correo-display {
            padding: 12px 15px;
            background-color: var(--input-bg-light);
            border: 1px solid var(--border-color-light);
            border-radius: 8px;
            color: var(--input-text-readonly-light);
            font-size: 1em;
            box-sizing: border-box;
            width: 100%;
            text-align: left;
            min-height: 44px; /* Para que coincida con la altura de los inputs */
            display: flex;
            align-items: center;
            transition: background-color 0.5s ease, border-color 0.5s ease, color 0.5s ease;
        }
        body.dark-mode .wecontainer .correo-display {
            background-color: var(--input-bg);
            border-color: var(--border-color);
            color: var(--input-text-readonly);
        }

        /* Botón de guardar cambios */
        .wecontainer .button {
            grid-column: span 2; /* El botón ocupa ambas columnas */
            margin-top: 25px;
            padding: 14px 30px;
            background: var(--secondary-color); /* Amarillo USM */
            color: var(--primary-color); /* Azul USM */
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: 700;
            box-shadow: 0 4px 10px var(--shadow-color-light);
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease, color 0.3s ease;
            display: block;
            width: fit-content; /* Ajustar al contenido del texto */
            margin-left: auto; /* Centrar el botón */
            margin-right: auto; /* Centrar el botón */
        }
        .wecontainer .button:hover {
            background-color: var(--primary-color); /* Azul USM */
            color: white; /* Texto blanco */
            transform: translateY(-2px);
            box-shadow: 0 6px 15px var(--shadow-color-light);
        }
        body.dark-mode .wecontainer .button {
            background: var(--secondary-color); /* Azul USM */
            color: var(--primary-color); /* Amarillo USM */
        }
        body.dark-mode .wecontainer .button:hover {
            background-color: var(--primary-color); /* Amarillo USM */
            color: var(--secondary-color); /* Azul USM */
            box-shadow: 0 6px 15px var(--shadow-color-dark);
        }

        /* Estilos para mensajes de error */
        .error-message {
            grid-column: span 2; /* Ocupa ambas columnas en el grid */
            background-color: var(--error-bg-light); /* Rojo claro */
            color: var(--error-text-light); /* Rojo oscuro */
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--error-border-light);
            text-align: left;
            font-weight: 500;
            box-shadow: 0 2px 5px var(--shadow-color-light);
        }
        body.dark-mode .error-message {
            background-color: var(--error-bg-dark);
            color: var(--error-text-dark);
            border-color: var(--error-border-dark);
            box-shadow: 0 2px 5px var(--shadow-color-dark);
        }

        .error-message strong {
            display: block;
            margin-bottom: 8px;
            font-size: 1.1em;
        }

        .error-message ul {
            list-style-type: disc; /* Puntos de lista */
            margin: 0;
            padding-left: 25px;
        }

        .error-message li {
            margin-bottom: 5px;
        }

        .required {
            color: #d32f2f; /* Rojo para indicar campos requeridos */
            font-weight: 700;
            margin-left: 5px; /* Espacio entre el texto y el asterisco */
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .wecontainer {
                padding: 25px;
                width: 95%;
            }

            .wecontainer h1 {
                font-size: 2em;
            }

            .wecontainer .form {
                grid-template-columns: 1fr; /* Una sola columna en pantallas pequeñas */
                gap: 15px;
                padding: 0;
            }

            .wecontainer label {
                text-align: left;
                padding-right: 0;
                margin-bottom: 5px;
            }

            .wecontainer input[type="text"],
            .wecontainer input[type="tel"],
            .wecontainer select,
            .wecontainer .correo-display,
            .wecontainer .button,
            .error-message {
                grid-column: span 1; /* Todos ocupan una columna */
                width: 100%;
                text-align: left; /* Alinea los campos de texto a la izquierda */
            }
            .wecontainer .button {
                margin-left: 0;
                margin-right: 0;
            }
        }

        @media (max-width: 480px) {
            .wecontainer {
                padding: 15px;
                border-radius: 8px;
                border-top-width: 8px;
                border-bottom-width: 8px;
            }

            .wecontainer h1 {
                font-size: 1.8em;
                margin-bottom: 15px;
            }

            .wecontainer input,
            .wecontainer select,
            .wecontainer .correo-display {
                padding: 10px 12px;
                font-size: 0.9em;
                border-radius: 6px;
            }

            .wecontainer .button {
                padding: 12px 25px;
                font-size: 1em;
                margin-top: 20px;
                border-radius: 6px;
            }

            .error-message {
                font-size: 0.9em;
                padding: 10px;
                border-radius: 6px;
            }
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
    <?php include 'menu_alumno.php'; ?>
    <div class="pagina">
        <div class="wecontainer">
            <h1>Llenar Datos del Alumno</h1>
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
                <div class="form">
                    <label for="numero_cedula">Número de Cédula:</label>
                    <input type="text" id="numero_cedula" name="numero_cedula"
                            pattern="\d{2,8}"
                            title="La cédula debe tener entre 2 y 8 dígitos numéricos."
                            value="<?php echo isset($datos['numero_cedula']) ? htmlspecialchars($datos['numero_cedula']) : ''; ?>"
                            maxlength="8"
                            class="<?php echo isset($errores) && in_array("El campo Número de Cédula tiene un formato inválido.", $errores) ? 'error' : ''; ?>">

                    <label for="nombres">Nombres: <span class="required">*</span></label>
                    <input type="text" id="nombres" name="nombres"
                            required
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                            title="Solo se permiten letras y espacios."
                            value="<?php echo isset($datos['nombres']) ? htmlspecialchars($datos['nombres']) : ''; ?>"
                            maxlength="100"
                            class="<?php echo isset($errores) && (in_array("El campo Nombres es requerido.", $errores) || in_array("El campo Nombres tiene un formato inválido.", $errores)) ? 'error' : ''; ?>">

                    <label for="apellidos">Apellidos: <span class="required">*</span></label>
                    <input type="text" id="apellidos" name="apellidos"
                            required
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                            title="Solo se permiten letras y espacios."
                            value="<?php echo isset($datos['apellidos']) ? htmlspecialchars($datos['apellidos']) : ''; ?>"
                            maxlength="100"
                            class="<?php echo isset($errores) && (in_array("El campo Apellidos es requerido.", $errores) || in_array("El campo Apellidos tiene un formato inválido.", $errores)) ? 'error' : ''; ?>">

                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo"
                            class="<?php echo isset($errores) && in_array("El campo Sexo tiene un formato inválido.", $errores) ? 'error' : ''; ?>">
                        <option value="" <?php if (isset($datos['sexo']) && $datos['sexo'] == '') echo 'selected'; ?>>Seleccione</option>
                        <option value="Masculino" <?php if (isset($datos['sexo']) && $datos['sexo'] == 'Masculino') echo 'selected'; ?>>Masculino</option>
                        <option value="Femenino" <?php if (isset($datos['sexo']) && $datos['sexo'] == 'Femenino') echo 'selected'; ?>>Femenino</option>
                    </select>

                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono"
                            pattern="\d{11}"
                            title="El teléfono debe tener exactamente 11 dígitos numéricos."
                            value="<?php echo isset($datos['telefono']) ? htmlspecialchars($datos['telefono']) : ''; ?>"
                            maxlength="11"
                            class="<?php echo isset($errores) && in_array("El campo Teléfono tiene un formato inválido.", $errores) ? 'error' : ''; ?>">

                    <label for="correo">Correo:</label>
                    <div class="correo-display">
                        <?php echo htmlspecialchars($correo_usuario); ?>
                    </div>

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion"
                            value="<?php echo isset($datos['direccion']) ? htmlspecialchars($datos['direccion']) : ''; ?>"
                            maxlength="200"
                            class="<?php echo isset($errores) && in_array("El campo Dirección tiene un formato inválido.", $errores) ? 'error' : ''; ?>">
                </div>
                <input type="submit" class="button" value="Guardar datos">
            </form>
        </div>
    </div>
</body>
</html>