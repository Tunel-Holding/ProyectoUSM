<?php
require_once 'AuthGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);

require 'conexion.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_usuario = htmlspecialchars($_POST['id_usuario']);
    $cedula_estudiante = htmlspecialchars($_POST['id_estudiante']);
    $nuevo_creditos = htmlspecialchars($_POST['creditos']);


    if (is_integer($nuevo_creditos)&&is_integer($cedula_estudiante)) {
        $nuevo_creditos = intval($nuevo_creditos);
        $cedula_estudiante = intval($cedula_estudiante);
    
        if($nuevo_creditos<0){
            echo "<p>El número de créditos no puede ser negativo.</p>";
            exit;
        }
        if($cedula_estudiante<0){
            echo "<p>La cédula del estudiante no puede ser negativa.</p>";
            exit;
        }
        if(strlen(strval($cedula_estudiante))>8){
            echo "<p>La cédula del estudiante no puede ser mayor a 8 dígitos.</p>";
            exit;
        }
        if(strlen(strval($cedula_estudiante))<7){
            echo "<p>La cédula del estudiante no puede ser menor a 7 dígitos.</p>";
            exit;
        }
        $sql_estudiante = "
        SELECT  e.creditosdisponibles
        FROM datos_usuario du
        JOIN estudiantes e ON du.usuario_id = e.id_usuario
        WHERE du.cedula = ?
    ";

    if ($stmt_estudiante = $conn->prepare($sql_estudiante)) {
        $stmt_estudiante->bind_param("s", $cedula_estudiante);
        $stmt_estudiante->execute();
        $result_estudiante = $stmt_estudiante->get_result();

        if ($result_estudiante->num_rows > 0) {
            $row_estudiante = $result_estudiante->fetch_assoc();
            $creditosdisponibles = htmlspecialchars($row_estudiante['creditosdisponibles']);
        }
    }
    if($creditosdisponibles<$nuevo_creditos){
        echo "<p>El número de créditos no puede ser mayor a los créditos disponibles.</p>";
        exit;
    }
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
    } else {
        echo "<p>El número de créditos o la cédula del estudiante no es válido.</p>";
        exit;
    }
}
?>
