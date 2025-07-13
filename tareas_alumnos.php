<?php
session_start();
include 'conexion.php'; // Asegúrate de tener un archivo para la conexión a la base de datos
$conn->set_charset("utf8mb4");

// Habilitar la visualización de errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('America/Caracas');

// Obtener la ID del usuario desde la sesión
$user_id = $_SESSION['idusuario'];

// Obtener el día actual en español para la región de Venezuela
$formatter = new IntlDateFormatter('es_VE', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Caracas', IntlDateFormatter::GREGORIAN, 'EEEE');
$dia_actual = $formatter->format(time());

// Convertir el primer carácter del día a mayúscula
$dia_actual = ucfirst($dia_actual);

// Consulta para obtener el horario del día actual
$query = "SELECT m.nombre AS materia, m.salon, h.hora_inicio, h.hora_fin 
          FROM horarios h 
          JOIN materias m ON h.id_materia = m.id 
          WHERE h.id_estudiante = ? AND h.dia = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt->bind_param("is", $user_id, $dia_actual);
if (!$stmt->execute()) {
    die("Error en la ejecución de la consulta: " . $stmt->error);
}
$result = $stmt->get_result();
if (!$result) {
    die("Error al obtener el resultado: " . $stmt->error);
}

// Consulta para obtener las notas del estudiante
$query_materias_profesor = "
SELECT 
    m.nombre AS materia,
    p.nombre AS profesor
FROM inscripciones i
JOIN materias m ON i.id_materia = m.id
JOIN profesores p ON m.id_profesor = p.id
WHERE i.id_estudiante = ?
";

$stmt = $conn->prepare($query_materias_profesor);
if (!$stmt) {
    die("Error preparando la consulta: " . $conn->error);
}

$stmt->bind_param("i", $user_id); // $user_id es el ID del estudiante
if (!$stmt->execute()) {
    die("Error ejecutando la consulta: " . $stmt->error);
}

$resultado = $stmt->get_result();

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
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/tareas.css">
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

    <?php include 'menu_alumno.php'; ?>

    <div class="contenido">
        <section class="semester-progress-section card">
            <h1>Mis Tareas - Nombre Materia</h1>
            <div class="progress-metrics">


                <div class="metric-item">
                    <div class="metric-circle green">
                        <i class="fas fa-check"></i>
                        <span class="metric-value">3</span>
                    </div>
                    <p class="metric-label">Tareas Completadas</p>
                </div>

                <div class="metric-item">
                    <div class="metric-circle yellow">
                        <i class="fas fa-hourglass-half"></i>
                        <span class="metric-value">2</span>
                    </div>
                    <p class="metric-label">Tareas Pendientes</p>
                </div>

                <div class="metric-item">
                    <div class="metric-circle red">
                        <i class="fas fa-exclamation"></i>
                        <span class="metric-value">1</span>
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
            </div>

        </section>

        <section class="tasks-list-section">
            <div class="task-grid">
                <div class="task-card" data-status="pending">
                    <div class="task-card-content">
                        <h4 class="task-title">Ensayo de Historia</h4>
                        <p class="task-description">Escribir un ensayo de 1500 palabras sobre la Revolución Industrial
                        </p>
                        <div class="task-details">
                            <p><i class="fas fa-book"></i> Materia: Historia Universal</p>
                            <p><i class="far fa-calendar-alt"></i> Fecha: 25/12/2024</p>
                            <p><i class="far fa-clock"></i> Hora: 23:59</p>
                            <p><i class="fas fa-hourglass"></i> Estado: <span
                                    class="status-badge pending">Pendiente</span></p>
                            <p><i class="fas fa-weight-hanging"></i> Peso: 25% del total</p>
                            <p><i class="fas fa-exclamation-triangle"></i> Tiempo: <span
                                    class="time-status overdue">Tiempo Agotado</span></p>
                        </div>
                    </div>
                    <div class="task-card-footer status-overdue">
                        <button class="grade-btn red">
                            <i class="fas fa-star"></i> Calificación: D (Regular)
                        </button>
                        <button class="submit-task-btn"><i class="fas fa-upload"></i> Subir Tarea</button>
                    </div>
                </div>

                <div class="task-card" data-status="pending">
                    <div class="task-card-content">
                        <h4 class="task-title">Proyecto de Matemáticas</h4>
                        <p class="task-description">Resolver ejercicios del capítulo 5 sobre derivadas e integrales</p>
                        <div class="task-details">
                            <p><i class="fas fa-book"></i> Materia: Cálculo I</p>
                            <p><i class="far fa-calendar-alt"></i> Fecha: 28/12/2024</p>
                            <p><i class="far fa-clock"></i> Hora: 18:00</p>
                            <p><i class="fas fa-hourglass"></i> Estado: <span
                                    class="status-badge pending">Pendiente</span></p>
                            <p><i class="fas fa-weight-hanging"></i> Peso: 30% del total</p>
                            <p><i class="fas fa-calendar-times"></i> Tiempo: <span class="time-status completed">Tiempo
                                    de Entrega Terminado</span></p>
                        </div>
                    </div>
                    <div class="task-card-footer status-deficient">
                        <button class="grade-btn red-dark">
                            <i class="fas fa-star"></i> Calificación: E (Deficiente)
                        </button>
                        <button class="submit-task-btn"><i class="fas fa-upload"></i> Subir Tarea</button>
                    </div>
                </div>

                <div class="task-card" data-status="completed">
                    <div class="task-card-content">
                        <h4 class="task-title">Laboratorio de Química</h4>
                        <p class="task-description">Completar el reporte del experimento sobre reacciones ácido-base</p>
                        <div class="task-details">
                            <p><i class="fas fa-book"></i> Materia: Química Orgánica</p>
                            <p><i class="far fa-calendar-alt"></i> Fecha: 20/12/2024</p>
                            <p><i class="far fa-clock"></i> Hora: 14:30</p>
                            <p><i class="fas fa-check-double"></i> Estado: <span
                                    class="status-badge completed">Completada</span></p>
                            <p><i class="fas fa-weight-hanging"></i> Peso: 20% del total</p>
                        </div>
                    </div>
                    <div class="task-card-footer status-completed">
                        <button class="grade-btn green">
                            <i class="fas fa-star"></i> Calificación: B (Muy Bueno)
                        </button>
                        <p class="task-completed-message"><i class="fas fa-check-circle"></i> Tarea Completada</p>
                    </div>
                </div>

                <div class="task-card" data-status="pending">
                    <div class="task-card-content">
                        <h4 class="task-title">Presentación de Literatura</h4>
                        <p class="task-description">Preparar presentación de 30 minutos sobre Gabriel García Márquez.
                        </p>
                        <div class="task-details">
                            <p><i class="fas fa-book"></i> Materia: Literatura Española</p>
                            <p><i class="far fa-calendar-alt"></i> Fecha: 05/01/2025</p>
                            <p><i class="far fa-clock"></i> Hora: 10:00</p>
                            <p><i class="fas fa-hourglass"></i> Estado: <span
                                    class="status-badge pending">Pendiente</span></p>
                            <p><i class="fas fa-weight-hanging"></i> Peso: 15% del total</p>
                        </div>
                    </div>
                    <div class="task-card-footer status-pending">
                        <button class="submit-task-btn"><i class="fas fa-upload"></i> Subir Tarea</button>
                    </div>
                </div>

                <div class="task-card" data-status="completed">
                    <div class="task-card-content">
                        <h4 class="task-title">Examen de Física</h4>
                        <p class="task-description">Estudiar capítulos 1-3 sobre mecánica clásica y cinemática.</p>
                        <div class="task-details">
                            <p><i class="fas fa-book"></i> Materia: Física Básica</p>
                            <p><i class="far fa-calendar-alt"></i> Fecha: 15/11/2024</p>
                            <p><i class="far fa-clock"></i> Hora: 09:00</p>
                            <p><i class="fas fa-check-double"></i> Estado: <span
                                    class="status-badge completed">Completada</span></p>
                            <p><i class="fas fa-weight-hanging"></i> Peso: 40% del total</p>
                        </div>
                    </div>
                    <div class="task-card-footer status-completed">
                        <button class="grade-btn green">
                            <i class="fas fa-star"></i> Calificación: A (Excelente)
                        </button>
                        <p class="task-completed-message"><i class="fas fa-check-circle"></i> Tarea Completada</p>
                    </div>
                </div>

                <div class="task-card" data-status="pending">
                    <div class="task-card-content">
                        <h4 class="task-title">Quiz de Inglés</h4>
                        <p class="task-description">Evaluación sobre gramática y vocabulario del capítulo 3.</p>
                        <div class="task-details">
                            <p><i class="fas fa-book"></i> Materia: Inglés Avanzado</p>
                            <p><i class="far fa-calendar-alt"></i> Fecha: 01/01/2025</p>
                            <p><i class="far fa-clock"></i> Hora: 11:00</p>
                            <p><i class="fas fa-hourglass"></i> Estado: <span
                                    class="status-badge pending">Pendiente</span></p>
                            <p><i class="fas fa-weight-hanging"></i> Peso: 10% del total</p>
                        </div>
                    </div>
                    <div class="task-card-footer status-pending">
                        <button class="submit-task-btn"><i class="fas fa-upload"></i> Subir Tarea</button>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <script>
        // Script para la fecha actual del semestre y funcionalidades de tareas
        document.addEventListener('DOMContentLoaded', () => {
            const dateElement = document.getElementById('current-semester-date');
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            dateElement.textContent = now.toLocaleDateString('es-ES', options);

            // Funcionalidad de filtros (ejemplo simple)
            const filterButtons = document.querySelectorAll('.filter-btn');
            const taskCards = document.querySelectorAll('.task-card');

            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');

                    const filter = button.dataset.filter;

                    taskCards.forEach(card => {
                        const status = card.dataset.status;
                        if (filter === 'all' || filter === status) {
                            card.style.display = 'block'; // Mostrar
                        } else {
                            card.style.display = 'none'; // Ocultar
                        }
                    });
                });
            });

            // Funcionalidad de búsqueda (ejemplo simple)
            const searchInput = document.querySelector('.search-bar input');
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
        });
    </script>
</body>

</html>