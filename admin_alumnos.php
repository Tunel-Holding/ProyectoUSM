<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            justify-content: center;
        }

        body.dark-mode {
            --background-color: #1a1a1a;
            --bg-container: rgb(47, 47, 47);
            color: white;
        }

        .container {
            max-width: 950px;
            width: 100%;
            margin-top:10%;
            min-height: 70vh;
            text-align: center;
            background-color: var(--bg-container);
            padding: 50px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            align-content: center;
            height: auto;
            justify-content: center;
        }
        td:first-child {
            font-weight: bold;
            color: #007ACC;
        }
        .titulo {
            font-size: 48px;
            /* Ajusta el tamaño de la fuente */
            font-weight: bold;
            /* Aplica negrita */
            margin-bottom: 10px;
            margin-top: 30px;
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
            flex-direction: row; /* 🔄 antes era column */
            align-items: center;
            gap: 10px;
            margin-top: 40px;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            max-width: 700px;
        }

        .error-message {
            color: red;
            background-color: #fff0f0;
            border: 1px solid red;
            border-radius: 6px;
            padding: 8px 16px;
            max-width: 600px;
            font-weight: bold;
            margin-top: 10px;
            animation: fadeInError 0.4s ease;
            display: none;
        }

        @keyframes fadeInError {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .mensaje-vacio {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 8px;
            padding: 15px 25px;
            margin-top: 20px;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.5s ease;
            animation: fadeVacio 0.5s ease;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .icono-error {
            font-size: 24px;
        }

        @keyframes fadeVacio {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
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

        .button-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            padding: 12px 20px;
            border-radius: 40px;
            border: 1px solid #ccc;
            font-size: 16px;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: #446ad3;
            box-shadow: 0 0 10px rgba(68, 106, 211, 0.3);
            outline: none;
        }

        .search-box:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            /* Efecto de sombra al pasar el cursor */
        }

        .search-button {
            padding: 12px 24px;
            border-radius: 40px;
            background-color: rgba(68, 106, 211, 1);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, background-color 0.3s ease;
            animation: botonEntrada 0.6s ease forwards;
            animation-delay: 0.3s;
        }

        .search-button:hover {
            background-color: #365ac0;
            transform: scale(1.05);
        }

        .add-student-button {
            padding: 12px 24px;
            border-radius: 40px;
            background-color: #28a745; /* Color verde */
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .add-student-button:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        @keyframes botonEntrada {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media screen and (max-width: 600px) {
            .formulario-cedula {
                flex-direction: column;
            }

            .search-box,
            .search-button {
                width: 100%;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-left: auto;
            margin-right: auto;
            max-width: 950px;
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
            background-color: rgba(68, 106, 211, 1);
            color: white;
        }

        body.dark-mode th {
            background-color: rgba(68, 106, 211, 1);
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
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .acciones a.btn-modificar,
        .acciones a.btn-ajustar {
            display: inline-block;
            padding: 10px 20px;
            background-color: rgba(68, 106, 211, 0.74);
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 40px;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);

            animation: fadeSlide 0.6s ease;
        }

        .acciones a.btn-modificar:active,
        .acciones a.btn-ajustar:active {
            transform: scale(0.95);
        }

        .acciones a.btn-modificar:hover,
        .acciones a.btn-ajustar:hover {
            background-color: rgba(68, 106, 211, 1);
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
                                        <a href='modificar_calificaciones.php?id_estudiante=" . htmlspecialchars($row['cedula'] ?? '', ENT_QUOTES, 'UTF-8') . "' class='btn-ajustar'>Modificar Calificaciones</a>
                                    </td>
                                </tr>";
                            }
                            echo "</table>";
                        } else {
                            echo '
                                <div class="mensaje-vacio">
                                <span class="icono-error">🔍</span>
                                No se encontraron resultados para la cédula ingresada.
                                </div>
                                ';
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
                    <a href="agregar_estudiante.php" class="add-student-button">Agregar Estudiante</a>
                </div>
                <p id="error-message" class="error-message">⚠️ Cedula no ingresada' . $errorMensaje . '</p>
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