<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

// Crear conexión
$db = mysqli_connect($servername, $username, $password, $dbname);

$nombre = $_POST['usuario'];
$contraseña = $_POST['Password1'];

$sql = "SELECT * FROM usuarios WHERE nombre_usuario = '$nombre'";
$result = mysqli_query($db, $sql);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    if (password_verify($contraseña, $row['contrasena'])) {
        // Contraseña válida
        $_SESSION['idusuario'] = $row['id'];
        $_SESSION['nivelusu'] = $row['nivel_usuario'];
        $sql = "SELECT semestre FROM estudiantes WHERE id_usuario = ?"; 
        $stmt = $db->prepare($sql); 
        $stmt->bind_param("i", $_SESSION['idusuario']); 
        $stmt->execute(); 
        $stmt->bind_result($semestre);
        if ($stmt->fetch()) { 
            $semestre_usuario = $semestre;
        }
        $_SESSION['semestre_usu'] = $semestre_usuario;
        header("Location: intermedio.php");
        exit();
    } else {
        // Contraseña inválida
        $_SESSION['mensaje'] = "Contraseña incorrecta.";
        header("Location: http://localhost/waos/Proyecto/PaginaPC/");
        exit();
    }
} else {
    // Usuario no encontrado
    $_SESSION['mensaje'] = "El Usuario ingresado no Existe. Cree una cuenta.";
    header("Location: http://localhost/waos/Proyecto/PaginaPC/");
    exit();
}

?>