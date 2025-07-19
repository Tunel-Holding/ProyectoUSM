<?php
include("comprobar_sesion.php");
require 'conexion.php';

// 🔐 Validación de sesión activa
if (!isset($_SESSION['idusuario'])) {
    exit(); // Usuario no autenticado
}

// 🍪 Leer el tema desde la cookie (si no existe, usar "light")
$tema = $_COOKIE['theme'] ?? 'light';

// 🎨 Paletas pastel
$palette_light = [
    "#FFB3BA",
    "#FFDFBA",
    "#FFFFBA",
    "#BAFFC9",
    "#BAE1FF",
    "#E0BBE4",
    "#D5F0DC",
    "#FCE1E4",
    "#F3EAC2",
    "#CDE7FF",
    "#FAD6FF",
    "#D7F9F1",
    "#FEE1E8",
    "#F6F0B2",
    "#F0C6B0",
    "#D9CEEA",
    "#BFFCC6",
    "#ECEAE4",
    "#FFC8DD",
    "#FAD2E1",
    "#CDEAC0",
    "#9DF9EF",
    "#A0CED9",
    "#FFD6A5",
    "#E2F0CB",
    "#FFABAB",
    "#B5EAD7",
    "#DADFF7",
    "#FFDEF0",
    "#D5E2FF"
];

$palette_dark = [
    "#CC9095",
    "#CCA98C",
    "#CCCC8A",
    "#95C7A5",
    "#95B4CC",
    "#B496B8",
    "#A8C3B2",
    "#D9AEB2",
    "#C9BD96",
    "#A3BCD4",
    "#D0A3CC",
    "#94CDAA",
    "#A4C4BC",
    "#D0ACB2",
    "#C3BE85",
    "#B89C8E",
    "#A59CC2",
    "#96CCA8",
    "#B0ACA8",
    "#CC99AA",
    "#D0A6B2",
    "#A5CBA0",
    "#6DC2B8",
    "#85A6B2",
    "#CCAA85",
    "#B5CBA0",
    "#CC898A",
    "#91C2AA",
    "#ACB4D0",
    "#9BADAF"
];

// 🧠 Seleccionar la paleta correspondiente al tema activo
$palette = ($tema === 'dark') ? $palette_dark : $palette_light;

// 🧩 Función para asignar color único por usuario
function idToColor($id, $palette)
{
    return $palette[$id % count($palette)];
}

$idgrupo = $_SESSION['idmateria'];
$current_user_id = $_SESSION['idusuario'];
$current_user_level = $_SESSION['nivel_usuario'] ?? 'usuario';

// 🔍 Consulta de mensajes con JOIN
$query = "
    SELECT 
        m.id, m.message, m.created_at, m.tipo, m.reply_to, 
        u.id AS user_id, u.nombre_usuario, u.nivel_usuario,
        f.foto
    FROM messages m
    JOIN usuarios u ON m.user_id = u.id
    LEFT JOIN fotousuario f ON u.id = f.id_usuario
    WHERE m.group_id = ?
    ORDER BY m.created_at ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idgrupo);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

$last_date = null;

