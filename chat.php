<?php
session_start();
require 'conexion.php';


// 🔐 Validación de sesión
if (!isset($_SESSION['idusuario'])) {
    header("Location: inicio.php");
    exit();
}

$advertencia = "";

// Obtener el nombre y sección de la materia
$id_materia = $_SESSION['idmateria']; // Usar el id de materia de la sesión
$stmt = $conn->prepare("SELECT nombre, seccion FROM materias WHERE id = ?");
$stmt->bind_param("i", $id_materia);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $_SESSION['nombre_materia'] = $row['nombre'];
    $_SESSION['seccion_materia'] = $row['seccion'];
} else {
    $_SESSION['nombre_materia'] = "Materia no encontrada";
    $_SESSION['seccion_materia'] = "";
}
$stmt->close();

// 📨 Enviar mensaje de texto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? (int)$_POST['reply_to'] : 0;
    $message = trim($_POST['message']);

    if (strlen($message) > 0 && strlen($message) <= 1000) {
        // Permitir letras, números, espacios, puntuación básica y emojis
        if (preg_match('/^[\p{L}\p{N}\s\.,!?;:()@#$%*+\-=_<>\/\\\\]+$/u', $message)) {
            // Mensaje válido, puedes procesarlo con confianza
        
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
        echo json_encode(['error' => 'El mensaje debe tener entre 1 y 1000 caracteres']);
    }
    exit();
}

// 🖼️ Enviar imagen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? (int)$_POST['reply_to'] : 0;

    // ✅ Validación de imagen
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

    // ✅ Sanitizar nombre de archivo
    $file_extension = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

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

// 📁 Enviar archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? (int)$_POST['reply_to'] : 0;

    // ✅ Validación de archivo
    $allowed_types = [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/pdf'
    ];
    $max_size = 10 * 1024 * 1024; // 10MB

    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['error' => 'Tipo de archivo no permitido']);
        exit();
    }

    if ($file['size'] > $max_size) {
        echo json_encode(['error' => 'El archivo es demasiado grande (máximo 10MB)']);
        exit();
    }

    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // ✅ Sanitizar nombre de archivo
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

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

// 💬 Mostrar historial de mensajes
$idgrupo = $_SESSION['idmateria'];

