<?php
include 'comprobar_sesion.php';

// Conexión a la base de datos
include 'conexion.php';

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el ID del usuario de la sesión
$id_usuario = $_SESSION['idusuario'];

// Obtener los datos del usuario
$sql = "SELECT cedula, nombres, apellidos, sexo, telefono, correo, direccion 
        FROM datos_usuario 
        WHERE usuario_id = ?";

// Preparar la sentencia
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error en la consulta: " . $conn->error);
}
$stmt->bind_param("i", $id_usuario); // Enlazar el ID del usuario

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

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

// Verificar si se encontraron datos del usuario
if ($result->num_rows > 0) {
    $estudiante = $result->fetch_assoc();
} else { 
    // Redirigir a la página de llenado de datos si no hay datos del usuario 
    header("Location: llenar_datos.php");
    exit();
}

$stmt->close(); // Cerrar la sentencia
$conn->close(); // Cerrar la conexión

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
        .perfil-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 80vh;
        }

        .perfil-foto {
            width: 400px;
            height: 400px;
            border-radius: 50%;
            margin-right: 100px;
        }

        .perfil-boton {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            cursor: pointer;
            width: 400px;
            height: 150px;
            transition: all 0.3s ease-in-out;
            font-size: 40px;
        }

        .perfil-boton:hover {
            background-color: #0056b3;
        }
        .contenedor-principal {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: auto;
            color: #fff;
            /* Blanco */
        }

        .wecontainer {
            margin-top: 20px;
            font-family: "Poppins", sans-serif;
            max-width: 1400px;
            /* Aumenté aún más el ancho máximo */
            background: var(--background-form);
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            border-radius: 8px;
            border-top: 10px solid #ffd700;
            /* Amarillo */
            border-bottom: 10px solid #ffd700;
            /* Amarillo */
            border-left: 1px solid #ffd700 !important;
            border-right: 1px solid #ffd700 !important;
            transition: 1s background ease-in-out;
        }

        .wecontainer h1 {
            text-align: center;
            color: #004c97;
            /* Azul oscuro */
        }

        .wecontainer ul {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            /* Permite que los elementos se envuelvan en varias filas */
            justify-content: space-between;
        }

        .wecontainer li {
            margin: 10px;
            padding: 20px;
            /* Aumenté el padding para cuadros más grandes */
            background: transparent;
            /* Azul claro */
            border: 1px solid #004c97;
            /* Borde azul oscuro */
            border-radius: 4px;
            flex: 1 1 calc(45% - 40px);
            /* Reduje un poco el cálculo para cuadros más grandes */
            box-sizing: border-box;
            font-size: 1.2em;
            /* Aumenté el tamaño de la fuente */
        }

        .wecontainer li strong {
            color: #004c97;
            /* Azul oscuro */
        }

        .wecontainer li span {
            color: #004c97;
            /* Azul oscuro */
        }

        .wecontainer a {
            font-weight: 700;
        }

        .button {
            display: block;
            margin: 20px auto 0;
            padding: 10px 20px;
            background-color: #ffd700;
            /* Amarillo */
            color: #004c97;
            /* Azul oscuro */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            font-size: 1em;
            text-decoration: none;
        }

        .button:hover {
            background-color: #ffcc00;
            /* Amarillo oscuro */
        }
    </style>
</head>

<body>
    
    <div class="cabecera">

        <button type="button" id="logoButton">
            <img src="css/logoazul.png" alt="Logo">
            
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
             <img src="css/audifonos-blanco.png" alt="Logo" class="soporte">
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_alumno.php'; ?>

    <div class="contenedor-principal">
        <div class="wecontainer">
            <h1>Datos del Estudiante</h1>
            <div class="perfil-container">
        <img src="<?php echo $foto; ?>" alt="Foto de perfil" class="perfil-foto" id="perfilFoto">
       
    </div>
            <ul>
                <li><strong>Número de Cédula:</strong> <span><?php echo $estudiante['cedula']; ?></span></li>
                <li><strong>Nombres:</strong> <span><?php echo $estudiante['nombres']; ?></span></li>
                <li><strong>Apellidos:</strong> <span><?php echo $estudiante['apellidos']; ?></span></li>
                <li><strong>Sexo:</strong> <span><?php echo $estudiante['sexo']; ?></span></li>
                <li><strong>Teléfono:</strong> <span><?php echo $estudiante['telefono']; ?></span></li>
                <li><strong>Correo:</strong> <span>&nbsp;&nbsp;&nbsp;<?php echo $estudiante['correo']; ?></span></li>
                <li><strong>Dirección:</strong> <span><?php echo $estudiante['direccion']; ?></span></li>
            </ul>
            <a href="modificar_datos.php" class="button">Modificar datos</a>
        </div>
    </div>

    <script>
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