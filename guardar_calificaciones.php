<?php
include 'comprobar_sesion.php';
header('Content-Type: application/json');

include 'conexion.php';

// Leer datos JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['task_id']) || !isset($input['calificaciones'])) {
    echo json_encode(['error' => 'Datos incompletos.']);
    exit;
}
$task_id = intval($input['task_id']);
$calificaciones = $input['calificaciones'];

$errores = [];
if (!is_array($calificaciones)) {
    echo json_encode(['success' => false, 'error' => 'El formato de calificaciones no es válido.']);
    exit;
}
foreach ($calificaciones as $item) {
    $student_id = intval($item['student_id'] ?? 0);
    $calificacion = $item['calificacion'] ?? '';
    $retro = $item['retroalimentacion'] ?? '';
    error_log("Procesando estudiante $student_id: calificacion='$calificacion', retro='$retro'");
    if (!$student_id) {
        $errores[] = "ID de estudiante inválido.";
        error_log("ID de estudiante inválido: $student_id");
        continue;
    }

    // Verificar si existe entrega para este estudiante y tarea
    $stmt_check = $conn->prepare("SELECT archivo FROM entregas_tareas WHERE id_tarea = ? AND id_alumno = ?");
    if ($stmt_check) {
        $stmt_check->bind_param("ii", $task_id, $student_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row = $result_check->fetch_assoc();
        $stmt_check->close();
        error_log("Check entrega: " . ($row ? 'EXISTE' : 'NO EXISTE'));
    } else {
        $errores[] = "Error en prepare (check) para estudiante $student_id: " . $conn->error;
        error_log("Error en prepare (check) para estudiante $student_id: " . $conn->error);
        continue;
    }

    if ($row) {
        // Ya existe entrega, actualizar calificación y retroalimentación
        $stmt = $conn->prepare("UPDATE entregas_tareas SET calificacion = ?, retroalimentacion = ? WHERE id_tarea = ? AND id_alumno = ?");
        if ($stmt) {
            $stmt->bind_param("ssii", $calificacion, $retro, $task_id, $student_id);
            if (!$stmt->execute()) {
                $errores[] = "Error con estudiante $student_id: " . $stmt->error;
                error_log("Error UPDATE estudiante $student_id: " . $stmt->error);
            } else {
                error_log("UPDATE exitoso para estudiante $student_id");
            }
            $stmt->close();
        } else {
            $errores[] = "Error en prepare para estudiante $student_id: " . $conn->error;
            error_log("Error en prepare UPDATE para estudiante $student_id: " . $conn->error);
        }
    } else {
        // No existe entrega, registrar como vencido
        $estado = 'vencido';
        $archivo = '';
        $stmt_insert = $conn->prepare("INSERT INTO entregas_tareas (id_tarea, id_alumno, archivo, estado, calificacion, retroalimentacion) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt_insert) {
            $stmt_insert->bind_param("iissss", $task_id, $student_id, $archivo, $estado, $calificacion, $retro);
            if (!$stmt_insert->execute()) {
                $errores[] = "Error insertando vencido para estudiante $student_id: " . $stmt_insert->error;
                error_log("Error INSERT estudiante $student_id: " . $stmt_insert->error);
            } else {
                error_log("INSERT exitoso para estudiante $student_id");
            }
            $stmt_insert->close();
        } else {
            $errores[] = "Error en prepare (insert) para estudiante $student_id: " . $conn->error;
            error_log("Error en prepare INSERT para estudiante $student_id: " . $conn->error);
        }
    }
}
$conn->close();
if (empty($errores)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => implode('; ', $errores)]);
}
