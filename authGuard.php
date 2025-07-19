<?php
// Archivo: AuthGuard.php
class AuthGuard {
    // Niveles de acceso como constantes de clase
    const NIVEL_USUARIO = 1;
    const NIVEL_PROFESOR = 2;
    const NIVEL_ADMIN = 3;
    
    private static $instance = null;
    private $requiredLevel = self::NIVEL_USUARIO;
    
    // Singleton pattern para asegurar una única instancia
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Constructor privado para prevenir creación directa
    private function __construct() {
        session_start();
        
        if (!isset($_SESSION['idusuario'])) {
            $this->redirectToLogin();
        }
    }
    
    // Método principal para verificar acceso
    public function checkAccess($requiredLevel) {
        $this->requiredLevel = $requiredLevel;
        
        if (!$this->hasRequiredLevel()) {
           // $this->logAccessAttempt();
            $this->denyAccess();
        }
        
        return true;
    }
    
    // Verifica si el usuario tiene el nivel requerido
    private function hasRequiredLevel() {
        return isset($_SESSION['nivelusu']) && 
               $_SESSION['nivelusu'] >= $this->requiredLevel;
    }
    
    // Redirección al login
    private function redirectToLogin() {
          
        header('Location: inicio.php?error=no_sesion');
        exit();
    }
    
    // Acceso denegado
    private function denyAccess() {
        header('HTTP/1.1 403 Forbidden');
        include 'error/403.php';
        exit();
    }
    /*
    // Registro de intentos no autorizados (opcional)
    private function logAccessAttempt() {
        $logMessage = sprintf(
            "[%s] Intento de acceso no autorizado. Usuario: %s, Nivel requerido: %d",
            date('Y-m-d H:i:s'),
            $_SESSION['usuario_id'] ?? 'null',
            $this->requiredLevel
        );
        
        file_put_contents('logs/access.log', $logMessage.PHP_EOL, FILE_APPEND);
    }
    */
}
?>