// 🧾 Renderizado de mensajes
while ($row = $result->fetch_assoc()) {
    $nivel_usuario = htmlspecialchars($row['nivel_usuario']);
    $user_id = $row['user_id'];
    $message_id = $row['id'];
    $reply_to = $row['reply_to'];
    $nombre_usuario = htmlspecialchars($row['nombre_usuario']);
    $mensaje = htmlspecialchars($row['message']);
    $tipo = $row['tipo'];
    $timestamp = date("h:i A", strtotime($row['created_at']));
    $current_date = date("Y-m-d", strtotime($row['created_at']));
    $foto_perfil = !empty($row['foto']) ? htmlspecialchars($row['foto']) : 'css/perfil.png';

    // 🎨 Color azul institucional
    $userColor = '#174388';
    $styleBurbuja = 'style="background:' . $userColor . '; color:white;"';
    $styleAvatar = 'style="border: 2px solid ' . $userColor . '; border-radius:50%;"';

    // 📅 Separador de fecha si cambia
    if ($last_date !== $current_date) {
        echo '<div class="date-separator">' . date("d M Y", strtotime($row['created_at'])) . '</div>';
        $last_date = $current_date;
    }

    // 🔐 Verificar permisos de eliminación
    $can_delete = false;
    if ($user_id == $current_user_id) {
        $can_delete = true; // El usuario puede eliminar su propio mensaje
    } elseif (in_array($current_user_level, ['administrador', 'profesor'])) {
        $can_delete = true; // Administradores y profesores pueden eliminar cualquier mensaje
    }

    // 🧱 Contenedor del mensaje
    $is_current_user = ($user_id == $current_user_id);
    $container_class = $is_current_user ? 'message-container-flex current-user' : 'message-container-flex other-user';
    echo '<div class="' . $container_class . '">';

    // Botones de acción
    echo '<div class="message-actions">';
    // Botón para responder
    echo '<button class="reply-button" data-message-id="' . $message_id . '" data-username="' . $nombre_usuario . '" title="Responder">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icono-responder" viewBox="0 0 16 16">
                <path d="M6.854 4.146a.5.5 0 0 0-.708.708L8.293 7H1.5a.5.5 0 0 0 0 1h6.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3z"/>
                <path d="M13.5 8a.5.5 0 0 1-.5.5H9a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5z"/>
            </svg>
        </button>';

    // Botón para eliminar (solo si tiene permiso)
    if ($can_delete) {
        echo '<button class="delete-button" data-message-id="' . $message_id . '" onclick="deleteMessage(' . $message_id . ')" title="Eliminar">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icono-eliminar" viewBox="0 0 16 16">
                    <path d="M5.5 5.5a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0v-6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0v-6zm2 .5a.5.5 0 0 1 .5-.5.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0v-6z"/>
                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2h3.5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1H14.5a1 1 0 0 1 1 1zm-11 1v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4h-8z"/>
                </svg>
            </button>';
    }
    echo '</div>';

    // Avatar y burbuja según el usuario
    if ($is_current_user) {
        // Para el usuario actual: [botones] [burbuja] [avatar]
        echo '<div class="message-bubble-' . $nivel_usuario . '" ' . $styleBurbuja . '>';
    } else {
        // Para otros usuarios: [botones] [avatar] [burbuja]
        echo '<img src="' . $foto_perfil . '" alt="Perfil" class="profile-icon-' . $nivel_usuario . '" ' . $styleAvatar . '>';
        echo '<div class="message-bubble-' . $nivel_usuario . '" ' . $styleBurbuja . '>';
    }

    // 📨 Contenido del mensaje
    echo "<strong>$nombre_usuario:</strong> ";
    if ($tipo === "texto") {
        echo "<p id='message-text-$message_id'>$mensaje</p>";
    } elseif ($tipo === "imagen") {
        echo "<img class='msg-foto' src='$mensaje' alt='Imagen'>";
    } elseif ($tipo === "archivo") {
        $file_name = basename($mensaje);
        $ext = pathinfo($mensaje, PATHINFO_EXTENSION);
        $icon_map = [
            'doc' => 'word.png',
            'docx' => 'word.png',
            'xls' => 'excel.png',
            'xlsx' => 'excel.png',
            'ppt' => 'powerpoint.png',
            'pptx' => 'powerpoint.png',
            'pdf' => 'pdf.png'
        ];
        $icon = isset($icon_map[$ext]) ? 'css/' . $icon_map[$ext] : 'css/file.png';
        echo "<a class='file' href='$mensaje' target='_blank'><img src='$icon' alt=''>$file_name</a>";
    }

    // Mostrar la respuesta dentro de la burbuja, debajo del mensaje
    if ($reply_to) {
        $reply_query = "
            SELECT m.message, u.nombre_usuario, m.tipo 
            FROM messages m 
            JOIN usuarios u ON m.user_id = u.id 
            WHERE m.id = ?
        ";
        $reply_stmt = $conn->prepare($reply_query);
        $reply_stmt->bind_param("i", $reply_to);
        $reply_stmt->execute();
        $reply_result = $reply_stmt->get_result();

        if ($reply_result && $reply_row = $reply_result->fetch_assoc()) {
            $reply_nombre = htmlspecialchars($reply_row['nombre_usuario']);
            $reply_mensaje = htmlspecialchars($reply_row['message']);

            echo "<div class='reply-preview-inside'>";
            echo "<span class='reply-to-text'>Respondiendo a <strong>$reply_nombre</strong></span>";

            if ($reply_row['tipo'] === 'imagen') {
                echo "<div class='reply-content'><img src='$reply_mensaje' class='reply-image' alt='Imagen'></div>";
            } else {
                echo "<div class='reply-content'>$reply_mensaje</div>";
            }
            echo "</div>";
        }
        $reply_stmt->close();
    }

    echo "<p class='timestamp'>$timestamp</p>";
    echo '</div>'; // Cierre de burbuja

    // Agregar avatar después de la burbuja para el usuario actual
    if ($is_current_user) {
        echo '<img src="' . $foto_perfil . '" alt="Perfil" class="profile-icon-' . $nivel_usuario . '" ' . $styleAvatar . '>';
    }

    // --- BOTÓN DE 3 PUNTITOS Y MENÚ CONTEXTUAL ---
    // (En el bucle de mensajes, justo después de los botones de acción)
    if ($is_current_user) {
        // Para el usuario actual: [botones] [burbuja] [avatar] [3 puntitos]
        echo '<button class="menu-btn" onclick="showMenu(event, ' . $message_id . ')">⋮</button>';
        echo '<div class="message-menu" id="menu-' . $message_id . '">
            <button class="menu-item" onclick="replyToMessage(' . $message_id . ', \'' . addslashes($nombre_usuario) . '\')">Responder</button>
            <button class="menu-item" onclick="editMessage(' . $message_id . ')">Editar</button>
            <button class="menu-item" onclick="deleteMessage(' . $message_id . ')">Eliminar</button>
        </div>';
    } else {
        // Para otros usuarios: [botones] [avatar] [burbuja] [3 puntitos]
        echo '<button class="menu-btn" onclick="showMenu(event, ' . $message_id . ')">⋮</button>';
        echo '<div class="message-menu" id="menu-' . $message_id . '">
            <button class="menu-item" onclick="replyToMessage(' . $message_id . ', \'' . addslashes($nombre_usuario) . '\')">Responder</button>
        </div>';
    }
    // --- FIN HTML DEL BOTÓN Y MENÚ CONTEXTUAL ---

    echo '</div>'; // Cierre de contenedor flex
}
actualizar_actividad();
$conn->close();
?>

