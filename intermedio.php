<?php 
include 'comprobar_sesion.php';
actualizar_actividad();
$nivelusuario = $_SESSION['nivelusu']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
	<link rel="stylesheet" href="css\style.css">
	<title>Inicio - USM</title>
    <script src="js/control_inactividad.js"></script>
</head>
<body class="body2">
	<div class="contenedorentrante2">
        <img src="css\logo.png">
    </div>
<script>
	var nivelUsuario = "<?php echo $nivelusuario; ?>";
	window.onload = function() { setTimeout(function() { 
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
	}, 2500)};
</script>
</body>
</html>