$stmt = $conn->prepare("
    SELECT 
        messages.id, 
        messages.message, 
        messages.created_at, 
        messages.tipo, 
        messages.reply_to,
        usuarios.id AS user_id, 
        usuarios.nombre_usuario, 
        usuarios.nivel_usuario,
        foto
    FROM messages
    JOIN usuarios ON messages.user_id = usuarios.id
    LEFT JOIN fotousuario ON usuarios.id = id_usuario
    WHERE messages.group_id = ?
    ORDER BY messages.created_at ASC
");

$stmt->bind_param("i", $idgrupo);
$stmt->execute();
$result = $stmt->get_result();

$last_date = null;
$last_user_id = null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/chat.css">
    <link rel="stylesheet" href="css/chat_actions.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
            <h1><?php echo htmlspecialchars($_SESSION['nombremateria'] ?? 'Chat') ?></h1>
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
                                        <path d="M16.62,2.99 C16.13,2.5 15.34,2.5 14.85,2.99 L6.54,11.3 C6.15,11.69 6.15,12.32 6.54,12.71 L14.85,21.02 C15.34,21.51 16.13,21.51 16.62,21.02 C17.11,20.53 17.11,19.74 16.62,19.25 L9.38,12 L16.63,4.75 C17.11,4.27 17.11,3.47 16.62,2.99 Z" id="🔹-Icon-Color" fill="#1D1D1D"></path>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </g>
                </svg>
            </button>
            <div class="menuopciones" id="contenedor">
                <div class="opción">
                    <div class="intopcion" id="inicio">
                        <img src="css\home.png">
                        <p>Inicio</p>
                    </div>
                </div>
                <div class="opción">
                    <div class="intopcion" id="datos">
                        <img src="css\person.png">
                        <p>Datos</p>
                    </div>
                </div>
                <div class="opción">
                    <div class="intopcion" id="foto">
                        <img src="css\camera.png">
                        <p>Foto</p>
                    </div>
                </div>
                <div class="opción" id="inscripcion">
                    <div class="intopcion">
                        <img src="css/inscripción.png">
                        <p>Inscripción</p>
                    </div>
                </div>
                <div class="opción" id="horario">
                    <div class="intopcion">
                        <img src="css/horario.png">
                        <p>Horario</p>
                    </div>
                </div>
                <div class="opción" id="chat">
                    <div class="intopcion">
                        <img src="css/muro.png">
                        <p>Chat</p>
                    </div>
                </div>
                <div class="opción">
                    <div class="intopcion" id="desempeño">
                        <img src="css/situacionacademica.png">
                        <p>Tareas</p>
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
                                        <path d="M7.38,21.01 C7.87,21.5 8.66,21.5 9.15,21.01 L17.46,12.7 C17.85,12.31 17.85,11.68 17.46,11.29 L9.15,2.98 C8.66,2.49 7.87,2.49 7.38,2.98 C6.89,3.47 6.89,4.26 7.38,4.75 L14.62,12 L7.37,19.25 C6.89,19.73 6.89,20.53 7.38,21.01 Z" id="🔹-Icon-Color" fill="#1D1D1D"></path>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </g>
                </svg>
            </button>
        </div>
        <div class="inferior">
            <div class="logout">
                <form action="logout.php" method="POST">
                    <button class="Btn" type="submit">
                        <div class="sign"><svg viewBox="0 0 512 512">
                                <path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path>
                            </svg></div>
                        <div class="text">Salir</div>
                    </button>
                </form>
            </div>
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
                <!-- Los mensajes se cargan aquí dinámicamente -->
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
            <input type="text" id="message" placeholder="Escribe un mensaje..." maxlength="1000" />
            <button id="send-button" class="button">
                <img src="css/enviar-mensaje.png" alt="Send" width="40" height="40">
            </button>
            <input type="file" id="imageInput" accept="image/*" style="display: none;">
            <input type="file" id="fileInput" accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf" style="display: none;">
            <button id="call-button" class="button">
                <img src="css/llamada_alumno.png" alt="Call" width="40" height="40">
            </button>
        </div>
    </div>

    <?php if ($advertencia) { ?>
        <div class="advertencia-flotante" style="position: fixed; top: 30px; right: 30px; z-index: 9999; background: #ffdddd; color: #a94442; border: 1px solid #a94442; border-radius: 8px; padding: 16px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-weight: bold;">
            <p style="margin:0;"><?php echo htmlspecialchars($advertencia); ?></p>
        </div>
        <script>
            setTimeout(function() {
                var adv = document.querySelector('.advertencia-flotante');
                if (adv) adv.style.display = 'none';
            }, 3500);
        </script>
    <?php } ?>

    <div id="upload-menu" style="display: none;">
        <div class="upload-option" id="upload-image">Subir Imagen</div>
        <div class="upload-option" id="upload-file">Subir Archivo</div>
    </div>

    <script>
        // 🔧 Configuración global
        const CONFIG = {
            maxMessageLength: 1000,
            maxImageSize: 5 * 1024 * 1024, // 5MB
            maxFileSize: 10 * 1024 * 1024, // 10MB
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

        // 🎨 Gestión de tema
        function initTheme() {
            const theme = localStorage.getItem('theme') || 'light';
            const switchElement = document.getElementById('switchtema');
            
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
                switchElement.checked = true;
            } else {
                document.body.classList.remove('dark-mode');
                switchElement.checked = false;
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
            
            // Enviar al backend
            fetch('set_theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'theme=' + theme
            }).catch(error => console.error('Error setting theme:', error));
        }

        // 📱 Navegación
        function goBack() {
            window.history.back();
        }

        function redirigir(url) {
            window.location.href = url;
        }

        // 🎯 Event Listeners principales
        document.addEventListener('DOMContentLoaded', function() {
            initTheme();
            loadMessages();
            setupEventListeners();
            setupNavigation();
        });

        // 🔗 Configurar navegación
        function setupNavigation() {
            const navItems = {
                'inicio': 'pagina_principal.php',
                'datos': 'datos.php',
                'inscripcion': 'inscripcion.php',
                'horario': 'horario.php',
                'chat': 'seleccionarmateria.php',
                'foto': 'foto.php',
                'desempeño': 'Seleccion_de_materias_tareas.php'
            };

            Object.keys(navItems).forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('click', () => redirigir(navItems[id]));
                }
            });
        }

        // 🎛️ Configurar event listeners
        function setupEventListeners() {
            
            // Tema
            const themeSwitch = document.getElementById('switchtema');
            if (themeSwitch) {
                themeSwitch.addEventListener('change', function() {
                    setTheme(this.checked ? 'dark' : 'light');
                });
            }

            // Navegación del menú
            document.getElementById('logoButton').addEventListener("click", () => {
                document.getElementById('menu').classList.toggle('toggle');
                event.stopPropagation();
            });

            // Scroll del menú
            const contenedor = document.getElementById('contenedor');
            const botonIzquierdo = document.getElementById('boton-izquierdo');
            const botonDerecho = document.getElementById('boton-derecho');
            
            botonIzquierdo.addEventListener('click', () => {
                contenedor.scrollBy({ left: -94, behavior: 'smooth' });
            });
            
            botonDerecho.addEventListener('click', () => {
                contenedor.scrollBy({ left: 94, behavior: 'smooth' });
            });

            // Cerrar menú al hacer clic fuera
            document.addEventListener('click', function(event) {
                var div = document.getElementById('menu');
                if (!div.contains(event.target)) {
                    div.classList.remove('toggle');
                }
            });

            // Mensajes
            setupMessageHandlers();
            setupFileHandlers();
        }

        // 💬 Configurar manejo de mensajes
        function setupMessageHandlers() {
            const sendButton = document.getElementById('send-button');
            const messageInput = document.getElementById('message');
            const cancelReplyButton = document.getElementById('cancel-reply');

            sendButton.addEventListener('click', sendMessage);
            
            messageInput.addEventListener('keypress', function(e) {
                if (e.which === 13) {
                    sendMessage();
                    return false;
                }
            });

            cancelReplyButton.addEventListener('click', function() {
                hideReplyPreview();
            });

            // Respuestas
            $(document).on('click', '.reply-button', function() {
                const messageId = $(this).data('message-id');
                const messageContent = $('#message-text-' + messageId).text();
                const userName = $(this).siblings('.message-bubble-usuario, .message-bubble-profesor').find('strong').first().text();
                
                showReplyPreview(userName, messageContent, messageId);
            });
        }

        // 📁 Configurar manejo de archivos
        function setupFileHandlers() {
            const uploadButton = document.getElementById('upload-button');
            const uploadMenu = document.getElementById('upload-menu');
            const uploadImage = document.getElementById('upload-image');
            const uploadFile = document.getElementById('upload-file');
            const imageInput = document.getElementById('imageInput');
            const fileInput = document.getElementById('fileInput');

            uploadButton.addEventListener('click', function(event) {
                uploadMenu.style.display = uploadMenu.style.display === 'block' ? 'none' : 'block';
                event.stopPropagation();
            });

            document.addEventListener('click', function(event) {
                if (!uploadMenu.contains(event.target) && !uploadButton.contains(event.target)) {
                    uploadMenu.style.display = 'none';
                }
            });

            uploadImage.addEventListener('click', () => imageInput.click());
            uploadFile.addEventListener('click', () => fileInput.click());

            imageInput.addEventListener('change', handleImageUpload);
            fileInput.addEventListener('change', handleFileUpload);
        }

        // 📤 Enviar mensaje
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

            $.post('chat.php', {
                message: message,
                reply_to: replyTo
            })
            .done(function(data) {
                try {
                    const response = JSON.parse(data);
                    if (response.success) {
                        $('#message').val('');
                        hideReplyPreview();
                        loadMessages();
                    } else {
                        showError(response.error || 'Error al enviar el mensaje');
                    }
                } catch (e) {
                    showError('Error al procesar la respuesta del servidor');
                }
            })
            .fail(function(xhr, status, error) {
                showError('Error de conexión: ' + error);
            });
        }

        // 🖼️ Manejar subida de imagen
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

            uploadFile(file, 'image');
        }

        // 📄 Manejar subida de archivo
        function handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!CONFIG.allowedFileTypes.includes(file.type)) {
                showError('Tipo de archivo no permitido');
                return;
            }

            if (file.size > CONFIG.maxFileSize) {
                showError('El archivo es demasiado grande (máximo 10MB)');
                return;
            }

            uploadFile(file, 'file');
        }

        // 📤 Subir archivo genérico
        function uploadFile(file, type) {
            const formData = new FormData();
            formData.append(type, file);
            formData.append('reply_to', $('#reply-preview').data('reply-to') || 0);

            fetch('chat.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const response = JSON.parse(data);
                    if (response.success) {
                        hideReplyPreview();
                        loadMessages();
                        document.getElementById('upload-menu').style.display = 'none';
                    } else {
                        showError(response.error || 'Error al subir el archivo');
                    }
                } catch (e) {
                    showError('Error al procesar la respuesta del servidor');
                }
            })
            .catch(error => {
                showError('Error de conexión: ' + error);
            });
        }

        // 🔄 Cargar mensajes
        function loadMessages() {
            $.get('load_messages.php')
                .done(function(data) {
                    $('#chat-box').html(data);
                    autoScroll();
                })
                .fail(function(xhr, status, error) {
                    console.error('Error loading messages:', error);
                });
        }

        // 🗑️ Eliminar mensaje
        function deleteMessage(messageId) {
            if (confirm('¿Estás seguro de que quieres eliminar este mensaje? Esta acción no se puede deshacer.')) {
                $.post('delete_message.php', {
                    message_id: messageId
                })
                .done(function(data) {
                    try {
                        const response = JSON.parse(data);
                        if (response.success) {
                            loadMessages(); // Recargar mensajes para mostrar el cambio
                        } else {
                            showError(response.error || 'Error al eliminar el mensaje');
                        }
                    } catch (e) {
                        showError('Error al procesar la respuesta del servidor');
                    }
                })
                .fail(function(xhr, status, error) {
                    showError('Error de conexión: ' + error);
                });
            }
        }

        // 📍 Auto-scroll
        let isUserScrolling = false;
        let userScrolledUp = false;

        $('#chat-box').on('scroll', function() {
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

        // 🔄 Actualizar mensajes cada 2 segundos
        setInterval(loadMessages, 2000);

        // 🎯 Funciones auxiliares
        function showReplyPreview(userName, messageContent, messageId) {
            $('#reply-message').html('<strong>' + userName + ':</strong> ' + messageContent);
            $('#reply-preview').show().data('reply-to', messageId);
            $('#message').focus();
        }

        function hideReplyPreview() {
            $('#reply-preview').hide().data('reply-to', null);
        }

        function showError(message) {
            alert(message); // Mejorar con una notificación más elegante
        }
    </script>


</body>

</html>