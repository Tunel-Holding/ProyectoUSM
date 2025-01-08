<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

// Crear conexión
$db = mysqli_connect($servername, $username, $password, $dbname);

$nombre = $_POST['email'];
$contraseña = $_POST['pswd'];

$sql = "SELECT * FROM usuarios WHERE nombre_usuario = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $nombre);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
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
        echo "<script>alert('Contraseña incorrecta.'); window.location.href='inicio.php';</script>";
    }
} else {
    // Usuario no encontrado
    echo "<script>alert('El Usuario ingresado no Existe. Cree una cuenta.'); window.location.href='inicio.php';</script>";
}

?>
