<?php
include 'comprobar_sesion.php';
include 'conexion.php';
$conn->set_charset("utf8mb4");
$user_id = isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : 0;
$nivel_usuario = null;
// Permitir publicaciones sin archivo adjunto (solo pregunta o texto)
if ($user_id > 0) {
    $stmt_nivel = $conn->prepare("SELECT nivel_usuario FROM usuarios WHERE id = ? LIMIT 1");
    $stmt_nivel->bind_param("i", $user_id);
    $stmt_nivel->execute();
    $stmt_nivel->bind_result($nivel_usuario);
    $stmt_nivel->fetch();
    $stmt_nivel->close();
    // Solo actualizar la variable de sesión si el usuario es profesor y no administrador
    if ($nivel_usuario == 2 || $nivel_usuario === '2') {
        $_SESSION['nivelusu'] = 'profesor';
    } elseif ($nivel_usuario == 3 || $nivel_usuario === '3') {
        $_SESSION['nivelusu'] = 'administrador';
    } // Si es usuario normal, no cambiar nada
}
if ($nivel_usuario !== 'profesor') {
    echo '<div style="padding:40px;text-align:center;color:#a94442;font-weight:bold;">Acceso solo para profesores.</div>';
    exit();
}

// --- Soporte AJAX para recargar solo los comentarios de un archivo ---
if (isset($_GET['ajax']) && isset($_GET['archivo_id'])) {
    $archivo_id = intval($_GET['archivo_id']);
    // Obtener comentarios de ese archivo
    $comentarios = [];
    $stmt = $conn->prepare("SELECT c.*, u.nombre_usuario FROM comentarios c JOIN usuarios u ON c.id_usuario = u.id WHERE c.archivo_id = ? ORDER BY c.fecha ASC");
    $stmt->bind_param("i", $archivo_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $comentarios[] = $row;
    }
    $stmt->close();
    // Obtener todos los comentarios de todos los archivos de la materia para mantener el árbol
    $comentarios_archivo = [];
    // Buscar el id de la materia de este archivo
    $materia_id = null;
    $stmt_mat = $conn->prepare("SELECT materia_id FROM archivos WHERE id = ? LIMIT 1");
    $stmt_mat->bind_param("i", $archivo_id);
    $stmt_mat->execute();
    $stmt_mat->bind_result($materia_id);
    $stmt_mat->fetch();
    $stmt_mat->close();
    if ($materia_id) {
        // Buscar todos los archivos de esa materia
        $archivos_ids = [];
        $res_arch = $conn->query("SELECT id FROM archivos WHERE materia_id = " . intval($materia_id));
        while ($row = $res_arch->fetch_assoc()) {
            $archivos_ids[] = $row['id'];
        }
        if (!empty($archivos_ids)) {
            $ids_str = implode(',', array_map('intval', $archivos_ids));
            $sql_com = "SELECT c.*, u.nombre_usuario FROM comentarios c JOIN usuarios u ON c.id_usuario = u.id WHERE c.archivo_id IN ($ids_str) ORDER BY c.fecha ASC";
            $res_com = $conn->query($sql_com);
            while ($row = $res_com->fetch_assoc()) {
                $comentarios_archivo[$row['archivo_id']][] = $row;
            }
        }
    }
    // Renderizar solo la sección de comentarios
    echo '<div class="classroom-comments">';
    echo '<strong style="font-size:1em;">Comentarios:</strong>';
    if (!empty($comentarios)) {
        echo renderComentarios($comentarios, $archivo_id, $comentarios_archivo);
    } else {
        echo '<div style="color:#aaa;">Aún no hay comentarios.</div>';
    }
    // Formulario para agregar comentario principal (con data-archivo para AJAX)
    echo '<form class="classroom-comment-form" method="post" action="guardar_comentario_profesores.php" data-archivo="'.$archivo_id.'">';
    echo '<input type="hidden" name="comentario_archivo_id" value="'.$archivo_id.'">';
    echo '<input type="text" name="comentario_texto" maxlength="300" placeholder="Escribe un comentario..." required>';
    echo '<button type="submit">Comentar</button>';
    echo '</form>';
    echo '</div>';
    exit;
}


actualizar_actividad();

// Obtener materias que imparte el profesor
$materias_prof = [];
$sql_materias = "SELECT m.id, m.nombre FROM materias m WHERE m.id_profesor = (SELECT id FROM profesores WHERE id_usuario = ?)";
$stmt_mat = $conn->prepare($sql_materias);
$stmt_mat->bind_param("i", $user_id);
$stmt_mat->execute();
$res_mat = $stmt_mat->get_result();
while ($row = $res_mat->fetch_assoc()) {
    $materias_prof[] = $row;
}
$stmt_mat->close();

