<?php
include 'comprobar_sesion.php';
actualizar_actividad();
include 'conexion.php';

// Sanitizar y validar datos de entrada
$nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
$salon = htmlspecialchars(trim($_POST['salon'] ?? ''), ENT_QUOTES, 'UTF-8');
$creditos = filter_var($_POST['creditos'] ?? 0, FILTER_VALIDATE_INT);
$semestre = filter_var($_POST['semestre'] ?? 0, FILTER_VALIDATE_INT);
$secciones = filter_var($_POST['secciones'] ?? 0, FILTER_VALIDATE_INT);

// Validaciones
if (empty($nombre) || empty($salon) || $creditos <= 0 || $semestre <= 0 || $secciones <= 0) {
    echo "<script>alert('Todos los campos son obligatorios y deben ser válidos.'); window.history.back();</script>";
    exit();
}

// Validar longitud de campos
if (strlen($nombre) > 100 || strlen($salon) > 50) {
    echo "<script>alert('Los campos exceden la longitud máxima permitida.'); window.history.back();</script>";
    exit();
}

$seccionLetras = range('A', 'Z');

if ($secciones > 0 && $secciones <= count($seccionLetras)) {
    // Usar prepared statement para prevenir inyección SQL
    $sql = "INSERT INTO materias (nombre, salon, creditos, semestre, seccion) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Error preparando consulta en procesar_materia.php: " . $conn->error);
        echo "<script>alert('Error interno del servidor.'); window.history.back();</script>";
        exit();
    }
    
    $stmt->bind_param("ssiis", $nombre, $salon, $creditos, $semestre, $seccion);
    
    for ($i = 0; $i < $secciones; $i++) {
        $seccion = $seccionLetras[$i];
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta en procesar_materia.php: " . $stmt->error);
            echo "<script>alert('Error al procesar la materia.'); window.history.back();</script>";
            $stmt->close();
            $conn->close();
            exit();
        }
    }
    $stmt->close();
    echo "<script>
            alert('Las secciones fueron añadidas correctamente.');
            window.location.href = 'admin_materias.php';
          </script>";
} else {
    echo "<script>alert('Número de secciones inválido.'); window.history.back();</script>";
}
actualizar_actividad();
$conn->close();
?>
