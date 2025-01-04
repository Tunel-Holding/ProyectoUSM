<?php
// Detectar dispositivo móvil
function Dispositivo_Movil() {
    return preg_match('/(Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini)/i', $_SERVER['HTTP_USER_AGENT']);
}

// Redirigir según el dispositivo
if (Dispositivo_Movil()) {
    header("Location: PaginaTlf/");
} else {
    header("Location: PaginaPC/");
}
exit();
?>
