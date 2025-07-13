<?php
include 'comprobar_sesion.php';

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Si el formulario ha sido enviado, procesa los datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusuario = $_SESSION['idusuario'];
    $numero_cedula = $_POST['numero_cedula'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $sexo = $_POST['sexo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];

    // Reemplazar "Ninguno" con una cadena vacía
    $numero_cedula = $numero_cedula == 'Ninguno' ? '' : $numero_cedula;
    $nombres = $nombres == 'Ninguno' ? '' : $nombres;
    $apellidos = $apellidos == 'Ninguno' ? '' : $apellidos;
    $sexo = $sexo == 'Ninguno' ? '' : $sexo;
    $telefono = $telefono == 'Ninguno' ? '' : $telefono;
    $correo = $correo == 'Ninguno' ? '' : $correo;
    $direccion = $direccion == 'Ninguno' ? '' : $direccion;

    // Insertar los datos en la base de datos
    $sql = "INSERT INTO datos_usuario (usuario_id, cedula, nombres, apellidos, sexo, telefono, correo, direccion)
            VALUES ('$idusuario', '$numero_cedula', '$nombres', '$apellidos', '$sexo', '$telefono', '$correo', '$direccion')";

    if ($conn->query($sql) === TRUE) {
        // Redirigir a datos_estudiante.php después de guardar los cambios
        header("Location: datos_profesor.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
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
    <link rel="stylesheet" href="css/principalprofesor.css">
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
            color: #fff;
            /* Blanco */
        }

        .wecontainer {
            font-family: "Poppins", sans-serif;
            max-width: 500px;
            /* Ajustamos el ancho del contenedor */
            background: #fff;
            color: var(--background-form);
            /* Azul oscuro */
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            border-top: 10px solid #ffd700;
            /* Amarillo */
            border-bottom: 10px solid #ffd700;
            /* Amarillo */
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        .wecontainer h1 {
            margin-bottom: 20px;
            color: #004c97;
            /* Azul oscuro */
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
            font-size: 0.9em;
            /* Reducimos el tamaño de la fuente */
        }

        .wecontainer input,
        select {
            left: 40%;
            padding: 8px;
            width: 100%;
            border: 1px solid #004c97;
            /* Azul oscuro */
            border-radius: 4px;
            font-size: 0.9em;
            /* Reducimos el tamaño de la fuente */
        }

        .wecontainer.button {
            grid-column: span 2;
            /* El botón ocupa ambas columnas */
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ffd700;
            /* Amarillo */
            color: #004c97;
            /* Azul oscuro */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }

        .wecontainer.button:hover {
            background-color: #ffcc00;
            /* Amarillo oscuro */
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

    <?php include 'menu_profesor.php'; ?>

    <!-- Aquí va el contenido y el JS exclusivo de la página, si lo hubiera -->

    <div class="pagina">
        <div class="wecontainer">
            <h1>Llenar Datos</h1>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                <div class="form">
                    <label for="numero_cedula">Número de Cédula:</label>
                    <input type="text" id="numero_cedula" name="numero_cedula"
                        value="<?php echo isset($numero_cedula) ? $numero_cedula : ''; ?>">

                    <label for="nombres">Nombres:</label>
                    <input type="text" id="nombres" name="nombres"
                        value="<?php echo isset($nombres) ? $nombres : ''; ?>">

                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos"
                        value="<?php echo isset($apellidos) ? $apellidos : ''; ?>">

                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo">
                        <option value="" <?php if (isset($sexo) && $sexo == '')
                            echo 'selected'; ?>>Seleccione</option>
                        <option value="Masculino" <?php if (isset($sexo) && $sexo == 'Masculino')
                            echo 'selected'; ?>>
                            Masculino</option>
                        <option value="Femenino" <?php if (isset($sexo) && $sexo == 'Femenino')
                            echo 'selected'; ?>>
                            Femenino</option>
                    </select>

                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono"
                        value="<?php echo isset($telefono) ? $telefono : ''; ?>">

                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" value="<?php echo isset($correo) ? $correo : ''; ?>">

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion"
                        value="<?php echo isset($direccion) ? $direccion : ''; ?>">
                </div>

                <input type="submit" class="button" value="Guardar cambios">
            </form>
        </div>
    </div>

</body>

</html>