<?php
include 'comprobar_sesion.php';
include 'conexion.php';

$id = $_POST['id']; // Obtener el ID de la sección desde el formulario
$salon = $_POST['salon'];
$cantidadClases = $_POST['cantidadClases'];

// Verificar que los datos necesarios están presentes
if (empty($id) || empty($salon) || empty($cantidadClases)) {
    die("Error: Todos los campos son obligatorios.");
}

// Obtener el nombre de la materia para la redirección
$sqlMateria = "SELECT nombre FROM materias WHERE id='$id'";
$resultMateria = $conn->query($sqlMateria);
$materia = $resultMateria->fetch_assoc();
$nombreMateria = $materia['nombre'];

// Depuración: Mostrar los datos recibidos
error_log("Datos recibidos: ID=$id, Salon=$salon, CantidadClases=$cantidadClases");

// Actualizar la sección
$sql = "UPDATE materias SET salon='$salon' WHERE id='$id'";
if ($conn->query($sql) === TRUE) {
    // Eliminar los horarios existentes para la sección
    $sqlDeleteHorarios = "DELETE FROM horariosmateria WHERE id_materia='$id'";
    if ($conn->query($sqlDeleteHorarios) === TRUE) {

        // Redirigir de vuelta a la página de editar materia con mensaje de éxito
        $redirectUrl = "editar_materia.php?nombre=" . urlencode($nombreMateria) . "&success=1";
        header("Location: $redirectUrl");
        exit();
    } else {
        error_log("Error al eliminar horarios: " . $conn->error);
        $redirectUrl = "editar_materia.php?nombre=" . urlencode($nombreMateria) . "&error=horarios";
        header("Location: $redirectUrl");
        exit();
    }
} else {
    error_log("Error al actualizar la sección: " . $conn->error);
    $redirectUrl = "editar_materia.php?nombre=" . urlencode($nombreMateria) . "&error=seccion";
    header("Location: $redirectUrl");
    exit();
}

$conn->close();
?>
