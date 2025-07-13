<?php
include 'comprobar_sesion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    
    <link rel="stylesheet" href="css/admin-general.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Ajustar Créditos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            --background-color: #d4d4d4;
            --bg-container: #f9f9f9;
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
            animation: fadeIn 1s ease-in-out;
        }

        /* Animación de desvanecimiento */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        h1 {
            text-align: center;
            font-family: 'Lobster', cursive;
            font-size: 60px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            color: #555;
        }

        input {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            max-width: 300px;
        }

        .btn-ajustar {
            padding: 10px;
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            border: 1px solid #ccc;
            background-color: rgb(69, 160, 160);
            color: white;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            max-width: 300px;
        }

        .btn-ajustar:hover {
            background-color: rgb(69, 160, 160);
            transform: scale(1.05);
        }

        .btn-ajustar:active {
            transform: scale(0.95);
        }

        .btn-container {
            display: flex;
            justify-content: center;
            border-radius: 15px;
        }

        /* Estilos para los elementos del formulario */
        select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            max-width: 300px;
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
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include 'navAdmin.php'; ?>

    <div class="container">
        <h1>Ajustar Créditos</h1>
        <?php
        require 'conexion.php';

        if (isset($_GET['id_estudiante'])) {
            $cedula_estudiante = htmlspecialchars($_GET['id_estudiante']);
            echo "<p>Cédula del Estudiante: $cedula_estudiante</p>";

            // Obtener la información del estudiante
            $sql_estudiante = "
                SELECT du.nombres, du.apellidos, e.creditosdisponibles, e.id_usuario
                FROM datos_usuario du
                JOIN estudiantes e ON du.usuario_id = e.id_usuario
                WHERE du.cedula = ?
            ";

            if ($stmt_estudiante = $conn->prepare($sql_estudiante)) {
                $stmt_estudiante->bind_param("s", $cedula_estudiante);
                $stmt_estudiante->execute();
                $result_estudiante = $stmt_estudiante->get_result();

                if ($result_estudiante->num_rows > 0) {
                    $row_estudiante = $result_estudiante->fetch_assoc();
                    $nombres = htmlspecialchars($row_estudiante['nombres']);
                    $apellidos = htmlspecialchars($row_estudiante['apellidos']);
                    $creditosdisponibles = htmlspecialchars($row_estudiante['creditosdisponibles']);
                    $id_usuario = htmlspecialchars($row_estudiante['id_usuario']);

                    echo "<p>Nombre: $nombres $apellidos</p>";
                    echo "<p>Créditos Disponibles: $creditosdisponibles</p>";

                    // Formulario para ajustar créditos
                    echo "<form action='ajustar_creditos_procesar.php' method='post'>
                            <input type='hidden' name='id_usuario' value='$id_usuario'>
                            <input type='hidden' name='id_estudiante' value='$cedula_estudiante'>
                            <label for='creditos'>Nuevo Número de Créditos:</label>
                            <input type='number' name='creditos' id='creditos' required min='0'>
                            <div class='btn-container'>
                                <button type='submit' class='btn-ajustar'>Ajustar Créditos</button>
                            </div>
                          </form>";
                } else {
                    echo "<p>No se encontró el estudiante con la cédula proporcionada.</p>";
                }

                $stmt_estudiante->close();
            } else {
                echo "<p>Error al preparar la consulta de estudiante: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Cédula de estudiante no proporcionada.</p>";
        }

        $conn->close();
        ?>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
        const contenedor = document.getElementById('contenedor');
        const botonIzquierdo = document.getElementById('boton-izquierdo');
        const botonDerecho = document.getElementById('boton-derecho');
        botonIzquierdo.addEventListener('click', () => {
            contenedor.scrollBy({
                left: -94,
                behavior: 'smooth'
            });
        });
        botonDerecho.addEventListener('click', () => {
            contenedor.scrollBy({
                left: 94,
                behavior: 'smooth'
            });
        });

        document.getElementById('logoButton').addEventListener("click", () => {
            document.getElementById('menu').classList.toggle('toggle');
            event.stopPropagation();
        });
        document.addEventListener('click', function(event) {
            if (!container.contains(event.target) && container.classList.contains('toggle')) {
                container.classList.remove('toggle');
            }
        });
        document.addEventListener('click', function(event) {
            var div = document.getElementById('menu');
            if (!div.contains(event.target)) {
                div.classList.remove('toggle');
            }
        });
        document.getElementById('switchtema').addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
        });

        // Aplicar la preferencia guardada del usuario al cargar la p谩gina
        window.addEventListener('load', function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
                document.getElementById('switchtema').checked = true;
            }
        });

        function redirigir(url) {
            window.location.href = url;;
            // Cambia esta URL a la página de destino 
        }
        window.onload = function() {
            document.getElementById('inicio').addEventListener('click', function() {
                redirigir('pagina_administracion.php');
            });
            document.getElementById('datos').addEventListener('click', function() {
                redirigir('buscar_datos_admin.html');
            });
            document.getElementById('profesor').addEventListener('click', function() {
                redirigir('admin_profesores.php');
            });
            document.getElementById('alumno').addEventListener('click', function() {
                redirigir('admin_alumnos.php');
            });
            document.getElementById('materias').addEventListener('click', function() {
                redirigir('admin_materias.php');
            });
        }
    </script>

</body>

</html>