<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="../css/icono.png" type="image/png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Acceso Denegado - USM</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #004c97 0%, #0066cc 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .error-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 500px;
            margin: 20px;
        }

        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: #ffd700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .error-title {
            font-size: 28px;
            font-weight: 600;
            margin: 20px 0;
            color: #ffffff;
        }

        .error-message {
            font-size: 16px;
            line-height: 1.6;
            margin: 20px 0;
            color: #e0e0e0;
        }

        .error-details {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffd700;
        }

        .btn-container {
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(45deg, #ffd700, #ffcc00);
            color: #004c97;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo img {
            height: 60px;
            filter: brightness(0) invert(1);
        }

        .countdown {
            font-size: 14px;
            color: #ffd700;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .error-container {
                margin: 10px;
                padding: 30px 20px;
            }
            
            .error-code {
                font-size: 80px;
            }
            
            .error-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="logo">
            <img src="../css/logo.png" alt="Logo USM" onerror="this.style.display='none'">
        </div>
        
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Acceso Denegado</h2>
        
        <p class="error-message">
            Lo sentimos, no tienes permisos para acceder a esta página.
        </p>
        
        <div class="error-details">
            <p><strong>Posibles causas:</strong></p>
            <ul style="text-align: left; margin: 10px 0;">
                <li>No tienes el nivel de acceso requerido</li>
                <li>Tu sesión ha expirado</li>
                <li>Estás intentando acceder a una página de otro rol</li>
            </ul>
        </div>
        
        <div class="btn-container">
            <a href="javascript:history.back()" class="btn btn-secondary">Página Anterior</a>
        </div>

        <div class="countdown" id="countdown">
            Redirigiendo en <span id="timer">10</span> segundos...
        </div>
    </div>

    <script>
        // Contador regresivo
        let timeLeft = 10;
        const timerElement = document.getElementById('timer');
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(function() {
            timeLeft--;
            timerElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                countdownElement.style.display = 'none';
                // Intentar redirigir a inicio.php, si falla ir a logout.php
                window.location.href = 'javascript:history.back()';
            }
        }, 1000);

        // Función para manejar errores de redirección
        window.onerror = function() {
            // Si hay error, intentar con logout.php
            window.location.href = './logout.php';
        };
    </script>
</body>
</html> 