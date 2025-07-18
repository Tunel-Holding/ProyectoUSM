<?php
include 'comprobar_sesion.php';
actualizar_actividad();
include 'conexion.php';

$materia_id = $_GET['materia_id'];
$usuario_id = $_GET['usuario_id'];

$sql = "SELECT DISTINCT parcial FROM archivos WHERE materia_id = ? AND usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $materia_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$parciales = [];

while ($row = $result->fetch_assoc()) {
    $parciales[] = $row['parcial'];
}

// Ordenar los parciales
sort($parciales);

header('Content-Type: application/json');
echo json_encode($parciales);
actualizar_actividad();
$conn->close();
?>