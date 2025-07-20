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
    <!-- <link rel="icon" href="css/icono.png" type="image/png"> -->
     <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Modificar Datos - UniHub</title>
    <script src="js/control_inactividad.js"></script>
    <style>
        body.dark-mode {
            --background-color: rgb(50, 50, 50);
            --text-color: white;
            --background-form: rgb(147, 136, 136);
        }

        .pagina {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
            background-color: var(--background-color);
        }

        .wecontainer {
            font-family: "Poppins", sans-serif;
            max-width: 1200px;
            width: 90%;
            background: var(--background-form);
            color: #004c97;
            padding: 40px;
            box-shadow: 2px 4px 8px rgba(0, 0, 0, 0.5);
            border-radius: 8px;
            border-top: 10px solid #ffd700;
            border-bottom: 10px solid #ffd700;
            border-left: 1px solid #ffd700 !important;
            border-right: 1px solid #ffd700 !important;
            text-align: center;
            display: flex;
            flex-direction: column;
            transition: 1s background ease-in-out;
        }

        .wecontainer h1 {
            margin-bottom: 20px;
            color: #004c97;
            font-size: 2em;
        }

        .perfil-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .perfil-foto {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-right: 20px;
        }

        .perfil-boton {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            font-size: 16px;
        }

        .perfil-boton:hover {
            background-color: #0056b3;
        }

        .wecontainer .form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: center;
        }

        .wecontainer label {
            color: #004c97;
            font-size: 1.2em;
            font-weight: 500;
        }

        .wecontainer input,
        .wecontainer select {
            padding: 10px;
            width: 100%;
            background: transparent;
            border: 1px solid #004c97;
            border-radius: 4px;
            font-size: 1em;
        }

        .wecontainer .readonly-field {
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            color: #6c757d;
            font-size: 1em;
        }

        .wecontainer .button {
            grid-column: span 2;
            margin-top: 20px;
            padding: 10px 20px;
            background: #ffd700 !important;
            color: #004c97 !important;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.2em;
        }

        .wecontainer .button:hover {
            background-color: #ffcc00;
        }

        .error {
            border: 2px solid red;
        }

        .error-message {
            color: red;
            font-weight: bold;
        }

        .success-message {
            color: green;
            font-weight: bold;
            background-color: rgba(40, 167, 69, 0.1);
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #28a745;
            margin-bottom: 15px;
        }

        /* Estilos mejorados para modo oscuro */
        body.dark-mode {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ffffff;
        }

        body.dark-mode .pagina {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        body.dark-mode .wecontainer {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            color: #ffffff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
            border-color: #ffd700;
        }

        body.dark-mode .wecontainer h1 {
            color: #ffd700;
        }

        body.dark-mode .wecontainer label {
            color: #ffffff;
        }

        body.dark-mode .wecontainer input,
        body.dark-mode .wecontainer select {
            background: #4a5568;
            border-color: #718096;
            color: #ffffff;
        }

        body.dark-mode .wecontainer input:focus,
        body.dark-mode .wecontainer select:focus {
            border-color: #ffd700;
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.2);
        }

        body.dark-mode .wecontainer .readonly-field {
            background-color: #4a5568;
            border-color: #718096;
            color: #a0aec0;
        }

        body.dark-mode .wecontainer .button {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%) !important;
            color: #1a1a2e !important;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }

        body.dark-mode .wecontainer .button:hover {
            background: linear-gradient(135deg, #ffed4e 0%, #ffd700 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
        }

        body.dark-mode .perfil-boton {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: #ffffff;
            border: 1px solid #718096;
        }

        body.dark-mode .perfil-boton:hover {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            border-color: #ffd700;
        }

        body.dark-mode .error {
            border-color: #e53e3e;
            background-color: rgba(229, 62, 62, 0.1);
        }

        body.dark-mode .error-message {
            color: #fc8181;
            background-color: rgba(229, 62, 62, 0.1);
            border-color: #e53e3e;
        }

        body.dark-mode .success-message {
            color: #68d391;
            background-color: rgba(104, 211, 145, 0.1);
            border-color: #38a169;
        }

        body.dark-mode .perfil-foto {
            border-color: #ffd700;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }

        body.dark-mode a {
            color: #ffd700;
            text-decoration: none;
        }

        body.dark-mode a:hover {
            color: #ffed4e;
            text-decoration: underline;
        }

        /* Estilos para el soporte flotante en modo oscuro */
        body.dark-mode .soporte-flotante {
            background-color: #4a5568;
        }

        body.dark-mode .soporte-flotante:hover {
            background-color: #2d3748;
        }
    </style>
</head>

<body>
    <div class="contenedorentrante3">
        <img src="css\logo.png">
    </div>
    <div class="cabecera">
        <button type="button" id="logoButton">
            <!-- <img src="css/logo.png" alt="Logo"> -->
            <img src="css/menu.png" alt="Menú" class="logo-menu">
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
            <div class="perfil-container" style="text-align:center; margin-bottom: 20px;">
                    <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto de perfil" class="perfil-foto" id="perfilFoto" style="width:120px; height:120px; object-fit:cover; border-radius:50%; border:2px solid #ccc;">
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <?php if ($error_message): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
                <?php if (isset($_GET['success'])): ?>
                    <p class="success-message">Datos actualizados correctamente.</p>
                <?php endif; ?>
                <div class="form">
                    <!-- Foto de perfil -->
                    <label for="foto" style="font-weight:bold;">Cambiar foto de perfil:</label>
                    <input type="file" name="foto" id="foto" accept="image/*" style="margin-bottom:10px;">
                    <!-- Campos de solo lectura -->
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
                    <!-- Campos editables -->
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

    <script>
        // Script vacío - funcionalidad de foto removida
    </script>
</body>
</html>