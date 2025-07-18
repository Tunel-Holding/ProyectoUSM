<?php
include 'comprobar_sesion.php';

require_once "conexion.php";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Clase para manejar la lógica de añadir profesores
 */
class AddProfesorManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Valida los datos del formulario
     */
    public function validarDatos($nombre, $nombre_usuario, $email) {
        $errores = [];
        
        // Validar nombre
        if (empty(trim($nombre))) {
            $errores[] = 'El nombre es obligatorio.';
        } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', trim($nombre))) {
            $errores[] = 'El nombre solo puede contener letras, números y espacios.';
        } elseif (strlen(trim($nombre)) > 100) {
            $errores[] = 'El nombre no puede exceder 100 caracteres.';
        }
        
        // Validar nombre de usuario
        if (empty(trim($nombre_usuario))) {
            $errores[] = 'El nombre de usuario es obligatorio.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($nombre_usuario))) {
            $errores[] = 'El nombre de usuario solo puede contener letras, números y guiones bajos.';
        } elseif (strlen(trim($nombre_usuario)) > 50) {
            $errores[] = 'El nombre de usuario no puede exceder 50 caracteres.';
        }
        
        // Validar email
        if (empty(trim($email))) {
            $errores[] = 'El email es obligatorio.';
        } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del email no es válido.';
        } elseif (strlen(trim($email)) > 100) {
            $errores[] = 'El email no puede exceder 100 caracteres.';
        }
        
        return $errores;
    }
    
    /**
     * Verifica si el nombre de usuario ya existe
     */
    public function verificarUsuarioExistente($nombre_usuario) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Verifica si el email ya existe
     */
    public function verificarEmailExistente($email) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Añade un nuevo profesor usando prepared statements
     */
    public function añadirProfesor($nombre, $nombre_usuario, $email) {
        actualizar_actividad();
        try {
            // Iniciar transacción
            $this->conn->begin_transaction();
            
            // Hash de la contraseña
            $hash = password_hash("UsMProfesor**", PASSWORD_DEFAULT);
            
            // Insertar en la tabla de usuarios
            $stmt_usuario = $this->conn->prepare("INSERT INTO usuarios (nombre_usuario, email, contrasena, nivel_usuario) VALUES (?, ?, ?, 'profesor')");
            $stmt_usuario->bind_param("sss", $nombre_usuario, $email, $hash);
            
            if (!$stmt_usuario->execute()) {
                throw new Exception('Error al crear el usuario: ' . $stmt_usuario->error);
            }
            
            $id_usuario = $this->conn->insert_id;
            
            // Insertar en la tabla de profesores
            $stmt_profesor = $this->conn->prepare("INSERT INTO profesores (id_usuario, nombre) VALUES (?, ?)");
            $stmt_profesor->bind_param("is", $id_usuario, $nombre);
            
            if (!$stmt_profesor->execute()) {
                throw new Exception('Error al crear el profesor: ' . $stmt_profesor->error);
            }
            
            // Confirmar transacción
            $this->conn->commit();
            
            return true;
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Envía email de bienvenida
     */
    public function enviarEmailBienvenida($nombre_usuario, $email) {
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8'; 
            $mail->isSMTP();
            $mail->isHTML(true);
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'modulo11usm@gmail.com';
            $mail->Password = 'aoau ilmo tglw yodm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Destinatarios y contenido
            $mail->setFrom('modulo11usm@gmail.com', 'Universidad Santa Maria');
            $mail->addAddress($email);
            $mail->Subject = 'Creación de Perfil Profesor - UniHub';
            
            // Cuerpo del email
            $mail->Body = $this->generarEmailHTML($nombre_usuario);
            
            // Enviar el correo
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Error al enviar email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Genera el HTML del email
     */
    private function generarEmailHTML($nombre_usuario) {
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Bienvenido a UniHub</title>
            <script src='js/control_inactividad.js'></script>
        </head>
        <body style='background-color: #f8f9fa; font-family: Arial, sans-serif; margin: 0; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <div style='background: linear-gradient(135deg, #61b7ff, #3a85ff); padding: 30px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 28px;'>Bienvenido a UniHub</h1>
                    <p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0;'>Universidad Santa María</p>
                </div>
                
                <div style='padding: 40px 30px;'>
                    <h2 style='color: #333; margin-bottom: 20px;'>Tu cuenta ha sido creada exitosamente</h2>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='color: #333; margin-top: 0;'>Datos de acceso:</h3>
                        <p><strong>Usuario:</strong> $nombre_usuario</p>
                        <p><strong>Contraseña:</strong> UsMProfesor**</p>
                    </div>
                    
                    <p style='color: #666; line-height: 1.6;'>
                        Ya puedes acceder al sistema UniHub con tus credenciales. 
                        Te recomendamos cambiar tu contraseña después del primer inicio de sesión.
                    </p>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='#' style='background: #61b7ff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                            Acceder al Sistema
                        </a>
                    </div>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px;'>
                    <p style='margin: 0;'>© 2024 UniHub - Universidad Santa María</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Cierra la conexión
     */
    public function cerrarConexion() {
        $this->conn->close();
    }
}

/**
 * Clase para manejar la presentación HTML
 */
class AddProfesorView {
    
    /**
     * Renderiza el header de la página
     */
    public static function renderHeader() {
        return '
        <div class="page-header">
            <div class="container">
                <h1 class="page-title">Añadir Nuevo Profesor</h1>
                <p class="page-subtitle">Crea una nueva cuenta de profesor en el sistema UniHub</p>
            </div>
        </div>';
    }
    
    /**
     * Renderiza el formulario
     */
    public static function renderForm($datos = [], $errores = []) {
        $nombre = htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
        $nombre_usuario = htmlspecialchars($datos['nombre_usuario'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($datos['email'] ?? '', ENT_QUOTES, 'UTF-8');
        
        $nombreError = in_array('nombre', $errores) ? 'error' : '';
        $usuarioError = in_array('nombre_usuario', $errores) ? 'error' : '';
        $emailError = in_array('email', $errores) ? 'error' : '';
        
        return '
        <div class="form-container">
            <form method="POST" class="admin-form" onsubmit="return validarFormulario()">
                <div class="form-group">
                    <label for="nombre" class="form-label">Nombre del Profesor</label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        value="' . $nombre . '"
                        class="form-input ' . $nombreError . '"
                        placeholder="Ingrese el nombre completo"
                        maxlength="100"
                        pattern="[a-zA-Z0-9\s]+"
                        title="Solo se permiten letras, números y espacios"
                        required
                    />
                    <div class="form-error" id="nombre-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="nombre_usuario" class="form-label">Nombre de Usuario</label>
                    <input 
                        type="text" 
                        id="nombre_usuario" 
                        name="nombre_usuario" 
                        value="' . $nombre_usuario . '"
                        class="form-input ' . $usuarioError . '"
                        placeholder="Ingrese el nombre de usuario"
                        maxlength="50"
                        pattern="[a-zA-Z0-9_]+"
                        title="Solo se permiten letras, números y guiones bajos"
                        required
                    />
                    <div class="form-error" id="usuario-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="' . $email . '"
                        class="form-input ' . $emailError . '"
                        placeholder="ejemplo@usm.edu.ve"
                        maxlength="100"
                        required
                    />
                    <div class="form-error" id="email-error"></div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Añadir Profesor
                    </button>
                    <a href="admin_profesores.php" class="btn-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>';
    }
    
    /**
     * Renderiza mensaje de éxito
     */
    public static function renderSuccess() {
        return '
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22,4 12,14.01 9,11.01"/>
            </svg>
            Profesor añadido exitosamente. Redirigiendo...
        </div>';
    }
    
    /**
     * Renderiza mensaje de error
     */
    public static function renderError($message) {
        return '
        <div class="alert alert-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '
        </div>';
    }
}

/**
 * Clase para manejar los estilos CSS
 */
class AddProfesorStyles {
    
    /**
     * Obtiene los estilos CSS específicos
     */
    public static function getStyles() {
        return '
        <style>
            /* Estilos específicos para la página de añadir profesor */
            .page-header {
                background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
                padding: 3rem 2rem;
                margin-top: 80px;
                color: var(--white);
                text-align: center;
            }

            .page-title {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
                text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .page-subtitle {
                font-size: 1.1rem;
                opacity: 0.9;
                font-weight: 400;
            }

            .main-container {
                max-width: 800px;
                margin: 0 auto;
                padding: 2rem;
            }

            .form-container {
                background: var(--white);
                border-radius: var(--border-radius);
                padding: 2rem;
                box-shadow: var(--shadow-md);
                border: 1px solid var(--gray-200);
            }

            .admin-form {
                background: transparent;
                padding: 0;
                box-shadow: none;
                margin-bottom: 0;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 600;
                color: var(--gray-900);
                font-size: 0.95rem;
            }

            .form-input {
                width: 100%;
                padding: 1rem;
                border: 2px solid var(--gray-300);
                border-radius: 8px;
                font-size: 1rem;
                transition: var(--transition);
                background: var(--white);
                color: var(--gray-900);
                font-family: "Inter", sans-serif;
            }

            .form-input:focus {
                outline: none;
                border-color: var(--primary-blue);
                box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.1);
            }

            .form-input.error {
                border-color: #dc3545;
                box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
            }

            .form-input::placeholder {
                color: var(--gray-500);
            }

            .form-error {
                color: #dc3545;
                font-size: 0.85rem;
                margin-top: 0.25rem;
                display: none;
            }

            .form-error.show {
                display: block;
            }

            .form-actions {
                display: flex;
                gap: 1rem;
                margin-top: 2rem;
                flex-wrap: wrap;
            }

            .btn-primary {
                background: linear-gradient(135deg, var(--primary-yellow), #f0d742);
                color: var(--gray-900);
                border: none;
                padding: 1rem 2rem;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: var(--transition);
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 1rem;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
            }

            .btn-secondary {
                background: transparent;
                color: var(--gray-700);
                border: 2px solid var(--gray-300);
                padding: 1rem 2rem;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: var(--transition);
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 1rem;
            }

            .btn-secondary:hover {
                background: var(--gray-100);
                border-color: var(--gray-400);
                transform: translateY(-2px);
            }

            .alert {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem 1.5rem;
                border-radius: var(--border-radius);
                margin-bottom: 2rem;
                font-weight: 500;
            }

            .alert-success {
                background: rgba(40, 167, 69, 0.1);
                border: 1px solid #28a745;
                color: #155724;
            }

            .alert-danger {
                background: rgba(220, 53, 69, 0.1);
                border: 1px solid #dc3545;
                color: #721c24;
            }

            /* Modo oscuro */
            body.dark-mode .form-container {
                background: #1e293b;
                border-color: #334155;
            }

            body.dark-mode .form-label {
                color: #f1f5f9;
            }

            body.dark-mode .form-input {
                background: #334155;
                color: #e2e8f0;
                border-color: #475569;
            }

            body.dark-mode .form-input:focus {
                border-color: #61b7ff;
                box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2);
            }

            body.dark-mode .form-input.error {
                border-color: #dc3545;
                box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
            }

            body.dark-mode .form-input::placeholder {
                color: #94a3b8;
            }

            body.dark-mode .btn-secondary {
                color: #e2e8f0;
                border-color: #475569;
            }

            body.dark-mode .btn-secondary:hover {
                background: #475569;
                border-color: #64748b;
            }

            body.dark-mode .alert-success {
                background: rgba(40, 167, 69, 0.1);
                border-color: #28a745;
                color: #d4edda;
            }

            body.dark-mode .alert-danger {
                background: rgba(220, 53, 69, 0.1);
                border-color: #dc3545;
                color: #f8d7da;
            }

            body.dark-mode .page-header {
                background: linear-gradient(135deg, #1e40af, #3b82f6);
            }

            body.dark-mode .main-container {
                background-color: #0f172a;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .page-header {
                    padding: 2rem 1rem;
                }

                .page-title {
                    font-size: 2rem;
                }

                .main-container {
                    padding: 1rem;
                }

                .form-container {
                    padding: 1.5rem;
                }

                .form-actions {
                    flex-direction: column;
                }

                .btn-primary,
                .btn-secondary {
                    justify-content: center;
                }
            }

            @media (max-width: 480px) {
                .page-title {
                    font-size: 1.75rem;
                }

                .form-container {
                    padding: 1rem;
                }

                .form-input {
                    padding: 0.75rem;
                }
            }
        </style>';
    }
}

// ========================================
// LÓGICA PRINCIPAL
// ========================================

$manager = new AddProfesorManager($conn);
$errores = [];
$datos = [];
$mensaje = '';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtener y sanitizar datos
        $nombre = trim($_POST['nombre'] ?? '');
        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $datos = [
            'nombre' => $nombre,
            'nombre_usuario' => $nombre_usuario,
            'email' => $email
        ];
        
        // Validar datos
        $errores = $manager->validarDatos($nombre, $nombre_usuario, $email);
        
        // Verificar si no hay errores de validación
        if (empty($errores)) {
            // Verificar si el usuario ya existe
            if ($manager->verificarUsuarioExistente($nombre_usuario)) {
                $errores[] = 'El nombre de usuario ya está en uso.';
            }
            
            // Verificar si el email ya existe
            if ($manager->verificarEmailExistente($email)) {
                $errores[] = 'El email ya está registrado.';
            }
            
            // Si no hay errores, proceder a crear el profesor
            if (empty($errores)) {
                if ($manager->añadirProfesor($nombre, $nombre_usuario, $email)) {
                    // Enviar email de bienvenida
                    $manager->enviarEmailBienvenida($nombre_usuario, $email);
                    
                    $mensaje = 'success';
                    
                    // Redirigir después de 2 segundos
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'admin_profesores.php';
                        }, 2000);
                    </script>";
                }
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Error en addprofesor.php: " . $e->getMessage());
    $mensaje = 'Ha ocurrido un error al procesar la solicitud. Por favor, inténtalo de nuevo.';
} finally {
    $manager->cerrarConexion();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/admin-general.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="js/control_inactividad.js"></script>
    <title>Añadir Profesor - UniHub</title>
    <?php echo AddProfesorStyles::getStyles(); ?>
</head>

<body>

    <!-- Navbar -->
    <?php include 'navAdmin.php'; ?>

    <!-- Header de la página -->
    <?php echo AddProfesorView::renderHeader(); ?>

    <!-- Contenido principal -->
    <div class="main-container">
        
        <?php if ($mensaje === 'success'): ?>
            <?php echo AddProfesorView::renderSuccess(); ?>
        <?php elseif (!empty($mensaje)): ?>
            <?php echo AddProfesorView::renderError($mensaje); ?>
        <?php endif; ?>
        
        <?php if (!empty($errores)): ?>
            <?php foreach ($errores as $error): ?>
                <?php echo AddProfesorView::renderError($error); ?>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php echo AddProfesorView::renderForm($datos, $errores); ?>
        
    </div>

    <script>
        // Validación del formulario en el lado del cliente
        function validarFormulario() {
            let isValid = true;
            
            // Limpiar errores anteriores
            document.querySelectorAll('.form-error').forEach(el => {
                el.classList.remove('show');
                el.textContent = '';
            });
            document.querySelectorAll('.form-input').forEach(el => {
                el.classList.remove('error');
            });
            
            // Validar nombre
            const nombre = document.getElementById('nombre').value.trim();
            if (nombre === '') {
                mostrarError('nombre', 'El nombre es obligatorio.');
                isValid = false;
            } else if (!/^[a-zA-Z0-9\s]+$/.test(nombre)) {
                mostrarError('nombre', 'El nombre solo puede contener letras, números y espacios.');
                isValid = false;
            } else if (nombre.length > 100) {
                mostrarError('nombre', 'El nombre no puede exceder 100 caracteres.');
                isValid = false;
            }
            
            // Validar nombre de usuario
            const usuario = document.getElementById('nombre_usuario').value.trim();
            if (usuario === '') {
                mostrarError('usuario', 'El nombre de usuario es obligatorio.');
                isValid = false;
            } else if (!/^[a-zA-Z0-9_]+$/.test(usuario)) {
                mostrarError('usuario', 'El nombre de usuario solo puede contener letras, números y guiones bajos.');
                isValid = false;
            } else if (usuario.length > 50) {
                mostrarError('usuario', 'El nombre de usuario no puede exceder 50 caracteres.');
                isValid = false;
            }
            
            // Validar email
            const email = document.getElementById('email').value.trim();
            if (email === '') {
                mostrarError('email', 'El email es obligatorio.');
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                mostrarError('email', 'El formato del email no es válido.');
                isValid = false;
            } else if (email.length > 100) {
                mostrarError('email', 'El email no puede exceder 100 caracteres.');
                isValid = false;
            }
            
            return isValid;
        }
        
        function mostrarError(campo, mensaje) {
            const input = document.getElementById(campo === 'usuario' ? 'nombre_usuario' : campo);
            const errorDiv = document.getElementById(campo === 'usuario' ? 'usuario-error' : campo + '-error');
            
            input.classList.add('error');
            errorDiv.textContent = mensaje;
            errorDiv.classList.add('show');
        }
        
        // Validación en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    const errorDiv = this.parentNode.querySelector('.form-error');
                    if (errorDiv) {
                        errorDiv.classList.remove('show');
                    }
                });
            });
        });

        // Funcionalidad del tema oscuro (compatibilidad con navAdmin.php)
        document.addEventListener("DOMContentLoaded", function() {
            const theme = localStorage.getItem("theme");
            if (theme === "dark") {
                document.body.classList.add("dark-mode");
            }
        });

        // Navegación
        function redirigir(url) {
            window.location.href = url;
        }

        window.onload = function() {
            const inicio = document.getElementById("inicio");
            const datos = document.getElementById("datos");
            const profesor = document.getElementById("profesor");
            const alumno = document.getElementById("alumno");
            const materias = document.getElementById("materias");

            if (inicio) inicio.addEventListener("click", () => redirigir("pagina_administracion.php"));
            if (datos) datos.addEventListener("click", () => redirigir("buscar_datos_admin.html"));
            if (profesor) profesor.addEventListener("click", () => redirigir("admin_profesores.php"));
            if (alumno) alumno.addEventListener("click", () => redirigir("admin_alumnos.php"));
            if (materias) materias.addEventListener("click", () => redirigir("admin_materias.php"));
        };
    </script>

</body>

</html>