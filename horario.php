<?php
include 'comprobar_sesion.php';
actualizar_actividad();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/tablahorario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Inicio - USM</title>
    <style>
        .soporte-flotante-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .soporte-flotante {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            background-color: #446ad3;
            padding: 12px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            overflow: hidden;
            width: 60px;            /* ✅ suficiente para mostrar solo el ícono */
            height: 50px;
            transition: width 0.4s ease, background-color 0.3s ease;
        }


        .soporte-flotante:hover {
            width: 210px; /* ✅ se expande hacia la izquierda */
            background-color: #365ac0;
        }

        .soporte-mensaje {
            flex: 1; /* ✅ ocupa todo el espacio disponible */
            opacity: 0;
            white-space: nowrap;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            transform: translateX(30px); /* animación desde la derecha */
            transition: transform 0.4s ease, opacity 0.4s ease;
            text-align: left; /* ✅ texto alineado a la izquierda */
            margin-right: auto;
            font-family: 'Poppins', sans-serif;
        }

        .soporte-flotante:hover .soporte-mensaje {
            opacity: 1;
            transform: translateX(0);
        }

        .soporte-flotante img {
            width: 30px;
            height: 30px;
            filter: brightness(0) invert(1);
            flex-shrink: 0;
            z-index: 2;
        }
    </style>
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

    <?php include 'menu_alumno.php'; ?>

    <h1>Horario de Clases</h1>
    <div class="div-horario">
        <?php
        require "conexion.php";
        $id_estudiante = $_SESSION['idusuario'];
        $sql = "
                SELECT h.dia, h.hora_inicio, h.hora_fin, m.nombre AS materia, m.salon, p.nombre AS profesor 
                FROM horarios h
                JOIN materias m ON h.id_materia = m.id
                JOIN profesores p ON m.id_profesor = p.id
                WHERE h.id_estudiante = $id_estudiante
            ";
        $result = $conn->query($sql);

        if (!$result) {
            die("Error en la consulta" . $conn->error);
        }

        $datos = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $hora_inicio = strtotime($row['hora_inicio']);
                $hora_fin = strtotime($row["hora_fin"]);
                $intervalo = 45 * 60;
                for ($hora = $hora_inicio; $hora < $hora_fin; $hora += $intervalo) {
                    $hora_formateada = date("H:i:s", $hora);
                    $datos[$row["dia"]][$hora_formateada] = [
                        "materia" => $row["materia"],
                        "salon" => $row["salon"],
                        "profesor" => $row["profesor"],
                        "inicio" => ($hora == $hora_inicio),
                        "rowspan" => ceil(($hora_fin - $hora_inicio) / $intervalo)
                    ];
                }
            }
            $conn->close();
        }
        ?>

        <table class="horario-tabla">
            <tr>
                <th>Hora</th>
                <th>Lunes</th>
                <th>Martes</th>
                <th>Miércoles</th>
                <th>Jueves</th>
                <th>Viernes</th>
            </tr>
            <?php

            function generar_horas($inicio, $intervalo, $total)
            {
                $horas = [];
                $hora_actual = strtotime($inicio);
                for ($i = 0; $i < $total; $i++) {
                    $horas[] = date("H:i:s", $hora_actual);
                    $hora_actual = strtotime("+$intervalo minutes", $hora_actual);
                }
                return $horas;
            }
            $dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
            $horas = generar_horas("07:00:00", 45, 10);
            foreach ($horas as $hora) {
                $hora_para_mostrar = date("H:i", strtotime($hora));
                echo "<tr>";
                echo "<td>$hora_para_mostrar</td>";
                foreach ($dias as $dia) {
                    $contenido_celda = "";
                    $rowspan = 1;
                    if (isset($datos[$dia][$hora])) {
                        $info = $datos[$dia][$hora];
                        if ($info["inicio"]) {
                            $contenido_celda = $info["materia"] . "<br>" . $info["salon"] . "<br>" . $info["profesor"];
                            $rowspan = $info["rowspan"];
                            echo "<td class='horario-celda' rowspan='$rowspan'>$contenido_celda</td>";
                        }
                    } elseif (!isset($datos[$dia][$hora]) || !$info["inicio"]) {
                        echo "<td class='celda-vacia'></td>";
                    }
                }
                echo "</tr>";
            }
            ?>
        </table>
    </div>
    <div class="soporte-flotante-container">
        <a href="contacto.php" class="soporte-flotante" title="Soporte">
            <span class="soporte-mensaje">Contacto soporte</span>
            <img src="css/audifonos-blanco.png" alt="Soporte">
        </a>
     </div>
    <!-- Aquí solo debe ir JS exclusivo para la funcionalidad de la página, si lo hubiera. Se eliminó la lógica de menú y tema. -->
</body>

</html>