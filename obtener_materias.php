<?php
include 'comprobar_sesion.php';
actualizar_actividad();
header('Content-Type: application/json');
require "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $profesor_id = $_GET['profesor_id'] ?? null;
    
    try {
        // Obtener todas las materias disponibles
        $sql_todas = "SELECT id, nombre, seccion FROM materias ORDER BY nombre, seccion";
        $result_todas = $conn->query($sql_todas);
        
        $todas_materias = [];
        while ($row = $result_todas->fetch_assoc()) {
            $todas_materias[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'seccion' => $row['seccion'],
                'texto_completo' => $row['nombre'] . ' (' . $row['seccion'] . ')'
            ];
        }
        
        // Si se proporciona un profesor_id, obtener sus materias asignadas
        $materias_asignadas = [];
        if ($profesor_id) {
            $sql_asignadas = "SELECT id FROM materias WHERE id_profesor = ?";
            $stmt_asignadas = $conn->prepare($sql_asignadas);
            $stmt_asignadas->bind_param("i", $profesor_id);
            $stmt_asignadas->execute();
            $result_asignadas = $stmt_asignadas->get_result();
            
            while ($row = $result_asignadas->fetch_assoc()) {
                $materias_asignadas[] = $row['id'];
            }
        }
        
        echo json_encode([
            'success' => true,
            'todas_materias' => $todas_materias,
            'materias_asignadas' => $materias_asignadas
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener materias: ' . $e->getMessage()]);
    }
    actualizar_actividad();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?> 