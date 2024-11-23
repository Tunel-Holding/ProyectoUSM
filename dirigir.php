<?php
// Detectar dispositivo móvil
function DispositivoMovil() {
    return preg_match('/(Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini)/i', $_SERVER['HTTP_USER_AGENT']);
}

// Redirigir según el dispositivo
if (DispositivoMovil()) {
    header("Location: PaginaTlf/");
} else {
    header("Location: PaginaPC/");
}
exit();
?>
