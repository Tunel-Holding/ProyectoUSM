<?php

require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_PROFESOR);
include 'comprobar_sesion.php';
actualizar_actividad();
include 'conexion.php';


// Obtener el ID del usuario de la sesión
$id_usuario = $_SESSION['idusuario'];

// Obtener los datos del profesor usando prepared statement
$sql = "SELECT cedula, nombres, apellidos, sexo, telefono, correo, direccion
        FROM datos_usuario
        WHERE usuario_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error en la consulta: " . $conn->error);
}

$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $estudiante = $result->fetch_assoc();
} else {
    // Si no hay datos, redirigir a la página de llenado de datos (o manejar como error si no aplica)
    // Para este contexto, asumo que siempre debería haber datos o se lanza un error fatal.
    die("No se encontraron datos del usuario.");
}

// Obtener la foto del usuario usando prepared statement
$sql_foto = "SELECT foto FROM fotousuario WHERE id_usuario = ?";
$stmt_foto = $conn->prepare($sql_foto);
$foto = "css/perfil.png"; // Foto por defecto
if ($stmt_foto) {
    $stmt_foto->bind_param("i", $id_usuario);
    $stmt_foto->execute();
    $result_foto = $stmt_foto->get_result();
    if ($result_foto->num_rows > 0) {
        $row_foto = $result_foto->fetch_assoc();
        $foto = $row_foto['foto'];
    }
    $stmt_foto->close();
}

$error_message = "";
$success_message = "";

// Lógica de procesamiento del formulario POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y sanitizar los datos del formulario
    // Usar el operador de coalescencia nula para mantener el valor existente si no se envía el campo
    $sexo = trim($_POST['sexo'] ?? $estudiante['sexo']);
    $telefono = trim($_POST['telefono'] ?? $estudiante['telefono']);
    $direccion = trim($_POST['direccion'] ?? $estudiante['direccion']);

   // Si se envió una imagen recortada desde Croppie, procesarla
    if (!empty($_POST['croppie_result'])) {
        $croppie_data = $_POST['croppie_result'];
        if (preg_match('/^data:image\/(\w+);base64,/', $croppie_data, $type)) {
            $croppie_data = substr($croppie_data, strpos($croppie_data, ',') + 1);
            $croppie_data = base64_decode($croppie_data);
            $ext = strtolower($type[1]);
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error_message = "Formato de imagen recortada no válido.";
            } else {
                $target_dir = "fotoperfil/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $nombre_archivo = time() . '_croppie.' . $ext;
                $target_file = $target_dir . $nombre_archivo;
                if (file_put_contents($target_file, $croppie_data)) {
                    // Actualizar o insertar la foto en la base de datos
                    $sql_foto_check = "SELECT foto FROM fotousuario WHERE id_usuario = ?";
                    $stmt_foto_check = $conn->prepare($sql_foto_check);
                    $stmt_foto_check->bind_param("i", $id_usuario);
                    $stmt_foto_check->execute();
                    $result_foto_check = $stmt_foto_check->get_result();
                    if ($result_foto_check->num_rows > 0) {
                        $sql_update = "UPDATE fotousuario SET foto = ? WHERE id_usuario = ?";
                        $stmt_update = $conn->prepare($sql_update);
                        $stmt_update->bind_param("si", $target_file, $id_usuario);
                        $stmt_update->execute();
                        $stmt_update->close();
                    } else {
                        $sql_insert = "INSERT INTO fotousuario (id_usuario, foto) VALUES (?, ?)";
                        $stmt_insert = $conn->prepare($sql_insert);
                        $stmt_insert->bind_param("is", $id_usuario, $target_file);
                        $stmt_insert->execute();
                        $stmt_insert->close();
                    }
                    $foto = $target_file;
                    $success_message = "Foto actualizada correctamente.";
                    $stmt_foto_check->close();
                } else {
                    $error_message = "Error al guardar la imagen recortada.";
                }
            }
        } else {
            $error_message = "Imagen recortada no válida.";
        }
    }

    // Validar que todos los datos estén presentes (excepto nombres, apellidos, cédula y correo que no se modifican)
    if (empty($sexo) || empty($telefono) || empty($direccion)) {
        $error_message = "Todos los campos de datos personales son obligatorios.";
    } elseif (!$error_message) { // Solo si no hay errores previos (ej. de foto)
        // Validar formato del teléfono (solo números y longitud 10)
        if (!preg_match('/^[0-9]{11}$/', $telefono)) {
            $error_message = "El formato del teléfono no es válido. Debe contener 10 dígitos numéricos.";
        } else if (strlen($telefono) > 11) {
            $error_message = "El teléfono no puede tener más de 11 dígitos.";
        } else if (strlen($direccion) > 100) {
            $error_message = "La dirección no puede tener más de 100 caracteres.";
        } else {
            // Actualizar los datos en la base de datos usando prepared statement
            $sql_update = "UPDATE datos_usuario SET sexo = ?, telefono = ?, direccion = ? WHERE usuario_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update) {
                $stmt_update->bind_param("sssi", $sexo, $telefono, $direccion, $id_usuario);
                if ($stmt_update->execute()) {
                    // Cerrar los statements y la conexión antes de la redirección
                    $stmt_update->close();
                    $stmt->close(); // Cerrar el statement original de obtención de datos
                    $conn->close();

                    // Redirigir a datos_profesor.php con un mensaje de éxito
                    header("Location: datos_profesor.php?success=1");
                    exit(); // Siempre salir después de una redirección de cabecera
                } else {
                    $error_message = "Error al actualizar los datos: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                $error_message = "Error en la preparación de la consulta de datos personales.";
            }
        }
    }
}

