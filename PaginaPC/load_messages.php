<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['idusuario'])) {
    exit(); // Salir si el usuario no está autenticado
}

// Obtener mensajes
$result = $conn->query("SELECT messages.message, usuarios.nombre_usuario FROM messages JOIN usuarios ON messages.user_id = usuarios.id ORDER BY created_at ASC");
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

// Devolver mensajes en formato HTML
while ($row = $result->fetch_assoc()) {
    echo '<div class="message-bubble"><strong>' . htmlspecialchars($row['nombre_usuario']) . ':</strong> ' . htmlspecialchars($row['message']) . '</div>';
}
?>