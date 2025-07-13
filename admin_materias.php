<?php
include 'comprobar_sesion.php';
?>
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
    <title>Materias - USM</title>
</head>

<body>
    <?php include 'navAdmin.php'; ?>

    <h1>Materias</h1>
    <?php
    require 'conexion.php';
    $sql = "SELECT DISTINCT nombre FROM materias";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "<table><tr><th>Nombre de la Materia</th><th></th><th></th></tr>";
        // Salida de datos de cada fila
        while ($row = $result->fetch_assoc()) {
            $nombreMateria = $row["nombre"];
            echo "<tr><td>" . $nombreMateria . "</td>";
            echo "<td class='button-cell'><button onclick=\"window.location.href='editar_materia.php?nombre=$nombreMateria'\">Editar</button></td>";
            echo "<td class='button-cell'><button onclick=\"window.location.href='eliminar_materia.php?nombre=$nombreMateria'\">Eliminar</button></td></tr>";
        }
        echo "</table>";
        echo "<a href='añadir_materias.php'><button id='agregar' >Agregar</button></a>";
    } else {
        echo "<a href='añadir_materias.php'><button id='agregar' >Agregar</button></a>";
    }
    $conn->close();
    ?>
    <script>
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
            if (!contenedor.contains(event.target) && contenedor.classList.contains('toggle')) {
                contenedor.classList.remove('toggle');
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