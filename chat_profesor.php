<?php
session_start();
require 'conexion.php';

// Obtener el nombre y sección de la materia
$id_materia = $_SESSION['idmateria']; // Usar el id de materia de la sesión
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
?>

<?php
require 'conexion.php';

if (!isset($_SESSION['idusuario'])) {
    header("Location: login.php");
    exit();
}

// Enviar mensaje
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    echo 'POST recibido';
    $message = $_POST['message'];
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? $_POST['reply_to'] : 0; // Asignar 0 si no se proporciona reply_to

    $stmt = $conn->prepare("INSERT INTO messages (user_id, message, group_id, tipo, reply_to) VALUES (?, ?, ?, 'texto', ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("isii", $user_id, $message, $group_id, $reply_to);
    if ($stmt->execute() === false) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }
    $stmt->close();
    exit(); // Salir después de insertar el mensaje
}

// Enviar imagen
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? $_POST['reply_to'] : 0;

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($image["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($image["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO messages (user_id, message, group_id, tipo, reply_to) VALUES (?, ?, ?, 'imagen', ?)");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("isii", $user_id, $target_file, $group_id, $reply_to);
            if ($stmt->execute() === false) {
                die('Execute failed: ' . htmlspecialchars($stmt->error));
            }
            $stmt->close();
        } else {
            die('Error al subir la imagen.');
        }
    } else {
        die('El archivo no es una imagen.');
    }
    exit();
}

