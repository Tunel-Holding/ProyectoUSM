<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_PROFESOR);
require_once 'comprobar_sesion.php';
actualizar_actividad();

include 'conexion.php';
$conn->set_charset("utf8mb4");

$user_id = $_SESSION['idusuario'];

// Obtener el id del profesor asociado al usuario
$sql_profesor = "SELECT id FROM profesores WHERE id_usuario = ? LIMIT 1";
$stmt_profesor = $conn->prepare($sql_profesor);
if (!$stmt_profesor) {
    die("Error en la preparación de la consulta de profesor: " . $conn->error);
}
$stmt_profesor->bind_param("i", $user_id);
$stmt_profesor->execute();
$stmt_profesor->bind_result($profesor_id);
if (!$stmt_profesor->fetch()) {
    die("No se encontró el profesor asociado a este usuario.");
}
$stmt_profesor->close();

// Validar si el usuario tiene datos registrados en datos_usuario
$sql_check = "SELECT 1 FROM datos_usuario WHERE usuario_id = ? LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
if ($stmt_check) {
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows === 0) {
        header("Location: llenar_datos_profesor.php");
        exit();
    }
    $stmt_check->close();
}

// Obtener el horario semanal completo del profesor
$query_horario = "SELECT hm.dia, hm.hora_inicio, hm.hora_fin, m.nombre AS materia, m.salon
                  FROM materias m
                  JOIN horariosmateria hm ON m.id = hm.id_materia
                  WHERE m.id_profesor = ?
                  ORDER BY hm.dia, hm.hora_inicio";
$stmt_horario = $conn->prepare($query_horario);
if (!$stmt_horario) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt_horario->bind_param("i", $profesor_id);
$stmt_horario->execute();
$result_horario = $stmt_horario->get_result();

$datos_horario = [];
$horas_disponibles = [];
if ($result_horario->num_rows > 0) {
    while ($row = $result_horario->fetch_assoc()) {
        $hora_inicio = strtotime($row['hora_inicio']);
        $hora_fin = strtotime($row['hora_fin']);
        $intervalo = 45 * 60; // 45 minutos
        for ($hora = $hora_inicio; $hora < $hora_fin; $hora += $intervalo) {
            $hora_formateada = date("H:i:s", $hora);
            $datos_horario[$row['dia']][$hora_formateada] = [
                "materia" => $row['materia'],
                "salon" => $row['salon'],
                "inicio" => ($hora == $hora_inicio),
                "rowspan" => ceil(($hora_fin - $hora_inicio) / $intervalo)
            ];
            $horas_disponibles[] = $hora_formateada;
        }
    }
}
$horas_disponibles = array_unique($horas_disponibles);
sort($horas_disponibles);
$stmt_horario->close();

// Materias asignadas al profesor y número de estudiantes
$query_materias = "SELECT m.nombre, COUNT(i.id_estudiante) AS num_estudiantes 
                   FROM materias m 
                   LEFT JOIN inscripciones i ON m.id = i.id_materia 
                   WHERE m.id_profesor = ? 
                   GROUP BY m.id";
$stmt_materias = $conn->prepare($query_materias);
if (!$stmt_materias) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt_materias->bind_param("i", $profesor_id);
$stmt_materias->execute();
$result_materias = $stmt_materias->get_result();
$stmt_materias->close();

actualizar_actividad();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/principalunihub.css">
    <link rel="stylesheet" href="css/horario.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Inicio - UniHub</title>
    <script src="js/control_inactividad.js"></script>
</head>
<body>
    <div class="cabecera">
        <button type="button" id="logoButton">
            <img src="css/menu.png" alt="Menú" class="logo-menu">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>
    <?php include 'menu_profesor.php'; ?>
    <div class="profesor-layout-dos-columnas">
        <!-- Columna izquierda: Horario semanal -->
        <div class="profesor-columna-izquierda">
            <div class="profesor-contenedor-horario">
                <h2 class="profesor-titulo-horario">Horario Semanal Completo</h2>
                <?php
                $dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
                // Inicializar matriz de control de rowspan
                $rowspan_control = [];
                foreach ($dias as $dia) {
                    $rowspan_control[$dia] = [];
                }
                ?>
                <div class="div-horario">
                    <table class="tabla-horario horario-tabla">
                        <thead>
                        <tr>
                            <th>Hora</th>
                            <?php foreach ($dias as $dia): ?>
                                <th><?php echo $dia; ?></th>
                            <?php endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (empty($datos_horario) || empty($horas_disponibles)) {
                            echo "<tr><td colspan='" . (count($dias)+1) . "' class='nohayclases'>No hay clases programadas para este profesor</td></tr>";
                        } else {
                            foreach ($horas_disponibles as $hora) {
                                $hora_para_mostrar = date("H:i", strtotime($hora));
                                echo "<tr>";
                                echo "<td class='hora-col'><strong>$hora_para_mostrar</strong></td>";
                                foreach ($dias as $dia) {
                                    // Si hay un rowspan activo, lo decrementamos y no imprimimos celda
                                    if (isset($rowspan_control[$dia][$hora]) && $rowspan_control[$dia][$hora] > 0) {
                                        $rowspan_control[$dia][$hora]--;
                                        continue;
                                    }
                                    $contenido_celda = "";
                                    $rowspan = 1;
                                    $celda_ocupada = false;
                                    if (isset($datos_horario[$dia][$hora])) {
                                        $info = $datos_horario[$dia][$hora];
                                        if ($info["inicio"]) {
                                            $contenido_celda = "<div class='materia-nombre'>" . htmlspecialchars($info["materia"]) . "</div>" .
                                                             "<div class='materia-aula'>Aula: " . htmlspecialchars($info["salon"]) . "</div>";
                                            $rowspan = $info["rowspan"];
                                            if ($rowspan > 1) {
                                                // Marcar las siguientes horas como cubiertas por rowspan
                                                $hora_actual = strtotime($hora);
                                                for ($i = 1; $i < $rowspan; $i++) {
                                                    $siguiente_hora = date("H:i:s", strtotime("+" . (45*$i) . " minutes", $hora_actual));
                                                    $rowspan_control[$dia][$siguiente_hora] = ($rowspan_control[$dia][$siguiente_hora] ?? 0) + 1;
                                                }
                                            }
                                            echo "<td class='horario-celda' rowspan='$rowspan'>$contenido_celda</td>";
                                            $celda_ocupada = true;
                                        }
                                    }
                                    if (!$celda_ocupada) {
                                        echo "<td class='celda-vacia'></td>";
                                    }
                                }
                                echo "</tr>";
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Columna derecha: Materias asignadas -->
        <div class="profesor-columna-derecha">
            <div class="profesor-contenedor-horario">
                <h2 class="profesor-titulo-horario">Materias Asignadas</h2>
                <div class="contenedor-materias-grid">
                    <?php if ($result_materias->num_rows > 0): ?>
                        <?php while ($row = $result_materias->fetch_assoc()): ?>
                            <div class="tarjeta-materia">
                                <div class="icono-materia">📚</div>
                                <h3 class="nombre-materia"><?php echo htmlspecialchars($row['nombre']); ?></h3>
                                <div class="info-materia">
                                    <div class="info-item">
                                        <span class="icono">👥</span>
                                        <span class="texto"><?php echo $row['num_estudiantes']; ?> estudiantes</span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="tarjeta-materia">
                            <h3 class="nombre-materia">No hay materias asignadas.</h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Aquí solo debe ir JS exclusivo de la página, si lo hubiera. Se eliminó la lógica de menú y tema.
    </script>
</body>
</html>