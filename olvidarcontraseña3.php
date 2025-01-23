<?php
session_start();

$codigo_ingresado = $_POST['codigo'];
$codigo_correcto = $_SESSION['codigo'];

echo $codigo_ingresado."<br>";
echo $codigo_correcto;

if ($codigo_ingresado == $codigo_correcto) {
     // Redirigir a la página de éxito
     header("Location: olvidarcontraseña4.php");
     exit();
} else {
     // Mostrar un mensaje de error y redirigir de nuevo
     $_SESSION['mensaje'] = "Código incorrecto. Por favor, inténtelo de nuevo.";
     header("Location: PaginaPC/");
     exit();
}
?>
