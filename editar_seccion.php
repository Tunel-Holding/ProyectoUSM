<?php
require_once 'AuthGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);
require 'conexion.php';

// Obtener el ID de la sección a editar
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    header('Location: admin_materias.php');
    exit();
}

// Obtener datos de la sección
$sql = "SELECT m.*, p.nombre AS profesor_nombre, p.id AS profesor_id 
        FROM materias m 
        LEFT JOIN profesores p ON m.id_profesor = p.id 
        WHERE m.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin_materias.php');
    exit();
}

$seccion = $result->fetch_assoc();

// Obtener lista de profesores para el select
$sql_profesores = "SELECT id, nombre FROM profesores ORDER BY nombre";
$result_profesores = $conn->query($sql_profesores);
$profesores = [];
while ($row = $result_profesores->fetch_assoc()) {
    $profesores[] = $row;
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
    <title>Editar Sección - USM</title>
    <style>
        .main-content {
            margin-top: 100px;
            padding: 2rem;
            max-width: 800px;
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

        .admin-form {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2);
        }

        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2);
        }

        .form-submit {
            background: var(--primary-yellow);
            color: var(--gray-900);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            margin-right: 1rem;
        }

        .form-submit:hover {
            background: #f0d742;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--gray-600);
            color: var(--white);
            border: none;
            padding: 0.75rem 2rem;
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

        .btn-primary {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: var(--white);
        }

        .current-info {
            background: var(--gray-100);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-blue);
        }

        .current-info h4 {
            margin: 0 0 0.5rem 0;
            color: var(--gray-800);
            font-weight: 600;
        }

        .current-info p {
            margin: 0.25rem 0;
            color: var(--gray-600);
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

        body.dark-mode .admin-form {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        body.dark-mode .form-label {
            color: #f1f5f9;
        }

        body.dark-mode .form-input,
        body.dark-mode .form-select {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        body.dark-mode .form-input:focus,
        body.dark-mode .form-select:focus {
            border-color: #61b7ff;
            box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2);
        }

        body.dark-mode .form-input::placeholder {
            color: #94a3b8;
        }

        body.dark-mode .current-info {
            background: #334155;
            border-left-color: #61b7ff;
        }

        body.dark-mode .current-info h4 {
            color: #f1f5f9;
        }

        body.dark-mode .current-info p {
            color: #cbd5e1;
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

        body.dark-mode .form-submit {
            background: #f59e0b;
            color: #1e293b;
        }

        body.dark-mode .form-submit:hover {
            background: #d97706;
        }

        body.dark-mode .btn-secondary {
            background: #6b7280;
            color: #ffffff;
        }

        body.dark-mode .btn-secondary:hover {
            background: #4b5563;
        }

        body.dark-mode .btn-primary {
            background: #3b82f6;
            color: #ffffff;
        }

        body.dark-mode .btn-primary:hover {
            background: #2563eb;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
                margin-top: 80px;
            }

            .page-title {
                font-size: 2rem;
            }

            .admin-form {
                padding: 1.5rem;
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
            <h1 class="page-title">Editar Sección</h1>
            <p class="page-subtitle">Modifica la configuración de la sección "<?php echo htmlspecialchars($seccion['nombre']); ?>"</p>
        </div>

        <!-- Información actual -->
        <div class="content-section">
            <h2 class="section-title">
                📋 Información Actual
            </h2>
            
            <div class="current-info">
                <h4>Datos de la Sección</h4>
                <p><strong>Materia:</strong> <?php echo htmlspecialchars($seccion['nombre']); ?></p>
                <p><strong>Sección:</strong> <?php echo htmlspecialchars($seccion['seccion']); ?></p>
                <p><strong>Salón:</strong> <?php echo htmlspecialchars($seccion['salon']); ?></p>
                <p><strong>Créditos:</strong> <?php echo htmlspecialchars($seccion['creditos']); ?></p>
                <p><strong>Semestre:</strong> <?php echo htmlspecialchars($seccion['semestre']); ?></p>
                <p><strong>Profesor:</strong> <?php echo htmlspecialchars($seccion['profesor_nombre'] ?? 'Sin asignar'); ?></p>
            </div>
        </div>

        <!-- Formulario de edición -->
        <div class="content-section">
            <h2 class="section-title">
                ✏️ Editar Sección
            </h2>
            
            <form class="admin-form" action="procesar_editar_seccion.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $seccion['id']; ?>">
                <input type="hidden" name="nombre_materia" value="<?php echo htmlspecialchars($seccion['nombre']); ?>">
                
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre de la Materia:</label>
                    <input class="form-input" type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($seccion['nombre']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="seccion">Sección:</label>
                    <input class="form-input" type="text" id="seccion" name="seccion" value="<?php echo htmlspecialchars($seccion['seccion']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="salon">Salón:</label>
                    <input class="form-input" type="text" id="salon" name="salon" value="<?php echo htmlspecialchars($seccion['salon']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="creditos">Créditos:</label>
                    <input class="form-input" type="number" id="creditos" name="creditos" value="<?php echo htmlspecialchars($seccion['creditos']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="semestre">Semestre:</label>
                    <input class="form-input" type="number" id="semestre" name="semestre" value="<?php echo htmlspecialchars($seccion['semestre']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="profesor">Profesor:</label>
                    <select class="form-select" id="profesor" name="profesor">
                        <option value="">Sin asignar</option>
                        <?php foreach ($profesores as $profesor): ?>
                            <option value="<?php echo $profesor['id']; ?>" 
                                    <?php echo ($profesor['id'] == $seccion['profesor_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($profesor['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin-top: 2rem;">
                    <button type="submit" class="form-submit">💾 Guardar Cambios</button>
                    <a href="calendario_horarios.php?materia_id=<?php echo $seccion['id']; ?>" class="btn-primary" style="margin-left: 1rem;">📅 Gestionar Horarios</a>
                    <a href="editar_materia.php?nombre=<?php echo urlencode($seccion['nombre']); ?>" class="btn-secondary" style="margin-left: 1rem;">↩️ Volver</a>
                </div>
            </form>
        </div>
    </div>

    <script>
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

        // Funciones del navbar (mantener las existentes)
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

        // Aplicar la preferencia guardada del usuario al cargar la página
        window.addEventListener('load', function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
                if (document.getElementById('switchtema')) {
                    document.getElementById('switchtema').checked = true;
                }
            }
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