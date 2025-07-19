<?php
require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('America/Caracas');

// Recibe el nombre del archivo por GET
$file = isset($_GET['file']) ? $_GET['file'] : '';
if (!$file || !file_exists(__DIR__ . '/clase/' . $file)) {
    die('Archivo no encontrado');
}

// Extraer materia y sección del nombre del archivo
$nombre_materia = '';
$seccion = '';
if (preg_match('/lista_(\d+)_([A-Za-z0-9]+)_/', $file, $matches)) {
    $materia_id = intval($matches[1]);
    $seccion = $matches[2];
    // Buscar nombre de la materia
    require_once(__DIR__ . '/conexion.php');
    $stmt = $conn->prepare('SELECT nombre FROM materias WHERE id = ?');
    $stmt->bind_param('i', $materia_id);
    $stmt->execute();
    $stmt->bind_result($nombre_materia);
    $stmt->fetch();
    $stmt->close();
    $conn->close();
}

// Leer el archivo TXT
$lines = file(__DIR__ . '/clase/' . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Procesar los datos
$rows = [];
$hora_inicio = '';
$hora_fin_clase = '';
foreach ($lines as $line) {
    if (strpos($line, 'Hora de inicio:') === 0) {
        // Extraer hora de inicio y hora de fin si están en la misma línea
        if (preg_match('/Hora de inicio: ([0-9:]+)(?: \| Hora de fin: ([0-9:]+))?/', $line, $matches)) {
            $hora_inicio = isset($matches[1]) ? $matches[1] : '';
            if (isset($matches[2])) {
                $hora_fin_clase = $matches[2];
            }
        }
        continue;
    }
    if (strpos($line, 'Hora de fin:') === 0) {
        $hora_fin_clase = trim(str_replace('Hora de fin:', '', $line));
        continue;
    }
    // Separar nombre y asistencias
    $parts = explode(' ', $line, 3);
    $nombre = isset($parts[0]) ? $parts[0] : '';
    $apellido = isset($parts[1]) ? $parts[1] : '';
    $asistencias = isset($parts[2]) ? $parts[2] : '';
    $rows[] = [
        'nombre' => $nombre,
        'apellido' => $apellido,
        'asistencias' => $asistencias
    ];
}

function formato12h($hora) {
    if (!$hora) return '-';
    $partes = explode(':', $hora);
    if (count($partes) < 2) return $hora;
    $h = intval($partes[0]);
    $m = $partes[1];
    $ampm = $h >= 12 ? 'PM' : 'AM';
    $h12 = $h % 12;
    if ($h12 == 0) $h12 = 12;
    return sprintf('%02d:%02d %s', $h12, $m, $ampm);
}

$mpdf = new \Mpdf\Mpdf();
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
$html .= '<h2>Lista de Asistencia</h2>';

if ($nombre_materia && $seccion) {
    $html .= '<div style="text-align:center; font-size:1.15rem; margin-bottom:10px; color:#34495e;">'
        . 'Materia: <strong>' . htmlspecialchars($nombre_materia) . '</strong> | Sección: <strong>' . htmlspecialchars($seccion) . '</strong></div>';
    // Extraer hora de inicio y día de la clase del nombre del archivo si no se obtuvo del contenido
    $dia_clase = '';
    if (!$hora_inicio && preg_match('/_(\d{2})-(\d{2})-(\d{4})_(\d{2})-(\d{2})-(\d{2})\.txt$/', $file, $m)) {
        $dia_clase = $m[1] . '-' . $m[2] . '-' . $m[3];
        $hora_inicio = $m[4] . ':' . $m[5] . ':' . $m[6];
    }
    // Mostrar la fecha del día actual en 'Día de la clase'
    $fecha_actual = date('d-m-Y');
    $html .= '<div style="text-align:center; font-size:1.1rem; margin-bottom:6px; color:#34495e;">Día de la clase: <strong>' . $fecha_actual . '</strong></div>';
    $html .= '<div style="text-align:center; font-size:1rem; margin-bottom:10px; color:#34495e;">';
    if ($hora_inicio || $hora_fin_clase) {
        $html .= 'Hora de inicio: <strong>' . formato12h($hora_inicio) . '</strong>';
        $html .= ' | Hora de fin: <strong>' . formato12h($hora_fin_clase) . '</strong>';
    }
    $html .= '</div>';
}
$html .= '<table>';
$html .= '<thead><tr><th>Nombre</th><th>Apellido</th><th>Asistencia</th></tr></thead><tbody>';
foreach ($rows as $row) {
    // Formatear todas las horas dentro de la cadena de asistencias
    $asistencias = preg_replace_callback('/\((\d{2}:\d{2}(?::\d{2})?) ([AR])\)/', function($m) {
        return '(' . formato12h($m[1]) . ' ' . $m[2] . ')';
    }, $row['asistencias']);
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['nombre']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['apellido']) . '</td>';
    $html .= '<td>' . htmlspecialchars($asistencias) . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody></table>';
$html .= '<div class="firma-section">';
$html .= '<div class="firma-label">Firma del Profesor:</div>';
$html .= '<div class="firma-linea"></div>';
$html .= '</div>';
$mpdf->WriteHTML($html);
$mpdf->Output('Lista_Asistencia.pdf', 'I');
