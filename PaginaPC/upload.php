<?php
session_start();
require 'conexion.php'; // Asegúrate de que este archivo conecta a tu base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    $fileExtension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $filePath = $target_file; // Ruta completa del archivo

    // Comprobar si el archivo es una imagen
    $check = getimagesize($_FILES["file"]["tmp_name"]);
    $tipo = ($check !== false) ? 'imagen' : 'archivo';

    // Verificar si el archivo es de un tipo permitido
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'ppt', 'pptx', 'doc', 'docx', 'xls', 'xlsx'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        die("Tipo de archivo no permitido. Solo se permiten archivos de imagen, PDF, PowerPoint, Word y Excel.");
    }

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $userId = $_SESSION['idusuario'];
        $groupId = $_SESSION['idmateria'];

        // Preparar la sentencia SQL
        $stmt = $conn->prepare("INSERT INTO messages (user_id, message, group_id, tipo) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt->bind_param("isis", $userId, $filePath, $groupId, $tipo);

        if ($stmt->execute()) {
            echo "El archivo " . htmlspecialchars(basename($_FILES["file"]["name"])) . " ha sido subido y registrado en la base de datos.";
        } else {
            echo "Error al registrar el archivo en la base de datos: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Lo siento, hubo un error al subir tu archivo.";
    }
} else {
    echo "Solicitud inválida.";
}
?>
