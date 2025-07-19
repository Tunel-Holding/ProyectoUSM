<?php
require "conexion.php";

if (isset($_GET['id'])) {
    actualizar_actividad();
    $id_profesor = intval($_GET['id']);
    echo "<script>
        if (confirm('¿Está seguro de que desea eliminar este profesor?')) {
            window.location.href = 'eliminar_profesor_confirmado.php?id=$id_profesor';
        } else {
            window.location.href = 'admin_profesores.php';
        }
    </script>";
} else {
    echo "ID de profesor no especificado.";
}
$conn->close();
?>
