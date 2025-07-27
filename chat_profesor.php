<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_PROFESOR);

include 'comprobar_sesion.php';
require 'conexion.php';
actualizar_actividad();

// --- INICIO BLOQUE BARRA LATERAL DE MATERIAS ---
$id_usuario = $_SESSION['idusuario'];
$profesor_id = null;
// Obtener id del profesor
$sql_profesor = "SELECT id FROM profesores WHERE id_usuario = ? LIMIT 1";
$stmt_profesor = $conn->prepare($sql_profesor);
if ($stmt_profesor) {
    $stmt_profesor->bind_param("i", $id_usuario);
    $stmt_profesor->execute();
    $stmt_profesor->bind_result($profesor_id);
    $stmt_profesor->fetch();
    $stmt_profesor->close();
}
$materias = [];
$materia_actual = isset($_SESSION['idmateria']) ? $_SESSION['idmateria'] : null;
if ($profesor_id) {
    $sql = "SELECT id, nombre, seccion FROM materias WHERE id_profesor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $profesor_id);
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
}
$sin_materias = empty($materias);
// --- FIN BLOQUE BARRA LATERAL DE MATERIAS ---

// Obtener el nombre y sección de la materia
if (!isset($_SESSION['idmateria']) || empty($_SESSION['idmateria'])) {
    // Si después de intentar seleccionar una por defecto, sigue sin haber materia,
    // es porque el profesor no tiene materias asignadas.
    // No mostramos error, la interfaz ya manejará el estado "sin_materias".
} else {
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
}

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
        if (preg_match('/^[\p{L}\p{N}\s\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2300}-\x{23FF}\x{2B50}\x{1F004}\x{1F0CF}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F1E6}-\x{1F1FF}\x{1F201}-\x{1F251}\x{200D}\x{FE0F}\!\?\.,]+$/u', $message)) {
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
    <link rel="stylesheet" href="css/principalprofesor.css">
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

        .chat-dashboard-main {
            background: #fff;
            width: 100vw;
            height: 100%;
            margin: 32px 32px 32px 0;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(33, 53, 85, 0.10);
            display: flex;
            flex-direction: column;
            position: relative;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        body.dark-mode .chat-dashboard-main {
            background: #1e1e1e;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .consulta-button {
            background-color: #174388;
            position: relative;
            padding: 12px 16px;
            border-radius: 100px;
            top: 10px;
            left: 10px;
            width: 75px;
            max-width: 260px;
            max-height: 75px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            cursor: pointer;
            transition: width 0.3s;
            gap: 0;
        }

        .consulta-button span {
            opacity: 0;
            visibility: hidden;
            max-width: 0;
            margin-right: 0;
            margin-left: 0;
            transition: opacity 0.2s, max-width 0.2s, margin-right 0.2s, margin-left 0.2s, visibility 0.2s;
            white-space: nowrap;
            color: #fff;
            font-weight: 500;
            font-size: 1em;
        }

        .consulta-button:hover {
            width: 250px;
        }

        .consulta-button:hover span {
            opacity: 1;
            visibility: visible;
            max-width: 160px;
            margin-right: 12px;
            margin-left: 0;
        }

        .consulta-button ion-icon {
            color: white;
            margin-left: 0;
            font-size: 1.6em;
            flex-shrink: 0;
        }

        .consulta-element{
            margin-bottom: 12px;
            padding: 16px 16px;
            background: #174388;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.2s, color 0.2s;
        }

        .consulta-link{
            color: #fff;
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
            .chat-dashboard-main {
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
            bottom: 80px !important;
            right: 10px !important;
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

        #edit-modal {
            display: none;
            position: fixed;
            z-index: 20010;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(24, 26, 27, 0.96);
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

        /* Asegurar que el botón de videollamada tenga el mismo estilo que .button */
        #video-call-button.button {
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
            margin-left: 0;
            margin-right: 0;
            padding: 0;
        }

        #video-call-button.button:active {
            transform: scale(0.95);
        }

        #video-call-button.button img {
            width: 28px;
            height: 28px;
        }

        .msg-foto {
            cursor: pointer !important;
        }

        .reply-preview-inside {
            cursor: pointer !important;
        }

        .highlight-reply {
            box-shadow: 0 0 0 3px #2196f3, 0 2px 8px rgba(33, 53, 85, 0.10) !important;
            transition: box-shadow 0.3s;
        }
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
        <?php
        // Sidebar de materias (idéntico a alumnos, pero para profesores)
        $sin_materias = empty($materias);
        ?>
        <div class="sidebar-materias">
            <h2>Mis Materias</h2>
            <div class="lista-materias">
                <?php foreach ($materias as $mat): ?>
                    <div class="materia-item<?php if ($materia_actual == $mat['id'])
                        echo ' selected'; ?>"
                        onclick="window.location.href='dirigirchat_profesores.php?valor=<?php echo $mat['id']; ?>'">
                        <?php echo htmlspecialchars($mat['nombre']) . ' (' . htmlspecialchars($mat['seccion']) . ')'; ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($sin_materias): ?>
                    <div style="color:#888; text-align:center; margin-top:30px;">No tienes materias asignadas.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="chat-dashboard-main">
            <div class="consulta-button" id="consultaButton">
                <span>Material de consulta</span>
                <ion-icon name="book-outline"></ion-icon>
            </div>
            <!-- Modal para material de consulta -->
            <div id="consulta-modal" class="consulta-modal">
                <div class="consulta-modal-content">
                    <button class="consulta-modal-close" id="consulta-modal-close" title="Cerrar">&times;</button>
                    <h2>Material de consulta</h2>
                    <div class="archivos-enviados">
                        <?php
                        // Mostrar archivos enviados en el chat de la materia actual
                        if (isset($materia_actual)) {
                            $stmt_archivos = $conn->prepare("SELECT message, created_at FROM messages WHERE group_id = ? AND tipo = 'archivo' ORDER BY created_at DESC");
                            $stmt_archivos->bind_param("i", $materia_actual);
                            $stmt_archivos->execute();
                            $result_archivos = $stmt_archivos->get_result();
                            if ($result_archivos->num_rows > 0) {
                                while ($archivo = $result_archivos->fetch_assoc()) {
                                    $nombre_archivo = basename($archivo['message']);
                                    $fecha = date('d/m/Y H:i', strtotime($archivo['created_at']));
                                    // Determinar el icono según la extensión
                                    $ext = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
                                    $icono = '';
                                    if (in_array($ext, ['doc', 'docx'])) {
                                        $icono = 'css/word.png';
                                    } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                        $icono = 'css/excel.png';
                                    } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                        $icono = 'css/powerpoint.png';
                                    } elseif ($ext === 'pdf') {
                                        $icono = 'css/pdf.png';
                                    }
                                    echo '<div class="consulta-element">';
                                    if ($icono) {
                                        echo '<img src="' . $icono . '" alt="icono archivo" style="width:38px;height:38px;vertical-align:middle;margin-right:12px;">';
                                    }
                                    echo '<a href="' . htmlspecialchars($archivo['message']) . '" target="_blank" class="consulta-link">' . htmlspecialchars($nombre_archivo) . '</a>';
                                    echo '<span class="consulta-fecha">(' . $fecha . ')</span>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div style="color:#888;">No hay archivos enviados en este chat.</div>';
                            }
                            $stmt_archivos->close();
                        }
                        ?>
                    </div>
                </div>
            </div>
    <style>
    .consulta-modal {
        display: none;
        position: fixed;
        z-index: 20010;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(24, 26, 27, 0.65);
        align-items: center;
        justify-content: center;
        animation: fadeInModal 0.25s;
    }
    .consulta-modal.show {
        display: flex !important;
    }
    .consulta-modal-content {
        background: #fff;
        border-radius: 18px;
        width: 50%;
        height: 75%;
        box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        position: relative;
        padding: 40px 32px;
        display: flex;
        flex-flow: row wrap;
        align-items: center;
        justify-content: center;
    }

    .consulta-modal-content h2{
        margin-bottom: 16px;
    }

    .consulta-modal-content .archivos-enviados{
        width: 100%;
        height: 100%;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    body.dark-mode .consulta-modal-content {
        background: #23263a;
        color: #fff;
    }

    .consulta-modal-close {
        position: absolute;
        top: 18px;
        right: 24px;
        background: none;
        border: none;
        color: #888;
        font-size: 2.2em;
        cursor: pointer;
        opacity: 0.7;
        z-index: 1;
        transition: color 0.2s, opacity 0.2s;
    }
    .consulta-modal-close:hover {
        color: #e74c3c;
        opacity: 1;
    }
    </style>
    <script>
    // Modal para material de consulta: cerrar con la X, click fuera o ESC
    document.addEventListener('DOMContentLoaded', function() {
        const consultaBtn = document.getElementById('consultaButton');
        const modal = document.getElementById('consulta-modal');
        const closeBtn = document.getElementById('consulta-modal-close');
        if (consultaBtn && modal) {
            consultaBtn.addEventListener('click', function(e) {
                modal.classList.add('show');
            });
            // Cerrar modal al hacer click fuera del contenido
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
            // Cerrar con ESC
            document.addEventListener('keydown', function(e) {
                if (modal.classList.contains('show') && e.key === 'Escape') {
                    modal.classList.remove('show');
                }
            });
            // Cerrar con la X
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    modal.classList.remove('show');
                });
            }
        }
    });
    </script>
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
                            echo 'disabled style=\"opacity:0.5;cursor:not-allowed;\"'; ?>>
                            <img src="css/plus-pequeno.png" alt="Upload">
                        </button>
                        <div id="upload-menu">
                            <div class="upload-option" id="upload-image">Subir Imagen</div>
                            <div class="upload-option" id="upload-file">Subir Archivo</div>
                        </div>
                    </div>
                    <input type="text" id="message"
                        placeholder="<?php echo $sin_materias ? 'No tienes materias disponibles' : 'Escribe un mensaje...'; ?>"
                        maxlength="250" autocomplete="off" <?php if ($sin_materias)
                            echo 'disabled style=\"background:#eee;cursor:not-allowed;\"'; ?> />
                    <button id="send-button" class="button" title="Enviar mensaje" <?php if ($sin_materias)
                        echo 'disabled style=\"opacity:0.5;cursor:not-allowed;\"'; ?>>
                        <img src="css/enviar-mensaje.png" alt="Send">
                    </button>
                    <input type="file" id="imageInput" accept="image/*" style="display: none;" <?php if ($sin_materias)
                        echo 'disabled'; ?>>
                    <input type="file" id="fileInput" accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf"
                        style="display: none;" <?php if ($sin_materias)
                            echo 'disabled'; ?>>
                    <a id="video-call-button" class="button llamada" href="videollamada_profesor.php"
                        title="Videollamada" <?php if ($sin_materias)
                        echo 'style=\"opacity:0.5;cursor:not-allowed;\"'; ?>>
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
                    <textarea class="edit-modal-input" id="edit-modal-input" maxlength="250"
                        placeholder="Escribe el nuevo mensaje..."></textarea>
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
            // Cierra el menú flotante si está abierto
            const menu = document.getElementById('menu-puntos-flotante');
            if (menu) menu.remove();
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
                // Usar el id global si existe, si no el local
                const idToEdit = (typeof editingId !== 'undefined' && editingId !== null) ? editingId : window._editingMessageId;
                if (!idToEdit) {
                    alert('No se pudo identificar el mensaje a editar');
                    return;
                }
                fetch('editar_mensaje.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${idToEdit}&nuevo_texto=${encodeURIComponent(nuevoTexto)}`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            modal.classList.remove('show');
                            if (typeof editingId !== 'undefined') editingId = null;
                            window._editingMessageId = null;
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
                    window._editingMessageId = null;
                }
            });
            closeBtn.addEventListener('click', function () {
                modal.classList.remove('show');
                editingId = null;
                window._editingMessageId = null;
            });
            document.addEventListener('keydown', function (e) {
                if (modal.classList.contains('show') && e.key === 'Escape') {
                    modal.classList.remove('show');
                    editingId = null;
                    window._editingMessageId = null;
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
                // Detectar si el mensaje es del usuario actual o de otro
                let isCurrentUser = false;
                let parent = btn.parentElement;
                while (parent) {
                    if (parent.classList && parent.classList.contains('message-container-flex')) {
                        isCurrentUser = parent.classList.contains('current-user');
                        break;
                    }
                    parent = parent.parentElement;
                }
                // Crea el menú
                const menu = document.createElement('div');
                menu.id = 'menu-puntos-flotante';
                menu.className = 'menu-puntos-flotante';
                // Opciones del menú según el usuario
                let menuHtml = '';
                menuHtml += `<button class=\"menu-puntos-opcion\" onclick=\"responderMensajeConTipo(${messageId}, '${userName.replace(/'/g, "&#39;")}', '${messageType}')\">Responder</button>`;
                if (isCurrentUser) {
                    if (messageType === 'texto') {
                        menuHtml += `<button class=\"menu-puntos-opcion\" onclick=\"editarMensaje(${messageId})\">Editar</button>`;
                    }
                    menuHtml += `<button class=\"menu-puntos-opcion\" onclick=\"eliminarMensaje(${messageId})\">Eliminar</button>`;
                }
                menu.innerHTML = menuHtml;
                document.body.appendChild(menu);
                // Posiciona el menú igual que el menú anterior
                function updateMenuPosition() {
                    const rect = btn.getBoundingClientRect();
                    menu.style.position = 'fixed';
                    menu.style.zIndex = 99999;
                    // Medidas del menú
                    menu.style.visibility = 'hidden';
                    menu.style.display = 'block';
                    const menuWidth = menu.offsetWidth;
                    const menuHeight = menu.offsetHeight;
                    menu.style.visibility = '';
                    menu.style.display = '';
                    // Ajustar posición según lado
                    if (isCurrentUser) {
                        // Usuario actual: menú a la izquierda del botón
                        menu.style.left = (rect.left - menuWidth - 8) + 'px';
                        menu.style.top = (rect.top - menuHeight + rect.height + 8) + 'px';
                    } else {
                        // Otro usuario: menú a la derecha del botón
                        menu.style.left = (rect.right + 8) + 'px';
                        menu.style.top = (rect.top - menuHeight + rect.height + 8) + 'px';
                    }
                }
                updateMenuPosition();
                // Al hacer scroll en el chat o resize, cerrar el menú
                const chatBox = document.getElementById('chat-box');
                function closeMenuOnScrollOrResize() {
                    if (menu.parentNode) menu.remove();
                    if (chatBox) chatBox.removeEventListener('scroll', closeMenuOnScrollOrResize);
                    window.removeEventListener('resize', closeMenuOnScrollOrResize);
                }
                if (chatBox) chatBox.addEventListener('scroll', closeMenuOnScrollOrResize);
                window.addEventListener('resize', closeMenuOnScrollOrResize);
                // Cierra al hacer click fuera
                setTimeout(() => {
                    document.addEventListener('mousedown', function handler(ev) {
                        if (!menu.contains(ev.target) && ev.target !== btn) {
                            menu.remove();
                            if (chatBox) chatBox.removeEventListener('scroll', closeMenuOnScrollOrResize);
                            window.removeEventListener('resize', closeMenuOnScrollOrResize);
                            document.removeEventListener('mousedown', handler);
                        }
                    });
                }, 10);
                // Evita scroll automático al abrir menú
                e.stopPropagation();
                e.preventDefault();
            }
        });
        // Función global para editar mensaje desde el menú flotante
        function editarMensaje(id) {
            // Cierra el menú flotante si está abierto
            const menu = document.getElementById('menu-puntos-flotante');
            if (menu) menu.remove();
            // Busca el elemento del mensaje
            const messageElem = document.getElementById('message-text-' + id);
            if (!messageElem) return;
            const originalText = messageElem.textContent;
            // Abre el modal de edición
            const modal = document.getElementById('edit-modal');
            const original = document.getElementById('edit-modal-original');
            const input = document.getElementById('edit-modal-input');
            original.textContent = originalText;
            input.value = originalText;
            input.placeholder = 'Escribe el nuevo mensaje...';
            modal.classList.add('show');
            input.focus();
            // Ajustar altura del textarea
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 160) + 'px';
            // Guarda el id que se está editando
            window._editingMessageId = id;
        }
        function eliminarMensaje(id) {
            // Cierra el menú flotante si está abierto
            const menu = document.getElementById('menu-puntos-flotante');
            if (menu) menu.remove();
            // Simula click en el botón original (que está oculto)
            document.querySelector('.delete-button[data-message-id="' + id + '"]').click();
        }
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
            position: fixed !important;
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
        // Cambia la posición del menú de subida (upload-menu) para que use left: 1px en vez de right
        const uploadMenuStyle = document.createElement('style');
        uploadMenuStyle.innerHTML = `
#upload-menu {
    left: 1px !important;
    right: auto !important;
}`;
        document.head.appendChild(uploadMenuStyle);
    </script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
    // Modal para material de consulta
    document.addEventListener('DOMContentLoaded', function() {
        const consultaBtn = document.getElementById('consultaButton');
        const modal = document.getElementById('consulta-modal');
        if (consultaBtn && modal) {
            consultaBtn.addEventListener('click', function(e) {
                modal.classList.add('show');
            });
            // Cerrar modal al hacer click fuera del contenido
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
            // Cerrar con ESC
            document.addEventListener('keydown', function(e) {
                if (modal.classList.contains('show') && e.key === 'Escape') {
                    modal.classList.remove('show');
                }
            });
        }
    });
    </script>
</body>

</html>