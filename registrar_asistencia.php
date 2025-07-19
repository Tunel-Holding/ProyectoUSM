<?php
// Si solo se quiere guardar la hora de fin
if (isset($_POST['hora_fin'])) {
    $filename = isset($_POST['file']) ? 'clase/' . basename($_POST['file']) : '';
    if ($filename && file_exists($filename)) {
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        $new_lines = [];
        $hora_inicio = '';
        $hora_fin = $_POST['hora_fin'];
        $primera_linea = true;
        foreach ($lines as $line) {
            if ($primera_linea && strpos($line, 'Hora de inicio:') === 0) {
                $hora_inicio = trim(str_replace('Hora de inicio:', '', $line));
                $new_lines[] = 'Hora de inicio: ' . $hora_inicio . ' | Hora de fin: ' . $hora_fin;
                $primera_linea = false;
            } else if (strpos($line, 'Hora de fin:') === 0) {
                // Omitir línea de hora de fin antigua
                continue;
            } else {
                $new_lines[] = $line;
            }
        }
        file_put_contents($filename, implode("\n", $new_lines) . "\n");
    }
    exit;
}

// Recibe materia_id, seccion, estudiante, hora, tipo (A o R)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$materia_id = isset($_POST['materia_id']) ? intval($_POST['materia_id']) : 0;
$seccion = isset($_POST['seccion']) ? $_POST['seccion'] : '';
$estudiante = isset($_POST['estudiante']) ? $_POST['estudiante'] : '';
$hora = isset($_POST['hora']) ? $_POST['hora'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
if ($materia_id <= 0 || empty($seccion) || empty($estudiante) || empty($hora) || !in_array($tipo, ['A','R'])) {
    http_response_code(400);
    exit;
}

$filename = isset($_POST['file']) ? 'clase/' . basename($_POST['file']) : 'clase/lista_' . $materia_id . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $seccion) . '.txt';
if (!file_exists($filename)) {
    file_put_contents($filename, "");
}

$lines = file($filename, FILE_IGNORE_NEW_LINES);
$new_lines = [];
$registro = ' (' . $hora . ' ' . $tipo . ')';
$estudiante = trim($estudiante);
$found = false;

foreach ($lines as $line) {
    $parts = explode(' ', $line, 3);
    $nombre_apellido = trim($parts[0] . ' ' . (isset($parts[1]) ? $parts[1] : ''));
    if ($nombre_apellido === $estudiante) {
        $asistencias = isset($parts[2]) ? $parts[2] : '';
        // Solo registrar retiro si ya existe una asistencia y NO hay una R previa
        if ($tipo === 'R') {
            if (strpos($asistencias, 'A') !== false && strpos($asistencias, 'R') === false) {
                $line = $nombre_apellido . ($asistencias ? ' ' . $asistencias : '') . $registro;
            }
            // Si ya hay una R, no modificar la línea
        } else {
            $line = $nombre_apellido . ($asistencias ? ' ' . $asistencias : '') . $registro;
        }
        $found = true;
    }
    $new_lines[] = $line;
}
if (!$found && $tipo === 'A') {
    $new_lines[] = $estudiante . $registro;
}
// Si es retiro y no hay asistencia previa, no se agrega nada
file_put_contents($filename, implode("\n", $new_lines) . "\n");
?>
