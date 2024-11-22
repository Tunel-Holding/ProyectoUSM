<?php
session_start();
$conexion = mysqli_connect("localhost","root","","proyectousm");
$email = $_POST['email'];
$_SESSION['email'] = $email;
$consulta = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
$consulta->bind_param("s", $email);
$consulta->execute();
$result = $consulta->get_result();


use PHPMailer\PHPMailer\PHPMailer;
require 'C:\wamp64\www\waos\vendor\autoload.php';

if ($result->num_rows > 0) {

    $mail = new PHPMailer(true);
    $codigo = mt_rand(100000, 999999);
    $_SESSION['codigo'] = $codigo;
    
    // Configuración del servidor
    $mail->isSMTP();
    $mail->isHTML(true);
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'modulo11usm@gmail.com'; // Tu correo de Gmail
    $mail->Password = 'aoau ilmo tglw yodm'; // Tu contraseña de Gmail o aplicación
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;;
    $mail->Port = 587;

    // Destinatarios y contenido
    $mail->setFrom('modulo11usm@gmail.com', 'Universidad Santa Maria');
    $mail->addAddress($email);
    $mail->Subject = 'Cambio de Clave';
    $mail->Body    ="
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link rel='preconnect' href='https://fonts.googleapis.com'>
        <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
        <link href='https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&display=swap' rel='stylesheet'>
        <title>Document</title>
    </head>
    <body style='background-color: black; font-family: \"Afacad Flux\", sans-serif; color: #baedff; text-align: center;'>
        <table width='100%' cellspacing='0' cellpadding='0'>
            <tr>
                <td style='background-color: blue; border-radius: 0 0 100% 100%; text-align: center;'>
                    <img src='https://i.ibb.co/vq9kfL9/logo.pngcss/logo.png' style='width: 170px; margin: 10px;' alt='Logo'>
                </td>
            </tr>
            <tr>
                <td style='padding: 40px 0;'>
                    <h1 style='margin: 20px; font-size: 50px;'>Cambio de Contraseña</h1>
                    <span style='font-size: 30px;'>Su código es: </span>
                    <div style='font-size: 40px; margin: 40px 0;'>$codigo</div>
                    <p>Se ha solicitado un cambio de contraseña a esta cuenta.</p>
                    <p>Si no lo ha solicitado, ignore este correo.</p>
                </td>
            </tr>
            <tr>
                <td style='background-color: blue; border-radius: 100% 100% 0 0; text-align: center; padding: 10px 0;'>
                    <h2 style='color: white; margin: 0;'>Modulo 11 - USM</h2>
                </td>
            </tr>
        </table>
    </body>
    </html>";

    // Enviar el correo
    $mail->send();
}
else {
    $_SESSION['mensaje'] = "El correo ingresado no esta registrado.";
    header("Location: http://localhost/waos/Proyecto/PaginaPC/");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="css/icono.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/olvidostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&display=swap" rel="stylesheet">
    
    <title>¿Olvido su Contraseña? - USM</title>
</head>
<body>
    <div class="container">
        <img src="https://usm.edu.ve/wp-content/uploads/2020/08/usmlgoretina-1.png" class="uni">
        <h1>Ingrese el codigo</h1>
        <span>Hemos enviado un codigo a su correo. Por favor introduzcalo</span>
        <form action="olvidarcontraseña3.php" method="post">
            <div class="container-input">
                <input type="text" name="codigo" placeholder="Codigo" required>
            </div>
            <input type="submit" class="button">   
        </form>
    </div>
    <script>
            document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['mensaje'])): ?>
                alert('<?php echo $_SESSION['mensaje']; ?>');
                <?php unset($_SESSION['mensaje']); ?>
            <?php endif; ?>
            });
    </script>
</body>
</html>