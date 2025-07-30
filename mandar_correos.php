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
        $mail->Subject = '¡Bienvenido a UniHub! - Creación de tu cuenta de estudiante (Incluye guía instruccional)';

        // Cuerpo del email
        $mail->Body = generarEmailHTMLEstudiante($username, $password);

        // Adjuntar la guía instruccional
        $ruta_guia = __DIR__ . '/css/Guia Instruccional Estudiante - UniHub.pdf';
        if (file_exists($ruta_guia)) {
            $mail->addAttachment($ruta_guia, 'Guia Instruccional Estudiante - UniHub.pdf');
        }

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

                <div style='background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 8px; padding: 18px; margin: 25px 0 10px 0; font-size: 16px;'>
                    <strong>¡Importante!</strong> Recuerda ingresar a la página y completar tus datos antes de las <b>6:00 p.m.</b> para aparecer en la lista de asistencia del sistema.
                </div>
                
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


// --- ENVÍO DE CORREO DE PRUEBA A UNA SOLA PERSONA ---
// Cambia estos valores por los datos de prueba deseados
$usuario = 'MLeon';
$correo = 'leon.abogado20@gmail.com';
$clave = 'UsMAlumno0**';

if (enviarEmailBienvenidaEstudiante($usuario, $correo, $clave)) {
    echo "Correo de prueba enviado a $usuario ($correo)";
} else {
    echo "<span style='color:red'>Fallo al enviar el correo de prueba a $usuario ($correo)</span>";
}


?>