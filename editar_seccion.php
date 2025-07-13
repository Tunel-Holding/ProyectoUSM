<?php
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

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/admin_materias.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principaladministracion.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Editar Sección</title>
    <script>
        // Solo JS exclusivo para la funcionalidad de edición de secciones
        function generarClases() {
            const cantidadClases = document.getElementById('cantidadClases').value;
            const contenedorClases = document.getElementById('contenedorClases');
            contenedorClases.innerHTML = '';

            for (let i = 0; i < cantidadClases; i++) {
                // Agregar línea divisora al principio del primer grupo
                if (i === 0) {
                    const hr = document.createElement('hr');
                    contenedorClases.appendChild(hr);
                }

                const claseDiv = document.createElement('div');
                claseDiv.classList.add('clase');

                const diaLabel = document.createElement('label');
                diaLabel.textContent = 'Día:';
                const diaSelect = document.createElement('select');
                diaSelect.name = `dia_${i}`;
                diaSelect.required = true;
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

                const finLabel = document.createElement('label');
                finLabel.textContent = 'Hora de Fin:';
                const finInput = document.createElement('input');
                finInput.type = 'time';
                finInput.name = `fin_${i}`;
                finInput.required = true;

                claseDiv.appendChild(diaLabel);
                claseDiv.appendChild(diaSelect);
                claseDiv.appendChild(inicioLabel);
                claseDiv.appendChild(inicioInput);
                claseDiv.appendChild(finLabel);
                claseDiv.appendChild(finInput);

                contenedorClases.appendChild(claseDiv);

                // Agregar línea divisora entre cada grupo
                const hr = document.createElement('hr');
                contenedorClases.appendChild(hr);
            }
        }

        window.onload = function () {
            document.getElementById('cantidadClases').value = 1;
            generarClases();
        };
    </script>
</head>

<body>

    <div class="cabecera">
        <button type="button" id="logoButton">
            <img src="css/logoazul.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>

    <?php include 'menu_administrador.php'; ?>

    <h1>Editar Sección</h1>

    <form class="form-materia" action="procesar_editar_seccion.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>"> <!-- Pasar el ID de la sección -->
        <div>
            <label for="salon">Salón:</label>
            <input type="text" name="salon" id="salon" value="<?php echo $seccion['salon']; ?>" required>
        </div>
        <div>
            <label for="cantidadClases">Cantidad de Clases:</label>
            <select id="cantidadClases" name="cantidadClases" onchange="generarClases()">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>
        <div id="contenedorClases"></div>
        <div>
            <button type="submit" id="editar">Editar Materia</button>
        </div>
    </form>

    <script>
        // Solo JS exclusivo para la funcionalidad de edición de secciones
        function generarClases() {
            const cantidadClases = document.getElementById('cantidadClases').value;
            const contenedorClases = document.getElementById('contenedorClases');
            contenedorClases.innerHTML = '';

            for (let i = 0; i < cantidadClases; i++) {
                // Agregar línea divisora al principio del primer grupo
                if (i === 0) {
                    const hr = document.createElement('hr');
                    contenedorClases.appendChild(hr);
                }

                const claseDiv = document.createElement('div');
                claseDiv.classList.add('clase');

                const diaLabel = document.createElement('label');
                diaLabel.textContent = 'Día:';
                const diaSelect = document.createElement('select');
                diaSelect.name = `dia_${i}`;
                diaSelect.required = true;
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

                const finLabel = document.createElement('label');
                finLabel.textContent = 'Hora de Fin:';
                const finInput = document.createElement('input');
                finInput.type = 'time';
                finInput.name = `fin_${i}`;
                finInput.required = true;

                claseDiv.appendChild(diaLabel);
                claseDiv.appendChild(diaSelect);
                claseDiv.appendChild(inicioLabel);
                claseDiv.appendChild(inicioInput);
                claseDiv.appendChild(finLabel);
                claseDiv.appendChild(finInput);

                contenedorClases.appendChild(claseDiv);

                // Agregar línea divisora entre cada grupo
                const hr = document.createElement('hr');
                contenedorClases.appendChild(hr);
            }
        }

        window.onload = function () {
            document.getElementById('cantidadClases').value = 1;
            generarClases();
        };
    </script>

</body>

</html>