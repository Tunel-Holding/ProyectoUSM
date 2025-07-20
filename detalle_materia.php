<?php
include 'comprobar_sesion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);

actualizar_actividad();
include 'conexion.php';
$conn->set_charset("utf8mb4");

// Obtener el ID de la materia desde la URL
$id_materia = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['idusuario'];

if ($id_materia <= 0) {
    header("Location: pagina_principal.php");
    exit();
}

// Verificar que el estudiante esté inscrito en esta materia
$sql_check = "SELECT 1 FROM inscripciones WHERE id_estudiante = ? AND id_materia = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $id_materia);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    header("Location: pagina_principal.php");
    exit();
}
$stmt_check->close();

// Obtener información detallada de la materia
$query_materia = "
SELECT 
    m.id,
    m.nombre AS materia,
    m.salon,
    m.creditos,
    m.semestre,
    m.seccion,
    p.nombre AS nombre_profesor,
    p.cedula AS cedula_profesor,
    fu.foto AS foto_profesor,
    du.correo
FROM materias m
LEFT JOIN profesores p ON m.id_profesor = p.id
LEFT JOIN fotousuario fu ON p.id_usuario = fu.id_usuario
LEFT JOIN datos_usuario du ON p.id_usuario = du.usuario_id
WHERE m.id = ?
";

$stmt_materia = $conn->prepare($query_materia);
$stmt_materia->bind_param("i", $id_materia);
$stmt_materia->execute();
$resultado_materia = $stmt_materia->get_result();
$materia = $resultado_materia->fetch_assoc();
$stmt_materia->close();

// Obtener horarios de la materia
$query_horarios = "
SELECT dia, hora_inicio, hora_fin
FROM horariosmateria
WHERE id_materia = ?
ORDER BY 
    CASE dia
        WHEN 'Lunes' THEN 1
        WHEN 'Martes' THEN 2
        WHEN 'Miércoles' THEN 3
        WHEN 'Jueves' THEN 4
        WHEN 'Viernes' THEN 5
        ELSE 6
    END,
    hora_inicio
";

