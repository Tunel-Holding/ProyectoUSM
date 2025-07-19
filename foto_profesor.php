<?php
include 'comprobar_sesion.php';
actualizar_actividad();
include 'conexion.php'; // Asegúrate de tener una conexión a la base de datos

$id_profesor = $_SESSION['idusuario'];
$sql = "SELECT foto FROM fotousuario WHERE id_usuario = '$id_profesor'";
$result = mysqli_query($conn, $sql);
$foto = "css/perfil.png"; // Foto por defecto

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $foto = $row['foto'];
}
actualizar_actividad();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="stylesheet" href="css/horario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Fotografía - USM</title>
    <script src="js/control_inactividad.js"></script>
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

    <div class="perfil-container">
        <img src="<?php echo $foto; ?>" alt="Foto de perfil" class="perfil-foto" id="perfilFoto">
        <form id="uploadForm" action="subir_foto_profesor.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="foto" id="fotoInput" style="display: none;">
            <button type="button" class="perfil-boton" id="editarPerfilBoton">Editar Perfil</button>
        </form>
    </div>

    <script>
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
    </style>
</body>

</html>