<?php

// Configuración segura de la cookie de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Solo si usas HTTPS
ini_set('session.cookie_samesite', 'Strict');
session_start();

include 'conexion.php';

// Función para limpiar y validar entrada
function limpiar_entrada($dato) {
    return htmlspecialchars(stripslashes(trim($dato)));
}

try {
    // Crear conexión usando PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener y validar datos de entrada
    $nombre_usuario = isset($_POST['usuario']) ? limpiar_entrada($_POST['usuario']) : '';
    $clave = isset($_POST['Password1']) ? $_POST['Password1'] : '';

    if (empty($nombre_usuario) || empty($clave)) {
        throw new Exception("Todos los campos son obligatorios");
    }

    // Validar formato de usuario (solo letras, números, guion y guion bajo, máximo 50)
    if (!preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $nombre_usuario)) {
        throw new Exception("Usuario o contraseña incorrectos.");
    }

    if (strlen($clave) > 50) {
        throw new Exception("Usuario o contraseña incorrectos.");
    }
    $sql = "SELECT id, nombre_usuario, contrasena, nivel_usuario, email FROM usuarios WHERE nombre_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre_usuario]);

    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar contraseña
        if (password_verify($clave, $row['contrasena'])) {
            // Regenerar sesión para evitar session fixation
            session_regenerate_id(true);

            // Guardar datos básicos del usuario
            $_SESSION['idusuario'] = $row['id'];
            $_SESSION['nombre_usuario'] = $row['nombre_usuario'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['nivelusu'] = $row['nivel_usuario'];
            $_SESSION['ultimo_acceso'] = time();

            // Buscar nombre en datos_usuario
            $sql_datos = "SELECT nombres FROM datos_usuario WHERE usuario_id = ?";
            $stmt_datos = $pdo->prepare($sql_datos);
            $stmt_datos->execute([$row['id']]);

            if ($stmt_datos->rowCount() == 1) {
                $datos = $stmt_datos->fetch(PDO::FETCH_ASSOC);
                $_SESSION['nombres'] = $datos['nombres']; // 💌 nombre desde datos_usuario
            } else {
                $_SESSION['nombres'] = "Sin registrar";
            }

            // Si es alumno, buscar semestre
            if ($row['nivel_usuario'] == 1) {
                $sql_estudiante = "SELECT semestre FROM estudiantes WHERE id_usuario = ?";
                $stmt_estudiante = $pdo->prepare($sql_estudiante);
                $stmt_estudiante->execute([$row['id']]);
                $estudiante = $stmt_estudiante->fetch(PDO::FETCH_ASSOC);
                if ($estudiante) {
                    $_SESSION['semestre_usu'] = $estudiante['semestre'];
                }
            }

            // Redirección según rol
            switch ($row['nivel_usuario']) {
                case 3: header("Location: pagina_administracion.php"); break;
                case 2: header("Location: pagina_profesor.php"); break;
                case 1: header("Location: pagina_principal.php"); break;
                default: header("Location: intermedio.php");
            }
            exit();

        } else {
            $_SESSION['mensaje'] = "Usuario o contraseña incorrectos.";
            header("Location: inicio.php");
            exit();
        }

    } else {
        $_SESSION['mensaje'] = "Usuario o contraseña incorrectos.";
        header("Location: inicio.php");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error de base de datos en login: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error del sistema. Por favor, intente más tarde.";
    header("Location: inicio.php");
    exit();

} catch (Exception $e) {
    $_SESSION['mensaje'] = $e->getMessage();
    header("Location: inicio.php");
    exit();

} finally {
    if (isset($pdo)) {
        $pdo = null;
    }
}
?>
