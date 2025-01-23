<?php
include 'conexion.php';

$nombre = $_POST['nombre'];
$creditos = $_POST['creditos'];
$nombreOriginal = $_POST['nombreOriginal']; // Obtener el nombre original de la materia

// Actualizar todas las secciones de la materia
$sql = "UPDATE materias SET nombre='$nombre', creditos='$creditos' WHERE nombre='$nombreOriginal'";
if ($conn->query($sql) === TRUE) {
    echo "<script>
            alert('Materia actualizada correctamente.');
            window.location.href = 'admin_materias.php';
          </script>";
} else {
    echo "Error al actualizar la materia: " . $conn->error;
}

$conn->close();
?>
