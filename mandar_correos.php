<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarEmailBienvenidaEstudiante($username, $email, $password) {
    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->isHTML(true);
        $mail->Host = 'mail.conexiondocente.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'unihub@conexiondocente.com';
        $mail->Password = 'unihubconexiondocente**';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Destinatarios y contenido
        $mail->setFrom('unihub@conexiondocente.com', 'UniHub');
        $mail->addAddress($email);
        $mail->Subject = '¡Bienvenido a UniHub! - Creación de tu cuenta de estudiante';

        // Cuerpo del email
        $mail->Body = generarEmailHTMLEstudiante($username, $password);

        // Enviar el correo
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error al enviar email de bienvenida al estudiante: " . $e->getMessage());
        return false;
    }
}

function generarEmailHTMLEstudiante($nombre_usuario, $password) {
    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Bienvenido a UniHub</title>
    </head>
    <body style='background-color: #f8f9fa; font-family: Arial, sans-serif; margin: 0; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #61b7ff, #3a85ff); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>¡Bienvenido a UniHub!</h1>
                <p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0;'>Universidad Santa María</p>
            </div>
            
            <div style='padding: 40px 30px;'>
                <h2 style='color: #333; margin-bottom: 20px;'>Tu cuenta de estudiante ha sido creada exitosamente</h2>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #333; margin-top: 0;'>Tus datos de acceso son:</h3>
                    <p><strong>Usuario:</strong> $nombre_usuario</p>
                    <p><strong>Contraseña:</strong> $password</p>
                </div>
                
                <p style='color: #666; line-height: 1.6;'>
                    Ya puedes acceder al sistema UniHub con tus credenciales. 
                    Te recomendamos guardar esta información en un lugar seguro.
                </p>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='http://www.conexiondocente.com' target='_blank' style='background-color: #3a85ff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Ir a la Página</a>
                </div>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px;'>
                <p style='margin: 0;'>© 2024 UniHub - Universidad Santa María</p>
            </div>
        </div>
    </body>
    </html>";
}




// --- ENVÍO MASIVO DE CORREOS A TODOS LOS ESTUDIANTES ---
require_once 'conexion.php';
require_once 'vendor/autoload.php';


$sql = "SELECT nombre_usuario, email FROM usuarios WHERE nivel_usuario = 'usuario'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $enviados = 0;
    $fallidos = 0;
    while ($row = $result->fetch_assoc()) {
        $usuario = $row['nombre_usuario'];
        $correo = $row['email'];
        $clave = 'UsMAlumno0**';
        if (enviarEmailBienvenidaEstudiante($usuario, $correo, $clave)) {
            $enviados++;
            echo "Correo enviado a $usuario ($correo)<br>";
        } else {
            $fallidos++;
            echo "<span style='color:red'>Fallo al enviar a $usuario ($correo)</span><br>";
        }
        // Puedes poner sleep(1); si quieres evitar bloqueos por spam
    }
    echo "<hr><b>Correos enviados: $enviados. Fallidos: $fallidos.</b>";
} else {
    echo "No se encontraron estudiantes para enviar correos.";
}

?>