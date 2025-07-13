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
    <link rel="stylesheet" href="css/inscripcionstyle.css">
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

    <h1>Seleccionar Curso</h1>
    <div class="materias">
        <?php
        require 'conexion.php';

        // Suponiendo que tienes el ID del estudiante
        $estudiante_id = $_SESSION['idusuario']; // Cambia esto por el ID del estudiante real
        
        // Consulta para obtener las materias inscritas
        $sql = "SELECT m.nombre, m.id FROM inscripciones i 
                JOIN materias m ON i.id_materia = m.id 
                WHERE i.id_estudiante = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt->bind_param("i", $estudiante_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si hay materias inscritas
        if ($result->num_rows > 0) {
            // Imprimir las materias
            while ($fila = $result->fetch_assoc()) {
                ?>
                <div class="div-materia">
                    <img src="css/images.png">
                    <h2><?php echo htmlspecialchars($fila['nombre']); ?></h2>
                    <a class="botoninscribir" data-valor="<?php $_SESSION['nombremateria'] = $fila['nombre'];
                    echo htmlspecialchars($fila['id']); ?>">Tareas</a>
                </div>
                <?php
            }
        } else {
            echo "No tienes materias inscritas.";
        }

        // Cerrar conexión
        $stmt->close();
        $conn->close();
        ?>
    </div>
    <script>
        function goBack() {
            window.history.back();
        }
        document.querySelectorAll('.botoninscribir').forEach(button => {
            button.addEventListener('click', function () {
                const valor = this.getAttribute('data-valor');
                window.location.href = `dirigir_tarea.php?valor=${valor}`;
            });
        });
    </script>
</body>

</html>