<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_PROFESOR);

include 'comprobar_sesion.php';
require 'conexion.php';
actualizar_actividad();

// Obtener el nombre y sección de la materia
if (!isset($_SESSION['idmateria']) || empty($_SESSION['idmateria'])) {
    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';
    echo "Error: no se ha seleccionado una materia";
    exit();
}
$id_materia = $_SESSION['idmateria'];
$stmt = $conn->prepare("SELECT nombre, seccion FROM materias WHERE id = ?");
$stmt->bind_param("i", $id_materia);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $_SESSION['nombremateria'] = $row['nombre'];
    $_SESSION['seccion_materia'] = $row['seccion'];
} else {
    $_SESSION['nombremateria'] = "Materia no encontrada";
    $_SESSION['seccion_materia'] = "";
}
$stmt->close();

if (!isset($_SESSION['idusuario'])) {
    header("Location: login.php");
    exit();
}

// Enviar mensaje de texto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    actualizar_actividad();
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? (int) $_POST['reply_to'] : 0;
    $message = trim($_POST['message']);

    if (strlen($message) > 0 && strlen($message) <= 250) {
        if (preg_match('/^[\p{L}\p{N}\s\.,!?;:()@#$%*+\-=_<>\/\\\\]+$/u', $message)) {
            $stmt = $conn->prepare("INSERT INTO messages (user_id, message, group_id, tipo, reply_to) VALUES (?, ?, ?, 'texto', ?)");
            $stmt->bind_param("isii", $user_id, $message, $group_id, $reply_to);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Error al guardar el mensaje']);
            }
            $stmt->close();
        } else {
            echo json_encode(['error' => 'El mensaje contiene caracteres no permitidos']);
        }
    } else {
        echo json_encode(['error' => 'El mensaje debe tener entre 1 y 250 caracteres']);
    }
    exit();
}

// Enviar imagen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    actualizar_actividad();
    $image = $_FILES['image'];
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? (int) $_POST['reply_to'] : 0;
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    if (!in_array($image['type'], $allowed_types)) {
        echo json_encode(['error' => 'Tipo de imagen no permitido']);
        exit();
    }
    if ($image['size'] > $max_size) {
        echo json_encode(['error' => 'La imagen es demasiado grande (máximo 5MB)']);
        exit();
    }
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $original_filename = basename($image["name"]);
    $target_file = $target_dir . $original_filename;
    if (move_uploaded_file($image["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, message, group_id, tipo, reply_to) VALUES (?, ?, ?, 'imagen', ?)");
        $stmt->bind_param("isii", $user_id, $target_file, $group_id, $reply_to);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'file' => $target_file]);
        } else {
            echo json_encode(['error' => 'Error al guardar la imagen']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error al subir la imagen']);
    }
    exit();
}

