<?php
// Mostrar errores en pantalla para depuración
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// CORS y JSON para todas las respuestas
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
// Responder preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
// Registrar errores fatales y warnings en un archivo log y enviar respuesta JSON

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $msg = "[PHP ERROR] $errstr en $errfile:$errline";
    error_log($msg, 3, __DIR__ . '/error_backend.log');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fatal en el backend',
        'details' => $msg
    ]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        $msg = "[PHP SHUTDOWN] {$error['message']} en {$error['file']}:{$error['line']}";
        error_log($msg, 3, __DIR__ . '/error_backend.log');
        if (!headers_sent()) {
            http_response_code(500);
            echo '<p style="color:red;font-weight:bold">' . htmlspecialchars($msg) . '</p>';
            echo json_encode([
                'success' => false,
                'message' => 'Error fatal en el backend',
                'details' => $msg
            ]);
        }
    }
});
$newHeaders = [
    'Content-Type: application/json',
    'Access-Control-Allow-Origin: *',
    'Access-Control-Allow-Methods: POST',
    'Access-Control-Allow-Headers: Content-Type'
];
foreach ($newHeaders as $hdr) {
    header($hdr);
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    actualizar_actividad();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

// Validar datos de entrada
if (!isset($input['profesor_id']) || !isset($input['materias_ids'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$profesor_id = intval($input['profesor_id']);
$materias_ids = $input['materias_ids']; // Array de IDs de materias

// Validar que el profesor existe
$stmt = $conn->prepare("SELECT id FROM profesores WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al validar profesor']);
    exit;
}
$stmt->bind_param("i", $profesor_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Profesor no encontrado']);
    exit;
}

// Validar que las materias existen y no están asignadas a otros profesores
if (!empty($materias_ids)) {
    $placeholders = str_repeat('?,', count($materias_ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT id, id_profesor FROM materias WHERE id IN ($placeholders)");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al validar materias']);
        exit;
    }
    
    $types = str_repeat('i', count($materias_ids));
    $stmt->bind_param($types, ...$materias_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $materias_existentes = [];
    while ($row = $result->fetch_assoc()) {
        $materias_existentes[] = $row;
    }
    
    // Verificar que todas las materias existen
    if (count($materias_existentes) !== count($materias_ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Una o más materias no existen']);
        exit;
    }
    
}

try {
    // Iniciar transacción
    $conn->begin_transaction();
    
    // Primero, desasignar todas las materias del profesor
    $stmt = $conn->prepare("UPDATE materias SET id_profesor = NULL WHERE id_profesor = ?");
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de desasignación: " . $conn->error);
    }
    $stmt->bind_param("i", $profesor_id);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la desasignación: " . $stmt->error);
    }
    
    // Luego, asignar las materias seleccionadas al profesor
    if (!empty($materias_ids)) {
        $placeholders = str_repeat('?,', count($materias_ids) - 1) . '?';
        $stmt = $conn->prepare("UPDATE materias SET id_profesor = ? WHERE id IN ($placeholders)");
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta de asignación: " . $conn->error);
        }
        $params = array_merge([$profesor_id], $materias_ids);
        $types = 'i' . str_repeat('i', count($materias_ids));
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la asignación: " . $stmt->error);
        }
    }
    // Confirmar transacción
    $conn->commit();
    // Obtener las materias actualizadas para devolver
    $stmt = $conn->prepare(
        "SELECT GROUP_CONCAT(CONCAT(nombre, ' (', seccion, ')') SEPARATOR ', ') AS materias
        FROM materias
        WHERE id_profesor = ?"
    );
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de obtención de materias: " . $conn->error);
    }
    $stmt->bind_param("i", $profesor_id);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta de obtención de materias: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Materias actualizadas correctamente',
        'materias' => $row['materias'] ?? 'Sin materias asignadas'
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($conn->connect_errno === 0) {
        $conn->rollback();
    }
    // Si el error es por array vacío, devolver mensaje claro
    $msg = $e->getMessage();
    if (strpos($msg, 'No se puede preparar la consulta de asignación') !== false || strpos($msg, 'No se puede ejecutar la asignación') !== false) {
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'message' => 'No se seleccionó ninguna materia para asignar. Si desea eliminar todas las materias, deje el campo vacío.',
            'details' => $msg
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar las materias',
            'details' => $msg
        ]);
    }
}

$conn->close();
?> 