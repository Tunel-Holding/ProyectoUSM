<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);
include 'comprobar_sesion.php';
actualizar_actividad();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inscripción - UniHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/inscripcionstyle.css">
    <link rel="stylesheet" href="css/tablastyle.css">
    <!-- <link rel="icon" href="css/icono.png" type="image/png"> -->
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <!-- <link rel="icon" href="css/icono.png" type="image/png"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .soporte-flotante-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .soporte-flotante {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            background-color: #446ad3;
            padding: 12px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            overflow: hidden;
            width: 60px;            /* ✅ suficiente para mostrar solo el ícono */
            height: 50px;
            transition: width 0.4s ease, background-color 0.3s ease;
        }


        .soporte-flotante:hover {
            width: 210px; /* ✅ se expande hacia la izquierda */
            background-color: #365ac0;
        }

        .soporte-mensaje {
            flex: 1; /* ✅ ocupa todo el espacio disponible */
            opacity: 0;
            white-space: nowrap;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            transform: translateX(30px); /* animación desde la derecha */
            transition: transform 0.4s ease, opacity 0.4s ease;
            text-align: left; /* ✅ texto alineado a la izquierda */
            margin-right: auto;
            font-family: 'Poppins', sans-serif;
        }

        .soporte-flotante:hover .soporte-mensaje {
            opacity: 1;
            transform: translateX(0);
        }

        .soporte-flotante img {
            width: 30px;
            height: 30px;
            filter: brightness(0) invert(1);
            flex-shrink: 0;
            z-index: 2;
        }

        /* Estilos para el botón "Ver detalle" */
        .btn-detalle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            background: linear-gradient(135deg, #446ad3 0%, #365ac0 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(68, 106, 211, 0.2);
            min-width: 100px;
        }

        .btn-detalle:hover {
            background: linear-gradient(135deg, #365ac0 0%, #2a4a9e 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(68, 106, 211, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-detalle:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(68, 106, 211, 0.2);
        }

        .btn-detalle i {
            margin-right: 6px;
            font-size: 12px;
        }

        /* Estilos para la tabla */
        .contenedormaterias table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .contenedormaterias th {
            background: linear-gradient(135deg, #446ad3 0%, #365ac0 100%);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .contenedormaterias td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .contenedormaterias tr:hover {
            background-color: #f8f9fa;
        }

        .contenedormaterias tr:last-child td {
            border-bottom: none;
        }

        /* Estilos para modo oscuro */
        body.dark-mode {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ffffff;
        }

        body.dark-mode .contenedor-principal {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        body.dark-mode .contenedormaterias table {
            background: #2d3748;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .contenedormaterias th {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            color: #ffffff;
        }

        body.dark-mode .contenedormaterias td {
            background-color: #2d3748;
            color: #ffffff;
            border-bottom-color: #4a5568;
        }

        body.dark-mode .contenedormaterias tr:hover {
            background-color: #4a5568;
        }

        body.dark-mode .btn-detalle {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .btn-detalle:hover {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
        }

        body.dark-mode .materias {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        body.dark-mode .div-materia {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            border-color: #4a5568;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .div-materia:hover {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        body.dark-mode .div-materia h2 {
            color: #ffffff;
        }

        body.dark-mode .div-materia h4 {
            color: #a0aec0;
        }

        body.dark-mode .botoninscribir {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            color: #ffffff;
        }

        body.dark-mode .botoninscribir:hover {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        }

        body.dark-mode .soporte-flotante {
            background-color: #4a5568;
        }

        body.dark-mode .soporte-flotante:hover {
            background-color: #2d3748;
        }

        body.dark-mode h1 {
            color: #ffffff;
        }

        body.dark-mode h2 {
            color: #ffffff;
        }
    </style>

    <script src="js/control_inactividad.js"></script>
</head>

<body>
    <div class="contenedorentrante3">
        <img src="css\logo.png">
    </div>
    <div class="cabecera">

        <button type="button" id="logoButton">
           <!-- <img src="css/logoazul.png" alt="Logo"> -->
             <img src="css/menu.png" alt="Menú" class="logo-menu">


        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
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
                        <th>Profesor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require 'conexion.php';
                    $sqlInscritas = "SELECT m.id, m.nombre, m.seccion, m.creditos, p.nombre AS profesor, m.salon
                             FROM materias m
                             JOIN inscripciones i ON m.id = i.id_materia
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
                            echo "<td>" . $fila['profesor'] . "</td>";
                            echo "<td><a href='detalle_materia.php?id=" . $fila['id'] . "' class='btn-detalle'><i class='fas fa-eye'></i>Ver detalle</a></td>";
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

        <div class="soporte-flotante-container">
            <a href="contacto.php" class="soporte-flotante" title="Soporte">
                <span class="soporte-mensaje">Contacto soporte</span>
                <img src="css/audifonos-blanco.png" alt="Soporte">
            </a>
        </div>
</body>

</html>