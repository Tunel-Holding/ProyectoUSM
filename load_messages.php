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

$idgrupo = isset($_SESSION['idmateria']) ? $_SESSION['idmateria'] : null;
if (!$idgrupo) {
    exit();
}
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
    echo '<div class="' . htmlspecialchars($container_class, ENT_QUOTES, 'UTF-8') . '">';

    // Botones de acción
    echo '<div class="message-actions">';
    // Botón para responder
    echo '<button class="reply-button" data-message-id="' . intval($message_id) . '" data-username="' . htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8') . '" title="Responder">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icono-responder" viewBox="0 0 16 16">
                <path d="M6.854 4.146a.5.5 0 0 0-.708.708L8.293 7H1.5a.5.5 0 0 0 0 1h6.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3z"/>
                <path d="M13.5 8a.5.5 0 0 1-.5.5H9a.5.5 0 0 1 0-1h4a.5.5 0 0 1 .5.5z"/>
            </svg>
        </button>';

    // Botón para eliminar (solo si tiene permiso)
    if ($can_delete) {
        echo '<button class="delete-button" data-message-id="' . intval($message_id) . '" onclick="deleteMessage(' . intval($message_id) . ')" title="Eliminar">
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
        echo '<button class="menu-puntos-btn" onclick="mostrarMenuPuntos(this, ' . $message_id . ', true)">⋮</button>';
        echo '<div class="menu-puntos" id="menu-puntos-' . $message_id . '">
                <button class="menu-puntos-opcion" onclick="responderMensaje(' . $message_id . ')">Responder</button>
                <button class="menu-puntos-opcion" onclick="editarMensaje(' . $message_id . ')">Editar</button>
                <button class="menu-puntos-opcion" onclick="eliminarMensaje(' . $message_id . ')">Eliminar</button>
            </div>';
        // Si es archivo, agrega la clase file-bubble
        $extra_class = ($tipo === "archivo") ? ' file-bubble' : '';
        echo '<div class="message-bubble-' . $nivel_usuario . $extra_class . '" ' . $styleBurbuja . '>';
    } else {
        // Para otros usuarios: [avatar] [burbuja] [botones]
        echo '<img src="' . $foto_perfil . '" alt="Perfil" class="profile-icon-' . $nivel_usuario . '" ' . $styleAvatar . '>';
        // Si es archivo, agrega la clase file-bubble
        $extra_class = ($tipo === "archivo") ? ' file-bubble' : '';
        echo '<div class="message-bubble-' . $nivel_usuario . $extra_class . '" ' . $styleBurbuja . '>';
    }

    // 📨 Contenido del mensaje
    echo "<strong>" . htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8') . ":</strong> ";
    // Mostrar la respuesta dentro de la burbuja, arriba del mensaje principal
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
            // Un solo <span> para 'Respondiendo a' y el nombre en la misma línea
            echo "<span class='reply-to-text'>Respondiendo a <strong>$reply_nombre</strong></span>";

            if ($reply_row['tipo'] === 'imagen') {
                echo "<div class='reply-content'><img src='" . htmlspecialchars($reply_mensaje, ENT_QUOTES, 'UTF-8') . "' class='reply-image' alt='Imagen'></div>";
            } else {
                echo "<div class='reply-content'>" . htmlspecialchars($reply_mensaje, ENT_QUOTES, 'UTF-8') . "</div>";
            }
            echo "</div>";
        }
        $reply_stmt->close();
    }
    // Ahora el mensaje principal
    if ($tipo === "texto") {
        echo "<p id='message-text-" . intval($message_id) . "'>" . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . "</p>";
    } elseif ($tipo === "imagen") {
        echo "<img class='msg-foto' src='" . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . "' alt='Imagen'>";
    } elseif ($tipo === "archivo") {
        $file_name = basename($mensaje);
        $ext = strtolower(pathinfo($mensaje, PATHINFO_EXTENSION));
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
        echo "<a class='file' href='" . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . "' target='_blank'><img src='$icon' alt=''><span>" . htmlspecialchars($file_name, ENT_QUOTES, 'UTF-8') . "</span></a>";
    }

    echo "<p class='timestamp'>" . htmlspecialchars($timestamp, ENT_QUOTES, 'UTF-8') . "</p>";
    echo '</div>'; // Cierre de burbuja

    // Agregar avatar después de la burbuja para el usuario actual
    if ($is_current_user) {
        echo '<img src="' . htmlspecialchars($foto_perfil, ENT_QUOTES, 'UTF-8') . '" alt="Perfil" class="profile-icon-' . htmlspecialchars($nivel_usuario, ENT_QUOTES, 'UTF-8') . '" ' . htmlspecialchars($styleAvatar, ENT_QUOTES, 'UTF-8') . '>';
    }

    // Al final del contenedor flex, para otros usuarios, agrego el botón de 3 puntos
    if (!$is_current_user) {
        echo '<button class="menu-puntos-btn" onclick="mostrarMenuPuntos(this, ' . $message_id . ', false)">⋮</button>';
        echo '<div class="menu-puntos" id="menu-puntos-' . $message_id . '">
                <button class="menu-puntos-opcion" onclick="responderMensaje(' . $message_id . ')">Responder</button>
                <button class="menu-puntos-opcion disabled" disabled>Editar</button>
                <button class="menu-puntos-opcion disabled" disabled>Eliminar</button>
            </div>';
    }

    echo '</div>'; // Cierre de contenedor flex
}
actualizar_actividad();
$conn->close();
?>

