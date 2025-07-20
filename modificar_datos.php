<?php
include 'comprobar_sesion.php';
actualizar_actividad();
include 'conexion.php';

// Obtener el ID del usuario de la sesión
$id_usuario = $_SESSION['idusuario'];

// Obtener los datos del estudiante usando prepared statement
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
    die("No se encontraron datos del usuario");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y sanitizar solo los campos editables
    $sexo = trim($_POST['sexo'] ?? $estudiante['sexo']);
    $telefono = trim($_POST['telefono'] ?? $estudiante['telefono']);
    $direccion = trim($_POST['direccion'] ?? $estudiante['direccion']);

    // Procesar la foto si se subió una nueva
    $foto_subida = false;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "fotoperfil/";
        $nombre_archivo = time() . '_' . basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . $nombre_archivo;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["foto"]["tmp_name"]);
        if ($check !== false) {
            if ($check[0] !== $check[1]) {
                $error_message = "La foto debe ser cuadrada (igual de altura y anchura).";
            } elseif ($_FILES["foto"]["size"] > 500000) {
                $error_message = "La foto es demasiado grande.";
            } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                $error_message = "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
            } elseif (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                // Borrar la foto anterior si existe y no es la de por defecto
                if ($foto != "css/perfil.png" && file_exists($foto)) {
                    unlink($foto);
                }
                // Actualizar o insertar la foto en la base de datos
                $sql_foto_check = "SELECT foto FROM fotousuario WHERE id_usuario = ?";
                $stmt_foto_check = $conn->prepare($sql_foto_check);
                $stmt_foto_check->bind_param("i", $id_usuario);
                $stmt_foto_check->execute();
                $result_foto_check = $stmt_foto_check->get_result();
                if ($result_foto_check->num_rows > 0) {
                    $sql_update_foto = "UPDATE fotousuario SET foto = ? WHERE id_usuario = ?";
                    $stmt_update_foto = $conn->prepare($sql_update_foto);
                    $stmt_update_foto->bind_param("si", $target_file, $id_usuario);
                    $stmt_update_foto->execute();
                    $stmt_update_foto->close();
                } else {
                    $sql_insert_foto = "INSERT INTO fotousuario (id_usuario, foto) VALUES (?, ?)";
                    $stmt_insert_foto = $conn->prepare($sql_insert_foto);
                    $stmt_insert_foto->bind_param("is", $id_usuario, $target_file);
                    $stmt_insert_foto->execute();
                    $stmt_insert_foto->close();
                }
                $foto = $target_file;
                $foto_subida = true;
                $stmt_foto_check->close();
            } else {
                $error_message = "Error al subir la foto.";
            }
        } else {
            $error_message = "El archivo no es una imagen válida.";
        }
    }

    // Verificar que todos los campos editables estén presentes
    if (empty($sexo) || empty($telefono) || empty($direccion)) {
        $error_message = "Todos los campos son obligatorios.";
    } elseif (!$error_message) {
        // Validar formato del teléfono (solo números y algunos caracteres especiales)
        if (!preg_match('/^[\\d\\s\\-\\+\\(\\)]+$/', $telefono)) {
            $error_message = "El formato del teléfono no es válido.";
        } else {
            // Actualizar solo los campos editables en la base de datos usando prepared statement
            $sql_update = "UPDATE datos_usuario SET sexo = ?, telefono = ?, direccion = ? WHERE usuario_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update) {
                $stmt_update->bind_param("sssi", $sexo, $telefono, $direccion, $id_usuario);
                if ($stmt_update->execute()) {
                    $success_message = "Datos actualizados correctamente.";
                    // Refrescar los datos para mostrar los nuevos valores
                    header("Location: datos.php?success=1");
                    exit();
                } else {
                    $error_message = "Error al actualizar los datos: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                $error_message = "Error en la preparación de la consulta.";
            }
        }
    }
}
actualizar_actividad();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Modificar Datos - USM</title>
    <script src="js/control_inactividad.js"></script>
    <style>
        /* Variables CSS para el modo oscuro y colores base */
        :root {
            --background-color-light: #f4f7f6; /* Fondo general claro, más suave */
            --text-color-light: #333; /* Color de texto general claro */
            --form-background-light: #ffffff; /* Fondo del formulario claro */
            --primary-color: #004c97; /* Azul USM para títulos, labels y énfasis */
            --secondary-color: #ffd700; /* Amarillo USM para bordes de formulario, botones */
            --border-color-light: #dee2e6; /* Borde de inputs y campos de solo lectura claro */
            --input-bg-light: #f8f9fa; /* Fondo de input de solo lectura claro */
            --input-text-readonly-light: #6c757d; /* Texto de campos de solo lectura claro */
            --shadow-color-light: rgba(0, 0, 0, 0.15); /* Sombra suave para elementos */
        }

        /* Definiciones para el modo oscuro */
        body.dark-mode {
            --background-color: rgb(50, 50, 50); /* Fondo general oscuro */
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

        /* Estilos del ícono de edición (el lápiz) */
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
            /* Añadimos un border-radius para que se adapte a la forma de la foto */
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
        /* Puedes eliminar .perfil-boton si ya no lo usas para cambiar foto */
        .perfil-boton {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            font-size: 16px;
            font-weight: 500;
        }

        .perfil-boton:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px var(--shadow-color-light);
        }
        body.dark-mode .perfil-boton {
            background-color: var(--primary-color);
            color: var(--secondary-color);
        }
        body.dark-mode .perfil-boton:hover {
            background-color: #ffcc00;
            box-shadow: 0 2px 5px var(--shadow-color-dark);
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
        .wecontainer input[type="file"],
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
        body.dark-mode .wecontainer input[type="file"],
        body.dark-mode .wecontainer select {
            background: var(--input-bg);
            border-color: var(--border-color);
            color: var(--text-color);
        }

        /* Estado de enfoque (focus) para inputs y selects */
        .wecontainer input[type="text"]:focus,
        .wecontainer select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 76, 151, 0.25);
        }
        body.dark-mode .wecontainer input[type="text"]:focus,
        body.dark-mode .wecontainer select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.25);
        }

        /* Campos de solo lectura */
        .wecontainer .readonly-field {
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
        }
        body.dark-mode .readonly-field {
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
            .perfil-foto { /* Asegura que también el wrapper se adapte */
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
            .wecontainer input[type="file"],
            .wecontainer select,
            .wecontainer .readonly-field,
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
            .perfil-foto { /* Asegura que también el wrapper se adapte */
                width: 90px;
                height: 90px;
                border-width: 3px;
            }

            .wecontainer input[type="text"],
            .wecontainer input[type="file"],
            .wecontainer select,
            .readonly-field {
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

                .soporte-flotante-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .soporte-flotante {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            background-color: #446ad3;
            padding: 12px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            overflow: hidden;
            width: 60px;            /* ✅ suficiente para mostrar solo el ícono */
            height: 50px;
            transition: width 0.4s ease, background-color 0.3s ease;
        }


        .soporte-flotante:hover {
            width: 210px; /* ✅ se expande hacia la izquierda */
            background-color: #365ac0;
        }

        .soporte-mensaje {
            flex: 1; /* ✅ ocupa todo el espacio disponible */
            opacity: 0;
            white-space: nowrap;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            transform: translateX(30px); /* animación desde la derecha */
            transition: transform 0.4s ease, opacity 0.4s ease;
            text-align: left; /* ✅ texto alineado a la izquierda */
            margin-right: auto;
            font-family: 'Poppins', sans-serif;
        }

        .soporte-flotante:hover .soporte-mensaje {
            opacity: 1;
            transform: translateX(0);
        }

        .soporte-flotante img {
            width: 30px;
            height: 30px;
            filter: brightness(0) invert(1);
            flex-shrink: 0;
            z-index: 2;
        }
    </style>
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

    <div class="pagina">
        <div class="wecontainer">
            <h1>Modificar Datos</h1>
            <div class="perfil-container">
                <div class="perfil-foto-wrapper">
                    <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto de perfil" class="perfil-foto" id="perfilFoto">
                    <input type="file" id="foto" name="foto" accept="image/*" style="display: none;">
                    <label for="foto" class="edit-icon" title="Cambiar foto de perfil">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-camera-fill" viewBox="0 0 16 16">
                            <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                            <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0"/>
                        </svg>
                    </label>
                </div>
            </div>

            <form method="POST" action="" enctype="multipart/form-data">
                <?php if ($error_message): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
                <?php if (isset($_GET['success'])): ?>
                    <p class="success-message">Datos actualizados correctamente.</p>
                <?php endif; ?>
                <div class="form">
                    <label for="cedula">Número de Cédula:</label>
                    <div class="readonly-field">
                        <?php echo htmlspecialchars($estudiante['cedula'] ?? ''); ?>
                    </div>
                    <label for="nombres">Nombres:</label>
                    <div class="readonly-field">
                        <?php echo htmlspecialchars($estudiante['nombres'] ?? ''); ?>
                    </div>
                    <label for="apellidos">Apellidos:</label>
                    <div class="readonly-field">
                        <?php echo htmlspecialchars($estudiante['apellidos'] ?? ''); ?>
                    </div>
                    <label for="correo">Correo:</label>
                    <div class="readonly-field">
                        <?php echo htmlspecialchars($estudiante['correo'] ?? ''); ?>
                    </div>
                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo"
                        class="<?php echo empty($_POST['sexo']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">
                        <option value="" <?php if (($estudiante['sexo'] ?? '') == '') echo 'selected'; ?>>Seleccione</option>
                        <option value="Masculino" <?php if (($estudiante['sexo'] ?? '') == 'Masculino') echo 'selected'; ?>>Masculino</option>
                        <option value="Femenino" <?php if (($estudiante['sexo'] ?? '') == 'Femenino') echo 'selected'; ?>>Femenino</option>
                    </select>
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono"
                        value="<?php echo htmlspecialchars($estudiante['telefono'] ?? ''); ?>"
                        class="<?php echo empty($_POST['telefono']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion"
                        value="<?php echo htmlspecialchars($estudiante['direccion'] ?? ''); ?>"
                        class="<?php echo empty($_POST['direccion']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">
                    <a href="forgotPassword.php">Cambiar contraseña</a>
                    <input type="submit" class="button" value="Guardar cambios">
                </div>
            </form>
        </div>
    </div>

    <div class="soporte-flotante-container">
        <a href="contacto.php" class="soporte-flotante" title="Soporte">
            <span class="soporte-mensaje">Contacto soporte</span>
            <img src="css/audifonos-blanco.png" alt="Soporte">
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script para la vista previa de la foto
            const fotoInput = document.getElementById('foto');
            const perfilFoto = document.getElementById('perfilFoto');

            fotoInput.addEventListener('change', function(event) {
                if (event.target.files && event.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        perfilFoto.src = e.target.result;
                    };
                    reader.readAsDataURL(event.target.files[0]);
                }
            });

            // Manejar mensajes de éxito/error de PHP (si vienen por GET)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success') && urlParams.get('success') === '1') {
                // Mostrar mensaje de éxito si es necesario, aunque el PHP ya hace un redirect con mensaje.
                // Podrías poner un setTimeout para que desaparezca
                const successMessageDiv = document.querySelector('.success-message');
                if (successMessageDiv) {
                    successMessageDiv.style.opacity = '1';
                    setTimeout(() => {
                        successMessageDiv.style.opacity = '0';
                        setTimeout(() => successMessageDiv.remove(), 1000); // Eliminar después de la transición
                    }, 5000); // Ocultar después de 5 segundos
                }
            }
            // Similar para el error_message si lo gestionaras por GET
            <?php if ($error_message): ?>
                const errorMessageDiv = document.querySelector('.error-message');
                if (errorMessageDiv) {
                    errorMessageDiv.style.opacity = '1';
                    setTimeout(() => {
                        errorMessageDiv.style.opacity = '0';
                        setTimeout(() => errorMessageDiv.remove(), 1000);
                    }, 5000);
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>