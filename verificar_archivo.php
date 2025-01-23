<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$usuario_id = $_POST['usuario_id'];
$materia_id = $_POST['materia_id'];
$parcial = $_POST['parcial'];

// Comprobar si ya existe un archivo subido para este usuario, materia y parcial
$sql_check = "SELECT * FROM archivos WHERE usuario_id = ? AND materia_id = ? AND parcial = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iii", $usuario_id, $materia_id, $parcial);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Archivo ya subido, mostrar mensaje
    echo "Archivo subido";
} else {
    // No hay archivo subido
    echo "No hay archivo subido";
}

$conn->close();
?>