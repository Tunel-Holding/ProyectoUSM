<?php
session_start();
if (!isset($_SESSION['idusuario'])) {
    header('Location: index.php');
    exit;
}

include 'conexion.php'; // Asegúrate de tener un archivo para la conexión a la base de datos
$conn->set_charset("utf8mb4");

// Obtener la ID del usuario desde la sesión
$user_id = $_SESSION['idusuario'];

// Obtener la ID del profesor usando la ID del usuario
$query_profesor = "SELECT id FROM profesores WHERE id_usuario = ?";
$stmt_profesor = $conn->prepare($query_profesor);
if (!$stmt_profesor) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt_profesor->bind_param("i", $user_id);
if (!$stmt_profesor->execute()) {
    die("Error en la ejecución de la consulta: " . $stmt_profesor->error);
}
$result_profesor = $stmt_profesor->get_result();
if ($result_profesor->num_rows === 0) {
    die("No se encontró el profesor.");
}
$profesor = $result_profesor->fetch_assoc();
$profesor_id = $profesor['id'];

// Obtener el día actual en español para la región de Venezuela
$formatter = new IntlDateFormatter('es_VE', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Caracas', IntlDateFormatter::GREGORIAN, 'EEEE');
$dia_actual = $formatter->format(time());
$dia_actual = ucfirst($dia_actual);

// Consulta para obtener el horario del día actual del profesor
$query_horario = "SELECT m.nombre AS materia, m.salon, h.hora_inicio, h.hora_fin 
                  FROM horariosmateria h 
                  JOIN materias m ON h.id_materia = m.id 
                  WHERE m.id_profesor = ? AND h.dia = ?";
$stmt_horario = $conn->prepare($query_horario);
if (!$stmt_horario) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt_horario->bind_param("is", $profesor_id, $dia_actual);
if (!$stmt_horario->execute()) {
    die("Error en la ejecución de la consulta: " . $stmt_horario->error);
}
$result_horario = $stmt_horario->get_result();
if (!$result_horario) {
    die("Error al obtener el resultado: " . $stmt_horario->error);
}

// Consulta para obtener las materias que da el profesor y la cantidad de estudiantes en cada una
$query_materias = "SELECT m.nombre, COUNT(i.id_estudiante) AS num_estudiantes 
                   FROM materias m 
                   LEFT JOIN inscripciones i ON m.id = i.id_materia 
                   WHERE m.id_profesor = ? 
                   GROUP BY m.id";
$stmt_materias = $conn->prepare($query_materias);
if (!$stmt_materias) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt_materias->bind_param("i", $profesor_id);
if (!$stmt_materias->execute()) {
    die("Error en la ejecución de la consulta: " . $stmt_materias->error);
}
$result_materias = $stmt_materias->get_result();
if (!$result_materias) {
    die("Error al obtener el resultado: " . $stmt_materias->error);
}

// PROCESAR FORMULARIO DE NUEVA TAREA
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task-title'])) {
    $titulo = trim($_POST['task-title']);
    $categoria = trim($_POST['task-category']);
    $fecha = trim($_POST['delivery-date']);
    $hora = trim($_POST['delivery-time']);
    $descripcion = trim($_POST['task-description']);
    // Normalizar hora a formato HH:MM:SS
    if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
        $hora .= ':00';
    }
    if ($titulo && $categoria && $fecha && $hora) {
        $stmt = $conn->prepare("INSERT INTO tareas (id_materia, titulo_tarea, categoria, fecha_entrega, hora_entrega, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isssss", $_SESSION['idmateria'], $titulo, $categoria, $fecha, $hora, $descripcion);
            if ($stmt->execute()) {
                $_SESSION['mensaje_tarea'] = '<div class="alert-success">Tarea creada correctamente.</div>';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $mensaje = '<div class="alert-error">Error al guardar la tarea.</div>';
            }
            $stmt->close();
        } else {
            $mensaje = '<div class="alert-error">Error en la base de datos.</div>';
        }
    } else {
        $mensaje = '<div class="alert-error">Completa todos los campos obligatorios.</div>';
    }
}
// Mostrar mensaje si existe en sesión y limpiar
if (isset($_SESSION['mensaje_tarea'])) {
    $mensaje = $_SESSION['mensaje_tarea'];
    unset($_SESSION['mensaje_tarea']);
}

// OBTENER TAREAS DEL PROFESOR
$tareas = [];
$stmt = $conn->prepare("SELECT id, titulo_tarea, categoria, fecha_entrega, hora_entrega, descripcion FROM tareas WHERE id_materia = ? ORDER BY fecha_entrega ASC, hora_entrega ASC");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['idmateria']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tareas[] = $row;
    }
    $stmt->close();
}

