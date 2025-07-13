<?php
session_start();
// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "proyectousm");

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los datos del estudiante más reciente
$sql = "SELECT cedula, nombres, apellidos, sexo, telefono, correo, direccion FROM datos_usuario WHERE usuario_id = '" . $_SESSION['idusuario'] . "'";
$result = $conn->query($sql);

// Verificar si la consulta fue exitosa
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

$estudiante = $result->fetch_assoc();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $cedula_nueva = $_POST['cedula'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $sexo = $_POST['sexo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $idusuario = $_SESSION['idusuario'];

    // Verificar que todos los datos estén presentes
    if (empty($cedula_nueva) || empty($nombres) || empty($apellidos) || empty($sexo) || empty($telefono) || empty($correo) || empty($direccion)) {
        $error_message = "Todos los campos son obligatorios.";
    } else {
        // Actualizar los datos en la base de datos
        $sql = "UPDATE datos_usuario SET 
                cedula='$cedula_nueva', 
                nombres='$nombres', 
                apellidos='$apellidos', 
                sexo='$sexo', 
                telefono='$telefono', 
                correo='$correo', 
                direccion='$direccion' 
                WHERE usuario_id='$idusuario'";

        $conn->query($sql);
        header('Location: datos.php');
    }
}

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
    <title>Datos - USM</title>
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
            color: #004c97;
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
            color: #004c97;
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
            color: #004c97;
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
            border: 1px solid #004c97;
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
            color: #004c97 !important;
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

    <!-- Aquí va el contenido y el JS exclusivo de la página, si lo hubiera -->

    <div class="pagina">
        <div class="wecontainer">
            <h1>Modificar Datos</h1>
            <form method="POST" action="">
                <?php if ($error_message): ?>
                    <p class="error-message"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <div class="form">
                    <label for="cedula">Número de Cédula:</label>
                    <input type="text" id="cedula" name="cedula"
                        value="<?php echo isset($estudiante['cedula']) ? $estudiante['cedula'] : ''; ?>"
                        class="<?php echo empty($_POST['cedula']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">

                    <label for="nombres">Nombres:</label>
                    <input type="text" id="nombres" name="nombres"
                        value="<?php echo isset($estudiante['nombres']) ? $estudiante['nombres'] : ''; ?>"
                        class="<?php echo empty($_POST['nombres']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">

                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos"
                        value="<?php echo isset($estudiante['apellidos']) ? $estudiante['apellidos'] : ''; ?>"
                        class="<?php echo empty($_POST['apellidos']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">

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
                    <input type="text" id="telefono" name="telefono"
                        value="<?php echo isset($estudiante['telefono']) ? $estudiante['telefono'] : ''; ?>"
                        class="<?php echo empty($_POST['telefono']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">

                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo"
                        value="<?php echo isset($estudiante['correo']) ? $estudiante['correo'] : ''; ?>"
                        class="<?php echo empty($_POST['correo']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion"
                        value="<?php echo isset($estudiante['direccion']) ? $estudiante['direccion'] : ''; ?>"
                        class="<?php echo empty($_POST['direccion']) && $_SERVER["REQUEST_METHOD"] == "POST" ? 'error' : ''; ?>">

                    <input type="submit" class="button" value="Guardar cambios">
                </div>
            </form>

        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>

</body>

</html>