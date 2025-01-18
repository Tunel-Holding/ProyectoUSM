<?php
session_start();
require 'conexion.php';

if (!isset($_GET['id'])) {
    header("Location: inscripcion.php");
    exit();
}

$idMateria = $_GET['id'];
$idUsuario = $_SESSION['idusuario'];

if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Obtener los créditos de la materia
    $sqlCreditos = "SELECT creditos FROM materias WHERE id = ?";
    $stmtCreditos = $conn->prepare($sqlCreditos);
    $stmtCreditos->bind_param("i", $idMateria);
    $stmtCreditos->execute();
    $stmtCreditos->bind_result($creditos);
    $stmtCreditos->fetch();
    $stmtCreditos->close();

    // Eliminar la inscripción de la materia
    $sql = "DELETE FROM Inscripciones WHERE id_materia = ? AND id_estudiante = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idMateria, $idUsuario);

    if ($stmt->execute()) {
        // Eliminar los registros del horario del estudiante para la materia
        $sqlHorario = "DELETE FROM Horarios WHERE id_materia = ? AND id_estudiante = ?";
        $stmtHorario = $conn->prepare($sqlHorario);
        $stmtHorario->bind_param("ii", $idMateria, $idUsuario);
        $stmtHorario->execute();
        $stmtHorario->close();

        // Eliminar los registros de notas del estudiante para la materia
        $sqlNotas = "DELETE FROM Notas WHERE materia_id = ? AND usuario_id = ?";
        $stmtNotas = $conn->prepare($sqlNotas);
        $stmtNotas->bind_param("ii", $idMateria, $idUsuario);
        $stmtNotas->execute();
        $stmtNotas->close();

        // Actualizar los créditos disponibles del estudiante
        $sqlUpdateCreditos = "UPDATE estudiantes SET creditosdisponibles = creditosdisponibles + ? WHERE id_usuario = ?";
        $stmtUpdateCreditos = $conn->prepare($sqlUpdateCreditos);
        $stmtUpdateCreditos->bind_param("ii", $creditos, $idUsuario);
        $stmtUpdateCreditos->execute();
        $stmtUpdateCreditos->close();

        $_SESSION['mensaje'] = "Materia eliminada exitosamente.";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar la materia.";
    }

    $stmt->close();
    $conn->close();

    header("Location: inscripcion.php");
    exit();
} else {
    echo "<script>
    if (confirm('¿Estás seguro de que deseas eliminar esta materia?')) {
        window.location.href = 'eliminar_inscripcion_materia.php?id=$idMateria&confirm=yes';
    } else {
        window.location.href = 'inscripcion.php';
    }
    </script>";
}
?>