// Procesar edición de descripción de archivo
// Procesar edición de descripción y/o archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_archivo_id'])) {
    $editar_archivo_id = intval($_POST['editar_archivo_id']);
    $nueva_descripcion = isset($_POST['nueva_descripcion']) ? trim($_POST['nueva_descripcion']) : null;
    $nuevo_archivo = $_FILES['nuevo_archivo'] ?? null;
    $actualizar = false;
    $sql_update = "UPDATE archivos SET ";
    $params = [];
    $types = '';
    if ($nueva_descripcion !== null) {
        $sql_update .= "descripcion = ?";
        $params[] = $nueva_descripcion;
        $types .= 's';
        $actualizar = true;
    }
    if ($nuevo_archivo && $nuevo_archivo['error'] === UPLOAD_ERR_OK) {
        // Obtener ruta anterior
        $stmt_get = $conn->prepare("SELECT nombre_archivo FROM archivos WHERE id = ? AND usuario_id = ?");
        $stmt_get->bind_param("ii", $editar_archivo_id, $user_id);
        $stmt_get->execute();
        $stmt_get->bind_result($ruta_antigua);
        $stmt_get->fetch();
        $stmt_get->close();
        $nombre_archivo = basename($nuevo_archivo['name']);
        $ruta_destino = 'uploads/' . uniqid() . '_' . $nombre_archivo;
        if (move_uploaded_file($nuevo_archivo['tmp_name'], $ruta_destino)) {
            if ($actualizar) $sql_update .= ", ";
            $sql_update .= "nombre_archivo = ?";
            $params[] = $ruta_destino;
            $types .= 's';
            $actualizar = true;
            if ($ruta_antigua && file_exists($ruta_antigua)) {
                unlink($ruta_antigua);
            }
        }
    }
    if ($actualizar) {
        $sql_update .= " WHERE id = ? AND usuario_id = ?";
        $params[] = $editar_archivo_id;
        $params[] = $user_id;
        $types .= 'ii';
        $stmt_edit = $conn->prepare($sql_update);
        $stmt_edit->bind_param($types, ...$params);
        $stmt_edit->execute();
        $stmt_edit->close();
        $msg = ($nuevo_archivo && $nuevo_archivo['error'] === UPLOAD_ERR_OK) ? 'Archivo editado exitosamente' : 'Descripción editada exitosamente';
        echo '<div id="toast-edit" class="toast-success"><span class="toast-icon">✔️</span><span class="toast-msg">'.$msg.'</span><span class="toast-close" onclick="document.getElementById(\'toast-edit\').style.display=\'none\'">&times;</span></div><style>.toast-success { position: fixed; right: 32px; bottom: 32px; background: #e6f9ed; color: #1a7f4c; border-radius: 10px; box-shadow: 0 2px 12px rgba(33,53,85,0.13); padding: 18px 32px 18px 18px; font-size: 1.1em; z-index: 9999; display: flex; align-items: center; min-width: 260px; animation: toastIn 0.4s; } .toast-icon { font-size: 1.5em; margin-right: 12px; } .toast-msg { flex: 1; } .toast-close { margin-left: 16px; font-size: 1.3em; color: #1a7f4c; cursor: pointer; } @keyframes toastIn { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }</style><script>setTimeout(function(){ var t=document.getElementById("toast-edit"); if(t)t.style.display="none"; }, 3000);</script>';
    }
}

