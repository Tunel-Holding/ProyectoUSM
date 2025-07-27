<?php
// guardar_comentario_profesores.php
// Endpoint AJAX para guardar comentarios y respuestas en foro_profesor.php
include 'comprobar_sesion.php';
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');
$conn->set_charset("utf8mb4");

$user_id = isset($_SESSION['idusuario']) ? intval($_SESSION['idusuario']) : 0;
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$archivo_id = isset($_POST['comentario_archivo_id']) ? intval($_POST['comentario_archivo_id']) : 0;
$comentario = isset($_POST['comentario_texto']) ? trim($_POST['comentario_texto']) : '';
$id_comentario_padre = isset($_POST['id_comentario_padre']) ? intval($_POST['id_comentario_padre']) : null;

if ($archivo_id <= 0 || $comentario === '') {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

if ($id_comentario_padre) {
    $stmt = $conn->prepare("INSERT INTO comentarios (archivo_id, id_usuario, comentario, fecha, id_comentario_padre) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("iisi", $archivo_id, $user_id, $comentario, $id_comentario_padre);
} else {
    $stmt = $conn->prepare("INSERT INTO comentarios (archivo_id, id_usuario, comentario, fecha) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $archivo_id, $user_id, $comentario);
}
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar comentario']);
}
$stmt->close();
