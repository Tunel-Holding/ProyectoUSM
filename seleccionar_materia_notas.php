<?php
include 'comprobar_sesion.php';
actualizar_actividad();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <link rel="stylesheet" href="css/inscripcionstyle.css">
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
            <img src="css/logo.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_profesor.php'; ?>

    <div class="materias">
        <?php
        require 'conexion.php'; // Asegúrate de que este archivo conecta a tu base de datos
        
        // Obtener el nombre de usuario del profesor desde la sesión
        $id_usuario = $_SESSION['idusuario']; // Cambia esto por la información real del profesor
        
        // Consulta para obtener el ID del profesor
        $sql_profesor = "SELECT id FROM profesores WHERE id_usuario = ?";
        $stmt_profesor = $conn->prepare($sql_profesor);
        if (!$stmt_profesor) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt_profesor->bind_param("s", $id_usuario);
        $stmt_profesor->execute();
        $result_profesor = $stmt_profesor->get_result();

        if ($result_profesor->num_rows > 0) {
            // Obtener el ID del profesor
            $fila_profesor = $result_profesor->fetch_assoc();
            $profesor_id = $fila_profesor['id'];
        } else {
            die("No se encontró el profesor con ese nombre de usuario.");
        }

        $stmt_profesor->close();

        // Consulta para obtener las materias que da el profesor
        $sql = "SELECT m.nombre, m.id FROM materias m 
                WHERE m.id_profesor = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt->bind_param("i", $profesor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si hay materias
        if ($result->num_rows > 0) {
            // Imprimir las materias
            while ($fila = $result->fetch_assoc()) {
                ?>
                <div class="div-materia">
                    <img src="css/images.png">
                    <h2><?php echo htmlspecialchars($fila['nombre']); ?></h2>
                    <a class="botoninscribir" data-valor="<?php echo htmlspecialchars($fila['id']); ?>">Notas</a>
                </div>
                <?php
            }
        } else {
            echo "No tienes materias asignadas.";
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
                window.location.href = `dirigirchat_profesores.php?valor=${valor}`;
            });
        });
    </script>

</body>

</html>