// Procesar eliminación de archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_archivo_id'])) {
    $eliminar_archivo_id = intval($_POST['eliminar_archivo_id']);
    if ($eliminar_archivo_id > 0) {
        // Eliminar archivo físico
        $stmt_get = $conn->prepare("SELECT nombre_archivo FROM archivos WHERE id = ? AND usuario_id = ?");
        $stmt_get->bind_param("ii", $eliminar_archivo_id, $user_id);
        $stmt_get->execute();
        $stmt_get->bind_result($ruta_archivo);
        $stmt_get->fetch();
        $stmt_get->close();
        if ($ruta_archivo && file_exists($ruta_archivo)) {
            unlink($ruta_archivo);
        }
        // Eliminar comentarios asociados
        $conn->query("DELETE FROM comentarios WHERE archivo_id = " . intval($eliminar_archivo_id));
        // Eliminar registro de archivo
        $stmt_del = $conn->prepare("DELETE FROM archivos WHERE id = ? AND usuario_id = ?");
        $stmt_del->bind_param("ii", $eliminar_archivo_id, $user_id);
        $stmt_del->execute();
        $stmt_del->close();
        echo '<div id="toast-success" class="toast-success">
                <span class="toast-icon">✔️</span>
                <span class="toast-msg">Publicación eliminada</span>
                <span class="toast-close" onclick="document.getElementById(\'toast-success\').style.display=\'none\'">&times;</span>
              </div>
              <style>
              .toast-success {
                  position: fixed;
                  right: 32px;
                  bottom: 32px;
                  background: #e6f9ed;
                  color: #1a7f4c;
                  border-radius: 10px;
                  box-shadow: 0 2px 12px rgba(33,53,85,0.13);
                  padding: 18px 32px 18px 18px;
                  font-size: 1.1em;
                  z-index: 9999;
                  display: flex;
                  align-items: center;
                  min-width: 260px;
                  animation: toastIn 0.4s;
              }
              .toast-icon {
                  font-size: 1.5em;
                  margin-right: 12px;
              }
              .toast-msg {
                  flex: 1;
              }
              .toast-close {
                  margin-left: 16px;
                  font-size: 1.3em;
                  color: #1a7f4c;
                  cursor: pointer;
              }
              @keyframes toastIn {
                  from { opacity: 0; transform: translateY(40px); }
                  to { opacity: 1; transform: translateY(0); }
              }
              </style>
              <script>setTimeout(function(){ var t=document.getElementById("toast-success"); if(t)t.style.display="none"; }, 3000);</script>';
    }
}

// Procesar subida de archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['materia_id'])) {
    $materia_id = intval($_POST['materia_id']);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $archivo = $_FILES['archivo'] ?? null;
    if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = basename($archivo['name']);
        $ruta_destino = 'uploads/' . uniqid() . '_' . $nombre_archivo;
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            $sql_insert = "INSERT INTO archivos (usuario_id, materia_id, nombre_archivo, descripcion) VALUES (?, ?, ?, ?)";
            $stmt_ins = $conn->prepare($sql_insert);
            $stmt_ins->bind_param("iiss", $user_id, $materia_id, $ruta_destino, $descripcion);
            $stmt_ins->execute();
            $stmt_ins->close();
            echo '<div id="toast-success" class="toast-success">
                    <span class="toast-icon">✔️</span>
                    <span class="toast-msg">Archivo subido correctamente</span>
                    <span class="toast-close" onclick="document.getElementById(\'toast-success\').style.display=\'none\'">&times;</span>
                  </div>
                  <style>
                  .toast-success {
                      position: fixed;
                      right: 32px;
                      bottom: 32px;
                      background: #e6f9ed;
                      color: #1a7f4c;
                      border-radius: 10px;
                      box-shadow: 0 2px 12px rgba(33,53,85,0.13);
                      padding: 18px 32px 18px 18px;
                      font-size: 1.1em;
                      z-index: 9999;
                      display: flex;
                      align-items: center;
                      min-width: 260px;
                      animation: toastIn 0.4s;
                  }
                  .toast-icon {
                      font-size: 1.5em;
                      margin-right: 12px;
                  }
                  .toast-msg {
                      flex: 1;
                  }
                  .toast-close {
                      margin-left: 16px;
                      font-size: 1.3em;
                      color: #1a7f4c;
                      cursor: pointer;
                  }
                  @keyframes toastIn {
                      from { opacity: 0; transform: translateY(40px); }
                      to { opacity: 1; transform: translateY(0); }
                  }
                  </style>
                  <script>setTimeout(function(){ var t=document.getElementById("toast-success"); if(t)t.style.display="none"; }, 3000);</script>';
        } else {
            echo '<div style="color:red;text-align:center;">Error al mover el archivo.</div>';
        }
    } else {
        echo '<div style="color:red;text-align:center;">Debes seleccionar un archivo válido para publicar.</div>';
    }
}

// Procesar comentario o respuesta enviada
$comentario_exito = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario_archivo_id'], $_POST['comentario_texto'])) {
    $archivo_id = intval($_POST['comentario_archivo_id']);
    $comentario = trim($_POST['comentario_texto']);
    $id_comentario_padre = isset($_POST['id_comentario_padre']) ? intval($_POST['id_comentario_padre']) : null;
    if ($archivo_id > 0 && $comentario !== '') {
        if ($id_comentario_padre) {
            $stmt_com = $conn->prepare("INSERT INTO comentarios (archivo_id, id_usuario, comentario, fecha, id_comentario_padre) VALUES (?, ?, ?, NOW(), ?)");
            $stmt_com->bind_param("iisi", $archivo_id, $user_id, $comentario, $id_comentario_padre);
        } else {
            $stmt_com = $conn->prepare("INSERT INTO comentarios (archivo_id, id_usuario, comentario, fecha) VALUES (?, ?, ?, NOW())");
            $stmt_com->bind_param("iis", $archivo_id, $user_id, $comentario);
        }
        $stmt_com->execute();
        $stmt_com->close();
        $comentario_exito = true;
    }
}

