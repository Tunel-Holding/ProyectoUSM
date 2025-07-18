<?php
include 'comprobar_sesion.php';
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

// Obtener el día actual en español para la región de Venezuela
$formatter = new IntlDateFormatter('es_VE', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Caracas', IntlDateFormatter::GREGORIAN, 'EEEE');
$dia_actual = $formatter->format(time());
$dia_actual = ucfirst($dia_actual);

// Consulta para obtener el horario del día actual del profesor
$query_horario = "SELECT m.nombre AS materia, m.salon, h.hora_inicio, h.hora_fin 
                  FROM horariosmateria h 
                  JOIN materias m ON h.id_materia = m.id 
                  WHERE m.id_profesor = ? AND h.dia = ?";
$stmt_horario = $conn->prepare($query_horario);
if (!$stmt_horario) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt_horario->bind_param("is", $profesor_id, $dia_actual);
if (!$stmt_horario->execute()) {
    die("Error en la ejecución de la consulta: " . $stmt_horario->error);
}
$result_horario = $stmt_horario->get_result();
if (!$result_horario) {
    die("Error al obtener el resultado: " . $stmt_horario->error);
}

// Consulta para obtener las materias que da el profesor y la cantidad de estudiantes en cada una
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
if (!$stmt_materias->execute()) {
    die("Error en la ejecución de la consulta: " . $stmt_materias->error);
}
$result_materias = $stmt_materias->get_result();
if (!$result_materias) {
    die("Error al obtener el resultado: " . $stmt_materias->error);
}
actualizar_actividad();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="stylesheet" href="css/horario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>    
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Inicio - USM</title>
    <script src="js/control_inactividad.js"></script>
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

    <?php include 'menu_profesor.php'; ?>

    <div class="contenedor-principal">

        <p class="bienvenido">Bienvenido a UniHub</p>

        <div class="divprincipal">
            <div class="contenedor-horario">
                <h2 class="titulo-horario">Horario del día: <?php echo $dia_actual; ?></h2>
                <table class="tabla-horario">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Salón</th>
                            <th>Hora de Inicio</th>
                            <th>Hora de Fin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_horario->num_rows > 0): ?>
                            <?php while ($row = $result_horario->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['materia']; ?></td>
                                    <td><?php echo $row['salon']; ?></td>
                                    <td><?php echo $row['hora_inicio']; ?></td>
                                    <td><?php echo $row['hora_fin']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td class="nohayclases" colspan="4"> ¡¡¡NO HAY CLASES!!! 🥳</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="contenedor-horario">
                <h2 class="titulo-horario">Materias</h2>
                <table class="tabla-horario">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Número de Estudiantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_materias->num_rows > 0): ?>
                            <?php while ($row = $result_materias->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['nombre']; ?></td>
                                    <td><?php echo $row['num_estudiantes']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td class="nohayclases" colspan="2">No hay materias asignadas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            // Aquí solo debe ir JS exclusivo de la página, si lo hubiera. Se eliminó la lógica de menú y tema.
        </script>

</body>

</html>