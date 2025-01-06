<?php
include 'conexion.php';

$id = $_POST['id']; // Obtener el ID de la sección desde el formulario
$salon = $_POST['salon'];
$cantidadClases = $_POST['cantidadClases'];

// Verificar que los datos necesarios están presentes
if (empty($id) || empty($salon) || empty($cantidadClases)) {
    die("Error: Todos los campos son obligatorios.");
}

// Depuración: Mostrar los datos recibidos
error_log("Datos recibidos: ID=$id, Salon=$salon, CantidadClases=$cantidadClases");

// Actualizar la sección
$sql = "UPDATE materias SET salon='$salon' WHERE id='$id'";
if ($conn->query($sql) === TRUE) {
    // Eliminar los horarios existentes para la sección
    $sqlDeleteHorarios = "DELETE FROM horariosmateria WHERE id_materia='$id'";
    if ($conn->query($sqlDeleteHorarios) === TRUE) {
        // Insertar los nuevos horarios
        for ($i = 0; $i < $cantidadClases; $i++) {
            $dia = $_POST["dia_$i"];
            $horaInicio = $_POST["inicio_$i"];
            $horaFin = $_POST["fin_$i"];

            // Depuración: Mostrar los datos de cada horario
            error_log("Insertando horario: ID=$id, Dia=$dia, HoraInicio=$horaInicio, HoraFin=$horaFin");

            $sqlInsertHorario = "INSERT INTO horariosmateria (id_materia, dia, hora_inicio, hora_fin) 
                                 VALUES ('$id', '$dia', '$horaInicio', '$horaFin')";
            if (!$conn->query($sqlInsertHorario)) {
                error_log("Error al insertar horario: " . $conn->error);
            }
        }

        echo "<script>
                alert('Sección y horarios actualizados correctamente.');
                window.location.href = 'admin_materias.php';
              </script>";
    } else {
        error_log("Error al eliminar horarios: " . $conn->error);
        echo "Error al eliminar los horarios: " . $conn->error;
    }
} else {
    error_log("Error al actualizar la sección: " . $conn->error);
    echo "Error al actualizar la sección: " . $conn->error;
}

$conn->close();
?>
