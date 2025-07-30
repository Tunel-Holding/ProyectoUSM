<?php
include 'conexion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);

// Recibe POST con lista de estudiantes únicos y crea el archivo de asistencia
date_default_timezone_set('America/Caracas');
$materia_id = isset($_GET['materia_id']) ? intval($_GET['materia_id']) : 0;
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
if ($materia_id <= 0 || empty($seccion) || empty($file)) {
    http_response_code(400);
    echo 'Parámetros inválidos';
    exit;
}

// Recibir el JSON con los estudiantes
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!isset($data['estudiantes']) || !is_array($data['estudiantes'])) {
    http_response_code(400);
    echo 'Lista de estudiantes inválida';
    exit;
}

$estudiantes = array_unique(array_map('trim', $data['estudiantes']));

// Crear carpeta si no existe
$dir = __DIR__ . '/clase/';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$filepath = $dir . basename($file);

// Escribir encabezado con hora de inicio y fecha
$hora_inicio = date('H:i:s');
$fecha = date('d-m-Y');
$contenido = "Hora de inicio: $hora_inicio | Fecha: $fecha\n";
foreach ($estudiantes as $est) {
    $contenido .= $est . "\n";
}
file_put_contents($filepath, $contenido);
echo 'Lista creada';
?>