// Si hubo un error en la solicitud POST, volver a obtener los datos para rellenar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($error_message)) {
    // Volver a obtener los datos para rellenar el formulario correctamente si hubo un error
    $sql = "SELECT cedula, nombres, apellidos, sexo, telefono, correo, direccion
            FROM datos_usuario
            WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error en la consulta: " . $conn->error);
    }
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $estudiante = $result->fetch_assoc();
    }
    // También volver a obtener la foto si hubo un error relacionado con ella y necesita ser actualizada en la vista
    $sql_foto = "SELECT foto FROM fotousuario WHERE id_usuario = ?";
    $stmt_foto = $conn->prepare($sql_foto);
    if ($stmt_foto) {
        $stmt_foto->bind_param("i", $id_usuario);
        $stmt_foto->execute();
        $result_foto = $stmt_foto->get_result();
        if ($result_foto->num_rows > 0) {
            $row_foto = $result_foto->fetch_assoc();
            $foto = $row_foto['foto'];
        }
        $stmt_foto->close();
    }
}


actualizar_actividad();
// Cerrar los statements y la conexión solo si no se han cerrado ya debido a la redirección
if (isset($stmt) && $stmt !== false && $stmt->num_rows > 0) {
    $stmt->close();
}
if (isset($conn) && $conn->ping()) { // Verificar si la conexión aún está abierta
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
        
    <!-- Croppie CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" />
    <!-- jQuery y Croppie JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>
    <title>Modificar datos - UniHub</title>
    <script src="js/control_inactividad.js"></script>
    <style>
        /* Variables CSS para el modo oscuro y colores base */
        :root {
            --background-color-light: #f4f7f6  !important; /* Fondo general claro, más suave */
            --text-color-light: #333; /* Color de texto general claro */
            --form-background-light: #ffffff; /* Fondo del formulario claro */
            --primary-color: #004c97; /* Azul USM para títulos, labels y énfasis */
            --secondary-color: #26c8dd; /* Amarillo USM para bordes de formulario, botones */
            --border-color-light: #dee2e6; /* Borde de inputs y campos de solo lectura claro */
            --input-bg-light: #f8f9fa; /* Fondo de input de solo lectura claro */
            --input-text-readonly-light: #6c757d; /* Texto de campos de solo lectura claro */
            --shadow-color-light: rgba(0, 0, 0, 0.15); /* Sombra suave para elementos */
        }

        /* Definiciones para el modo oscuro */
        body.dark-mode {
            --background-color: rgb(50, 50, 50) !important; /* Fondo general oscuro */
            --text-color: white; /* Color de texto general oscuro */
            --form-background: rgb(80, 80, 80); /* Fondo del formulario oscuro, un poco más claro que el fondo general */
            --primary-color: #ffd700; /* Amarillo USM como primario en oscuro (para títulos, etc.) */
            --secondary-color: #004c97; /* Azul USM como secundario en oscuro (para bordes, acentos) */
            --border-color: #666; /* Borde de inputs y campos de solo lectura oscuro */
            --input-bg: #444; /* Fondo de input de solo lectura oscuro */
            --input-text-readonly: #cccccc; /* Texto de campos de solo lectura oscuro */
            --shadow-color-dark: rgba(0, 0, 0, 0.4); /* Sombra más pronunciada en oscuro */
        }
        .pagina {
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
             /* Fondo general */
            background-color: var(--background-color-light);
            transition: background-color 0.5s ease-in-out;
        }
        body.dark-mode .pagina {
            background-color: var(--background-color);
        }

        /* Contenedor principal del formulario */
        .wecontainer {
            font-family: "Poppins", sans-serif;
            max-width: 1200px;
            width: 90%;
            background: var(--form-background-light);
            color: var(--primary-color);
            padding: 40px;
            box-shadow: 2px 4px 12px var(--shadow-color-light);
            border-radius: 10px;
            border-top: 10px solid var(--secondary-color);
            border-bottom: 10px solid var(--secondary-color);
            border-left: 1px solid var(--secondary-color);
            border-right: 1px solid var(--secondary-color);
            text-align: center;
            display: flex;
            flex-direction: column;
            transition: background 0.5s ease-in-out, box-shadow 0.5s ease, border-color 0.5s ease;
        }

        body.dark-mode .wecontainer {
            background: var(--form-background);
            color: var(--primary-color);
            box-shadow: 2px 4px 12px var(--shadow-color-dark);
            border-color: var(--primary-color);
        }

        .wecontainer h1 {
            margin-bottom: 25px;
            color: var(--primary-color);
            font-size: 2.2em;
            font-weight: 700;
        }
        body.dark-mode .wecontainer h1 {
            color: var(--primary-color);
        }

        /* Contenedor de la foto de perfil */
        .perfil-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
        }

        /* Nuevo Wrapper para la foto y el ícono */
        .perfil-foto-wrapper {
            position: relative; /* Clave para posicionar el ícono dentro */
            width: 150px; /* Igual que el ancho de la foto */
            height: 150px; /* Igual que el alto de la foto */
            border-radius: 50%; /* Para que el ícono se adapte a la forma circular */
            overflow: hidden; /* Asegura que cualquier cosa que sobresalga de la foto esté oculta */
            cursor: pointer; /* Indica que es clicable */
        }

        /* Mantiene el estilo de la foto de perfil */
        .perfil-foto {
            width: 100%; /* Ocupa todo el ancho del wrapper */
            height: 100%; /* Ocupa todo el alto del wrapper */
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--secondary-color);
            box-shadow: 0 0 15px var(--shadow-color-light);
            transition: border-color 0.5s ease-in-out, box-shadow 0.5s ease;
            display: block; /* Elimina cualquier espacio extra debajo de la imagen */
        }
        body.dark-mode .perfil-foto {
            border: 4px solid var(--primary-color);
            box-shadow: 0 0 15px var(--shadow-color-dark);
        }

        /* Estilos del ícono de edición (la capa de superposición con el lápiz) */
        .edit-icon {
            position: absolute;
            top: 0; /* Cubre desde arriba */
            left: 0; /* Cubre desde la izquierda */
            width: 100%; /* Ocupa todo el ancho del wrapper */
            height: 100%; /* Ocupa toda la altura del wrapper */
            background-color: rgba(0, 0, 0, 0.6); /* Fondo semi-transparente oscuro */
            color: white; /* Color del lápiz */
            display: flex;
            justify-content: center; /* Centra el lápiz horizontalmente */
            align-items: center; /* Centra el lápiz verticalmente */
            opacity: 0; /* Oculto por defecto */
            transition: opacity 0.3s ease, background-color 0.3s ease; /* Transición suave */
            pointer-events: none; /* Permite clics a través de él cuando es opacidad 0 */
            cursor: pointer; /* Vuelve a indicar clicable */
            border-radius: 50%;
        }

        /* Efecto al pasar el cursor sobre el contenedor de la foto */
        .perfil-foto-wrapper:hover .edit-icon {
            opacity: 1; /* Se vuelve visible */
            pointer-events: auto; /* Permite que el ícono sea clicable */
        }

        /* Estilos para el SVG del lápiz */
        .edit-icon svg {
            width: 30px; /* Tamaño del SVG */
            height: 30px;
        }

        /* Modo oscuro para el ícono de edición */
        body.dark-mode .edit-icon {
            background-color: rgba(0, 0, 0, 0.7); /* Un poco más oscuro en modo oscuro */
            color: var(--primary-color); /* Lápiz amarillo en modo oscuro */
        }

        /* Grid del formulario */
        .wecontainer .form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 30px;
            align-items: center;
            padding: 0 15px;
        }

        /* Etiquetas de los campos */
        .wecontainer label {
            color: var(--primary-color);
            font-size: 1.15em;
            font-weight: 600;
            text-align: right;
            padding-right: 15px;
            box-sizing: border-box;
        }
        body.dark-mode .wecontainer label {
            color: var(--text-color);
        }

        /* Inputs y Selects editables */
        .wecontainer input[type="text"],
        .wecontainer input[type="number"],
        .wecontainer select {
            padding: 12px 15px;
            width: 100%;
            background: var(--form-background-light);
            border: 1px solid var(--border-color-light);
            border-radius: 8px;
            font-size: 1em;
            color: var(--text-color-light);
            box-sizing: border-box;
            transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.5s ease, color 0.5s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        body.dark-mode .wecontainer input[type="text"],
        body.dark-mode .wecontainer input[type="number"],
        body.dark-mode .wecontainer select {
            background: var(--input-bg);
            border-color: var(--border-color);
            color: var(--text-color);
        }

        /* Estado de enfoque (focus) para inputs y selects */
        .wecontainer input[type="text"]:focus,
        .wecontainer input[type="number"]:focus,
        .wecontainer select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 76, 151, 0.25);
        }
        body.dark-mode .wecontainer input[type="text"]:focus,
        body.dark-mode .wecontainer input[type="number"]:focus,
        body.dark-mode .wecontainer select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.25);
        }

        /* Campos de solo lectura */
        .wecontainer p { /* Cambiado de .readonly-field a p para tu HTML actual */
            padding: 12px 15px;
            background-color: var(--input-bg-light);
            border: 1px solid var(--border-color-light);
            border-radius: 8px;
            color: var(--input-text-readonly-light);
            font-size: 1em;
            box-sizing: border-box;
            width: 100%;
            text-align: left;
            min-height: 44px;
            display: flex;
            align-items: center;
            transition: background-color 0.5s ease, border-color 0.5s ease, color 0.5s ease;
            margin: 0; /* Quitar el margen por defecto de los párrafos */
        }
        body.dark-mode .wecontainer p {
            background-color: var(--input-bg);
            border-color: var(--border-color);
            color: var(--input-text-readonly);
        }

        /* Botón de guardar cambios */
        .wecontainer .button {
            grid-column: span 2;
            margin-top: 25px;
            padding: 14px 30px;
            background: var(--secondary-color); /* Color base del botón (amarillo USM en modo claro, azul USM en oscuro) */
            color: var(--primary-color); /* Color base del texto (azul USM en modo claro, amarillo USM en oscuro) */
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: 700;
            box-shadow: 0 4px 10px var(--shadow-color-light);
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease, color 0.3s ease; /* Añadido 'color' a la transición */
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .wecontainer .button:hover {
            /* Modo Claro: Color al pasar el cursor */
            background-color: #004c97; /* Ejemplo: Un azul más oscuro */
            color: white; /* Ejemplo: Texto blanco */
            transform: translateY(-2px);
            box-shadow: 0 6px 15px var(--shadow-color-light);
        }

        body.dark-mode .wecontainer .button:hover {
            /* Modo Oscuro: Color al pasar el cursor */
            background-color: #ffd700; /* Ejemplo: Amarillo USM */
            color: #004c97; /* Ejemplo: Texto azul USM */
            transform: translateY(-2px);
            box-shadow: 0 6px 15px var(--shadow-color-dark);
        }

        /* Campo con error */
        .error {
            border: 2px solid #dc3545 !important;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25);
        }

        /* Mensajes de error y éxito */
        .error-message,
        .success-message {
            grid-column: span 2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
            box-shadow: 0 2px 5px var(--shadow-color-light);
            transition: opacity 1s ease-out;
            opacity: 1; /* Asegurar que sea visible inicialmente */
        }
        body.dark-mode .error-message,
        body.dark-mode .success-message {
            box-shadow: 0 2px 5px var(--shadow-color-dark);
        }

        .error-message {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.15);
            border: 1px solid #dc3545;
        }

        .success-message {
            color: #28a745;
            background-color: rgba(40, 167, 69, 0.15);
            border: 1px solid #28a745;
        }

        /* Enlace de cambiar contraseña */
        .wecontainer a[href="forgotPassword.php"] {
            grid-column: span 2;
            display: block;
            margin-top: 15px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease, text-decoration 0.3s ease;
        }

        .wecontainer a[href="forgotPassword.php"]:hover {
            color: #007bff;
            text-decoration: underline;
        }
        body.dark-mode .wecontainer a[href="forgotPassword.php"] {
            color: var(--text-color);
        }
        body.dark-mode .wecontainer a[href="forgotPassword.php"]:hover {
            color: #66b3ff;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .wecontainer {
                padding: 25px;
                width: 95%;
            }

            .wecontainer h1 {
                font-size: 2em;
            }

            .perfil-foto-wrapper,
            .perfil-foto {
                width: 120px;
                height: 120px;
            }

            .wecontainer .form {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 0;
            }
            .wecontainer label {
                text-align: left;
                padding-right: 0;
                margin-bottom: 5px;
            }

            .wecontainer input[type="text"],
            .wecontainer input[type="number"],
            .wecontainer select,
            .wecontainer p, /* Afecta a los <p> que actúan como readonly-field */
            .wecontainer .button,
            .error-message,
            .success-message,
            .wecontainer a[href="forgotPassword.php"] {
                grid-column: span 1;
                width: 100%;
                text-align: left;
            }
            .wecontainer .button {
                margin-left: 0;
                margin-right: 0;
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .wecontainer {
                padding: 15px;
                border-radius: 8px;
                border-top-width: 8px;
                border-bottom-width: 8px;
            }

            .wecontainer h1 {
                font-size: 1.6em;
                margin-bottom: 15px;
            }

            .perfil-foto-wrapper,
            .perfil-foto {
                width: 90px;
                height: 90px;
                border-width: 3px;
            }

            .wecontainer input[type="text"],
            .wecontainer input[type="number"],
            .wecontainer select,
            .wecontainer p {
                padding: 10px 12px;
                font-size: 0.9em;
                border-radius: 6px;
            }

            .wecontainer .button {
                padding: 12px 25px;
                font-size: 1em;
                margin-top: 20px;
                border-radius: 6px;
            }

            .error-message,
            .success-message {
                font-size: 0.9em;
                padding: 10px;
                border-radius: 6px;
            }
        }
    </style>
