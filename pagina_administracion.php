<?php
include 'conexion.php'; // Incluye tu archivo de conexión a la base de datos

// Consulta para obtener la cantidad de profesores
$result_profesores = $conn->query("SELECT COUNT(*) as count FROM profesores");
if ($result_profesores === false) {
    die("Error en la consulta de profesores: " . $conn->error);
}
$row_profesores = $result_profesores->fetch_assoc();
$cantidad_profesores = $row_profesores['count'];

// Consulta para obtener la cantidad de estudiantes
$result_estudiantes = $conn->query("SELECT COUNT(*) as count FROM estudiantes");
if ($result_estudiantes === false) {
    die("Error en la consulta de estudiantes: " . $conn->error);
}
$row_estudiantes = $result_estudiantes->fetch_assoc();
$cantidad_estudiantes = $row_estudiantes['count'];

// Consulta para obtener la cantidad de materias únicas
$result_materias = $conn->query("SELECT COUNT(DISTINCT nombre) as count FROM materias");
if ($result_materias === false) {
    die("Error en la consulta de materias: " . $conn->error);
}
$row_materias = $result_materias->fetch_assoc();
$cantidad_materias = $row_materias['count'];

$registros = [];

$result_profesores = $conn->query("SELECT id, nombre, 'profesor' AS tipo FROM profesores ORDER BY id DESC LIMIT 1");
if ($result_profesores) {
    while ($row = $result_profesores->fetch_assoc()) {
        $registros[] = $row;
    }
}

$result_estudiantes = $conn->query("SELECT id, nombre, 'estudiante' AS tipo FROM estudiantes ORDER BY id DESC LIMIT 1");
if ($result_estudiantes) {
    while ($row = $result_estudiantes->fetch_assoc()) {
        $registros[] = $row;
    }
}

$result_materias = $conn->query("SELECT id, nombre, 'materia' AS tipo FROM materias ORDER BY id DESC LIMIT 1");
if ($result_materias) {
    while ($row = $result_materias->fetch_assoc()) {
        $registros[] = $row;
    }
}

usort($registros, function ($a, $b) {
    return $b['id'] - $a['id'];
});

