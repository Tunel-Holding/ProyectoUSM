<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_PROFESOR);
include 'comprobar_sesion.php';
actualizar_actividad();
if (!isset($_SESSION['idusuario'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="icon" href="css/icono.png" type="image/png"> -->
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tablahorario.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="stylesheet" href="css/tablahorario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Cursos - UniHub</title>
    <script src="js/control_inactividad.js"></script>
</head>

<body>

    <div class="cabecera">

        <button type="button" id="logoButton">
            <img src="css/logo.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_profesor.php'; ?>

    <h1>Lista de Estudiantes Inscritos</h1>

    <?php
    require "conexion.php";
    $id_materia = $_GET['valor'];

    // Obtener los estudiantes inscritos en la materia
    $sql_estudiantes = "
                SELECT du.nombres, du.apellidos, du.cedula, du.correo 
                FROM datos_usuario du
                JOIN inscripciones i ON i.id_estudiante = du.id
                WHERE i.id_materia = $id_materia
                AND du.usuario_id NOT IN (SELECT id_usuario FROM profesores)
            ";
    $result_estudiantes = $conn->query($sql_estudiantes);

    if ($result_estudiantes->num_rows > 0) {
        echo "<div class='div-horario'>";
        echo "<table class='horario-tabla'>";
        echo "<tr><th>Nombres</th><th>Apellidos</th><th '>Cédula</th><th '>Correo</th></tr>";
        while ($fila = $result_estudiantes->fetch_assoc()) {
            echo "<tr>";
            echo "<td class='celda-vacia'>" . htmlspecialchars($fila['nombres']) . "</td>";
            echo "<td class='celda-vacia'>" . htmlspecialchars($fila['apellidos']) . "</td>";
            echo "<td class='celda-vacia'>" . htmlspecialchars($fila['cedula']) . "</td>";
            echo "<td class='celda-vacia'>" . htmlspecialchars($fila['correo']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "No hay estudiantes inscritos en esta materia.";
    }
    $conn->close();
    ?>

</body>

</html>