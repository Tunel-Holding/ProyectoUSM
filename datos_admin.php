<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);

require_once "conexion.php";
// Función para obtener el nivel de usuario
function obtenerNivelUsuario($conn, $usuario_id) {
    $usuario_id = (int)$usuario_id;
    $sql = "SELECT nivel_usuario FROM usuarios WHERE id = $usuario_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nivel = $row['nivel_usuario'];
        
        switch ($nivel) {
            case 'usuario':
                return 'Estudiante';
            case 'profesor':
                return 'Profesor';
            case 'administrador':
                return 'Administrador';
            default:
                return $nivel;
        }
    }
    
    return 'No disponible';
}

// Función para obtener datos del estudiante
function obtenerDatosEstudiante($conn, $usuario_id) {
    $usuario_id = (int)$usuario_id;
    $sql = "SELECT carrera, semestre, creditosdisponibles FROM estudiantes WHERE id_usuario = $usuario_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return [
        'carrera' => 'No disponible',
        'semestre' => 'No disponible',
        'creditosdisponibles' => 'No disponible'
    ];
}

// Procesar búsqueda
$busqueda = $_GET['query'] ?? '';
$resultados = [];
$error = null;

if (!empty($busqueda) && isset($busqueda) && ctype_digit($busqueda)) {
    try {
        $cedula = $conn->real_escape_string($busqueda);
        $sql = "SELECT * FROM datos_usuario WHERE cedula LIKE '%$cedula%'";
        $result = $conn->query($sql);
        
        if ($result === false) {
            throw new Exception('Error en la consulta SQL: ' . $conn->error);
        }
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $usuario_id = $row['usuario_id'];
                
                // Obtener nivel de usuario
                $nivelUsuario = obtenerNivelUsuario($conn, $usuario_id);
                
                // Obtener datos del estudiante
                $datosEstudiante = obtenerDatosEstudiante($conn, $usuario_id);
                
                // Combinar todos los datos
                $datosCompletos = array_merge($row, [
                    'nivel_usuario' => $nivelUsuario,
                    'carrera' => $datosEstudiante['carrera'],
                    'semestre' => $datosEstudiante['semestre'],
                    'creditosdisponibles' => $datosEstudiante['creditosdisponibles']
                ]);
                
                $resultados[] = $datosCompletos;
            }
        }
    } catch (Exception $e) {
        error_log("Error en datos_admin.php: " . $e->getMessage());
        $error = 'Ha ocurrido un error al procesar la búsqueda. Por favor, inténtalo de nuevo.';
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Búsqueda de Datos - UniHub</title>
    <style>
        /* Estilos específicos para la página de datos admin */
        .page-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            padding: 3rem 2rem;
            margin-top: 80px;
            color: var(--white);
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .results-section {
            margin-top: 2rem;
        }

        .result-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .user-type {
            background: linear-gradient(135deg, var(--primary-yellow), #f0d742);
            color: var(--gray-900);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .result-content {
            margin-top: 1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
        }

        .info-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1rem;
            color: var(--gray-900);
            font-weight: 500;
            padding: 0.5rem 0;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-600);
        }

        .empty-icon {
            margin-bottom: 1.5rem;
            color: var(--gray-400);
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--gray-800);
        }

        .empty-state p {
            font-size: 1rem;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid #dc3545;
            color: #721c24;
        }

        /* Modo oscuro */
        body.dark-mode .result-card {
            background: #1e293b;
            border-color: #334155;
        }

        body.dark-mode .result-card:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }

        body.dark-mode .result-header {
            border-bottom-color: #334155;
        }

        body.dark-mode .user-name {
            color: #f1f5f9;
        }

        body.dark-mode .info-label {
            color: #94a3b8;
        }

        body.dark-mode .info-value {
            color: #e2e8f0;
        }

        body.dark-mode .empty-state h3 {
            color: #f1f5f9;
        }

        body.dark-mode .empty-state {
            color: #94a3b8;
        }

        body.dark-mode .empty-icon {
            color: #64748b;
        }

        body.dark-mode .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
            color: #f8d7da;
        }

        body.dark-mode .page-header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
        }

        body.dark-mode .main-container {
            background-color: #0f172a;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .main-container {
                padding: 1rem;
            }

            .result-card {
                padding: 1.5rem;
            }

            .result-header {
                flex-direction: column;
                text-align: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .user-avatar {
                width: 50px;
                height: 50px;
            }

            .user-name {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.75rem;
            }

            .result-card {
                padding: 1rem;
            }

            .info-value {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <?php include 'navAdmin.php'; ?>

    <!-- Header de la página -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">Búsqueda de Datos de Usuario</h1>
            <p class="page-subtitle">Consulta información detallada de estudiantes, profesores y administradores</p>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="main-container">
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <div class="results-section">
            <?php if (empty($busqueda)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                    </div>
                    <h3>Ingresa una cédula para buscar</h3>
                    <p>Utiliza el formulario de búsqueda para encontrar información de usuarios</p>
                    <a href="buscar_datos_admin.html" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Ir a Búsqueda
                    </a>
                </div>
            <?php elseif (empty($resultados)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"/>
                        </svg>
                    </div>
                    <h3>No se encontraron resultados</h3>
                    <p>No se encontró ningún usuario con la cédula "<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>"</p>
                    <a href="buscar_datos_admin.php" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Nueva Búsqueda
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($resultados as $datos): ?>
                    <div class="result-card">
                        <div class="result-header">
                            <div class="user-avatar">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <div class="user-info">
                                <h3 class="user-name"><?php echo htmlspecialchars($datos['nombres'] . ' ' . $datos['apellidos'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <span class="user-type"><?php echo htmlspecialchars($datos['nivel_usuario'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                        <div class="result-content">
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Cédula:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($datos['cedula'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Sexo:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($datos['sexo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Teléfono:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($datos['telefono'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Correo:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($datos['correo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="info-item full-width">
                                    <span class="info-label">Dirección:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($datos['direccion'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Carrera:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($datos['carrera'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Semestre:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($datos['semestre'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Créditos Disponibles:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($datos['creditosdisponibles'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
    </div>

    <script>
        // Funcionalidad del tema oscuro (compatibilidad con navAdmin.php)
        document.addEventListener("DOMContentLoaded", function() {
            const theme = localStorage.getItem("theme");
            if (theme === "dark") {
                document.body.classList.add("dark-mode");
            }
        });

        // Navegación
        function redirigir(url) {
            window.location.href = url;
        }

        window.onload = function() {
            const inicio = document.getElementById("inicio");
            const datos = document.getElementById("datos");
            const profesor = document.getElementById("profesor");
            const alumno = document.getElementById("alumno");
            const materias = document.getElementById("materias");

            if (inicio) inicio.addEventListener("click", () => redirigir("pagina_administracion.php"));
            if (datos) datos.addEventListener("click", () => redirigir("buscar_datos_admin.html"));
            if (profesor) profesor.addEventListener("click", () => redirigir("admin_profesores.php"));
            if (alumno) alumno.addEventListener("click", () => redirigir("admin_alumnos.php"));
            if (materias) materias.addEventListener("click", () => redirigir("admin_materias.php"));
        };
    </script>

</body>

</html>