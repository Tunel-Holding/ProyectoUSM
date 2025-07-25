<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);
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
    <title>Editar Materia - USM</title>
    <style>
        .main-content {
            margin-top: 100px;
            padding: 2rem;
            max-width: 1200px;
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

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .admin-table th {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        @media (max-width: 600px) {
            .admin-table th:nth-child(3),
            .admin-table th:nth-child(4),
            .admin-table th:nth-child(5),
            .admin-table td:nth-child(3),
            .admin-table td:nth-child(4),
            .admin-table td:nth-child(5) {
                display: none;
            }
        }

        .admin-table tr:hover {
            background-color: var(--gray-100);
            transition: var(--transition);
        }

        .button-cell {
            text-align: center;
        }

        .btn-edit {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            margin-right: 0.5rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: var(--white);
        }

        .btn-delete {
            background: #dc3545;
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-add {
            background: var(--primary-yellow);
            color: var(--gray-900);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .btn-add:hover {
            background: #f0d742;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
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
        }

        .btn-secondary:hover {
            background: var(--gray-800);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
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

        body.dark-mode .admin-table {
            background: #1e293b;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        body.dark-mode .admin-table th {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: #ffffff;
        }

        body.dark-mode .admin-table td {
            border-bottom-color: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .admin-table tr:hover {
            background-color: #334155;
        }

        body.dark-mode .admin-form {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        body.dark-mode .form-label {
            color: #f1f5f9;
        }

        body.dark-mode .form-input {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        body.dark-mode .form-input:focus {
            border-color: #61b7ff;
            box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2);
        }

        body.dark-mode .form-input::placeholder {
            color: #94a3b8;
        }



        body.dark-mode .btn-edit {
            background: #3b82f6;
        }

        body.dark-mode .btn-edit:hover {
            background: #2563eb;
        }

        body.dark-mode .btn-delete {
            background: #ef4444;
        }

        body.dark-mode .btn-delete:hover {
            background: #dc2626;
        }

        body.dark-mode .btn-add {
            background: #f59e0b;
            color: #1e293b;
        }

        body.dark-mode .btn-add:hover {
            background: #d97706;
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

        /* Mejoras adicionales para modo oscuro */
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

        body.dark-mode hr {
            border-color: #334155;
        }



        /* Mejoras para los mensajes de notificación */
        body.dark-mode .notification-success {
            background: #1e4d2b !important;
            color: #a7f3d0 !important;
            border-color: #059669 !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        body.dark-mode .notification-error {
            background: #4c1d1d !important;
            color: #fca5a5 !important;
            border-color: #dc2626 !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        /* Mejoras de legibilidad para modo oscuro */
        body.dark-mode {
            color-scheme: dark;
        }

        body.dark-mode input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }

        body.dark-mode select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }



        /* Transiciones para mensajes de notificación */
        .notification-success,
        .notification-error {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
                margin-top: 80px;
            }

            .page-title {
                font-size: 2rem;
            }

            .admin-table {
                font-size: 0.875rem;
            }

            .admin-table th,
            .admin-table td {
                padding: 0.75rem 0.5rem;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include 'navAdmin.php'; ?>
    
    <div class="main-content">
        <?php
        require 'conexion.php';
        
        // Sanitizar y validar el parámetro nombre
        $nombre = isset($_GET['nombre']) ? trim(htmlspecialchars(strip_tags($_GET['nombre']), ENT_QUOTES, 'UTF-8')) : '';
        
        if (empty($nombre)) {
            header('Location: admin_materias.php');
            exit();
        }
        
        // Usar prepared statements para prevenir inyección SQL
        $sql = "SELECT m.*, p.nombre AS profesor_nombre FROM materias m LEFT JOIN profesores p ON m.id_profesor = p.id WHERE m.nombre = ? ORDER BY m.seccion ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Obtener información de la materia para el formulario de edición
        $sqlMateria = "SELECT nombre, creditos FROM materias WHERE nombre = ? LIMIT 1";
        $stmtMateria = $conn->prepare($sqlMateria);
        $stmtMateria->bind_param("s", $nombre);
        $stmtMateria->execute();
        $resultMateria = $stmtMateria->get_result();
        $materia = $resultMateria->fetch_assoc();
        ?>

        <!-- Header de la página -->
        <div class="page-header">
            <h1 class="page-title">Editar Materia</h1>
            <p class="page-subtitle">Gestiona las secciones y configuración de "<?php echo htmlspecialchars($nombre); ?>"</p>
        </div>

        <!-- Mensajes de notificación -->
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="notification-success" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                ✅ Sección actualizada correctamente
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="notification-error" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1rem; border: 1px solid #f5c6cb;">
                ❌ Error al actualizar la sección. Por favor, inténtalo de nuevo.
            </div>
        <?php endif; ?>

        <!-- Sección de secciones -->
        <div class="content-section">
            <h2 class="section-title">
                📚 Secciones de la Materia
            </h2>
            
            <?php if ($result->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nombre de la Materia</th>
                            <th>Profesor</th>
                            <th>Salón</th>
                            <th>Créditos</th>
                            <th>Semestre</th>
                            <th>Sección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["nombre"]); ?></td>
                                <td><?php echo htmlspecialchars($row["profesor_nombre"] ?? 'Sin asignar'); ?></td>
                                <td><?php echo htmlspecialchars($row["salon"]); ?></td>
                                <td><?php echo htmlspecialchars($row["creditos"]); ?></td>
                                <td><?php echo htmlspecialchars($row["semestre"]); ?></td>
                                <td><?php echo htmlspecialchars($row["seccion"]); ?></td>
                                <td class="button-cell">
                                    <a href="editar_seccion.php?id=<?php echo $row["id"]; ?>" class="btn-edit">Editar</a>
                                    <button class="btn-delete" onclick="eliminarSeccion(<?php echo $row["id"]; ?>)">Eliminar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: var(--gray-600); padding: 2rem;">No se encontraron secciones para la materia seleccionada.</p>
            <?php endif; ?>

            <button class="btn-add" onclick="window.location.href='añadir_seccion.php?nombre=<?php echo urlencode($nombre); ?>'">
                ➕ Añadir Nueva Sección
            </button>
        </div>

        <!-- Sección de edición de materia -->
        <div class="content-section">
            <h2 class="section-title">
                ⚙️ Configuración General de la Materia
            </h2>
            
            <form class="admin-form" action="procesar_editar_materia.php" method="POST">
                <input type="hidden" name="nombreOriginal" value="<?php echo htmlspecialchars($materia['nombre']); ?>" maxlength="50">
                
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre de la Materia:</label>
                    <input class="form-input" type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($materia['nombre']); ?>" required maxlength="50">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="creditos">Número de Créditos:</label>
                    <input class="form-input" type="number" id="creditos" name="creditos" value="<?php echo htmlspecialchars($materia['creditos']); ?>" required min="0" max="50">
                </div>
                
                <button type="submit" class="form-submit">💾 Guardar Cambios</button>
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



        function eliminarSeccion(id) {
            if (confirm('¿Estás seguro de que quieres eliminar esta sección?')) {
                window.location.href = `eliminar_seccion.php?id=${id}`;
            }
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

                    // Auto-ocultar mensajes de notificación después de 5 segundos
        const notifications = document.querySelectorAll('.notification-success, .notification-error');
        notifications.forEach(notification => {
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        });


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