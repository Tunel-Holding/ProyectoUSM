<?php
include 'comprobar_sesion.php';
actualizar_actividad();
session_start();
require 'conexion.php';

// 🔐 Validación de sesión
if (!isset($_SESSION['idusuario'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit();
}

// 📨 Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// 🔍 Verificar que se proporcionó el ID del mensaje
if (!isset($_POST['message_id']) || !is_numeric($_POST['message_id'])) {
    echo json_encode(['error' => 'ID de mensaje inválido']);
    exit();
}

$message_id = (int)$_POST['message_id'];
$user_id = $_SESSION['idusuario'];
$user_level = $_SESSION['nivel_usuario'] ?? 'usuario';

// 🔍 Obtener información del mensaje
$stmt = $conn->prepare("
    SELECT m.id, m.user_id, m.message, m.tipo, m.group_id, u.nivel_usuario 
    FROM messages m 
    JOIN usuarios u ON m.user_id = u.id 
    WHERE m.id = ?
");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode(['error' => 'Mensaje no encontrado']);
    exit();
}

$message_data = $result->fetch_assoc();

// 🔐 Verificar permisos de eliminación
$can_delete = false;

// El usuario puede eliminar su propio mensaje
if ($message_data['user_id'] == $user_id) {
    $can_delete = true;
}

// Los administradores y profesores pueden eliminar cualquier mensaje
if (in_array($user_level, ['administrador', 'profesor'])) {
    $can_delete = true;
}

if (!$can_delete) {
    echo json_encode(['error' => 'No tienes permisos para eliminar este mensaje']);
    exit();
}

// 🗑️ Eliminar archivo físico si es imagen o archivo
if (in_array($message_data['tipo'], ['imagen', 'archivo'])) {
    $file_path = $message_data['message'];
    if (file_exists($file_path) && strpos($file_path, 'uploads/') === 0) {
        unlink($file_path);
    }
}

// 🗑️ Eliminar el mensaje de la base de datos
$stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
$stmt->bind_param("i", $message_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Mensaje eliminado correctamente']);
} else {
    echo json_encode(['error' => 'Error al eliminar el mensaje']);
}

actualizar_actividad();
$conn->close();
?> 