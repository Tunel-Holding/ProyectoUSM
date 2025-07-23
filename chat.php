<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);

include 'comprobar_sesion.php';
actualizar_actividad();
require 'conexion.php';


// 🔐 Validación de sesión
if (!isset($_SESSION['idusuario'])) {
    header("Location: inicio.php");
    exit();
}

$advertencia = "";

// Obtener el nombre y sección de la materia
$id_materia = isset($_SESSION['idmateria']) ? $_SESSION['idmateria'] : null;
if ($id_materia) {
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
} else {
    $_SESSION['nombre_materia'] = "";
    $_SESSION['seccion_materia'] = "";
}

// 📨 Enviar mensaje de texto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    error_log("Enviando mensaje");

    // Actualizar actividad del usuario
    actualizar_actividad();

    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? (int) $_POST['reply_to'] : 0;
    $message = trim($_POST['message']);

    if (strlen($message) > 0 && strlen($message) <= 250) {
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
    // Actualizar actividad del usuario
    actualizar_actividad();

    $image = $_FILES['image'];
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? (int) $_POST['reply_to'] : 0;

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
    // Mantener nombre original de archivo
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

// 📁 Enviar archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Actualizar actividad del usuario
    actualizar_actividad();

    $file = $_FILES['file'];
    $user_id = $_SESSION['idusuario'];
    $group_id = $_SESSION['idmateria'];
    $reply_to = isset($_POST['reply_to']) ? (int) $_POST['reply_to'] : 0;

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
    $max_size = 50 * 1024 * 1024; // 50MB

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
    // Mantener nombre original de archivo
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

// 💬 Mostrar historial de mensajes
$idgrupo = isset($_SESSION['idmateria']) ? $_SESSION['idmateria'] : null;
if ($idgrupo) {
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
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="icon" href="css/icono.png" type="image/png"> -->
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Chat - UniHub</title>
    <script src="js/control_inactividad.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap');

        /* SOLO rediseño el área central del chat y barra de entrada, sin tocar header ni menú */
        .chat-dashboard-area {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            justify-content: flex-start;
            width: 100vw;
            height: calc(100vh - 120px);
            background: #f4f8fb;
            transition: background 0.3s ease;
        }

        body.dark-mode .chat-dashboard-area {
            background: #1a1a1a;
        }

        .sidebar-materias {
            width: 270px;
            height: 100%;
            background: #fff;
            border-radius: 18px 0 0 18px;
            box-shadow: 0 8px 32px rgba(33, 53, 85, 0.10);
            margin: 32px 0 0 32px;
            padding: 24px 0 24px 0;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            position: relative;
            margin-right: 24px;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        body.dark-mode .sidebar-materias {
            background: #2d2d2d;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .sidebar-materias h2 {
            text-align: center;
            color: #213555;
            font-size: 1.2rem;
            margin-bottom: 18px;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        body.dark-mode .sidebar-materias h2 {
            color: #ffffff;
        }

        .lista-materias {
            flex: 1;
            overflow-y: auto;
            padding: 0 18px;
        }

        .materia-item {
            background: #f7fafc;
            border-radius: 10px;
            margin-bottom: 12px;
            padding: 12px 16px;
            color: #213555;
            font-weight: 500;
            cursor: pointer;
            border: 2px solid transparent;
            transition: background 0.2s, border 0.2s, color 0.2s;
        }

        body.dark-mode .materia-item {
            background: #3a3a3a;
            color: #e0e0e0;
        }

        .materia-item.selected,
        .materia-item:hover {
            background: #174388;
            border: 2px solid #0e3470;
            color: white;
        }

        .chat-dashboard-main {
            background: #fff;
            width: 1400px;
            height: 100%;
            margin: 32px 32px 32px 0;
            border-radius: 0 18px 18px 0;
            box-shadow: 0 8px 32px rgba(33, 53, 85, 0.10);
            display: flex;
            flex-direction: column;
            position: relative;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        body.dark-mode .chat-dashboard-main {
            background: #2d2d2d;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 1500px) {
            .chat-dashboard-main {
                width: 1100px;
            }
        }

        @media (max-width: 1200px) {
            .chat-dashboard-main {
                width: 900px;
            }
        }

        @media (max-width: 950px) {

            .chat-dashboard-main,
            .sidebar-materias {
                width: 100vw;
                min-width: 0;
                border-radius: 0;
                margin: 0;
            }

            .chat-dashboard-area {
                flex-direction: column;
            }
        }

        .chat-dashboard-messages {
            flex: 1;
            overflow-y: auto;
            padding: 32px 40px 24px 40px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            background: #f7fafc;
            border-radius: 18px 18px 0 0;
            min-height: 0;
            transition: background 0.3s ease;
        }

        body.dark-mode .chat-dashboard-messages {
            background: #1e1e1e;
        }

        .chat-dashboard-entry {
            width: 100%;
            background: #fff;
            border-radius: 0 0 18px 18px;
            box-shadow: 0 -2px 12px rgba(33, 53, 85, 0.04);
            padding: 18px 32px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-top: 1px solid #e6e6e6;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        body.dark-mode .chat-dashboard-entry {
            background: #2d2d2d;
            border-top: 1px solid #404040;
        }

        .chat-dashboard-entry input[type="text"] {
            border-radius: 24px;
            border: 1px solid #e0e0e0;
            padding: 12px 18px;
            font-size: 1.1rem;
            background: #f7f7f7;
            color: #333;
            transition: border 0.2s, background 0.2s, color 0.2s;
            flex: 1;
        }

        .chat-dashboard-entry input[type="text"]:focus {
            border: 1.5px solid #ffd166;
            background: #fffbe6;
        }

        body.dark-mode .chat-dashboard-entry input[type="text"] {
            background: #404040;
            border: 1px solid #555;
            color: #ffffff;
        }

        body.dark-mode .chat-dashboard-entry input[type="text"]:focus {
            border: 1.5px solid #ffd166;
            background: #4a4a4a;
        }

        .chat-dashboard-entry .button {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.1s;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #174388;
            border: none;
        }

        .chat-dashboard-entry .button:active {
            transform: scale(0.95);
        }

        .chat-dashboard-entry .button img {
            width: 28px;
            height: 28px;
        }

        .chat-dashboard-reply {
            background: #f1f1f1;
            border-left: 4px solid #007bff;
            border-radius: 10px;
            padding: 8px 12px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.98rem;
            width: 100%;
            transition: background 0.3s ease;
        }

        body.dark-mode .chat-dashboard-reply {
            background: #404040;
        }

        .chat-dashboard-reply #reply-to-user {
            color: #2196f3;
            font-weight: 600;
        }

        .chat-dashboard-reply #cancel-reply {
            margin-left: auto;
        }

        /* Modal de progreso de subida */
        #upload-progress-modal {
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            /* Oculto por defecto */
            align-items: center;
            justify-content: center;
        }

        #upload-progress-modal .modal-content {
            background-color: #fefefe;
            padding: 20px 40px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode #upload-progress-modal .modal-content {
            background-color: #2d2d2d;
            border-color: #555;
            color: #f1f1f1;
        }

        .progress-bar-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 5px;
            margin: 20px 0;
        }

        body.dark-mode .progress-bar-container {
            background-color: #404040;
        }

        .progress-bar {
            width: 0%;
            height: 20px;
            background-color: #4caf50;
            text-align: center;
            line-height: 20px;
            color: white;
            border-radius: 5px;
            transition: width 0.4s ease;
        }

        #progress-text {
            font-weight: bold;
        }

        .upload-wrapper {
            position: relative;
            display: inline-block;
        }

        #upload-menu {
            position: absolute !important;
            bottom: 20px !important;
            right: 50px !important;
            background: #23272f !important;
            border: 1.5px solid #fff !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 16px rgba(33, 53, 85, 0.13) !important;
            padding: 0 !important;
            min-width: 180px !important;
            z-index: 1001 !important;
            display: none;
        }

        #upload-menu.show {
            display: block;
        }

        #upload-menu .upload-option {
            padding: 12px 18px !important;
            cursor: pointer !important;
            color: #fff !important;
            font-weight: 600 !important;
            font-size: 1.08em !important;
            background: none !important;
            transition: background 0.2s, color 0.2s !important;
            white-space: nowrap !important;
            border-bottom: none !important;
            margin-bottom: 2px !important;
        }

        #upload-menu .upload-option:last-child {
            margin-bottom: 0 !important;
        }

        #upload-menu .upload-option:not(:last-child) {
            border-bottom: 1px solid #444 !important;
        }

        #upload-menu .upload-option:hover {
            background: #333 !important;
            color: #fff !important;
            border-bottom: none !important;
        }

        .reply-preview {
            display: flex;
            align-items: center;
            background: #23272f;
            border-radius: 10px 10px 0 0;
            padding: 8px 12px;
            margin-bottom: 0;
            position: relative;
            border-left: 4px solid #25d366;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            min-height: 40px;
            max-width: 100%;
        }

        .reply-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .reply-user {
            color: #25d366;
            font-weight: 600;
            font-size: 0.98em;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .reply-text {
            color: #fff;
            font-size: 0.97em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .close-reply {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.3em;
            margin-left: 10px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .close-reply:hover {
            opacity: 1;
        }

        .reply-preview-blue {
            display: flex;
            align-items: center;
            background: rgba(23, 67, 136, 0.18);
            border-radius: 12px;
            padding: 10px 16px 10px 12px;
            margin-bottom: 0;
            position: relative;
            border-left: 4px solid #174388;
            box-shadow: 0 2px 8px rgba(23, 67, 136, 0.08);
            min-height: 40px;
            max-width: 100%;
        }

        .reply-content-blue {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            gap: 2px;
        }

        .reply-user-blue {
            color: #174388;
            font-weight: 700;
            font-size: 0.97em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .reply-message-blue {
            color: #fff;
            font-size: 0.98em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 400;
        }

        .close-reply-blue {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.3em;
            margin-left: 10px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .close-reply-blue:hover {
            opacity: 1;
        }

        .reply-img-thumb {
            max-width: 60px;
            max-height: 40px;
            border-radius: 4px;
            margin-top: 2px;
            display: block;
        }

        .chat-entry-wrapper {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .reply-preview-blue {
            margin-bottom: 6px;
        }

        /* Modal para vista ampliada de imagen */
        #image-modal {
            display: none;
            position: fixed;
            z-index: 20000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.85);
            align-items: center;
            justify-content: center;
            animation: fadeInModal 0.25s;
        }

        #image-modal.show {
            display: flex;
        }

        #image-modal .modal-img {
            max-width: 90vw;
            max-height: 80vh;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            animation: zoomInModal 0.25s;
        }

        #image-modal .close-modal {
            position: absolute;
            top: 32px;
            right: 48px;
            font-size: 2.2em;
            color: #fff;
            background: none;
            border: none;
            cursor: pointer;
            opacity: 0.8;
            z-index: 20001;
            transition: opacity 0.2s;
        }

        #image-modal .close-modal:hover {
            opacity: 1;
        }

        @keyframes fadeInModal {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes zoomInModal {
            from {
                transform: scale(0.8);
                opacity: 0.5;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Modal para editar mensaje */
        #edit-modal {
            display: none;
            position: fixed;
            z-index: 20010;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(24, 26, 27, 0.96);
            /* Más oscuro */
            align-items: center;
            justify-content: center;
            animation: fadeInModal 0.25s;
        }

        #edit-modal.show {
            display: flex;
        }

        .edit-modal-content {
            background: #23272f;
            border-radius: 22px;
            padding: 40px 48px 48px 48px;
            min-width: 520px;
            max-width: 700px;
            min-height: 320px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.45);
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .edit-modal-close {
            position: absolute;
            top: 16px;
            left: 16px;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.8em;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
            z-index: 1;
        }

        .edit-modal-close:hover {
            opacity: 1;
        }

        .edit-modal-bubble {
            background: #174388;
            color: #fff;
            border-radius: 14px 14px 14px 4px;
            padding: 12px 16px;
            font-size: 1em;
            margin-bottom: 0;
            box-shadow: 0 2px 8px rgba(23, 67, 136, 0.18);
            max-width: 70%;
            align-self: flex-start;
            word-break: break-word;
            margin-top: 20px;
        }

        .edit-modal-input {
            width: 100%;
            min-height: 40px;
            max-height: 160px;
            font-size: 1.13em;
            border: none;
            border-bottom: 2px solid #25d366;
            background: transparent;
            color: #fff;
            margin-bottom: 0;
            padding: 10px 0 6px 0;
            outline: none;
            resize: none;
            overflow-y: auto;
            transition: border-color 0.2s;
        }

        .edit-modal-input:focus {
            border-bottom: 2px solid #34b7f1;
        }

        .edit-modal-input::placeholder {
            color: #888;
        }

        .edit-modal-save {
            position: absolute;
            bottom: 16px;
            right: 16px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: #25d366;
            color: #fff;
            font-size: 1.8em;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(37, 211, 102, 0.2);
        }

        .edit-modal-save:hover {
            background: #22c55e;
            box-shadow: 0 4px 16px rgba(37, 211, 102, 0.3);
        }

        @media (max-width: 600px) {
            .edit-modal-content {
                padding: 18px 8px 18px 8px;
            }

            .edit-modal-bubble {
                max-width: 100%;
            }
        }

        .edit-modal-flex-row {
            display: flex;
            flex-direction: row;
            align-items: center;
            /* Cambiado para alinear verticalmente */
            gap: 12px;
            width: 100%;
        }

        .edit-modal-input {
            flex: 1 1 auto;
            min-width: 0;
        }

        .edit-modal-save {
            flex: 0 0 auto;
            height: 48px;
            margin-bottom: 0;
            align-self: center;
        }

        .edit-modal-textarea-container {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            align-items: flex-end;
        }

        .edit-modal-btn-container {
            display: flex;
            align-items: flex-end;
        }

        .edit-modal-input {
            width: 100%;
            min-height: 40px;
            max-height: 160px;
            font-size: 1.13em;
            border: none;
            border-bottom: 2px solid #25d366;
            background: transparent;
            color: #fff;
            padding: 10px 0 6px 0;
            outline: none;
            resize: none;
            overflow-y: auto;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .edit-modal-save {
            height: 40px;
            min-width: 48px;
            border-radius: 50%;
            border: none;
            background: #174388;
            color: #fff;
            font-size: 1.8em;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(23, 67, 136, 0.2);
            margin-bottom: 2px;
        }

        .edit-modal-save:hover {
            background: #0e3470;
            box-shadow: 0 4px 16px rgba(23, 67, 136, 0.3);
        }
    </style>
</head>

<body>
    <div class="cabecera">
        <button type="button" id="logoButton">
            <!-- <img src="css/logoazul.png" alt="Logo">-->
            <img src="css/menu.png" alt="Menú" class="logo-menu">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>
    <?php include 'menu_alumno.php'; ?>
    <div class="chat-dashboard-area">
        <?php
        // Sidebar de materias
        require 'conexion.php';
        $id_usuario = $_SESSION['idusuario'];
        $materias = [];
        $materia_actual = isset($_SESSION['idmateria']) ? $_SESSION['idmateria'] : null;
        $sql = "SELECT m.id, m.nombre, m.seccion FROM inscripciones i JOIN materias m ON i.id_materia = m.id WHERE i.id_estudiante = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $materias[] = $row;
        }
        $stmt->close();
        // Seleccionar materia por defecto si no hay una seleccionada y hay materias
        if (!$materia_actual && count($materias) > 0) {
            $_SESSION['idmateria'] = $materias[0]['id'];
            $materia_actual = $materias[0]['id'];
        }
        ?>
        <div class="sidebar-materias">
            <h2>Mis Materias</h2>
            <div class="lista-materias">
                <?php foreach ($materias as $mat): ?>
                    <div class="materia-item<?php if ($materia_actual == $mat['id'])
                        echo ' selected'; ?>"
                        onclick="window.location.href='dirigirchat.php?valor=<?php echo $mat['id']; ?>'">
                        <?php echo htmlspecialchars($mat['nombre']) . ' (' . htmlspecialchars($mat['seccion']) . ')'; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($materias)): ?>
                    <div style="color:#888; text-align:center; margin-top:30px;">No tienes materias inscritas.</div>
                <?php endif; ?>
            </div>
        </div>
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
                    <?php $sin_materias = empty($materias); ?>
                    <div class="upload-wrapper">
                        <button id="upload-button" class="button" title="Subir archivo o imagen" <?php if ($sin_materias)
                            echo 'disabled style="opacity:0.5;cursor:not-allowed;"'; ?>>
                            <img src="css/plus-pequeno.png" alt="Upload">
                        </button>
                        <div id="upload-menu">
                            <div class="upload-option" id="upload-image">Subir Imagen</div>
                            <div class="upload-option" id="upload-file">Subir Archivo</div>
                        </div>
                    </div>
                    <input type="text" id="message"
                        placeholder="<?php echo $sin_materias ? 'No tienes materias disponibles' : 'Escribe un mensaje...'; ?>"
                        maxlength="1000" autocomplete="off" <?php if ($sin_materias)
                            echo 'disabled style="background:#eee;cursor:not-allowed;"'; ?> />
                    <button id="send-button" class="button" title="Enviar mensaje" <?php if ($sin_materias)
                        echo 'disabled style="opacity:0.5;cursor:not-allowed;"'; ?>>
                        <img src="css/enviar-mensaje.png" alt="Send">
                    </button>
                    <input type="file" id="imageInput" accept="image/*" style="display: none;" <?php if ($sin_materias)
                        echo 'disabled'; ?>>
                    <input type="file" id="fileInput" accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf"
                        style="display: none;" <?php if ($sin_materias)
                            echo 'disabled'; ?>>
                    <button id="call-button" class="button" title="Llamada" <?php if ($sin_materias)
                        echo 'disabled style="opacity:0.5;cursor:not-allowed;"'; ?>>
                        <img src="css/icons/meet.svg" alt="Call">
                    </button>
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
    <?php if ($advertencia) { ?>
        <div class="advertencia-flotante"
            style="position: fixed; top: 30px; right: 30px; z-index: 9999; background: #ffdddd; color: #a94442; border: 1px solid #a94442; border-radius: 8px; padding: 16px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-weight: bold;">
            <p style="margin:0;"><?php echo htmlspecialchars($advertencia); ?></p>
        </div>
        <script>
            setTimeout(function () {
                var adv = document.querySelector('.advertencia-flotante');
                if (adv) adv.style.display = 'none';
            }, 3500);
        </script>
    <?php } ?>

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
                    <textarea class="edit-modal-input" id="edit-modal-input" maxlength="1000"
                        placeholder="Escribe el nuevo mensaje..."></textarea>
                </div>
                <div class="edit-modal-btn-container" style="display:flex;align-items:flex-end;">
                    <button class="edit-modal-save" id="edit-modal-save" title="Guardar edición">&#10003;</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 🔧 Configuración global
        const CONFIG = {
            maxMessageLength: 1000,
            maxImageSize: 50 * 1024 * 1024, // 50MB
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
        document.addEventListener('DOMContentLoaded', function () {
            initTheme();
            loadMessages();
            setupEventListeners();
            setupNavigation();
            // Deshabilitar inputs si no hay materias
            var sinMaterias = <?php echo json_encode($sin_materias); ?>;
            if (sinMaterias) {
                document.getElementById('message').disabled = true;
                document.getElementById('send-button').disabled = true;
                document.getElementById('upload-button').disabled = true;
                document.getElementById('call-button').disabled = true;
            }
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
                themeSwitch.addEventListener('change', function () {
                    setTheme(this.checked ? 'dark' : 'light');
                });
            }

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
            document.addEventListener('click', function (event) {
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

            sendButton.addEventListener('click', function () {
                console.log('Botón ENVIAR presionado');
                sendMessage();
            });

            messageInput.addEventListener('keypress', function (e) {
                if (e.which === 13) {
                    console.log('Enter presionado en input de mensaje');
                    sendMessage();
                    return false;
                }
            });

            cancelReplyButton.addEventListener('click', function () {
                console.log('Botón CANCELAR RESPUESTA presionado');
                hideReplyPreview();
            });

            // Respuestas
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
                console.log('Botón RESPONDER mensaje presionado, id:', messageId, 'tipo:', messageType);
                showReplyPreview(userName, messageContent, messageId, messageType, fileName);
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

            uploadButton.addEventListener('click', function (event) {
                console.log('Botón SUBIR (plus) presionado');
                uploadMenu.classList.toggle('show');
                event.stopPropagation();
            });

            document.addEventListener('click', function (event) {
                if (!uploadMenu.contains(event.target) && !uploadButton.contains(event.target)) {
                    uploadMenu.classList.remove('show');
                }
            });

            uploadImage.addEventListener('click', function () {
                console.log('Opción SUBIR IMAGEN presionada');
                imageInput.click();
            });
            uploadFile.addEventListener('click', function () {
                console.log('Opción SUBIR ARCHIVO presionada');
                fileInput.click();
            });

            imageInput.addEventListener('change', function (e) {
                console.log('Archivo de imagen seleccionado');
                handleImageUpload(e);
            });
            fileInput.addEventListener('change', function (e) {
                console.log('Archivo de documento seleccionado');
                handleFileUpload(e);
            });

            // Botón de llamada
            const callButton = document.getElementById('call-button');
            if (callButton) {
                callButton.addEventListener('click', function () {
                    console.log('Botón LLAMADA presionado');
                });
            }
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
                .done(function (data) {
                    try {
                        const response = JSON.parse(data);
                        if (response.success) {
                            $('#message').val('');
                            hideReplyPreview();
                            loadMessages();
                            // Forzar scroll al final después de enviar
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

            uploadFileWithProgress(file, 'image');
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
                showError('El archivo es demasiado grande (máximo 10MB), file.size: ' + file.size + ' bytes');
                return;
            }

            uploadFileWithProgress(file, 'file');
        }

        // 📤 Subir archivo genérico con progreso
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
            xhr.open('POST', 'chat.php', true);

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

        // 🔄 Cargar mensajes
        let menuPuntosAbierto = null; // Guardar el ID del menú abierto
        // Sobrescribo mostrarMenuPuntos para guardar el menú abierto
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

        // Modifico loadMessages para reabrir el menú si corresponde
        function loadMessages() {
            $.get('load_messages.php')
                .done(function (data) {
                    $('#chat-box').html(data);
                    autoScroll();
                    // Reabrir menú si estaba abierto
                    if (menuPuntosAbierto) {
                        setTimeout(() => {
                            const btn = document.querySelector('.menu-puntos-btn[onclick*="' + menuPuntosAbierto + '"]');
                            if (btn) btn.click();
                        }, 100); // Espera para asegurar que el DOM esté listo
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Error loading messages:', error);
                });
        }



        // 🗑️ Eliminar mensaje
        function deleteMessage(messageId) {
            if (confirm('¿Estás seguro de que quieres eliminar este mensaje? Esta acción no se puede deshacer.')) {
                $.post('delete_message.php', {
                    message_id: messageId
                })
                    .done(function (data) {
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
                    .fail(function (xhr, status, error) {
                        showError('Error de conexión: ' + error);
                    });
            }
        }

        // 📍 Auto-scroll
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

        // 📍 Forzar scroll al final (sin condiciones)
        function forceScrollToBottom() {
            $('.chat-dashboard-messages').animate({
                scrollTop: $('.chat-dashboard-messages')[0].scrollHeight
            }, 'fast');
        }

        // 🔄 Actualizar mensajes cada 2 segundos
        setInterval(loadMessages, 2000);

        // 🎯 Funciones auxiliares
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
            alert(message); // Mejorar con una notificación más elegante
        }

        // Deshabilitar el botón de enviar si el input está vacío
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
        // Refuerza el bloqueo al limpiar el input o cancelar respuesta
        function forceDisableSendButton() {
            setTimeout(toggleSendButton, 10);
        }
        document.getElementById('cancel-reply').addEventListener('click', forceDisableSendButton);
        messageInput.addEventListener('change', toggleSendButton);
        messageInput.addEventListener('keyup', toggleSendButton);

        // Modal de imagen ampliada
        function setupImageModal() {
            const modal = document.getElementById('image-modal');
            const modalImg = modal.querySelector('.modal-img');
            const closeBtn = modal.querySelector('.close-modal');
            // Delegación para todas las imágenes del chat
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
            // Cerrar con ESC
            document.addEventListener('keydown', function (e) {
                if (modal.classList.contains('show') && e.key === 'Escape') {
                    modal.classList.remove('show');
                    modalImg.src = '';
                }
            });
        }
        document.addEventListener('DOMContentLoaded', setupImageModal);

        // Modal de edición de mensaje
        function setupEditModal() {
            const modal = document.getElementById('edit-modal');
            const original = document.getElementById('edit-modal-original');
            const input = document.getElementById('edit-modal-input');
            const saveBtn = document.getElementById('edit-modal-save');
            const closeBtn = document.getElementById('edit-modal-close');
            let editingId = null;

            // Delegación para botón editar
            $(document).on('click', '.menu-puntos-opcion', function () {
                if ($(this).text().trim().toLowerCase() === 'editar') {
                    const messageId = $(this).closest('.menu-puntos').attr('id').replace('menu-puntos-', '');
                    const messageElem = document.getElementById('message-text-' + messageId);
                    if (!messageElem) return;
                    const originalText = messageElem.textContent;
                    original.textContent = originalText; // La burbuja solo muestra el mensaje original
                    input.value = originalText; // El textarea tiene el mensaje editable
                    input.placeholder = 'Escribe el nuevo mensaje...'; // Placeholder fijo
                    editingId = messageId;
                    modal.classList.add('show');
                    input.focus();
                    // Ajustar altura del textarea al abrir
                    input.style.height = 'auto';
                    input.style.height = Math.min(input.scrollHeight, 160) + 'px';
                }
            });
            // Guardar edición
            saveBtn.addEventListener('click', function () {
                const nuevoTexto = input.value.trim();
                if (nuevoTexto.length === 0) {
                    alert('El mensaje no puede estar vacío');
                    return;
                }
                // AJAX para actualizar el mensaje
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
            // Cerrar modal al hacer clic fuera
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    editingId = null;
                }
            });

            // Cerrar con botón X
            closeBtn.addEventListener('click', function () {
                modal.classList.remove('show');
                editingId = null;
            });

            // Cerrar con ESC
            document.addEventListener('keydown', function (e) {
                if (modal.classList.contains('show') && e.key === 'Escape') {
                    modal.classList.remove('show');
                    editingId = null;
                }
            });
        }
        document.addEventListener('DOMContentLoaded', setupEditModal);

        // --- Autoajuste de altura del textarea en el modal de edición ---
        document.addEventListener('DOMContentLoaded', function () {
            const editInput = document.getElementById('edit-modal-input');
            if (editInput) {
                editInput.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 160) + 'px';
                });
                // Ajustar altura al abrir el modal si ya tiene texto
                editInput.style.height = 'auto';
                editInput.style.height = Math.min(editInput.scrollHeight, 160) + 'px';
            }
        });

        // --- MENÚ DE 3 PUNTOS FLOTANTE ---
        document.addEventListener('click', function (e) {
            // Abrir menú flotante al hacer click en los 3 puntos
            if (e.target.classList.contains('menu-puntos-btn')) {
                const btn = e.target;
                const messageId = btn.getAttribute('data-message-id');
                const messageType = btn.getAttribute('data-message-type');
                const userName = btn.getAttribute('data-username');
                // Elimina menú anterior si existe
                const oldMenu = document.getElementById('menu-puntos-flotante');
                if (oldMenu) oldMenu.remove();
                // Crea el menú
                const menu = document.createElement('div');
                menu.id = 'menu-puntos-flotante';
                menu.className = 'menu-puntos-flotante';
                menu.innerHTML = `
                    <button class=\"menu-puntos-opcion\" onclick=\"responderMensajeConTipo(${messageId}, '${userName.replace(/'/g, "&#39;")}', '${messageType}')\">Responder</button>
                    ${messageType === 'texto' ? `<button class=\"menu-puntos-opcion\" onclick=\"editarMensaje(${messageId})\">Editar</button>` : ''}
                    <button class=\"menu-puntos-opcion\" onclick=\"eliminarMensaje(${messageId})\">Eliminar</button>
                `;
                document.body.appendChild(menu);
                // Posiciona el menú de forma absoluta respecto al documento
                function updateMenuPosition() {
                    const rect = btn.getBoundingClientRect();
                    menu.style.position = 'absolute';
                    menu.style.top = (window.scrollY + rect.bottom + 4) + 'px';
                    menu.style.left = (window.scrollX + rect.left) + 'px';
                    menu.style.zIndex = 99999;
                }
                updateMenuPosition();
                window.addEventListener('scroll', updateMenuPosition);
                window.addEventListener('resize', updateMenuPosition);
                // Cierra al hacer click fuera
                setTimeout(() => {
                    document.addEventListener('mousedown', function handler(ev) {
                        if (!menu.contains(ev.target) && ev.target !== btn) {
                            menu.remove();
                            window.removeEventListener('scroll', updateMenuPosition);
                            window.removeEventListener('resize', updateMenuPosition);
                            document.removeEventListener('mousedown', handler);
                        }
                    });
                }, 10);
                // Evita scroll automático al abrir menú
                e.stopPropagation();
                e.preventDefault();
            }
        });

        // Nueva función para responder con tipo de mensaje y nombre de usuario
        function responderMensajeConTipo(messageId, userName, messageType) {
            // Busca el contenido del mensaje según el tipo
            let messageContent = '';
            let fileName = '';
            if (messageType === 'texto') {
                const elem = document.getElementById('message-text-' + messageId);
                if (elem) messageContent = elem.textContent;
            } else if (messageType === 'imagen') {
                const elem = document.getElementById('message-img-' + messageId);
                if (elem) messageContent = elem.getAttribute('src');
            } else if (messageType === 'archivo') {
                const fileElem = document.getElementById('message-file-' + messageId);
                if (fileElem) {
                    messageContent = fileElem.getAttribute('href');
                    const span = fileElem.querySelector('span');
                    if (span) fileName = span.textContent;
                }
            }
            showReplyPreview(userName, messageContent, messageId, messageType, fileName);
            // Cierra el menú flotante si está abierto
            const menu = document.getElementById('menu-puntos-flotante');
            if (menu) menu.remove();
        }

        // Agrega estilos para el menú flotante (idénticos al menú anterior)
        const style = document.createElement('style');
        style.innerHTML = `
.menu-puntos-flotante {
    background: #fff !important;
    border: 1.5px solid #fff !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 16px rgba(33, 53, 85, 0.13) !important;
    min-width: 180px !important;
    padding: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    position: absolute !important;
    z-index: 99999 !important;
}
.menu-puntos-flotante .menu-puntos-opcion {
    padding: 10px 18px !important;
    cursor: pointer !important;
    background: none !important;
    border: none !important;
    text-align: left !important;
    font-size: 15px !important;
    color: #213555 !important;
    transition: background 0.2s !important;
}
.menu-puntos-flotante .menu-puntos-opcion:hover {
    background: #f4f8fb !important;
}
body.dark-mode .menu-puntos-flotante {
    background: #232323 !important;
    border: 1px solid #444 !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.32) !important;
}
body.dark-mode .menu-puntos-flotante .menu-puntos-opcion {
    color: #e0e0e0 !important;
}
body.dark-mode .menu-puntos-flotante .menu-puntos-opcion:hover {
    background: #333 !important;
}
`;
        document.head.appendChild(style);
    </script>


</body>

</html>