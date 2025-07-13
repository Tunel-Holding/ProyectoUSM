<?php
include 'comprobar_sesion.php';
include 'conexion.php'; // Asegúrate de tener una conexión a la base de datos

$target_dir = "fotoperfil/";
$target_file = $target_dir . basename($_FILES["foto"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Verificar si el archivo es una imagen real o una imagen falsa
$check = getimagesize($_FILES["foto"]["tmp_name"]);
if ($check !== false) {
    // Verificar si la imagen es cuadrada
    if ($check[0] !== $check[1]) {
        echo "<script>alert('La foto debe ser cuadrada (igual de altura y anchura).'); window.location.href = 'foto_profesor.php';</script>";
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
    echo "<script>alert('Lo siento, tu archivo no fue subido.'); window.location.href = 'foto_profesor.php';</script>";
// Si todo está bien, intentar subir el archivo
} else {
    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
        $id_profesor = $_SESSION['idusuario'];
        $foto = $target_file;

        // Verificar si el profesor ya tiene una foto
        $sql = "SELECT foto FROM fotousuario WHERE id_usuario = '$id_profesor'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            // El profesor ya tiene una foto, actualizar la columna y borrar el archivo anterior
            $row = mysqli_fetch_assoc($result);
            $old_foto = $row['foto'];
            if (file_exists($old_foto)) {
                unlink($old_foto); // Borrar el archivo anterior
            }
            $sql = "UPDATE fotousuario SET foto = '$foto' WHERE id_usuario = '$id_profesor'";
        } else {
            // El profesor no tiene una foto, insertar un nuevo registro
            $sql = "INSERT INTO fotousuario (id_usuario, foto) VALUES ('$id_profesor', '$foto')";
        }

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('El archivo ". basename($_FILES["foto"]["name"]). " ha sido subido y registrado en la base de datos.'); window.location.href = 'foto_profesor.php';</script>";
        } else {
            echo "<script>alert('Lo siento, hubo un error al registrar tu archivo en la base de datos.'); window.location.href = 'foto_profesor.php';</script>";
        }
    } else {
        echo "<script>alert('Lo siento, hubo un error al subir tu archivo.'); window.location.href = 'foto_profesor.php';</script>";
    }
}
?>