// Enviar archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    actualizar_actividad();
    $file = $_FILES['file'];
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? (int) $_POST['reply_to'] : 0;
    $allowed_types = [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/pdf'
    ];
    $max_size = 50 * 1024 * 1024; // 50MB
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['error' => 'Tipo de archivo no permitido']);
        exit();
    }
    if ($file['size'] > $max_size) {
        echo json_encode(['error' => 'El archivo es demasiado grande (máximo 50MB)']);
        exit();
    }
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $original_filename = basename($file["name"]);
    $target_file = $target_dir . $original_filename;
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, message, group_id, tipo, reply_to) VALUES (?, ?, ?, 'archivo', ?)");
        $stmt->bind_param("isii", $user_id, $target_file, $group_id, $reply_to);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'file' => $target_file]);
        } else {
            echo json_encode(['error' => 'Error al guardar el archivo']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error al subir el archivo']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Chat - UniHub</title>
    <script src="js/control_inactividad.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap');
        /* Copiar aquí los estilos relevantes de chat.php para unificar la apariencia */
    </style>
</head>
<body>
    <div class="cabecera">
        <button type="button" id="logoButton">
            <img src="css/menu.png" alt="Menú" class="logo-menu">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>
    <?php include 'menu_profesor.php'; ?>
    <div class="chat-dashboard-area">
        <div class="chat-dashboard-main">
            <div class="chat-dashboard-messages" id="chat-box">
                <!-- Los mensajes se cargan aquí dinámicamente -->
            </div>
            <div class="chat-entry-wrapper">
                <div id="reply-preview" class="reply-preview-blue" style="display: none;">
                    <div class="reply-content-blue">
                        <div class="reply-user-blue" id="reply-to-user"></div>
                        <div class="reply-message-blue" id="reply-message"></div>
                    </div>
                    <button id="cancel-reply" class="close-reply-blue" title="Cancelar respuesta">&times;</button>
                </div>
                <div class="chat-dashboard-entry">
                    <div class="upload-wrapper">
                        <button id="upload-button" class="button" title="Subir archivo o imagen">
                            <img src="css/plus-pequeno.png" alt="Upload">
                        </button>
                        <div id="upload-menu">
                            <div class="upload-option" id="upload-image">Subir Imagen</div>
                            <div class="upload-option" id="upload-file">Subir Archivo</div>
                        </div>
                    </div>
                    <input type="text" id="message" placeholder="Escribe un mensaje..." maxlength="250" autocomplete="off" />
                    <button id="send-button" class="button" title="Enviar mensaje">
                        <img src="css/enviar-mensaje.png" alt="Send">
                    </button>
                    <input type="file" id="imageInput" accept="image/*" style="display: none;">
                    <input type="file" id="fileInput" accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf" style="display: none;">
                    <a id="video-call-button" class="button llamada" href="videollamada_profesor.php" title="Videollamada">
                        <img src="css/icons/meet.svg" alt="Videollamada">
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div id="upload-progress-modal">
        <div class="modal-content">
            <h2>Subiendo archivo...</h2>
            <div class="progress-bar-container">
                <div id="progress-bar" class="progress-bar"></div>
            </div>
            <p id="progress-text">0%</p>
        </div>
    </div>
    <!-- Modal para vista ampliada de imagen -->
    <div id="image-modal">
        <button class="close-modal" title="Cerrar">&times;</button>
        <img src="" alt="Imagen ampliada" class="modal-img">
    </div>
    <!-- Modal para editar mensaje -->
    <div id="edit-modal">
        <div class="edit-modal-content">
            <button class="edit-modal-close" id="edit-modal-close" title="Cerrar">&times;</button>
            <div class="edit-modal-bubble" id="edit-modal-original"></div>
            <div class="edit-modal-flex-row">
                <div class="edit-modal-textarea-container" style="flex:1;display:flex;align-items:flex-end;">
                    <textarea class="edit-modal-input" id="edit-modal-input" maxlength="250" placeholder="Escribe el nuevo mensaje..."></textarea>
                </div>
                <div class="edit-modal-btn-container" style="display:flex;align-items:flex-end;">
                    <button class="edit-modal-save" id="edit-modal-save" title="Guardar edición">&#10003;</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Configuración global igual que en chat.php
        const CONFIG = {
            maxMessageLength: 250,
            maxImageSize: 5 * 1024 * 1024, // 5MB
            maxFileSize: 50 * 1024 * 1024, // 50MB
            allowedImageTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            allowedFileTypes: [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/pdf'
            ]
        };

        // Gestión de tema (modo oscuro)
        function initTheme() {
            const theme = localStorage.getItem('theme') || 'light';
            const switchElement = document.getElementById('switchtema');
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
                if (switchElement) switchElement.checked = true;
            } else {
                document.body.classList.remove('dark-mode');
                if (switchElement) switchElement.checked = false;
            }
            document.cookie = "theme=" + theme + "; path=/";
        }
        function setTheme(theme) {
            localStorage.setItem('theme', theme);
            document.cookie = "theme=" + theme + "; path=/";
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
            fetch('set_theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + theme
            }).catch(error => console.error('Error setting theme:', error));
        }
        document.addEventListener('DOMContentLoaded', function () {
            initTheme();
            loadMessages();
            setupEventListeners();
            setupNavigation();
        });
        function setupNavigation() {
            // Puedes personalizar la navegación si lo deseas
        }
        function setupEventListeners() {
            const themeSwitch = document.getElementById('switchtema');
            if (themeSwitch) {
                themeSwitch.addEventListener('change', function () {
                    setTheme(this.checked ? 'dark' : 'light');
                });
            }
            setupMessageHandlers();
            setupFileHandlers();
        }
        function setupMessageHandlers() {
            const sendButton = document.getElementById('send-button');
            const messageInput = document.getElementById('message');
            const cancelReplyButton = document.getElementById('cancel-reply');
            sendButton.addEventListener('click', function () {
                sendMessage();
            });
            messageInput.addEventListener('keypress', function (e) {
                if (e.which === 13) {
                    sendMessage();
                    return false;
                }
            });
            cancelReplyButton.addEventListener('click', function () {
                hideReplyPreview();
            });
            $(document).on('click', '.reply-button', function () {
                const messageId = $(this).data('message-id');
                const userName = $(this).data('username');
                const messageType = $(this).data('messagetype');
                let messageContent = '';
                let fileName = '';
                if (messageType === 'texto') {
                    messageContent = $('#message-text-' + messageId).text();
                } else if (messageType === 'imagen') {
                    messageContent = $('#message-img-' + messageId).attr('src');
                } else if (messageType === 'archivo') {
                    const fileElem = $('#message-file-' + messageId);
                    messageContent = fileElem.attr('href');
                    fileName = fileElem.find('span').text();
                }
                showReplyPreview(userName, messageContent, messageId, messageType, fileName);
            });
        }
        function setupFileHandlers() {
            const uploadButton = document.getElementById('upload-button');
            const uploadMenu = document.getElementById('upload-menu');
            const uploadImage = document.getElementById('upload-image');
            const uploadFile = document.getElementById('upload-file');
            const imageInput = document.getElementById('imageInput');
            const fileInput = document.getElementById('fileInput');
            uploadButton.addEventListener('click', function (event) {
                uploadMenu.classList.toggle('show');
                event.stopPropagation();
            });
            document.addEventListener('click', function (event) {
                if (!uploadMenu.contains(event.target) && !uploadButton.contains(event.target)) {
                    uploadMenu.classList.remove('show');
                }
            });
            uploadImage.addEventListener('click', function () {
                imageInput.click();
            });
            uploadFile.addEventListener('click', function () {
                fileInput.click();
            });
            imageInput.addEventListener('change', function (e) {
                handleImageUpload(e);
            });
            fileInput.addEventListener('change', function (e) {
                handleFileUpload(e);
            });
        }
        function sendMessage() {
            const message = $('#message').val().trim();
            const replyTo = $('#reply-preview').data('reply-to') || 0;
            if (message.length === 0) {
                showError('El mensaje no puede estar vacío');
                return;
            }
            if (message.length > CONFIG.maxMessageLength) {
                showError('El mensaje es demasiado largo');
                return;
            }
            $.post('chat_profesor.php', {
                message: message,
                reply_to: replyTo
            })
                .done(function (data) {
                    try {
                        const response = JSON.parse(data);
                        if (response.success) {
                            $('#message').val('');
                            hideReplyPreview();
                            loadMessages();
                            setTimeout(() => {
                                forceScrollToBottom();
                            }, 100);
                        } else {
                            showError(response.error || 'Error al enviar el mensaje');
                        }
                    } catch (e) {
                        showError('Error al procesar la respuesta del servidor');
                    }
                })
                .fail(function (xhr, status, error) {
                    showError('Error de conexión: ' + error);
                });
        }
        function handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!CONFIG.allowedImageTypes.includes(file.type)) {
                showError('Tipo de imagen no permitido');
                return;
            }
            if (file.size > CONFIG.maxImageSize) {
                showError('La imagen es demasiado grande (máximo 5MB)');
                return;
            }
            uploadFileWithProgress(file, 'image');
        }
        function handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (!CONFIG.allowedFileTypes.includes(file.type)) {
                showError('Tipo de archivo no permitido');
                return;
            }
            if (file.size > CONFIG.maxFileSize) {
                showError('El archivo es demasiado grande (máximo 50MB), file.size: ' + file.size + ' bytes');
                return;
            }
            uploadFileWithProgress(file, 'file');
        }
        function uploadFileWithProgress(file, type) {
            const formData = new FormData();
            formData.append(type, file);
            formData.append('reply_to', $('#reply-preview').data('reply-to') || 0);
            const modal = document.getElementById('upload-progress-modal');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            modal.style.display = 'flex';
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'chat_profesor.php', true);
            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressText.textContent = Math.round(percentComplete) + '%';
                }
            };
            xhr.onload = function () {
                modal.style.display = 'none';
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            hideReplyPreview();
                            loadMessages();
                            document.getElementById('upload-menu').classList.remove('show');
                            setTimeout(() => {
                                forceScrollToBottom();
                            }, 100);
                        } else {
                            showError(response.error || 'Error al subir el archivo');
                        }
                    } catch (e) {
                        showError('Error al procesar la respuesta del servidor: ' + xhr.responseText);
                    }
                } else {
                    showError('Error en la subida: ' + xhr.statusText);
                }
            };
            xhr.onerror = function () {
                modal.style.display = 'none';
                showError('Error de red al intentar subir el archivo.');
            };
            xhr.send(formData);
        }
        let menuPuntosAbierto = null;
        function mostrarMenuPuntos(btn, messageId, esPropio) {
            document.querySelectorAll('.menu-puntos').forEach(m => m.classList.remove('show'));
            const menu = document.getElementById('menu-puntos-' + messageId);
            menu.classList.toggle('show');
            if (menu.classList.contains('show')) {
                menuPuntosAbierto = messageId;
            } else {
                menuPuntosAbierto = null;
            }
            document.addEventListener('mousedown', function handler(e) {
                if (!btn.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.remove('show');
                    menuPuntosAbierto = null;
                    document.removeEventListener('mousedown', handler);
                }
            });
        }
        function loadMessages() {
            $.get('load_messages.php')
                .done(function (data) {
                    $('#chat-box').html(data);
                    autoScroll();
                    if (menuPuntosAbierto) {
                        const btn = document.querySelector('.menu-puntos-btn[onclick*="' + menuPuntosAbierto + '"]');
                        if (btn) {
                            setTimeout(() => btn.click(), 50);
                        }
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Error loading messages:', error);
                });
        }
        function deleteMessage(messageId) {
            if (confirm('¿Estás seguro de que quieres eliminar este mensaje? Esta acción no se puede deshacer.')) {
                $.post('delete_message.php', {
                    message_id: messageId
                })
                    .done(function (data) {
                        try {
                            const response = JSON.parse(data);
                            if (response.success) {
                                loadMessages();
                            } else {
                                showError(response.error || 'Error al eliminar el mensaje');
                            }
                        } catch (e) {
                            showError('Error al procesar la respuesta del servidor');
                        }
                    })
                    .fail(function (xhr, status, error) {
                        showError('Error de conexión: ' + error);
                    });
            }
        }
        let isUserScrolling = false;
        let userScrolledUp = false;
        $('#chat-box').on('scroll', function () {
            isUserScrolling = true;
            if ($(this).scrollTop() < $(this)[0].scrollHeight - $(this).innerHeight()) {
                userScrolledUp = true;
            } else {
                userScrolledUp = false;
            }
        });
        function autoScroll() {
            if (!isUserScrolling && !userScrolledUp) {
                $('#chat-box').animate({
                    scrollTop: $('#chat-box')[0].scrollHeight
                }, 'normal');
            }
        }
        function forceScrollToBottom() {
            $('.chat-dashboard-messages').animate({
                scrollTop: $('.chat-dashboard-messages')[0].scrollHeight
            }, 'fast');
        }
        setInterval(loadMessages, 2000);
        function showReplyPreview(userName, messageContent, messageId, messageType, fileName) {
            $('#reply-to-user').text('Respondiendo a: ' + userName);
            let html = '';
            if (messageType === 'texto') {
                html = messageContent;
            } else if (messageType === 'imagen') {
                html = '<span style="color:#aaa;">Imagen</span><br><img src="' + messageContent + '" class="reply-img-thumb">';
            } else if (messageType === 'archivo') {
                html = '<span style="color:#aaa;">Archivo</span><br><span>' + (fileName || messageContent.split('/').pop()) + '</span>';
            } else {
                html = messageContent;
            }
            $('#reply-message').html(html);
            $('#reply-preview').show().data('reply-to', messageId);
            $('#message').focus();
        }
        function hideReplyPreview() {
            $('#reply-preview').hide().data('reply-to', null);
        }
        function showError(message) {
            alert(message);
        }
        const messageInput = document.getElementById('message');
        const sendButton = document.getElementById('send-button');
        function toggleSendButton() {
            const isEmpty = messageInput.value.trim().length === 0;
            sendButton.disabled = isEmpty;
            if (isEmpty) {
                sendButton.style.opacity = '0.5';
                sendButton.style.cursor = 'not-allowed';
            } else {
                sendButton.style.opacity = '';
                sendButton.style.cursor = '';
            }
        }
        messageInput.addEventListener('input', toggleSendButton);
        document.addEventListener('DOMContentLoaded', toggleSendButton);
        function forceDisableSendButton() {
            setTimeout(toggleSendButton, 10);
        }
        document.getElementById('cancel-reply').addEventListener('click', forceDisableSendButton);
        messageInput.addEventListener('change', toggleSendButton);
        messageInput.addEventListener('keyup', toggleSendButton);
        function setupImageModal() {
            const modal = document.getElementById('image-modal');
            const modalImg = modal.querySelector('.modal-img');
            const closeBtn = modal.querySelector('.close-modal');
            document.getElementById('chat-box').addEventListener('click', function (e) {
                if (e.target.tagName === 'IMG' && e.target.classList.contains('msg-foto')) {
                    modalImg.src = e.target.src;
                    modal.classList.add('show');
                }
            });
            closeBtn.addEventListener('click', function () {
                modal.classList.remove('show');
                modalImg.src = '';
            });
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    modalImg.src = '';
                }
            });
            document.addEventListener('keydown', function (e) {
                if (modal.classList.contains('show') && e.key === 'Escape') {
                    modal.classList.remove('show');
                    modalImg.src = '';
                }
            });
        }
        document.addEventListener('DOMContentLoaded', setupImageModal);
        function setupEditModal() {
            const modal = document.getElementById('edit-modal');
            const original = document.getElementById('edit-modal-original');
            const input = document.getElementById('edit-modal-input');
            const saveBtn = document.getElementById('edit-modal-save');
            const closeBtn = document.getElementById('edit-modal-close');
            let editingId = null;
            $(document).on('click', '.menu-puntos-opcion', function () {
                if ($(this).text().trim().toLowerCase() === 'editar') {
                    const messageId = $(this).closest('.menu-puntos').attr('id').replace('menu-puntos-', '');
                    const messageElem = document.getElementById('message-text-' + messageId);
                    if (!messageElem) return;
                    const originalText = messageElem.textContent;
                    original.textContent = originalText;
                    input.value = originalText;
                    input.placeholder = 'Escribe el nuevo mensaje...';
                    editingId = messageId;
                    modal.classList.add('show');
                    input.focus();
                    input.style.height = 'auto';
                    input.style.height = Math.min(input.scrollHeight, 160) + 'px';
                }
            });
            saveBtn.addEventListener('click', function () {
                const nuevoTexto = input.value.trim();
                if (nuevoTexto.length === 0) {
                    alert('El mensaje no puede estar vacío');
                    return;
                }
                fetch('editar_mensaje.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${editingId}&nuevo_texto=${encodeURIComponent(nuevoTexto)}`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            modal.classList.remove('show');
                            editingId = null;
                            loadMessages();
                        } else {
                            alert(data.error || 'Error al editar el mensaje');
                        }
                    })
                    .catch(() => alert('Error de conexión al editar mensaje'));
            });
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    editingId = null;
                }
            });
            closeBtn.addEventListener('click', function () {
                modal.classList.remove('show');
                editingId = null;
            });
            document.addEventListener('keydown', function (e) {
                if (modal.classList.contains('show') && e.key === 'Escape') {
                    modal.classList.remove('show');
                    editingId = null;
                }
            });
        }
        document.addEventListener('DOMContentLoaded', setupEditModal);
        document.addEventListener('DOMContentLoaded', function () {
            const editInput = document.getElementById('edit-modal-input');
            if (editInput) {
                editInput.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 160) + 'px';
                });
                editInput.style.height = 'auto';
                editInput.style.height = Math.min(editInput.scrollHeight, 160) + 'px';
            }
        });
        // El botón de videollamada sigue funcionando porque es un <a> con href a videollamada_profesor.php
    </script>
</body>
</html>