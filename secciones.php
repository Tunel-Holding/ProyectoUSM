<?php
session_start();
if (isset($_GET['valor'])) {
    $_SESSION['materiaselecc'] = $_GET['valor'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inscripción - USM</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/inscripcionstyle.css">
    <link rel="icon" href="css/icono.png" type="image/png">
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

    <h1><?php echo $_SESSION['materiaselecc'] ?></h1>
    <div class="materias">

        <?php
        require 'conexion.php'; // Asegúrate de conectar a tu base de datos
        
        if (isset($_GET['valor'])) {
            $nombre_materia = $_GET['valor'];

            // Consulta para obtener las secciones de la materia, el nombre del profesor y el horario
            $stmt = $conn->prepare("SELECT m.id, m.nombre, m.seccion, m.creditos, p.nombre AS profesor, h.dia, h.hora_inicio, h.hora_fin
                                FROM materias m
                                JOIN profesores p ON m.id_profesor = p.id
                                JOIN horariosmateria h ON m.id = h.id_materia
                                WHERE m.nombre = ?");
            $stmt->bind_param("s", $nombre_materia);
            $stmt->execute();
            $result = $stmt->get_result();

            // Agrupar los resultados por sección
            $secciones = [];
            while ($row = $result->fetch_assoc()) {
                $seccion = $row['seccion'];
                if (!isset($secciones[$seccion])) {
                    $secciones[$seccion] = [
                        'profesor' => $row['profesor'],
                        'id' => $row['id'],
                        'horarios' => []
                    ];
                }
                $secciones[$seccion]['horarios'][] = $row['dia'] . " " . $row['hora_inicio'] . " - " . $row['hora_fin'];
            }

            // Mostrar las secciones y sus horarios agrupados
            foreach ($secciones as $seccion => $data) {
                ?>
                <div class="div-seccion">
                    <img src="css/images.png" alt="Imagen de la materia">
                    <h2>Profesor: <?php echo $data['profesor']; ?></h2>
                    <h2>Sección: <?php echo $seccion; ?></h2>
                    <h4>Horarios: </h4>
                    <h4><?php echo implode("<br>", $data['horarios']); ?></h4>
                    <a class="botoninscribir" data-valor="<?php echo $data['id']; ?>">Inscribirse</a>
                </div>
                <?php
            }
        } else {
            echo "No se ha recibido el nombre de la materia.";
        }
        ?>


    </div>
    <script>
        function goBack() {
            window.history.back();
        }
        document.querySelectorAll('.botoninscribir').forEach(button => {
            button.addEventListener('click', function () {
                const valor = this.getAttribute('data-valor');
                window.location.href = `inscribir.php?valor=${valor}`;
            });
        });
    </script>
</body>

</html>