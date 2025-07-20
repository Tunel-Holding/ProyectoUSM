<?php
$servername = "md-32";
$username = "conexftd_conexionProfesores";
$password = "Lcar0n@2023";
$dbname = "conexftd_proyectousm";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Configurar charset
$conn->set_charset("utf8");
?>
