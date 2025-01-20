<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$materia_id = $_GET['materia_id'];
$usuario_id = $_GET['usuario_id'];
$parcial_num = $_GET['parcial'];

$sql = "SELECT ruta_archivo FROM archivos WHERE materia_id = ? AND usuario_id = ? AND parcial = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $materia_id, $usuario_id, $parcial_num);
$stmt->execute();
$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row['ruta_archivo'];
}

header('Content-Type: application/json');
echo json_encode($data);

$stmt->close();
$conn->close();

error_log("Materias ID: $materia_id, Usuario ID: $usuario_id, Parcial: $parcial_num, Rutas: " . json_encode($data));
?>