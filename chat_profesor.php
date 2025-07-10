<?php
session_start();
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
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
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

    <div class="menu" id="menu">
        <div class="menuopc">
            <button class="boton" id="boton-izquierdo">
                <svg width="11px" height="20px" viewBox="0 0 11 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <title>arrow_back_ios</title>
                    <desc>Created with Sketch.</desc>
                    <g id="Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <g id="Rounded" transform="translate(-548.000000, -3434.000000)">
                            <g id="Navigation" transform="translate(100.000000, 3378.000000)">
                                <g id="-Round-/-Navigation-/-arrow_back_ios" transform="translate(442.000000, 54.000000)">
                                    <g>
                                        <polygon id="Path" opacity="0.87" points="0 0 24 0 24 24 0 24"></polygon>
                                        <path d="M16.62,2.99 C16.13,2.5 15.34,2.5 14.85,2.99 L6.54,11.3 C6.15,11.69 6.15,12.32 6.54,12.71 L14.85,21.02 C15.34,21.51 16.13,21.51 16.62,21.02 C17.11,20.53 17.11,19.74 16.62,19.25 L9.38,12 L16.63,4.75 C17.11,4.27 17.11,3.47 16.62,2.99 Z" id="馃敼-Icon-Color" fill="#1D1D1D"></path>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </g>
                </svg>
            </button>
            <div class="menuopciones" id="contenedor">
                <div class="opción" id="inicio">
                    <div class="intopcion">
                        <img src="css\home.png">
                        <p>Inicio</p>
                    </div>
                </div>
                <div class="opción" id="datos">
                    <div class="intopcion">
                        <img src="css\person.png">
                        <p>Datos</p>
                    </div>
                </div>
                <div class="opción" id="foto">
                    <div class="intopcion">
                        <img src="css\camera.png">
                        <p>Foto</p>
                    </div>
                </div>
                <div class="opción">
                    <div class="intopcion" id="cursos">
                        <img src="css/cursos.png">
                        <p>Cursos</p>
                    </div>
                </div>
                <div class="opción">
                    <div class="intopcion" id="chat">
                        <img src="css/muro.png">
                        <p>Chat</p>
                    </div>
                </div>
                <div class="opción">
                    <div class="intopcion" id="notas">
                        <img src="css/notas.png">
                        <p>Notas</p>
                    </div>
                </div>
            </div>
            <button class="boton" id="boton-derecho">
                <svg width="11px" height="20px" viewBox="0 0 11 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <title>arrow_forward_ios</title>
                    <desc>Created with Sketch.</desc>
                    <g id="Icons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <g id="Rounded" transform="translate(-345.000000, -3434.000000)">
                            <g id="Navigation" transform="translate(100.000000, 3378.000000)">
                                <g id="-Round-/-Navigation-/-arrow_forward_ios" transform="translate(238.000000, 54.000000)">
                                    <g>
                                        <polygon id="Path" opacity="0.87" points="24 24 0 24 0 0 24 0"></polygon>
                                        <path d="M7.38,21.01 C7.87,21.5 8.66,21.5 9.15,21.01 L17.46,12.7 C17.85,12.31 17.85,11.68 17.46,11.29 L9.15,2.98 C8.66,2.49 7.87,2.49 7.38,2.98 C6.89,3.47 6.89,4.26 7.38,4.75 L14.62,12 L7.37,19.25 C6.89,19.73 6.89,20.53 7.38,21.01 Z" id="馃敼-Icon-Color" fill="#1D1D1D"></path>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </g>
                </svg>
            </button>
        </div>
        <div class="inferior">
            <form action="logout.php" method="POST">
                <div class="logout">
                    <button class="Btn">

                        <div class="sign"><svg viewBox="0 0 512 512">
                                <path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path>
                            </svg></div>

                        <div class="text">Salir</div>
                    </button>
                </div>
            </form>
            <div class="themeswitcher">
                <label class="theme-switch">
                    <input type="checkbox" class="theme-switch__checkbox" id="switchtema">
                    <div class="theme-switch__container">
                        <div class="theme-switch__clouds"></div>
                        <div class="theme-switch__stars-container">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 144 55" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M135.831 3.00688C135.055 3.85027 134.111 4.29946 133 4.35447C134.111 4.40947 135.055 4.85867 135.831 5.71123C136.607 6.55462 136.996 7.56303 136.996 8.72727C136.996 7.95722 137.172 7.25134 137.525 6.59129C137.886 5.93124 138.372 5.39954 138.98 5.00535C139.598 4.60199 140.268 4.39114 141 4.35447C139.88 4.2903 138.936 3.85027 138.16 3.00688C137.384 2.16348 136.996 1.16425 136.996 0C136.996 1.16425 136.607 2.16348 135.831 3.00688ZM31 23.3545C32.1114 23.2995 33.0551 22.8503 33.8313 22.0069C34.6075 21.1635 34.9956 20.1642 34.9956 19C34.9956 20.1642 35.3837 21.1635 36.1599 22.0069C36.9361 22.8503 37.8798 23.2903 39 23.3545C38.2679 23.3911 37.5976 23.602 36.9802 24.0053C36.3716 24.3995 35.8864 24.9312 35.5248 25.5913C35.172 26.2513 34.9956 26.9572 34.9956 27.7273C34.9956 26.563 34.6075 25.5546 33.8313 24.7112C33.0551 23.8587 32.1114 23.4095 31 23.3545ZM0 36.3545C1.11136 36.2995 2.05513 35.8503 2.83131 35.0069C3.6075 34.1635 3.99559 33.1642 3.99559 32C3.99559 33.1642 4.38368 34.1635 5.15987 35.0069C5.93605 35.8503 6.87982 36.2903 8 36.3545C7.26792 36.3911 6.59757 36.602 5.98015 37.0053C5.37155 37.3995 4.88644 37.9312 4.52481 38.5913C4.172 39.2513 3.99559 39.9572 3.99559 40.7273C3.99559 39.563 3.6075 38.5546 2.83131 37.7112C2.05513 36.8587 1.11136 36.4095 0 36.3545ZM56.8313 24.0069C56.0551 24.8503 55.1114 25.2995 54 25.3545C55.1114 25.4095 56.0551 25.8587 56.8313 26.7112C57.6075 27.5546 57.9956 28.563 57.9956 29.7273C57.9956 28.9572 58.172 28.2513 58.5248 27.5913C58.8864 26.9312 59.3716 26.3995 59.9802 26.0053C60.5976 25.602 61.2679 25.3911 62 25.3545C60.8798 25.2903 59.9361 24.8503 59.1599 24.0069C58.3837 23.1635 57.9956 22.1642 57.9956 21C57.9956 22.1642 57.6075 23.1635 56.8313 24.0069ZM81 25.3545C82.1114 25.2995 83.0551 24.8503 83.8313 24.0069C84.6075 23.1635 84.9956 22.1642 84.9956 21C84.9956 22.1642 85.3837 23.1635 86.1599 24.0069C86.9361 24.8503 87.8798 25.2903 89 25.3545C88.2679 25.3911 87.5976 25.602 86.9802 26.0053C86.3716 26.3995 85.8864 26.9312 85.5248 27.5913C85.172 28.2513 84.9956 28.9572 84.9956 29.7273C84.9956 28.563 84.6075 27.5546 83.8313 26.7112C83.0551 25.8587 82.1114 25.4095 81 25.3545ZM136 36.3545C137.111 36.2995 138.055 35.8503 138.831 35.0069C139.607 34.1635 139.996 33.1642 139.996 32C139.996 33.1642 140.384 34.1635 141.16 35.0069C141.936 35.8503 142.88 36.2903 144 36.3545C143.268 36.3911 142.598 36.602 141.98 37.0053C141.372 37.3995 140.886 37.9312 140.525 38.5913C140.172 39.2513 139.996 39.9572 139.996 40.7273C139.996 39.563 139.607 38.5546 138.831 37.7112C138.055 36.8587 137.111 36.4095 136 36.3545ZM101.831 49.0069C101.055 49.8503 100.111 50.2995 99 50.3545C100.111 50.4095 101.055 50.8587 101.831 51.7112C102.607 52.5546 102.996 53.563 102.996 54.7273C102.996 53.9572 103.172 53.2513 103.525 52.5913C103.886 51.9312 104.372 51.3995 104.98 51.0053C105.598 50.602 106.268 50.3911 107 50.3545C105.88 50.2903 104.936 49.8503 104.16 49.0069C103.384 48.1635 102.996 47.1642 102.996 46C102.996 47.1642 102.607 48.1635 101.831 49.0069Z" fill="currentColor"></path>
                            </svg>
                        </div>
                        <div class="theme-switch__circle-container">
                            <div class="theme-switch__sun-moon-container">
                                <div class="theme-switch__moon">
                                    <div class="theme-switch__spot"></div>
                                    <div class="theme-switch__spot"></div>
                                    <div class="theme-switch__spot"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>

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
        document.addEventListener('click', function(event) {
            if (!container.contains(event.target) && container.classList.contains('toggle')) {
                container.classList.remove('toggle');
            }
        });
        document.addEventListener('click', function(event) {
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




        function redirigir(url) {
            window.location.href = url;;
            // Cambia esta URL a la página de destino 
        }
        window.onload = function() {
            document.getElementById('inicio').addEventListener('click', function() {
                redirigir('pagina_principal.php');
            });
            document.getElementById('datos').addEventListener('click', function() {
                redirigir('datos.php');
            });
            document.getElementById('inscripcion').addEventListener('click', function() {
                redirigir('inscripcion.php');
            });
            document.getElementById('horario').addEventListener('click', function() {
                redirigir('horario.php');
            });
            document.getElementById('chat').addEventListener('click', function() {
                redirigir('seleccionarmateria.php');
            });
            document.getElementById('foto').addEventListener('click', function() {
                redirigir('foto.php');
            });
            document.getElementById('desempeño').addEventListener('click', function() {
                redirigir('desempeño.php');
            });
            document.getElementById('notas').addEventListener('click', function() {
                redirigir('NAlumnos.php');
            });
        }

        $(document).ready(function() {
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
                    }, function(data) {
                        console.log('Server response:', data); // Depuración
                        $('#message').val(''); // Limpiar el campo de mensaje
                        $('#reply-preview').hide(); // Ocultar la vista previa de la respuesta
                        $('#reply-preview').data('reply-to', null); // Limpiar el ID de respuesta
                        loadMessages(); // Cargar mensajes después de enviar
                    }).fail(function(xhr, status, error) {
                        console.error('Error sending message:', xhr.responseText); // Depuración
                        alert('Error al enviar el mensaje: ' + xhr.responseText);
                    });
                }
            }

            $('#send-button').on('click', sendMessage);

            $('#message').on('keypress', function(e) {
                if (e.which === 13) { // Enter key pressed
                    sendMessage();
                    return false; // Prevent the default action (form submission)
                }
            });

            $('#cancel-reply').on('click', function() {
                $('#reply-preview').hide();
                $('#reply-preview').data('reply-to', null);
            });

            // Función para cargar mensajes
            function loadMessages() {
                console.log('Loading messages'); // Depuración
                $.get('load_messages.php', function(data) {
                    console.log('Messages loaded'); // Depuración
                    $('#chat-box').html(data);
                    autoScroll(); // Llamar a la función de desplazamiento automático
                }).fail(function(xhr, status, error) {
                    console.error('Error loading messages:', xhr.responseText); // Depuración
                });
            }

            // Actualizar mensajes cada 2 segundos
            setInterval(loadMessages, 2000);

            // Manejar el evento de clic del botón de respuesta
            $(document).on('click', '.reply-button', function() {
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

            document.getElementById('upload-button').addEventListener('click', function(event) {
                var uploadMenu = document.getElementById('upload-menu');
                if (uploadMenu.style.display === 'block') {
                    uploadMenu.style.display = 'none';
                } else {
                    uploadMenu.style.display = 'block';
                }
                event.stopPropagation();
            });

            document.addEventListener('click', function(event) {
                var uploadMenu = document.getElementById('upload-menu');
                if (!uploadMenu.contains(event.target) && !document.getElementById('upload-button').contains(event.target)) {
                    uploadMenu.style.display = 'none';
                }
            });

            document.getElementById('upload-image').addEventListener('click', function() {
                document.getElementById('imageInput').click();
            });

            document.getElementById('imageInput').addEventListener('change', function() {
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

            document.getElementById('upload-file').addEventListener('click', function() {
                document.getElementById('fileInput').click();
            });

            document.getElementById('fileInput').addEventListener('change', function() {
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
        $('#chat-box').on('scroll', function() {
            isUserScrolling = true;

            // Verificar si el usuario ha desplazado hacia arriba
            if ($(this).scrollTop() < $(this)[0].scrollHeight - $(this).innerHeight()) {
                userScrolledUp = true; // El usuario ha desplazado hacia arriba
            } else {
                userScrolledUp = false; // El usuario está en la parte inferior
            }
        });

        // Detectar cuando el usuario deja de desplazarse
        $('#chat-box').on('scrollstop', function() {
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
        $('#chat-box').on('scroll', function() {
            clearTimeout(scrollTimeout);
            isUserScrolling = true;
            scrollTimeout = setTimeout(function() {
                isUserScrolling = false;
            }, 100); // Cambia el tiempo de espera según sea necesario
        });
        $('#send-image').on('click', function() {
            $('#image').click(); // Simula un clic en el input de archivo
        });

        document.getElementById('uploadButton').addEventListener("click", () => {
            document.getElementById('SubirDiv').classList.toggle('show');
            event.stopPropagation();
        });
        document.addEventListener('click', function(event) {
            if (!container.contains(event.target) && container.classList.contains('show')) {
                container.classList.remove('show');
            }
        });
        document.addEventListener('click', function(event) {
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

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('upload-button').addEventListener('click', function(event) {
                var uploadMenu = document.getElementById('upload-menu');
                if (uploadMenu.style.display === 'block') {
                    uploadMenu.style.display = 'none';
                } else {
                    uploadMenu.style.display = 'block';
                }
                event.stopPropagation();
            });
        });

        document.addEventListener('click', function(event) {
            var uploadMenu = document.getElementById('upload-menu');
            if (!uploadMenu.contains(event.target) && !document.getElementById('upload-button').contains(event.target)) {
                uploadMenu.style.display = 'none';
            }
        });

        document.getElementById('upload-image').addEventListener('click', function() {
            // Lógica para subir imagen
            document.getElementById('upload-menu').classList.remove('show');
            alert('Subir Imagen');
        });

        document.getElementById('upload-file').addEventListener('click', function() {
            // Lógica para subir archivo
            document.getElementById('upload-menu').classList.remove('show');
            alert('Subir Archivo');
        });

        document.addEventListener('click', function(event) {
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