<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
require 'conexion.php';

include 'comprobar_sesion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);

// Validar sesión
if (!isset($_SESSION['idusuario'])) {
    header("Location: inicio.php");
    exit();
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
    // Validación de token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorEnvio = "Error de seguridad. Por favor, recarga la página e intenta nuevamente.";
    } else {
        // Sanitización y validación de datos de entrada
        $nombre  = trim(htmlspecialchars(strip_tags($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'));
        $asunto  = trim(htmlspecialchars(strip_tags($_POST['asunto'] ?? ''), ENT_QUOTES, 'UTF-8'));
        $mensaje = trim(htmlspecialchars(strip_tags($_POST['mensaje'] ?? ''), ENT_QUOTES, 'UTF-8'));
        $email   = filter_var($_SESSION['email'] ?? '', FILTER_SANITIZE_EMAIL);

    // Validaciones para prevenir inyección SQL y XSS
    if (empty($asunto) || empty($mensaje)) {
        $errorEnvio = "Mensaje y asunto son obligatorios.";
    } else if (empty($nombre) || empty($email)) {
        $errorEnvio = "Nombre y correo son obligatorios.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorEnvio = "El formato del correo electrónico no es válido.";
    } else if (strlen($asunto) > 100) {
        $errorEnvio = "El asunto no puede tener más de 100 caracteres.";
    } else if (strlen($mensaje) > 5000) {
        $errorEnvio = "El mensaje no puede tener más de 5000 caracteres.";
    } else if (preg_match('/[<>"\']/', $nombre) || preg_match('/[<>"\']/', $asunto)) {
        $errorEnvio = "Los caracteres especiales no están permitidos en el nombre o asunto.";
    } else if (preg_match('/script|javascript|vbscript|onload|onerror/i', $mensaje)) {
        $errorEnvio = "El mensaje contiene contenido no permitido.";
    } else {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.conexiondocente.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'soportetecnico@conexiondocente.com';
            $mail->Password   = 'ZEZ^Q!@~Xgbv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'html';  

            $mail->setFrom($email, $nombre);
            $mail->addAddress('soportetecnico@conexiondocente.com');
            $mail->isHTML(true);
            $mail->Subject = "Mensaje de: $nombre - $asunto";

            // Adjuntar imagen como embebida si existe
            $imgCid = '';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                // Validaciones de seguridad para archivos
                $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 5 * 1024 * 1024; // 5MB máximo
                
                // Verificar tamaño del archivo
                if ($_FILES['imagen']['size'] > $maxSize) {
                    $errorEnvio = "La imagen no puede ser mayor a 5MB.";
                } else {
                    // Verificar tipo MIME real del archivo
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $tipo = finfo_file($finfo, $_FILES['imagen']['tmp_name']);
                    finfo_close($finfo);
                    
                    if (in_array($tipo, $permitidos)) {
                        // Verificar extensión del archivo
                        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($extension, $extensionesPermitidas)) {
                            // Generar un cid único
                            $imgCid = md5(uniqid(time()));
                            $mail->addEmbeddedImage($_FILES['imagen']['tmp_name'], $imgCid, $_FILES['imagen']['name']);
                        } else {
                            $errorEnvio = "Extensión de archivo no permitida. Usa JPG, PNG o GIF.";
                        }
                    } else {
                        $errorEnvio = "Formato de imagen no válido. Usa JPG, PNG o GIF.";
                    }
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
            echo "<script>alert('Error al enviar el mensaje: " . htmlspecialchars($errorEnvio, ENT_QUOTES, 'UTF-8') . "');</script>";
        }
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
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
