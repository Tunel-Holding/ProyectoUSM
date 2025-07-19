<?php
include 'comprobar_sesion.php';
    include 'conexion.php'; // Incluye tu archivo de conexión a la base de datos

// Consulta para obtener la cantidad de profesores
$result_profesores = $conn->query("SELECT COUNT(*) as count FROM profesores");
if ($result_profesores === false) {
    die("Error en la consulta de profesores: " . $conn->error);
}
$row_profesores = $result_profesores->fetch_assoc();
$cantidad_profesores = $row_profesores['count'];

// Consulta para obtener la cantidad de estudiantes
$result_estudiantes = $conn->query("SELECT COUNT(*) as count FROM estudiantes");
if ($result_estudiantes === false) {
    die("Error en la consulta de estudiantes: " . $conn->error);
}
$row_estudiantes = $result_estudiantes->fetch_assoc();
$cantidad_estudiantes = $row_estudiantes['count'];

// Consulta para obtener la cantidad de materias únicas
$result_materias = $conn->query("SELECT COUNT(DISTINCT nombre) as count FROM materias");
if ($result_materias === false) {
    die("Error en la consulta de materias: " . $conn->error);
}
$row_materias = $result_materias->fetch_assoc();
$cantidad_materias = $row_materias['count'];

$registros = [];

$result_profesores = $conn->query("SELECT id, nombre, 'profesor' AS tipo FROM profesores ORDER BY id DESC LIMIT 1");
if ($result_profesores) {
    while ($row = $result_profesores->fetch_assoc()) {
        $registros[] = $row;
    }
}

    $result_estudiantes = $conn->query("SELECT id, carrera, 'carrera' AS tipo FROM estudiantes ORDER BY id DESC LIMIT 1");
    if ($result_estudiantes) {
        while ($row = $result_estudiantes->fetch_assoc()) {
            $registros[] = $row;
        }
    }

$result_materias = $conn->query("SELECT id, nombre, 'materia' AS tipo FROM materias ORDER BY id DESC LIMIT 1");
if ($result_materias) {
    while ($row = $result_materias->fetch_assoc()) {
        $registros[] = $row;
    }
}

usort($registros, function ($a, $b) {
    return $b['id'] - $a['id'];
});

$conn->close();


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-general.css">
    <title>UniHub - Panel de Administración</title>
</head>

<body>
    <!-- Navbar -->
    <?php include 'navAdmin.php'; ?>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <h1 class="hero-title">Bienvenido al administrador del Sistema UniHub</h1>
            <p class="hero-subtitle">Panel de administración completo para gestionar estudiantes, profesores y materias de manera eficiente</p>
            <div class="hero-cta">
                <a href="admin_profesores.php" class="btn-primary">
                    <img src="css/profesor.png" alt="Profesores" style="width: 20px; height: 20px; filter: none;">
                    Gestionar Profesores
                </a>
                <a href="admin_alumnos.php" class="btn-primary">
                    <img src="css/alumno.png" alt="Alumnos" style="width: 20px; height: 20px; filter: none;">
                    Gestionar Alumnos
                </a>
                <a href="admin_materias.php" class="btn-primary">
                    <img src="css/horario.png" alt="Materias" style="width: 20px; height: 20px; filter: none;">
                    Gestionar Materias
                </a>
                <a href="admin_asistencias.php" class="btn-primary">
                    <img src="css/asistencia.png" alt="Asistencias" style="width: 20px; height: 20px; filter: none;">
                    Gestionar Asistencias
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <h2 class="section-title">Estadísticas del Sistema</h2>
        
        <!-- Statistics Cards -->
        <div class="cards-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🎓</div>
                    <h3 class="card-title">Estudiantes</h3>
                </div>
                <div class="card-number"><?php echo $cantidad_estudiantes; ?></div>
                <p class="card-description">Total de estudiantes registrados en el sistema</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">👨‍🏫</div>
                    <h3 class="card-title">Profesores</h3>
                </div>
                <div class="card-number"><?php echo $cantidad_profesores; ?></div>
                <p class="card-description">Total de profesores activos en el sistema</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">📚</div>
                    <h3 class="card-title">Materias</h3>
                </div>
                <div class="card-number"><?php echo $cantidad_materias; ?></div>
                <p class="card-description">Total de materias únicas disponibles</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-section">
            <h3 class="section-title" style="font-size: 2rem; margin-bottom: 2rem;">Actividad Reciente</h3>
            <ul class="activity-list">
                <?php foreach ($registros as $r): ?>
                    <?php if ($r['tipo'] == 'profesor'): ?>
                    <li class="activity-item">
                        🧑‍🏫 <strong>Nuevo profesor registrado:</strong> <?php echo htmlspecialchars($r['nombre']); ?>
                    </li>
                    <?php elseif ($r['tipo'] == 'carrera'): ?>
                    <li class="activity-item">
                        🎓 <strong>Nuevo estudiante registrado:</strong> <?php echo htmlspecialchars($r['carrera']); ?>
                    </li>
                    <?php elseif ($r['tipo'] == 'materia'): ?>
                    <li class="activity-item">
                        📘 <strong>Nueva materia añadida:</strong> <?php echo htmlspecialchars($r['nombre']); ?>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </main>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad del cambio de tema
    const themeToggle = document.getElementById('themeToggle');
    const themeIconLight = document.querySelector('.theme-icon-light');
    const themeIconDark = document.querySelector('.theme-icon-dark');

    // Aplicar tema guardado al cargar la página
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        themeIconLight.style.display = 'none';
        themeIconDark.style.display = 'block';
    } else {
        document.body.classList.remove('dark-mode');
        themeIconLight.style.display = 'block';
        themeIconDark.style.display = 'none';
    }

    // Función para cambiar el tema
    function toggleTheme() {
        const body = document.body;
        const isDarkMode = body.classList.contains('dark-mode');
        
        if (isDarkMode) {
            // Cambiar a modo claro
            body.classList.remove('dark-mode');
            themeIconLight.style.display = 'block';
            themeIconDark.style.display = 'none';
            localStorage.setItem('theme', 'light');
        } else {
            // Cambiar a modo oscuro
            body.classList.add('dark-mode');
            themeIconLight.style.display = 'none';
            themeIconDark.style.display = 'block';
            localStorage.setItem('theme', 'dark');
        }
    }

    // Event listeners
    themeToggle.addEventListener('click', toggleTheme);

    // Aplicar tema al cargar la página
    applySavedTheme();

        // Smooth scrolling para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Animación de entrada para las cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card').forEach(card => {
            observer.observe(card);
        });

        // Efecto hover mejorado para las cards
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
});
    </script>
</body>
</html>