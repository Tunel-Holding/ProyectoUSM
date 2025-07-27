<?php
include 'comprobar_sesion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);


// Comprobar en la base de datos si el usuario es estudiante (nivel_usuario = 'usuario')
include 'conexion.php';
$conn->set_charset("utf8mb4");
$user_id = isset($_SESSION['idusuario']) ? $_SESSION['idusuario'] : 0;
$nivel_usuario = null;
if ($user_id > 0) {
    $stmt_nivel = $conn->prepare("SELECT nivel_usuario FROM usuarios WHERE id = ? LIMIT 1");
    $stmt_nivel->bind_param("i", $user_id);
    $stmt_nivel->execute();
    $stmt_nivel->bind_result($nivel_usuario);
    $stmt_nivel->fetch();
    $stmt_nivel->close();
}
if ($nivel_usuario !== 'usuario') {
    echo '<div style="padding:40px;text-align:center;color:#a94442;font-weight:bold;">Acceso solo para estudiantes.</div>';
    exit();
}



actualizar_actividad();

// Habilitar la visualización de errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('America/Caracas');

// Obtener la ID del usuario desde la sesión
$user_id = $_SESSION['idusuario'];

// Validar si el usuario tiene datos registrados en datos_usuario
$sql_check = "SELECT 1 FROM datos_usuario WHERE usuario_id = ? LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
if ($stmt_check) {
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows === 0) {
        header("Location: datos.php");
        exit();
    }
    $stmt_check->close();
}

