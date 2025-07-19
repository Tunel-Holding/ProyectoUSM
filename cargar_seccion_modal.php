<?php
require_once 'AuthGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);

include 'conexion.php';
$id = $_GET['id'];

// Obtener los detalles de la sección a editar
$sql = "SELECT * FROM materias WHERE id='$id'";
$result = $conn->query($sql);
$seccion = $result->fetch_assoc();

// Verificar si la columna id_profesor existe en la tabla materias y si está vacía
if (!array_key_exists('id_profesor', $seccion)) {
    die("Error: La columna 'id_profesor' no existe en la tabla 'materias'.");
}
$idProfesor = !empty($seccion['id_profesor']) ? $seccion['id_profesor'] : null;

// Obtener la lista de profesores
$sqlProfesores = "SELECT id, nombre FROM profesores";
$resultProfesores = $conn->query($sqlProfesores);
$conn->close();
?>

<style>
    /* Estilos específicos para el modal en modo oscuro */
    body.dark-mode .modal-form {
        background: #1e293b;
        border-color: #334155;
    }

    body.dark-mode .modal-form .form-label {
        color: #f1f5f9;
    }

    body.dark-mode .modal-form .form-input {
        background: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }

    body.dark-mode .modal-form .form-input:focus {
        border-color: #61b7ff;
        box-shadow: 0 0 0 3px rgba(97, 183, 255, 0.2);
    }

    body.dark-mode .modal-form .form-input::placeholder {
        color: #94a3b8;
    }

    body.dark-mode .modal-form .form-submit {
        background: #f59e0b;
        color: #1e293b;
    }

    body.dark-mode .modal-form .form-submit:hover {
        background: #d97706;
    }

    body.dark-mode .modal-form .btn-secondary {
        background: #6b7280;
        color: #ffffff;
    }

    body.dark-mode .modal-form .btn-secondary:hover {
        background: #4b5563;
    }

    body.dark-mode .modal-clase {
        background: #334155;
        border-color: #475569;
    }

    body.dark-mode .modal-clase label {
        color: #f1f5f9;
    }

    body.dark-mode .modal-clase select,
    body.dark-mode .modal-clase input {
        background: #475569;
        border-color: #64748b;
        color: #e2e8f0;
    }

    body.dark-mode .modal-clase select:focus,
    body.dark-mode .modal-clase input:focus {
        border-color: #61b7ff;
        box-shadow: 0 0 0 2px rgba(97, 183, 255, 0.2);
    }

    body.dark-mode .modal-clase select option {
        background: #475569;
        color: #e2e8f0;
    }

    .modal-form {
        background: var(--white);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
    }

    .modal-clase {
        background: var(--gray-100);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border: 1px solid var(--gray-200);
    }

    .modal-clase label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--gray-800);
    }

    .modal-clase select,
    .modal-clase input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid var(--gray-300);
        border-radius: 4px;
        margin-bottom: 1rem;
        background: var(--white);
        color: var(--gray-900);
    }

    .modal-clase select:focus,
    .modal-clase input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 2px rgba(97, 183, 255, 0.2);
    }

    .modal-clase select option {
        background: var(--white);
        color: var(--gray-900);
    }
</style>

<form class="modal-form admin-form" action="procesar_editar_seccion.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    
    <div class="form-group">
        <label class="form-label" for="salon">Salón:</label>
        <input class="form-input" type="text" name="salon" id="salon" value="<?php echo htmlspecialchars($seccion['salon']); ?>" required maxlength="50">
    </div>
    
    <div class="form-group">
        <label class="form-label" for="cantidadClases">Cantidad de Clases:</label>
        <select class="form-input" id="cantidadClases" name="cantidadClases" onchange="generarClases()">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>
    </div>
    
    <div id="contenedorClases"></div>
    
    <div class="form-group" style="display: flex; gap: 1rem; margin-top: 2rem;">
        <button type="submit" class="form-submit" style="flex: 1;">💾 Guardar Cambios</button>
        <button type="button" class="btn-secondary" onclick="cerrarModal()" style="flex: 1;">❌ Cancelar</button>
    </div>
</form>

<script>
    function generarClases() {
        const cantidadClases = document.getElementById('cantidadClases').value;
        const contenedorClases = document.getElementById('contenedorClases');
        contenedorClases.innerHTML = '';

        for (let i = 0; i < cantidadClases; i++) {
            // Agregar línea divisora al principio del primer grupo
            if (i === 0) {
                const hr = document.createElement('hr');
                hr.style.border = 'none';
                hr.style.height = '1px';
                hr.style.background = document.body.classList.contains('dark-mode') ? '#334155' : '#e5e7eb';
                hr.style.margin = '1rem 0';
                contenedorClases.appendChild(hr);
            }

            const claseDiv = document.createElement('div');
            claseDiv.classList.add('modal-clase');

            const diaLabel = document.createElement('label');
            diaLabel.textContent = 'Día:';
            const diaSelect = document.createElement('select');
            diaSelect.name = `dia_${i}`;
            diaSelect.required = true;
            diaSelect.className = 'form-input';
            const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            dias.forEach(dia => {
                const option = document.createElement('option');
                option.value = dia;
                option.textContent = dia;
                diaSelect.appendChild(option);
            });

            const inicioLabel = document.createElement('label');
            inicioLabel.textContent = 'Hora de Inicio:';
            const inicioInput = document.createElement('input');
            inicioInput.type = 'time';
            inicioInput.name = `inicio_${i}`;
            inicioInput.required = true;
            inicioInput.className = 'form-input';

            const finLabel = document.createElement('label');
            finLabel.textContent = 'Hora de Fin:';
            const finInput = document.createElement('input');
            finInput.type = 'time';
            finInput.name = `fin_${i}`;
            finInput.required = true;
            finInput.className = 'form-input';

            claseDiv.appendChild(diaLabel);
            claseDiv.appendChild(diaSelect);
            claseDiv.appendChild(inicioLabel);
            claseDiv.appendChild(inicioInput);
            claseDiv.appendChild(finLabel);
            claseDiv.appendChild(finInput);

            contenedorClases.appendChild(claseDiv);

            // Agregar línea divisora entre cada grupo
            const hr = document.createElement('hr');
            hr.style.border = 'none';
            hr.style.height = '1px';
            hr.style.background = document.body.classList.contains('dark-mode') ? '#334155' : '#e5e7eb';
            hr.style.margin = '1rem 0';
            contenedorClases.appendChild(hr);
        }
    }

    // Inicializar con 1 clase al cargar
    document.getElementById('cantidadClases').value = 1;
    generarClases();
</script> 