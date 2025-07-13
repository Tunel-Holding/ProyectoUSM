<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/admin_materias.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principaladministracion.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Inicio - USM</title>
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

    <h1>Secciones de la Materia</h1>
    <?php
    require 'conexion.php';
    $nombre = $_GET['nombre'];
    $sql = "SELECT m.*, p.nombre AS profesor_nombre FROM materias m LEFT JOIN profesores p ON m.id_profesor = p.id WHERE m.nombre='$nombre' ORDER BY m.seccion ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "<table><tr><th>Nombre de la Materia</th><th>Profesor</th><th>Salón</th><th>Créditos</th><th>Semestre</th><th>Sección</th><th></th><th></th></tr>";
        // Salida de datos de cada fila
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["nombre"] . "</td>";
            echo "<td>" . $row["profesor_nombre"] . "</td>";
            echo "<td>" . $row["salon"] . "</td>";
            echo "<td>" . $row["creditos"] . "</td>";
            echo "<td>" . $row["semestre"] . "</td>";
            echo "<td>" . $row["seccion"] . "</td>";
            echo "<td class='button-cell'><button onclick=\"window.location.href='editar_seccion.php?id=" . $row["id"] . "'\">Editar</button></td>";
            echo "<td class='button-cell'><button onclick=\"window.location.href='eliminar_seccion.php?id=" . $row["id"] . "'\">Eliminar</button></td></tr>";
        }
        echo "</table>";
    } else {
        echo "No se encontraron secciones para la materia seleccionada.";
    }

    // Obtener información de la materia para el formulario de edición
    $sqlMateria = "SELECT nombre, creditos FROM materias WHERE nombre='$nombre' LIMIT 1";
    $resultMateria = $conn->query($sqlMateria);
    $materia = $resultMateria->fetch_assoc();
    $conn->close();
    ?>

    <a href='añadir_seccion.php?nombre=<?php echo $nombre; ?>'><button id='agregar'>Añadir Sección</button></a>

    <h1>Editar Materia</h1>
    <form class="form-materia" action="procesar_editar_materia.php" method="POST">
        <input type="hidden" name="nombreOriginal" value="<?php echo $materia['nombre']; ?>">
        <div>
            <label for="nombre">Nombre de la Materia:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $materia['nombre']; ?>" required>
        </div>
        <div>
            <label for="creditos">Número de Créditos:</label>
            <input type="number" id="creditos" name="creditos" value="<?php echo $materia['creditos']; ?>" required>
        </div>
        <div>
            <button type="submit" id="editar">Editar Materia</button>
        </div>
    </form>

    <script>
        // Aquí solo debe ir JS exclusivo de la página, si lo hubiera. Se eliminó la lógica de menú y tema.
    </script>

</body>

</html>