<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principaladministracion.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Agregar Estudiante - USM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            --background-color: rgb(255, 255, 255);
            --bg-container: rgb(240, 240, 240);
            color: #333;
        }

        body.dark-mode {
            --background-color: #1a1a1a;
            --bg-container: rgb(47, 47, 47);
            color: white;
        }

        .container {
            max-width: 90%;
            margin: auto;
            text-align: center;
            background-color: var(--bg-container);
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 80px;
            margin-bottom: 30px;
        }

        .titulo {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            margin-top: 30px;
            color: #333333;
            font-family: 'Roboto', sans-serif;
        }

        body.dark-mode .titulo {
            color: #ffffff;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
            max-width: 500px;
        }

        .form-container input {
            padding: 12px 20px;
            border-radius: 40px;
            border: 1px solid #ccc;
            font-size: 16px;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .form-container input:focus {
            border-color: #446ad3;
            box-shadow: 0 0 10px rgba(68, 106, 211, 0.3);
            outline: none;
        }

        .submit-button {
            padding: 12px 24px;
            border-radius: 40px;
            background-color: rgba(68, 106, 211, 1);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .submit-button:hover {
            background-color: #365ac0;
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <?php include 'navAdmin.php'; ?>
    <div class="container">
        <h1 class="titulo">Agregar Nuevo Estudiante</h1>
        <form action="procesar_agregar_estudiante.php" method="post" class="form-container">
            <input type="text" name="username" placeholder="Nombre de Usuario" required>
            <input type="email" name="email" placeholder="Correo Electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="submit-button">Agregar Estudiante</button>
        </form>
    </div>
</body>

</html>
