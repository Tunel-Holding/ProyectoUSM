<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['idusuario']) || empty($_SESSION['idusuario'])) {
    // Limpiar cualquier sesión residual
    session_destroy();
    
    // Redirigir al login con mensaje
    $_SESSION['mensaje'] = "Debe iniciar sesión para acceder a esta página.";
    header("Location: inicio.php");
    exit();
}

// Verificar que el nivel de usuario esté definido
if (!isset($_SESSION['nivelusu'])) {
    session_destroy();
    $_SESSION['mensaje'] = "Error en la sesión. Por favor, inicie sesión nuevamente.";
    header("Location: inicio.php");
    exit();
}

// Verificar si la sesión no ha expirado (opcional: 30 minutos)
$tiempo_maximo = 30 * 60; // 30 minutos en segundos
if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $tiempo_maximo) {
    // Sesión expirada
    session_destroy();
    $_SESSION['mensaje'] = "Su sesión ha expirado. Por favor, inicie sesión nuevamente.";
    header("Location: inicio.php");
    exit();
}

// Actualizar tiempo de último acceso
$_SESSION['ultimo_acceso'] = time();

// Función para verificar permisos específicos
function verificar_permiso($nivel_requerido) {
    $niveles = [
        'admin' => 3,
        'profesor' => 2,
        'alumno' => 1
    ];
    
    $nivel_usuario = $_SESSION['nivelusu'];
    $nivel_minimo = $niveles[$nivel_requerido] ?? 0;
    
    return $nivel_usuario >= $nivel_minimo;
}

// Función para verificar si el usuario es administrador
function es_admin() {
    return $_SESSION['nivelusu'] >= 3;
}

// Función para verificar si el usuario es profesor
function es_profesor() {
    return $_SESSION['nivelusu'] >= 2;
}

// Función para verificar si el usuario es alumno
function es_alumno() {
    return $_SESSION['nivelusu'] >= 1;
}

// Función para obtener información del usuario actual
function obtener_usuario_actual() {
    return [
        'id' => $_SESSION['idusuario'],
        'nivel' => $_SESSION['nivelusu'],
        'nombre_usuario' => $_SESSION['nombre_usuario'] ?? null,
        'semestre' => $_SESSION['semestre_usu'] ?? null
    ];
}

// Función para cerrar sesión de forma segura
function cerrar_sesion() {
    // Actualizar estado en la base de datos
    if (isset($_SESSION['idusuario'])) {
        try {
            include 'conexion.php';
            
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "UPDATE usuarios SET session = 0 WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['idusuario']]);
            
            $pdo = null;
        } catch (PDOException $e) {
            error_log("Error al actualizar estado de sesión: " . $e->getMessage());
        }
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Limpiar cookies de sesión
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
}
?>
