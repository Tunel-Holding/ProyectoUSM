<?php
require "conexion.php";

// Función para obtener todas las materias
function obtenerMaterias($conn) {
    $materias_sql = "SELECT m.id, m.nombre, m.seccion, m.id_profesor, p.nombre as nombre_profesor 
                    FROM materias m 
                    LEFT JOIN Profesores p ON m.id_profesor = p.id 
                    ORDER BY m.nombre, m.seccion";
    $materias_result = $conn->query($materias_sql);
    $todas_materias = [];
    while ($materia = $materias_result->fetch_assoc()) {
        $todas_materias[] = $materia;
    }
    return $todas_materias;
}

$todas_materias = obtenerMaterias($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Select Múltiple</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .test-container {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .materias-select {
            width: 100%;
            min-height: 120px;
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .debug-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>Prueba del Select Múltiple</h1>
    
    <div class="test-container">
        <h3>Materias disponibles:</h3>
        <select class="materias-select" id="test-select" multiple>
            <?php foreach ($todas_materias as $materia): ?>
                <?php 
                $nombre_materia = htmlspecialchars($materia['nombre'] . ' (' . $materia['seccion'] . ')');
                $disabled = ($materia['id_profesor'] !== null && $materia['id_profesor'] != 1) ? 'disabled' : '';
                $selected = ($materia['id_profesor'] == 1) ? 'selected' : '';
                ?>
                <option value="<?php echo $materia['id']; ?>" <?php echo $disabled . ' ' . $selected; ?>>
                    <?php echo $nombre_materia; ?>
                    <?php if ($materia['id_profesor'] !== null && $materia['id_profesor'] != 1): ?>
                        (Asignada a: <?php echo htmlspecialchars($materia['nombre_profesor'] ?? 'Profesor desconocido'); ?>)
                    <?php endif; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="debug-info">
        <h4>Información de depuración:</h4>
        <p><strong>Total de materias:</strong> <?php echo count($todas_materias); ?></p>
        <p><strong>Materias disponibles:</strong> 
            <?php 
            $disponibles = array_filter($todas_materias, function($m) { 
                return $m['id_profesor'] === null || $m['id_profesor'] == 1; 
            });
            echo count($disponibles);
            ?>
        </p>
        <p><strong>Materias asignadas a otros:</strong> 
            <?php 
            $asignadas_otros = array_filter($todas_materias, function($m) { 
                return $m['id_profesor'] !== null && $m['id_profesor'] != 1; 
            });
            echo count($asignadas_otros);
            ?>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('test-select');
            
            const choices = new Choices(select, {
                removeItemButton: true,
                searchResultLimit: 10,
                placeholder: true,
                placeholderValue: 'Seleccionar materias...',
                noResultsText: 'No se encontraron materias',
                noChoicesText: 'No hay materias disponibles',
                itemSelectText: '',
                shouldSort: false
            });
            
            // Evento para detectar cambios
            choices.passedElement.element.addEventListener('change', function() {
                const selectedValues = choices.getValue(true);
                const selectedOptions = selectedValues.map(item => item.value);
                console.log('Materias seleccionadas:', selectedOptions);
                
                // Mostrar en la consola
                console.log('Valores seleccionados:', selectedOptions);
                console.log('Opciones disponibles:', choices.choices);
            });
        });
    </script>
</body>
</html> 