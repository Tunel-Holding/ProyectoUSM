<?php
// Archivo de prueba para verificar el logout
session_start();

echo "<h1>Prueba de Logout</h1>";

if (isset($_SESSION['idusuario'])) {
    echo "<p>Usuario logueado: " . $_SESSION['idusuario'] . "</p>";
    echo "<p>Nivel: " . $_SESSION['nivelusu'] . "</p>";
    echo "<a href='logout.php'>Cerrar Sesión</a>";
} else {
    echo "<p>No hay usuario logueado</p>";
    echo "<a href='inicio.php'>Ir al Login</a>";
}
?> 