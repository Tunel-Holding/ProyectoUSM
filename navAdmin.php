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
    <nav class="navbar">
        <div class="navbar-container">
            <a href="pagina_administracion.php" class="navbar-brand">
                <img src="css/logounihubblanco.png" alt="UniHub">
                UniHub
            </a>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="pagina_administracion.php" class="nav-link">
                        <img src="css/home.png" alt="Inicio">
                        Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="buscar_datos_admin.php" class="nav-link">
                        <img src="css/person.png" alt="Datos">
                        Datos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_profesores.php" class="nav-link">
                        <img src="css/profesor.png" alt="Profesores">
                        Profesores
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_alumnos.php" class="nav-link">
                        <img src="css/alumno.png" alt="Alumnos">
                        Alumnos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_materias.php" class="nav-link">
                        <img src="css/horario.png" alt="Materias">
                        Materias
                    </a>
                </li>
                <li class="nav-item">
                    <button class="theme-toggle" id="themeToggle" title="Cambiar tema">
                        <!-- Icono de sol para modo claro -->
                        <svg class="theme-icon-light" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/>
                        </svg>
                        <!-- Icono de luna para modo oscuro -->
                        <svg class="theme-icon-dark" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                            <path d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z"/>
                        </svg>
                    </button>
                </li>
                <li class="nav-item">
                    <form action="logout.php" method="POST" style="margin: 0;">
                        <button type="submit" class="btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 13v-2H7V8l-5 4 5 4v-3z M17 2H5C3.9 2 3 2.9 3 4v3h2V4h12v16H5v-3H3v3c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                            </svg>
                            Salir
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <script>
        // Funcionalidad del tema oscuro
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIconLight = document.querySelector('.theme-icon-light');
            const themeIconDark = document.querySelector('.theme-icon-dark');
            
            // Verificar si hay un tema guardado en localStorage
            const currentTheme = localStorage.getItem('theme');
            
            // Aplicar tema inicial
            if (currentTheme === 'dark') {
                document.body.classList.add('dark-mode');
                themeIconLight.style.display = 'none';
                themeIconDark.style.display = 'block';
            } else {
                document.body.classList.remove('dark-mode');
                themeIconLight.style.display = 'block';
                themeIconDark.style.display = 'none';
            }
            
            // Manejar cambio de tema
            themeToggle.addEventListener('click', function() {
                const isDarkMode = document.body.classList.contains('dark-mode');
                
                if (isDarkMode) {
                    // Cambiar a modo claro
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('theme', 'light');
                    themeIconLight.style.display = 'block';
                    themeIconDark.style.display = 'none';
                } else {
                    // Cambiar a modo oscuro
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark');
                    themeIconLight.style.display = 'none';
                    themeIconDark.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>