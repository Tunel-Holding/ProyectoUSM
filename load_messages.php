<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['idusuario'])) {
    exit(); // Salir si el usuario no está autenticado
}
$idgrupo = $_SESSION['idmateria'];
// Obtener mensajes
$result = $conn->query("SELECT messages.id, messages.message, messages.created_at, messages.tipo, messages.reply_to, usuarios.id AS user_id, usuarios.nombre_usuario, usuarios.nivel_usuario FROM messages JOIN usuarios ON messages.user_id = usuarios.id WHERE messages.group_id = $idgrupo ORDER BY created_at ASC");
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

$last_date = null;
$last_user_id = null;

// Devolver mensajes en formato HTML
while ($row = $result->fetch_assoc()) {
    $nivel_usuario = htmlspecialchars($row['nivel_usuario']);
    $timestamp = htmlspecialchars(date("h:i A", strtotime($row['created_at'])));
    $current_date = htmlspecialchars(date("Y-m-d", strtotime($row['created_at'])));
    $message_id = htmlspecialchars($row['id']); // Obtener la ID del mensaje
    $reply_to = $row['reply_to'];
    $user_id = $row['user_id'];

    // Obtener la foto de perfil del usuario
    $foto_result = $conn->query("SELECT foto FROM fotousuario WHERE id_usuario = $user_id");
    $foto_row = $foto_result ? $foto_result->fetch_assoc() : null;
    $foto_perfil = $foto_row && $foto_row['foto'] ? htmlspecialchars($foto_row['foto']) : 'css/perfil.png';

    // Insertar una línea de fecha si es un nuevo día 
    if ($last_date !== $current_date) {
        echo '<div class="date-separator">' . date("d M Y", strtotime($row['created_at'])) . '</div>';
        $last_date = $current_date;
    }

    // Mostrar la foto de perfil solo si el mensaje es de un usuario diferente al anterior
    if ($last_user_id !== $user_id) {
        echo '<div class="message-container-' . $nivel_usuario . '">';
        echo '<img src="' . $foto_perfil . '" alt="Perfil" class="profile-icon-' . $nivel_usuario . '">';
        $last_user_id = $user_id;
    } else {
        echo '<div class="message-container-' . $nivel_usuario . '">';
        echo '<img src="css/vacio.png" alt="" class="profile-icon-' . $nivel_usuario . '" style="border: none;">';
    }

    echo '<button class="reply-button" data-message-id="' . $message_id . '">Responder</button>';
    echo '<div class="message-bubble-' . $nivel_usuario . '">';

    if ($reply_to) {
        $reply_result = $conn->query("SELECT message, nombre_usuario, tipo FROM messages JOIN usuarios ON messages.user_id = usuarios.id WHERE messages.id = $reply_to");
        if ($reply_result && $reply_row = $reply_result->fetch_assoc()) {
            if ($reply_row['tipo'] == 'imagen') {
                echo '<div class="reply-preview"><strong>' . htmlspecialchars($reply_row['nombre_usuario']) . ':</strong> <img src="' . htmlspecialchars($reply_row['message']) . '" alt="Imagen" class="reply-image"></div>';
            } else {
                echo '<div class="reply-preview"><strong>' . htmlspecialchars($reply_row['nombre_usuario']) . ':</strong> ' . htmlspecialchars($reply_row['message']) . '</div>';
            }
        }
    }

    if ($row['tipo'] == "texto") {
        echo '<strong>' . htmlspecialchars($row['nombre_usuario']) . ':</strong> ' . '<p id="message-text-' . $message_id . '">' . htmlspecialchars($row['message']) . '</p> <p class="timestamp">' . $timestamp . '</p>';
    } elseif ($row['tipo'] == "imagen") {
        echo '<strong>' . htmlspecialchars($row['nombre_usuario']) . ':</strong> ' . '<img class="msg-foto" src="' . htmlspecialchars($row['message']) . '" alt="Imagen" class="message-image"> <p class="timestamp">' . $timestamp . '</p>';
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

        echo '<strong>' . htmlspecialchars($row['nombre_usuario']) . ':</strong> ' . '<a class="file" href="' . htmlspecialchars($row['message']) . '" target="_blank"> <img src="' . htmlspecialchars($icon_path) . '" alt="">' . htmlspecialchars($file_name) . '</a> <p class="timestamp">' . $timestamp . '</p>';
    }

    echo '</div></div>'; // Cerrar los divs correctamente
}
?>
