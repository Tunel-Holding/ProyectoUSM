<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&display=swap" rel="stylesheet">
    <title>Document</title>
    <style>
        body{
            width: 100%;
            height: auto;
            background-color: gray;
            display: flex;
            justify-content: center;
            flex-direction: column;
            font-family: "Afacad Flux", sans-serif;
        }
        .logo{
            width: 170px;
            height: auto;
            margin: 10px 10px 10px 10px;
        }
        .header{
            background-color: blue;
            width: 100%;
            display: flex;
            justify-content: center;
            border-radius: 0 0 100% 100%;
        }
        .footer{
            position: relative;
            background-color: blue;
            width: 100%;
            display: flex;
            justify-content: center;
            border-radius: 100% 100% 0 0;
        }
        h2{
            color: white;
        }
        .contenido{
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #baedff;
        }
        .contenido h1{
            margin-top: 40px;
            margin-bottom: 40px;
            font-size: 60px;
        }
        .contenido p{
            margin-top: 5px;
            margin-bottom: 5px;
        }
        .contenido span{
            font-size: 40px;
        }
        .codigo{
            font-size: 50px;
            margin-top: 40px;
            margin-bottom: 60px;
        }
    </style>
</head>
<body>
    
    <div class="header">
        <img src="css\logo.png" class="logo">
    </div>
    <div class="contenido">
        <h1>Cambio de Contraseña</h1>
        <span>Su código es: </span>

        <?php
        $codigo = mt_rand(100000, 999999);
        echo "<span class='codigo'>$codigo</span>";
        ?>


        <p>Se ha solicitado un cambio de contraseña a esta cuenta.</p>
        <p>Si no lo ha solicitado, ignore este correo.</p>
    </div>
    <div class="footer">
        <h2>Modulo 11 - USM</h2>
    </div>

</body>
</html>