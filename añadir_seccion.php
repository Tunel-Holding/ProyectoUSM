<?php
include 'conexion.php';
$nombre = $_GET['nombre'];

// Obtener la última sección de la materia
$sql = "SELECT * FROM materias WHERE nombre='$nombre' ORDER BY seccion DESC LIMIT 1";
$result = $conn->query($sql);
$ultimaSeccion = $result->fetch_assoc();

// Calcular la nueva sección
$nuevaSeccion = chr(ord($ultimaSeccion['seccion']) + 1);

// Clonar la información de la última sección
$salon = $ultimaSeccion['salon'];
$idProfesor = !empty($ultimaSeccion['id_profesor']) ? $ultimaSeccion['id_profesor'] : 'NULL';
$creditos = $ultimaSeccion['creditos'];
$semestre = $ultimaSeccion['semestre'];

// Insertar la nueva sección
$sqlInsert = "INSERT INTO materias (nombre, salon, id_profesor, creditos, semestre, seccion) 
              VALUES ('$nombre', '$salon', " . ($idProfesor === 'NULL' ? 'NULL' : "'$idProfesor'") . ", '$creditos', '$semestre', '$nuevaSeccion')";
if ($conn->query($sqlInsert) === TRUE) {
    echo "<script>
            alert('Sección añadida correctamente.');
            window.location.href = 'editar_materia.php?nombre=$nombre';
          </script>";
} else {
    echo "<script>
            alert('Error al añadir la sección: " . $conn->error . "');
            window.location.href = 'admin_materias.php';
          </script>";
}

$conn->close();
?>
