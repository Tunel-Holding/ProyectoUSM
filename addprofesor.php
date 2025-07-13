<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principaladministracion.css">
    <link rel="stylesheet" href="css/admin_profesores.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Añadir profesor</title>
</head>

<body>

    <div class="cabecera">

        <button type="button" id="logoButton">
            <img src="css/logoazul.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_administrador.php'; ?>

    <h1>Añadir Profesores</h1>
    <div class="formulario">
        <form action="addprofesor.php" method="POST">
            <label for="nombre">Nombre del Profesor:</label>
            <input type="text" id="nombre" name="nombre" required><br><br>

            <label for="nombre_usuario">Nombre de Usuario:</label>
            <input type="text" id="nombre_usuario" name="nombre_usuario" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <input type="submit" value="Añadir Profesor">
        </form>
    </div>

    <?php
    require "conexion.php";

    use PHPMailer\PHPMailer\PHPMailer;

    require 'vendor/autoload.php';
    // Procesar el formulario al enviarlo
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'];
        $nombre_usuario = $_POST['nombre_usuario'];
        $email = $_POST['email'];
        $hash = password_hash("UsMProfesor**", PASSWORD_DEFAULT);

        // Insertar en la tabla de usuarios
        $sql_usuario = "INSERT INTO usuarios (nombre_usuario, email, contrasena, nivel_usuario) VALUES ('$nombre_usuario', '$email', '$hash', 'profesor')";

        if ($conn->query($sql_usuario) === TRUE) {
            // Obtener el ID del usuario recién insertado
            $id_usuario = $conn->insert_id;

            // Insertar en la tabla de profesores
            $sql_profesor = "INSERT INTO profesores (id_usuario, nombre) VALUES ('$id_usuario', '$nombre')";

            if ($conn->query($sql_profesor) === TRUE) {

                $mail = new PHPMailer(true);
                // Configuración del servidor
                $mail->isSMTP();
                $mail->isHTML(true);
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'modulo11usm@gmail.com'; // Tu correo de Gmail
                $mail->Password = 'aoau ilmo tglw yodm'; // Tu contraseña de Gmail o aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                ;
                $mail->Port = 587;

                // Destinatarios y contenido
                $mail->setFrom('modulo11usm@gmail.com', 'Universidad Santa Maria');
                $mail->addAddress($email);
                $mail->Subject = 'Creacion de Perfil Profesor';
                $mail->Body = "
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
                                <h1 style='margin: 20px; font-size: 50px;'>Bienvenido a la USM</h1>
                                <span style='font-size: 30px;'>Sus dato s son: </span>
                                <div style='font-size: 40px; margin: 40px 0;'>Usuario: $nombre_usuario </div>
                                <div style='font-size: 40px; margin: 40px 0;'>Contraseña: UsMProfesor**</div>
                                <p>Ingrese a nuestro sistema para iniciar su cuenta</p>
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

                echo "<script>
                        alert('Profesor añadido con éxito.');
                        window.location.href = 'admin_profesores.php';
                      </script>";
            } else {
                echo "Error al añadir el profesor: " . $conn->error;
            }
        } else {
            echo "Error al añadir el usuario: " . $conn->error;
        }
    }

    // Cerrar conexión
    $conn->close();

    ?>

</body>

</html>