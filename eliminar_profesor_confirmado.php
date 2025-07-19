<?php
require "conexion.php";

if (isset($_GET['id'])) {
    $id_profesor = intval($_GET['id']);
    // Eliminar el profesor de la base de datos
    $sql = "DELETE FROM Profesores WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_profesor);

    if ($stmt->execute()) {
        echo "<script>
            alert('Profesor eliminado exitosamente.');
            window.location.href = 'admin_profesores.php';
        </script>";
    } else {
        echo "<script>
            alert('Error al eliminar el profesor: " . $conn->error . "');
            window.location.href = 'admin_profesores.php';
        </script>";
    }
    actualizar_actividad();
    $conn->close();
} else {
    echo "ID de profesor no especificado.";
}
actualizar_actividad();
$conn->close();
?>
