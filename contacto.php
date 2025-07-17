<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
require 'conexion.php';

// Validar sesión
if (!isset($_SESSION['idusuario'])) {
    header("Location: inicio.php");
    exit();
}

// Obtener nombre del usuario desde la base de datos
$idusuario = $_SESSION['idusuario'];
$sql = "SELECT nombres FROM datos_usuario WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idusuario);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows === 1) {
    $fila = $resultado->fetch_assoc();
    $_SESSION['nombres'] = $fila['nombres'];
} else {
    $_SESSION['nombres'] = "Sin registrar";
}

$mensajeEnviado = false;
$errorEnvio = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre  = htmlspecialchars($_POST['nombre']);
    $asunto  = htmlspecialchars($_POST['asunto']);
    $mensaje = htmlspecialchars($_POST['mensaje']);
    $email   = $_SESSION['email'];

    if (empty($asunto) || empty($mensaje)) {
        $errorEnvio = "Mensaje y asunto son obligatorios.";
    } else {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stonksappsoporte@gmail.com';
            $mail->Password   = 'xmgbudtscybkhaxo';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;


            $mail->setFrom($email, $nombre);
            $mail->addAddress('stonksappsoporte@gmail.com');
            $mail->isHTML(true);
            $mail->Subject = "Mensaje de: $nombre - $asunto";

            // Adjuntar imagen como embebida si existe
            $imgCid = '';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
                $tipo = mime_content_type($_FILES['imagen']['tmp_name']);
                if (in_array($tipo, $permitidos)) {
                    // Generar un cid único
                    $imgCid = md5(uniqid(time()));
                    $mail->addEmbeddedImage($_FILES['imagen']['tmp_name'], $imgCid, $_FILES['imagen']['name']);
                } else {
                    $errorEnvio = "Formato de imagen no válido. Usa JPG, PNG o GIF.";
                }
            }

            // Construir el body con o sin imagen
            $mailBody = '<div style="max-width:400px;margin:auto;padding:20px;border-radius:10px;background:#f9fafb;border:1px solid #e0e0e0;font-family:Montserrat,sans-serif;">';
            $mailBody .= '<h2 style="color:#2d8cf0;text-align:center;margin-top:0;">🎫 Ticket de Soporte</h2>';
            if ($imgCid) {
                $mailBody .= '<div style="text-align:center;margin-bottom:10px;"><img src="cid:' . $imgCid . '" style="max-width:100%;max-height:180px;border-radius:8px;box-shadow:0 2px 8px #ccc;" alt="Imagen adjunta"></div>';
            }
            $mailBody .= '<hr style="border:none;border-top:1px solid #e0e0e0;">';
            $mailBody .= '<p><strong>Nombre:</strong> ' . $nombre . '</p>';
            $mailBody .= '<p><strong>Correo:</strong> ' . $email . '</p>';
            $mailBody .= '<p><strong>Asunto:</strong> ' . $asunto . '</p>';
            $mailBody .= '<div style="background:#fffbe6;padding:10px 15px;border-radius:6px;margin:15px 0;"><strong>Mensaje:</strong><br>' . nl2br($mensaje) . '</div>';
            $mailBody .= '<hr style="border:none;border-top:1px dashed #bdbdbd;">';
            $mailBody .= '<p style="font-size:12px;color:#888;text-align:center;">Gracias por contactarnos. Te responderemos pronto.</p>';
            $mailBody .= '</div>';
            $mail->Body = $mailBody;
            $mail->AltBody = "Nombre: $nombre\nCorreo: $email\nMensaje:\n$mensaje";

            if (empty($errorEnvio)) {
                $mail->send();
                $mensajeEnviado = true;
            }

        } catch (Exception $e) {
            $errorEnvio = "Error al enviar el mensaje: " . $mail->ErrorInfo;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Formulario de Contacto</title>
  <link rel="stylesheet" href="css/formulario.css" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet" />
</head>
<body>
  <form class="formulario" action="" method="POST" enctype="multipart/form-data">
    <h1>Soporte</h1>
     <img src="css/email.png" alt="Logo" class ="imagen-central">
     <br>
    <p>Envíanos un mensaje y te responderemos lo antes posible</p>
   
    <?php if ($mensajeEnviado): ?>
    <div class="ventana-mensaje">
      <p>💌 Gracias por tu mensaje, nos pondremos en contacto contigo pronto.</p>
        <p>Redirigiendo a la página principal...</p>
    </div>
    <?php elseif (!empty($errorEnvio)): ?>
      <p style="color: red;">❌ <?php echo $errorEnvio; ?></p>
    <?php endif; ?>

    <label for="nombre">Tu nombre:</label>
    <input type="text" id="nombre" name="nombre"
      value="<?php echo isset($_SESSION['nombres']) ? $_SESSION['nombres'] : ''; ?>" readonly />

    <label for="email">Tu correo:</label>
    <input type="email" id="email" name="email"
      value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>" readonly />

    <label for="asunto">Asunto:</label>
    <input type="text" id="asunto" name="asunto" required />

    <label for="mensaje">Mensaje:</label>
    <textarea id="mensaje" name="mensaje" rows="4" required></textarea>

    <label for="imagen">Adjuntar imagen (JPG, PNG o GIF) de su problema(opcional):</label>
   <label for="imagen" class="label-imagen">Selecionar archivo</label>
<input type="file" id="imagen" name="imagen" accept="image/*" />
<img id="vista-previa" class="imagen-previsualizada" style="display:none;">


    <button type="submit">Enviar</button>
<?php if (!$mensajeEnviado): ?>
  <div class="bloque-volver">
    <label>¿No quieres enviar el mensaje?</label>
    <button type="button" onclick="window.location.href='pagina_principal.php'">
      ⏎ Volver al inicio
    </button>
  </div>
<?php endif; ?>


  </form>




  <?php if ($mensajeEnviado): ?>
    <script>
      setTimeout(() => {
        window.location.href = 'pagina_principal.php';
      }, 4000);
    </script>
  <?php endif; ?>

  <script>
    document.getElementById("imagen").addEventListener("change", function(event) {
      const file = event.target.files[0];
      if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.getElementById("vista-previa");
          img.src = e.target.result;
          img.style.display = "block";
        };
        reader.readAsDataURL(file);
      }
    });
  </script>
</body>
</html>
