<?php
include 'comprobar_sesion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Directorio donde se guardarán los archivos
        $uploadFile = $uploadDir . basename($_FILES['file']['name']);

        // Mover el archivo subido al directorio deseado
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
            echo "El archivo ha sido subido exitosamente.";
        } else {
            echo "Error al mover el archivo.";
        }
    } else {
        echo "No se ha subido ningún archivo o ha ocurrido un error.";
    }
} else {
    echo "Método de solicitud no permitido.";
}
?>