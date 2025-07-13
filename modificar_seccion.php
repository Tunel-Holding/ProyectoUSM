<?php
include 'comprobar_sesion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="css/icono.png" type="image/png">
        <link rel="stylesheet" href="css/admin-general.css">>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Modificar Sección - UniHub</title>
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

        /* Estilos para los elementos del formulario */
        select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            max-width: 300px;
        }

        .btn-modificar {
            padding: 10px;
            border-radius: 20px;
            padding: 8px 16px;
            border: 1px solid #ccc;
            transition: background-color 0.3s ease, transform 0.3s ease;
            background-color: rgba(68, 106, 211, 1);
            color: white;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            max-width: 300px;
        }

        .btn-modificar:hover {
            background-color: rgba(68, 106, 211, 1);
            transform: scale(1.05);
        }

        .btn-modificar:active {
            transform: scale(0.95);
        }

        .btn-container {
            display: flex;
            justify-content: center;
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
            background-color: rgba(68, 106, 211, 1);
            color: white;
        }

        body.dark-mode th {
            background-color: rgba(68, 106, 211, 1);
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
        <h1>Modificar Sección</h1>
        <?php
        require 'conexion.php';

        if (isset($_GET['id_estudiante'])) {
            $cedula_estudiante = htmlspecialchars($_GET['id_estudiante']);
            echo "<p>Cédula del Estudiante: $cedula_estudiante</p>";

            // Verificar la existencia del estudiante en datos_usuario
            $sql_verificar = "SELECT * FROM datos_usuario WHERE cedula = ?";
            if ($stmt_verificar = $conn->prepare($sql_verificar)) {
                $stmt_verificar->bind_param("s", $cedula_estudiante);
                $stmt_verificar->execute();
                $result_verificar = $stmt_verificar->get_result();

                if ($result_verificar->num_rows > 0) {

                    // Consulta principal para obtener las materias inscritas y sus secciones
                    $sql = "
                        SELECT
                            du.nombres, du.apellidos, m.nombre AS materia, m.id AS id_materia, m.seccion AS seccion_actual
                        FROM
                            inscripciones i
                        JOIN
                            datos_usuario du ON i.id_estudiante = du.usuario_id
                        JOIN
                            estudiantes e ON du.usuario_id = e.id_usuario
                        JOIN
                            materias m ON i.id_materia = m.id
                        WHERE
                            du.cedula = ?
                    ";

                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("s", $cedula_estudiante);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            echo "<p>Materias Inscritas:</p>";
                            echo "<table>
                                    <tr>
                                        <th>Materia</th>
                                        <th>Sección Actual</th>
                                        <th>Nueva Sección</th>
                                    </tr>";

                            while ($row = $result->fetch_assoc()) {
                                $id_materia = htmlspecialchars($row['id_materia']);
                                $materia = htmlspecialchars($row['materia']);
                                $seccion_actual = htmlspecialchars($row['seccion_actual']);

                                // Consulta para obtener todas las secciones disponibles de la materia actual
                                $sql_secciones = "
                                    SELECT seccion
                                    FROM materias
                                    WHERE nombre = ?
                                ";
                                if ($stmt_secciones = $conn->prepare($sql_secciones)) {
                                    $stmt_secciones->bind_param("s", $materia);
                                    $stmt_secciones->execute();
                                    $result_secciones = $stmt_secciones->get_result();

                                    echo "<tr>
                                            <td>$materia</td>
                                            <td>$seccion_actual</td>
                                            <td>
                                                <form action='modificar_seccion_procesar.php' method='post'>
                                                    <input type='hidden' name='id_estudiante' value='$cedula_estudiante'>
                                                    <input type='hidden' name='id_materia' value='$id_materia'>
                                                    <input type='hidden' name='materia_nombre' value='$materia'> <!-- Añadido -->
                                                    <select name='nueva_seccion'>";
                                    while ($row_seccion = $result_secciones->fetch_assoc()) {
                                        $seccion = htmlspecialchars($row_seccion['seccion']);
                                        echo "<option value='$seccion'>$seccion</option>";
                                    }
                                    echo "</select>
                                                    <button type='submit' class='btn-modificar'>Modificar</button>
                                                </form>
                                            </td>
                                        </tr>";
                                    $stmt_secciones->close();
                                } else {
                                    echo "<p>Error al preparar la consulta de secciones: " . $conn->error . "</p>";
                                }
                            }

                            echo "</table>";
                        } else {
                            echo "<p>No se encontró el estudiante en la consulta principal.</p>";
                        }

                        $stmt->close();
                    } else {
                        echo "<p>Error al preparar la consulta principal: " . $conn->error . "</p>";
                    }
                } else {
                    echo "<p>Estudiante no encontrado en datos_usuario.</p>";
                }

                $stmt_verificar->close();
            } else {
                echo "<p>Error al preparar la consulta de verificación: " . $conn->error . "</p>";
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
    </script>

</body>

</html>