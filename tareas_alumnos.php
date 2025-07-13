<?php
include 'comprobar_sesion.php';
include 'conexion.php'; // Asegúrate de tener un archivo para la conexión a la base de datos

// Consultar tareas desde la base de datos
$idMateria = $_SESSION['idmateria'];
$id_alumno = $_SESSION['idusuario'];

// Modificado para incluir id_tarea y verificar entregas
$sql = "SELECT t.id, t.titulo_tarea, t.descripcion, t.fecha_entrega, t.hora_entrega, t.categoria, et.id_entrega 
        FROM tareas t 
        LEFT JOIN entregas_tareas et ON t.id = et.id_tarea AND et.id_alumno = ?
        WHERE t.id_materia = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $id_alumno, $idMateria);
$stmt->execute();
$result = $stmt->get_result();
$tareas = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/horario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/tareas.css">
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
            max-height: 200px; /* ajusta según el contenido */
            opacity: 1;
        }
    </style>
</head>

<body>
    <div class="contenedorentrante3">
        <img src="css\logo.png">
    </div>
    <div class="cabecera">
        <button type="button" id="logoButton">
            <img src="css/logo.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
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

    // Obtener calificación y retroalimentación de la entrega si existe
    $calificacion = null;
    $retroalimentacion = null;
    if ($entregada) {
        $stmtCalif = $conn->prepare("SELECT calificacion, retroalimentacion FROM entregas_tareas WHERE id_tarea = ? AND id_alumno = ? ORDER BY id_entrega DESC LIMIT 1");
        $stmtCalif->bind_param("ii", $tarea['id'], $id_alumno);
        $stmtCalif->execute();
        $stmtCalif->bind_result($califRes, $retroRes);
        if ($stmtCalif->fetch()) {
            $calificacion = $califRes;
            $retroalimentacion = $retroRes;
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
    <div class="task-card" data-status="<?php echo $status_class; ?>">
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
        <div class="task-card-footer status-<?php echo $status_class; ?>">
            <button class="grade-btn"
                data-calificacion="<?php echo htmlspecialchars(($calificacion !== null && $calificacion !== '') ? $calificacion : 'N/A'); ?>"
                data-retroalimentacion="<?php echo htmlspecialchars(($retroalimentacion !== null && $retroalimentacion !== '') ? $retroalimentacion : 'Sin retroalimentación.'); ?>"
                <?php if ($calificacion === null || $calificacion === '') echo 'disabled'; ?>
            >
                <i class="fas fa-star"></i> Calificación: <?php echo ($calificacion !== null && $calificacion !== '') ? htmlspecialchars($calificacion) : 'N/A'; ?>
            </button>
            <?php if ($entregada): ?>
                <p class="task-completed-message"><i class="fas fa-check-circle"></i> Tarea Entregada</p>
            <?php else: ?>
                <button class="submit-task-btn" data-idtarea="<?php echo $tarea['id']; ?>" <?php if ($status === 'Vencida') echo 'disabled'; ?>>
                    <i class="fas fa-upload"></i> Subir Tarea
                </button>
            <?php endif; ?>
        </div>
    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

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
                <p>Selecciona el archivo que deseas subir. El formato debe ser PDF, DOCX, o ZIP.</p>
                <div id="uploadErrorMsg" style="color: #d32f2f; margin-bottom: 10px; display: none;"></div>
                <input type="file" name="archivo_tarea" id="archivo_tarea" required accept=".pdf,.docx,.zip" style="display:none;">
                <button type="button" id="archivo_tarea_btn" class="upload_button">
                    <i class="fas fa-file-upload"></i> Seleccionar Archivo
                </button>
                <span id="archivo_tarea_nombre" style="margin-left:10px;color:var(--accent-blue);"></span>
                <div id="archivo_tarea_size_error" style="color: #d32f2f; margin-top: 10px; display: none;"></div>
                <button type="submit" class="submit-task-btn" style="background-color: var(--accent-blue); margin-top: 20px;">Enviar Tarea</button>
            </form>
        </div>
    </div>
</div>

    <script>
        // Script para la fecha actual del semestre y métricas de tareas
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

            const uploadModal = document.getElementById('uploadModal');
            const closeModal = document.getElementById('closeModal');
            const uploadForm = document.getElementById('uploadForm');
            const modalIdTarea = document.getElementById('modal_id_tarea');
            const uploadErrorMsg = document.getElementById('uploadErrorMsg');

            document.querySelectorAll('.submit-task-btn[data-idtarea]').forEach(button => {
                button.addEventListener('click', function() {
                    const idTarea = this.getAttribute('data-idtarea');
                    modalIdTarea.value = idTarea;
                    uploadModal.classList.add('visible');
                    uploadErrorMsg.style.display = 'none';
                    uploadErrorMsg.textContent = '';
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
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                uploadErrorMsg.style.display = 'none';
                uploadErrorMsg.textContent = '';
                const sizeError = document.getElementById('archivo_tarea_size_error');
                sizeError.style.display = 'none';
                sizeError.textContent = '';
                if (archivoInput.files.length > 0) {
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    if (archivoInput.files[0].size > maxSize) {
                        sizeError.textContent = 'El archivo es demasiado grande. El tamaño máximo permitido es 10MB.';
                        sizeError.style.display = 'block';
                        return;
                    }
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
    
    <script>
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
        document.getElementById('switchtema').addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
        });

        // Aplicar la preferencia guardada del usuario al cargar la página
        window.addEventListener('load', function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
                document.getElementById('switchtema').checked = true;
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
            document.getElementById('desempeño').addEventListener('click', function() {
                redirigir('desempeño.php');
            });
            document.getElementById('notas').addEventListener('click', function() {
                redirigir('NAlumnos.php');
            });
            document.getElementById('foto').addEventListener('click', function() {
                redirigir('foto.php');
            });
        }
    </script>
</body>

</html>