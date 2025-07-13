<?php
include 'comprobar_sesion.php';
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = htmlspecialchars($_POST['id_usuario']);
    $cedula_estudiante = htmlspecialchars($_POST['id_estudiante']);
    $nuevo_creditos = htmlspecialchars($_POST['creditos']);

    // Actualizar los créditos disponibles del estudiante
    $sql_actualizar_creditos = "
        UPDATE estudiantes
        SET creditosdisponibles = ?
        WHERE id_usuario = ?
    ";

    if ($stmt_actualizar_creditos = $conn->prepare($sql_actualizar_creditos)) {
        $stmt_actualizar_creditos->bind_param("ii", $nuevo_creditos, $id_usuario);

        if ($stmt_actualizar_creditos->execute()) {
            echo "<p>Créditos disponibles actualizados correctamente.</p>";
            echo "<script>
                    alert('Créditos disponibles actualizados correctamente.');
                    // Redirigir a la página de búsqueda del estudiante con los datos actualizados
                    window.location.href = 'admin_alumnos.php?query=" . $cedula_estudiante . "';
                  </script>";
        } else {
            echo "<p>Error al actualizar los créditos: " . $stmt_actualizar_creditos->error . "</p>";
        }

        $stmt_actualizar_creditos->close();
    } else {
        echo "<p>Error al preparar la consulta de actualización: " . $conn->error . "</p>";
    }

    $conn->close();
}
?>
