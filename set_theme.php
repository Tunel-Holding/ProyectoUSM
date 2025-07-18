<?php
include 'comprobar_sesion.php';
actualizar_actividad();
if (isset($_POST['theme'])) {
    $_SESSION['theme'] = $_POST['theme'];
}
?>