$stmt_horarios = $conn->prepare($query_horarios);
$stmt_horarios->bind_param("i", $id_materia);
$stmt_horarios->execute();
$resultado_horarios = $stmt_horarios->get_result();
$stmt_horarios->close();

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Detalle de Materia - UniHub</title>
    <script src="js/control_inactividad.js"></script>
    <style>
        /* Aplicar fuentes globalmente */
        * {
            font-family: 'Poppins', 'Afacad Flux', 'Noto Sans KR', 'Raleway', sans-serif;
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
        }

        .detalle-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: calc(100vh - 140px);
            font-family: 'Poppins', sans-serif;
        }

        .header-materia {
            background: linear-gradient(135deg, #174388 0%, #1e5aa8 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(23, 67, 136, 0.3);
        }

        .header-materia h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            letter-spacing: -0.5px;
        }

        .header-materia .codigo {
            font-size: 1.2em;
            opacity: 0.9;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            border: 2px solid #174388;
        }

        .info-card h3 {
            color: #174388;
            font-size: 1.3em;
            margin-bottom: 20px;
            border-bottom: 2px solid #ffd700;
            padding-bottom: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #174388;
        }

        .info-item .icono {
            font-size: 20px;
            margin-right: 15px;
            min-width: 30px;
        }

        .info-item .label {
            font-weight: 600;
            color: #174388;
            margin-right: 10px;
            font-family: 'Poppins', sans-serif;
        }

        .info-item .value {
            color: #333;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
        }

        .horarios-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .horarios-table th,
        .horarios-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .horarios-table th {
            background: linear-gradient(135deg, #174388 0%, #1e5aa8 100%);
            color: white;
            font-weight: 600;
        }

        .horarios-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .btn-volver {
            display: inline-block;
            background: linear-gradient(135deg, #174388 0%, #1e5aa8 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(23, 67, 136, 0.3);
        }

        .btn-volver:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(23, 67, 136, 0.4);
        }

        .profesor-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .profesor-foto {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #174388;
        }

        .profesor-datos h4 {
            color: #174388;
            margin-bottom: 5px;
        }

        .profesor-datos p {
            color: #666;
            margin: 3px 0;
        }

        @media (max-width: 768px) {
            .detalle-container {
                padding: 20px 15px;
            }
            
            .header-materia h1 {
                font-size: 2em;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        /* Estilos para el soporte flotante */
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
            width: 60px;
            height: 50px;
            transition: width 0.4s ease, background-color 0.3s ease;
        }

        .soporte-flotante:hover {
            width: 210px;
            background-color: #365ac0;
        }

        .soporte-mensaje {
            flex: 1;
            opacity: 0;
            white-space: nowrap;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            transform: translateX(30px);
            transition: transform 0.4s ease, opacity 0.4s ease;
            text-align: left;
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

        /* Estilos para modo oscuro */
        body.dark-mode {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ffffff;
        }

        body.dark-mode .detalle-container {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        body.dark-mode .header-materia {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
        }

        body.dark-mode .info-card {
            background: #2d3748;
            border-color: #4a5568;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .info-card h3 {
            color: #ffffff;
            border-bottom-color: #ffd700;
        }

        body.dark-mode .info-item {
            background: #4a5568;
            border-left-color: #ffd700;
        }

        body.dark-mode .info-item .label {
            color: #ffd700;
        }

        body.dark-mode .info-item .value {
            color: #ffffff;
        }

        body.dark-mode .horarios-table th {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            color: #ffffff;
        }

        body.dark-mode .horarios-table td {
            background-color: #2d3748;
            color: #ffffff;
            border-color: #4a5568;
        }

        body.dark-mode .horarios-table tr:nth-child(even) {
            background-color: #4a5568;
        }

        body.dark-mode .btn-volver {
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .btn-volver:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        body.dark-mode .profesor-datos h4 {
            color: #ffffff;
        }

        body.dark-mode .profesor-datos p {
            color: #a0aec0;
        }

        body.dark-mode .soporte-flotante {
            background-color: #4a5568;
        }

        body.dark-mode .soporte-flotante:hover {
            background-color: #2d3748;
        }
    </style>
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

    <div class="detalle-container">
        <div class="header-materia">
            <h1><?php echo htmlspecialchars($materia['materia']); ?></h1>
            <div class="codigo">Sección <?php echo htmlspecialchars($materia['seccion']); ?></div>
        </div>

        <div class="info-grid">
            
            <!-- Información del Profesor -->
            <div class="info-card">
                <h3>👨‍🏫 Profesor</h3>
                <?php if (!empty($materia['nombre_profesor'])): ?>
                    <div class="profesor-info">
                        <img src="<?php echo $materia['foto_profesor'] ?: 'https://cdn-icons-png.flaticon.com/512/6073/6073873.png'; ?>" 
                             alt="Foto del profesor" class="profesor-foto">
                        <div class="profesor-datos">
                            <h4><?php echo htmlspecialchars($materia['nombre_profesor']); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="info-item">
                        <span class="icono">❌</span>
                        <span class="value">Profesor no asignado</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Horarios -->
            <div class="info-card">
                <h3>🕒 Horarios</h3>
                <?php if ($resultado_horarios->num_rows > 0): ?>
                    <table class="horarios-table">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Hora Inicio</th>
                                <th>Hora Fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($horario = $resultado_horarios->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($horario['dia']); ?></td>
                                    <td><?php echo date('H:i', strtotime($horario['hora_inicio'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($horario['hora_fin'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="info-item">
                        <span class="icono">⚠️</span>
                        <span class="value">Horarios no disponibles</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="javascript:history.back()" class="btn-volver">← Volver</a>
        </div>
    </div>

    <div class="soporte-flotante-container">
        <a href="contacto.php" class="soporte-flotante" title="Soporte">
            <span class="soporte-mensaje">Contacto soporte</span>
            <img src="css/audifonos-blanco.png" alt="Soporte">
        </a>
    </div>
</body>
</html> 