// Obtener el día actual en español para la región de Venezuela
$formatter = new IntlDateFormatter('es_VE', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Caracas', IntlDateFormatter::GREGORIAN, 'EEEE');
$dia_actual = $formatter->format(time());


// Consulta para obtener las materias inscritas del estudiante (usando inscripciones)
$materias_alumno = [];
$stmt_materias = $conn->prepare("SELECT m.id, m.nombre FROM inscripciones i JOIN materias m ON i.id_materia = m.id WHERE i.id_estudiante = ?");
$stmt_materias->bind_param("i", $user_id);
$stmt_materias->execute();
$res_materias = $stmt_materias->get_result();
while ($row = $res_materias->fetch_assoc()) {
    $materias_alumno[] = $row;
}
$stmt_materias->close();

// Consulta para obtener el horario completo del estudiante
$query_horario = "SELECT hm.dia, hm.hora_inicio, hm.hora_fin, m.nombre AS materia, m.salon, m.id,
                         COALESCE(p.nombre, 'Profesor no asignado') AS profesor
                  FROM horarios h
                  JOIN horariosmateria hm ON h.id_materia = hm.id_materia
                  JOIN materias m ON h.id_materia = m.id
                  LEFT JOIN profesores p ON m.id_profesor = p.id
                  WHERE h.id_estudiante = ?
                  ORDER BY hm.dia, hm.hora_inicio";
$stmt_horario = $conn->prepare($query_horario);
$stmt_horario->bind_param("i", $user_id);
$stmt_horario->execute();
$result_horario = $stmt_horario->get_result();

// Procesar los datos del horario para pasarlos a horario.php
$datos_horario = [];
if ($result_horario->num_rows > 0) {
    while ($row = $result_horario->fetch_assoc()) {
        $hora_inicio = strtotime($row['hora_inicio']);
        $hora_fin = strtotime($row['hora_fin']);
        $intervalo = 45 * 60; // 45 minutos
        
        for ($hora = $hora_inicio; $hora < $hora_fin; $hora += $intervalo) {
            $hora_formateada = date("H:i:s", $hora);
            $datos_horario[$row['dia']][$hora_formateada] = [
                "materia" => $row['materia'],
                "salon" => $row['salon'],
                "profesor" => $row['profesor'] ?: "Profesor no asignado",
                "inicio" => ($hora == $hora_inicio),
                "rowspan" => ceil(($hora_fin - $hora_inicio) / $intervalo)
            ];
        }
    }
}
$stmt_horario->close();

$horas_disponibles = [];
foreach ($datos_horario as $dia => $horas) {
    foreach ($horas as $hora => $info) {
        $horas_disponibles[] = $hora;
    }
}
$horas_disponibles = array_unique($horas_disponibles);
sort($horas_disponibles);


$query_materias_profesor = "
SELECT 
    m.id AS id_materia,
    m.nombre AS materia,
    m.salon,
    m.creditos,
    m.semestre,
    m.seccion,
    fu.foto,
    COALESCE(p.nombre, 'No asignado') AS nombre_profesor
FROM inscripciones i
JOIN materias m ON i.id_materia = m.id
LEFT JOIN profesores p ON m.id_profesor = p.id
LEFT JOIN fotousuario fu ON p.id_usuario = fu.id_usuario
WHERE i.id_estudiante = ?
GROUP BY m.id
";
$stmt_materias_profesor = $conn->prepare($query_materias_profesor);
$stmt_materias_profesor->bind_param("i", $user_id);
$stmt_materias_profesor->execute();
$resultado = $stmt_materias_profesor->get_result();
$stmt_materias_profesor->close();



actualizar_actividad();
// ...
?>
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

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="css/logounihubblanco.png" type="image/png">
        <link rel="stylesheet" href="css/principalunihub.css">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/principalalumnostyle.css">
        <link rel="stylesheet" href="css/horario.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
            rel="stylesheet">
        <title>Inicio - UniHub</title>
        <script src="js/control_inactividad.js"></script>
    </head>

    <div class="classroom-container">
        <!-- Materiales subidos por los profesores de las materias inscritas -->
        <div class="classroom-posts-grid" id="posts-list" style="display:flex;flex-direction:column;gap:32px;">
            <?php
            // Obtener materias inscritas del alumno
            $materias_alumno = [];
            $stmt_materias = $conn->prepare("SELECT m.id, m.nombre FROM inscripciones i JOIN materias m ON i.id_materia = m.id WHERE i.id_estudiante = ?");
            $stmt_materias->bind_param("i", $user_id);
            $stmt_materias->execute();
            $res_materias = $stmt_materias->get_result();
            while ($row = $res_materias->fetch_assoc()) {
                $materias_alumno[] = $row;
            }
            $stmt_materias->close();

            // Obtener archivos/materiales por materia
            $archivos_materias = [];
            if (!empty($materias_alumno)) {
                $ids = implode(',', array_map(fn($m) => $m['id'], $materias_alumno));
                $sql_arch = "SELECT a.*, m.nombre AS materia_nombre, u.nombre_usuario FROM archivos a JOIN materias m ON a.materia_id = m.id JOIN usuarios u ON a.usuario_id = u.id WHERE a.materia_id IN ($ids) ORDER BY a.fecha_subida DESC";
                $res_arch = $conn->query($sql_arch);
                while ($row = $res_arch->fetch_assoc()) {
                    $archivos_materias[$row['materia_id']][] = $row;
                }
            }

            // Procesar comentario enviado
            $comentario_exito = false;
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario_archivo_id'], $_POST['comentario_texto'])) {
                $archivo_id = intval($_POST['comentario_archivo_id']);
                $comentario = trim($_POST['comentario_texto']);
        if ($archivo_id > 0 && $comentario !== '') {
            $stmt_com = $conn->prepare("INSERT INTO comentarios (archivo_id, id_usuario, comentario, fecha) VALUES (?, ?, ?, NOW())");
            $stmt_com->bind_param("iis", $archivo_id, $user_id, $comentario);
            $stmt_com->execute();
            $stmt_com->close();
            $comentario_exito = true;
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
                        $comentarios_archivo[$row['archivo_id']][] = $row;
                    }
                }
            }

            // Función para mostrar comentarios y respuestas en árbol (con formulario de respuesta)
            function renderComentariosAlumno($comentarios, $archivo_id, $padre = null, $nivel = 0, $todos = null) {
                if ($todos === null) $todos = $comentarios;
                $html = '';
                foreach ($comentarios as $com) {
                    if ((is_null($padre) && empty($com['id_comentario_padre'])) || (!is_null($padre) && $com['id_comentario_padre'] == $padre)) {
                        $margen = $nivel * 24;
                        $html .= '<div class="classroom-comment" style="margin-left:'.$margen.'px;">';
                        $html .= '<span style="font-weight:bold; color:#174388;">'.htmlspecialchars($com['nombre_usuario']).'</span> ';
                        $html .= '<span style="color:#888; font-size:0.9em;">('.(isset($com['fecha']) ? date('d/m/Y H:i', strtotime($com['fecha'])) : '').')</span>';
                        // Si es respuesta, mostrar a quién responde, con extracto
                        if (!empty($com['id_comentario_padre'])) {
                            $padreObj = null;
                            foreach ($todos as $c2) { if ($c2['id'] == $com['id_comentario_padre']) { $padreObj = $c2; break; } }
                            if ($padreObj) {
                                $extracto = mb_substr(str_replace(["\r","\n"]," ", $padreObj['comentario']), 0, 32, 'UTF-8');
                                if (mb_strlen($padreObj['comentario'], 'UTF-8') > 32) $extracto .= '...';
                                $html .= '<span class="reply-to" style="margin-left:8px;background:#e9f1fb;color:#174388;padding:2px 8px;border-radius:6px;font-size:0.95em;">↳ Respondiendo a @'.htmlspecialchars($padreObj['nombre_usuario']).': "'.htmlspecialchars($extracto).'"</span>';
                            }
                        }
                        $html .= '<br>';
                        $html .= nl2br(htmlspecialchars($com['comentario']));
                        // Botón para mostrar/ocultar el formulario de respuesta
                        $html .= '<button type="button" class="btn-mostrar-respuesta" data-reply="'.$com['id'].'" style="background:#e9f1fb;color:#174388;border:none;border-radius:5px;padding:2px 10px;margin-top:6px;margin-bottom:4px;cursor:pointer;">Responder</button>';
                        $html .= '<form class="classroom-comment-form reply-form" method="post" action="" data-archivo="'.$archivo_id.'" data-padre="'.$com['id'].'" style="display:none;margin-top:6px;">';
                        $html .= '<input type="hidden" name="comentario_archivo_id" value="'.$archivo_id.'">';
                        $html .= '<input type="hidden" name="id_comentario_padre" value="'.$com['id'].'">';
                        $html .= '<input type="text" name="comentario_texto" maxlength="300" placeholder="Responder..." required>';
                        $html .= '<button type="submit">Enviar</button>';
                        $html .= '<button type="button" class="btn-cancelar-respuesta" style="margin-left:8px;background:#eee;color:#174388;border:none;border-radius:5px;padding:2px 10px;">Cancelar</button>';
                        $html .= '</form>';
                        // Mostrar respuestas recursivamente
                        $html .= renderComentariosAlumno($comentarios, $archivo_id, $com['id'], $nivel+1, $todos);
                        $html .= '</div>';
                    }
                }
                return $html;
            }
        // ...existing code...
        // Eliminar el bloque CSS de aquí, irá en <style> más abajo
        ?>

            <?php
            // AJAX: si se pide solo la sección de comentarios de un archivo
            if (isset($_GET['ajax']) && $_GET['ajax'] == '1' && isset($_GET['archivo_id'])) {
                $archivo_id = intval($_GET['archivo_id']);
                // Renderizar solo la sección de comentarios de ese archivo
                echo '<div class="classroom-comments">';
                echo '<strong style="font-size:1em;">Comentarios:</strong>';
                if (!empty($comentarios_archivo[$archivo_id])) {
                    echo renderComentariosAlumno($comentarios_archivo[$archivo_id], $archivo_id);
                } else {
                    echo '<div style="color:#aaa;">Aún no hay comentarios.</div>';
                }
                // Formulario para agregar comentario principal
                echo '<form class="classroom-comment-form main-comment-form" method="post" action="" data-archivo="'.$archivo_id.'">';
                echo '<input type="hidden" name="comentario_archivo_id" value="'.$archivo_id.'">';
                echo '<input type="text" name="comentario_texto" maxlength="300" placeholder="Escribe un comentario..." required>';
                echo '<button type="submit">Comentar</button>';
                echo '</form>';
                echo '</div>';
                exit;
            }
            ?>

            <?php if (!empty($materias_alumno)): ?>
                <?php foreach ($materias_alumno as $mat): ?>
                    <div class="classroom-post" style="background:#f7fafc;border-radius:10px;padding:22px 24px 18px 24px;box-shadow:0 2px 8px rgba(33,53,85,0.06);margin-bottom:0;width:100%;max-width:650px;margin-left:auto;margin-right:auto;">
                        <div class="post-header" style="display:flex;align-items:center;justify-content:space-between;">
                            <span class="post-author">Materia: <?php echo htmlspecialchars($mat['nombre']); ?></span>
                        </div>
                        <div class="post-content">
                            <strong>Materiales del profesor:</strong>
                            <?php if (!empty($archivos_materias[$mat['id']])): ?>
                                <ul style="padding-left:0;list-style:none;">
                                <?php foreach ($archivos_materias[$mat['id']] as $arch): ?>
                                    <li style="margin-bottom:18px;">
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <a href="<?php echo htmlspecialchars($arch['nombre_archivo']); ?>" target="_blank" style="font-weight:bold;">
                                                <?php echo basename($arch['nombre_archivo']); ?>
                                            </a>
                                        </div>
                                        <div style="margin-top:2px;margin-bottom:2px;">
                                            <span><?php echo htmlspecialchars($arch['descripcion']); ?></span>
                                        </div>
                                        <span style="color:#888;font-size:0.9em;">Subido por: <?php echo htmlspecialchars($arch['nombre_usuario']); ?> | <?php echo date('d/m/Y H:i', strtotime($arch['fecha_subida'])); ?></span>
                                        <!-- Comentarios -->
                                        <div class="classroom-comments">
                                            <strong style="font-size:1em;">Comentarios:</strong>
                                            <?php if (!empty($comentarios_archivo[$arch['id']])): ?>
                                                <?php echo renderComentariosAlumno($comentarios_archivo[$arch['id']], $arch['id']); ?>
                                            <?php else: ?>
                                                <div style="color:#aaa;">Aún no hay comentarios.</div>
                                            <?php endif; ?>
                                            <!-- Formulario para agregar comentario principal (alumno) -->
                                            <form class="classroom-comment-form main-comment-form" method="post" action="" data-archivo="<?php echo $arch['id']; ?>">
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
                <div style="color:#888;text-align:center;">No tienes materias inscritas.</div>
            <?php endif; ?>

            <div id="toast-comentario" style="display:none;position:fixed;bottom:32px;right:32px;background:#174388;color:#fff;padding:16px 28px;border-radius:10px;box-shadow:0 2px 8px rgba(33,53,85,0.18);z-index:9999;font-size:1.1em;animation:fadeIn 0.5s;">
                ¡Comentario publicado!
            </div>
        </div>
    </div>

    <script>
    // AJAX para comentarios y UX de mostrar/ocultar respuesta
    document.addEventListener('DOMContentLoaded', function() {
        function handleCommentFormSubmit(e) {
            e.preventDefault();
            var form = e.target;
            var formData = new FormData(form);
            var archivoId = form.getAttribute('data-archivo');
            var padreId = form.getAttribute('data-padre');
            if (padreId) formData.append('id_comentario_padre', padreId);
            fetch('comentar_archivo.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    recargarComentarios(archivoId, form);
                    mostrarToast();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo comentar.'));
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
            fetch('foro.php?ajax=1&archivo_id=' + archivoId)
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

    <style>
    /* Visual para a quién se responde */
    .reply-to {
        display: inline-block;
        margin-left: 8px;
        background: #e9f1fb;
        color: #174388;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.95em;
    }
        .classroom-container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(33,53,85,0.10);
            padding: 32px 24px;
        }
        .classroom-posts-grid {
            display: flex;
            flex-direction: column;
            gap: 32px;
            align-items: center;
        }
        .classroom-post {
            background: #f7fafc;
            border-radius: 10px;
            padding: 22px 24px 18px 24px;
            box-shadow: 0 2px 8px rgba(33,53,85,0.06);
            margin-bottom: 0;
            width: 100%;
            max-width: 650px;
            min-width: 320px;
            margin-left: 0;
            margin-right: 0;
        }
        .post-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.95rem;
            color: #174388;
            margin-bottom: 6px;
        }
        .post-content strong {
            font-size: 1.1rem;
            color: #174388;
        }
        .post-file {
            margin-bottom: 10px;
        }
        .ver-comentarios-btn {
            background: #174388;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 16px;
            cursor: pointer;
            margin-bottom: 8px;
            transition: background 0.2s;
        }
        .ver-comentarios-btn:hover {
            background: #0e3470;
        }
        .classroom-comments {
            margin-top: 12px;
            padding-left: 10px;
            border-left: 3px solid #174388;
        }
        .classroom-comment {
            margin-bottom: 8px;
            background: #fff;
            border-radius: 6px;
            padding: 8px 12px;
            box-shadow: 0 1px 4px rgba(33,53,85,0.04);
        }
        .classroom-comment-form {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .classroom-comment-form input[type="text"] {
            flex: 1;
            border-radius: 6px;
            border: 1px solid #ccc;
            padding: 6px 10px;
        }
        .classroom-comment-form button {
            background: #174388;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .classroom-comment-form button:hover {
            background: #0e3470;
        }
        .logo-menu {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }
        .logounihub {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }
        .classroom-post-section h2 {
            margin-bottom: 12px;
            color: #174388;
        }
        #post-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 32px;
            max-width: 650px;
            min-width: 320px;
            margin-left: auto;
            margin-right: auto;
        }
        #post-form textarea {
            resize: vertical;
            border-radius: 8px;
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 1rem;
        }
        #post-form input[type="file"] {
            margin-top: 4px;
        }
        #post-form button {
            align-self: flex-end;
            background: #174388;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        #post-form button:hover {
            background: #0e3470;
        }
        .classroom-posts-list {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .classroom-post {
            background: #f7fafc;
            border-radius: 10px;
            padding: 18px 20px;
            box-shadow: 0 2px 8px rgba(33,53,85,0.06);
        }
        .classroom-post .post-content {
            margin-bottom: 10px;
        }
        .classroom-post .post-file {
            margin-bottom: 10px;
        }
        .classroom-comments {
            margin-top: 12px;
            padding-left: 10px;
            border-left: 3px solid #174388;
        }
        .classroom-comment {
            margin-bottom: 8px;
            background: #fff;
            border-radius: 6px;
            padding: 8px 12px;
            box-shadow: 0 1px 4px rgba(33,53,85,0.04);
        }
        .classroom-comment-form {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .classroom-comment-form input[type="text"] {
            flex: 1;
            border-radius: 6px;
            border: 1px solid #ccc;
            padding: 6px 10px;
        }
        .classroom-comment-form button {
            background: #174388;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 6px 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .classroom-comment-form button:hover {
            background: #0e3470;
        }
    </style>
