<?php
include 'comprobar_sesion.php';
include 'conexion.php';

header('Content-Type: application/json');
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_tarea'])) {
    $id_tarea = $_POST['id_tarea'];
    $id_alumno = $_SESSION['idusuario'];

    $directorio_subida = 'uploads/';
    $nombre_archivo_original = basename($_FILES['archivo_tarea']['name']);
    $nombre_archivo_unico = time() . '_' . uniqid() . '_' . $nombre_archivo_original;
    $ruta_archivo = $directorio_subida . $nombre_archivo_unico;

    if (move_uploaded_file($_FILES['archivo_tarea']['tmp_name'], $ruta_archivo)) {
        $sql = "INSERT INTO entregas_tareas (id_tarea, id_alumno, archivo, fecha_entrega) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $response['success'] = false;
            $response['error'] = "Error al preparar la consulta: " . $conn->error;
            echo json_encode($response);
            exit();
        }
        $stmt->bind_param("iis", $id_tarea, $id_alumno, $ruta_archivo);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Tarea entregada con éxito.";
        } else {
            $response['success'] = false;
            $response['error'] = "Error al registrar la entrega en la base de datos: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['success'] = false;
        $response['error'] = "Error al subir el archivo: " . $_FILES['archivo_tarea']['error'];
    }
} else {
    $response['success'] = false;
    $response['error'] = "Solicitud no válida.";
}

echo json_encode($response);
exit();
?>