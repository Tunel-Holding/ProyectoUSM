<?php
include 'conexion.php';

$id_usuario = 1; // ID del usuario del estudiante que ha iniciado sesión
$horarios = [];
$ultima_hora_fin = "00:00"; // Inicializamos la última hora de fin con un valor temprano
$primera_hora_inicio = "23:59"; // Inicializamos la primera hora de inicio con un valor tarde

// Consulta a la base de datos
$sql = "SELECT dia, hora_inicio, hora_fin, materias.nombre AS materia, materias.salon, profesores.nombre AS profesor
        FROM horarios
        JOIN estudiantes ON horarios.id_estudiante = estudiantes.id
        JOIN materias ON horarios.id_materia = materias.id
        JOIN profesores ON materias.id_profesor = profesores.id
        WHERE estudiantes.id_usuario = $id_usuario";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $horarios[] = $row;
        // Comparamos la hora de fin actual con la última hora de fin guardada
        if ($row['hora_fin'] > $ultima_hora_fin) {
            $ultima_hora_fin = $row['hora_fin'];
        }
        // Comparamos la hora de inicio actual con la primera hora de inicio guardada
        if ($row['hora_inicio'] < $primera_hora_inicio) {
            $primera_hora_inicio = $row['hora_inicio'];
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario de Clases</title>
    <link rel="stylesheet" href="horario.css">
    <link rel="icon" href="css/icono.png" type="image/png">
</head>
<body>
    <h1>Horario de Clases</h1>
    <div class="table-styled">
    <table class="horario-tabla">
        <tr>
            <th>Hora</th>
            <th>Lunes</th>
            <th>Martes</th>
            <th>Miércoles</th>
            <th>Jueves</th>
            <th>Viernes</th>
        </tr>
        <?php
        $horas = ["07:00 - 07:45", "07:45 - 08:30", "08:30 - 09:15", "09:15 - 10:00", "10:00 - 10:45", "10:45 - 11:30", "11:30 - 12:15", "12:15 - 01:00", "01:00 - 01:45", "01:45 - 02:30", "02:30 - 03:15", "03:15 - 04:00", "04:00 - 04:45", "04:45 - 05:30"];
        $dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
        $limiteHoras = count($horas); // Por defecto, mostramos todas las horas

        switch ($primera_hora_inicio) { 
            case "07:00:00": 
                $inicioHoras = 0; 
                break; 
            case "07:45:00": 
                $inicioHoras = 1; 
                break;
            case "08:30:00": 
                $inicioHoras = 2; 
                break; 
            case "09:15:00": 
                $inicioHoras = 3; 
                break; 
            case "10:00:00": 
                $inicioHoras = 4; 
                break; 
            case "10:45:00": 
                $inicioHoras = 5; 
                break; 
            case "11:30:00": 
                $inicioHoras = 6; 
                break; 
            case "12:15:00": 
                $inicioHoras = 7; 
                break; 
            case "01:00:00": 
                $inicioHoras = 8; 
                break; 
            case "01:45:00": 
                $inicioHoras = 9; 
                break; 
            case "02:30:00": 
                $inicioHoras = 10; 
                break; 
            case "03:15:00": 
                $inicioHoras = 11; 
                break; 
            case "04:00:00": 
                $inicioHoras = 12; 
                break; 
            case "04:45:00": 
                $inicioHoras = 13; 
                break; 
            default: 
                $inicioHoras = 0;
        }


        // Determinamos el límite de horas según la última hora de fin
        switch ($ultima_hora_fin) {
            case "07:45:00":
                $limiteHoras = 1;
                break;
            case "08:30:00":
                $limiteHoras = 2;
                break;
            case "09:15:00":
                $limiteHoras = 3;
                break;
            case "10:00:00":
                $limiteHoras = 4;
                break;
            case "10:45:00":
                $limiteHoras = 5;
                break;
            case "11:30:00":
                $limiteHoras = 6;
                break;
            case "12:15:00":
                $limiteHoras = 7;
                break;
            case "01:00:00":
                $limiteHoras = 8;
                break;
            case "01:45:00":
                $limiteHoras = 9;
                break;
            case "02:30:00":
                $limiteHoras = 10;
                break;
            case "03:15:00":
                $limiteHoras = 11;
                break;
            case "04:00:00":
                $limiteHoras = 12;
                break;
            case "04:45:00":
                $limiteHoras = 13;
                break;
            case "05:30:00":
                $limiteHoras = 14;
                break;
            default:
                $limiteHoras = count($horas);
        }
        ?>

        <?php
        function time_to_index($time) {
            $times = ["07:00", "07:45", "08:30", "09:15", "10:00", "10:45", "11:30", "12:15", "01:00", "01:45", "02:30", "03:15", "04:00", "04:45"];
            return array_search($time, $times);
        }

        $occupied_cells = []; // Array para mantener registro de celdas ocupadas

        for ($i = $inicioHoras; $i < $limiteHoras; $i++): ?>
            <tr>
                <td class="horario"><?php echo $horas[$i]; ?></td>
                <?php foreach ($dias as $dia):
                    // Generar el ID de la celda
                    $cell_id = $dia . '-' . str_replace([':', ' - '], '', $horas[$i]);
                    // Verificar si la celda ya está ocupada
                    if (in_array($cell_id, $occupied_cells)) {
                        continue;
                    }

                    $celda = '';
                    $rowspan = 1;
                    $clase_css = 'celda-vacia'; // Clase por defecto para celdas vacías

                    foreach ($horarios as $horario) {
                        $start_index = time_to_index(substr($horario['hora_inicio'], 0, 5));
                        $end_index = time_to_index(substr($horario['hora_fin'], 0, 5));

                        if ($horario['dia'] == $dia && $i == $start_index) {
                            $celda = $horario['materia'] . '<br>' . $horario['salon'] . '<br>' . $horario['profesor'];
                            $rowspan = $end_index - $start_index;
                            $clase_css = 'celda-horario'; // Clase para celdas con horarios

                            // Marcar las celdas que abarca esta clase como ocupadas
                            for ($j = $start_index; $j < $end_index; $j++) {
                                $occupied_cells[] = $dia . '-' . str_replace([':', ' - '], '', $horas[$j]);
                            }
                        }
                    }

                    if ($rowspan > 1): ?>
                        <td class="horario-celda <?php echo $clase_css; ?>" id="<?php echo $cell_id; ?>" rowspan="<?php echo $rowspan; ?>">
                            <?php echo $celda; ?>
                        </td>
                    <?php else: ?>
                        <td class="horario-celda <?php echo $clase_css; ?>" id="<?php echo $cell_id; ?>">
                            <?php echo $celda; ?>
                        </td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
    </table>
    </div>
</body>
</html>
