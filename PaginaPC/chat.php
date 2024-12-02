<?php
session_start();
require 'conexion.php';

$_SESSION['idusuario'] = 4;

if (!isset($_SESSION['idusuario'])) {
    header("Location: login.php");
    exit();
}

// Enviar mensaje
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $user_id = $_SESSION['idusuario'];

    $stmt = $conn->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
    exit(); // Salir después de insertar el mensaje
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <link rel="stylesheet" href="css/chat.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Chat</h1>
    
    <div id="chat-box">
        <!-- Aquí se cargarán los mensajes mediante AJAX -->
    </div>

    <form id="message-form" enctype="multipart/form-data">
        <input type="text" id="message" name="message" required>
        <button type="submit">Enviar</button>
    </form>
    <form id="image-form" enctype="multipart/form-data">
    <input type="file" id="image" name="image" accept="image/*" style="display: none;" required>
    <button type="button" id="send-image">Seleccionar y Enviar Imagen</button>
    </form>

    <script>
        $(document).ready(function() {
        // Cargar mensajes al cargar la página
        loadMessages();

        // Enviar mensaje
        $('#message-form').on('submit', function(e) {
            e.preventDefault(); // Evitar el envío del formulario tradicional

            var message = $('#message').val();
            $.post('chat.php', { message: message }, function() {
                $('#message').val(''); // Limpiar el campo de mensaje
                loadMessages(); // Cargar mensajes después de enviar
            });
        });

        // Función para cargar mensajes
        function loadMessages() {
            $.get('load_messages.php', function(data) {
                $('#chat-box').html(data);
                autoScroll(); // Llamar a la función de desplazamiento automático
            });
        }

        // Actualizar mensajes cada 2 segundos
        setInterval(loadMessages, 2000);
    });

    let isUserScrolling = false;
    let userScrolledUp = false; // Flag para indicar si el usuario ha desplazado hacia arriba

    // Detectar cuando el usuario está desplazándose
    $('#chat-box').on('scroll', function() {
        isUserScrolling = true;
        
        // Verificar si el usuario ha desplazado hacia arriba
        if ($(this).scrollTop() < $(this)[0].scrollHeight - $(this).innerHeight()) {
            userScrolledUp = true; // El usuario ha desplazado hacia arriba
        } else {
            userScrolledUp = false; // El usuario está en la parte inferior
        }
    });

    // Detectar cuando el usuario deja de desplazarse
    $('#chat-box').on('scrollstop', function() {
        isUserScrolling = false;
    });

    // Desplazamiento automático solo si el usuario no está desplazándose y no ha desplazado hacia arriba
    function autoScroll() {
        if (!isUserScrolling && !userScrolledUp) {
            $('#chat-box').animate({ scrollTop: $('#chat-box')[0].scrollHeight }, 'normal');
        }
    }

    // Añadir un evento para detectar cuando el usuario deja de desplazarse
        let scrollTimeout;
        $('#chat-box').on('scroll', function() {
            clearTimeout(scrollTimeout);
            isUserScrolling = true;
            scrollTimeout = setTimeout(function() {
                isUserScrolling = false;
            }, 100); // Cambia el tiempo de espera según sea necesario
        });
        $('#send-image').on('click', function() {
        $('#image').click(); // Simula un clic en el input de archivo
    });

// Detectar cuando se selecciona un archivo
    $('#image').on('change', function() {
        var formData = new FormData($('#image-form')[0]); // Crea un objeto FormData con los datos del formulario

        $.ajax({
            url: 'upload_image.php', // Cambia esto a la URL de tu script PHP que manejará la subida
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                $('#chat-box').html(data); // Actualiza el chat con las nuevas imágenes
                $('#image').val(''); // Limpiar el campo de imagen
            },
            error: function() {
                alert('Error al enviar la imagen.');
            }
        });
    });
    </script>
</body>
</html>