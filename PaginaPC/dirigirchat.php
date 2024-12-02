<?php

    session_start();
    require 'conexion.php';
    if (isset($_GET['valor'])) { 

        $id_estudiante = $_SESSION['idusuario'];
        $_SESSION['idmateria'] = $_GET['valor'];
        
        header('Location: chat.php');
    }
?>