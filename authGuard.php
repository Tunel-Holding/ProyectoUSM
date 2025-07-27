<?php
class AuthGuard {
    const NIVEL_USUARIO = 1;
    const NIVEL_PROFESOR = 2;
    const NIVEL_ADMIN = 3;
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Debug: Registrar inicio de AuthGuard
        error_log("AuthGuard inicializado. Nivel de usuario: " . ($_SESSION['nivelusu'] ?? 'NO DEFINIDO'));
    }
    
    public function checkAccess($nivelRequerido) {
        $nivelActual = null;
        if (isset($_SESSION['nivelusu'])){
            if ($_SESSION['nivelusu'] === 'administrador' || $_SESSION['nivelusu'] === 3 || $_SESSION['nivelusu'] === '3') {
                $nivelActual = 3;
            } elseif ($_SESSION['nivelusu'] === 'profesor' || $_SESSION['nivelusu'] === 2 || $_SESSION['nivelusu'] === '2') {
                $nivelActual = 2;
            } elseif ($_SESSION['nivelusu'] === 'usuario' || $_SESSION['nivelusu'] === 1 || $_SESSION['nivelusu'] === '1') {
                $nivelActual = 1;
            } else {
                // Si el valor es inesperado pero hay sesión, asumir usuario normal
                $nivelActual = 1;
            }
        }
        if ($nivelActual != $nivelRequerido) {
            error_log("Acceso denegado. Se esperaba: $nivelRequerido, tiene: " . ($_SESSION['nivelusu'] ?? 'NULL'));
            $this->denyAccess();
        }
        
        error_log("Acceso permitido para nivel: $nivelRequerido");
    }
    
    private function denyAccess() {
        header('HTTP/1.1 403 Forbidden');
        include 'error/403.php';
        exit();
    }
}
?>
