<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/tabla_calificaciones.css">
    <link rel="stylesheet" href="css/Notas A2.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Notas</title>
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

    <h1>Tabla de Calificaciones</h1>
    <div class="tabla">
        <table>
            <tr>
                <th class="th">Materia</th>
                <th class="th">Parcial 1</th>
                <th class="th">Parcial 2</th>
                <th class="th">Parcial 3</th>
                <th class="th">Parcial 4</th>
                <th class="th">Final</th>
                <th class="th">Acción</th>

            </tr>
            <?php

            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "proyectousm";

            // Crear conexión
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Verificar conexión
            if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
            }

            // Obtener el id de usuario de la sesión
            $idusuario = $_SESSION['idusuario'];

            // Obtener id_materia de la tabla inscripciones
            $sql = "SELECT id_materia FROM inscripciones WHERE id_estudiante = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error al preparar la declaración: " . $conn->error);
            }
            $stmt->bind_param("i", $idusuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $materias = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Obtener semestre de la tabla estudiantes
            $sql = "SELECT semestre FROM estudiantes WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error al preparar la declaración: " . $conn->error);
            }
            $stmt->bind_param("i", $idusuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            $semestre = $usuario['semestre'];
            $stmt->close();

            // Obtener notas de la tabla notas
            $notasEncontradas = false;
            foreach ($materias as $materia) {
                $materia_id = $materia['id_materia'];
                $sql = "SELECT n.Parcial1, n.Parcial2, n.Parcial3, n.Parcial4, n.Final, m.nombre AS materia 
                        FROM notas n 
                        INNER JOIN materias m ON n.materia_id = m.id 
                        WHERE n.usuario_id = ? AND n.materia_id = ? AND n.semestre = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Error al preparar la declaración: " . $conn->error);
                }
                $stmt->bind_param("iii", $idusuario, $materia_id, $semestre);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $notasEncontradas = true;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='td'>" . $row['materia'] . "</td>";
                        echo "<td class='td'>" . ($row['Parcial1'] ?? 'N/A') . "</td>";
                        echo "<td class='td'>" . ($row['Parcial2'] ?? 'N/A') . "</td>";
                        echo "<td class='td'>" . ($row['Parcial3'] ?? 'N/A') . "</td>";
                        echo "<td class='td'>" . ($row['Parcial4'] ?? 'N/A') . "</td>";
                        echo "<td class='td'>" . ($row['Final'] ?? 'N/A') . "</td>";
                        echo "<td class='td button-cell'><button onclick=\"verParciales('" . $materia_id . "', '" . $idusuario . "')\">Ver Parcial</button></td>";
                        echo "</tr>";
                    }
                }
                $stmt->close();

            }
            if (!$notasEncontradas) {
                echo "<tr>";
                echo "<td class='td' colspan='7'>No hay datos disponibles</td>";
                echo "</tr>";
            }
            $conn->close();
            ?>
        </table>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Selecciona Parcial</h2>
            <div id="modal-body">
                <!-- Botones de parciales aparecerán aquí -->
            </div>
        </div>
    </div>

    <!-- Modal de error -->
    <div id="modal-error" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeErrorModal()">&times;</span>

            <p>No se ha encontrado ningún parcial</p>
        </div>
    </div>
    <script>
        // Funcionalidad exclusiva de los modales de notas
        async function verParciales(materia_id, usuario_id) {
            try {
                const response = await fetch(`obtener_parciales.php?materia_id=${materia_id}&usuario_id=${usuario_id}`);
                if (!response.ok) {
                    throw new Error('Error en la solicitud');
                }
                const parciales = await response.json();
                if (parciales.length > 0) {
                    const modalBody = document.getElementById('modal-body');
                    modalBody.innerHTML = parciales.map(parcial =>
                        `<button onclick="verParcial('${materia_id}', '${usuario_id}', ${parcial})">Parcial ${parcial}</button>`
                    ).join('');
                    const modal = document.getElementById('modal');
                    modal.style.display = 'block';
                } else {
                    const errorModal = document.getElementById('modal-error');
                    errorModal.style.display = 'block';
                }
            } catch (error) {
                console.error('Error al obtener los parciales:', error);
            }
        }

        async function verParcial(materia_id, usuario_id, parcial_num) {
            try {
                const response = await fetch(`obtener_archivo.php?materia_id=${materia_id}&usuario_id=${usuario_id}&parcial=${parcial_num}`);
                if (!response.ok) {
                    throw new Error('Error en la solicitud');
                }
                const data = await response.json();
                if (data.length > 0) {
                    const rutaArchivo = data[0];
                    window.open(rutaArchivo, '_blank');
                } else {
                    const errorModal = document.getElementById('modal-error');
                    errorModal.style.display = 'block';
                }
            } catch (error) {
                console.error('Error al obtener los archivos:', error);
                const errorModal = document.getElementById('modal-error');
                errorModal.style.display = 'block';
            }
        }

        function closeModal() {
            const modal = document.getElementById('modal');
            modal.style.display = 'none';
        }

        function closeErrorModal() {
            const errorModal = document.getElementById('modal-error');
            errorModal.style.display = 'none';
        }

        // Cerrar el modal cuando se hace clic fuera del contenido del modal
        window.onclick = function (event) {
            const modal = document.getElementById('modal');
            const errorModal = document.getElementById('modal-error');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            if (event.target == errorModal) {
                errorModal.style.display = 'none';
            }
        }
    </script>
</body>

</html>