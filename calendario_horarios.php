<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);

require 'conexion.php';

// Sanitizar y validar el ID de la materia
$materia_id = isset($_GET['materia_id']) ? filter_var($_GET['materia_id'], FILTER_VALIDATE_INT) : null;

if (!$materia_id) {
    header('Location: admin_materias.php');
    exit();
}

// Obtener información de la materia
$sql_materia = "SELECT m.*, p.nombre AS profesor_nombre 
                FROM materias m 
                LEFT JOIN profesores p ON m.id_profesor = p.id 
                WHERE m.id = ?";
$stmt_materia = $conn->prepare($sql_materia);
$stmt_materia->bind_param("i", $materia_id);
$stmt_materia->execute();
$result_materia = $stmt_materia->get_result();

if ($result_materia->num_rows === 0) {
    header('Location: admin_materias.php');
    exit();
}

$materia = $result_materia->fetch_assoc();

// Obtener horarios existentes de la materia
$sql_horarios = "SELECT * FROM horariosmateria WHERE id_materia = ? ORDER BY dia, hora_inicio";
$stmt_horarios = $conn->prepare($sql_horarios);
$stmt_horarios->bind_param("i", $materia_id);
$stmt_horarios->execute();
$result_horarios = $stmt_horarios->get_result();

$horarios_existentes = [];
while ($row = $result_horarios->fetch_assoc()) {
    $horarios_existentes[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/admin-general.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Calendario de Horarios - <?php echo htmlspecialchars($materia['nombre']); ?></title>
    <style>
        .main-content {
            margin-top: 100px;
            padding: 2rem;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content-section {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Estilos del calendario */
        .calendar-container {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .calendar-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: 100px repeat(5, 1fr);
            gap: 1px;
            background: var(--gray-200);
        }

        .time-slot {
            background: var(--white);
            padding: 0.5rem;
            text-align: center;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-600);
            border: none;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .day-header {
            background: var(--gray-100);
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            color: var(--gray-800);
            border: none;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .calendar-cell {
            background: var(--white);
            padding: 0.5rem;
            min-height: 60px;
            border: 1px solid var(--gray-100);
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .calendar-cell:hover {
            background: var(--gray-50);
            border-color: var(--primary-blue);
        }

        .calendar-cell.selected {
            background: rgba(97, 183, 255, 0.1);
            border-color: var(--primary-blue);
        }

        .calendar-cell.has-schedule {
            background: linear-gradient(135deg, var(--primary-yellow), #fbbf24);
            color: var(--gray-900);
            font-weight: 600;
            border: 2px solid var(--primary-blue);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }

        .calendar-cell.has-schedule::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: var(--primary-blue);
            border-radius: 50%;
            opacity: 0.8;
        }

        .schedule-info {
            font-size: 0.75rem;
            text-align: center;
            line-height: 1.2;
        }

        /* Panel de controles */
        .controls-panel {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
        }

        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.875rem;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2);
        }

        .btn-primary {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--gray-600);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: var(--gray-800);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: var(--white);
        }

        .btn-danger {
            background: #dc3545;
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Horarios existentes */
        .existing-schedules {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            border: 1px solid var(--gray-200);
        }

        .schedule-item {
            background: var(--white);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            border-left: 4px solid var(--primary-blue);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schedule-info-text {
            font-weight: 500;
            color: var(--gray-800);
        }

        .schedule-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 4px;
        }

        /* Dark mode adjustments */
        body.dark-mode .content-section {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        body.dark-mode .section-title {
            color: #f1f5f9;
        }

        body.dark-mode .calendar-container {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        body.dark-mode .time-slot {
            background: #1e293b;
            color: #cbd5e1;
        }

        body.dark-mode .day-header {
            background: #334155;
            color: #f1f5f9;
        }

        body.dark-mode .calendar-cell {
            background: #1e293b;
            border-color: #334155;
        }

        body.dark-mode .calendar-cell:hover {
            background: #334155;
            border-color: #61b7ff;
        }

        body.dark-mode .calendar-cell.selected {
            background: rgba(97, 183, 255, 0.2);
            border-color: #61b7ff;
        }

        body.dark-mode .calendar-cell.has-schedule {
            background: #f59e0b;
            color: #1e293b;
            border: 2px solid #61b7ff;
        }

        body.dark-mode .calendar-cell.has-schedule::after {
            background: #61b7ff;
        }

        body.dark-mode .controls-panel {
            background: #334155;
            border-color: #475569;
        }

        body.dark-mode .form-label {
            color: #f1f5f9;
        }

        body.dark-mode .form-input,
        body.dark-mode .form-select {
            background: #475569;
            border-color: #64748b;
            color: #e2e8f0;
        }

        body.dark-mode .form-input:focus,
        body.dark-mode .form-select:focus {
            border-color: #61b7ff;
            box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2);
        }

        body.dark-mode .existing-schedules {
            background: #334155;
            border-color: #475569;
        }

        body.dark-mode .schedule-item {
            background: #475569;
            border-left-color: #61b7ff;
        }

        body.dark-mode .schedule-info-text {
            color: #e2e8f0;
        }

        body.dark-mode .page-header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        body.dark-mode .page-title {
            color: #ffffff;
        }

        body.dark-mode .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
                margin-top: 80px;
            }

            .page-title {
                font-size: 2rem;
            }

            .calendar-grid {
                grid-template-columns: 80px repeat(5, 1fr);
                font-size: 0.75rem;
            }

            .time-slot, .day-header {
                min-height: 50px;
                padding: 0.25rem;
            }

            .calendar-cell {
                min-height: 50px;
                padding: 0.25rem;
            }

            .controls-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include 'navAdmin.php'; ?>
    
    <div class="main-content">
        <!-- Header de la página -->
        <div class="page-header">
            <h1 class="page-title">📅 Calendario de Horarios</h1>
            <p class="page-subtitle">Gestiona los horarios de "<?php echo htmlspecialchars($materia['nombre']); ?>" - Sección <?php echo htmlspecialchars($materia['seccion']); ?></p>
        </div>

        <!-- Información de la materia -->
        <div class="content-section">
            <h2 class="section-title">
                📚 Información de la Materia
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <strong>Materia:</strong> <?php echo htmlspecialchars($materia['nombre']); ?>
                </div>
                <div>
                    <strong>Sección:</strong> <?php echo htmlspecialchars($materia['seccion']); ?>
                </div>
                <div>
                    <strong>Salón:</strong> <?php echo htmlspecialchars($materia['salon']); ?>
                </div>
                <div>
                    <strong>Profesor:</strong> <?php echo htmlspecialchars($materia['profesor_nombre'] ?? 'Sin asignar'); ?>
                </div>
                <div>
                    <strong>Créditos:</strong> <?php echo htmlspecialchars($materia['creditos']); ?>
                </div>
                <div>
                    <strong>Semestre:</strong> <?php echo htmlspecialchars($materia['semestre']); ?>
                </div>
            </div>
        </div>

        <!-- Horarios existentes -->
        <?php if (!empty($horarios_existentes)): ?>
        <div class="content-section">
            <h2 class="section-title">
                ⏰ Horarios Actuales
            </h2>
            
            <div class="existing-schedules">
                <?php foreach ($horarios_existentes as $horario): ?>
                    <div class="schedule-item">
                        <div class="schedule-info-text">
                            <strong><?php echo htmlspecialchars($horario['dia']); ?></strong> - 
                            <?php echo date('H:i', strtotime($horario['hora_inicio'])); ?> a 
                            <?php echo date('H:i', strtotime($horario['hora_fin'])); ?>
                        </div>
                        <div class="schedule-actions">
                            <button class="btn-danger btn-small" onclick="eliminarHorario(<?php echo $horario['id']; ?>)">
                                🗑️ Eliminar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Panel de controles -->
        <div class="content-section">
            <h2 class="section-title">
                ⚙️ Agregar Horario
            </h2>
            
            <div class="controls-panel">
                <form id="horarioForm">
                    <input type="hidden" name="materia_id" value="<?php echo $materia_id; ?>">
                    
                    <div class="controls-grid">
                        <div class="form-group">
                            <label class="form-label" for="dia">Día de la semana:</label>
                            <select class="form-select" id="dia" name="dia" required>
                                <option value="">Seleccionar día</option>
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="hora_inicio">Hora de inicio:</label>
                            <input class="form-input" type="time" id="hora_inicio" name="hora_inicio" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="hora_fin">Hora de fin:</label>
                            <input class="form-input" type="time" id="hora_fin" name="hora_fin" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-primary">➕ Agregar Horario</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Calendario semanal -->
        <div class="content-section">
            <h2 class="section-title">
                📅 Vista Semanal
            </h2>
            
            <div class="calendar-container">
                <div class="calendar-header">
                    Horario Semanal - <?php echo htmlspecialchars($materia['nombre']); ?>
                </div>
                
                <div class="calendar-grid" id="calendarGrid">
                    <!-- Las celdas se generarán dinámicamente con JavaScript -->
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="editar_materia.php?nombre=<?php echo urlencode($materia['nombre']); ?>" class="btn-secondary">
                ↩️ Volver a Editar Materia
            </a>
        </div>
    </div>

    <script>
        // Datos de los horarios existentes
        const horariosExistentes = <?php echo json_encode($horarios_existentes); ?>;
        const materiaId = <?php echo $materia_id; ?>;

        // Horarios de clase (7:00 AM a 9:00 PM) - incluyendo intervalos de 15 minutos
        const horarios = [
            '07:00', '07:15', '07:30', '07:45',
            '08:00', '08:15', '08:30', '08:45',
            '09:00', '09:15', '09:30', '09:45',
            '10:00', '10:15', '10:30', '10:45',
            '11:00', '11:15', '11:30', '11:45',
            '12:00', '12:15', '12:30', '12:45',
            '13:00', '13:15', '13:30', '13:45',
            '14:00', '14:15', '14:30', '14:45',
            '15:00', '15:15', '15:30', '15:45',
            '16:00', '16:15', '16:30', '16:45',
            '17:00', '17:15', '17:30', '17:45',
            '18:00', '18:15', '18:30', '18:45',
            '19:00', '19:15', '19:30', '19:45',
            '20:00', '20:15', '20:30', '20:45',
            '21:00'
        ];

        const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

        // Generar el calendario
        function generarCalendario() {
            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = '';

            // Crear encabezado de días
            grid.appendChild(createCell('', 'time-slot', true));
            dias.forEach(dia => {
                grid.appendChild(createCell(dia, 'day-header', true));
            });

            // Crear filas de horarios
            horarios.forEach(hora => {
                // Celda de tiempo
                grid.appendChild(createCell(hora, 'time-slot', true));
                
                // Celdas para cada día
                dias.forEach(dia => {
                    const cell = createCell('', 'calendar-cell');
                    
                    // Verificar si hay horario en esta celda
                    const horario = encontrarHorario(hora, dia);
                    if (horario) {
                        cell.classList.add('has-schedule');
                        // Solo mostrar el color, sin texto
                        cell.innerHTML = '';
                    }
                    
                    grid.appendChild(cell);
                });
            });
        }

        function createCell(content, className, isHeader = false) {
            const cell = document.createElement('div');
            cell.className = className;
            cell.textContent = content;
            return cell;
        }

        function encontrarHorario(hora, dia) {
            return horariosExistentes.find(h => {
                const horaInicio = h.hora_inicio.substring(0, 5);
                const horaFin = h.hora_fin.substring(0, 5);
                
                // Verificar si la hora actual está dentro del rango del horario
                return h.dia === dia && hora >= horaInicio && hora < horaFin;
            });
        }

        // Manejar el formulario
        document.getElementById('horarioForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Validaciones del lado del cliente
            const dia = formData.get('dia');
            const horaInicio = formData.get('hora_inicio');
            const horaFin = formData.get('hora_fin');
            
            if (!dia || !horaInicio || !horaFin) {
                alert('❌ Todos los campos son obligatorios');
                return;
            }
            
            if (horaFin <= horaInicio) {
                alert('❌ La hora de fin debe ser mayor que la hora de inicio');
                return;
            }
            
            // Verificar si ya existe un horario para este día
            const horarioExistente = horariosExistentes.find(h => h.dia === dia);
            if (horarioExistente) {
                alert('❌ Ya existe un horario para este día');
                return;
            }
            
            fetch('guardar_horario.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Horario guardado correctamente');
                    // Limpiar formulario
                    document.getElementById('horarioForm').reset();
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al guardar el horario');
            });
        });

        function eliminarHorario(horarioId) {
            if (confirm('¿Estás seguro de que quieres eliminar este horario?')) {
                fetch('eliminar_horario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        horario_id: horarioId,
                        materia_id: materiaId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Horario eliminado correctamente');
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error al eliminar el horario');
                });
            }
        }

        // Funciones del navbar (mantener las existentes)
        const contenedor = document.getElementById('contenedor');
        const botonIzquierdo = document.getElementById('boton-izquierdo');
        const botonDerecho = document.getElementById('boton-derecho');
        
        if (botonIzquierdo && botonDerecho) {
            botonIzquierdo.addEventListener('click', () => {
                contenedor.scrollBy({
                    left: -94,
                    behavior: 'smooth'
                });
            });
            botonDerecho.addEventListener('click', () => {
                contenedor.scrollBy({
                    left: 94,
                    behavior: 'smooth'
                });
            });
        }

        if (document.getElementById('logoButton')) {
            document.getElementById('logoButton').addEventListener("click", () => {
                document.getElementById('menu').classList.toggle('toggle');
                event.stopPropagation();
            });
        }

        document.addEventListener('click', function(event) {
            if (contenedor && !contenedor.contains(event.target) && contenedor.classList.contains('toggle')) {
                contenedor.classList.remove('toggle');
            }
        });

        document.addEventListener('click', function(event) {
            var div = document.getElementById('menu');
            if (div && !div.contains(event.target)) {
                div.classList.remove('toggle');
            }
        });

        if (document.getElementById('switchtema')) {
            document.getElementById('switchtema').addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('theme', 'light');
                }
            });
        }

        window.addEventListener('load', function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
                if (document.getElementById('switchtema')) {
                    document.getElementById('switchtema').checked = true;
                }
            }
            
            generarCalendario();
        });

        function redirigir(url) {
            window.location.href = url;
        }

        window.onload = function() {
            const elementos = ['inicio', 'datos', 'profesor', 'materias', 'alumno'];
            const urls = [
                'pagina_administracion.php',
                'buscar_datos_admin.html',
                'admin_profesores.php',
                'admin_materias.php',
                'admin_alumnos.php'
            ];

            elementos.forEach((elemento, index) => {
                const element = document.getElementById(elemento);
                if (element) {
                    element.addEventListener('click', function() {
                        redirigir(urls[index]);
                    });
                }
            });
        }
    </script>

</body>

</html> 