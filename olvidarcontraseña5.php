<?php
session_start();
$conexion = mysqli_connect("localhost", "root", "", "proyectousm");

if (!$conexion) {
    die("Conexión fallida: " . mysqli_connect_error());
}

$email = $_SESSION['email']; // Supongamos que el email está guardado en la sesión
$new_password = password_hash($_POST['contraseña'], PASSWORD_DEFAULT); // Cifrar la contraseña

// Preparar y ejecutar la consulta de actualización
$consulta = $conexion->prepare("UPDATE usuarios SET contrasena = ? WHERE email = ?");
if (!$consulta) {
    die("Error en la preparación de la consulta: " . $conexion->error);
}
$consulta->bind_param("ss", $new_password, $email);

if ($consulta->execute()) {
    $_SESSION['mensaje'] = "La contraseña se ha establecido con éxito. Inicie sesión con su nueva contraseña";
     header("Location: inicio.php"); // Redirigir a la página de inicio o de éxito
     exit(); // Redirigir a una página de éxito
}

$consulta->close();
$conexion->close();
?>