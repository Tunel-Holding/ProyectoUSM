<?php
require_once 'AuthGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);

include 'comprobar_sesion.php';
actualizar_actividad();
include 'conexion.php'; // Asegúrate de tener un archivo para la conexión a la base de datos
$conn->set_charset("utf8mb4");

// Habilitar la visualización de errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('America/Caracas');

// Obtener la ID del usuario desde la sesión
$user_id = $_SESSION['idusuario'];

// Validar si el usuario tiene datos registrados en datos_usuario
$sql_check = "SELECT 1 FROM datos_usuario WHERE usuario_id = ? LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
if ($stmt_check) {
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows === 0) {
        header("Location: datos.php");
        exit();
    }
    $stmt_check->close();
}

// Obtener el día actual en español para la región de Venezuela
$formatter = new IntlDateFormatter('es_VE', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Caracas', IntlDateFormatter::GREGORIAN, 'EEEE');
$dia_actual = $formatter->format(time());

// Convertir el primer carácter del día a mayúscula
$dia_actual = ucfirst($dia_actual);

// Consulta para obtener el horario del día actual
$query = "SELECT m.nombre AS materia, m.salon, h.hora_inicio, h.hora_fin 
          FROM horarios h 
          JOIN materias m ON h.id_materia = m.id 
          WHERE h.id_estudiante = ? AND h.dia = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt->bind_param("is", $user_id, $dia_actual);
if (!$stmt->execute()) {
    die("Error en la ejecución de la consulta: " . $stmt->error);
}
$result = $stmt->get_result();
if (!$result) {
    die("Error al obtener el resultado: " . $stmt->error);
}

// Consulta para obtener las notas del estudiante
// Consulta para obtener las materias inscritas y los datos completos del profesor usando el id_usuario del profesor asignado a la materia
$query_materias_profesor = "
SELECT 
    m.nombre AS materia,
    fu.foto,
    p.id_usuario AS profesor_usuario_id,
    du.cedula,
    du.nombres,
    du.apellidos,
    du.telefono,
    du.correo,
    du.direccion
FROM inscripciones i
JOIN materias m ON i.id_materia = m.id
JOIN profesores p ON m.id_profesor = p.id
LEFT JOIN fotousuario fu ON p.id_usuario = fu.id_usuario
LEFT JOIN datos_usuario du ON du.usuario_id = p.id_usuario
WHERE i.id_estudiante = ?
";
$stmt = $conn->prepare($query_materias_profesor);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();
actualizar_actividad();
$conn->close();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="stylesheet" href="css/horario.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Inicio - USM</title>
    <script src="js/control_inactividad.js"></script>
    <style>
        .contenedor-materias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            padding: 30px;
        }

        .tarjeta-materia {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            position: relative;
            overflow: visible;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .nombre-materia {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .profesor-img {
            width: 89px;
            height: 89px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .profesor-img:hover {
            transform: scale(1.05);
        }

        .hover-container {
            position: relative;
            display: inline-block;
        }

        .hover-box {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            position: absolute;
            top: 110%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 10px;
            width: 240px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            transition: max-height 0.5s ease, opacity 0.5s ease;
            z-index: 100;
        }

        .hover-container:hover .hover-box {
            max-height: 300px;
            opacity: 1;
        }

        .info-lista {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .info-lista li {
            font-size: 13px;
            margin-bottom: 6px;
            color: #333;
        }
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
    </style>
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
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_alumno.php'; ?>

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
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
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
            <h2 class="titulo-horario">Materias Inscritas</h2>
            <div class="contenedor-materias-grid">
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <div class="tarjeta-materia">
                        <p class="nombre-materia"><?php echo htmlspecialchars($row['materia']); ?></p>
                        <div class="hover-container">
                            <img src="<?php echo $row['foto'] ?: 'https://cdn-icons-png.flaticon.com/512/6073/6073873.png'; ?>" alt="Foto del profesor" class="profesor-img">
                            <div class="hover-box">
                                <ul class="info-lista">
                                    <li><strong>Nombre:</strong> <?php echo (!empty($row['nombres']) || !empty($row['apellidos'])) ? htmlspecialchars(trim($row['nombres'] . ' ' . $row['apellidos'])) : 'No disponible'; ?></li>
                                    <li><strong>Cédula:</strong> <?php echo !empty($row['cedula']) ? htmlspecialchars($row['cedula']) : 'No disponible'; ?></li>
                                    <li><strong>Teléfono:</strong> <?php echo !empty($row['telefono']) ? htmlspecialchars($row['telefono']) : 'No disponible'; ?></li>
                                    <li><strong>Email:</strong> <?php echo !empty($row['correo']) ? htmlspecialchars($row['correo']) : 'No disponible'; ?></li>
                                    <li><strong>Dirección:</strong> <?php echo !empty($row['direccion']) ? htmlspecialchars($row['direccion']) : 'No disponible'; ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <div class="soporte-flotante-container">
        <a href="contacto.php" class="soporte-flotante" title="Soporte">
            <span class="soporte-mensaje">Contacto soporte</span>
            <img src="css/audifonos-blanco.png" alt="Soporte">
        </a>
    </div>
    <script>
        // Aquí solo debe ir JS exclusivo de la página, si lo hubiera. Se eliminó la lógica de menú y tema.
    </script>
</body>

</html>