<?php
require 'conexion.php';
session_start();

if (!isset($_SESSION['idusuario'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nuevo_texto = isset($_POST['nuevo_texto']) ? trim($_POST['nuevo_texto']) : '';

    if ($id <= 0 || $nuevo_texto === '') {
        echo json_encode(['error' => 'Datos inválidos']);
        exit;
    }

    // Solo permitir editar mensajes de tipo texto y del usuario actual
    $stmt = $conn->prepare("UPDATE messages SET message = ? WHERE id = ? AND user_id = ? AND tipo = 'texto'");
    $stmt->bind_param("sii", $nuevo_texto, $id, $_SESSION['idusuario']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al actualizar el mensaje']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(['error' => 'Petición inválida']);
exit;