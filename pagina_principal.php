<?php
include 'comprobar_sesion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);


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

// Consulta para obtener las materias inscritas del estudiante
$query_materias_inscritas = "SELECT DISTINCT i.id_materia 
                             FROM inscripciones i 
                             WHERE i.id_estudiante = ?";
$stmt_materias = $conn->prepare($query_materias_inscritas);
$stmt_materias->bind_param("i", $user_id);
$stmt_materias->execute();
$result_materias = $stmt_materias->get_result();

// Consulta para obtener el horario completo del estudiante
$query_horario = "SELECT hm.dia, hm.hora_inicio, hm.hora_fin, m.nombre AS materia, m.salon, m.id,
                         COALESCE(p.nombre, 'Profesor no asignado') AS profesor
                  FROM horarios h
                  JOIN horariosmateria hm ON h.id_materia = hm.id_materia
                  JOIN materias m ON h.id_materia = m.id
                  LEFT JOIN profesores p ON m.id_profesor = p.id
                  WHERE h.id_estudiante = ?
                  ORDER BY hm.dia, hm.hora_inicio";
$stmt_horario = $conn->prepare($query_horario);
$stmt_horario->bind_param("i", $user_id);
$stmt_horario->execute();
$result_horario = $stmt_horario->get_result();

// Procesar los datos del horario para pasarlos a horario.php
$datos_horario = [];
if ($result_horario->num_rows > 0) {
    while ($row = $result_horario->fetch_assoc()) {
        $hora_inicio = strtotime($row['hora_inicio']);
        $hora_fin = strtotime($row['hora_fin']);
        $intervalo = 45 * 60; // 45 minutos
        
        for ($hora = $hora_inicio; $hora < $hora_fin; $hora += $intervalo) {
            $hora_formateada = date("H:i:s", $hora);
            $datos_horario[$row['dia']][$hora_formateada] = [
                "materia" => $row['materia'],
                "salon" => $row['salon'],
                "profesor" => $row['profesor'] ?: "Profesor no asignado",
                "inicio" => ($hora == $hora_inicio),
                "rowspan" => ceil(($hora_fin - $hora_inicio) / $intervalo)
            ];
        }
    }
}
$stmt_horario->close();


$query_materias_profesor = "
SELECT 
    m.id AS id_materia,
    m.nombre AS materia,
    m.salon,
    m.creditos,
    m.semestre,
    m.seccion,
    fu.foto,
    COALESCE(p.nombre, 'No asignado') AS nombre_profesor
FROM inscripciones i
JOIN materias m ON i.id_materia = m.id
LEFT JOIN profesores p ON m.id_profesor = p.id
LEFT JOIN fotousuario fu ON p.id_usuario = fu.id_usuario
WHERE i.id_estudiante = ?
GROUP BY m.id
";
$stmt_materias_profesor = $conn->prepare($query_materias_profesor);
$stmt_materias_profesor->bind_param("i", $user_id);
$stmt_materias_profesor->execute();
$resultado = $stmt_materias_profesor->get_result();
$stmt_materias_profesor->close();
$stmt_materias->close();




