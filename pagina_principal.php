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
    <title>Inicio - UniHub</title>
    <script src="js/control_inactividad.js"></script>
    <style>
        :root {
            /* --- Variables de Colores Base (Modo Claro) --- */
            --color-primary-blue: #174388; /* Azul principal (ej: para bordes, textos importantes) */
            --color-secondary-blue: #1e5aa8; /* Azul secundario (ej: para degradados) */
            --color-accent-gold: #ffd700; /* Dorado para acentos */
            --color-text-dark: #333; /* Color de texto oscuro general */
            --color-text-light: #fff; /* Color de texto claro (para fondos oscuros) */
            --color-border-light: #ddd; /* Borde ligero */
            --color-bg-light-1: #f9f9f9; /* Fondo muy claro (ej: tarjetas base) */
            --color-bg-light-2: #f8f9fa; /* Fondo claro general */
            --color-bg-light-3: #e9ecef; /* Fondo ligeramente más oscuro */
            --color-success-green: #28a745; /* Verde para mensajes de éxito */
            --color-success-green-dark: #20c997; /* Verde más oscuro para degradado de éxito */

            /* --- Variables de Degradados (Modo Claro) --- */
            --gradient-main-bg: linear-gradient(135deg, var(--color-bg-light-2) 0%, var(--color-bg-light-3) 100%);
            --gradient-header-bg: linear-gradient(135deg, var(--color-primary-blue) 0%, var(--color-secondary-blue) 100%);
            --gradient-card-bg: linear-gradient(135deg, #ffffff 0%, var(--color-bg-light-2) 100%);
            --gradient-info-item-bg: linear-gradient(135deg, var(--color-bg-light-2) 0%, var(--color-bg-light-3) 100%);
            --gradient-support-button: #446ad3; /* Color sólido para el botón flotante */
            --gradient-support-button-hover: #365ac0; /* Color sólido para el hover del botón flotante */
            --gradient-no-classes: linear-gradient(135deg, var(--color-success-green) 0%, var(--color-success-green-dark) 100%);

            /* --- Variables de Sombras --- */
            --shadow-base: 0 4px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 10px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 4px 15px rgba(23, 67, 136, 0.1);
            --shadow-card-hover: 0 15px 35px rgba(23, 67, 136, 0.25);
            --shadow-header: 0 4px 15px rgba(0, 0, 0, 0.2);
            --shadow-table: 0 2px 8px rgba(0, 0, 0, 0.1);
            --shadow-hover-box: 0 8px 25px rgba(0, 0, 0, 0.2);
            --shadow-profesor-img: 0 4px 12px rgba(0, 0, 0, 0.15);

            /* --- Variables de Bordes --- */
            --border-main: 2px solid var(--color-primary-blue);
            --border-light: 1px solid var(--color-border-light);
            --border-light-2: 1px solid #dee2e6;
            --border-accent: 2px solid var(--color-accent-gold);

            /* --- Otras Variables --- */
            --border-radius-base: 10px;
            --border-radius-lg: 15px;
            --border-radius-pill: 50px;
        }

        /* --- Modo Oscuro: Sobrescribe las variables base --- */
        body.dark-mode {
            --color-primary-blue: #2d3748; /* Fondo oscuro para elementos principales */
            --color-secondary-blue: #4a5568; /* Fondo más oscuro para elementos */
            --color-text-dark: #ffffff; /* Texto blanco para modo oscuro */
            --color-border-light: #4a5568; /* Borde más oscuro */
            --color-bg-light-1: #1a1a2e;
            --color-bg-light-2: #1a1a2e; /* Fondo general oscuro */
            --color-bg-light-3: #16213e; /* Fondo ligeramente más oscuro */
            --color-success-green: #38a169;
            --color-success-green-dark: #48bb78;

            --gradient-main-bg: linear-gradient(135deg, var(--color-bg-light-2) 0%, var(--color-bg-light-3) 100%);
            --gradient-header-bg: linear-gradient(135deg, var(--color-primary-blue) 0%, var(--color-secondary-blue) 100%);
            --gradient-card-bg: linear-gradient(135deg, var(--color-primary-blue) 0%, var(--color-secondary-blue) 100%);
            --gradient-info-item-bg: linear-gradient(135deg, var(--color-secondary-blue) 0%, #718096 100%);
            --gradient-support-button: #4a5568;
            --gradient-support-button-hover: #2d3748;
            --gradient-no-classes: linear-gradient(135deg, var(--color-success-green) 0%, var(--color-success-green-dark) 100%);

            --shadow-md: 0 4px 10px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 4px 15px rgba(0, 0, 0, 0.3);
            --shadow-xl: 0 4px 15px rgba(0, 0, 0, 0.3);
            --shadow-card-hover: 0 15px 35px rgba(0, 0, 0, 0.4);
            --shadow-header: 0 4px 15px rgba(0, 0, 0, 0.3);
            --shadow-table: 0 2px 8px rgba(0, 0, 0, 0.2);
            --shadow-hover-box: 0 8px 25px rgba(0, 0, 0, 0.3);
            --shadow-profesor-img: 0 4px 12px rgba(0, 0, 0, 0.25);


            background: var(--gradient-main-bg); /* Aplica el fondo del modo oscuro al body */
            color: var(--color-text-light); /* Color de texto general para dark mode */
        }

        /* --- Estilos Generales y Layout --- */
        .divprincipal {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
            background: var(--gradient-main-bg); /* Usando variable */
            min-height: calc(100vh - 140px); /* Ajustado, sin !important */
        }

        .layout-dos-columnas {
            display: flex;
            gap: 40px; /* Usando variable, sin !important */
            width: 90%;
            max-width: 1400px;
            margin: 20px auto; /* Ajustado el margin-top, sin !important */
            align-items: stretch; /* Clave para que las columnas se estiren a la misma altura */
        }

        .columna-izquierda,
        .columna-derecha {
            display: flex;
            flex-direction: column;
            flex: 1; /* Ocupa el mismo espacio disponible */
            min-width: 0; /* Permite que las columnas se encojan */
            height: auto;
        }

        /* --- Contenedor de Horario --- */
        .contenedor-horario {
            flex: 1; /* Ocupa todo el espacio disponible dentro de su columna */
            width: 100%; /* Asegura que ocupe todo el ancho de su columna */
            box-sizing: border-box;
            background: var(--gradient-main-bg); /* Usando variable, sin !important */
            border: var(--border-main); /* Usando variable, sin !important */
            border-radius: var(--border-radius-lg); /* Usando variable, sin !important */
            box-shadow: var(--shadow-lg); /* Usando variable, sin !important */
            padding: 25px;
            margin-bottom: 0; /* Quita el margin-bottom aquí, el gap del layout se encarga */
        }

        /* Modo oscuro para contenedor-horario */
        body.dark-mode .contenedor-horario {
            background: var(--gradient-card-bg); /* Usa la variable de dark-mode */
            border-color: var(--color-border-light);
            box-shadow: var(--shadow-dark);
        }

        .titulo-horario {
            font-size: 22px;
            font-weight: 600;
            color: var(--color-primary-blue); /* Usando variable, sin !important */
            margin-bottom: 15px;
            text-align: center;
            border-bottom: var(--border-accent); /* Usando variable, sin !important */
            padding-bottom: 10px;
        }

        /* Modo oscuro para titulo-horario */
        body.dark-mode .titulo-horario {
            color: var(--color-text-light);
            border-bottom-color: var(--color-accent-gold);
        }

        /* Tabla de Horario */
        .div-horario {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            border-radius: var(--border-radius-base);
            border: var(--border-light);
            margin: 10px 0;
            background: white; /* Por defecto */
        }

        /* Modo oscuro para div-horario */
        body.dark-mode .div-horario {
            background: var(--color-primary-blue); /* Ajusta si quieres otro color de fondo para la tabla en dark mode */
            border-color: var(--color-border-light);
        }


        .horario-tabla {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            font-size: 12px;
            table-layout: fixed;
            border-collapse: collapse; /* Añadido para un mejor aspecto */
        }

        .div-horario table {
            min-width: 600px;
            max-width: 100%;
        }

        .horario-tabla th,
        .horario-tabla td {
            padding: 6px 2px;
            min-width: 60px;
            max-width: 120px;
            word-wrap: break-word;
            overflow: hidden;
            text-align: center; /* Centrar contenido en las celdas */
            vertical-align: middle; /* Alinear verticalmente */
        }

        .tabla-horario th { /* Selector más específico si es necesario, pero .horario-tabla th ya es bueno */
            background: var(--gradient-header-bg); /* Usando variable, sin !important */
            color: var(--color-text-light); /* Usando variable, sin !important */
            font-weight: 600;
            padding: 12px 8px;
        }

        /* Modo oscuro para tabla-horario th */
        body.dark-mode .tabla-horario th {
            background: var(--gradient-header-bg);
            color: var(--color-text-light);
        }

        .tabla-horario td { /* Selector más específico si es necesario */
            padding: 10px 8px;
            border-bottom: var(--border-light-2); /* Usando variable, sin !important */
            background-color: white; /* Fondo de celdas por defecto */
            color: var(--color-text-dark); /* Color de texto por defecto */
        }

        /* Modo oscuro para tabla-horario td */
        body.dark-mode .tabla-horario td {
            background-color: var(--color-primary-blue); /* Ajusta el fondo de la celda */
            color: var(--color-text-light);
            border-bottom-color: var(--color-border-light);
        }


        .horario-celda {
            font-size: 10px;
            line-height: 1.1;
            word-break: break-word;
        }

        .nohayclases {
            background: var(--gradient-no-classes); /* Usando variable, sin !important */
            color: var(--color-text-light); /* Usando variable, sin !important */
            font-weight: 600;
            padding: 20px;
            border-radius: var(--border-radius-base);
            text-align: center;
            font-size: 18px;
        }

        /* --- Contenedor de Materias (Grid) --- */
        .contenedor-materias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Minmax ajustado a 150px */
            gap: 15px; /* Gap ajustado */
            padding: 30px; /* Padding ajustado */
            width: 100%;
            max-width: 100%;
        }

        /* --- Tarjetas de Materia --- */
        .tarjeta-materia-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .tarjeta-materia {
            background: var(--gradient-card-bg); /* Usando variable, sin !important */
            border: var(--border-main); /* Usando variable, sin !important */
            border-radius: var(--border-radius-lg); /* Usando variable, sin !important */
            padding: 20px 15px;
            box-shadow: var(--shadow-xl); /* Usando variable, sin !important */
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            text-align: center;
            cursor: pointer;
        }

        /* Estilo para la línea superior de la tarjeta */
        .tarjeta-materia::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary-blue) 0%, var(--color-accent-gold) 50%, var(--color-primary-blue) 100%); /* Sin !important */
        }

        .tarjeta-materia:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: var(--shadow-card-hover); /* Usando variable, sin !important */
            border-color: var(--color-accent-gold); /* Usando variable, sin !important */
        }

        /* Modo oscuro para tarjeta-materia */
        body.dark-mode .tarjeta-materia {
            background: var(--gradient-card-bg);
            border-color: var(--color-border-light);
            box-shadow: var(--shadow-lg); /* Ajustado para dark mode */
        }

        body.dark-mode .tarjeta-materia:hover {
            border-color: var(--color-accent-gold);
            box-shadow: var(--shadow-card-hover); /* Ajustado para dark mode */
        }

        .icono-materia {
            font-size: 36px;
            margin-bottom: 12px;
            display: block;
            transition: transform 0.3s ease;
        }

        .tarjeta-materia:hover .icono-materia {
            transform: scale(1.1) rotate(5deg);
        }

        .nombre-materia {
            color: var(--color-primary-blue); /* Usando variable, sin !important */
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: var(--border-accent); /* Usando variable, sin !important */
            padding-bottom: 8px;
        }

        /* Modo oscuro para nombre-materia */
        body.dark-mode .nombre-materia {
            color: var(--color-text-light);
            border-bottom-color: var(--color-accent-gold);
        }


        .info-materia {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: center;
        }

        .info-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 12px;
            background: var(--gradient-info-item-bg); /* Usando variable, sin !important */
            border-radius: 20px;
            border: var(--border-light-2); /* Usando variable, sin !important */
            transition: all 0.3s ease;
            width: 100%;
            max-width: 160px;
        }

        /* Modo oscuro para info-item */
        body.dark-mode .info-item {
            background: var(--gradient-info-item-bg);
            border-color: var(--color-border-light);
        }

        .icono {
            font-size: 16px;
            min-width: 24px;
            text-align: center;
        }

        .texto {
            color: var(--color-text-dark); /* Usando variable, sin !important */
            font-weight: 500;
            font-size: 12px;
        }

        /* Modo oscuro para texto */
        body.dark-mode .texto {
            color: var(--color-text-light);
        }

        /* --- Imágenes de Profesor y Hover Box --- */
        .profesor-img {
            width: 89px;
            height: 89px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto;
            transition: transform 0.3s ease;
            cursor: pointer;
            border: 3px solid var(--color-primary-blue); /* Usando variable, sin !important */
            box-shadow: var(--shadow-profesor-img); /* Usando variable, sin !important */
        }

        .profesor-img:hover {
            transform: scale(1.05);
        }

        /* Modo oscuro para profesor-img (el borde cambiará con la variable principal) */


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
            background: white; /* Usando variable, sin !important */
            border: 2px solid var(--color-primary-blue); /* Usando variable, sin !important */
            padding: 10px;
            width: 240px;
            box-shadow: var(--shadow-hover-box); /* Usando variable, sin !important */
            border-radius: var(--border-radius-base); /* Usando variable, sin !important */
            transition: max-height 0.5s ease, opacity 0.5s ease;
            z-index: 100;
        }

        /* Modo oscuro para hover-box */
        body.dark-mode .hover-box {
            background: var(--color-primary-blue); /* Fondo oscuro para el hover box */
            border-color: var(--color-border-light);
            box-shadow: var(--shadow-hover-box); /* Ajustado para dark mode */
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
            color: var(--color-primary-blue); /* Usando variable, sin !important */
            font-weight: 500;
            border-bottom: var(--border-light-2); /* Usando variable, sin !important */
            padding: 5px 0;
        }

        /* Modo oscuro para info-lista li */
        body.dark-mode .info-lista li {
            color: var(--color-text-light);
            border-bottom-color: var(--color-border-light);
        }

        .info-lista li:last-child {
            border-bottom: none;
        }

        /* --- Botón de Soporte Flotante --- */
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
            background-color: var(--gradient-support-button); /* Usando variable, sin !important */
            padding: 12px 16px;
            border-radius: var(--border-radius-pill); /* Usando variable, sin !important */
            box-shadow: var(--shadow-md); /* Usando variable, sin !important */
            text-decoration: none;
            overflow: hidden;
            width: 60px; /* Suficiente para mostrar solo el ícono */
            height: 50px;
            transition: width 0.4s ease, background-color 0.3s ease;
        }

        .soporte-flotante:hover {
            width: 210px; /* Se expande hacia la izquierda */
            background-color: var(--gradient-support-button-hover); /* Usando variable, sin !important */
        }

        /* Modo oscuro para soporte-flotante */
        body.dark-mode .soporte-flotante {
            background-color: var(--gradient-support-button);
        }

        body.dark-mode .soporte-flotante:hover {
            background-color: var(--gradient-support-button-hover);
        }


        .soporte-mensaje {
            flex: 1; /* Ocupa todo el espacio disponible */
            opacity: 0;
            white-space: nowrap;
            color: var(--color-text-light); /* Usando variable, sin !important */
            font-weight: 500;
            font-size: 14px;
            transform: translateX(30px); /* animación desde la derecha */
            transition: transform 0.4s ease, opacity 0.4s ease;
            text-align: left; /* Texto alineado a la izquierda */
            margin-right: auto;
        }

        .soporte-flotante:hover .soporte-mensaje {
            opacity: 1;
            transform: translateX(0);
        }

        .soporte-flotante img {
            width: 30px;
            height: 30px;
            filter: brightness(0) invert(1); /* Para iconos blancos */
            flex-shrink: 0;
            z-index: 2;
        }


        /* --- Header --- */
        .cabecera {
            background: var(--gradient-header-bg); /* Usando variable, sin !important */
            box-shadow: var(--shadow-header); /* Usando variable, sin !important */
        }

        /* --- Menu (mantengo !important aquí ya que son estilos de posicionamiento/animación) --- */
        /* A menudo, para menus "overlay" o con JS que manipula `transform`, `!important` puede ser útil
        para asegurar que los estilos de JS no entren en conflicto con otros CSS.
        Si ves que el menú no funciona sin ellos, puedes reintroducirlos selectivamente. */
        .menu {
            position: fixed; /* Mantuve fixed por ser un menú flotante */
            top: 80px;
            left: 20px;
            z-index: 1000;
            transform: translateX(-120%);
            transition: transform 0.5s ease-in-out;
        }

        .menu.toggle {
            transform: translateX(0);
        }

        .menuopciones {
            max-width: 300px;
        }

        .opción {
            min-width: 80px;
            margin: 5px;
        }

        /* Asegurar que el contenido principal no se superponga con el menú */
        .divprincipal {
            margin-left: 0;
            padding-left: 20px;
        }

        /* --- Media Queries --- */

        @media (max-width: 1200px) {
            .layout-dos-columnas {
                gap: 30px; /* Sin !important */
            }

            .contenedor-horario {
                padding: 20px; /* Sin !important */
            }
        }

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
                padding: 20px 15px; /* Sin !important */
                min-height: calc(100vh - 120px); /* Ajustado el valor */
            }

            .contenedor-horario {
                padding: 15px; /* Sin !important */
                margin-bottom: 20px; /* Sin !important */
            }

            .titulo-horario {
                font-size: 20px; /* Sin !important */
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

            /* Responsive para el menú */
            .menu {
                left: 10px; /* Sin !important */
                top: 70px; /* Sin !important */
            }

            .menuopciones {
                max-width: 250px; /* Sin !important */
            }

            .opción {
                min-width: 70px; /* Sin !important */
            }

            .layout-dos-columnas {
                gap: 20px; /* Sin !important */
            }
        }
    </style>
</head>

<body>
    <div class="contenedorentrante3">
        <img src="css/logounihubblanco.png">
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