// LÓGICA DE BORRADO DE TAREA
if (isset($_POST['delete_task_id'])) {
    $delete_id = intval($_POST['delete_task_id']);
    $stmt = $conn->prepare("DELETE FROM tareas WHERE id = ? AND id_materia = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $delete_id, $_SESSION['idmateria']);
        if ($stmt->execute()) {
            $_SESSION['mensaje_tarea'] = '<div class="alert-success">Tarea eliminada correctamente.</div>';
        } else {
            $_SESSION['mensaje_tarea'] = '<div class="alert-error">No se pudo eliminar la tarea.</div>';
        }
        $stmt->close();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// LÓGICA DE EDICIÓN DE TAREA
if (isset($_POST['update_task_id'])) {
    $update_id = intval($_POST['update_task_id']);
    $titulo = trim($_POST['edit-task-title']);
    $categoria = trim($_POST['edit-task-category']);
    $fecha = trim($_POST['edit-delivery-date']);
    $hora = trim($_POST['edit-delivery-time']);
    $descripcion = trim($_POST['edit-task-description']);

    // Normalizar hora a formato HH:MM:SS para compatibilidad con DB
    if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
        $hora .= ':00';
    }

    if ($update_id && $titulo && $categoria && $fecha && $hora) {
        $stmt = $conn->prepare("UPDATE tareas SET titulo_tarea = ?, categoria = ?, fecha_entrega = ?, hora_entrega = ?, descripcion = ? WHERE id = ? AND id_materia = ?");
        if ($stmt) {
            $stmt->bind_param("sssssii", $titulo, $categoria, $fecha, $hora, $descripcion, $update_id, $_SESSION['idmateria']);
            if ($stmt->execute()) {
                $_SESSION['mensaje_tarea'] = '<div class="alert-success">Tarea actualizada correctamente.</div>';
            } else {
                $_SESSION['mensaje_tarea'] = '<div class="alert-error">Error al actualizar la tarea.</div>';
            }
            $stmt->close();
        } else {
            $_SESSION['mensaje_tarea'] = '<div class="alert-error">Error en la base de datos al preparar la actualización.</div>';
        }
    } else {
        $_SESSION['mensaje_tarea'] = '<div class="alert-error">Faltan datos para actualizar la tarea.</div>';
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="stylesheet" href="css/horario.css">
    <link rel="stylesheet" href="css/tareasprofesores.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Tareas - Plataforma de Profesores</title>

</head>

<body>

    <div class="cabecera">

        <button type="button" id="logoButton">
            <img src="css/logo.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_profesor.php'; ?>



    <div class="contenedor">
        <header class="header">
            <div class="header-left">
                <h1>Tareas</h1>
                <p>Gestiona las tareas de tus estudiantes</p>
            </div>
        </header>

        <section class="new-task-section">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus"></i>
                    <h2>Nueva Tarea</h2>
                </div>
                <form method="POST" autocomplete="off">
                    <div class="form-container">
                        <div class="form-group">
                            <label for="task-title">Título de la Tarea</label>
                            <input type="text" id="task-title" name="task-title"
                                placeholder="Ingresa el título de la tarea" required>
                        </div>
                        <div class="form-group">
                            <label for="task-category">Categoría</label>
                            <select id="task-category" name="task-category" required>
                                <option value="" disabled selected>Selecciona una categoría</option>
                                <option value="examen">Examen</option>
                                <option value="proyecto">Proyecto</option>
                                <option value="investigacion">Investigación</option>
                                <option value="presentacion">Presentación</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group date-group">
                                <label for="delivery-date">Fecha de Entrega</label>
                                <div class="input-with-icon">
                                    <input type="date" id="delivery-date" name="delivery-date" required>
                                    <i class="far fa-calendar-alt"></i>
                                </div>
                            </div>
                            <div class="form-group time-group">
                                <label for="delivery-time">Hora de Entrega</label>
                                <div class="input-with-icon">
                                    <input type="time" id="delivery-time" name="delivery-time" required>
                                    <i class="far fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="task-description">Descripción de la Tarea</label>
                            <textarea id="task-description" name="task-description"
                                placeholder="Describe los detalles de la tarea, instrucciones especiales, materiales necesarios, etc."></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="reset" class="btn btn-secondary">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Crear</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <section class="created-tasks-section">
            <div class="card">
                <h3>Tareas Creadas</h3>
                <?php if (empty($tareas)): ?>
                    <div class="no-tasks-message" id="no-tasks-message">
                        <p>No hay tareas creadas aún. Crea tu primera tarea usando el formulario de arriba.</p>
                    </div>
                <?php else: ?>
                    <div id="task-list">
                        <?php foreach ($tareas as $tarea): ?>
                            <div class="task-card gradient-<?php echo rand(1, 6); ?>"
                                data-task-id="<?php echo $tarea['id']; ?>">
                                <div class="task-actions">
                                    <button class="task-action-btn edit-btn" title="Editar Tarea">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_task_id" value="<?php echo $tarea['id']; ?>">
                                        <button type="submit" class="task-action-btn delete-btn" title="Eliminar Tarea"
                                            onclick="return confirm('¿Estás seguro de que quieres eliminar esta tarea?');">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                                <h4 class="task-title"><?php echo htmlspecialchars($tarea['titulo_tarea']); ?></h4>
                                <p class="task-category"><?php echo htmlspecialchars($tarea['categoria']); ?></p>
                                <p class="task-description">
                                    <?php echo htmlspecialchars($tarea['descripcion'] ?: 'Sin descripción.'); ?>
                                </p>
                                <p class="task-due-date">
                                    <i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($tarea['fecha_entrega']); ?>
                                    <i class="far fa-clock"></i>
                                    <span class="task-due-date-data" data-date="<?php echo $tarea['fecha_entrega']; ?>"
                                        data-time="<?php echo $tarea['hora_entrega']; ?>">
                                        <?php
                                        $hora = $tarea['hora_entrega'];
                                        $horaObj = DateTime::createFromFormat('H:i:s', $hora);
                                        if ($horaObj) {
                                            echo $horaObj->format('g:i A');
                                        } else {
                                            $horaObj = DateTime::createFromFormat('H:i', $hora);
                                            echo $horaObj ? $horaObj->format('g:i A') : htmlspecialchars($hora);
                                        }
                                        ?>
                                    </span>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- ================================= -->
    <!-- MODAL PARA EDITAR TAREA           -->
    <!-- ================================= -->
    <div id="edit-task-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Tarea</h2>
                <button id="close-modal-btn" class="close-btn">&times;</button>
            </div>
            <form id="edit-task-form" method="POST" autocomplete="off">
                <input type="hidden" name="update_task_id" id="edit-task-id">
                <div class="form-container">
                    <div class="form-group">
                        <label for="edit-task-title">Título de la Tarea</label>
                        <input type="text" id="edit-task-title" name="edit-task-title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-task-category">Categoría</label>
                        <select id="edit-task-category" name="edit-task-category" required>
                            <option value="examen">Examen</option>
                            <option value="proyecto">Proyecto</option>
                            <option value="investigacion">Investigación</option>
                            <option value="presentacion">Presentación</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group date-group">
                            <label for="edit-delivery-date">Fecha de Entrega</label>
                            <input type="date" id="edit-delivery-date" name="edit-delivery-date" required>
                        </div>
                        <div class="form-group time-group">
                            <label for="edit-delivery-time">Hora de Entrega</label>
                            <input type="time" id="edit-delivery-time" name="edit-delivery-time" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-task-description">Descripción de la Tarea</label>
                        <textarea id="edit-task-description" name="edit-task-description"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" id="cancel-edit-btn" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- MANEJO DEL MODAL DE EDICIÓN ---
            const editTaskModal = document.getElementById('edit-task-modal');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const cancelEditBtn = document.getElementById('cancel-edit-btn');
            const taskList = document.getElementById('task-list');

            if (editTaskModal && taskList) {
                // Inputs del formulario de edición
                const editTaskIdInput = document.getElementById('edit-task-id');
                const editTaskTitleInput = document.getElementById('edit-task-title');
                const editTaskCategoryInput = document.getElementById('edit-task-category');
                const editDeliveryDateInput = document.getElementById('edit-delivery-date');
                const editDeliveryTimeInput = document.getElementById('edit-delivery-time');
                const editTaskDescriptionInput = document.getElementById('edit-task-description');

                // Función para cerrar el modal
                const closeModal = () => {
                    editTaskModal.classList.remove('visible');
                };

                // Event listeners para cerrar el modal
                closeModalBtn.addEventListener('click', closeModal);
                cancelEditBtn.addEventListener('click', closeModal);
                editTaskModal.addEventListener('click', (event) => {
                    if (event.target === editTaskModal) {
                        closeModal();
                    }
                });

                // Delegación de eventos para abrir el modal al hacer clic en "Editar"
                taskList.addEventListener('click', (event) => {
                    const editBtn = event.target.closest('.edit-btn');
                    if (editBtn) {
                        event.preventDefault(); // Prevenir cualquier acción por defecto
                        const taskCard = editBtn.closest('.task-card');

                        // Extraer datos de la tarjeta
                        const taskId = taskCard.dataset.taskId;
                        const title = taskCard.querySelector('.task-title').textContent.trim();
                        const category = taskCard.querySelector('.task-category').textContent.trim().toLowerCase();
                        const description = taskCard.querySelector('.task-description').textContent.trim();

                        // Extraer fecha y hora de los atributos data-*
                        const dateDataElem = taskCard.querySelector('.task-due-date-data');
                        const dateMatch = dateDataElem.dataset.date;
                        const timeMatch = dateDataElem.dataset.time;

                        // Poblar el formulario del modal
                        editTaskIdInput.value = taskId;
                        editTaskTitleInput.value = title;
                        editTaskCategoryInput.value = category;
                        editDeliveryDateInput.value = dateMatch; // Formato YYYY-MM-DD
                        editDeliveryTimeInput.value = timeMatch ? timeMatch.substring(0, 5) : ''; // Formato HH:MM
                        editTaskDescriptionInput.value = description === 'Sin descripción.' ? '' : description;

                        // Mostrar el modal
                        editTaskModal.classList.add('visible');
                    }
                });
            }
        });
    </script>
</body>

</html>