<style>
    /* Contenedor de mensajes */
    .message-container-flex {
        display: flex;
        flex-direction: row;
        align-items: flex-end;
        margin-bottom: 15px;
        position: relative;
    }

    .message-container-flex.current-user {
        justify-content: flex-end;
    }

    .message-container-flex.other-user {
        justify-content: flex-start;
    }

    /* Acciones de mensaje */
    .message-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-self: flex-end;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
    }

    .message-container-flex.current-user .message-actions {
        margin-right: 8px;
        order: -1;
    }

    .message-container-flex.other-user .message-actions {
        margin-left: 8px;
        order: 1;
    }

    .message-container-flex:hover .message-actions {
        opacity: 1;
        pointer-events: auto;
    }

    /* Avatares */
    .profile-icon-alumno,
    .profile-icon-profesor,
    .profile-icon-administrador {
        width: 40px;
        height: 40px;
        object-fit: cover;
    }

    .message-container-flex.other-user .profile-icon-alumno,
    .message-container-flex.other-user .profile-icon-profesor,
    .message-container-flex.other-user .profile-icon-administrador {
        margin-right: 10px;
    }

    .message-container-flex.current-user .profile-icon-alumno,
    .message-container-flex.current-user .profile-icon-profesor,
    .message-container-flex.current-user .profile-icon-administrador {
        margin-left: 10px;
    }

    /* Burbujas de mensaje */
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

    /* Burbujas de archivos */
    .file-bubble {
        max-width: 260px !important;
        min-width: 0 !important;
        padding-left: 14px !important;
        padding-right: 14px !important;
        box-sizing: border-box;
    }

    .file-bubble .file {
        display: flex;
        align-items: center;
        background: #174388;
        border-radius: 8px;
        padding: 6px 10px;
        margin: 4px 0;
        font-size: 1em;
        text-decoration: none;
        color: #fff;
        transition: background 0.2s;
        box-sizing: border-box;
        max-width: 220px;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-bubble .file img {
        width: 36px;
        height: 36px;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .file-bubble .file span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
        max-width: 150px;
        vertical-align: middle;
    }

    .file-bubble .file:hover {
        background: #2559b3;
        text-decoration: underline;
    }

    /* Respuestas dentro de la burbuja */
    .reply-preview-inside {
        background: rgba(33, 150, 243, 0.10);
        border-left: 4px solid #2196f3;
        border-radius: 8px;
        padding: 10px 14px;
        margin-top: 10px;
        margin-bottom: 8px;
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
        display: inline;
        opacity: 1;
        vertical-align: middle;
    }

    .reply-to-text strong {
        font-style: normal;
        font-weight: bold;
        color: #1976d2;
        margin-left: 3px;
        display: inline;
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

    /* Menú de 3 puntitos */
    .menu-puntos-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 22px;
        color: #888;
        padding: 4px 8px;
        border-radius: 50%;
        transition: background 0.2s;
        position: relative;
        z-index: 2;
        display: inline-block;
    }

    .menu-puntos-btn:hover {
        background: #e0e0e0;
    }

    .menu-puntos {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        min-width: 120px;
        z-index: 10;
        flex-direction: column;
        padding: 6px 0;
        margin-top: 4px;
    }

    .menu-puntos.show {
        display: flex;
    }

    .menu-puntos-opcion {
        padding: 10px 18px;
        cursor: pointer;
        background: none;
        border: none;
        text-align: left;
        font-size: 15px;
        color: #213555;
        transition: background 0.2s;
    }

    .menu-puntos-opcion:hover {
        background: #f4f8fb;
    }

    .menu-puntos-opcion.disabled {
        color: #aaa;
        cursor: not-allowed;
        background: none;
    }

    body.dark-mode .menu-puntos {
        background: #232323;
        border: 1px solid #444;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.32);
    }

    body.dark-mode .menu-puntos-opcion {
        color: #e0e0e0;
    }

    body.dark-mode .menu-puntos-opcion:hover {
        background: #333;
    }

    .message-container-flex.current-user .menu-puntos {
        left:800px;
        right: auto;
    }

    .reply-button,
    .delete-button {
        display: none !important;
    }

    /* Mensajes del usuario actual alineados a la derecha */
    .message-container-flex.current-user .message-bubble-alumno,
    .message-container-flex.current-user .message-bubble-profesor,
    .message-container-flex.current-user .message-bubble-administrador {
        text-align: right;
    }

    /* Mensajes de otros usuarios alineados a la izquierda */
    .message-container-flex.other-user .message-bubble-alumno,
    .message-container-flex.other-user .message-bubble-profesor,
    .message-container-flex.other-user .message-bubble-administrador {
        text-align: left;
    }

    /* Previsualización de respuesta sigue la alineación del mensaje */
    .message-container-flex.current-user .reply-preview-inside {
        text-align: right;
    }

    .message-container-flex.other-user .reply-preview-inside {
        text-align: left;
    }

    /* SOLO el texto del mensaje principal a la derecha si es tuyo */
    .message-container-flex.current-user .message-bubble-usuario p,
    .message-container-flex.current-user .message-bubble-alumno p,
    .message-container-flex.current-user .message-bubble-profesor p,
    .message-container-flex.current-user .message-bubble-administrador p {
        text-align: right !important;
        margin-right: 0 !important;

    }

    .reply-preview-inside .reply-to-text {
        text-align: left;
        display: block;
    }

    .reply-preview-inside .reply-content {
        text-align: left;
        display: block;
    }

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

        .file-bubble {
            max-width: 80vw !important;
        }

        .file-bubble .file img {
            width: 28px;
            height: 28px;
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

    function mostrarMenuPuntos(btn, messageId, esPropio) {
        // Cerrar otros menús
        document.querySelectorAll('.menu-puntos').forEach(m => m.classList.remove('show'));
        // Mostrar el menú de este mensaje
        const menu = document.getElementById('menu-puntos-' + messageId);
        menu.classList.toggle('show');
        // Cerrar al hacer click fuera del botón y del menú
        document.addEventListener('mousedown', function handler(e) {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
                document.removeEventListener('mousedown', handler);
            }
        });
    }
    function responderMensaje(id) {
        // Simula click en el botón original (que está oculto)
        document.querySelector('.reply-button[data-message-id="' + id + '"]').click();
    }
    function editarMensaje(id) {
        // Aquí puedes implementar la lógica de edición
        alert('Funcionalidad de edición próximamente...');
    }
    function eliminarMensaje(id) {
        // Simula click en el botón original (que está oculto)
        document.querySelector('.delete-button[data-message-id="' + id + '"]').click();
    }
</script>