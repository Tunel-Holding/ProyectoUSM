<?php
include 'comprobar_sesion.php';
actualizar_actividad();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar y validar datos de entrada
    $cedula_estudiante = htmlspecialchars(trim($_POST['id_estudiante'] ?? ''), ENT_QUOTES, 'UTF-8');
    $id_materia = filter_var($_POST['id_materia'] ?? 0, FILTER_VALIDATE_INT);
    $nueva_seccion = htmlspecialchars(trim($_POST['nueva_seccion'] ?? ''), ENT_QUOTES, 'UTF-8');
    $materia_nombre = htmlspecialchars(trim($_POST['materia_nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    
    // Validaciones
    if (empty($cedula_estudiante) || $id_materia <= 0 || empty($nueva_seccion) || empty($materia_nombre)) {
        echo "<script>alert('Todos los campos son obligatorios y deben ser válidos.'); window.history.back();</script>";
        exit();
    }
    
    // Validar formato de cédula (solo números)
    if (!preg_match('/^\d+$/', $cedula_estudiante)) {
        echo "<script>alert('La cédula debe contener solo números.'); window.history.back();</script>";
        exit();
    }

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
                            echo "<script>
                                    alert('Sección actualizada correctamente.');
                                    // Redirigir a la página de búsqueda con la cédula del estudiante ingresada
                                    window.location.href = 'admin_alumnos.php?query=" . urlencode($cedula_estudiante) . "';
                                  </script>";
                        } else {
                            error_log("Error actualizando sección en modificar_seccion_procesar.php: " . $stmt_update->error);
                            echo "<script>alert('Error al actualizar la sección.'); window.history.back();</script>";
                        }

                        $stmt_update->close();
                    } else {
                        error_log("Error preparando consulta en modificar_seccion_procesar.php: " . $conn->error);
                        echo "<script>alert('Error interno del servidor.'); window.history.back();</script>";
                    }
                } else {
                    echo "<script>alert('No se encontró la materia con la nueva sección proporcionada.'); window.history.back();</script>";
                }

                $stmt_nueva_materia->close();
            } else {
                error_log("Error preparando consulta en modificar_seccion_procesar.php: " . $conn->error);
                echo "<script>alert('Error interno del servidor.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('No se encontró el estudiante con la cédula proporcionada.'); window.history.back();</script>";
        }

        $stmt_id_estudiante->close();
    } else {
        error_log("Error preparando consulta en modificar_seccion_procesar.php: " . $conn->error);
        echo "<script>alert('Error interno del servidor.'); window.history.back();</script>";
    }
    actualizar_actividad();
    $conn->close();
}
?>
    