// Enviar archivo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? $_POST['reply_to'] : 0;

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file["name"]);
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $validTypes = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf'];

    if (in_array($fileType, $validTypes)) {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO messages (user_id, message, group_id, tipo, reply_to) VALUES (?, ?, ?, 'archivo', ?)");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("isii", $user_id, $target_file, $group_id, $reply_to);
            if ($stmt->execute() === false) {
                die('Execute failed: ' . htmlspecialchars($stmt->error));
            }
            $stmt->close();
        } else {
            die('Error al subir el archivo.');
        }
    } else {
        die('Tipo de archivo no permitido.');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="stylesheet" href="css/chat.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .message-container {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .profile-icon-profesor,
        .profile-icon-usuario {
            width: 50px;
            height: 50px;
            border-radius: 50%;

            align-self: flex-start;
            margin-top: 10px;
        }

        .profile-icon-profesor {
            border: 2px solid rgb(0, 208, 255);
            /* Ejemplo de borde rojo para profesor */
            margin-left: 80px;
            margin-right: 20px;
        }

        .profile-icon-usuario {
            border: 2px solid #0000ff;
            /* Ejemplo de borde azul para alumno */
            margin-right: 80px;
            margin-left: 20px;
        }

        .message-content {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <div class="contenedorentrante3">
        <img src="css\logo.png">
    </div>
    <div class="cabecera cabecera-chat">

        <button type="button" id="logoButton">
            <img src="css/logo.png" alt="Logo">
        </button>

        <div class="nombremateria">
            <h1><?php echo $_SESSION['nombremateria'] ?></h1>
        </div>

        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UH</p>
        </div>
    </div>

    <?php include 'menu_profesor.php'; ?>

    <div class="divchat">
        <div class="cont-chat">
            <div id="chat-box">
                <!-- Aquí se cargarán los mensajes mediante AJAX -->
            </div>
        </div>
    </div>
    <div class="message-entry">
        <div id="reply-preview" style="display: none;">
            <div id="reply-message"></div>
            <button id="cancel-reply" class="buttoncancel">Cancelar</button>
        </div>
        <div class="button-container">
            <button id="upload-button" class="button">
                <img src="css/plus-pequeno.png" alt="Upload" width="40" height="40">
            </button>
            <input type="text" id="message" placeholder="Escribe un mensaje..." />
            <button id="send-button" class="button">
                <img src="css/enviar-mensaje.png" alt="Send" width="40" height="40">
            </button>
            <a id="video-call-button" class="button llamada" href="videollamada_profesor.php">
                <ion-icon name="videocam-outline"></ion-icon>
            </a>
            <input type="file" id="imageInput" accept="image/*">
            <input type="file" id="fileInput" accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf">
        </div>
    </div>

    <div id="upload-menu">
        <div class="upload-option" id="upload-image">Subir Imagen</div>
        <div class="upload-option" id="upload-file">Subir Archivo</div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
        const contenedor = document.getElementById('contenedor');
        const botonIzquierdo = document.getElementById('boton-izquierdo');
        const botonDerecho = document.getElementById('boton-derecho');
        botonIzquierdo.addEventListener('click', () => {
            contenedor.scrollBy({
                left: -94,
                behavior: 'smooth'
            });
        });
        botonDerecho.addEventListener('click', () => {
            contenedor.scrollBy({
                left: 94,
                behavior: 'smooth'
            });
        });

        document.getElementById('logoButton').addEventListener("click", () => {
            document.getElementById('menu').classList.toggle('toggle');
            event.stopPropagation();
        });
        document.addEventListener('click', function (event) {
            if (!container.contains(event.target) && container.classList.contains('toggle')) {
                container.classList.remove('toggle');
            }
        });
        document.addEventListener('click', function (event) {
            var div = document.getElementById('menu');
            if (!div.contains(event.target)) {
                div.classList.remove('toggle');
            }
        });
        document.getElementById('switchtema').addEventListener('change', function () {
            const theme = this.checked ? 'dark' : 'light';

            // 🌗 Aplicar clase visual
            document.body.classList.toggle('dark-mode', theme === 'dark');

            // 💾 Guardar en localStorage
            localStorage.setItem('theme', theme);

            // 🍪 Guardar en cookie para que PHP lo detecte
            document.cookie = "theme=" + theme + "; path=/";

            // 🔁 Enviar al backend si lo usas también vía POST
            fetch('set_theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + theme
            });

            // 🔄 Recargar mensajes (solo si usas AJAX)
            // loadMessages(); // o location.reload(); si es HTML estático
        });

        // Aplicar la preferencia guardada del usuario al cargar la página
        window.addEventListener('load', function () {
            const theme = localStorage.getItem('theme') || 'light';

            // 🧁 Aplicar visualmente el modo al cuerpo
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
                document.getElementById('switchtema').checked = true;
            } else {
                document.body.classList.remove('dark-mode');
                document.getElementById('switchtema').checked = false;
            }

            // 🍪 Guardar el tema en una cookie para que PHP lo lea
            document.cookie = "theme=" + theme + "; path=/";

            // 🔁 Enviar también el tema al backend si usas POST (opcional)
            fetch('set_theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'theme=' + theme
            });
        });

        document.getElementById('switchtema').addEventListener('change', function () {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');

                fetch('set_theme.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'theme=dark'
                });

            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');

                fetch('set_theme.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'theme=light'
                });
            }
        });






        $(document).ready(function () {
            // Cargar mensajes al cargar la página
            loadMessages();

            // Enviar mensaje
            function sendMessage() {
                var message = $('#message').val();
                var replyTo = $('#reply-preview').data('reply-to') || 0; // Asignar 0 si no se proporciona reply_to
                console.log('Sending message:', message); // Depuración
                console.log('Reply to:', replyTo); // Depuración
                if (message.trim() !== '') {
                    $.post('chat.php', {
                        message: message,
                        reply_to: replyTo
                    }, function (data) {
                        console.log('Server response:', data); // Depuración
                        $('#message').val(''); // Limpiar el campo de mensaje
                        $('#reply-preview').hide(); // Ocultar la vista previa de la respuesta
                        $('#reply-preview').data('reply-to', null); // Limpiar el ID de respuesta
                        loadMessages(); // Cargar mensajes después de enviar
                    }).fail(function (xhr, status, error) {
                        console.error('Error sending message:', xhr.responseText); // Depuración
                        alert('Error al enviar el mensaje: ' + xhr.responseText);
                    });
                }
            }

            $('#send-button').on('click', sendMessage);

            $('#message').on('keypress', function (e) {
                if (e.which === 13) { // Enter key pressed
                    sendMessage();
                    return false; // Prevent the default action (form submission)
                }
            });

            $('#cancel-reply').on('click', function () {
                $('#reply-preview').hide();
                $('#reply-preview').data('reply-to', null);
            });

            // Función para cargar mensajes
            function loadMessages() {
                console.log('Loading messages'); // Depuración
                $.get('load_messages.php', function (data) {
                    console.log('Messages loaded'); // Depuración
                    $('#chat-box').html(data);
                    autoScroll(); // Llamar a la función de desplazamiento automático
                }).fail(function (xhr, status, error) {
                    console.error('Error loading messages:', xhr.responseText); // Depuración
                });
            }

            // Actualizar mensajes cada 2 segundos
            setInterval(loadMessages, 2000);

            // Manejar el evento de clic del botón de respuesta
            $(document).on('click', '.reply-button', function () {
                console.log('Reply button clicked'); // Depuración
                var messageId = $(this).data('message-id');
                console.log('Message ID:', messageId); // Depuración
                var messageContent = $('#message-text-' + messageId).text();
                console.log('Message Content:', messageContent); // Depuración
                var messageType = $(this).siblings('.message-bubble-usuario, .message-bubble-profesor').find('img').not('.reply-preview img').length ? 'imagen' : 'texto';
                console.log('Message Type:', messageType); // Depuración
                var userName = $(this).siblings('.message-bubble-usuario, .message-bubble-profesor').find('strong').first().text();
                console.log('User Name:', userName); // Depuración

                // Limpiar el contenido anterior del reply-preview
                $('#reply-message').empty();

                // Verificar si el mensaje al que se está respondiendo es una respuesta a otro mensaje
                var originalMessage = $(this).closest('.message').find('.original-message');
                if (originalMessage.length) {
                    var originalMessageType = originalMessage.find('img').not('.reply-preview img').length ? 'imagen' : 'texto';
                    if (originalMessageType === 'imagen') {
                        var originalImageUrl = originalMessage.find('img').not('.reply-preview img').attr('src');
                        messageContent = '<img src="' + originalImageUrl + '" alt="Imagen" style="max-width: 65px; max-height: auto; border-radius: 5px; margin-top: 5px;">';
                    } else {
                        messageContent = originalMessage.find('p').last().text();
                    }
                }

                if (messageType === 'imagen') {
                    var imageUrl = $(this).siblings('.message-bubble-usuario, .message-bubble-profesor').find('img').not('.reply-preview img').last().attr('src');
                    $('#reply-message').html('<strong>' + userName + '</strong><br>' + messageContent + '<br><img src="' + imageUrl + '" alt="Imagen" style="max-width: 65px; max-height: auto; border-radius: 5px; margin-top: 5px;">');
                } else {
                    $('#reply-message').html('<strong>' + userName + '</strong><br>' + messageContent);
                }

                $('#reply-preview').show();
                $('#reply-preview').data('reply-to', messageId);
                $('#message').focus(); // Enfocar el campo de mensaje
            });

            document.getElementById('upload-button').addEventListener('click', function (event) {
                var uploadMenu = document.getElementById('upload-menu');
                if (uploadMenu.style.display === 'block') {
                    uploadMenu.style.display = 'none';
                } else {
                    uploadMenu.style.display = 'block';
                }
                event.stopPropagation();
            });

            document.addEventListener('click', function (event) {
                var uploadMenu = document.getElementById('upload-menu');
                if (!uploadMenu.contains(event.target) && !document.getElementById('upload-button').contains(event.target)) {
                    uploadMenu.style.display = 'none';
                }
            });

            document.getElementById('upload-image').addEventListener('click', function () {
                document.getElementById('imageInput').click();
            });

            document.getElementById('imageInput').addEventListener('change', function () {
                var formData = new FormData();
                formData.append('image', this.files[0]);
                formData.append('reply_to', $('#reply-preview').data('reply-to') || 0);

                fetch('chat.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                    .then(data => {
                        console.log('Server response:', data); // Depuración
                        $('#reply-preview').hide();
                        $('#reply-preview').data('reply-to', null);
                        loadMessages();
                    }).catch(error => {
                        console.error('Error uploading image:', error); // Depuración
                        alert('Error al subir la imagen');
                    });
            });

            document.getElementById('upload-file').addEventListener('click', function () {
                document.getElementById('fileInput').click();
            });

            document.getElementById('fileInput').addEventListener('change', function () {
                var formData = new FormData();
                formData.append('file', this.files[0]);
                formData.append('reply_to', $('#reply-preview').data('reply-to') || 0);

                fetch('chat.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                    .then(data => {
                        console.log('Server response:', data); // Depuración
                        $('#reply-preview').hide();
                        $('#reply-preview').data('reply-to', null);
                        loadMessages();
                    }).catch(error => {
                        console.error('Error uploading file:', error); // Depuración
                        alert('Error al subir el archivo');
                    });
            });
        });

        let isUserScrolling = false;
        let userScrolledUp = false; // Flag para indicar si el usuario ha desplazado hacia arriba

        // Detectar cuando el usuario está desplazándose
        $('#chat-box').on('scroll', function () {
            isUserScrolling = true;

            // Verificar si el usuario ha desplazado hacia arriba
            if ($(this).scrollTop() < $(this)[0].scrollHeight - $(this).innerHeight()) {
                userScrolledUp = true; // El usuario ha desplazado hacia arriba
            } else {
                userScrolledUp = false; // El usuario está en la parte inferior
            }
        });

        // Detectar cuando el usuario deja de desplazarse
        $('#chat-box').on('scrollstop', function () {
            isUserScrolling = false;
        });

        // Desplazamiento automático solo si el usuario no está desplazándose y no ha desplazado hacia arriba
        function autoScroll() {
            if (!isUserScrolling && !userScrolledUp) {
                $('#chat-box').animate({
                    scrollTop: $('#chat-box')[0].scrollHeight
                }, 'normal');
            }
        }

        // Añadir un evento para detectar cuando el usuario deja de desplazarse
        let scrollTimeout;
        $('#chat-box').on('scroll', function () {
            clearTimeout(scrollTimeout);
            isUserScrolling = true;
            scrollTimeout = setTimeout(function () {
                isUserScrolling = false;
            }, 100); // Cambia el tiempo de espera según sea necesario
        });
        $('#send-image').on('click', function () {
            $('#image').click(); // Simula un clic en el input de archivo
        });

        document.getElementById('uploadButton').addEventListener("click", () => {
            document.getElementById('SubirDiv').classList.toggle('show');
            event.stopPropagation();
        });
        document.addEventListener('click', function (event) {
            if (!container.contains(event.target) && container.classList.contains('show')) {
                container.classList.remove('show');
            }
        });
        document.addEventListener('click', function (event) {
            var div = document.getElementById('SubirDiv');
            if (!div.contains(event.target)) {
                div.classList.remove('show');
            }
        });

        function uploadFile(type) {
            var fileInput;
            var validTypes;

            if (type === 'image') {
                fileInput = document.getElementById('imageInput');
                validTypes = ['image/gif', 'image/jpeg', 'image/png'];
            } else {
                fileInput = document.getElementById('docInput');
                validTypes = [
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/pdf'
                ];
            }

            var file = fileInput.files[0];
            if (file) {
                if (validTypes.includes(file.type)) {
                    var formData = new FormData();
                    formData.append('file', file);

                    fetch('upload.php', {
                        method: 'POST',
                        body: formData
                    }).then(response => {
                        if (response.ok) {
                            alert('Archivo subido exitosamente');
                        } else {
                            alert('Error al subir el archivo');
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                        alert('Error al subir el archivo');
                    });
                } else {
                    alert('Tipo de archivo no permitido.');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('upload-button').addEventListener('click', function (event) {
                var uploadMenu = document.getElementById('upload-menu');
                if (uploadMenu.style.display === 'block') {
                    uploadMenu.style.display = 'none';
                } else {
                    uploadMenu.style.display = 'block';
                }
                event.stopPropagation();
            });
        });

        document.addEventListener('click', function (event) {
            var uploadMenu = document.getElementById('upload-menu');
            if (!uploadMenu.contains(event.target) && !document.getElementById('upload-button').contains(event.target)) {
                uploadMenu.style.display = 'none';
            }
        });

        document.getElementById('upload-image').addEventListener('click', function () {
            // Lógica para subir imagen
            document.getElementById('upload-menu').classList.remove('show');
            alert('Subir Imagen');
        });

        document.getElementById('upload-file').addEventListener('click', function () {
            // Lógica para subir archivo
            document.getElementById('upload-menu').classList.remove('show');
            alert('Subir Archivo');
        });

        document.addEventListener('click', function (event) {
            var uploadMenu = document.getElementById('upload-menu');
            if (!uploadMenu.contains(event.target) && !document.getElementById('upload-button').contains(event.target)) {
                uploadMenu.classList.remove('show');
            }
        });
    </script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>