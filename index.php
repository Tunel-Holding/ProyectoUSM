<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universidad Santa Maria</title>
    <link rel="icon" href="PaginaPC/css/icono.png" type="image/png">
</head>
<body>

    <?php
        // Obtenemos el user agent del navegador
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        // Detectamos si es un dispositivo móvil
        if(preg_match('/Mobile|Android|BlackBerry|iPhone|Windows Phone/', $user_agent)) {
            // Redireccionamos a la página para dispositivos móviles
            header('Location: /ProyectoUSM/PaginaTlf');
            exit;
        } else {
            // Redireccionamos a la página para computadoras
            header('Location: /ProyectoUSM/PaginaPC');
            exit;
        }
    ?>
    
</body>
</html>
