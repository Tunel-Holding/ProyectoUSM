<?php
session_start(); // Iniciar sesión para manejo de mensajes
// Habilitar reporte de errores para depuración SOLO en desarrollo
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include 'conexion.php';
$db = $conn;
// Verificar conexión
if (!$db) {
    $_SESSION['mensaje'] = "Ocurrió un problema. Intente más tarde.";
    header("Location: inicio.php");
    exit();
}

// Verificar que se recibieron los datos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = "Ocurrió un problema. Intente más tarde.";
    header("Location: inicio.php");
    exit();
}

// Validar que los campos no estén vacíos
if (empty($_POST['nombre']) || empty($_POST['mail']) || empty($_POST['Password'])) {
    $_SESSION['mensaje'] = "Complete todos los campos.";
    header("Location: inicio.php");
    exit();
}

$nombre = trim($_POST['nombre']);
$correo = trim($_POST['mail']);
$contraseña = $_POST['Password'];

// Sanitizar entradas
$nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
$correo = filter_var($correo, FILTER_SANITIZE_EMAIL);

// Validar formato de email
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensaje'] = "Correo no válido.";
    header("Location: inicio.php");
    exit();
}

// Validar longitud del nombre de usuario
if (strlen($nombre) < 3 || strlen($nombre) > 50) {
    $_SESSION['mensaje'] = "Nombre de usuario inválido.";
    header("Location: inicio.php");
    exit();
}

// Validar contraseña segura
if (strlen($contraseña) < 8 || !preg_match('/[A-Z]/', $contraseña) || !preg_match('/[a-z]/', $contraseña) || !preg_match('/[0-9]/', $contraseña)) {
    $_SESSION['mensaje'] = "La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.";
    header("Location: inicio.php");
    exit();
}

$hash = password_hash($contraseña, PASSWORD_DEFAULT);

// Verificar si el usuario o email ya existe usando prepared statements
$sql = "SELECT id FROM usuarios WHERE nombre_usuario = ? OR email = ?";
$stmt = $db->prepare($sql);
if ($stmt === false) {
    $_SESSION['mensaje'] = "Ocurrió un problema. Intente más tarde.";
    header("Location: inicio.php");
    exit();
}
$stmt->bind_param("ss", $nombre, $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['mensaje'] = "No se pudo completar el registro.";
} else {
    // Insertar usuario usando prepared statements
    $sql = "INSERT INTO usuarios (nombre_usuario, email, contrasena, nivel_usuario) VALUES (?, ?, ?, 'usuario')";
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        $_SESSION['mensaje'] = "Ocurrió un problema. Intente más tarde.";
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
            $_SESSION['mensaje'] = "Ocurrió un problema. Intente más tarde.";
            header("Location: inicio.php");
            exit();
        }
        $stmt->bind_param("isis", $idusuario, $carrera, $semestre, $creditos);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Registro exitoso.";
        } else {
            $_SESSION['mensaje'] = "Ocurrió un problema. Intente más tarde.";
        }
    } else {
        $_SESSION['mensaje'] = "Ocurrió un problema. Intente más tarde.";
    }
}

$stmt->close();
$conn->close();
header("Location: inicio.php");
exit();
?>
