<?php
include 'comprobar_sesion.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principaladministracion.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Alumnos - USM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            --background-color: rgb(255, 255, 255);
            --bg-container: rgb(240, 240, 240);
            color: #333;
        }

        body.dark-mode {
            --background-color: #1a1a1a;
            --bg-container: rgb(47, 47, 47);
            color: white;
        }

        .container {
            max-width: 1000px;
            /* Ajustar el ancho del contenedor */
            margin: auto;
            background-color: var(--bg-container);
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
            /* Añadir transición a los cambios */
            display: flex;
            flex-direction: column;
            align-items: center;
            align-content: center;
            height: auto;
            margin-top: 80px;
        }

        .titulo {
            font-size: 68px;
            /* Ajusta el tamaño de la fuente */
            font-weight: bold;
            /* Aplica negrita */
            margin-bottom: 20px;
            margin-top: 40px;
            /* Añade margen superior */
            color: #333333;
            font-family: 'Roboto', sans-serif;
            /* Aplica la fuente Roboto */
            text-align: center;
            /* Centra el título */
        }

        body.dark-mode .titulo {
            color: #ffffff;
            /* Color blanco para el modo oscuro */
        }

        .formulario-cedula {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
            width: 100%;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
            display: none;
            /* Inicialmente oculto */
        }

        .search-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Centra el contenedor de búsqueda */
            align-items: center;
            /* Alinea verticalmente */
            margin-bottom: 20px;
            gap: 20px;
            /* Añade espacio entre la barra de búsqueda y el botón */
            width: 100%;
        }

        .search-box {
            width: 100%;
            /* Permite que la barra de búsqueda ocupe todo el espacio disponible */
            max-width: 600px;
            /* Ajusta el ancho máximo de la barra de búsqueda */
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 40px;
            transition: box-shadow 0.3s ease;
            /* Animación al pasar el cursor */
        }

        .search-box:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            /* Efecto de sombra al pasar el cursor */
        }

        .search-button {
            padding: 10px;
            background-color: rgb(69, 160, 160);
            color: white;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            /* Centra el texto vertical y horizontalmente */
            font-size: 14px;
            /* Reduce el tamaño de la letra */
            transition: background-color 0.3s ease, transform 0.3s ease;
            /* Añade transición para animación */
        }

        .search-button:hover {
            background-color: rgb(45, 120, 120);
            /* Cambia el color de fondo al pasar el cursor */
            transform: scale(1.05);
            /* Escala ligeramente el botón */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--bg-container);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        th {
            background-color: rgb(69, 160, 160);
            color: white;
        }

        body.dark-mode th {
            background-color: rgb(45, 120, 120);
        }

        td {
            border-bottom: 1px solid #ddd;
        }

        body.dark-mode td {
            border-bottom: 1px solid #555;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        body.dark-mode tr:nth-child(even) {
            background-color: #333;
        }

        tr:hover {
            background-color: #e9e9e9;
        }

        body.dark-mode tr:hover {
            background-color: #444;
        }

        .acciones {
            display: flex direction column;
            gap: 10px;
        }

        .acciones a.btn-modificar,
        .acciones a.btn-ajustar {
            display: inline-block;
            padding: 8px 16px;
            background-color: rgb(69, 160, 160);
            color: white;
            text-decoration: none;
            border-radius: 20px;
            transition: background-color 0.3s ease;
            text-align: center;
            display: flex direction column;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            margin: 5px;
        }

        .acciones a.btn-modificar:hover,
        .acciones a.btn-ajustar:hover {
            background-color: rgb(45, 120, 120);
        }
    </style>
</head>

<body>
    <?php include 'navAdmin.php'; ?>

        <div class="container">
            <h1 class="titulo">Búsqueda de Estudiantes</h1>
            <?php
            require "conexion.php";

            $mostrarBusqueda = true;
            $errorMensaje = '';

            if (isset($_GET['query'])) {
                $busqueda = trim($_GET['query']);

            if ($busqueda === '') {
                $errorMensaje = 'Por favor, ingrese una cédula para buscar.';
            } else {
                require 'conexion.php';

                    if ($conn->connect_error) {
                        die("Conexión fallida: " . $conn->connect_error);
                    }

                    $conn->set_charset("utf8");

                    $sql = "
                    SELECT
                        du.cedula, du.nombres, du.apellidos,
                        e.semestre, e.creditosdisponibles,
                        GROUP_CONCAT(CONCAT(m.nombre, ' (', m.seccion, ')') SEPARATOR ', ') AS materias
                    FROM
                        datos_usuario du
                    LEFT JOIN
                        estudiantes e ON du.usuario_id = e.id_usuario
                    LEFT JOIN
                        inscripciones i ON du.usuario_id = i.id_estudiante
                    LEFT JOIN
                        materias m ON i.id_materia = m.id
                    WHERE
                        du.cedula LIKE '%$busqueda%'
                    GROUP BY
                        du.cedula, du.nombres, du.apellidos, e.semestre, e.creditosdisponibles
                ";

                    $result = $conn->query($sql);

                    if ($result === false) {
                        echo "<p>Error en la consulta SQL: " . $conn->error . "</p>";
                    } else {
                        if ($result->num_rows > 0) {
                            $mostrarBusqueda = false;
                            echo "<table border='1'>
                                <tr>
                                    <th>Nombre y Apellido</th>
                                    <th>Cédula</th>
                                    <th>Materias</th>
                                    <th>Semestre</th>
                                    <th>Créditos Disponibles</th>
                                    <th>Acciones</th>
                                </tr>";

                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['nombres'] ?? '', ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($row['apellidos'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                                    <td>" . htmlspecialchars($row['cedula'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                                    <td>" . htmlspecialchars($row['materias'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                                    <td>" . htmlspecialchars($row['semestre'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                                    <td>" . htmlspecialchars($row['creditosdisponibles'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                                    <td class='acciones'>
                                        <a href='modificar_seccion.php?id_estudiante=" . htmlspecialchars($row['cedula'] ?? '', ENT_QUOTES, 'UTF-8') . "' class='btn-modificar'>Modificar Sección</a>
                                        <a href='ajustar_creditos.php?id_estudiante=" . htmlspecialchars($row['cedula'] ?? '', ENT_QUOTES, 'UTF-8') . "' class='btn-ajustar'>Ajustar Créditos</a>
                                    </td>
                                </tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<p>No se encontraron resultados.</p>";
                        }
                    }

                    $conn->close();
                }
            }

            if ($mostrarBusqueda) {
                echo '
                <div class="search-container">
                    <form action="admin_alumnos.php" method="get" class="formulario-cedula" onsubmit="return validateForm()">
                        <input type="text" name="query" class="search-box" placeholder="Ingrese cédula...">
                        <button type="submit" class="search-button">Buscar</button>
                    </form>
                </div>
                <p id="error-message" class="error-message">' . $errorMensaje . '</p>
            ';
            }
            ?>

            <script>
                function validateForm() {
                    var query = document.querySelector('.search-box').value.trim();
                    var errorMessage = document.getElementById('error-message');

                    if (query === '') {
                        errorMessage.style.display = 'block';
                        setTimeout(function () {
                            errorMessage.style.display = 'none';
                        }, 3000);
                        return false;
                    }
                    return true;
                }

                if ('<?php echo $errorMensaje; ?>' !== '') {
                    document.getElementById('error-message').style.display = 'block';
                    setTimeout(function () {
                        document.getElementById('error-message').style.display = 'none';
                    }, 3000);
                }
            </script>
        </div>

    </body>

</html>