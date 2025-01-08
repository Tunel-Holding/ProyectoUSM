<?php 
session_start(); 
$nivelusuario = $_SESSION['nivelusu']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
	<link rel="stylesheet" href="css\iniciostyle.css">
	<title>Inicio - USM</title>
</head>
<body>
	
<div class="contenedorentrante1 fixed-top" id="contenedorEntrante">
    <img src="logo.png">
</div>

<script>
	var nivelUsuario = "<?php echo $nivelusuario; ?>";
	window.onload = function() { 
        setTimeout(function() { 
            const contenedor = document.getElementById('contenedorEntrante');
            contenedor.style.transform = 'translateY(-100%)';
            setTimeout(() => {
                // Redirigir según el nivel del usuario 
                if (nivelUsuario === "usuario") { 
                    window.location.href = 'pagina_principal.php'; 
                } 
                else if (nivelUsuario === "profesor") { 
                    window.location.href = 'pagina_profesor.php'; 
                } 
                else if (nivelUsuario === "administrador") { 
                    window.location.href = 'pagina_administracion.php'; 
                } 
            }, 1000);
        }, 2500);
    };

    document.addEventListener('DOMContentLoaded', function() {
        const contenedor = document.getElementById('contenedorEntrante');
        contenedor.style.transition = 'transform 1s';
        contenedor.style.transform = 'translateY(0)';
    });

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const contenedor = document.getElementById('contenedorEntrante');
            contenedor.style.transform = 'translateY(-100%)';
            setTimeout(() => {
                form.submit();
            }, 1000);
        });
    });
</script>
</body>
</html>