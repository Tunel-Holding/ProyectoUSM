<?php
// Siempre iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forzar salida solo JSON y evitar cualquier salida previa
ob_start();
header('Content-Type: application/json');

include 'conexion.php';
$debug = [];

// Validar sesión
if (!isset($_SESSION['idusuario']) || !isset($_SESSION['idmateria'])) {
    $debug['session'] = isset($_SESSION) ? $_SESSION : 'no_session';
    ob_end_clean();
    echo json_encode(['error' => 'Acceso no autorizado.', 'debug' => $debug]);
    exit;
}

$id_materia = $_SESSION['idmateria'];
$debug['id_materia'] = $id_materia;

// Obtener el task_id por GET
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$debug['task_id'] = $task_id;

// Consulta para obtener estudiantes inscritos y su entrega para la tarea
$query = "SELECT d.id, d.nombres, d.apellidos, et.archivo AS archivo_entregado, et.calificacion, et.retroalimentacion
          FROM datos_usuario d
          JOIN inscripciones i ON d.usuario_id = i.id_estudiante
          LEFT JOIN (
              SELECT t1.* FROM entregas_tareas t1
              INNER JOIN (
                  SELECT id_alumno, MAX(id_entrega) AS max_id
                  FROM entregas_tareas
                  WHERE id_tarea = ?
                  GROUP BY id_alumno
              ) t2 ON t1.id_alumno = t2.id_alumno AND t1.id_entrega = t2.max_id
          ) et ON et.id_alumno = d.usuario_id
          WHERE i.id_materia = ?";
$debug['query'] = $query;

$stmt = $conn->prepare($query);
if (!$stmt) {
    $debug['prepare_error'] = $conn->error;
    ob_end_clean();
    echo json_encode(['error' => 'Error al preparar la consulta: ' . $conn->error, 'debug' => $debug]);
    exit;
}

$stmt->bind_param("ii", $task_id, $id_materia);
if (!$stmt->execute()) {
    $debug['execute_error'] = $stmt->error;
    ob_end_clean();
    echo json_encode(['error' => 'Error al ejecutar la consulta: ' . $stmt->error, 'debug' => $debug]);
    exit;
}

$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    // Si no hay entrega, poner calificacion y retroalimentacion como string vacío
    if (is_null($row['calificacion'])) $row['calificacion'] = '';
    if (is_null($row['retroalimentacion'])) $row['retroalimentacion'] = '';
    $students[] = $row;
}
$debug['num_students'] = count($students);
$conn->close();

// Limpiar cualquier salida previa (warnings, espacios, etc.)
ob_end_clean();
echo json_encode(['students' => $students, 'debug' => $debug]);