// Obtener archivos subidos por el profesor, agrupados por materia
$archivos_materias = [];
if (!empty($materias_prof)) {
    $ids = implode(',', array_map(fn($m) => $m['id'], $materias_prof));
    $sql_arch = "SELECT a.*, m.nombre AS materia_nombre FROM archivos a JOIN materias m ON a.materia_id = m.id WHERE a.materia_id IN ($ids) ORDER BY a.fecha_subida DESC";
    $res_arch = $conn->query($sql_arch);
    while ($row = $res_arch->fetch_assoc()) {
        $archivos_materias[$row['materia_id']][] = $row;
    }
}

// Obtener comentarios y respuestas por archivo
$comentarios_archivo = [];
if (!empty($archivos_materias)) {
    $archivo_ids = [];
    foreach ($archivos_materias as $archs) {
        foreach ($archs as $a) {
            $archivo_ids[] = $a['id'];
        }
    }
    if (!empty($archivo_ids)) {
        $ids_str = implode(',', array_map('intval', $archivo_ids));
        $sql_com = "SELECT c.*, u.nombre_usuario FROM comentarios c JOIN usuarios u ON c.id_usuario = u.id WHERE c.archivo_id IN ($ids_str) ORDER BY c.fecha ASC";
        $res_com = $conn->query($sql_com);
        while ($row = $res_com->fetch_assoc()) {
            // Agrupar por archivo y por id_comentario_padre
            $comentarios_archivo[$row['archivo_id']][] = $row;
        }
    }
}

