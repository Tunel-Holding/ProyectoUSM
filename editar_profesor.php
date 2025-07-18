<?php
include 'comprobar_sesion.php';
actualizar_actividad();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principaladministracion.css">
    <link rel="stylesheet" href="css/admin_profesores.css">
    <link rel="stylesheet" href="css/editprof.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Inicio - USM</title>
    <script src="js/control_inactividad.js"></script>
</head>

<body>

    <div class="cabecera">
        <button type="button" id="logoButton">
            <img src="css/logoazul.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>

    <?php include 'menu_administrador.php'; ?>

    <h2>Seleccione una materia:</h2>
    <div class="form-container">
        <form action="editar_profesores.php" method="POST" class="styled-form">
            <input type="hidden" name="id_profesor" value="<?php echo $_GET['id']; ?>">

            <select name="id_materia" id="materia">
                <?php
                // Suponiendo que tienes una conexión a la base de datos 
                require "conexion.php";
                // Consultar las materias 
                $result = $conn->query("SELECT id, nombre, seccion FROM materias ORDER BY nombre ASC");
                // Llenar la lista desplegable con las materias 
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['nombre']} ({$row['seccion']})</option>";
                }
                ?>
            </select>
            <button type="submit">Asignar</button>
        </form>
    </div>

    <script>
        // Aquí solo debe ir JS exclusivo de la página, si lo hubiera. Se eliminó la lógica de menú y tema.
    </script>

</body>

</html>