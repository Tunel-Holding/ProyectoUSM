<?php
require_once __DIR__ . '/vendor/autoload.php';
include 'conexion.php';
// Iniciar sesión y obtener materia desde sesión
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['idmateria']) || intval($_SESSION['idmateria']) <= 0) {
    die('ID de materia no válido.');
}
$materia_id = intval($_SESSION['idmateria']);

// Configuración de mPDF
$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->SetTitle('Listado de Notas de Todas las Tareas');


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
$html .= '<h2>Listado de Notas de Todas las Tareas</h2>';
$html .= '<table>';

$query = "SELECT t.id AS id_tarea, t.titulo_tarea, du.usuario_id, du.nombres, du.apellidos, et.calificacion
          FROM tareas t
          JOIN entregas_tareas et ON et.id_tarea = t.id
          JOIN estudiantes e ON et.id_alumno = e.id
          JOIN datos_usuario du ON e.id_usuario = du.usuario_id
          WHERE t.id_materia = " . $materia_id . "
          ORDER BY du.nombres, du.apellidos, t.titulo_tarea";
$result = $conn->query($query);

$alumnos = [];
$tareas = [];
$notas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_alumno = $row['usuario_id'];
        $nombre_alumno = $row['nombres'] . ' ' . $row['apellidos'];
        $id_tarea = $row['id_tarea'];
        $titulo_tarea = $row['titulo_tarea'];
        $calificacion = $row['calificacion'];
        $alumnos[$id_alumno] = $nombre_alumno;
        $tareas[$id_tarea] = $titulo_tarea;
        $notas[$id_alumno][$id_tarea] = $calificacion;
    }
    // Encabezado de la tabla
    $html .= '<thead><tr>'
        . '<th>Alumno</th>';
    foreach ($tareas as $id_tarea => $titulo_tarea) {
        $html .= '<th>' . htmlspecialchars($titulo_tarea) . ' (Calificación)</th>';
    }
    $html .= '</tr></thead><tbody>';
    // Filas de alumnos y sus notas
    foreach ($alumnos as $id_alumno => $nombre_alumno) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($nombre_alumno) . '</td>';
        foreach ($tareas as $id_tarea => $titulo_tarea) {
            $nota = isset($notas[$id_alumno][$id_tarea]) ? htmlspecialchars($notas[$id_alumno][$id_tarea]) : '-';
            $html .= '<td style="text-align:center;">' . $nota . '</td>';
        }
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="100" style="text-align:center;padding:12px;">No hay tareas registradas.</td></tr>';
}
$html .= '</tbody></table>';

// Zona de firma

$html .= '<div class="firma-section">';
$html .= '<div class="firma-label">Firma del Profesor:</div>';
$html .= '<div class="firma-linea"></div>';
$html .= '</div>';

$mpdf->WriteHTML($html);
$mpdf->Output('notas_todas_las_tareas.pdf', 'I');