actualizar_actividad();
$conn->close();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="icon" href="css/icono.png" type="image/png"> -->
    <link rel="icon" href="css/logounihubblanco.png" type="image/png">
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

        /* Estilos específicos para la tabla del horario */
        .horario-tabla {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            font-size: 12px;
            table-layout: fixed;
        }

        .horario-tabla th,
        .horario-tabla td {
            padding: 6px 2px;
            min-width: 60px;
            max-width: 120px;
            word-wrap: break-word;
            overflow: hidden;
        }

        .horario-celda {
            font-size: 10px;
            line-height: 1.1;
            word-break: break-word;
        }

        /* Contenedor para el horario con scroll horizontal si es necesario */
        .div-horario {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin: 10px 0;
            background: white;
        }

        /* Forzar que la tabla no exceda el ancho del contenedor */
        .div-horario table {
            min-width: 600px;
            max-width: 100%;
        }

        /* Ajustes específicos para el contenedor principal */
        .divprincipal {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }

        .contenedor-horario {
            width: 100%;
            max-width: 100%;
            margin-bottom: 30px;
            box-sizing: border-box;
        }

        /* Layout de dos columnas para horario y materias */
        .layout-dos-columnas {
            display: flex;
            gap: 30px;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .columna-izquierda {
            flex: 2;
            min-width: 0; /* Permite que se encoja */
        }

        .columna-derecha {
            flex: 1;
            min-width: 0; /* Permite que se encoja */
        }

        .contenedor-materias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            width: 100%;
            max-width: 100%;
        }

        /* Responsive design */
        @media (max-width: 1024px) {
            .layout-dos-columnas {
                flex-direction: column;
                gap: 20px;
            }
            
            .columna-izquierda,
            .columna-derecha {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .divprincipal {
                padding: 10px;
            }
            
            .contenedor-horario {
                margin-bottom: 20px;
            }
            
            .contenedor-materias-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 10px;
            }

            .horario-tabla th,
            .horario-tabla td {
                padding: 4px 1px;
                font-size: 10px;
            }

            .horario-celda {
                font-size: 9px;
            }
        }

        /* Estilos específicos para el menú */
        .menu {
            position: fixed !important;
            top: 80px !important;
            left: 20px !important;
            z-index: 1000 !important;
            transform: translateX(-120%) !important;
            transition: transform 0.5s ease-in-out !important;
        }

        .menu.toggle {
            transform: translateX(0) !important;
        }

        .menuopciones {
            max-width: 300px !important;
        }

        .opción {
            min-width: 80px !important;
            margin: 5px !important;
        }

        /* Asegurar que el contenido principal no se superponga con el menú */
        .divprincipal {
            margin-left: 0 !important;
            padding-left: 20px !important;
        }

        /* Responsive para el menú */
        @media (max-width: 768px) {
            .menu {
                left: 10px !important;
                top: 70px !important;
            }
            
            .menuopciones {
                max-width: 250px !important;
            }
            
            .opción {
                min-width: 70px !important;
            }
        }

        /* Mejoras finales para la organización de la página */
        .titulo-horario {
            font-size: 22px !important;
            font-weight: 600 !important;
            color: #174388 !important;
            margin-bottom: 15px !important;
            text-align: center !important;
            border-bottom: 2px solid #ffd700 !important;
            padding-bottom: 10px !important;
        }

        .contenedor-horario {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            border: 2px solid #174388 !important;
            border-radius: 15px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
            padding: 25px !important;
            margin-bottom: 30px !important;
        }

        .tabla-horario {
            border-radius: 10px !important;
            overflow: hidden !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        }

        .tabla-horario th {
            background: linear-gradient(135deg, #174388 0%, #1e5aa8 100%) !important;
            color: white !important;
            font-weight: 600 !important;
            padding: 12px 8px !important;
        }

        .tabla-horario td {
            padding: 10px 8px !important;
            border-bottom: 1px solid #dee2e6 !important;
        }

        .nohayclases {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            color: white !important;
            font-weight: 600 !important;
            padding: 20px !important;
            border-radius: 10px !important;
            text-align: center !important;
            font-size: 18px !important;
        }

        /* Mejoras para las tarjetas de materias */
        .tarjeta-materia-link {
            text-decoration: none !important;
            color: inherit !important;
            display: block !important;
        }

        .tarjeta-materia {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            border: 2px solid #174388 !important;
            border-radius: 15px !important;
            padding: 20px 15px !important;
            box-shadow: 0 4px 15px rgba(23, 67, 136, 0.1) !important;
            transition: all 0.4s ease !important;
            position: relative !important;
            overflow: hidden !important;
            text-align: center !important;
            cursor: pointer !important;
        }

        .tarjeta-materia::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: 4px !important;
            background: linear-gradient(90deg, #174388 0%, #ffd700 50%, #174388 100%) !important;
        }

        .tarjeta-materia:hover {
            transform: translateY(-8px) scale(1.05) !important;
            box-shadow: 0 15px 35px rgba(23, 67, 136, 0.25) !important;
            border-color: #ffd700 !important;
        }

        .icono-materia {
            font-size: 36px !important;
            margin-bottom: 12px !important;
            display: block !important;
            transition: transform 0.3s ease !important;
        }

        .tarjeta-materia:hover .icono-materia {
            transform: scale(1.1) rotate(5deg) !important;
        }

        .nombre-materia {
            color: #174388 !important;
            font-weight: 700 !important;
            font-size: 16px !important;
            margin-bottom: 15px !important;
            text-align: center !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            border-bottom: 2px solid #ffd700 !important;
            padding-bottom: 8px !important;
        }

        .info-materia {
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
            align-items: center !important;
        }

        .info-item {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            padding: 6px 12px !important;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            border-radius: 20px !important;
            border: 1px solid #dee2e6 !important;
            transition: all 0.3s ease !important;
            width: 100% !important;
            max-width: 160px !important;
        }

        .icono {
            font-size: 16px !important;
            min-width: 24px !important;
            text-align: center !important;
        }

        .texto {
            color: #333 !important;
            font-weight: 500 !important;
            font-size: 12px !important;
        }

        .profesor-img {
            border: 3px solid #174388 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        /* Mejoras para el hover box */
        .hover-box {
            background: white !important;
            border: 2px solid #174388 !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2) !important;
        }

        .info-lista li {
            color: #174388 !important;
            font-weight: 500 !important;
            border-bottom: 1px solid #e9ecef !important;
            padding: 5px 0 !important;
        }

        .info-lista li:last-child {
            border-bottom: none !important;
        }

        /* Mejoras para el header */
        .cabecera {
            background: linear-gradient(135deg, #174388 0%, #1e5aa8 100%) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2) !important;
        }

        /* Espaciado general mejorado */
        .divprincipal {
            padding: 30px 20px !important;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            min-height: calc(100vh - 140px) !important;
        }

        /* Mejoras para el layout de dos columnas */
        .layout-dos-columnas {
            gap: 40px !important;
            margin-top: 20px !important;
        }

        /* Responsive final */
        @media (max-width: 1200px) {
            .layout-dos-columnas {
                gap: 30px !important;
            }
            
            .contenedor-horario {
                padding: 20px !important;
            }
        }

        @media (max-width: 768px) {
            .divprincipal {
                padding: 20px 15px !important;
            }
            
            .contenedor-horario {
                padding: 15px !important;
                margin-bottom: 20px !important;
            }
            
            .titulo-horario {
                font-size: 20px !important;
            }
            
            .layout-dos-columnas {
                gap: 20px !important;
            }
        }

        /* Estilos para modo oscuro */
        body.dark-mode {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
            color: #ffffff !important;
        }

        body.dark-mode .divprincipal {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
        }

        body.dark-mode .contenedor-horario {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
            border-color: #4a5568 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
        }

        body.dark-mode .titulo-horario {
            color: #ffffff !important;
            border-bottom-color: #ffd700 !important;
        }

        body.dark-mode .tabla-horario th {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
            color: #ffffff !important;
        }

        body.dark-mode .tabla-horario td {
            background-color: #2d3748 !important;
            color: #ffffff !important;
            border-bottom-color: #4a5568 !important;
        }

        body.dark-mode .nohayclases {
            background: linear-gradient(135deg, #38a169 0%, #48bb78 100%) !important;
            color: #ffffff !important;
        }

        body.dark-mode .tarjeta-materia {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
            border-color: #4a5568 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
        }

        body.dark-mode .tarjeta-materia:hover {
            border-color: #ffd700 !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4) !important;
        }

        body.dark-mode .nombre-materia {
            color: #ffffff !important;
            border-bottom-color: #ffd700 !important;
        }

        body.dark-mode .info-item {
            background: linear-gradient(135deg, #4a5568 0%, #718096 100%) !important;
            border-color: #718096 !important;
        }

        body.dark-mode .texto {
            color: #ffffff !important;
        }

        body.dark-mode .soporte-flotante {
            background-color: #4a5568 !important;
        }

        body.dark-mode .soporte-flotante:hover {
            background-color: #2d3748 !important;
        }
    </style>
</head>

<body>
    <div class="contenedorentrante3">
        <img src="css\logo.png">
    </div>
    <div class="cabecera">

        <button type="button" id="logoButton">
          <!-- <img src="css/logoazul.png" alt="Logo">-->
            <img src="css/menu.png" alt="Menú" class="logo-menu">


        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_alumno.php'; ?>

        
        <!-- Layout de dos columnas: Horario y Materias -->
        <div class="layout-dos-columnas">
            <!-- Columna izquierda: Horario semanal -->
            <div class="columna-izquierda">
                <div class="contenedor-horario">
                    <h2 class="titulo-horario">Horario Semanal Completo</h2>
                    <?php 
                    define('INCLUDED_FROM_MAIN', true);
                    include 'horario.php'; 
                    ?>
                </div>
            </div>
            
            <!-- Columna derecha: Materias inscritas -->
            <div class="columna-derecha">
                <div class="contenedor-horario">
                    <h2 class="titulo-horario">Materias Inscritas</h2>
                    <div class="contenedor-materias-grid">
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                            <a href="detalle_materia.php?id=<?php echo $row['id_materia']; ?>" class="tarjeta-materia-link">
                                <div class="tarjeta-materia">
                                    <div class="icono-materia">📚</div>
                                    <h3 class="nombre-materia"><?php echo htmlspecialchars($row['materia']); ?></h3>
                                    <div class="info-materia">
                                        <div class="info-item">
                                            <span class="icono">👨‍🏫</span>
                                            <span class="texto"><?php echo !empty(trim($row['nombre_profesor'])) ? htmlspecialchars(trim($row['nombre_profesor'])) : 'No asignado'; ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="icono">🏢</span>
                                            <span class="texto">Salón <?php echo htmlspecialchars($row['salon']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
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