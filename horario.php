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
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/tablahorario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Inicio - USM</title>
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


    <!-- Aquí solo debe ir JS exclusivo para la funcionalidad de la página, si lo hubiera. Se eliminó la lógica de menú y tema. -->
</body>

</html>