<?php
include 'comprobar_sesion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/olvidostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&display=swap" rel="stylesheet">

    <title>¿Olvido su Contraseña? - USM</title>
</head>

<body>
    <div class="container">
        <div class="logos">
            <img src="https://usm.edu.ve/wp-content/uploads/2020/08/usmlgoretina-1.png" class="logo-uni1">
            <div class="barravertical"></div>
            <img src="css/logounihubazul.png" class="logo-uni1">
        </div>
        <span>Introduzca su correo electrónico</span>
        <form action="olvidarcontraseña2.php" method="post">
            <div class="container-input">
                <input type="text" name="email" placeholder="Correo Electrónico" required>
            </div>
            <input type="submit" class="button">

        </form>
    </div>
</body>

</html>