<style>
    .message-container-flex {
        display: flex;
        flex-direction: row;
        align-items: flex-end;
        margin-bottom: 15px;
        position: relative;
    }

    /* Mensajes del usuario actual (derecha) */
    .message-container-flex.current-user {
        justify-content: flex-end;
    }

    /* Mensajes de otros usuarios (izquierda) */
    .message-container-flex.other-user {
        justify-content: flex-start;
    }

    .message-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-self: flex-end;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
    }

    /* Posicionamiento de botones para usuario actual */
    .message-container-flex.current-user .message-actions {
        margin-right: 8px;
        order: -1;
    }

    /* Posicionamiento de botones para otros usuarios */
    .message-container-flex.other-user .message-actions {
        margin-left: 8px;
        order: 1;
    }

    .message-container-flex:hover .message-actions {
        opacity: 1;
        pointer-events: auto;
    }

    .profile-icon-alumno,
    .profile-icon-profesor,
    .profile-icon-administrador {
        width: 40px;
        height: 40px;
        object-fit: cover;
    }

    /* Margen para avatares de otros usuarios (izquierda) */
    .message-container-flex.other-user .profile-icon-alumno,
    .message-container-flex.other-user .profile-icon-profesor,
    .message-container-flex.other-user .profile-icon-administrador {
        margin-right: 10px;
    }

    /* Margen para avatar del usuario actual (derecha) */
    .message-container-flex.current-user .profile-icon-alumno,
    .message-container-flex.current-user .profile-icon-profesor,
    .message-container-flex.current-user .profile-icon-administrador {
        margin-left: 10px;
    }

    .message-bubble-alumno,
    .message-bubble-profesor,
    .message-bubble-administrador {
        border-radius: 16px;
        padding: 12px 18px;
        min-width: 80px;
        max-width: 400px;
        word-break: break-word;
        position: relative;
        margin-right: 0;
        margin-left: 10px;
    }

    .reply-button,
    .delete-button {
        background: rgba(255, 255, 255, 0.95);
        border: 1.5px solid #bbb;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        padding: 0;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .reply-button:hover {
        background: #e3f2fd;
        border-color: #2196f3;
        transform: scale(1.1);
    }

    .delete-button {
        background: rgba(255, 255, 255, 0.95);
        color: #d32f2f;
        border-color: #d32f2f;
    }

    .delete-button:hover {
        background: #ffebee;
        border-color: #c62828;
        transform: scale(1.1);
    }

    .reply-preview-inside {
        background: rgba(33, 150, 243, 0.10);
        border-left: 4px solid #2196f3;
        border-radius: 8px;
        padding: 10px 14px;
        margin-top: 10px;
        font-size: 1em;
        color: #222;
        font-weight: 500;
        box-shadow: 0 1px 6px rgba(33, 150, 243, 0.07);
    }

    .dark-mode .reply-preview-inside {
        background: rgba(33, 150, 243, 0.18);
        border-left-color: #90caf9;
        color: #e3e3e3;
    }

    .reply-to-text {
        font-size: 0.95em;
        font-style: italic;
        color: #1976d2;
        margin-bottom: 2px;
        display: block;
        opacity: 1;
    }

    .reply-content {
        opacity: 0.9;
        word-break: break-word;
    }

    .reply-image {
        max-width: 100px;
        max-height: 60px;
        border-radius: 4px;
        object-fit: cover;
    }

    .reply-button,
    .delete-button {
        display: none !important;
    }

    /* --- BOTÓN DE 3 PUNTITOS Y MENÚ CONTEXTUAL --- */
    .menu-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px 8px;
        font-size: 22px;
        color: #888;
        border-radius: 50%;
        transition: background 0.2s;
        position: relative;
        z-index: 2;
    }

    .menu-btn:hover {
        background: #e0e0e0;
    }

    .message-menu {
        display: none;
        position: absolute;
        min-width: 120px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
        z-index: 10;
        right: 0;
        top: 32px;
        padding: 6px 0;
        font-size: 15px;
    }

    body.dark-mode .message-menu {
        background: #232323;
        border: 1px solid #444;
        color: #eee;
    }

    .message-menu .menu-item {
        padding: 10px 18px;
        cursor: pointer;
        transition: background 0.2s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        font-size: 15px;
    }

    .message-menu .menu-item:hover {
        background: #f0f0f0;
    }

    body.dark-mode .message-menu .menu-item:hover {
        background: #333;
    }

    /* --- FIN ESTILOS MENÚ --- */

    @media (max-width: 768px) {

        .profile-icon-alumno,
        .profile-icon-profesor,
        .profile-icon-administrador {
            width: 32px;
            height: 32px;
        }

        .message-bubble-alumno,
        .message-bubble-profesor,
        .message-bubble-administrador {
            max-width: 90vw;
            padding: 8px 10px;
        }

        .reply-button,
        .delete-button {
            width: 30px;
            height: 30px;
            font-size: 16px;
        }

        .reply-preview-inside {
            padding: 6px 10px;
            font-size: 0.95em;
        }

        .reply-image {
            max-width: 80px;
            max-height: 50px;
        }
    }
