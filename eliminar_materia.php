<?php
include 'conexion.php';

$nombre = $_GET['nombre'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $sql = "DELETE FROM materias WHERE nombre='$nombre'";
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('Materia eliminada correctamente.');
                    window.location.href = 'admin_materias.php';
                  </script>";
        } else {
            echo "Error al eliminar la materia: " . $conn->error;
        }
    } else {
        echo "<script>
                window.location.href = 'admin_materias.php';
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
    <title>Eliminar Materia</title>
</head>
<body>
    <script>
        if (confirm('¿Estás seguro de que deseas eliminar la materia "<?php echo $nombre; ?>"?')) {
            document.write('<form method="POST" id="confirmForm"><input type="hidden" name="confirm" value="yes"></form>');
        } else {
            document.write('<form method="POST" id="confirmForm"><input type="hidden" name="confirm" value="no"></form>');
        }
        document.getElementById('confirmForm').submit();
    </script>
</body>
</html>
