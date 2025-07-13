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
        // Actualizar la sesión del usuario de forma segura usando consulta preparada
        $sql_update = "UPDATE usuarios SET session = 1 WHERE id = ?";
        if ($stmt_update = $db->prepare($sql_update)) {
            $stmt_update->bind_param("i", $row['id']);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Si ocurre un error al preparar la consulta, registrar el error (opcional)
            error_log("Error al preparar la actualización de sesión: " . $db->error);
        }
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
        header("Location: inicio.php");
        exit();
    }
} else {
    // Usuario no encontrado
    $_SESSION['mensaje'] = "El Usuario ingresado no Existe. Cree una cuenta.";
    header("Location: inicio.php");
    exit();
}

?>