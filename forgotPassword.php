<?php
session_start();

// Verificar si el usuario viene de modificar contraseña desde su perfil
$usuario_autenticado = isset($_SESSION['idusuario']) && !empty($_SESSION['idusuario']);
// Si viene del perfil y está autenticado, cerrar sesión por seguridad
if ($usuario_autenticado) {
    require_once 'comprobar_sesion.php';
    cerrar_sesion();
    $mensaje = '🔒 Sesión cerrada por seguridad. Por favor, inicia sesión con tu nueva contraseña.';
    $tipo = 'success';
}

include 'conexion.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar conexión a la base de datos
if ($conn->connect_error) {
          die("Conexión fallida: " . $conn->connect_error);
}

// Función para enviar email usando PHPMailer
function enviarEmailRecuperacion($email, $nombre_usuario, $resetPassLink) {
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
        $mail->setFrom('modulo11usm@gmail.com', 'UniHub - Universidad Santa Maria');
          $mail->addAddress($email);
        $mail->Subject = '🔐 Recuperación de Contraseña - UniHub';
        
        // Cuerpo del email moderno
        $mailContent = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Recuperación de Contraseña</title>
            <style>
                                 body {
                     font-family: "Poppins", Arial, sans-serif;
                     margin: 0;
                     padding: 0;
                     background: linear-gradient(135deg, #87CEEB 0%, #FFE4B5 100%);
                     min-height: 100vh;
                 }
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: white;
                    border-radius: 20px;
                    overflow: hidden;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                }
                                 .header {
                     background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
                     padding: 40px 30px;
                     text-align: center;
                     color: white;
                 }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: 600;
                }
                .header p {
                    margin: 10px 0 0 0;
                    opacity: 0.9;
                    font-size: 16px;
                }
                .content {
                    padding: 40px 30px;
                    text-align: center;
                }
                .greeting {
                    font-size: 18px;
                    color: #333;
                    margin-bottom: 20px;
                }
                .message {
                    font-size: 16px;
                    color: #666;
                    line-height: 1.6;
                    margin-bottom: 30px;
                }
                                 .reset-button {
                     display: inline-block;
                     background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
                     color: white;
                     text-decoration: none;
                     padding: 15px 30px;
                     border-radius: 50px;
                     font-weight: 600;
                     font-size: 16px;
                     margin: 20px 0;
                     transition: all 0.3s ease;
                     box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
                 }
                 .reset-button:hover {
                     transform: translateY(-2px);
                     box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
                 }
                .warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 10px;
                    padding: 15px;
                    margin: 20px 0;
                    color: #856404;
                    font-size: 14px;
                }
                .footer {
                    background: #f8f9fa;
                    padding: 30px;
                    text-align: center;
                    color: #666;
                    font-size: 14px;
                }
                                 .logo {
                     width: 80px;
                     height: 80px;
                     background: white;
                     border-radius: 50%;
                     margin: 0 auto 20px;
                     display: flex;
                     align-items: center;
                     justify-content: center;
                     font-size: 24px;
                     font-weight: bold;
                     color: #FFD700;
                 }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <div class="logo">UH</div>
                    <h1>🔐 Recuperación de Contraseña</h1>
                    <p>UniHub - Universidad Santa Maria</p>
                </div>
                
                <div class="content">
                    <div class="greeting">
                        Hola <strong>' . htmlspecialchars($nombre_usuario) . '</strong>,
                    </div>
                    
                    <div class="message">
                        Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en UniHub. 
                        Si no realizaste esta solicitud, puedes ignorar este correo electrónico de forma segura.
                    </div>
                    
                    <a href="' . $resetPassLink . '" class="reset-button">
                        🔑 Restablecer Contraseña
                    </a>
                    
                    <div class="warning">
                        <strong>⚠️ Importante:</strong> Este enlace expirará en 24 horas por seguridad. 
                        Si no puedes hacer clic en el botón, copia y pega este enlace en tu navegador:<br>
                                                 <a href="' . $resetPassLink . '" style="color: #FFD700; word-break: break-all;">' . $resetPassLink . '</a>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                    <p>© 2024 UniHub - Universidad Santa Maria. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail->Body = $mailContent;
          
          // Enviar el correo
          $mail->send();
          return true;
          
      } catch (Exception $e) {
          error_log("Error al enviar email: " . $e->getMessage());
          return false;
      }
  }

