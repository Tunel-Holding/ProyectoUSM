<?php
// Incluir el archivo de verificación de sesión
require_once 'comprobar_sesion.php';
actualizar_actividad();
/**
 * Verificar si el usuario tiene permisos de administrador
 * Redirige al login si no tiene permisos
 */
function requerir_admin() {
    if (!es_admin()) {
        $_SESSION['mensaje'] = "Acceso denegado. Se requieren permisos de administrador.";
        header("Location: inicio.php");
        exit();
    }
}

/**
 * Verificar si el usuario tiene permisos de profesor
 * Redirige al login si no tiene permisos
 */
function requerir_profesor() {
    if (!es_profesor()) {
        $_SESSION['mensaje'] = "Acceso denegado. Se requieren permisos de profesor.";
        header("Location: inicio.php");
        exit();
    }
}

/**
 * Verificar si el usuario tiene permisos de alumno
 * Redirige al login si no tiene permisos
 */
function requerir_alumno() {
    if (!es_alumno()) {
        $_SESSION['mensaje'] = "Acceso denegado. Se requieren permisos de alumno.";
        header("Location: inicio.php");
        exit();
    }
}

/**
 * Verificar si el usuario puede acceder a una materia específica
 * @param int $id_materia ID de la materia
 * @return bool True si tiene acceso, False si no
 */
function puede_acceder_materia($id_materia) {
    require_once 'conexion.php';
    
    $usuario = obtener_usuario_actual();
    
    // Administradores pueden acceder a todas las materias
    if (es_admin()) {
        return true;
    }
    
    // Profesores pueden acceder a sus materias asignadas
    if (es_profesor()) {
        $sql = "SELECT COUNT(*) as count FROM materias WHERE id = ? AND id_profesor = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_materia, $usuario['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }
    
    // Alumnos pueden acceder a materias en las que están inscritos
    if (es_alumno()) {
        $sql = "SELECT COUNT(*) as count FROM inscripciones_materias 
                WHERE id_materia = ? AND id_estudiante = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_materia, $usuario['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }
    
    $conn->close();
    return false;
}

/**
 * Verificar si el usuario puede editar un elemento específico
 * @param string $tipo Tipo de elemento (materia, profesor, alumno, etc.)
 * @param int $id ID del elemento
 * @return bool True si puede editar, False si no
 */
function puede_editar($tipo, $id) {
    $usuario = obtener_usuario_actual();
    
    // Administradores pueden editar todo
    if (es_admin()) {
        return true;
    }
    
    switch ($tipo) {
        case 'materia':
            return puede_acceder_materia($id);
            
        case 'profesor':
            // Solo administradores pueden editar profesores
            return false;
            
        case 'alumno':
            // Solo administradores pueden editar alumnos
            return false;
            
        case 'perfil':
            // Usuarios pueden editar su propio perfil
            return $id == $usuario['id'];
            
        default:
            return false;
    }
}

/**
 * Obtener el nivel de usuario como texto
 * @return string Nivel del usuario
 */
function obtener_nivel_usuario_texto() {
    $nivel = $_SESSION['nivelusu'];
    
    switch ($nivel) {
        case 3:
            return 'Administrador';
        case 2:
            return 'Profesor';
        case 1:
            return 'Alumno';
        default:
            return 'Desconocido';
    }
}

/**
 * Verificar si la sesión está activa en la base de datos
 * @return bool True si está activa, False si no
 */
function verificar_sesion_activa() {
    require_once 'conexion.php';
    
    $usuario = obtener_usuario_actual();
    $sql = "SELECT session FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $conn->close();
    
    return $row && $row['session'] == 1;
}
?> 