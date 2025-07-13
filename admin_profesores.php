<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principaladministracion.css">
    <link rel="stylesheet" href="css/admin_profesores.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Profesores - USM</title>
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

    <div class="contenedor-principal">
        <h1>Profesores</h1>
        <div class="tabla">
            <?php
            require "conexion.php";
            $sql = "
                SELECT
                    p.id AS id_profesor,
                    p.nombre AS Nombre_Profesor, 
                    GROUP_CONCAT(CONCAT(m.nombre, ' (', m.seccion, ')') SEPARATOR ', ') AS Materias
                FROM 
                    Profesores p
                LEFT JOIN 
                    Materias m ON p.id = m.id_profesor
                GROUP BY 
                  p.id;
            ";

            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                // Imprimir la tabla
                echo "<table border='1'>
                        <tr>
                            <th>Nombre del Profesor</th>
                            <th>Materias</th>
                            <th>  Añadir materias</th>
                            <th>Eliminar</th>
                        </tr>";

                // Salida de cada fila
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row['Nombre_Profesor'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                            <td>" . htmlspecialchars($row['Materias'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                            <td><a href='editar_profesor.php?id=" . htmlspecialchars($row['id_profesor'] ?? '', ENT_QUOTES, 'UTF-8') . "' class='btneditar'>Añadir</a></td>
                            <td><a href='eliminar_profesor.php?id=" . htmlspecialchars($row['id_profesor'] ?? '', ENT_QUOTES, 'UTF-8') . "' class='btneliminar'>Eliminar</a></td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "No se encontraron resultados.";
            }

            $conn->close();
            ?>
        </div>

        <a href="addprofesor.php" class="addprof">Añadir Profesor</a>

    </div>

</body>

</html>