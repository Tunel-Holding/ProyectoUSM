<?php
session_start();
require 'conexion.php';

$_SESSION['nombremateria'] = isset($_SESSION['nombremateria']) ? $_SESSION['nombremateria'] : 'Materia no definida';

$idusuario = $_SESSION['idusuario'];
// Consulta SQL para obtener solo el primer nombre y primer apellido
date_default_timezone_set('America/Caracas'); // Ajusta la zona horaria si es necesario
$sql = "SELECT nombres, apellidos FROM datos_usuario WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idusuario);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $primer_nombre = explode(' ', trim($row['nombres']))[0];
    $primer_apellido = explode(' ', trim($row['apellidos']))[0];
    // Puedes usar $primer_nombre y $primer_apellido donde lo necesites
    $_SESSION['nombreusuario'] = $primer_nombre . ' ' . $primer_apellido;
} else {
    echo "<script>alert('Debes llenar tus datos personales antes de continuar.'); window.location.href='llenar_datos.php';</script>";
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
    <link rel="stylesheet" href="css/principalalumnostyle.css">
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

    <?php include 'menu_alumno.php'; ?>

    <div class="jitsi-container" id="jitsi-container">
    </div>

    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        const domain = "meet.jit.si";
        const options = {
            roomName: "<?php echo $_SESSION['nombremateria'] . ' Sección ' . $_SESSION['seccion_materia']; ?>",
            parentNode: document.querySelector("#jitsi-container"),
            width: "100%",
            height: "100%",
            userInfo: {
                displayName: "<?php echo isset($_SESSION['nombreusuario']) ? $_SESSION['nombreusuario'] : 'Invitado'; ?>"
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    "microphone", "camera", "chat", "desktop", "raisehand", "recording", "hangup"
                ]
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    </script>
</body>

</html>