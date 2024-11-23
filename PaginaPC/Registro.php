<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

// Crear conexión
$db = mysqli_connect($servername, $username, $password, $dbname);

$nombre = $_POST['nombre'];
$correo = $_POST['mail'];
$contraseña = $_POST['Password'];
$hash = password_hash($contraseña, PASSWORD_DEFAULT);

$sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? OR email = ?"; 
$stmt = $db->prepare($sql);
if ($stmt === false) { 
    die("Error en la preparación de la consultaa: " . $db->error); 
}
$stmt->bind_param("ss", $nombre, $correo); 
$stmt->execute(); 
$result = $stmt->get_result();

if ($result->num_rows > 0) { 
    $_SESSION['mensaje'] = "El nombre de usuario o correo ya esta registrado";
} else {
    $sql= "INSERT INTO usuarios (nombre_usuario, email, contrasena, nivel_usuario) VALUES ('$nombre', '$correo', '$hash', 'usuario')";
    if (mysqli_query($db, $sql)) {
        $_SESSION['mensaje'] = "Registro exitoso";
    }
}

mysqli_close($db);

header("Location: PaginaPC/");
exit()

?>
