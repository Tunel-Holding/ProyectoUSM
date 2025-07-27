<?php
include 'comprobar_sesion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);

// Solo permitir acceso a estudiantes
if (!isset($_SESSION['nivel_usuario']) || $_SESSION['nivel_usuario'] != 1) {
    echo '<div style="padding:40px;text-align:center;color:#a94442;font-weight:bold;">Acceso solo para estudiantes.</div>';
    exit();
}

include 'conexion.php';
$conn->set_charset("utf8mb4");

$user_id = $_SESSION['idusuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_publicacion = isset($_POST['id_publicacion']) ? intval($_POST['id_publicacion']) : 0;
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

    if ($id_publicacion > 0 && $comentario !== '') {
        $sql = "INSERT INTO comentarios (id_publicacion, id_usuario, comentario, fecha) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iis", $id_publicacion, $user_id, $comentario);
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: foro.php");
                exit();
            } else {
                $error = "Error al guardar el comentario.";
            }
        } else {
            $error = "Error en la base de datos.";
        }
    } else {
        $error = "Datos incompletos.";
    }
} else {
    $error = "Acceso no permitido.";
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error al comentar</title>
    <link rel="stylesheet" href="css/principalunihub.css">
</head>
<body>
    <div style="max-width:500px;margin:60px auto;padding:32px;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(33,53,85,0.10);text-align:center;">
        <h2 style="color:#a94442;">Ocurrió un error</h2>
        <p><?php echo isset($error) ? htmlspecialchars($error) : 'Error desconocido.'; ?></p>
        <a href="foro.php" style="color:#174388;text-decoration:underline;">Volver al foro</a>
    </div>
</body>
</html>
