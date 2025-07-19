<?php
include 'comprobar_sesion.php';
actualizar_actividad();
include 'conexion.php';

$usuario_id = $_POST['usuario_id'];
$materia_id = $_POST['materia_id'];
$parcial = $_POST['parcial'];

// Obtener la ruta del archivo desde la base de datos
$sql_select = "SELECT ruta_archivo FROM archivos WHERE usuario_id = ? AND materia_id = ? AND parcial = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("iii", $usuario_id, $materia_id, $parcial);
$stmt_select->execute();
$result_select = $stmt_select->get_result();

if ($result_select->num_rows > 0) {
    $row = $result_select->fetch_assoc();
    $ruta_archivo = $row['ruta_archivo'];

    // Eliminar archivo del servidor
    if (file_exists($ruta_archivo)) {
        unlink($ruta_archivo);
    }
actualizar_actividad();
    // Eliminar registro de la base de datos
    $sql_delete = "DELETE FROM archivos WHERE usuario_id = ? AND materia_id = ? AND parcial = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("iii", $usuario_id, $materia_id, $parcial);

    if ($stmt_delete->execute()) {
        echo "Archivo eliminado correctamente";
    } else {
        echo "Error al eliminar el archivo";
    }
} else {
    echo "No se encontró el archivo";
}
actualizar_actividad();
$conn->close();
?>
