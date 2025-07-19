<?php
require 'conexion.php';
header('Content-Type: application/json');

// Validaciones de seguridad
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener y sanitizar datos del JSON
$input = json_decode(file_get_contents('php://input'), true);
$horario_id = filter_var($input['horario_id'] ?? null, FILTER_VALIDATE_INT);
$materia_id = filter_var($input['materia_id'] ?? null, FILTER_VALIDATE_INT);

// Validaciones
if (!$horario_id || !$materia_id) {
    echo json_encode(['success' => false, 'message' => 'ID de horario y materia son requeridos']);
    exit();
}

// Verificar que el horario existe y pertenece a la materia
$sql_check = "SELECT id FROM horariosmateria WHERE id = ? AND id_materia = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $horario_id, $materia_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'El horario no existe o no pertenece a esta materia']);
    exit();
}
// Eliminar el horario
$sql_delete = "DELETE FROM horariosmateria WHERE id = ? AND id_materia = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("ii", $horario_id, $materia_id);

if ($stmt_delete->execute()) {
    echo json_encode(['success' => true, 'message' => 'Horario eliminado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el horario: ' . $conn->error]);
}
$conn->close();
?> 