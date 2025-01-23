<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$usuario_id = $_POST['usuario_id'];
$materia_id = $_POST['materia_id'];
$parcial = $_POST['parcial'];
$target_dir = "uploads/";
$file = $_FILES['file'];
$filename = basename($file["name"]);
$target_file = $target_dir . $filename;
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Comprobar si ya existe un archivo subido para este usuario, materia y parcial
$sql_check = "SELECT * FROM archivos WHERE usuario_id = ? AND materia_id = ? AND parcial = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iii", $usuario_id, $materia_id, $parcial);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Archivo ya subido, mostrar mensaje
    $uploadOk = 0; // No permitir la carga de otro archivo
} else {
    // Validar tamaño del archivo
    if ($file["size"] > 5000000) { // Límite de 5MB
        $uploadOk = 0;
    }

    // Permitir ciertos formatos de archivo
    if($fileType != "jpg" && $fileType != "png" && $fileType != "pdf" ) {
        $uploadOk = 0;
    }

    // Comprobar si $uploadOk se ha establecido a 0 debido a un error
    if ($uploadOk == 0) {
        // No se muestra el mensaje de error
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $fecha_subida = date("Y-m-d H:i:s");

            // Insertar información del archivo en la base de datos
            $sql = "INSERT INTO archivos (usuario_id, materia_id, nombre_archivo, ruta_archivo, fecha_subida, parcial) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssi", $usuario_id, $materia_id, $filename, $target_file, $fecha_subida, $parcial);

            if ($stmt->execute()) {
                // Archivo subido y guardado correctamente, no mostrar mensaje
            } else {
                // No se muestra el mensaje de error
            }
        } else {
            // No se muestra el mensaje de error
        }
    }
}
$conn->close();
?>
