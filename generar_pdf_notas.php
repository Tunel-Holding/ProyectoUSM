
<?php
// Iniciar buffer para evitar salidas previas
if (session_status() === PHP_SESSION_NONE) { ob_start(); }
require_once('conexion.php');
require_once('vendor/autoload.php');
use Mpdf\Mpdf;

// Obtener el id de la tarea desde GET
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
if ($task_id <= 0) {
    die('ID de tarea no válido.');
}

// Buscar la materia de la tarea
$stmt = $conn->prepare('SELECT id_materia FROM tareas WHERE id = ?');
$stmt->bind_param('i', $task_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Tarea no encontrada.');
}
$row = $result->fetch_assoc();
$id_materia = $row['id_materia'];
$stmt->close();

// Buscar estudiantes inscritos en la materia usando id_estudiante como clave y obtener nombre/apellido de datos_usuario
$stmt = $conn->prepare('SELECT e.id_usuario, d.nombres, d.apellidos FROM inscripciones i JOIN estudiantes e ON i.id_estudiante = e.id_usuario JOIN datos_usuario d ON e.id_usuario = d.usuario_id WHERE i.id_materia = ?');
$stmt->bind_param('i', $id_materia);
$stmt->execute();
$result = $stmt->get_result();
$estudiantes = [];
while ($row = $result->fetch_assoc()) {
    $estudiantes[$row['id_usuario']] = [
        'nombre' => $row['nombres'] . ' ' . $row['apellidos'],
        'calificacion' => '',
        'retroalimentacion' => ''
    ];
}
$stmt->close();

// Buscar calificaciones y retroalimentaciones desde entregas_tareas usando id_alumno (que es id_usuario)
$stmt = $conn->prepare('SELECT id_alumno, calificacion, retroalimentacion FROM entregas_tareas WHERE id_tarea = ?');
$stmt->bind_param('i', $task_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if (isset($estudiantes[$row['id_alumno']])) {
        $estudiantes[$row['id_alumno']]['calificacion'] = $row['calificacion'];
        $estudiantes[$row['id_alumno']]['retroalimentacion'] = $row['retroalimentacion'];
    }
}
$stmt->close();

// Crear PDF

$mpdf = new Mpdf();
$html = '<style>
    body { font-family: Arial, sans-serif; }
    h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
    th, td { padding: 10px 8px; text-align: left; }
    th { background-color: #34495e; color: #fff; font-size: 15px; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    tr:nth-child(odd) { background-color: #eaf1fb; }
    td { font-size: 14px; }
    .firma-section { margin-top: 60px; text-align: right; }
    .firma-label { font-size: 15px; color: #2c3e50; margin-bottom: 40px; }
    .firma-linea { border-bottom: 1px solid #34495e; width: 250px; margin: 0 0 8px auto; height: 30px; }
</style>';
$html .= '<h2>Listado de Notas de Estudiantes</h2>';
$html .= '<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Calificación</th>
            <th>Retroalimentación</th>
        </tr>
    </thead>
    <tbody>';
foreach ($estudiantes as $est) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($est['nombre']) . '</td>';
    $html .= '<td>' . htmlspecialchars($est['calificacion']) . '</td>';
    $html .= '<td>' . htmlspecialchars($est['retroalimentacion']) . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';

// Sección para la firma del profesor
$html .= '<div class="firma-section">';
$html .= '<div class="firma-label">Firma del Profesor:</div>';
$html .= '<div class="firma-linea"></div>';
$html .= '</div>';

$mpdf->WriteHTML($html);
// Limpiar buffer antes de enviar PDF
if (ob_get_length()) { ob_end_clean(); }
$mpdf->Output('notas_tarea_' . $task_id . '.pdf', 'I');