// Función para mostrar comentarios y respuestas en árbol
function renderComentarios($comentarios, $archivo_id, $comentarios_archivo, $padre = null, $nivel = 0) {
    $html = '';
    // Crear un mapa de comentarios por ID para mostrar extractos de respuestas
    $mapa_por_id = [];
    foreach ($comentarios as $c) {
        $mapa_por_id[$c['id']] = $c;
    }
    foreach ($comentarios as $com) {
        if ((is_null($padre) && empty($com['id_comentario_padre'])) || (!is_null($padre) && $com['id_comentario_padre'] == $padre)) {
            $margen = $nivel * 24;
            $html .= '<div class="classroom-comment" style="margin-left:'.$margen.'px;">';
            $html .= '<span class="comment-autor">'.htmlspecialchars($com['nombre_usuario']).'</span> ';
            $html .= '<span class="comment-fecha">('.date('d/m/Y H:i', strtotime($com['fecha'])).')</span>';
            // Si es respuesta, mostrar a quién responde y extracto
            if (!empty($com['id_comentario_padre']) && isset($mapa_por_id[$com['id_comentario_padre']])) {
                $padreObj = $mapa_por_id[$com['id_comentario_padre']];
                $extracto = mb_substr(str_replace(["\r","\n"]," ", $padreObj['comentario']), 0, 30, 'UTF-8');
                if (mb_strlen($padreObj['comentario'], 'UTF-8') > 30) $extracto .= '...';
                $html .= '<span class="reply-to">↳ Respondiendo a @'.htmlspecialchars($padreObj['nombre_usuario']).': "'.htmlspecialchars($extracto).'"</span>';
            }
            $html .= '<br>';
            $html .= nl2br(htmlspecialchars($com['comentario']));
            // Botón para mostrar/ocultar el formulario de respuesta (AJAX)
            $html .= '<button type="button" class="btn-mostrar-respuesta reply-to" data-reply="'.$com['id'].'">Responder</button>';
            $html .= '<form class="classroom-comment-form reply-form" method="post" action="guardar_comentario_profesores.php" data-archivo="'.$archivo_id.'" data-padre="'.$com['id'].'" style="display:none;margin-top:6px;">';
            $html .= '<input type="hidden" name="comentario_archivo_id" value="'.$archivo_id.'">';
            $html .= '<input type="hidden" name="id_comentario_padre" value="'.$com['id'].'">';
            $html .= '<input type="text" name="comentario_texto" maxlength="300" placeholder="Responder..." required style="flex:1;">';
            $html .= '<button type="submit">Enviar</button>';
            $html .= '<button type="button" class="btn-cancelar-respuesta cancel-btn" style="margin-left:8px;">Cancelar</button>';
            $html .= '</form>';
            // Mostrar respuestas recursivamente
            $html .= renderComentarios($comentarios, $archivo_id, $comentarios_archivo, $com['id'], $nivel+1);
            $html .= '</div>';
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/principalunihub.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/foro.css">
    <link rel="stylesheet" href="css/horario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Foro Profesor - UniHub</title>
    <script src="js/control_inactividad.js"></script>
    <style>
    :root {
        --color-bg: #f8f9fa;
        --color-bg-alt: #fff;
        --color-text: #222;
        --color-text-alt: #555;
        --color-primary: #174388;
        --color-secondary: #1e5aa8;
        --color-accent: #ffd700;
        --color-border: #dee2e6;
        --color-border-strong: #174388;
        --color-comment-bg: #e3f2fd;
        --color-comment-border: #b6d4fe;
        --color-input-bg: #fff;
        --color-input-border: #ccc;
        --color-btn-bg: #174388;
        --color-btn-bg-alt: #888;
        --color-btn-text: #fff;
        --color-toast-bg: #e6f9ed;
        --color-toast-text: #1a7f4c;
        --color-toast-border: #20c997;
        --color-scrollbar: #e3f2fd;
        --color-scrollbar-thumb: #174388;
    }
    body.dark-mode {
        --color-bg: #1a1a2e;
        --color-bg-alt: #2d3748;
        --color-text: #f8f9fa;
        --color-text-alt: #a0aec0;
        --color-primary: #ffd700;
        --color-secondary: #4a5568;
        --color-accent: #ffd700;
        --color-border: #4a5568;
        --color-border-strong: #ffd700;
        --color-comment-bg: #2d3748;
        --color-comment-border: #4a5568;
        --color-input-bg: #23263a;
        --color-input-border: #4a5568;
        --color-btn-bg: #ffd700;
        --color-btn-bg-alt: #4a5568;
        --color-btn-text: #23263a;
        --color-toast-bg: #23263a;
        --color-toast-text: #ffd700;
        --color-toast-border: #ffd700;
        --color-scrollbar: #2d3748;
        --color-scrollbar-thumb: #ffd700;
    }

    body {
        background: var(--color-bg) !important;
        color: var(--color-text) !important;
    }
    .classroom-container {
        background: var(--color-bg-alt) !important;
        color: var(--color-text) !important;
        box-shadow: 0 4px 24px rgba(33,53,85,0.10);
        border-radius: 12px;
        padding: 24px 12px;
        margin: 32px auto;
        max-width: 900px;
    }
    .publicar-section, .classroom-posts-grid {
        background: var(--color-bg) !important;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(33,53,85,0.07);
        padding: 18px 16px;
        margin-bottom: 24px;
    }
    .publicar-section h2 {
        color: var(--color-primary);
        font-size: 1.3em;
        margin-bottom: 12px;
    }
    .publicar-section label, .publicar-section textarea, .publicar-section select, .publicar-section input[type="file"] {
        color: var(--color-text);
    }
    .publicar-section textarea, .publicar-section select, .publicar-section input[type="file"] {
        background: var(--color-input-bg);
        border: 1px solid var(--color-input-border);
        border-radius: 6px;
        padding: 6px 8px;
        margin-bottom: 8px;
        color: var(--color-text);
        width: 100%;
        font-size: 1em;
    }
    .publicar-section button {
        background: var(--color-btn-bg);
        color: var(--color-btn-text);
        border: none;
        border-radius: 6px;
        padding: 7px 18px;
        font-weight: 600;
        font-size: 1em;
        margin-top: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .publicar-section button:hover {
        background: var(--color-secondary);
        color: var(--color-accent);
    }
    .classroom-posts-grid {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    .classroom-post {
        background: var(--color-bg-alt);
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(33,53,85,0.07);
        padding: 18px 16px;
    }
    .post-header {
        color: var(--color-primary);
        font-weight: 600;
        font-size: 1.1em;
        margin-bottom: 8px;
    }
    .post-content strong {
        color: var(--color-secondary);
    }
    .classroom-comments {
        background: var(--color-comment-bg);
        border: 1px solid var(--color-comment-border);
        border-radius: 8px;
        padding: 12px 10px;
        margin-top: 10px;
        color: var(--color-text);
    }
    .classroom-comment-form input[type="text"] {
        background: var(--color-input-bg);
        border: 1px solid var(--color-input-border);
        color: var(--color-text);
        border-radius: 6px;
        padding: 5px 8px;
        margin-right: 8px;
        width: 60%;
    }
    .classroom-comment-form button {
        background: var(--color-btn-bg);
        color: var(--color-btn-text);
        border: none;
        border-radius: 6px;
        padding: 5px 14px;
        font-weight: 600;
        font-size: 1em;
        margin-left: 4px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .classroom-comment-form button:hover {
        background: var(--color-secondary);
        color: var(--color-accent);
    }
    .btn-mostrar-respuesta, .btn-cancelar-respuesta {
        background: var(--color-btn-bg-alt);
        color: var(--color-btn-text);
        border: none;
        border-radius: 6px;
        padding: 3px 10px;
        font-size: 0.95em;
        margin-left: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-mostrar-respuesta:hover, .btn-cancelar-respuesta:hover {
        background: var(--color-secondary);
        color: var(--color-accent);
    }
    .classroom-comment {
        background: transparent;
        border-left: 3px solid var(--color-border-strong);
        margin-bottom: 10px;
        padding: 6px 0 6px 12px;
        color: var(--color-text);
    }
    .comment-autor {
        font-weight: bold;
        color: var(--color-primary);
    }
    .comment-fecha {
        color: var(--color-text-alt);
        font-size: 0.9em;
    }
    .reply-to {
        color: var(--color-secondary);
        font-size: 0.95em;
        margin-left: 4px;
    }
    /* Toasts */
    .toast-success {
        position: fixed;
        right: 32px;
        bottom: 32px;
        background: var(--color-toast-bg);
        color: var(--color-toast-text);
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(33,53,85,0.13);
        padding: 18px 32px 18px 18px;
        font-size: 1.1em;
        z-index: 9999;
        display: flex;
        align-items: center;
        min-width: 260px;
        animation: toastIn 0.4s;
        border: 2px solid var(--color-toast-border);
    }
    .toast-icon {
        font-size: 1.5em;
        margin-right: 12px;
    }
    .toast-msg {
        flex: 1;
    }
    .toast-close {
        margin-left: 16px;
        font-size: 1.3em;
        color: var(--color-toast-text);
        cursor: pointer;
    }
    @keyframes toastIn {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Scrollbar personalizado para classroom-container */
    .classroom-container::-webkit-scrollbar {
        height: 10px;
        background: var(--color-scrollbar);
        border-radius: 8px;
    }
    .classroom-container::-webkit-scrollbar-thumb {
        background: var(--color-scrollbar-thumb);
        border-radius: 8px;
    }
    .classroom-container::-webkit-scrollbar-thumb:hover {
        background: var(--color-primary);
    }
    .classroom-container::-webkit-scrollbar-corner {
        background: var(--color-scrollbar);
    }
    .classroom-container {
        scrollbar-width: thin;
        scrollbar-color: var(--color-scrollbar-thumb) var(--color-scrollbar);
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
    <?php
    if (isset($_SESSION['nivelusu'])) {
        if ($_SESSION['nivelusu'] === 'profesor') {
            include 'menu_profesor.php';
        } elseif ($_SESSION['nivelusu'] === 'administrador') {
            // Puedes crear un menu_administrador.php si lo deseas, o mostrar el de profesor
            include 'menu_profesor.php';
        } else {
            include 'menu_alumno.php';
        }
    } else {
        include 'menu_alumno.php';
    }
    ?>
    <div class="classroom-container">
        <!-- Sección para publicar contenido (solo profesores) -->
        <div class="publicar-section">
            <h2>Publicar nuevo material</h2>
            <form id="post-form" method="post" enctype="multipart/form-data">
                <label for="materia_id">Materia:</label>
                <select name="materia_id" id="materia_id" required>
                    <option value="">Seleccione una materia</option>
                    <?php foreach ($materias_prof as $mat): ?>
                        <option value="<?php echo $mat['id']; ?>"><?php echo htmlspecialchars($mat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="descripcion" id="descripcion" rows="2" placeholder="Descripción o instrucciones..." required></textarea>
                <input type="file" name="archivo" id="archivo" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png,.mp4,.avi" required>
                <button type="submit">Subir material</button>
            </form>
        </div>
        <!-- Publicaciones tipo Classroom -->

        <div class="classroom-posts-grid" id="posts-list">
            <?php if (!empty($materias_prof)): ?>
                <?php foreach ($materias_prof as $mat): ?>
                    <div class="classroom-post">
                        <div class="post-header" style="display:flex;align-items:center;justify-content:space-between;">
                            <span class="post-author">Materia: <?php echo htmlspecialchars($mat['nombre']); ?></span>
                            <!-- Eliminar publicación por archivo ahora está junto a cada archivo -->
                        </div>
                        <div class="post-content">
                            <strong>Materiales subidos:</strong>
                            <?php if (!empty($archivos_materias[$mat['id']])): ?>
                                <ul>
                                <?php foreach ($archivos_materias[$mat['id']] as $arch): ?>
                                    <li style="margin-bottom:18px;">
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <a href="<?php echo htmlspecialchars($arch['nombre_archivo']); ?>" target="_blank" style="font-weight:bold;">
                                                <?php echo basename($arch['nombre_archivo']); ?>
                                            </a>
                                            <form method="post" style="display:inline;margin-left:8px;">
                                                <input type="hidden" name="eliminar_archivo_id" value="<?php echo $arch['id']; ?>">
                                                <button type="submit" onclick="return confirm('¿Seguro que deseas eliminar esta publicación y sus comentarios?')" style="background:#c0392b;color:#fff;border:none;border-radius:5px;padding:2px 10px;">Eliminar publicación</button>
                                            </form>
                                        </div>
                                        <div id="desc-view-<?php echo $arch['id']; ?>" style="display:inline;">
                                            <span><?php echo htmlspecialchars($arch['descripcion']); ?></span>
                                            <button type="button" onclick="mostrarEditarDesc(<?php echo $arch['id']; ?>)" style="background:#174388;color:#fff;border:none;border-radius:5px;padding:2px 10px;margin-left:8px;">Editar descripción</button>
                                            <button type="button" onclick="mostrarEditarArchivo(<?php echo $arch['id']; ?>)" style="background:#888;color:#fff;border:none;border-radius:5px;padding:2px 10px;margin-left:4px;">Editar archivo</button>
                                        </div>
                                        <form method="post" id="desc-form-<?php echo $arch['id']; ?>" style="display:none;margin-top:4px;">
                                            <input type="hidden" name="editar_archivo_id" value="<?php echo $arch['id']; ?>">
                                            <input type="text" name="nueva_descripcion" value="<?php echo htmlspecialchars($arch['descripcion']); ?>" maxlength="255" style="width:60%;padding:2px 6px;border-radius:5px;border:1px solid #bbb;">
                                            <button type="submit" style="background:#174388;color:#fff;border:none;border-radius:5px;padding:2px 10px;margin-left:4px;">Guardar</button>
                                            <button type="button" onclick="cancelarEditarDesc(<?php echo $arch['id']; ?>)" style="background:#888;color:#fff;border:none;border-radius:5px;padding:2px 10px;margin-left:4px;">Cancelar</button>
                                        </form>
                                        <form method="post" enctype="multipart/form-data" id="file-form-<?php echo $arch['id']; ?>" style="display:none;margin-top:4px;">
                                            <input type="hidden" name="editar_archivo_id" value="<?php echo $arch['id']; ?>">
                                            <input type="file" name="nuevo_archivo" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png,.mp4,.avi" required style="margin-bottom:4px;">
                                            <button type="submit" style="background:#174388;color:#fff;border:none;border-radius:5px;padding:2px 10px;margin-left:4px;">Guardar</button>
                                            <button type="button" onclick="cancelarEditarArchivo(<?php echo $arch['id']; ?>)" style="background:#888;color:#fff;border:none;border-radius:5px;padding:2px 10px;margin-left:4px;">Cancelar</button>
                                        </form>
                                        <br><span style="color:#888;font-size:0.9em;">Subido: <?php echo date('d/m/Y H:i', strtotime($arch['fecha_subida'])); ?></span>
                                        <!-- Comentarios -->
                                        <div class="classroom-comments">
                                            <strong style="font-size:1em;">Comentarios:</strong>
                                            <?php if (!empty($comentarios_archivo[$arch['id']])): ?>
                                                <?php echo renderComentarios($comentarios_archivo[$arch['id']], $arch['id'], $comentarios_archivo); ?>
                                            <?php else: ?>
                                                <div style="color:#aaa;">Aún no hay comentarios.</div>
                                            <?php endif; ?>
                                            <!-- Formulario para agregar comentario principal (profesor puede comentar) -->
                                            <form class="classroom-comment-form" method="post" action="guardar_comentario_profesores.php" data-archivo="<?php echo $arch['id']; ?>">
                                                <input type="hidden" name="comentario_archivo_id" value="<?php echo $arch['id']; ?>">
                                                <input type="text" name="comentario_texto" maxlength="300" placeholder="Escribe un comentario..." required>
                                                <button type="submit">Comentar</button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div style="color:#888;">No hay materiales subidos para esta materia.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color:#888;text-align:center;">No tienes materias asignadas.</div>
            <?php endif; ?>
    <script>
    function mostrarEditarDesc(id) {
        document.getElementById('desc-view-' + id).style.display = 'none';
        document.getElementById('desc-form-' + id).style.display = 'inline';
        document.getElementById('file-form-' + id).style.display = 'none';
        var input = document.querySelector('#desc-form-' + id + ' input[name="nueva_descripcion"]');
        if(input) input.focus();
    }
    function cancelarEditarDesc(id) {
        document.getElementById('desc-form-' + id).style.display = 'none';
        document.getElementById('desc-view-' + id).style.display = 'inline';
    }
    function mostrarEditarArchivo(id) {
        document.getElementById('desc-view-' + id).style.display = 'none';
        document.getElementById('file-form-' + id).style.display = 'inline';
        document.getElementById('desc-form-' + id).style.display = 'none';
    }
    function cancelarEditarArchivo(id) {
        document.getElementById('file-form-' + id).style.display = 'none';
        document.getElementById('desc-view-' + id).style.display = 'inline';
    }
    </script>

            <div id="toast-comentario" style="display:none;position:fixed;bottom:32px;right:32px;z-index:9999;font-size:1.1em;animation:fadeIn 0.5s;" class="toast-success">
                ¡Comentario publicado!
            </div>
<style>
.comment-autor {
    font-weight: bold;
    color: var(--color-text);
}
.comment-fecha {
    color: var(--color-text-alt);
    font-size: 0.9em;
}
</style>
        </div>
    </div>
    <script>
    // Sistema de respuesta AJAX igual que foro.php
    document.addEventListener('DOMContentLoaded', function() {
        function handleCommentFormSubmit(e) {
            e.preventDefault();
            var form = e.target;
            var formData = new FormData(form);
            var archivoId = form.getAttribute('data-archivo');
            // Si no existe el atributo, lo tomamos del input oculto
            if (!archivoId) {
                var inputArchivo = form.querySelector('input[name="comentario_archivo_id"]');
                if (inputArchivo) archivoId = inputArchivo.value;
            }
            var padreId = form.getAttribute('data-padre');
            if (padreId) formData.append('id_comentario_padre', padreId);
            fetch('guardar_comentario_profesores.php', {
                method: 'POST',
                body: formData
            })
            .then(async res => {
                let data;
                let ok = false;
                try {
                    data = await res.json();
                    ok = !!data.success;
                } catch {
                    // Si no es JSON, asumimos éxito (caso legacy PHP)
                    ok = true;
                }
                if (ok) {
                    recargarComentarios(archivoId, form);
                    var input = form.querySelector('input[name="comentario_texto"]');
                    if (input) input.value = '';
                    mostrarToast();
                } else {
                    alert('Error: ' + (data && data.error ? data.error : 'No se pudo comentar.'));
                }
            });
        }

        function attachCommentEvents(scope) {
            (scope || document).querySelectorAll('.classroom-comment-form').forEach(f => {
                f.addEventListener('submit', handleCommentFormSubmit);
            });
            (scope || document).querySelectorAll('.btn-mostrar-respuesta').forEach(btn => {
                btn.addEventListener('click', function() {
                    var id = btn.getAttribute('data-reply');
                    var form = btn.parentElement.querySelector('.reply-form[data-padre="'+id+'"]');
                    if (form) {
                        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'flex' : 'none';
                        if (form.style.display === 'flex') {
                            var input = form.querySelector('input[name="comentario_texto"]');
                            if (input) input.focus();
                        }
                    }
                });
            });
            (scope || document).querySelectorAll('.btn-cancelar-respuesta').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var form = btn.closest('.reply-form');
                    if (form) form.style.display = 'none';
                });
            });
        }

        attachCommentEvents();

        function recargarComentarios(archivoId, form) {
            fetch('foro_profesor.php?ajax=1&archivo_id=' + archivoId)
                .then(res => res.text())
                .then(html => {
                    var temp = document.createElement('div');
                    temp.innerHTML = html;
                    var nueva = temp.querySelector('.classroom-comments');
                    if (nueva) {
                        var cont = form.closest('.classroom-comments');
                        if (cont) {
                            cont.innerHTML = nueva.innerHTML;
                            attachCommentEvents(cont);
                        }
                    }
                });
        }

        function mostrarToast() {
            var notif = document.getElementById('toast-comentario');
            if (notif) {
                notif.style.display = 'block';
                setTimeout(function(){ notif.style.display = 'none'; }, 2500);
            }
        }
    });
    </script>
</body>
</html>
