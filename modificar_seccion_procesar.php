<?php
include 'comprobar_sesion.php';
actualizar_actividad();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula_estudiante = htmlspecialchars($_POST['id_estudiante']);
    $id_materia = htmlspecialchars($_POST['id_materia']);
    $nueva_seccion = htmlspecialchars($_POST['nueva_seccion']);
    $materia_nombre = htmlspecialchars($_POST['materia_nombre']); // Añadido

    // Obtener el ID del estudiante usando la cédula
    $sql_id_estudiante = "SELECT usuario_id FROM datos_usuario WHERE cedula = ?";
    if ($stmt_id_estudiante = $conn->prepare($sql_id_estudiante)) {
        $stmt_id_estudiante->bind_param("s", $cedula_estudiante);
        $stmt_id_estudiante->execute();
        $result_id_estudiante = $stmt_id_estudiante->get_result();

        if ($result_id_estudiante->num_rows > 0) {
            $row_id_estudiante = $result_id_estudiante->fetch_assoc();
            $id_estudiante = $row_id_estudiante['usuario_id'];

            // Obtener el nuevo ID de la materia con la nueva sección
            $sql_nueva_materia = "SELECT id FROM materias WHERE nombre = ? AND seccion = ?";
            if ($stmt_nueva_materia = $conn->prepare($sql_nueva_materia)) {
                $stmt_nueva_materia->bind_param("ss", $materia_nombre, $nueva_seccion); // Ajuste en parámetros
                $stmt_nueva_materia->execute();
                $result_nueva_materia = $stmt_nueva_materia->get_result();

                if ($result_nueva_materia->num_rows > 0) {
                    $row_nueva_materia = $result_nueva_materia->fetch_assoc();
                    $nuevo_id_materia = $row_nueva_materia['id'];

                    // Actualizar la inscripción del estudiante a la nueva sección en la base de datos
                    $sql_update = "UPDATE inscripciones SET id_materia = ? WHERE id_estudiante = ? AND id_materia = ?";
                    if ($stmt_update = $conn->prepare($sql_update)) {
                        $stmt_update->bind_param("iii", $nuevo_id_materia, $id_estudiante, $id_materia);

                        if ($stmt_update->execute()) {
                            echo "<p>La sección de la materia se ha actualizado correctamente.</p>";
                            echo "<script>
                                    alert('Sección actualizada correctamente.');
                                    // Redirigir a la página de búsqueda con la cédula del estudiante ingresada
                                    window.location.href = 'admin_alumnos.php?query=" . $cedula_estudiante . "';
                                  </script>";
                        } else {
                            echo "<p>Error al actualizar la sección: " . $stmt_update->error . "</p>";
                        }

                        $stmt_update->close();
                    } else {
                        echo "<p>Error al preparar la consulta de actualización: " . $conn->error . "</p>";
                    }
                } else {
                    echo "<p>Error: No se encontró la materia con la nueva sección proporcionada.</p>";
                }

                $stmt_nueva_materia->close();
            } else {
                echo "<p>Error al preparar la consulta para obtener el ID de la nueva materia: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Error: No se encontró el estudiante con la cédula proporcionada.</p>";
        }

        $stmt_id_estudiante->close();
    } else {
        echo "<p>Error al preparar la consulta para obtener el ID del estudiante: " . $conn->error . "</p>";
    }
    actualizar_actividad();
    $conn->close();
}
?>
    