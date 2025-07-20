<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Datos de conexión
$servername = "199.79.62.11";
$username   = "conexftd_conexionProfesores";
$password   = "Lcar0n@2023";
$dbname     = "conexftd_proyectousm";

try {
    // Crear la conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // Mostrar mensaje si la conexión falla
    echo "<h2>Error al conectar con la base de datos:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    exit;
}
?>
