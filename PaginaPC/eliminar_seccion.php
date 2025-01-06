<?php
include 'conexion.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        // Obtener los detalles de la sección a eliminar
        $sql = "SELECT nombre, seccion FROM materias WHERE id='$id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
            $seccionEliminada = $row['seccion'];

            // Eliminar la sección
            $sql = "DELETE FROM materias WHERE id='$id'";
            if ($conn->query($sql) === TRUE) {
                // Verificar si quedan más secciones
                $sql = "SELECT COUNT(*) as total FROM materias WHERE nombre='$nombre'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                if ($row['total'] == 0) {
                    echo "<script>
                            alert('Sección eliminada correctamente. No quedan más secciones.');
                            window.location.href = 'admin_materias.php';
                          </script>";
                } else {
                    // Reordenar las secciones
                    $sql = "SELECT id, seccion FROM materias WHERE nombre='$nombre' ORDER BY seccion ASC";
                    $result = $conn->query($sql);
                    $seccionLetras = range('A', 'Z');
                    $index = 0;

                    while ($row = $result->fetch_assoc()) {
                        $newSeccion = $seccionLetras[$index++];
                        $sqlUpdate = "UPDATE materias SET seccion='$newSeccion' WHERE id=" . $row['id'];
                        $conn->query($sqlUpdate);
                    }

                    echo "<script>
                            alert('Sección eliminada y reordenada correctamente.');
                            window.location.href = 'editar_materia.php?nombre=$nombre';
                          </script>";
                }
            } else {
                echo "Error al eliminar la sección: " . $conn->error;
            }
        } else {
            echo "Sección no encontrada.";
        }
    } else {
        echo "<script>
                window.location.href = 'editar_materia.php?nombre=$nombre';
              </script>";
    }
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Sección</title>
</head>
<body>
    <script>
        if (confirm('¿Estás seguro de que deseas eliminar esta sección?')) {
            document.write('<form method="POST" id="confirmForm"><input type="hidden" name="confirm" value="yes"></form>');
        } else {
            document.write('<form method="POST" id="confirmForm"><input type="hidden" name="confirm" value="no"></form>');
        }
        document.getElementById('confirmForm').submit();
    </script>
</body>
</html>
