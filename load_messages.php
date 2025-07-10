<?php
session_start();
require 'conexion.php';

// 🔐 Validación de sesión activa
if (!isset($_SESSION['idusuario'])) {
    exit(); // Usuario no autenticado
}

// 🍪 Leer el tema desde la cookie (si no existe, usar "light")
$tema = $_COOKIE['theme'] ?? 'light';

// 🎨 Paletas pastel
$palette_light = [
    "#FFB3BA", "#FFDFBA", "#FFFFBA", "#BAFFC9", "#BAE1FF", "#E0BBE4",
    "#D5F0DC", "#FCE1E4", "#F3EAC2", "#CDE7FF", "#FAD6FF", "#D7F9F1",
    "#FEE1E8", "#F6F0B2", "#F0C6B0", "#D9CEEA", "#BFFCC6", "#ECEAE4",
    "#FFC8DD", "#FAD2E1", "#CDEAC0", "#9DF9EF", "#A0CED9", "#FFD6A5",
    "#E2F0CB", "#FFABAB", "#B5EAD7", "#DADFF7", "#FFDEF0", "#D5E2FF"
];


$palette_dark = [
    "#CC9095", "#CCA98C", "#CCCC8A", "#95C7A5", "#95B4CC", "#B496B8",
    "#A8C3B2", "#D9AEB2", "#C9BD96", "#A3BCD4", "#D0A3CC", "#94CDAA",
    "#A4C4BC", "#D0ACB2", "#C3BE85", "#B89C8E", "#A59CC2", "#96CCA8",
    "#B0ACA8", "#CC99AA", "#D0A6B2", "#A5CBA0", "#6DC2B8", "#85A6B2",
    "#CCAA85", "#B5CBA0", "#CC898A", "#91C2AA", "#ACB4D0", "#9BADAF"
];

// 🧠 Seleccionar la paleta correspondiente al tema activo
$palette = ($tema === 'dark') ? $palette_dark : $palette_light;

// 🧩 Función para asignar color único por usuario
function idToColor($id, $palette) {
    return $palette[$id % count($palette)];
}

$idgrupo = $_SESSION['idmateria'];

// 🔍 Consulta de mensajes con JOIN
$query = "
    SELECT 
        m.id, m.message, m.created_at, m.tipo, m.reply_to, 
        u.id AS user_id, u.nombre_usuario, u.nivel_usuario,
        f.foto
    FROM messages m
    JOIN usuarios u ON m.user_id = u.id
    LEFT JOIN fotousuario f ON u.id = f.id_usuario
    WHERE m.group_id = $idgrupo
    ORDER BY m.created_at ASC
";

$result = $conn->query($query);
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

$last_date = null;

// 🧾 Renderizado de mensajes
while ($row = $result->fetch_assoc()) {
    $nivel_usuario  = htmlspecialchars($row['nivel_usuario']);
    $user_id        = $row['user_id'];
    $message_id     = $row['id'];
    $reply_to       = $row['reply_to'];
    $nombre_usuario = htmlspecialchars($row['nombre_usuario']);
    $mensaje        = htmlspecialchars($row['message']);
    $tipo           = $row['tipo'];
    $timestamp      = date("h:i A", strtotime($row['created_at']));
    $current_date   = date("Y-m-d", strtotime($row['created_at']));
    $foto_perfil    = !empty($row['foto']) ? htmlspecialchars($row['foto']) : 'css/perfil.png';

    // 🎨 Color pastel dinámico
    $userColor    = idToColor($user_id, $palette);
    $styleBurbuja = 'style="background:' . $userColor . '; color:white;"';
    $styleAvatar  = 'style="border: 2px solid ' . $userColor . '; border-radius:50%;"';

    // 📅 Separador de fecha si cambia
    if ($last_date !== $current_date) {
        echo '<div class="date-separator">' . date("d M Y", strtotime($row['created_at'])) . '</div>';
        $last_date = $current_date;
    }

    // 🧱 Contenedor del mensaje
    echo '<div class="message-container-' . $nivel_usuario . '">';
    echo '<img src="' . $foto_perfil . '" alt="Perfil" class="profile-icon-' . $nivel_usuario . '" ' . $styleAvatar . '>';
    echo '<button class="reply-button" data-message-id="' . $message_id . '">Responder</button>';
    echo '<div class="message-bubble-' . $nivel_usuario . '" ' . $styleBurbuja . '>';

    // 🔁 Mostrar vista previa si es respuesta
    if ($reply_to) {
        $reply_query = "
            SELECT m.message, u.nombre_usuario, m.tipo 
            FROM messages m 
            JOIN usuarios u ON m.user_id = u.id 
            WHERE m.id = $reply_to
        ";
        $reply_result = $conn->query($reply_query);
        if ($reply_result && $reply_row = $reply_result->fetch_assoc()) {
            $reply_nombre  = htmlspecialchars($reply_row['nombre_usuario']);
            $reply_mensaje = htmlspecialchars($reply_row['message']);
            if ($reply_row['tipo'] === 'imagen') {
                echo "<div class='reply-preview'><strong>$reply_nombre:</strong> <img src='$reply_mensaje' class='reply-image' alt='Imagen'></div>";
            } else {
                echo "<div class='reply-preview'><strong>$reply_nombre:</strong> $reply_mensaje</div>";
            }
        }
    }

    // 📨 Contenido del mensaje
    if ($tipo === "texto") {
        echo "<strong>$nombre_usuario:</strong> <p id='message-text-$message_id'>$mensaje</p><p class='timestamp'>$timestamp</p>";
    } elseif ($tipo === "imagen") {
        echo "<strong>$nombre_usuario:</strong> <img class='msg-foto' src='$mensaje' alt='Imagen'><p class='timestamp'>$timestamp</p>";
    } elseif ($tipo === "archivo") {
        $file_name = basename($mensaje);
        $ext       = pathinfo($mensaje, PATHINFO_EXTENSION);
        $icon_map  = [
            'doc' => 'word.png', 'docx' => 'word.png',
            'xls' => 'excel.png', 'xlsx' => 'excel.png',
            'ppt' => 'powerpoint.png', 'pptx' => 'powerpoint.png',
            'pdf' => 'pdf.png'
        ];
        $icon = isset($icon_map[$ext]) ? 'css/' . $icon_map[$ext] : 'css/file.png';
        echo "<strong>$nombre_usuario:</strong> <a class='file' href='$mensaje' target='_blank'><img src='$icon' alt=''>$file_name</a><p class='timestamp'>$timestamp</p>";
    }

    echo '</div></div>'; // Cierre de burbuja y contenedor
}
?>
