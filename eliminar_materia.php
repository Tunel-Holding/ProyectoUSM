<?php
include 'conexion.php';

// Sanitizar y validar entrada
$nombre = isset($_GET['nombre']) ? trim(htmlspecialchars(strip_tags($_GET['nombre']), ENT_QUOTES, 'UTF-8')) : '';

// Validar que el nombre no esté vacío
if (empty($nombre)) {
    echo "<script>alert('Nombre de materia no válido.'); window.location.href = 'admin_materias.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    actualizar_actividad();
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        // Usar prepared statement para prevenir inyección SQL
        $sql = "DELETE FROM materias WHERE nombre = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nombre);
        if ($stmt->execute()) {
            echo "<script>
                    alert('Materia eliminada correctamente.');
                    window.location.href = 'admin_materias.php';
                  </script>";
        } else {
            echo "Error al eliminar la materia: " . $stmt->error;
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
