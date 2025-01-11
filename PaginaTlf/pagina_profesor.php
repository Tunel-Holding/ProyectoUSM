<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/iniciostyle.css">
    <title>Document</title>
</head>
<body>
    <div class="contenedorentrante1 fixed-top" id="contenedorEntrante">
        <img src="css/logo.png">
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contenedor = document.getElementById('contenedorEntrante');
            contenedor.style.transition = 'transform 1s';
            contenedor.style.transform = 'translateY(-100%)';
        });
    </script>
</body>
</html>