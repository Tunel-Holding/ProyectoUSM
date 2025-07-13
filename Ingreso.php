<?php
session_start();

include 'conexion.php';

// Función para limpiar y validar entrada
function limpiar_entrada($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

try {
    // Crear conexión usando PDO para mayor seguridad
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener y validar datos de entrada
    $nombre = isset($_POST['usuario']) ? limpiar_entrada($_POST['usuario']) : '';
    $contraseña = isset($_POST['Password1']) ? $_POST['Password1'] : '';
    
    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($contraseña)) {
        throw new Exception("Todos los campos son obligatorios");
    }
    
    // Consulta preparada para prevenir inyección SQL
    $sql = "SELECT id, nombre_usuario, contrasena, nivel_usuario FROM usuarios WHERE nombre_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre]);
    
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar contraseña usando password_verify
        if (password_verify($contraseña, $row['contrasena'])) {
            // Contraseña válida - Iniciar sesión
            session_start();
            // Establecer variables de sesión
            $_SESSION['idusuario'] = $row['id'];
            $_SESSION['nivelusu'] = $row['nivel_usuario'];
            $_SESSION['nombre_usuario'] = $row['nombre_usuario'];
            $_SESSION['ultimo_acceso'] = time();
            
            // Actualizar estado de sesión en la base de datos
            $sql_update = "UPDATE usuarios SET session = 1 WHERE id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$row['id']]);
            
            // Obtener información adicional del estudiante si aplica
            if ($row['nivel_usuario'] == 1) { // Alumno
                $sql_estudiante = "SELECT semestre FROM estudiantes WHERE id_usuario = ?";
                $stmt_estudiante = $pdo->prepare($sql_estudiante);
                $stmt_estudiante->execute([$row['id']]);
                $estudiante = $stmt_estudiante->fetch(PDO::FETCH_ASSOC);
                
                if ($estudiante) {
                    $_SESSION['semestre_usu'] = $estudiante['semestre'];
                }
            }
            
            // Registrar login exitoso
            error_log("Login exitoso - Usuario: " . $row['nombre_usuario']);
            
            // Redirigir según el nivel de usuario
            switch ($row['nivel_usuario']) {
                case 3: // Administrador
                    header("Location: pagina_administracion.php");
                    break;
                case 2: // Profesor
                    header("Location: pagina_profesor.php");
                    break;
                case 1: // Alumno
                    header("Location: pagina_principal.php");
                    break;
                default:
                    header("Location: intermedio.php");
            }
            exit();
            
        } else {
            // Contraseña inválida
            $_SESSION['mensaje'] = "Usuario o contraseña incorrectos.";
            header("Location: inicio.php");
            exit();
        }
    } else {
        // Usuario no encontrado
        $_SESSION['mensaje'] = "Usuario o contraseña incorrectos.";
        header("Location: inicio.php");
        exit();
    }
    
} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error de base de datos en login: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error del sistema. Por favor, intente más tarde.";
    header("Location: inicio.php");
    exit();
    
} catch (Exception $e) {
    // Error general
    $_SESSION['mensaje'] = $e->getMessage();
    header("Location: inicio.php");
    exit();
    
} finally {
    // Cerrar conexión
    if (isset($pdo)) {
        $pdo = null;
    }
}
?> 