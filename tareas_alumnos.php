<?php
include 'comprobar_sesion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);



include 'conexion.php'; // Asegúrate de tener un archivo para la conexión a la base de datos
actualizar_actividad();
// Consultar tareas desde la base de datos
$idMateria = $_SESSION['idmateria'];
$id_alumno = $_SESSION['idusuario'];


// Modificado para incluir id_tarea, verificar entregas y traer link

$sql = "SELECT t.id, t.titulo_tarea, t.descripcion, t.fecha_entrega, t.hora_entrega, t.categoria, et.id_entrega, et.archivo 
        FROM tareas t 
        LEFT JOIN entregas_tareas et ON t.id = et.id_tarea AND et.id_alumno = ?
        WHERE t.id_materia = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $id_alumno, $idMateria);
$stmt->execute();
$result = $stmt->get_result();
$tareas = $result->fetch_all(MYSQLI_ASSOC);
actualizar_actividad();
// $conn->close(); // Se mueve al final del archivo
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
    <link rel="stylesheet" href="css/horario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/tareas.css">
    <script src="js/control_inactividad.js"></script>
    <style>
        .task-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }
        .task-card {
            min-height: 340px;
            max-height: 340px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }
        .task-card-content {
            flex: 1 1 auto;
        }
        .task-card-footer {
            flex-shrink: 0;
        }
        .task-completed-message {
            text-align: center;
            width: 100%;
            display: block;
            margin: 0 auto 0 auto;
        }
        
        .btn-mini {
            padding: 6px 12px !important;
            font-size: 0.95em !important;
            min-width: 0 !important;
            max-width: 180px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            margin-bottom: 8px !important;
        }
        @media (max-width: 600px) {
            .task-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Mis Tareas - Plataforma de Estudiantes</title>
    <style>
        .hover-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .hover-box {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.5s ease, opacity 0.5s ease;
        }

        .hover-container:hover .hover-box {
            max-height: 200px;
            /* ajusta según el contenido */
            opacity: 1;
        }
        .soporte-flotante-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .soporte-flotante {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            background-color: #446ad3;
            padding: 12px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            overflow: hidden;
            width: 60px;            /* ✅ suficiente para mostrar solo el ícono */
            height: 50px;
            transition: width 0.4s ease, background-color 0.3s ease;
        }


        .soporte-flotante:hover {
            width: 210px; /* ✅ se expande hacia la izquierda */
            background-color: #365ac0;
        }

        .soporte-mensaje {
            flex: 1; /* ✅ ocupa todo el espacio disponible */
            opacity: 0;
            white-space: nowrap;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            transform: translateX(30px); /* animación desde la derecha */
            transition: transform 0.4s ease, opacity 0.4s ease;
            text-align: left; /* ✅ texto alineado a la izquierda */
            margin-right: auto;
            font-family: 'Poppins', sans-serif;
        }

        .soporte-flotante:hover .soporte-mensaje {
            opacity: 1;
            transform: translateX(0);
        }

        .soporte-flotante img {
            width: 30px;
            height: 30px;
            filter: brightness(0) invert(1);
            flex-shrink: 0;
            z-index: 2;
        }
    </style>
</head>

<body>
    <div class="contenedorentrante3">
        <img src="css\logo.png">

    </div>


    <div class="cabecera">
        <button type="button" id="logoButton">
            <!-- <img src="css/logo.png" alt="Logo"> -->
             <img src="css/menu.png" alt="Menú" class="logo-menu">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>

    <?php include 'menu_alumno.php'; ?>

    <div class="contenido">
        <section class="semester-progress-section card">
            <h1>Mis Tareas - <?php echo $_SESSION['nombremateria']; ?></h1>
            <div class="progress-metrics">


                <div class="metric-item">
                    <div class="metric-circle green">
                        <i class="fas fa-check"></i>
                        <span class="metric-value" id="completadas-count"></span>
                    </div>
                    <p class="metric-label">Tareas Completadas</p>
                </div>

                <div class="metric-item">
                    <div class="metric-circle yellow">
                        <i class="fas fa-hourglass-half"></i>
                        <span class="metric-value" id="pendientes-count"></span>
                    </div>
                    <p class="metric-label">Tareas Pendientes</p>
                </div>

                <div class="metric-item">
                    <div class="metric-circle red">
                        <i class="fas fa-times"></i>
                        <span class="metric-value" id="vencidas-count"></span>
                    </div>
                    <p class="metric-label">Tareas Vencidas</p>
                </div>
            </div>
        </section>

        <section class="task-filters-section">
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-list"></i> Todas las Tareas
                </button>
                <button class="filter-btn" data-filter="pending">
                    <i class="fas fa-hourglass-half"></i> Pendientes
                </button>
                <button class="filter-btn" data-filter="completed">
                    <i class="fas fa-check-circle"></i> Completadas
                </button>
                <button class="filter-btn" data-filter="overdue">
                    <i class="fas fa-times"></i> Vencidas
                </button>
            </div>

        </section>

        <section class="tasks-list-section">
            <div class="task-grid" style="padding-bottom: 40px;">
                <?php if (empty($tareas)): ?>
                    <p>No hay tareas asignadas para esta materia.</p>
                <?php else: ?>
                    <?php 
                    foreach ($tareas as $tarea): 
    $tz_ve = new DateTimeZone('America/Caracas');
    $fecha_entrega = new DateTime($tarea['fecha_entrega'] . ' ' . $tarea['hora_entrega'], $tz_ve);
    $ahora = new DateTime('now', $tz_ve);
    $status = '';
    $status_class = '';
    $entregada = !is_null($tarea['id_entrega']);



    // Obtener calificación, retroalimentación y archivo de la entrega si existe
    $calificacion = null;
    $retroalimentacion = null;
    $archivo_entregado = null;
    if ($entregada) {
        $stmtCalif = $conn->prepare("SELECT calificacion, retroalimentacion, archivo FROM entregas_tareas WHERE id_tarea = ? AND id_alumno = ? ORDER BY id_entrega DESC LIMIT 1");
        $stmtCalif->bind_param("ii", $tarea['id'], $id_alumno);
        $stmtCalif->execute();
        $stmtCalif->bind_result($califRes, $retroRes, $archivoRes);
        if ($stmtCalif->fetch()) {
            $calificacion = $califRes;
            $retroalimentacion = $retroRes;
            $archivo_entregado = $archivoRes;
        }
        $stmtCalif->close();
    }

    if ($entregada) {
        $status = 'Entregada';
        $status_class = 'completed';
    } elseif ($ahora > $fecha_entrega) {
        $status = 'Vencida';
        $status_class = 'overdue';
    } else {
        $status = 'Pendiente';
        $status_class = 'pending';
    }
                    ?>
    <div class="task-card" data-status="<?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>" data-fechaentrega="<?php echo htmlspecialchars($tarea['fecha_entrega'] . 'T' . $tarea['hora_entrega'] . '-04:00', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="task-card-content">
            <h4 class="task-title"><?php echo htmlspecialchars($tarea['titulo_tarea']); ?></h4>
            <p class="task-description"><?php echo htmlspecialchars($tarea['descripcion']); ?></p>
            <div class="task-details">
                <p><i class="fas fa-book"></i> Categoría: <?php echo htmlspecialchars($tarea['categoria']); ?></p>
                <p><i class="far fa-calendar-alt"></i> Fecha: <?php echo htmlspecialchars(date("d/m/Y", strtotime($tarea['fecha_entrega']))); ?></p>
                <p><i class="far fa-clock"></i> Hora: <?php echo htmlspecialchars(date("h:i A", strtotime($tarea['hora_entrega']))); ?></p>
                <p><i class="fas fa-hourglass"></i> Estado: <span class="status-badge <?php echo $status_class; ?>"><?php echo $status; ?></span></p>
            </div>
        </div>
        <div class="task-card-footer status-<?php echo htmlspecialchars($status_class, ENT_QUOTES, 'UTF-8'); ?>">
            <button class="grade-btn"
                data-calificacion="<?php echo htmlspecialchars(($calificacion !== null && $calificacion !== '') ? $calificacion : 'N/A'); ?>"
                data-retroalimentacion="<?php echo htmlspecialchars(($retroalimentacion !== null && $retroalimentacion !== '') ? $retroalimentacion : 'Sin retroalimentación.'); ?>"
                <?php if ($calificacion === null || $calificacion === '') echo 'disabled'; ?>
            >
                <i class="fas fa-star"></i> Calificación: <?php echo ($calificacion !== null && $calificacion !== '') ? htmlspecialchars($calificacion) : 'N/A'; ?>
            </button>
            <?php
                // Calcular si la tarea está vencida por fecha/hora exacta
                $fechaHoraEntrega = new DateTime($tarea['fecha_entrega'] . ' ' . $tarea['hora_entrega'], $tz_ve);
                $ahoraExacto = new DateTime('now', $tz_ve);
                $expirada = ($ahoraExacto > $fechaHoraEntrega);
            ?>
            <button class="submit-task-btn btn-mini<?php if ($expirada) echo ' disabled-task-btn'; ?>" data-idtarea="<?php echo intval($tarea['id']); ?>" data-archivo="<?php echo htmlspecialchars($archivo_entregado ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-vencida="<?php echo $expirada ? '1' : '0'; ?>" <?php if ($expirada) echo 'disabled tabindex="-1"'; ?> >
                <i class="fas fa-upload"></i> <?php echo $entregada ? 'Remplazar Tarea' : 'Subir Tarea'; ?>
            </button>
            <?php if ($entregada): ?>
                <p class="task-completed-message"><i class="fas fa-check-circle"></i> Tarea Entregada</p>
            <?php endif; ?>
        </div>
    </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
<?php
// Cerrar la conexión después de usarla en todo el archivo (después del foreach)
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

    <!-- Modal para ver calificación y retroalimentación -->
    <div id="gradeModal" class="modal-overlay">
        <div class="modal-content modal-alumno">
            <div class="modal-header">
                <h2>Detalles de la Calificación</h2>
                <button id="closeGradeModal" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <h4>Calificación</h4>
                <p id="modal_calificacion" style="font-size: 1.2em; font-weight: bold; color: var(--accent-blue);"></p>
                <h4 style="margin-top: 20px;">Retroalimentación del Profesor</h4>
                <p id="modal_retroalimentacion" style="line-height: 1.6;"></p>
            </div>
        </div>
    </div>

    <!-- Modal para subir tarea -->
<div id="uploadModal" class="modal-overlay">
    <div class="modal-content modal-alumno">
        <div class="modal-header">
            <h2>Subir Tarea</h2>
            <button id="closeModal" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="uploadForm" action="subir_tarea.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_tarea" id="modal_id_tarea">
                <input type="hidden" id="modal_vencida" value="0">
                <p>Selecciona el tipo de entrega:</p>
                <div style="margin-bottom: 12px;">
                    <label style="margin-right: 18px;">
                        <input type="radio" name="tipo_entrega" id="radio_archivo" value="archivo" checked> Archivo
                    </label>
                    <label>
                        <input type="radio" name="tipo_entrega" id="radio_link" value="link"> Link
                    </label>
                </div>
                <div id="uploadErrorMsg" style="color: #d32f2f; margin-bottom: 10px; display: none;"></div>
                <div id="entrega_archivo">
                    <input type="file" name="archivo_tarea" id="archivo_tarea" accept=".pdf,.docx,.zip" style="display:none;">
                    <button type="button" id="archivo_tarea_btn" class="upload_button">
                        <i class="fas fa-file-upload"></i> Seleccionar Archivo
                    </button>
                    <span id="archivo_tarea_nombre" style="margin-left:10px;color:var(--accent-blue); min-width: 180px; display: inline-block;"></span>
                    <div id="archivo_tarea_size_error" style="color: #d32f2f; margin-top: 10px; display: none;"></div>
                </div>
                <div id="entrega_link" style="display:none; margin: 18px 0 0 0;">
                    <label for="link_tarea" style="font-weight:500;">Ingresa un link:</label>
                    <input type="url" name="link_tarea" id="link_tarea" placeholder="https://..." style="width: 100%; margin-top: 6px; padding: 7px 10px; border-radius: 5px; border: 1px solid #bbb;">
                </div>
                <button type="submit" class="submit-task-btn" id="enviar_tarea_btn" style="background-color: var(--accent-blue); margin-top: 20px;">Enviar Tarea</button>
            </form>
        </div>
    </div>
</div>

    <script>
        // Script para la fecha actual del semestre y funcionalidades de tareas
        document.addEventListener('DOMContentLoaded', () => {
            const dateElement = document.getElementById('current-semester-date');
            if (dateElement) {
                const now = new Date();
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                dateElement.textContent = now.toLocaleDateString('es-ES', options);
            }

            // Contar tareas por estado
            let pendientes = 0;
            let completadas = 0;
            let vencidas = 0;
            const taskCards = document.querySelectorAll('.task-card');
            taskCards.forEach(card => {
                const status = card.dataset.status;
                // Si tienes una lógica para completadas, ajústala aquí
                if (status === 'pending') {
                    pendientes++;
                } else if (status === 'overdue') {
                    vencidas++;
                } else if (status === 'completed') {
                    completadas++;
                }
            });
            document.getElementById('pendientes-count').textContent = pendientes;
            document.getElementById('completadas-count').textContent = completadas;
            document.getElementById('vencidas-count').textContent = vencidas;

            // Funcionalidad de filtros (ejemplo simple)
            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');

                    const filter = button.dataset.filter;

                    taskCards.forEach(card => {
                        const status = card.dataset.status;
                        if (filter === 'all' || filter === status) {
                            card.style.visibility = 'visible';
                            card.style.position = 'static';
                        } else {
                            card.style.visibility = 'hidden';
                            card.style.position = 'absolute';
                        }
                    });
                });
            });


            // Funcionalidad de búsqueda (ejemplo simple)
            const searchInput = document.querySelector('.search-bar input');
            if (searchInput) {
                searchInput.addEventListener('keyup', (event) => {
                    const searchTerm = event.target.value.toLowerCase();
                    taskCards.forEach(card => {
                        const title = card.querySelector('.task-title').textContent.toLowerCase();
                        const description = card.querySelector('.task-description').textContent.toLowerCase();
                        const materia = card.querySelector('.task-details p:nth-child(1)').textContent.toLowerCase(); // Suponiendo el orden

                        if (title.includes(searchTerm) || description.includes(searchTerm) || materia.includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }

            // --- Desactivar botones de subir/remplazar tarea en tiempo real si expira la hora de entrega ---

            function checkTareasExpiradas() {
                document.querySelectorAll('.task-card').forEach(card => {
                    const fechaEntregaISO = card.getAttribute('data-fechaentrega');
                    if (!fechaEntregaISO) return;
                    const fechaHoraEntrega = new Date(fechaEntregaISO);
                    const ahora = new Date();
                    // Si está pendiente, cambiar a vencida y deshabilitar botón
                    if (card.dataset.status === 'pending') {
                        if (ahora >= fechaHoraEntrega) {
                            const btn = card.querySelector('.submit-task-btn');
                            if (btn) {
                                btn.disabled = true;
                                btn.classList.add('disabled-task-btn');
                                btn.setAttribute('tabindex', '-1');
                                btn.style.pointerEvents = 'none';
                                btn.style.userSelect = 'none';
                            }
                            card.dataset.status = 'overdue';
                            const badge = card.querySelector('.status-badge');
                            if (badge) {
                                badge.textContent = 'Vencida';
                                badge.className = 'status-badge overdue';
                            }
                        }
                    }
                    // Si está entregada, solo deshabilitar el botón de reemplazo si expira
                    if (card.dataset.status === 'completed') {
                        if (ahora >= fechaHoraEntrega) {
                            const btn = card.querySelector('.submit-task-btn');
                            if (btn && !btn.disabled) {
                                btn.disabled = true;
                                btn.classList.add('disabled-task-btn');
                                btn.setAttribute('tabindex', '-1');
                                btn.style.pointerEvents = 'none';
                                btn.style.userSelect = 'none';
                            }
                        }
                    }
                });
            }
            setInterval(checkTareasExpiradas, 1000);

            const uploadModal = document.getElementById('uploadModal');
            const closeModal = document.getElementById('closeModal');
            const uploadForm = document.getElementById('uploadForm');
            const modalIdTarea = document.getElementById('modal_id_tarea');
            const uploadErrorMsg = document.getElementById('uploadErrorMsg');

            document.querySelectorAll('.submit-task-btn[data-idtarea]').forEach(button => {
                button.addEventListener('click', function(e) {
                    // Verificar en tiempo real si la tarea ya expiró
                    const card = this.closest('.task-card');
                    const fechaEntregaISO = card ? card.getAttribute('data-fechaentrega') : null;
                    if (fechaEntregaISO) {
                        const fechaHoraEntrega = new Date(fechaEntregaISO);
                        const ahora = new Date();
                        if (ahora >= fechaHoraEntrega) {
                            // Desactivar botón y no abrir modal
                            this.disabled = true;
                            this.classList.add('disabled-task-btn');
                            this.setAttribute('tabindex', '-1');
                            this.style.pointerEvents = 'none';
                            this.style.userSelect = 'none';
                            // Cambiar estado visual
                            if (card) {
                                card.dataset.status = 'overdue';
                                const badge = card.querySelector('.status-badge');
                                if (badge) {
                                    badge.textContent = 'Vencida';
                                    badge.className = 'status-badge overdue';
                                }
                            }
                            return; // No abrir modal
                        }
                    }
                    // ...código original para abrir el modal...
                    const idTarea = this.getAttribute('data-idtarea');
                    const archivoRegistrado = this.getAttribute('data-archivo');
                    const vencida = this.getAttribute('data-vencida');
                    modalIdTarea.value = idTarea;
                    document.getElementById('modal_vencida').value = vencida;
                    uploadModal.classList.add('visible');
                    uploadErrorMsg.style.display = 'none';
                    uploadErrorMsg.textContent = '';
                    // Mostrar el nombre del archivo o link entregado
                    const archivoNombre = document.getElementById('archivo_tarea_nombre');
                    if (typeof archivoRegistrado === 'string' && archivoRegistrado.length > 0) {
                        if (/^https?:\/\//i.test(archivoRegistrado)) {
                            // Es un link, mostrar el dominio o el link recortado
                            let nombreLink = archivoRegistrado;
                            try {
                                const urlObj = new URL(archivoRegistrado);
                                nombreLink = urlObj.hostname + urlObj.pathname;
                                if (nombreLink.length > 40) {
                                    nombreLink = nombreLink.substring(0, 37) + '...';
                                }
                            } catch (e) {
                                // Si no es un link válido, mostrar el texto tal cual
                                nombreLink = archivoRegistrado;
                            }
                            archivoNombre.textContent = 'Link: ' + nombreLink;
                            document.getElementById('archivo_tarea_btn').innerHTML = '<i class="fas fa-file-upload"></i> Seleccionar Archivo';
                        } else if (archivoRegistrado.match(/\.(pdf|docx|zip)$/i)) {
                            // Es un archivo, mostrar el nombre original
                            const nombreSolo = archivoRegistrado.split(/[\\/]/).pop();
                            const partes = nombreSolo.split('_');
                            let nombreOriginal = nombreSolo;
                            if (partes.length > 2) {
                                nombreOriginal = partes.slice(2).join('_');
                            }
                            archivoNombre.textContent = 'Archivo: ' + nombreOriginal;
                            document.getElementById('archivo_tarea_btn').innerHTML = '<i class="fas fa-file-upload"></i> Remplazar Archivo';
                        } else {
                            archivoNombre.textContent = '';
                            document.getElementById('archivo_tarea_btn').innerHTML = '<i class="fas fa-file-upload"></i> Seleccionar Archivo';
                        }
                    } else {
                        archivoNombre.textContent = '';
                        document.getElementById('archivo_tarea_btn').innerHTML = '<i class="fas fa-file-upload"></i> Seleccionar Archivo';
                    }
                    // Si la tarea está vencida, deshabilitar los botones dentro del modal
                    const archivoBtn = document.getElementById('archivo_tarea_btn');
                    const enviarBtn = document.getElementById('enviar_tarea_btn');
                    if (vencida === '1') {
                        archivoBtn.disabled = true;
                        enviarBtn.disabled = true;
                        archivoBtn.classList.add('disabled');
                        enviarBtn.classList.add('disabled');
                    } else {
                        archivoBtn.disabled = false;
                        enviarBtn.disabled = false;
                        archivoBtn.classList.remove('disabled');
                        enviarBtn.classList.remove('disabled');
                    }
                });
            });

            function hideModal() {
                uploadModal.classList.remove('visible');
                uploadErrorMsg.style.display = 'none';
                uploadErrorMsg.textContent = '';
            }

            closeModal.addEventListener('click', hideModal);
            uploadModal.addEventListener('click', function(event) {
                if (event.target === uploadModal) {
                    hideModal();
                }
            });
            // Estilo y nombre para el input de archivo
            const archivoInput = document.getElementById('archivo_tarea');
            const archivoBtn = document.getElementById('archivo_tarea_btn');
            const archivoNombre = document.getElementById('archivo_tarea_nombre');
            archivoBtn.addEventListener('click', function() {
                archivoInput.value = '';
                archivoInput.click();
            });
            archivoInput.addEventListener('change', function() {
                const sizeError = document.getElementById('archivo_tarea_size_error');
                sizeError.style.display = 'none';
                sizeError.textContent = '';
                if (archivoInput.files.length > 0) {
                    archivoNombre.textContent = archivoInput.files[0].name;
                    // Validar tamaño (por ejemplo, 10MB)
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (archivoInput.files[0].size > maxSize) {
                        sizeError.textContent = 'El archivo es demasiado grande. El tamaño máximo permitido es 10MB.';
                        sizeError.style.display = 'block';
                        archivoInput.value = '';
                        archivoNombre.textContent = '';
                    }
                } else {
                    archivoNombre.textContent = '';
                }
            });
            uploadForm.addEventListener('reset', function() {
                archivoNombre.textContent = '';
                uploadErrorMsg.style.display = 'none';
                uploadErrorMsg.textContent = '';
                const sizeError = document.getElementById('archivo_tarea_size_error');
                sizeError.style.display = 'none';
                sizeError.textContent = '';
            });

            // Interceptar el submit para mostrar error si ocurre
            // Mostrar/ocultar inputs según radio seleccionado
            const radioArchivo = document.getElementById('radio_archivo');
            const radioLink = document.getElementById('radio_link');
            const entregaArchivoDiv = document.getElementById('entrega_archivo');
            const entregaLinkDiv = document.getElementById('entrega_link');
            radioArchivo.addEventListener('change', function() {
                if (radioArchivo.checked) {
                    entregaArchivoDiv.style.display = '';
                    entregaLinkDiv.style.display = 'none';
                    // Mostrar nombre si existe
                    const archivoNombre = document.getElementById('archivo_tarea_nombre');
                    if (archivoNombre.dataset.linkname) {
                        archivoNombre.textContent = archivoNombre.dataset.linkname;
                    }
                    // Mostrar botón de archivo
                    document.getElementById('archivo_tarea_btn').style.display = '';
                }
            });
            radioLink.addEventListener('change', function() {
                if (radioLink.checked) {
                    entregaArchivoDiv.style.display = '';
                    entregaLinkDiv.style.display = '';
                    // Mostrar nombre si existe
                    const archivoNombre = document.getElementById('archivo_tarea_nombre');
                    if (archivoNombre.dataset.linkname) {
                        archivoNombre.textContent = archivoNombre.dataset.linkname;
                    }
                    // Ocultar botón de archivo
                    document.getElementById('archivo_tarea_btn').style.display = 'none';
                }
            });

            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                uploadErrorMsg.style.display = 'none';
                uploadErrorMsg.textContent = '';
                const sizeError = document.getElementById('archivo_tarea_size_error');
                sizeError.style.display = 'none';
                sizeError.textContent = '';
                const archivoTieneValor = archivoInput.files.length > 0;
                const linkInput = document.getElementById('link_tarea');
                const linkTieneValor = linkInput && linkInput.value.trim() !== '';
                // Validación según radio
                if (radioArchivo.checked) {
                    if (!archivoTieneValor) {
                        uploadErrorMsg.textContent = 'Debes seleccionar un archivo.';
                        uploadErrorMsg.style.display = 'block';
                        return;
                    }
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (archivoInput.files[0].size > maxSize) {
                        sizeError.textContent = 'El archivo es demasiado grande. El tamaño máximo permitido es 10MB.';
                        sizeError.style.display = 'block';
                        return;
                    }
                } else if (radioLink.checked) {
                    if (!linkTieneValor) {
                        uploadErrorMsg.textContent = 'Debes ingresar un link.';
                        uploadErrorMsg.style.display = 'block';
                        return;
                    }
                } else {
                    uploadErrorMsg.textContent = 'Selecciona el tipo de entrega.';
                    uploadErrorMsg.style.display = 'block';
                    return;
                }
                const formData = new FormData(uploadForm);
                fetch(uploadForm.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hideModal();
                        // Opcional: recargar la página o actualizar tareas
                        location.reload();
                    } else {
                        uploadErrorMsg.textContent = data.error || 'Ocurrió un error al subir el archivo.';
                        uploadErrorMsg.style.display = 'block';
                    }
                })
                .catch(() => {
                    uploadErrorMsg.textContent = 'No se pudo conectar con el servidor.';
                    uploadErrorMsg.style.display = 'block';
                });
            });
            // Modal de calificación y retroalimentación
            const gradeModal = document.getElementById('gradeModal');
            const closeGradeModal = document.getElementById('closeGradeModal');
            const modalCalificacion = document.getElementById('modal_calificacion');
            const modalRetroalimentacion = document.getElementById('modal_retroalimentacion');

            document.querySelectorAll('.grade-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const calificacion = this.getAttribute('data-calificacion');
                    const retroalimentacion = this.getAttribute('data-retroalimentacion');
                    if (calificacion !== 'N/A') {
                        modalCalificacion.textContent = calificacion;
                        modalRetroalimentacion.textContent = retroalimentacion || 'Sin retroalimentación.';
                        gradeModal.classList.add('visible');
                    }
                });
            });

            function hideGradeModal() {
                gradeModal.classList.remove('visible');
            }
            closeGradeModal.addEventListener('click', hideGradeModal);
            gradeModal.addEventListener('click', function(event) {
                if (event.target === gradeModal) {
                    hideGradeModal();
                }
            });
        });
    </script>
    <div class="soporte-flotante-container">
        <a href="contacto.php" class="soporte-flotante" title="Soporte">
            <span class="soporte-mensaje">Contacto soporte</span>
            <img src="css/audifonos-blanco.png" alt="Soporte">
        </a>
    </div>
</body>

</html>