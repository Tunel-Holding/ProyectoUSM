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
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);
include 'comprobar_sesion.php';
actualizar_actividad();
require "conexion.php";

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
            return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $dato) ? $dato : false;
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
$datos = [];
$idusuario = $_SESSION['idusuario'] ?? null;

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
            if (usuarioTieneDatos($conn, $idusuario)) {
                $errores[] = "Error: Ya existen datos para este usuario.";
            } else {
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
                    if ($valor === 'Ninguno') {
                        $valor = '';
                    }
                    if ($config['requerido'] && empty($valor)) {
                        $errores[] = "El campo " . ucfirst(str_replace('_', ' ', $campo)) . " es requerido.";
                        continue;
                    }
                    if (empty($valor)) {
                        $datos[$campo] = '';
                        continue;
                    }
                    $valorValidado = validarEntrada($valor, $config['tipo']);
                    if ($valorValidado === false) {
                        $errores[] = "El campo " . ucfirst(str_replace('_', ' ', $campo)) . " tiene un formato inválido.";
                    } else {
                        $datos[$campo] = $valorValidado;
                    }
                }
                if (empty($errores)) {
                    $correo = obtenerMail($conn, $idusuario); // Obtener el correo antes

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
                            header("Location: datos.php");
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
$correo_usuario = obtenerMail($conn, $idusuario);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- <link rel="icon" href="css/icono.png" type="image/png"> -->
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Datos - USM</title>
    <script src="js/control_inactividad.js"></script>
    <style>
        body.dark-mode {
            --background-color: rgb(50, 50, 50);
            --text-color: white;
            --background-form: rgb(147, 136, 136);
        }
        .pagina {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }
        .wecontainer {
            font-family: "Poppins", sans-serif;
            max-width: 500px;
            background: #fff;
            color: var(--background-form);
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            border-top: 10px solid #ffd700;
            border-bottom: 10px solid #ffd700;
            text-align: center;
            display: flex;
            flex-direction: column;
        }
        .wecontainer h1 {
            margin-bottom: 20px;
            color: #004c97;
        }
        .wecontainer .form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: center;
        }
        .wecontainer label {
            color: #004c97;
            font-size: 0.9em;
        }
        .wecontainer input,
        select {
            left: 40%;
            padding: 8px;
            width: 100%;
            border: 1px solid #004c97;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .wecontainer.button {
            grid-column: span 2;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ffd700;
            color: #004c97;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .wecontainer.button:hover {
            background-color: #ffcc00;
        }
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #ffcdd2;
        }
        .error-message ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        .required {
            color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="contenedorentrante3">
        <img src="css\logo.png" alt="Logo USM">
    </div>
    <div class="cabecera">
        <button type="button" id="logoButton">
            <!-- <img src="css/logo.png" alt="Logo"> -->
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
                <div class="form">
                    <label for="numero_cedula">Número de Cédula:</label>
                    <input type="text" id="numero_cedula" name="numero_cedula" 
                           pattern="\d{2,8}"
                           title="La cédula debe tener entre 2 y 8 dígitos"
                           value="<?php echo isset($datos['numero_cedula']) ? htmlspecialchars($datos['numero_cedula']) : ''; ?>"
                           maxlength="8">
                    <label for="nombres">Nombres: <span class="required">*</span></label>
                    <input type="text" id="nombres" name="nombres" 
                           required
                           pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                           title="Solo se permiten letras y espacios"
                           value="<?php echo isset($datos['nombres']) ? htmlspecialchars($datos['nombres']) : ''; ?>"
                           maxlength="100">
                    <label for="apellidos">Apellidos: <span class="required">*</span></label>
                    <input type="text" id="apellidos" name="apellidos" 
                           required
                           pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                           title="Solo se permiten letras y espacios"
                           value="<?php echo isset($datos['apellidos']) ? htmlspecialchars($datos['apellidos']) : ''; ?>"
                           maxlength="100">
                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo">
                        <option value="" <?php if (isset($datos['sexo']) && $datos['sexo'] == '') echo 'selected'; ?>>Seleccione</option>
                        <option value="Masculino" <?php if (isset($datos['sexo']) && $datos['sexo'] == 'Masculino') echo 'selected'; ?>>Masculino</option>
                        <option value="Femenino" <?php if (isset($datos['sexo']) && $datos['sexo'] == 'Femenino') echo 'selected'; ?>>Femenino</option>
                    </select>
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" 
                           pattern="\d{11}"
                           title="El teléfono debe tener exactamente 11 dígitos"
                           value="<?php echo isset($datos['telefono']) ? htmlspecialchars($datos['telefono']) : ''; ?>"
                           maxlength="11">

                    <label for="correo">Correo:</label>
                    <div style="padding: 8px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; color: #666; font-size: 0.9em;">
                        <?php echo htmlspecialchars($correo_usuario); ?>
                    </div>

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" 
                           value="<?php echo isset($datos['direccion']) ? htmlspecialchars($datos['direccion']) : ''; ?>"
                           maxlength="200">
                </div>
                <input type="submit" class="button" value="Guardar cambios">
            </form>
        </div>
    </div>
    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>