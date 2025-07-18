<?php
include 'comprobar_sesion.php';
actualizar_actividad();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inscripción - USM</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/inscripcionstyle.css">
    <link rel="stylesheet" href="css/tablastyle.css">
    <link rel="icon" href="css/icono.png" type="image/png">
    <script src="js/control_inactividad.js"></script>
</head>

<body>
    <div class="contenedorentrante3">
        <img src="css\logo.png">
    </div>
    <div class="cabecera">

        <button type="button" id="logoButton">
            <img src="css/logoazul.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
             <img src="css/audifonos-blanco.png" alt="Logo" class="soporte">
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_alumno.php'; ?>

    <div class="contenedor-principal">
        <h1>Cursos Inscritos</h1>
        <div class="contenedormaterias">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Sección</th>
                        <th>Créditos</th>
                        <th>Profesor</th>
                        <th>Salón</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require 'conexion.php';
                    $sqlInscritas = "SELECT m.id, m.nombre, m.seccion, m.creditos, p.nombre AS profesor, m.salon
                             FROM materias m
                             JOIN Inscripciones i ON m.id = i.id_materia
                             JOIN profesores p ON m.id_profesor = p.id
                             WHERE i.id_estudiante = ?";
                    $stmtInscritas = $conn->prepare($sqlInscritas);
                    $stmtInscritas->bind_param("i", $_SESSION['idusuario']);
                    $stmtInscritas->execute();
                    $resultadoInscritas = $stmtInscritas->get_result();

                    if ($resultadoInscritas->num_rows > 0) {
                        while ($fila = $resultadoInscritas->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $fila['nombre'] . "</td>";
                            echo "<td>" . $fila['seccion'] . "</td>";
                            echo "<td>" . $fila['creditos'] . "</td>";
                            echo "<td>" . $fila['profesor'] . "</td>";
                            echo "<td>" . $fila['salon'] . "</td>";

                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No hay materias inscritas.</td></tr>";
                    }

                    $stmtInscritas->close();
                    ?>
                </tbody>
            </table>
        </div>
        <h1>Cursos Disponibles</h1>
        <?php
        require 'conexion.php';
        function getAvailableCredits($id_usuario)
        {
            global $conn;
            $sql = "SELECT creditosdisponibles FROM estudiantes WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->bind_result($creditosdisponibles);
            $stmt->fetch();
            $stmt->close();
            return $creditosdisponibles;
        }
        ?>
        <h2 class="h2">Cursos disponibles: <?php echo getAvailableCredits($_SESSION['idusuario']) ?></h2>
        <div class="materias">
            <?php
            // Conexión a la base de datos
            $conexion = new mysqli("localhost", "root", "", "proyectousm");

            // Verificar la conexión
            if ($conexion->connect_error) {
                die("Conexión fallida: " . $conexion->connect_error);
            }

            // ID del usuario
            $id_usuario = $_SESSION['idusuario'];

            // Obtener el semestre del usuario
            $sql = "SELECT semestre FROM estudiantes WHERE id_usuario = ?";
            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                die("Error en la preparación de la consulta: " . $conexion->error);
            }
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->bind_result($semestre);
            $stmt->fetch();
            $stmt->close();

            if (isset($semestre)) {
                // Mostrar las materias disponibles
                $sqlMaterias = "SELECT m.id, m.nombre, m.creditos, m.semestre
            FROM (
                SELECT nombre, MIN(id) as min_id
                FROM materias
                GROUP BY nombre
            ) sub
            JOIN materias m ON m.id = sub.min_id
            LEFT JOIN Inscripciones i ON m.id = i.id_materia AND i.id_estudiante = ?
            LEFT JOIN HistoricoAcademico h ON m.id = h.MateriaID AND h.EstudianteID = ?
            WHERE m.semestre <= ? AND i.id_materia IS NULL AND (h.MateriaID IS NULL OR h.Calificacion IS NULL)";



                $stmtMaterias = $conexion->prepare($sqlMaterias);
                if (!$stmtMaterias) {
                    die("Error en la preparación de la consulta de materias: " . $conexion->error);
                }
                $stmtMaterias->bind_param("iii", $id_usuario, $id_usuario, $semestre);
                $stmtMaterias->execute();
                $resultado = $stmtMaterias->get_result();

                if ($resultado->num_rows > 0) {
                    while ($fila = $resultado->fetch_assoc()) {

                        ?>

                        <div class="div-materia">
                            <img src="css/images.png">
                            <h2><?php echo $fila['nombre']; ?></h2>
                            <h4>Creditos: <?php echo $fila['creditos']; ?></h4>
                            <a class="botoninscribir" data-valor="<?php echo $fila['nombre'] ?>">Ver secciones</a>
                        </div>

                        <?php
                    }
                } else {
                    echo "No hay cursos disponibles para inscribir.";
                }

                $stmtMaterias->close();
            } else {
                echo "No se encontró el usuario.";
            }

            // Cerrar la conexión
            $conexion->close();
            ?>
        </div>
        <script>
            // Solo JS exclusivo para la funcionalidad de la página
            document.querySelectorAll('.botoninscribir').forEach(button => {
                button.addEventListener('click', function () {
                    const valor = this.getAttribute('data-valor');
                    window.location.href = `secciones.php?valor=${valor}`;
                });
            });

            document.addEventListener('DOMContentLoaded', function () {
                <?php if (isset($_SESSION['mensaje'])): ?>
                    alert('<?php echo $_SESSION['mensaje']; ?>');
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>
            });
        </script>
</body>

</html>