<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

// Crear conexión
$db = mysqli_connect($servername, $username, $password, $dbname);

$nombre = $_POST['name'];
$correo = $_POST['email'];
$contraseña = $_POST['pswd'];
$hash = password_hash($contraseña, PASSWORD_DEFAULT);

$sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? OR email = ?"; 
$stmt = $db->prepare($sql);
if ($stmt === false) { 
    die("Error en la preparación de la consulta: " . $db->error); 
}
$stmt->bind_param("ss", $nombre, $correo); 
$stmt->execute(); 
$result = $stmt->get_result();

if ($result->num_rows > 0) { 
    echo "<script>alert('El nombre de usuario o correo ya está registrado'); window.location.href='index.php';</script>";
} else {
    $sql= "INSERT INTO usuarios (nombre_usuario, email, contrasena, nivel_usuario) VALUES (?, ?, ?, 'usuario')";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sss", $nombre, $correo, $hash);
    if ($stmt->execute()) {
        $idusuario = $db->insert_id;
        $sql= "INSERT INTO estudiantes (id_usuario, carrera, semestre, creditosdisponibles) VALUES (?, 'Ingenieria en Sistemas', 1, 20)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $idusuario);
        $stmt->execute();
        echo "<script>alert('Registro exitoso'); window.location.href='inicio.php';</script>";
    }
}

mysqli_close($db);
?>
