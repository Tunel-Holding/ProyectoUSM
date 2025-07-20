<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);


require_once "conexion.php";

/**
 * Clase para manejar la lógica de profesores
 */
class ProfesoresManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Obtiene todas las materias disponibles con información de asignación
     */
    public function obtenerTodasMateriasConAsignacion() {
        $sql = "
            SELECT 
                m.id,
                m.nombre,
                m.seccion,
                m.id_profesor,
                CASE 
                    WHEN m.id_profesor IS NOT NULL THEN p.nombre
                    ELSE NULL
                END AS profesor_asignado
            FROM materias m
            LEFT JOIN profesores p ON m.id_profesor = p.id
            ORDER BY m.nombre, m.seccion
        ";
        $result = $this->conn->query($sql);
        if (!$result) {
            error_log('Error en obtenerTodasMateriasConAsignacion: ' . $this->conn->error);
            return [];
        }
        $materias = [];
        while ($materia = $result->fetch_assoc()) {
            $materias[] = $materia;
        }
        return $materias;
    }
    
    /**
     * Construye la consulta SQL base para profesores
     */
    private function construirConsultaBase() {
        return "
            SELECT
                p.id AS id_profesor,
                p.nombre AS Nombre_Profesor, 
                GROUP_CONCAT(CONCAT(m.nombre, ' (', m.seccion, ')') SEPARATOR ', ') AS materias,
                GROUP_CONCAT(m.id SEPARATOR ',') AS materias_ids
            FROM 
                profesores p
            LEFT JOIN 
                materias m ON p.id = m.id_profesor
        ";
    }
    
    /**
     * Aplica filtros de búsqueda a la consulta usando prepared statements
     */
    /**
     * Aplica filtros de búsqueda a la consulta usando prepared statements
     */
    private function aplicarFiltros($sql, $busqueda = null) {
        if ($busqueda && !empty(trim($busqueda))) {
            $busqueda = trim($busqueda);
            // Validación estricta: solo letras, números y espacios
            if (preg_match('/^[a-zA-Z0-9\s]+$/', $busqueda)) {
                // Limitar longitud para prevenir ataques
                if (strlen($busqueda) <= 100) {
                    // Buscar solo por nombre (el campo cedula no existe)
                    $sql .= " WHERE p.nombre LIKE ?";
                    return [
                        'sql' => $sql,
                        'params' => [
                            '%' . $busqueda . '%'
                        ]
                    ];
                } else {
                    throw new Exception('La búsqueda es demasiado larga. Máximo 100 caracteres.');
                }
            } else {
                throw new Exception('La búsqueda debe contener solo letras, números y espacios.');
            }
        }
        return ['sql' => $sql, 'params' => []];
    }
    
    /**
     * Obtiene la lista de profesores con filtros opcionales usando prepared statements
     */
    public function obtenerProfesores($busqueda = null) {
        try {
            $sql = $this->construirConsultaBase();
            $filtros = $this->aplicarFiltros($sql, $busqueda);
            $sql = $filtros['sql'] . " GROUP BY p.id ORDER BY p.nombre";
            
            // Usar prepared statement para mayor seguridad
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception('Error al preparar la consulta: ' . $this->conn->error);
            }
            
            // Bind parameters si hay parámetros
            if (!empty($filtros['params'])) {
                $types = str_repeat('s', count($filtros['params'])); // 's' para strings
                $stmt->bind_param($types, ...$filtros['params']);
            }
            
            // Ejecutar la consulta
            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if (!$result) {
                throw new Exception('Error al obtener resultados: ' . $stmt->error);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error en obtenerProfesores: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valida si una búsqueda es válida con validaciones más estrictas
     */
    public function validarBusqueda($busqueda) {
        $busqueda = trim($busqueda);
        
        // Verificar que no esté vacía
        if (empty($busqueda)) {
            return false;
        }
        
        // Verificar longitud máxima
        if (strlen($busqueda) > 100) {
            return false;
        }
        
        // Verificar caracteres permitidos (solo letras, números y espacios)
        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $busqueda)) {
            return false;
        }
        
        // Verificar que no contenga solo espacios
        if (preg_match('/^\s+$/', $busqueda)) {
            return false;
        }
        
        // Verificar que no contenga caracteres sospechosos de SQL injection
        $suspicious = ['--', '/*', '*/', 'union', 'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter', 'exec', 'execute', 'script'];
        foreach ($suspicious as $susp) {
            if (stripos($busqueda, $susp) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Cierra la conexión
     */
    public function cerrarConexion() {
        $this->conn->close();
    }
}

/**
 * Clase para manejar la presentación HTML
 */
class ProfesoresView {
    
    /**
     * Renderiza el header de la página
     */
    public static function renderHeader() {
        return '
        <div class="page-header">
            <div class="container">
                <h1 class="page-title">Gestión de Profesores</h1>
                <p class="page-subtitle">Administra y gestiona los profesores del sistema UniHub</p>
            </div>
        </div>';
    }
    
    /**
     * Renderiza el formulario de búsqueda con validaciones de seguridad
     */
    public static function renderSearchForm($busqueda = '') {
        // Sanitizar y validar la búsqueda antes de mostrarla
        $busqueda = trim($busqueda);
        $busquedaEscapada = htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8');
        $clearButton = '';
        
        if (!empty($busqueda)) {
            $clearButton = '
                <a href="admin_profesores.php" class="clear-search" title="Limpiar búsqueda">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                    Limpiar búsqueda
                </a>';
        }
        
        return '
        <div class="search-section">
            <form method="get" class="search-form" onsubmit="return validarBusqueda()">
                <div class="search-input-group">
                    <input 
                        type="text" 
                        name="buscar" 
                        id="buscar-input"
                        value="' . $busquedaEscapada . '" 
                        placeholder="🔍 Buscar profesores por nombre o cedula..." 
                        class="search-input"
                        autocomplete="off"
                        maxlength="100"
                        pattern="[a-zA-Z0-9\s]+"
                        title="Solo se permiten letras, números y espacios"
                    />
                    <button type="submit" class="search-button" title="Buscar profesores">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </div>
                <div id="error-message" class="error-message" style="display: none;"></div>
                ' . $clearButton . '
            </form>
        </div>';
    }
    
    /**
     * Renderiza el botón para añadir profesor
     */
    public static function renderAddButton() {
        return '
        <a href="addprofesor.php" class="add-profesor-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Añadir Nuevo Profesor
        </a>';
    }
    
    /**
     * Renderiza las opciones del select de materias
     */
    public static function renderMateriasOptions($todasMaterias, $materiasActuales, $profesorActualId) {
        $options = '';
        
        foreach ($todasMaterias as $materia) {
            $selected = in_array($materia['id'], $materiasActuales) ? 'selected' : '';
            $materiaText = htmlspecialchars($materia['nombre'] . ' (' . $materia['seccion'] . ')', ENT_QUOTES, 'UTF-8');
            
            // Verifica si la materia está asignada a otro profesor
            $asignadaAOtro = false;
            $profesorAsignado = '';
            
            if (isset($materia['profesor_asignado']) && 
                $materia['id_profesor'] !== null && 
                $materia['id_profesor'] != $profesorActualId) {
                $asignadaAOtro = true;
                $profesorAsignado = htmlspecialchars($materia['profesor_asignado'], ENT_QUOTES, 'UTF-8');
                $materiaText .= ' (Asignada a ' . $profesorAsignado . ')';
            }
            
            // Si está asignada a otro profesor, hacer no seleccionable
            $disabled = $asignadaAOtro ? 'disabled' : '';
            $class = $asignadaAOtro ? 'class="materia-asignada"' : '';
            
            $options .= '<option value="' . $materia['id'] . '" ' . $selected . ' ' . $disabled . ' ' . $class . '>' . $materiaText . '</option>';
        }
        
        return $options;
    }
    
    /**
     * Renderiza los botones de acción
     */
    public static function renderActionButtons($profesorId) {
        $profesorIdEscapado = htmlspecialchars($profesorId, ENT_QUOTES, 'UTF-8');
        
        return '
        <div class="action-buttons">
            <a href="eliminar_profesor.php?id=' . $profesorIdEscapado . '" class="btn-delete">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
                Eliminar
            </a>
        </div>';
    }
    
    /**
     * Renderiza una fila de profesor
     */
    public static function renderProfesorRow($profesor, $todasMaterias) {
        $profesorId = $profesor['id_profesor'];
        $nombreProfesor = htmlspecialchars($profesor['Nombre_Profesor'] ?? '', ENT_QUOTES, 'UTF-8');
        $materiasActuales = $profesor['materias_ids'] ? explode(',', $profesor['materias_ids']) : [];
        
        $materiasOptions = self::renderMateriasOptions($todasMaterias, $materiasActuales, $profesorId);
        $actionButtons = self::renderActionButtons($profesorId);
        
        return '
        <tr>
            <td>
                <div class="profesor-name">' . $nombreProfesor . '</div>
            </td>
            <td>
                <select class="materias-select" id="materias-select-' . $profesorId . '" data-profesor-id="' . $profesorId . '" multiple>
                    ' . $materiasOptions . '
                </select>
                <div class="save-indicator" id="save-indicator-' . $profesorId . '">✓ Guardado</div>
            </td>
            <td>
                ' . $actionButtons . '
            </td>
        </tr>';
    }
    
    /**
     * Renderiza la tabla de profesores
     */
    public static function renderProfesoresTable($result, $todasMaterias) {
        if ($result && $result->num_rows > 0) {
            $rows = '';
            
            while ($profesor = $result->fetch_assoc()) {
                $rows .= self::renderProfesorRow($profesor, $todasMaterias);
            }
            
            return '
            <div class="profesores-table">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre del Profesor</th>
                            <th>Materias Asignadas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $rows . '
                    </tbody>
                </table>
            </div>';
        } else {
            return self::renderEmptyState();
        }
    }
    
    /**
     * Renderiza el estado vacío
     */
    public static function renderEmptyState() {
        return '
        <div class="profesores-table">
            <div class="empty-state">
                <h3>No se encontraron profesores</h3>
                <p>No hay profesores registrados en el sistema o no coinciden con tu búsqueda.</p>
                <a href="addprofesor.php" class="add-profesor-btn">Añadir Primer Profesor</a>
            </div>
        </div>';
    }
}

/**
 * Clase para manejar los estilos CSS
 */
class ProfesoresStyles {
    
    /**
     * Obtiene los estilos CSS específicos
     */
    public static function getStyles() {
        return '
        <style>
            /* Estilos específicos para la página de profesores */
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
                max-width: 1400px;
                margin: 0 auto;
                padding: 2rem;
            }

            .search-section {
                background: var(--white);
                border-radius: var(--border-radius);
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: var(--shadow-md);
                border: 1px solid var(--gray-200);
            }

            .search-form {
                display: flex;
                align-items: center;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .search-input-group {
                flex: 1;
                min-width: 300px;
                position: relative;
            }

            .search-input {
                width: 100%;
                padding: 1rem 3rem 1rem 1.5rem;
                border: 2px solid var(--gray-300);
                border-radius: 12px;
                font-size: 1rem;
                font-weight: 500;
                background: var(--white);
                color: var(--gray-800);
                transition: var(--transition);
                box-shadow: var(--shadow-sm);
            }

            .search-input:focus {
                outline: none;
                border-color: var(--primary-blue);
                box-shadow: 0 0 0 4px rgba(97, 183, 255, 0.15);
                transform: translateY(-1px);
            }

            .search-button {
                position: absolute;
                right: 0.75rem;
                top: 50%;
                transform: translateY(-50%);
                background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
                border: none;
                color: var(--white);
                cursor: pointer;
                padding: 0.75rem;
                border-radius: 8px;
                transition: var(--transition);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .search-button:hover {
                transform: translateY(-50%) scale(1.05);
                box-shadow: 0 4px 8px rgba(97, 183, 255, 0.3);
            }

            .clear-search {
                background: linear-gradient(135deg, var(--primary-yellow), #f0d742);
                color: var(--gray-900);
                text-decoration: none;
                font-size: 0.9rem;
                font-weight: 600;
                padding: 1rem 1.5rem;
                border-radius: 12px;
                transition: var(--transition);
                border: none;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                box-shadow: var(--shadow-sm);
            }

            .clear-search:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
                text-decoration: none;
                color: var(--gray-900);
            }

            .add-profesor-btn {
                background: linear-gradient(135deg, var(--primary-yellow), #f0d742);
                color: var(--gray-900);
                text-decoration: none;
                font-weight: 600;
                padding: 1rem 2rem;
                border-radius: 12px;
                transition: var(--transition);
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                box-shadow: var(--shadow-sm);
                margin-bottom: 2rem;
            }

            .add-profesor-btn:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
                text-decoration: none;
                color: var(--gray-900);
            }

            .profesores-table {
                background: var(--white);
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-md);
                border: 1px solid var(--gray-200);
            }

            .profesores-table table {
                width: 100%;
                border-collapse: collapse;
            }

            .profesores-table th {
                background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
                color: var(--white);
                padding: 1.5rem 1rem;
                text-align: left;
                font-weight: 600;
                font-size: 0.95rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .profesores-table td {
                padding: 1.5rem 1rem;
                border-bottom: 1px solid var(--gray-200);
                vertical-align: middle;
            }

            .profesores-table tr:hover {
                background-color: var(--gray-100);
            }

            .profesor-name {
                font-weight: 600;
                color: var(--gray-800);
                font-size: 1.1rem;
            }

            .materias-select {
                width: 100%;
                min-height: 120px;
                padding: 0.75rem;
                border: 2px solid var(--gray-300);
                border-radius: 8px;
                background: var(--white);
                color: var(--gray-800);
                font-family: "Inter", sans-serif;
                font-size: 0.9rem;
                transition: var(--transition);
            }

            .materias-select:focus {
                outline: none;
                border-color: var(--primary-blue);
                box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.1);
            }

            .materias-select option {
                padding: 0.5rem;
                margin: 2px 0;
                border-radius: 4px;
            }

            .materias-select option:checked {
                background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
                color: var(--white);
            }

            /* Materias asignadas a otros profesores */
            .materias-select option.materia-asignada {
                background: linear-gradient(135deg, #6c757d, #495057);
                color: #adb5bd;
                font-style: italic;
                cursor: not-allowed;
            }

            /* Compatibilidad con Choices.js */
            .choices__item--disabled {
                background: linear-gradient(135deg, #6c757d, #495057) !important;
                color: #adb5bd !important;
                font-style: italic !important;
                cursor: not-allowed !important;
                opacity: 0.7 !important;
            }

            .choices__item--disabled .choices__button {
                display: none !important;
            }

            /* Choices.js en modo oscuro */
            body.dark-mode .choices__inner {
                background: #334155 !important;
                border-color: #475569 !important;
            }

            body.dark-mode .choices__input {
                background: #334155 !important;
                color: #e2e8f0 !important;
            }

            body.dark-mode .choices__input::placeholder {
                color: #94a3b8 !important;
            }

            body.dark-mode .choices__list--dropdown {
                background: #1e293b !important;
                border-color: #334155 !important;
            }

            body.dark-mode .choices__item {
                color: #e2e8f0 !important;
            }

            body.dark-mode .choices__item--selectable {
                background: #334155 !important;
            }

            body.dark-mode .choices__item--selectable:hover {
                background: #475569 !important;
            }

            body.dark-mode .choices__item--selected {
                background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue)) !important;
                color: #ffffff !important;
            }

            body.dark-mode .choices__item--disabled {
                background: #475569 !important;
                color: #94a3b8 !important;
            }

            body.dark-mode .choices__list--single {
                color: #e2e8f0 !important;
            }

            body.dark-mode .choices__placeholder {
                color: #94a3b8 !important;
            }

            /* Estilos adicionales para Choices.js en modo oscuro */
            body.dark-mode .choices__list--multiple .choices__item {
                background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue)) !important;
                color: #ffffff !important;
                border: 1px solid #61b7ff !important;
            }

            body.dark-mode .choices__list--multiple .choices__item .choices__button {
                border-left: 1px solid #61b7ff !important;
                color: #ffffff !important;
            }

            body.dark-mode .choices__list--multiple .choices__item .choices__button:hover {
                background: rgba(255, 255, 255, 0.1) !important;
            }

            body.dark-mode .choices.is-focused .choices__inner {
                border-color: #61b7ff !important;
                box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2) !important;
            }

            body.dark-mode .choices.is-open .choices__inner {
                border-color: #61b7ff !important;
            }

            .save-indicator {
                display: none;
                color: #28a745;
                font-size: 0.8rem;
                margin-top: 0.5rem;
                font-weight: 500;
                padding: 0.25rem 0.5rem;
                background: rgba(40, 167, 69, 0.1);
                border-radius: 4px;
            }

            .save-indicator.show {
                display: inline-block;
                animation: fadeIn 0.3s ease;
            }

            .error-message {
                color: #dc3545;
                background: rgba(220, 53, 69, 0.1);
                border: 1px solid #dc3545;
                border-radius: 8px;
                padding: 0.75rem 1rem;
                margin-top: 1rem;
                font-size: 0.9rem;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .error-message.show {
                display: flex;
                animation: fadeIn 0.3s ease;
            }

            .search-input.error {
                border-color: #dc3545;
                box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-5px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .action-buttons {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .btn-delete {
                background: linear-gradient(135deg, #dc3545, #c82333);
                color: var(--white);
                border: none;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                transition: var(--transition);
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.9rem;
            }

            .btn-delete:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
                text-decoration: none;
                color: var(--white);
            }

            .empty-state {
                text-align: center;
                padding: 4rem 2rem;
                color: var(--gray-600);
            }

            .empty-state h3 {
                font-size: 1.5rem;
                margin-bottom: 1rem;
                color: var(--gray-800);
            }

            .empty-state p {
                font-size: 1rem;
                margin-bottom: 2rem;
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

                .search-form {
                    flex-direction: column;
                    align-items: stretch;
                }

                .search-input-group {
                    min-width: auto;
                }

                .profesores-table {
                    overflow-x: auto;
                }

                .profesores-table th,
                .profesores-table td {
                    padding: 1rem 0.75rem;
                    font-size: 0.9rem;
                }

                .action-buttons {
                    flex-direction: column;
                }

                .btn-delete {
                    justify-content: center;
                }
            }

            @media (max-width: 480px) {
                .page-title {
                    font-size: 1.75rem;
                }

                .profesores-table th,
                .profesores-table td {
                    padding: 0.75rem 0.5rem;
                    font-size: 0.85rem;
                }
            }

            /* Modo oscuro */
            body.dark-mode .search-section {
                background: #1e293b;
                border-color: #334155;
            }

            body.dark-mode .search-input {
                background: #334155;
                color: #e2e8f0;
                border-color: #475569;
            }

            body.dark-mode .search-input:focus {
                border-color: #61b7ff;
                box-shadow: 0 0 0 4px rgba(97, 183, 255, 0.2);
            }

            body.dark-mode .search-input::placeholder {
                color: #94a3b8;
            }

            body.dark-mode .profesores-table {
                background: #1e293b;
                border-color: #334155;
            }

            body.dark-mode .profesores-table th {
                background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            }

            body.dark-mode .profesores-table td {
                border-bottom-color: #334155;
                color: #e2e8f0;
            }

            body.dark-mode .profesores-table tr:hover {
                background-color: #334155;
            }

            body.dark-mode .profesor-name {
                color: #f1f5f9;
            }

            body.dark-mode .materias-select {
                background: #334155;
                color: #e2e8f0;
                border-color: #475569;
            }

            body.dark-mode .materias-select:focus {
                border-color: #61b7ff;
                box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2);
            }

            body.dark-mode .materias-select option {
                background: #334155;
                color: #e2e8f0;
            }

            body.dark-mode .materias-select option:checked {
                background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
                color: #ffffff;
            }

            body.dark-mode .materias-select option.materia-asignada {
                background: #475569;
                color: #94a3b8;
            }

            body.dark-mode .save-indicator {
                color: #10b981;
                background: rgba(16, 185, 129, 0.1);
            }

            body.dark-mode .btn-delete {
                background: linear-gradient(135deg, #dc3545, #c82333);
            }

            body.dark-mode .btn-delete:hover {
                box-shadow: 0 4px 8px rgba(220, 53, 69, 0.4);
            }

            body.dark-mode .empty-state h3 {
                color: #f1f5f9;
            }

            body.dark-mode .empty-state {
                color: #94a3b8;
            }

            body.dark-mode .page-header {
                background: linear-gradient(135deg, #1e40af, #3b82f6);
            }

            body.dark-mode .page-title {
                color: #ffffff;
            }

            body.dark-mode .page-subtitle {
                color: #e2e8f0;
            }

            body.dark-mode .add-profesor-btn {
                background: linear-gradient(135deg, var(--primary-yellow), #f0d742);
                color: #0f172a;
            }

            body.dark-mode .add-profesor-btn:hover {
                box-shadow: 0 4px 8px rgba(248, 227, 82, 0.3);
            }

            body.dark-mode .clear-search {
                background: linear-gradient(135deg, var(--primary-yellow), #f0d742);
                color: #0f172a;
            }

            body.dark-mode .clear-search:hover {
                box-shadow: 0 4px 8px rgba(248, 227, 82, 0.3);
            }

            /* Estilos adicionales para mejorar la experiencia del modo oscuro */
            body.dark-mode .main-container {
                background-color: #0f172a;
            }

            body.dark-mode .search-button {
                background: linear-gradient(135deg, #61b7ff, #3a85ff);
            }

            body.dark-mode .search-button:hover {
                box-shadow: 0 4px 8px rgba(97, 183, 255, 0.4);
            }

            /* Mejoras para el indicador de guardado en modo oscuro */
            body.dark-mode .save-indicator.show {
                animation: fadeIn 0.3s ease;
            }

            /* Estilos para mensajes de error en modo oscuro */
            body.dark-mode .alert {
                background: #1e293b;
                border-color: #dc3545;
                color: #e2e8f0;
            }

            body.dark-mode .alert-danger {
                background: rgba(220, 53, 69, 0.1);
                border-color: #dc3545;
                color: #f8d7da;
            }

            body.dark-mode .error-message {
                background: rgba(220, 53, 69, 0.1);
                border-color: #dc3545;
                color: #f8d7da;
            }

            body.dark-mode .search-input.error {
                border-color: #dc3545;
                box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
            }
        </style>';
    }
}

/**
 * Clase para manejar JavaScript
 */
class ProfesoresScripts {
    
    /**
     * Obtiene el JavaScript específico
     */
    public static function getScripts() {
        return '
        <script>
            // Funcionalidad del navbar y tema
            function goBack() {
                window.history.back();
            }

            // Validación de búsqueda en el lado del cliente
            function validarBusqueda() {
                const input = document.getElementById(\'buscar-input\');
                const errorDiv = document.getElementById(\'error-message\');
                const busqueda = input.value.trim();
                
                // Limpiar errores anteriores
                input.classList.remove(\'error\');
                errorDiv.style.display = \'none\';
                errorDiv.textContent = \'\';
                
                // Si está vacío, permitir (mostrar todos los profesores)
                if (busqueda === \'\') {
                    return true;
                }
                
                // Verificar longitud
                if (busqueda.length > 100) {
                    mostrarError(\'La búsqueda es demasiado larga. Máximo 100 caracteres.\');
                    return false;
                }
                
                // Verificar caracteres permitidos
                const regex = /^[a-zA-Z0-9\\s]+$/;
                if (!regex.test(busqueda)) {
                    mostrarError(\'Solo se permiten letras, números y espacios.\');
                    return false;
                }
                
                // Verificar que no contenga solo espacios
                if (/^\\s+$/.test(busqueda)) {
                    mostrarError(\'La búsqueda no puede contener solo espacios.\');
                    return false;
                }
                
                // Verificar palabras sospechosas
                const suspicious = [\'--\', \'/*\', \'*/\', \'union\', \'select\', \'insert\', \'update\', \'delete\', \'drop\', \'create\', \'alter\', \'exec\', \'execute\', \'script\'];
                for (let susp of suspicious) {
                    if (busqueda.toLowerCase().includes(susp)) {
                        mostrarError(\'La búsqueda contiene caracteres no permitidos.\');
                        return false;
                    }
                }
                
                return true;
            }
            
            function mostrarError(mensaje) {
                const input = document.getElementById(\'buscar-input\');
                const errorDiv = document.getElementById(\'error-message\');
                
                input.classList.add(\'error\');
                errorDiv.textContent = mensaje;
                errorDiv.style.display = \'flex\';
                
                // Enfocar el input
                input.focus();
            }
            
            // Validación en tiempo real
            document.addEventListener(\'DOMContentLoaded\', function() {
                const input = document.getElementById(\'buscar-input\');
                if (input) {
                    input.addEventListener(\'input\', function() {
                        // Remover clase de error mientras el usuario escribe
                        this.classList.remove(\'error\');
                        const errorDiv = document.getElementById(\'error-message\');
                        if (errorDiv) {
                            errorDiv.style.display = \'none\';
                        }
                    });
                }
            });

            // Inicializar Choices.js para los selects múltiples
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".materias-select").forEach(function(select) {
                    const profesorId = select.getAttribute("data-profesor-id");
                    const saveIndicator = document.getElementById(`save-indicator-${profesorId}`);
                    let timeoutId;

                    const choices = new Choices(select, {
                        removeItemButton: true,
                        searchResultLimit: 10,
                        placeholder: true,
                        placeholderValue: "Seleccionar materias...",
                        noResultsText: "No se encontraron materias",
                        noChoicesText: "No hay materias disponibles",
                        itemSelectText: "",
                        shouldSort: false,
                        classNames: {
                            item: "choices__item",
                            itemSelectable: "choices__item--selectable",
                            itemDisabled: "choices__item--disabled",
                            itemChoice: "choices__item--choice",
                            placeholder: "choices__placeholder",
                            group: "choices__group",
                            groupHeading: "choices__heading",
                            containerOuter: "choices",
                            containerInner: "choices__inner",
                            input: "choices__input",
                            inputCloned: "choices__input--cloned",
                            list: "choices__list",
                            listItems: "choices__list--multiple",
                            listSingle: "choices__list--single",
                            listDropdown: "choices__list--dropdown",
                            itemActive: "is-active",
                            itemSelected: "is-selected",
                            itemDisabled: "is-disabled",
                            itemHighlighted: "is-highlighted",
                            itemPlaceholder: "is-placeholder",
                            groupActive: "is-active",
                            groupDisabled: "is-disabled",
                            groupSelectable: "is-selectable",
                            groupSelected: "is-selected",
                            containerOpen: "is-open",
                            containerDisabled: "is-disabled",
                            containerFocused: "is-focused",
                            containerSelectable: "is-selectable",
                            containerSelected: "is-selected"
                        }
                    });

                    // Manejar cambios en el select
                    select.addEventListener("change", function() {
                        const selectedOptions = Array.from(select.selectedOptions).map(option => option.value);
                        
                        // Mostrar indicador de guardado
                        saveIndicator.classList.add("show");
                        
                        // Enviar datos al servidor
                        fetch("actualizar_materias_profesor.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                profesor_id: parseInt(profesorId),
                                materias_ids: selectedOptions.filter(id => id !== "")
                            })
                        })
                        .then(async response => {
                            let data;
                            try {
                                data = await response.json();
                            } catch (e) {
                                // Si no es JSON válido, mostrar el texto completo (HTML de error PHP)
                                const text = await response.text();
                                let errorMsg = "✗ Error de conexión\n" + text;
                                saveIndicator.textContent = errorMsg;
                                saveIndicator.style.color = "#dc3545";
                                saveIndicator.style.background = "rgba(220, 53, 69, 0.1)";
                                setTimeout(() => {
                                    saveIndicator.classList.remove("show");
                                    saveIndicator.textContent = "✓ Guardado";
                                    saveIndicator.style.color = "#28a745";
                                    saveIndicator.style.background = "rgba(40, 167, 69, 0.1)";
                                }, 3000);
                                return;
                            }
                            if (data.success) {
                                timeoutId = setTimeout(() => {
                                    saveIndicator.classList.remove("show");
                                }, 2000);
                            } else {
                                // Mostrar el mensaje exacto del backend y detalles si existen
                                let errorMsg = "✗ " + (data.message ? data.message : "Error al guardar");
                                if (data.details) {
                                    errorMsg += "\n" + data.details;
                                }
                                saveIndicator.textContent = errorMsg;
                                saveIndicator.style.color = "#dc3545";
                                saveIndicator.style.background = "rgba(220, 53, 69, 0.1)";
                                setTimeout(() => {
                                    saveIndicator.classList.remove("show");
                                    saveIndicator.textContent = "✓ Guardado";
                                    saveIndicator.style.color = "#28a745";
                                    saveIndicator.style.background = "rgba(40, 167, 69, 0.1)";
                                }, 3000);
                            }
                        })
                        .catch(error => {
                            let errorMsg = "✗ Error de conexión";
                            if (error && error.message) {
                                errorMsg += "\n" + error.message;
                            }
                            saveIndicator.textContent = errorMsg;
                            saveIndicator.style.color = "#dc3545";
                            saveIndicator.style.background = "rgba(220, 53, 69, 0.1)";
                            setTimeout(() => {
                                saveIndicator.classList.remove("show");
                                saveIndicator.textContent = "✓ Guardado";
                                saveIndicator.style.color = "#28a745";
                                saveIndicator.style.background = "rgba(40, 167, 69, 0.1)";
                            }, 3000);
                        });
                    });
                });
            });

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
        </script>';
    }
}

// ========================================
// LÓGICA PRINCIPAL
// ========================================

try {
    // Inicializar el manager de profesores
    $profesoresManager = new ProfesoresManager($conn);
    
    // Obtener y sanitizar parámetros de búsqueda
    $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    
    // Validación de seguridad adicional
    if (!empty($busqueda)) {
        // Verificar longitud máxima
        if (strlen($busqueda) > 100) {
            $error = 'La búsqueda es demasiado larga. Máximo 100 caracteres.';
            $busqueda = '';
        }
        // Verificar caracteres permitidos
        elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $busqueda)) {
            $error = 'La búsqueda debe contener solo letras, números y espacios.';
            $busqueda = '';
        }
        // Verificar que no contenga solo espacios
        elseif (preg_match('/^\s+$/', $busqueda)) {
            $error = 'La búsqueda no puede contener solo espacios.';
            $busqueda = '';
        }
        // Verificar palabras sospechosas
        else {
            $suspicious = ['--', '/*', '*/', 'union', 'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter', 'exec', 'execute', 'script'];
            foreach ($suspicious as $susp) {
                if (stripos($busqueda, $susp) !== false) {
                    $error = 'La búsqueda contiene caracteres no permitidos.';
                    $busqueda = '';
                    break;
                }
            }
        }
        
        // Si hay error, registrar el intento de búsqueda sospechosa
        if (isset($error)) {
            error_log("Intento de búsqueda sospechosa en admin_profesores.php: " . $_GET['buscar']);
        }
    }
    
    // Obtener datos solo si no hay errores
    if (!isset($error)) {
        $todasMaterias = $profesoresManager->obtenerTodasMateriasConAsignacion();
        $resultProfesores = $profesoresManager->obtenerProfesores($busqueda);
        
        if ($resultProfesores === false) {
            throw new Exception('Error al obtener los profesores');
        }
    }
    
} catch (Exception $e) {
    error_log("Error en admin_profesores.php: " . $e->getMessage());
    $error = 'Ha ocurrido un error al cargar los datos. Por favor, inténtalo de nuevo.<br><span style="color:#dc3545;font-size:0.95em">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</span>';
}
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
    <title>Gestión de Profesores - UniHub</title>
    <?php echo ProfesoresStyles::getStyles(); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
</head>

<body>

    <!-- Navbar -->
    <?php include 'navAdmin.php'; ?>

    <!-- Header de la página -->
    <?php echo ProfesoresView::renderHeader(); ?>

    <!-- Contenido principal -->
    <div class="main-container">
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php else: ?>
            
            <!-- Sección de búsqueda -->
            <?php echo ProfesoresView::renderSearchForm($busqueda); ?>

            <!-- Botón para añadir profesor -->
            <?php echo ProfesoresView::renderAddButton(); ?>

            <!-- Tabla de profesores -->
            <?php echo ProfesoresView::renderProfesoresTable($resultProfesores, $todasMaterias); ?>
            
        <?php endif; ?>
        
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <?php echo ProfesoresScripts::getScripts(); ?>

</body>

</html>

<?php
// Cerrar conexión al final
if (isset($profesoresManager)) {
    $profesoresManager->cerrarConexion();
}
?>