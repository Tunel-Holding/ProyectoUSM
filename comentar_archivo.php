<?php
session_start();
include 'conexion.php';
$conn->set_charset("utf8mb4");

// Detectar si la petición es AJAX (fetch)
$isAjax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    (isset($_SERVER['HTTP_SEC_FETCH_MODE']) && strtolower($_SERVER['HTTP_SEC_FETCH_MODE']) === 'cors')
);

$comentario_exito = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario_archivo_id'], $_POST['comentario_texto'])) {
    $archivo_id = intval($_POST['comentario_archivo_id']);
    $comentario = trim($_POST['comentario_texto']);
    $id_comentario_padre = isset($_POST['id_comentario_padre']) ? intval($_POST['id_comentario_padre']) : null;
    $user_id = isset($_SESSION['idusuario']) ? intval($_SESSION['idusuario']) : 0;
    if ($archivo_id > 0 && $comentario !== '' && $user_id > 0) {
        if ($id_comentario_padre) {
            $stmt_com = $conn->prepare("INSERT INTO comentarios (archivo_id, id_usuario, comentario, fecha, id_comentario_padre) VALUES (?, ?, ?, NOW(), ?)");
            $stmt_com->bind_param("iisi", $archivo_id, $user_id, $comentario, $id_comentario_padre);
        } else {
            $stmt_com = $conn->prepare("INSERT INTO comentarios (archivo_id, id_usuario, comentario, fecha) VALUES (?, ?, ?, NOW())");
            $stmt_com->bind_param("iis", $archivo_id, $user_id, $comentario);
        }
        if ($stmt_com->execute()) {
            $comentario_exito = true;
        } else {
            $error = 'Error al guardar el comentario.';
        }
        $stmt_com->close();
    } else {
        $error = 'Datos incompletos.';
    }
} else {
    $error = 'Petición inválida.';
}

if ($isAjax) {
    header('Content-Type: application/json');
    if ($comentario_exito) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $error]);
    }
    exit;
}

// Si no es AJAX, redirigir de vuelta (comportamiento clásico)
if ($comentario_exito && isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    echo '<div style="color:red;text-align:center;">'.htmlspecialchars($error).'</div>';
}
