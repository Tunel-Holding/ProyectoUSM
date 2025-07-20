<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);

require 'conexion.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = htmlspecialchars($_POST['id_usuario']);
    $cedula_estudiante = htmlspecialchars($_POST['id_estudiante']);

    $errores = [];
    $actualizaciones = 0;

    // Recorrer todos los campos enviados
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'calificacion_') === 0) {
            $id_tarea = substr($key, strlen('calificacion_'));
            $calificacion = strtoupper(trim($value));

            // Validar que la calificación sea 'A' o 'NA'
            if ($calificacion !== 'A' && $calificacion !== 'NA') {
                $errores[] = "La calificación para la tarea ID $id_tarea no es válida (solo se permite 'A' o 'NA').";
                continue;
            }

            // Actualizar la calificación en la base de datos
            $sql_update = "UPDATE entregas_tareas SET calificacion = ? WHERE id_alumno = ? AND id_tarea = ?";
            if ($stmt = $conn->prepare($sql_update)) {
                $stmt->bind_param("sii", $calificacion, $id_usuario, $id_tarea);
                if ($stmt->execute()) {
                    $actualizaciones++;
                } else {
                    $errores[] = "Error al actualizar la calificación de la tarea ID $id_tarea: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errores[] = "Error al preparar la consulta para la tarea ID $id_tarea: " . $conn->error;
            }
        }
    }

    if (count($errores) > 0) {
        echo "<p>Se produjeron los siguientes errores:</p><ul>";
        foreach ($errores as $err) {
            echo "<li>" . htmlspecialchars($err) . "</li>";
        }
        echo "</ul>";
    }

    if ($actualizaciones > 0) {
        echo "<p>Se actualizaron $actualizaciones calificaciones correctamente.</p>";
        echo "<script>
                alert('Calificaciones actualizadas correctamente.');
                window.location.href = 'modificar_calificaciones.php?id_estudiante=" . urlencode($cedula_estudiante) . "';
              </script>";
    } elseif (count($errores) === 0) {
        echo "<p>No se recibieron calificaciones para actualizar.</p>";
    }

    $conn->close();
}
?>
