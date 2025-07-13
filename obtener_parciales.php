<?php
include 'comprobar_sesion.php';
include 'conexion.php';
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

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

$stmt->close();
$conn->close();
?>