$conn->close();
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
    <title>Inicio - USM</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap');
        /* Importa la fuente Roboto en negrita */

        .titulo {
            font-size: 85px;
            /* Ajusta el tamaño de la fuente */
            font-weight: bold;
            /* Aplica negrita */
            margin-bottom: 20px;
            margin-top: 80px;
            color: #333333;
            font-family: 'Roboto', sans-serif;
            /* Aplica la fuente Roboto */
            text-align: center;
            /* Centra el título */
        }

        .bienvenida {
            font-size: 100px;
            /* Ajusta el tamaño de la fuente */
            font-weight: normal;
            /* Aplica negrita */
            margin-bottom: 5px;
            margin-top: 40px;
            /* Añade margen superior */
            color: rgba(85, 85, 85, 0.5);
            /* Color transparente */
            font-family: 'Roboto', sans-serif;
            /* Aplica la fuente Roboto */
            text-align: center;
            /* Centra el mensaje */
        }

        .dark-mode .titulo {
            color: #ffffff;
        }

        .contenedor-dashboard {
            display: flex;
            gap: 30px;
            margin: 30px;
            align-items: stretch;
            flex-wrap: wrap;
        }

        .cuadro-panel,
        .cuadro-actividad {
            background-color: #d6d6d6a1;
            padding: 55px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            width: 48%;
            min-width: 300px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .titulo-seccion {
            font-size: 40px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .panel-resumen {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .panel-item {
            flex: 1;
            background-color: #f0f4f8;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .panel-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
        }

        .icono-contenedor {
            margin-bottom: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .panel-icono {
            width: 50px;
            height: 50px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .panel-item:hover .panel-icono {
            transform: scale(1.1);
        }

        .panel-item h4 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .contador {
            font-size: 28px;
            font-weight: bold;
            color: #007ACC;
        }

        .lista-actividad {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .lista-actividad li {
            margin-bottom: 10px;
            padding: 14px;
            background-color: #f9f9f9;
            border-radius: 6px;
            border-left: 4px solid #007ACC;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
            font-size: 18px;
            transition: transform 0.4s ease, background-color 0.4s ease;
            cursor: default;
        }

        .lista-actividad li:hover {
            transform: translateX(5px);
            background-color: #eef6ff;
        }

        /* Responsivo */
        @media screen and (max-width: 768px) {
            .contenedor-dashboard {
                flex-direction: column;
                gap: 20px;
            }

            .cuadro-panel,
            .cuadro-actividad {
                width: 100%;
            }

            .panel-resumen {
                flex-direction: column;
                gap: 10px;
            }

            .panel-item {
                width: 100%;
            }
        }

        /* Modo oscuro */
        body.dark-mode .cuadro-panel,
        body.dark-mode .cuadro-actividad {
            background-color: #1e1e1e;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.06);
        }

        body.dark-mode .titulo-seccion {
            color: #f0f0f0;
        }

        body.dark-mode .panel-item {
            background-color: #2a2a2a;
            color: #f0f0f0;
        }

        body.dark-mode .panel-item h4 {
            background-color: #2a2a2a;
            color: #f0ececff;
        }

        body.dark-mode .contador {
            color: #4ea1ff;
        }

        body.dark-mode .lista-actividad li {
            background-color: #2a2a2a;
            color: #f0f0f0;
            border-left: 4px solid #4ea1ff;
            box-shadow: 0 2px 5px rgba(255, 255, 255, 0.05);
        }

        body.dark-mode .lista-actividad li:hover {
            background-color: #333c4d;
        }

        body.dark-mode .panel-icono {
            filter: brightness(1.1);
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
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_administrador.php'; ?>

    <div class="contenedor-principal">
        <h1 class="bienvenida">Bienvenido al sistema UniHub</h1>
        <h1 class="titulo">Estadísticas del Sistema</h1>
        <div class="contenedor-dashboard">
            <div class="cuadro-panel">
                <h3 class="titulo-seccion">Panel de Administración</h3>
                <div class="panel-resumen">
                    <div class="panel-item">
                        <div class="icono-contenedor">
                            <img src="https://cdn-icons-png.freepik.com/512/5526/5526504.png" alt="Estudiantes"
                                class="panel-icono">
                        </div>
                        <h4>Estudiantes</h4>
                        <p class="contador"><?php echo $cantidad_estudiantes; ?></p>
                    </div>

                    <div class="panel-item">
                        <div class="icono-contenedor">
                            <img src="https://cdn-icons-png.flaticon.com/256/6454/6454364.png" alt="Profesores"
                                class="panel-icono">
                        </div>
                        <h4>Profesores</h4>
                        <p class="contador"><?php echo $cantidad_profesores; ?></p>
                    </div>

                    <div class="panel-item">
                        <div class="icono-contenedor">
                            <img src="https://cdn-icons-png.flaticon.com/512/5780/5780875.png" alt="Materias"
                                class="panel-icono">
                        </div>
                        <h4>Materias</h4>
                        <p class="contador"><?php echo $cantidad_materias; ?></p>
                    </div>
                </div>
            </div>
            <!-- Actividad reciente -->
            <div class="cuadro-actividad">
                <h3 class="titulo-seccion">Actividad Reciente</h3>
                <ul class="lista-actividad">
                    <?php foreach ($registros as $r): ?>
                        <?php if ($r['tipo'] == 'profesor'): ?>
                            <li>🧑‍🏫 Nuevo profesor: <?php echo htmlspecialchars($r['nombre']); ?></li>
                        <?php elseif ($r['tipo'] == 'estudiante'): ?>
                            <li>🎓 Nuevo estudiante: <?php echo htmlspecialchars($r['nombre']); ?></li>
                        <?php elseif ($r['tipo'] == 'materia'): ?>
                            <li>📘 Materia añadida: <?php echo htmlspecialchars($r['nombre']); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const estadisticas = document.querySelectorAll('.estadistica');
                estadisticas.forEach((element, index) => {
                    setTimeout(() => {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }, index * 300); // Delay between each element
                });
            });
        </script>
</body>

</html>