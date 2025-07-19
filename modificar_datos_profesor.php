<?php

require_once 'AuthGuard.php';
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
    // Obtener y sanitizar los datos del formulario
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

    // Validar que todos los datos estén presentes
    if (empty($sexo) || empty($telefono) || empty($direccion)) {
        $error_message = "Todos los campos son obligatorios.";
    } elseif (!$error_message) {
        // Validar formato del teléfono (solo números y longitud 10)
        if (!is_numeric($telefono) || strlen($telefono) != 10) {
            $error_message = "El formato del teléfono no es válido.";
        } else {
            // Actualizar los datos en la base de datos usando prepared statement
            $sql_update = "UPDATE datos_usuario SET sexo = ?, telefono = ?, direccion = ? WHERE usuario_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update) {
                $stmt_update->bind_param("sssi", $sexo, $telefono, $direccion, $id_usuario);
                if ($stmt_update->execute()) {
                    $success_message = "Datos actualizados correctamente.";
                    // Refrescar los datos para mostrar los nuevos valores
                    header("Location: datos_profesor.php?success=1");
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
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Modificar Profesor - USM</title>
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
            /* Blanco */
            background-color: var(--background-color);
        }

        .wecontainer {
            font-family: "Poppins", sans-serif;
            max-width: 1200px;
            /* Ajustamos el ancho máximo del contenedor */
            width: 90%;
            /* Abarcamos un 90% del ancho de la página */
            background: var(--background-form);
            color: #26c8dd;
            /* Azul oscuro */
            padding: 40px;
            /* Aumentamos el padding */
            box-shadow: 2px 4px 8px rgba(0, 0, 0, 0.5);
            border-radius: 8px;
            border-top: 10px solid #ffd700;
            /* Amarillo */
            border-bottom: 10px solid #ffd700;
            /* Amarillo */
            border-left: 1px solid #ffd700 !important;
            border-right: 1px solid #ffd700 !important;
            text-align: center;
            display: flex;
            flex-direction: column;
            transition: 1s background ease-in-out;
        }

        .wecontainer h1 {
            margin-bottom: 20px;
            color: #26c8dd;
            /* Azul oscuro */
            font-size: 2em;
            /* Aumentamos el tamaño de la fuente */
        }

        .wecontainer .form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* Dividimos en dos columnas */
            gap: 10px;
            align-items: center;
        }

        .wecontainer label {
            color: #26c8dd;
            /* Azul oscuro */
            font-size: 1.2em;
            /* Aumentamos el tamaño de la fuente */
            font-weight: 500;
        }

        .wecontainer input,
        select {
            left: 40%;
            padding: 10px;
            /* Aumentamos el padding */
            width: 100%;
            background: transparent;
            border: 1px solid #26c8dd;
            /* Azul oscuro */
            border-radius: 4px;
            font-size: 1em;
            /* Aumentamos el tamaño de la fuente */
        }

        .wecontainer .button {
            grid-column: span 2;
            /* El botón ocupa ambas columnas */
            margin-top: 20px;
            padding: 10px 20px;
            background: #ffd700 !important;
            /* Amarillo */
            color: #26c8dd !important;
            /* Azul oscuro */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.2em;
            /* Aumentamos el tamaño de la fuente */
        }

        .wecontainer .button:hover {
            background-color: #ffcc00;
            /* Amarillo oscuro */
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

    <?php include 'menu_profesor.php'; ?>

    <!-- Aquí va el contenido y el JS exclusivo de la página, si lo hubiera -->

    <div class="pagina">
        <div class="wecontainer">
            <h1>Modificar Datos</h1>
            <div class="perfil-container">
                <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto de perfil" class="perfil-foto" id="perfilFoto">
            </div>

            <form method="POST" action="" enctype="multipart/form-data">
                <?php if ($error_message): ?>
                    <p class="error-message"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <p class="success-message"><?php echo $success_message; ?></p>
                <?php endif; ?>

                <div class="form">
                    <!-- Foto de perfil -->
                    <label for="foto" style="font-weight:bold;">Cambiar foto de perfil:</label>
                    <input type="file" name="foto" id="foto" accept="image/*" style="margin-bottom:10px;">
                    <label for="cedula">Número de Cédula:</label>
                    <p  id="cedula" name="cedula">
                        <?php echo isset($estudiante['cedula']) ? $estudiante['cedula'] : ''; ?>
                    </p>

                    <label for="nombres">Nombres:</label>
                    <p  id="nombres" name="nombres">
                    <?php echo isset($estudiante['nombres']) ? $estudiante['nombres'] : ''; ?>
                    </p>

                    <label for="apellidos">Apellidos:</label>
                    <p  id="apellidos" name="apellidos">
                        <?php echo isset($estudiante['apellidos']) ? $estudiante['apellidos'] : ''; ?>
                    </p>
                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo"
                        class="<?php echo empty($_POST['sexo']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">
                        <option value="" <?php if (isset($estudiante['sexo']) && $estudiante['sexo'] == '')
                            echo 'selected'; ?>>Seleccione</option>
                        <option value="Masculino" <?php if (isset($estudiante['sexo']) && $estudiante['sexo'] == 'Masculino')
                            echo 'selected'; ?>>Masculino</option>
                        <option value="Femenino" <?php if (isset($estudiante['sexo']) && $estudiante['sexo'] == 'Femenino')
                            echo 'selected'; ?>>Femenino</option>
                    </select>

                    <label for="telefono">Teléfono:</label>
                    <input type="number" id="telefono" name="telefono"
                        value="<?php echo isset($estudiante['telefono']) ? $estudiante['telefono'] : ''; ?>"
                        class="<?php echo empty($_POST['telefono']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">

                    <label for="correo">Correo:</label>
                    <p  id="correo" name="correo">
                        <?php echo isset($estudiante['correo']) ? $estudiante['correo'] : ''; ?>
                    </p>

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion"
                        value="<?php echo isset($estudiante['direccion']) ? $estudiante['direccion'] : ''; ?>"
                        class="<?php echo empty($_POST['direccion']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">
                    <a href="forgotPassword.php">Cambiar contraseña</a>
                    <input type="submit" class="button" value="Guardar cambios">
                </div>
            </form>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
         // Solo JS exclusivo para la funcionalidad de la foto de perfil
         document.getElementById('editarPerfilBoton').addEventListener('click', function () {
            alert('La foto debe ser cuadrada (igual de altura y anchura).');
            document.getElementById('fotoInput').click();
        });

        document.getElementById('fotoInput').addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const img = new Image();
                img.onload = function () {
                    if (img.width !== img.height) {
                        alert('La foto debe ser cuadrada (igual de altura y anchura).');
                    } else {
                        document.getElementById('uploadForm').submit();
                    }
                };
                img.src = URL.createObjectURL(file);
            }
        });

    </script>

</body>

</html>