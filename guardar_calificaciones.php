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

    // Obtener usuario_id real (id_alumno) desde datos_usuario
    $stmt_userid = $conn->prepare("SELECT usuario_id FROM datos_usuario WHERE id = ?");
    if ($stmt_userid) {
        $stmt_userid->bind_param("i", $student_id);
        $stmt_userid->execute();
        $stmt_userid->bind_result($usuario_id);
        $stmt_userid->fetch();
        $stmt_userid->close();
        if (!$usuario_id) {
            $errores[] = "No se encontró usuario_id para estudiante $student_id";
            continue;
        }
    } else {
        $errores[] = "Error al obtener usuario_id para estudiante $student_id: " . $conn->error;
        continue;
    }

    // Verificar si existe entrega para este estudiante y tarea
    $stmt_check = $conn->prepare("SELECT archivo FROM entregas_tareas WHERE id_tarea = ? AND id_alumno = ? ORDER BY id_entrega DESC LIMIT 1");
    if ($stmt_check) {
        $stmt_check->bind_param("ii", $task_id, $usuario_id);
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
        // Ya existe entrega (con o sin archivo), actualizar calificación y retroalimentación
        $query_update = "UPDATE entregas_tareas SET calificacion = ?, retroalimentacion = ? WHERE id_tarea = ? AND id_alumno = ?";
        // Si hay archivo, filtrar por archivo específico
        if (!empty($row['archivo'])) {
            $query_update .= " AND archivo = ?";
        }
        $stmt = $conn->prepare($query_update);
        if ($stmt) {
            if (!empty($row['archivo'])) {
                $stmt->bind_param("ssiss", $calificacion, $retro, $task_id, $usuario_id, $row['archivo']);
            } else {
                $stmt->bind_param("ssis", $calificacion, $retro, $task_id, $usuario_id);
            }
            if (!$stmt->execute()) {
                $errores[] = "Error con estudiante $student_id: " . $stmt->error;
                error_log("Error UPDATE estudiante $student_id: " . $stmt->error);
            } else {
                error_log("UPDATE exitoso para estudiante $student_id");
            }
            $stmt->close();
        } else {
            $errores[] = "Error en prepare UPDATE para estudiante $student_id: " . $conn->error;
            error_log("Error en prepare UPDATE para estudiante $student_id: " . $conn->error);
        }
    } else {
        // No existe entrega previa, registrar como vencido
        $estado = 'vencido';
        $archivo = '';
        $stmt_insert = $conn->prepare("INSERT INTO entregas_tareas (id_tarea, id_alumno, archivo, estado, calificacion, retroalimentacion) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt_insert) {
            $stmt_insert->bind_param("iissss", $task_id, $usuario_id, $archivo, $estado, $calificacion, $retro);
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