</head>

<body>
    <div class="cabecera">
        <button type="button" id="logoButton">
            <img src="css/menu.png" alt="Menú" class="logo-menu">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>

    <?php include 'menu_profesor.php'; ?>

    <div class="pagina">
        <div class="wecontainer">
            <a href="datos_profesor.php">
            <button class="button">Regresar</button>
            </a>
            <h1>Modificar Datos del Profesor</h1>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="perfil-container">
                    
                    <div class="perfil-foto-wrapper" id="fotoWrapper">
                        <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto de perfil" class="perfil-foto" id="perfilFoto">
                        <!-- Oculto, pero sirve para disparar el cambio -->
                        <input type="file" name="foto" id="input-imagen" accept="image/*" style="display: none;">
                        <label for="input-imagen" class="edit-icon" title="Cambiar foto de perfil">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-camera-fill" viewBox="0 0 16 16">
                                <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0"/>
                            </svg>
                        </label>
                    </div>
                <br>
                <br>
                <!-- Contenedor para Croppie (inicialmente oculto) -->
                <div id="croppie-container" style="display: none;"></div>
                <button type="button" id="btn-cortar" style="display:none; margin: 10px auto;">Recortar Imagen</button>
                <input type="hidden" name="croppie_result" id="croppie-result">
                </div>

                
                <div class="form">
                    <label for="cedula">Número de Cédula:</label>
                    <p id="cedula"><?php echo htmlspecialchars($estudiante['cedula'] ?? ''); ?></p>

                    <label for="nombres">Nombres:</label>
                    <p id="nombres"><?php echo htmlspecialchars($estudiante['nombres'] ?? ''); ?></p>

                    <label for="apellidos">Apellidos:</label>
                    <p id="apellidos"><?php echo htmlspecialchars($estudiante['apellidos'] ?? ''); ?></p>

                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo" class="<?php echo empty($sexo) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sexo']) ? 'error' : ''; ?>">
                        <option value="">Seleccione</option>
                        <option value="Masculino" <?php if (isset($estudiante['sexo']) && $estudiante['sexo'] == 'Masculino') echo 'selected'; ?>>Masculino</option>
                        <option value="Femenino" <?php if (isset($estudiante['sexo']) && $estudiante['sexo'] == 'Femenino') echo 'selected'; ?>>Femenino</option>
                    </select>

                    <label for="telefono">Teléfono:</label>
                    <input type="number" id="telefono" name="telefono"
                        value="<?php echo htmlspecialchars($estudiante['telefono'] ?? ''); ?>"
                        class="<?php echo empty($telefono) && $_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message) ? 'error' : ''; ?>">
                         <label for="correo">Correo:</label>
                    <p id="correo"><?php echo htmlspecialchars($estudiante['correo'] ?? ''); ?></p>

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion"
                        value="<?php echo htmlspecialchars($estudiante['direccion'] ?? ''); ?>"
                        class="<?php echo empty($direccion) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">

                    <a href="forgotPassword.php" class="cambiar-contrasena-link">Cambiar contraseña</a>
                    <input type="submit" class="button" value="Guardar cambios">
                </div>
            </form>
        </div>
    </div>

    <script>
         document.getElementById('input-imagen').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (!file) return;
        if (file.size > 500000) { // Validar tamaño máximo de 5MB
            alert('El archivo es demasiado grande. El tamaño máximo permitido es 5MB.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            // Mostrar el cropper cada vez que se selecciona una imagen
            document.getElementById('croppie-container').style.display = 'block';
            document.getElementById('btn-cortar').style.display = 'inline-block';
            // Si ya existe una instancia, destruirla
            if (window.croppieInstance) {
                window.croppieInstance.destroy();
            }
            window.croppieInstance = new Croppie(document.getElementById('croppie-container'), {
                viewport: { width: 200, height: 200, type: 'square' },
                boundary: { width: 300, height: 300 }
            });
            window.croppieInstance.bind({
                url: e.target.result
            });
        }
        reader.readAsDataURL(file);
    });

    // Al hacer clic en el botón de recortar, se obtiene la imagen recortada
    document.getElementById('btn-cortar').addEventListener('click', function () {
        window.croppieInstance.result({
            type: 'base64',
            size: 'viewport'
        }).then(function (base64) {
            document.getElementById('croppie-result').value = base64;
            alert('Imagen recortada lista para enviar. Ahora puedes guardar los cambios.');
            document.getElementById('croppie-container').style.display = 'none';
            document.getElementById('btn-cortar').style.display = 'none';
            // Actualizar la foto de vista previa en el wrapper
            document.getElementById('perfilFoto').src = base64;
        });
    });
    </script>
</body>

</html>