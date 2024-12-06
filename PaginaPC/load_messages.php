<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['idusuario'])) {
    exit(); // Salir si el usuario no está autenticado
}
$idgrupo = $_SESSION['idmateria'];
// Obtener mensajes
$result = $conn->query("SELECT messages.message, messages.created_at, messages.tipo, usuarios.nombre_usuario, usuarios.nivel_usuario FROM messages JOIN usuarios ON messages.user_id = usuarios.id WHERE messages.group_id = $idgrupo ORDER BY created_at ASC");
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

$last_date = null;

// Devolver mensajes en formato HTML
while ($row = $result->fetch_assoc()) {
    $nivel_usuario = htmlspecialchars($row['nivel_usuario']);
    $timestamp = htmlspecialchars(date("h:i A",strtotime($row['created_at'])));
    $current_date = htmlspecialchars(date("Y-m-d", strtotime($row['created_at']))); 
    // Insertar una línea de fecha si es un nuevo día 
    if ($last_date !== $current_date) { 
        echo '<div class="date-separator">' . date("d M Y", strtotime($row['created_at'])) . '</div>'; 
        $last_date = $current_date; 
    }
    if ($row['tipo']=="texto") {
        echo '<div class="message-bubble-'.$nivel_usuario.'"><strong>' . htmlspecialchars($row['nombre_usuario']) . ':</strong> ' . '<p>'. htmlspecialchars($row['message']) .'</p> <p class="timestamp">' . $timestamp .'</p> </div>';
    } elseif ($row['tipo'] == "imagen") {
        echo '<div class="message-bubble-'.$nivel_usuario.'"><strong>' . htmlspecialchars($row['nombre_usuario']) . ':</strong> ' . '<img class="msg-foto" src="'. htmlspecialchars($row['message']) .'" alt="Imagen"> <p class="timestamp">' . $timestamp .'</p> </div>';
    } elseif ($row['tipo'] == "archivo") {
        $file_name = basename($row['message']);
        $file_extension = pathinfo($row['message'], PATHINFO_EXTENSION);

        $icon_path = 'css';
        if (in_array($file_extension, ['doc', 'docx'])) {
            $icon_path = "css/word.png";
        } elseif (in_array($file_extension, ['xls', 'xlsx'])) {
            $icon_path = "css/excel.png";
        } elseif (in_array($file_extension, ['ppt', 'pptx'])) {
            $icon_path = "css/powerpoint.png";
        } elseif ($file_extension == 'pdf') {
            $icon_path = "css/pdf.png";
        }

        echo '<div class="message-bubble-'.$nivel_usuario.'"><strong>' . htmlspecialchars($row['nombre_usuario']) . ':</strong> ' . '<a class="file" href="'. htmlspecialchars($row['message']) .'" target="_blank"> <img src="'. htmlspecialchars($icon_path). '"alt="">' . htmlspecialchars($file_name) .'</a> <p class="timestamp">' . $timestamp .'</p> </div>';
    }
    
}
?>