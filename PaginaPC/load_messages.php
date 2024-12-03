<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['idusuario'])) {
    exit(); // Salir si el usuario no está autenticado
}
$idgrupo = $_SESSION['idmateria'];
// Obtener mensajes
$result = $conn->query("SELECT messages.message, messages.created_at,usuarios.nombre_usuario, usuarios.nivel_usuario FROM messages JOIN usuarios ON messages.user_id = usuarios.id WHERE messages.group_id = $idgrupo ORDER BY created_at ASC");
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

// Devolver mensajes en formato HTML
while ($row = $result->fetch_assoc()) {
    $nivel_usuario = htmlspecialchars($row['nivel_usuario']);
    $timestamp = htmlspecialchars(date("h:i A",strtotime($row['created_at'])));
    echo '<div class="message-bubble-'.$nivel_usuario.'"><strong>' . htmlspecialchars($row['nombre_usuario']) . ':</strong> ' . '<p>'. htmlspecialchars($row['message']) .'</p> <p class="timestamp">' . $timestamp .'</p> </div>';
}
?>