<?php
include 'comprobar_sesion.php';
require 'conexion.php';

// Definir nombre de la materia si no está definida
$_SESSION['nombremateria'] = isset($_SESSION['nombremateria']) ? $_SESSION['nombremateria'] : 'SalaProfesor';

$idusuario = $_SESSION['idusuario'];
// Consulta SQL para obtener solo el primer nombre y primer apellido
date_default_timezone_set('America/Caracas');
$sql = "SELECT nombres, apellidos FROM datos_usuario WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idusuario);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $primer_nombre = explode(' ', trim($row['nombres']))[0];
    $primer_apellido = explode(' ', trim($row['apellidos']))[0];
    // Prefijo para profesores
    $_SESSION['nombreusuario'] = 'Prep. ' . $primer_nombre . ' ' . $primer_apellido;
} else {
    echo "<script>alert('Debes llenar tus datos personales antes de continuar.'); window.location.href='llenar_datos_profesor.php';</script>";
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="stylesheet" href="css/horario.css">
    <link rel="stylesheet" href="css/videollamada.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Inicio - USM</title>
</head>

<body>
    <div class="contenedorentrante3">
        <img src="css\logo.png">
    </div>
    <div class="cabecera">
        <button type="button" id="logoButton">
            <img src="css/logo.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>

    <?php include 'menu_profesor.php'; ?>

    <div class="jitsi-container" id="jitsi-container">
    </div>

    <button id="record-btn" class="record-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="red" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="8" />
        </svg>
        Grabar
    </button>

    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        // Configuración de Jitsi Meet
        const domain = "meet.jit.si";
        const options = {
            roomName: "<?php echo $_SESSION['nombremateria'] . ' Sección ' . $_SESSION['seccion_materia']; ?>",
            parentNode: document.querySelector("#jitsi-container"),
            width: "100%",
            height: "100%",
            userInfo: {
                displayName: "<?php echo isset($_SESSION['nombreusuario']) ? $_SESSION['nombreusuario'] : 'Prep. Invitado'; ?>"
            },
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                BRAND_WATERMARK_LINK: '',
                TOOLBAR_BUTTONS: [
                    "microphone", "camera", "chat", "desktop", "hangup"
                ]
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    </script>
    <script src="js/zoom_api.js"></script>

</body>

</html>