// Procesar la solicitud de recuperación de contraseña
if(!empty($_POST['email'])) {
          $email = $_POST['email'];
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $usuario_data = $result->fetch_assoc();
        $nivel_usuario = $usuario_data['nivel_usuario'];
        
        // Obtener información del usuario según su nivel
        $nombre_usuario = '';
        if ($nivel_usuario == 'administrador') {
            $sql_usuario = "SELECT nombres, apellidos, cedula FROM datos_usuario WHERE email = ?";
            $stmt_usuario = $conn->prepare($sql_usuario);
            $stmt_usuario->bind_param("s", $email);
            $stmt_usuario->execute();
            $result_usuario = $stmt_usuario->get_result();
            
            if($result_usuario->num_rows > 0) {
                $usuario = $result_usuario->fetch_assoc();
                if (!empty($usuario['nombres']) && !empty($usuario['apellidos'])) {
                    $nombre_usuario = $usuario['nombres'] . ' ' . $usuario['apellidos'];
                } else if (!empty($usuario['apellidos'])) {
                    $nombre_usuario = $usuario['apellidos'];
                } else {
                    $nombre_usuario = $usuario['cedula'];
                }
            }
        } else if ($nivel_usuario == 'profesor') {
            $sql_usuario = "SELECT nombres, cedula FROM profesores WHERE email = ?";
            $stmt_usuario = $conn->prepare($sql_usuario);
            $stmt_usuario->bind_param("s", $email);
            $stmt_usuario->execute();
            $result_usuario = $stmt_usuario->get_result();
            
            if($result_usuario->num_rows > 0) {
                $usuario = $result_usuario->fetch_assoc();
                if (!empty($usuario['nombres'])) {
                    $nombre_usuario = $usuario['nombres'];
                } else {
                    $nombre_usuario = $usuario['cedula'];
                }
            }
        } else {
            $sql_usuario = "SELECT cedula FROM estudiantes WHERE email = ?";
            $stmt_usuario = $conn->prepare($sql_usuario);
            $stmt_usuario->bind_param("s", $email);
            $stmt_usuario->execute();
            $result_usuario = $stmt_usuario->get_result();
            
            if($result_usuario->num_rows > 0) {
                $usuario = $result_usuario->fetch_assoc();
                $nombre_usuario = $usuario['cedula'];
            }
        }
        
        // Generar string único para el enlace de recuperación
        $uniqidStr = md5(uniqid(mt_rand()));
        
        // Actualizar la base de datos con el código de recuperación
        $sql_update = "UPDATE usuarios SET codigo_recuperacion = ? WHERE email = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ss", $uniqidStr, $email);
        
        if($stmt_update->execute()) {
            // Crear enlace de recuperación
            $resetPassLink = 'http://localhost/ProyectoUSM/resetPassword.php?fp_code=' . $uniqidStr;
            
            // Enviar email de recuperación
            if(enviarEmailRecuperacion($email, $nombre_usuario, $resetPassLink)) {
                $mensaje = '✅ ¡Perfecto! Hemos enviado un enlace de recuperación a tu correo electrónico. Revisa tu bandeja de entrada y sigue las instrucciones.';
                $tipo = 'success';
            } else {
                $mensaje = '❌ Error al enviar el correo electrónico. Por favor, intente nuevamente o contacta al administrador.';
                $tipo = 'error';
            }
        } else {
            $mensaje = '❌ Se produjo un problema técnico. Por favor, intente nuevamente.';
            $tipo = 'error';
        }
    } else {
        $mensaje = '❌ El correo electrónico no está registrado en nuestro sistema.';
        $tipo = 'error';
    }
} else if(!$usuario_autenticado) {
    // Usuario viene del perfil y ya se cerró la sesión
    $mensaje = '🔒 Sesión cerrada por seguridad. Por favor, inicia sesión con tu nueva contraseña.';
    $tipo = 'success';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - UniHub</title>
    <link rel="stylesheet" href="css/principaladministracion.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .forgot-password-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #87CEEB 0%, #FFE4B5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }
        
        .forgot-card {
            background: white;
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .forgot-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        }
        
        .logo-section {
            margin-bottom: 30px;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        .title {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            margin-bottom: 20px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.3);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            color: #FFD700;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link:hover {
            color: #FFA500;
        }
        
        .back-link::before {
            content: '←';
            font-size: 18px;
        }
        
        .info-text {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .success-section {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .success-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .success-section h3 {
            color: #155724;
            margin: 0 0 15px 0;
            font-size: 20px;
            font-weight: 600;
        }
        
        .success-section p {
            color: #155724;
            margin: 0 0 10px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        
        @media (max-width: 480px) {
            .forgot-card {
                padding: 30px 20px;
            }
            
            .title {
                font-size: 24px;
            }
        }
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #ffd700;
            color: #004c97;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 1px 2px 6px rgba(0,0,0,0.08);
            transition: background 0.2s, color 0.2s;
            z-index: 1000;
        }
        .back-link:hover {
            background: #ffcc00;
            color: #222;
        }
    </style>
</head>
<body>
    
    <div class="forgot-password-container">
    <a href="inicio.php" class="back-link">Volver al Inicio de Sesión</a>
        <div class="forgot-card">
            <div class="logo-section">
                <div class="logo-circle">UH</div>
                <h1 class="title">🔐 Recuperar Contraseña</h1>
                <p class="subtitle">Ingresa tu correo electrónico para recibir un enlace de recuperación</p>
            </div>
            
            <?php if(isset($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
           
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">📧 Correo Electrónico</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="ejemplo@usm.edu.ve"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <button type="submit" class="submit-btn">
                    📤 Enviar Enlace de Recuperación
                </button>
            </form>
            
            <p class="info-text">
                Te enviaremos un enlace seguro a tu correo electrónico para que puedas restablecer tu contraseña.
            </p>
            
        
        </div>
    </div>
</body>
</html>