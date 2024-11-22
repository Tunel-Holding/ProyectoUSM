<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Materias Disponibles</title>
</head>
<body>
    <h1>Materias Disponibles</h1>
    <form method="post" action="">
        <label for="id_usuario">ID de Usuario:</label>
        <input type="number" id="id_usuario" name="id_usuario" required>
        <button type="submit">Ver Materias</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_usuario'])) {
        // Conexión a la base de datos
        $conexion = new mysqli("localhost", "root", "", "proyectousm");

        // Verificar la conexión
        if ($conexion->connect_error) {
            die("Conexión fallida: " . $conexion->connect_error);
        }

        // ID del usuario
        $id_usuario = $_POST['id_usuario'];

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
            $sqlMaterias = "
                SELECT m.nombre, MIN(m.id) as min_id, m.semestre 
                FROM materias m
                LEFT JOIN Inscripciones i ON m.id = i.id_materia AND i.id_estudiante = ?
                LEFT JOIN HistoricoAcademico h ON m.id = h.MateriaID AND h.EstudianteID = ?
                WHERE m.semestre <= ? AND i.id_materia IS NULL AND h.MateriaID IS NULL
                GROUP BY m.nombre, m.semestre";

            $stmtMaterias = $conexion->prepare($sqlMaterias);
            if (!$stmtMaterias) {
                die("Error en la preparación de la consulta de materias: " . $conexion->error);
            }
            $stmtMaterias->bind_param("iii", $id_usuario, $id_usuario, $semestre);
            $stmtMaterias->execute();
            $resultado = $stmtMaterias->get_result();

            if ($resultado->num_rows > 0) {
                echo "<form method='post' action=''>";
                echo "<h2>Seleccione una materia:</h2>";
                echo "<select name='materia_id'>";
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<option value='" . $fila['min_id'] . "'>" . $fila['nombre'] . "</option>";
                }
                echo "</select>";
                echo "<input type='hidden' name='id_usuario' value='$id_usuario'>";
                echo "<input type='hidden' name='semestre' value='$semestre'>";
                echo "<button type='submit'>Ver Secciones</button>";
                echo "</form>";
            } else {
                echo "No hay materias disponibles.";
            }

            $stmtMaterias->close();
        } else {
            echo "No se encontró el usuario.";
        }

        // Cerrar la conexión
        $conexion->close();

    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['materia_id'])) {
        // Conexión a la base de datos
        $conexion = new mysqli("localhost", "root", "", "proyectousm");

        // Verificar la conexión
        if ($conexion->connect_error) {
            die("Conexión fallida: " . $conexion->connect_error);
        }

        // ID de la materia
        $materia_id = "1";
        $id_usuario = "1";
        $semestre = "1";

        // Mostrar las secciones disponibles de la materia seleccionada
        $sqlSecciones = "
            SELECT m.id, m.nombre, m.creditos, m.salon, p.nombre AS profesor 
            FROM materias m
            LEFT JOIN Profesores p ON m.id_profesor = p.id
            LEFT JOIN Inscripciones i ON m.id = i.id_materia AND i.id_estudiante = ?
            LEFT JOIN HistoricoAcademico h ON m.id = h.MateriaID AND h.EstudianteID = ?
            WHERE m.nombre = (SELECT nombre FROM materias WHERE id = ?) 
            AND m.semestre <= ? AND i.id_materia IS NULL AND h.MateriaID IS NULL";

        $stmtSecciones = $conexion->prepare($sqlSecciones);
        if (!$stmtSecciones) {
            die("Error en la preparación de la consulta de secciones: " . $conexion->error);
        }
        $stmtSecciones->bind_param("iiii", $id_usuario, $id_usuario, $materia_id, $semestre);
        $stmtSecciones->execute();
        $resultado = $stmtSecciones->get_result();

        if ($resultado->num_rows > 0) {
            echo "<h2>Secciones disponibles para la materia seleccionada:</h2>";
            while ($fila = $resultado->fetch_assoc()) {
                echo "<div class='div-materia'>";
                echo "<img src='css/images.png'>";
                echo "<h2>" . $fila['nombre'] . " - Sección: " . $fila['salon'] . "</h2>";
                echo "<h4>Créditos: " . $fila['creditos'] . " - Profesor: " . $fila['profesor'] . "</h4>";
                echo "</div>";
            }
        } else {
            echo "No hay secciones disponibles.";
        }

        $stmtSecciones->close();
        // Cerrar la conexión
        $conexion->close();

    }
    ?>
</body>
</html>
