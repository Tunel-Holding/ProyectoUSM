<?php
// Incluir el sistema de sesiones mejorado
require_once 'comprobar_sesion.php';

// Cerrar sesión de forma segura usando la función del sistema
cerrar_sesion();

// Redirigir al usuario a la página de inicio de sesión
header("Location: inicio.php");
exit();
?>
