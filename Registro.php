<?php
session_start();

// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexion.php';
$db=$conn;
// Verificar conexión
if (!$db) {
    $_SESSION['mensaje'] = "Error de conexión a la base de datos: " . mysqli_connect_error();
    header("Location: inicio.php");
    exit();
}

// Verificar que se recibieron los datos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = "Método de solicitud no válido";
    header("Location: inicio.php");
    exit();
}

// Validar que los campos no estén vacíos
if (empty($_POST['nombre']) || empty($_POST['mail']) || empty($_POST['Password'])) {
    $_SESSION['mensaje'] = "Todos los campos son obligatorios";
    header("Location: inicio.php");
    exit();
}

$nombre = trim($_POST['nombre']);
$correo = trim($_POST['mail']);
$contraseña = $_POST['Password'];

// Validar formato de email
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensaje'] = "El formato del correo electrónico no es válido";
    header("Location: inicio.php");
    exit();
}

// Validar longitud del nombre de usuario
if (strlen($nombre) < 3 || strlen($nombre) > 50) {
    $_SESSION['mensaje'] = "El nombre de usuario debe tener entre 3 y 50 caracteres";
    header("Location: inicio.php");
    exit();
}

$hash = password_hash($contraseña, PASSWORD_DEFAULT);

// Verificar si el usuario o email ya existe usando prepared statements
$sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? OR email = ?"; 
$stmt = $db->prepare($sql);
if ($stmt === false) { 
    $_SESSION['mensaje'] = "Error en la preparación de la consulta: " . $db->error;
    header("Location: inicio.php");
    exit();
}
$stmt->bind_param("ss", $nombre, $correo); 
$stmt->execute(); 
$result = $stmt->get_result();

if ($result->num_rows > 0) { 
    $_SESSION['mensaje'] = "El nombre de usuario o correo ya está registrado";
} else {
    // Insertar usuario usando prepared statements
    $sql = "INSERT INTO usuarios (nombre_usuario, email, contrasena, nivel_usuario) VALUES (?, ?, ?, 'usuario')";
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        $_SESSION['mensaje'] = "Error en la preparación del INSERT: " . $db->error;
        header("Location: inicio.php");
        exit();
    }
    $stmt->bind_param("sss", $nombre, $correo, $hash);
    
    if ($stmt->execute()) {
        $idusuario = $db->insert_id;
        
        // Insertar estudiante usando prepared statements
        $carrera = "Ingenieria en Sistemas";
        $semestre = 1;
        $creditos = 20;
        
        $sql = "INSERT INTO estudiantes (id_usuario, carrera, semestre, creditosdisponibles) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        if ($stmt === false) {
            $_SESSION['mensaje'] = "Error en la preparación del INSERT de estudiante: " . $db->error;
            header("Location: inicio.php");
            exit();
        }
        $stmt->bind_param("isis", $idusuario, $carrera, $semestre, $creditos);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Registro exitoso. Usuario ID: " . $idusuario;
        } else {
            $_SESSION['mensaje'] = "Error al crear el perfil de estudiante: " . $stmt->error;
        }
    } else {
        $_SESSION['mensaje'] = "Error al registrar usuario: " . $stmt->error;
    }
}

$stmt->close();
mysqli_close($db);

header("Location: inicio.php");
exit();
?>
