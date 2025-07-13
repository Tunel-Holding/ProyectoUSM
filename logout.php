<?php
// Iniciar la sesión
session_start();

// Incluir la conexión a la base de datos
include 'conexion.php';

// Verificar si el usuario está autenticado antes de actualizar la sesión
if (isset($_SESSION['idusuario'])) {
    // Actualizar la sesión del usuario de forma segura usando consulta preparada
    $sql_update = "UPDATE usuarios SET session = 0 WHERE id = ?";
    if ($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param("i", $_SESSION['idusuario']);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Si ocurre un error al preparar la consulta, registrar el error
        error_log("Error al preparar la actualización de sesión: " . $conn->error);
    }
}

// Limpiar y destruir la sesión
session_unset();
session_destroy();

// Redirigir al usuario a la página de inicio de sesión
header("Location: inicio.php");
exit();
?>
