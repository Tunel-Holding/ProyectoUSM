<?php
include 'conexion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);

$materia_id = isset($_GET['materia_id']) ? intval($_GET['materia_id']) : 0;
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';
if ($materia_id <= 0 || empty($seccion)) {
    http_response_code(400);
    echo 'Parámetros inválidos';
    exit;
}

$query = "SELECT d.nombres, d.apellidos FROM datos_usuario d
          JOIN inscripciones i ON d.usuario_id = i.id_estudiante
          JOIN materias m ON i.id_materia = m.id
          WHERE i.id_materia = ? AND m.seccion = ?
          ORDER BY d.apellidos, d.nombres";
$stmt = $conn->prepare($query);
$stmt->bind_param('is', $materia_id, $seccion);
$stmt->execute();
$result = $stmt->get_result();

$lista = [];
while ($row = $result->fetch_assoc()) {
    $lista[] = $row['nombres'] . ' ' . $row['apellidos'];
}
$stmt->close();
$conn->close();


if (!is_dir('clase')) {
    mkdir('clase');
}
$filename = isset($_GET['file']) ? 'clase/' . basename($_GET['file']) : 'clase/lista_' . $materia_id . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $seccion) . '.txt';
// Guardar hora de inicio en la parte superior
date_default_timezone_set('America/Caracas');
$hora_inicio = date('H:i:s');
$contenido = "Hora de inicio: $hora_inicio\n" . implode("\n", $lista);
file_put_contents($filename, $contenido);
echo 'Archivo creado: ' . $filename;
?>