</style>

<script>
    function deleteMessage(messageId) {
        if (confirm('¿Estás seguro de que quieres eliminar este mensaje? Esta acción no se puede deshacer.')) {
            $.post('delete_message.php', {
                message_id: messageId
            })
                .done(function (data) {
                    try {
                        const response = JSON.parse(data);
                        if (response.success) {
                            // Recargar mensajes para mostrar el cambio
                            loadMessages();
                        } else {
                            alert('Error: ' + response.error);
                        }
                    } catch (e) {
                        alert('Error al procesar la respuesta del servidor');
                    }
                })
                .fail(function (xhr, status, error) {
                    alert('Error de conexión: ' + error);
                });
        }
    }

    // --- JS para mostrar/ocultar menú contextual y conectar acciones ---
    function showMenu(event, messageId) {
        event.stopPropagation();
        // Ocultar otros menús
        document.querySelectorAll('.message-menu').forEach(m => m.style.display = 'none');
        // Mostrar el menú de este mensaje
        const menu = document.getElementById('menu-' + messageId);
        if (menu) {
            menu.style.display = 'block';
            // Posicionar el menú si es necesario
            const btn = event.currentTarget;
            const rect = btn.getBoundingClientRect();
            menu.style.top = (btn.offsetTop + btn.offsetHeight + 4) + 'px';
            menu.style.right = '0px';
        }
    }
    // Cerrar menú al hacer clic fuera
    window.addEventListener('click', function () {
        document.querySelectorAll('.message-menu').forEach(m => m.style.display = 'none');
    });
    // Opciones del menú
    function replyToMessage(messageId, userName) {
        // Simula el click en el botón de responder
        $(".reply-button[data-message-id='" + messageId + "']").trigger('click');
        document.querySelectorAll('.message-menu').forEach(m => m.style.display = 'none');
    }
    function editMessage(messageId) {
        alert('Funcionalidad de edición próximamente...');
        document.querySelectorAll('.message-menu').forEach(m => m.style.display = 'none');
    }
    // deleteMessage ya existe y funciona
</script>