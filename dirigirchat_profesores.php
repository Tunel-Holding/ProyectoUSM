<?php
include 'comprobar_sesion.php';
require 'conexion.php';
actualizar_actividad();
if (isset($_GET['valor'])) { 
    actualizar_actividad();
    $id_estudiante = $_SESSION['idusuario'];
    $_SESSION['idmateria'] = $_GET['valor'];
    $id_materia = $_GET['valor'];
    
    $sql = "SELECT nombre FROM materias WHERE id = ?"; 
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param("i", $id_materia); 
    $stmt->execute(); 
    $stmt->bind_result($nombre_materia);

    // Fetch the result to retrieve the value
    if ($stmt->fetch()) {
        $_SESSION['nombremateria'] = $nombre_materia;
        header('Location: chat_profesor.php');
    } else {
        echo "No se encontró la materia con el ID proporcionado.";
    }

    actualizar_actividad();
    $conn->close();

    
}
?>
