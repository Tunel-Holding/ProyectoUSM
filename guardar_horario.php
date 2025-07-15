<?php
include 'comprobar_sesion.php';
require 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$materia_id = $_POST['materia_id'] ?? null;
$dia = $_POST['dia'] ?? null;
$hora_inicio = $_POST['hora_inicio'] ?? null;
$hora_fin = $_POST['hora_fin'] ?? null;

// Validaciones
if (!$materia_id || !$dia || !$hora_inicio || !$hora_fin) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit();
}

// Validar que la materia existe
$sql_materia = "SELECT id FROM materias WHERE id = ?";
$stmt_materia = $conn->prepare($sql_materia);
$stmt_materia->bind_param("i", $materia_id);
$stmt_materia->execute();
$result_materia = $stmt_materia->get_result();

if ($result_materia->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'La materia no existe']);
    exit();
}

// Validar que la hora de fin sea mayor que la hora de inicio
if ($hora_fin <= $hora_inicio) {
    echo json_encode(['success' => false, 'message' => 'La hora de fin debe ser mayor que la hora de inicio']);
    exit();
}

// Verificar si ya existe un horario para este día y materia
$sql_check = "SELECT id FROM horariosmateria WHERE id_materia = ? AND dia = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("is", $materia_id, $dia);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Ya existe un horario para este día']);
    exit();
}

// Insertar el nuevo horario
$sql_insert = "INSERT INTO horariosmateria (id_materia, dia, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("isss", $materia_id, $dia, $hora_inicio, $hora_fin);

if ($stmt_insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Horario guardado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el horario: ' . $conn->error]);
}

$conn->close();
?> 