<?php
session_start();
include 'conexion.php';


// Verificar conexión a la base de datos
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$mensaje = '';
$tipo = '';
$fp_code = '';
$enlace = '';

// Procesar el formulario de restablecimiento de contraseña
if(!empty($_POST['password']) && !empty($_POST['confirm_password']) && !empty($_POST['fp_code'])) {
    $fp_code = $_POST['fp_code'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verificar que las contraseñas coincidan
    if($password !== $confirm_password) {
        $mensaje = '❌ Las contraseñas no coinciden. Por favor, verifica que ambas contraseñas sean iguales.';
        $tipo = 'error';
    } else {
        // Verificar que el código de recuperación existe en la base de datos
        $sql = "SELECT * FROM usuarios WHERE codigo_recuperacion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $fp_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            
            // Actualizar la contraseña en la base de datos
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE usuarios SET contrasena = ?, codigo_recuperacion = NULL WHERE codigo_recuperacion = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $hashed_password, $fp_code);
            
            if($stmt_update->execute()) {
                $mensaje = '✅ ¡Contraseña actualizada exitosamente! Ya puedes iniciar sesión con tu nueva contraseña.';
                $tipo = 'success';
            } else {
                $mensaje = '❌ Error al actualizar la contraseña. Por favor, intente nuevamente.';
                $tipo = 'error';
            }
        } else {
            $mensaje = '❌ Código de recuperación inválido o expirado. Solicita un nuevo enlace de recuperación.';
            $tipo = 'error';
            $enlace = 'resetPassword.php';
        }
    }
} else if(!empty($_GET['fp_code'])) {
    // Verificar que el código de recuperación sea válido al cargar la página
    $fp_code = $_GET['fp_code'];
    $sql = "SELECT * FROM usuarios WHERE codigo_recuperacion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $fp_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 0) {
        $mensaje = '❌ Código de recuperación inválido o expirado. Solicita un nuevo enlace de recuperación.';
        $tipo = 'error';
    }
} else {
    $mensaje = '❌ Enlace de recuperación inválido. Solicita un nuevo enlace de recuperación.';
    $tipo = 'error';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - UniHub</title>
    <link rel="stylesheet" href="css/principaladministracion.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .reset-password-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #87CEEB 0%, #FFE4B5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }
        
        .reset-card {
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
        
        .reset-card::before {
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
        
        .password-requirements {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: left;
        }
        
        .password-requirements h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 14px;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
            color: #666;
            font-size: 12px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
        }
        
        @media (max-width: 480px) {
            .reset-card {
                padding: 30px 20px;
            }
            
            .title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-card">
            <div class="logo-section">
                <div class="logo-circle">UH</div>
                <h1 class="title">🔐 Restablecer Contraseña</h1>
                <p class="subtitle">Crea una nueva contraseña segura para tu cuenta</p>
            </div>
            
            <?php if(!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo; ?>">
                    <?php echo $mensaje; ?>
                    <?php if($enlace): ?>
                        <a href="<?php echo $enlace; ?>" class="back-link">Volver a recuperar contraseña</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if($tipo !== 'success' && !empty($fp_code)): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="password">🔒 Nueva Contraseña</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Ingresa tu nueva contraseña"
                               minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">🔒 Confirmar Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirma tu nueva contraseña"
                               minlength="8">
				</div>
                    
                    <input type="hidden" name="fp_code" value="<?php echo htmlspecialchars($fp_code); ?>">
                    
                    <button type="submit" class="submit-btn">
                        🔑 Cambiar Contraseña
                    </button>
			</form>
                
                <div class="password-requirements">
                    <h4>📋 Requisitos de la contraseña:</h4>
                    <ul>
                        <li>Mínimo 8 caracteres</li>
                        <li>Incluir letras mayúsculas y minúsculas</li>
                        <li>Incluir al menos un número</li>
                        <li>Incluir al menos un carácter especial</li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <p class="info-text">
                Una vez que cambies tu contraseña, podrás iniciar sesión con tus nuevas credenciales.
            </p>
            
            <a href="Ingreso.php" class="back-link">Volver al Inicio de Sesión</a>
		</div>
	</div>
</body>
</html>