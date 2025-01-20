<?php
session_start();

$target_dir = "fotoperfil/";
$target_file = $target_dir . basename($_FILES["foto"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Verificar si el archivo es una imagen real o una imagen falsa
$check = getimagesize($_FILES["foto"]["tmp_name"]);
if ($check !== false) {
    // Verificar si la imagen es cuadrada
    if ($check[0] !== $check[1]) {
        echo "<script>alert('La foto debe ser cuadrada (igual de altura y anchura).'); window.location.href = 'foto.php';</script>";
        $uploadOk = 0;
    }
} else {
    $uploadOk = 0;
}

// Verificar si el archivo ya existe
if (file_exists($target_file)) {
    $uploadOk = 0;
}

// Verificar el tamaño del archivo
if ($_FILES["foto"]["size"] > 500000) {
    $uploadOk = 0;
}

// Permitir ciertos formatos de archivo
if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
    $uploadOk = 0;
}

// Verificar si $uploadOk es 0 debido a un error
if ($uploadOk == 0) {
    echo "<script>alert('Lo siento, tu archivo no fue subido.'); window.location.href = 'foto.php';</script>";
// Si todo está bien, intentar subir el archivo
} else {
    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
        echo "<script>alert('El archivo ". basename($_FILES["foto"]["name"]). " ha sido subido.'); window.location.href = 'foto.php';</script>";
    } else {
        echo "<script>alert('Lo siento, hubo un error al subir tu archivo.'); window.location.href = 'foto.php';</script>";
    }
}
?>
