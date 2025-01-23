<?php
include 'conexion.php';

$nombre = $_POST['nombre'];
$salon = $_POST['salon'];
$creditos = $_POST['creditos'];
$semestre = $_POST['semestre'];
$secciones = intval($_POST['secciones']); // Convertir a entero

$seccionLetras = range('A', 'Z');

if ($secciones > 0 && $secciones <= count($seccionLetras)) {
    for ($i = 0; $i < $secciones; $i++) {
        $seccion = $seccionLetras[$i];
        $sql = "INSERT INTO materias (nombre, salon, creditos, semestre, seccion) VALUES ('$nombre', '$salon', '$creditos', '$semestre', '$seccion')";
        if ($conn->query($sql) !== TRUE) {
            echo "Error: " . $sql . "<br>" . $conn->error;
            $conn->close();
            exit();
        }
    }
    echo "<script>
            alert('Las secciones fueron añadidas correctamente.');
            window.location.href = 'admin_materias.php';
          </script>";
} else {
    echo "Número de secciones inválido.";
}

$conn->close();
?>
