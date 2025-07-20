<?php
// Solo incluir si no está ya incluido desde otra página
if (!defined('INCLUDED_FROM_MAIN')) {
    include 'comprobar_sesion.php';
    actualizar_actividad();
    
    // Si no se pasan los datos del horario desde la página principal, los obtenemos aquí
    if (!isset($datos_horario)) {
        require "conexion.php";
        $id_estudiante = $_SESSION['idusuario'];
        
        // Consulta para obtener el horario completo del estudiante
        $query_horario = "SELECT hm.dia, hm.hora_inicio, hm.hora_fin, m.nombre AS materia, m.salon,
                                 COALESCE(p.nombre, 'Profesor no asignado') AS profesor
                          FROM horarios h
                          JOIN horariosmateria hm ON h.id_materia = hm.id_materia
                          JOIN materias m ON h.id_materia = m.id
                          LEFT JOIN profesores p ON m.id_profesor = p.id
                          WHERE h.id_estudiante = ?
                          ORDER BY hm.dia, hm.hora_inicio";
        
        $stmt_horario = $conn->prepare($query_horario);
        $stmt_horario->bind_param("i", $id_estudiante);
        $stmt_horario->execute();
        $result_horario = $stmt_horario->get_result();

        $datos_horario = [];
        $horas_disponibles = [];
        if ($result_horario->num_rows > 0) {
            while ($row = $result_horario->fetch_assoc()) {
                $hora_inicio = strtotime($row['hora_inicio']);
                $hora_fin = strtotime($row['hora_fin']);
                $intervalo = 45 * 60; // 45 minutos
                for ($hora = $hora_inicio; $hora < $hora_fin; $hora += $intervalo) {
                    $hora_formateada = date("H:i:s", $hora);
                    $datos_horario[$row['dia']][$hora_formateada] = [
                        "materia" => $row['materia'],
                        "salon" => $row['salon'],
                        "profesor" => $row['profesor'] ?: "Profesor no asignado",
                        "inicio" => ($hora == $hora_inicio),
                        "rowspan" => ceil(($hora_fin - $hora_inicio) / $intervalo)
                    ];
                    $horas_disponibles[] = $hora_formateada;
                }
            }
        }
        $stmt_horario->close();
        $conn->close();
        // Calcular horas únicas y ordenadas
        $horas_disponibles = array_unique($horas_disponibles);
        sort($horas_disponibles);
    }
}
?>

<style>
/* Estilos específicos para la tabla del horario */

.horario-tabla {
    width: 100% !important;
    font-size: 11px !important;
    border-collapse: collapse !important;
    table-layout: fixed !important;
}

.horario-tabla th,
.horario-tabla td {
    padding: 6px 4px !important;
    border: 1px solid #dee2e6 !important;
    text-align: center !important;
    word-wrap: break-word !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.horario-tabla th {
    background: linear-gradient(135deg, #174388 0%, #1e5aa8 100%) !important;
    color: white !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    width: 16.66% !important; /* 6 columnas: 100% / 6 = 16.66% */
}

.horario-tabla th:first-child {
    width: 16.66% !important; /* Columna de hora */
}

.horario-celda {
    font-size: 10px !important;
    line-height: 1.2 !important;
    word-break: break-word !important;
    background-color: #e3f2fd !important;
    color: #174388 !important;
    font-weight: 500 !important;
}

.celda-vacia {
    background-color: #f8f9fa !important;
}



.div-horario {
    width: 100% !important;
    border-radius: 10px !important;
    border: 2px solid #174388 !important;
    margin: 10px 0 !important;
    background: white !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    /* Scrollbar visible si el contenedor es pequeño */
    overflow-x: auto !important;
}

/* Scrollbar personalizado para .div-horario */
.div-horario::-webkit-scrollbar {
    height: 10px;
    background: #e3f2fd;
    border-radius: 8px;
}
.div-horario::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #174388 0%, #1e5aa8 100%);
    border-radius: 8px;
}
.div-horario::-webkit-scrollbar-thumb:hover {
    background: #174388;
}
.div-horario::-webkit-scrollbar-corner {
    background: #e3f2fd;
}

/* Firefox */
.div-horario {
    scrollbar-width: thin;
    scrollbar-color: #174388 #e3f2fd;
}

/* Modo oscuro: scroll acorde */
body.dark-mode .div-horario::-webkit-scrollbar {
    background: #2d3748;
}
body.dark-mode .div-horario::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
}
body.dark-mode .div-horario::-webkit-scrollbar-thumb:hover {
    background: #4a5568;
}
body.dark-mode .div-horario::-webkit-scrollbar-corner {
    background: #2d3748;
}
body.dark-mode .div-horario {
    scrollbar-color: #4a5568 #2d3748;
}

/* Responsive para móviles */
@media (max-width: 768px) {
    .horario-tabla {
        font-size: 9px !important;
    }
    
    .horario-tabla th,
    .horario-tabla td {
        padding: 4px 2px !important;
    }
    
    .horario-celda {
        font-size: 8px !important;
    }
}

/* Estilos para modo oscuro */
body.dark-mode .horario-tabla {
    background-color: #2d3748 !important;
}

body.dark-mode .horario-tabla th {
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
    color: #ffffff !important;
    border-color: #4a5568 !important;
}

body.dark-mode .horario-tabla td {
    background-color: #2d3748 !important;
    color: #ffffff !important;
    border-color: #4a5568 !important;
}

body.dark-mode .horario-celda {
    background-color: #4a5568 !important;
    color: #ffffff !important;
    border: 1px solid #718096 !important;
}

body.dark-mode .celda-vacia {
    background-color: #2d3748 !important;
    border-color: #4a5568 !important;
}

body.dark-mode .div-horario {
    background: #2d3748 !important;
    border-color: #4a5568 !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
}

body.dark-mode .horario-tabla strong {
    color: #ffd700 !important;
}

/* Estilos específicos para el mensaje de "No hay clases" en modo oscuro */
body.dark-mode .horario-tabla td[colspan="6"] {
    background-color: #2d3748 !important;
    color: #a0aec0 !important;
    border-color: #4a5568 !important;
}
</style>

<?php
// Función para generar horas
function generar_horas($inicio, $intervalo, $total) {
    $horas = [];
    $hora_actual = strtotime($inicio);
    for ($i = 0; $i < $total; $i++) {
        $horas[] = date("H:i:s", $hora_actual);
        $hora_actual = strtotime("+$intervalo minutes", $hora_actual);
    }
    return $horas;
}
?>

<?php
// El array de días correcto para lunes a sábado
$dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
?>

<div class="div-horario">
    <table class="horario-tabla">
        <tr>
            <th>Hora</th>
            <th>Lunes</th>
            <th>Martes</th>
            <th>Miércoles</th>
            <th>Jueves</th>
            <th>Viernes</th>
            <th>Sábado</th>
        </tr>
        <?php
        if (empty($datos_horario) || empty($horas_disponibles)) {
            echo "<tr><td colspan='7' style='text-align: center; padding: 20px; color: #666;'>No hay clases programadas para este estudiante</td></tr>";
        } else {
            // Inicializar control de saltos para cada día
            $saltos = array_fill_keys($dias, 0);
            foreach ($horas_disponibles as $hora) {
                $hora_para_mostrar = date("H:i", strtotime($hora));
                echo "<tr>";
                echo "<td><strong>$hora_para_mostrar</strong></td>";
                for ($i = 0; $i < 6; $i++) {
                    $dia = $dias[$i];
                    if ($saltos[$dia] > 0) {
                        $saltos[$dia]--;
                        continue;
                    }
                    $contenido_celda = "";
                    if (isset($datos_horario[$dia][$hora])) {
                        $info = $datos_horario[$dia][$hora];
                        if ($info["inicio"]) {
                            $contenido_celda = "<strong>" . htmlspecialchars($info["materia"]) . "</strong><br>" .
                                             htmlspecialchars($info["salon"]) . "<br>" .
                                             htmlspecialchars($info["profesor"]);
                            $rowspan = $info["rowspan"];
                            echo "<td class='horario-celda' rowspan='$rowspan'>$contenido_celda</td>";
                            $saltos[$dia] = $rowspan - 1;
                        }
                    } else {
                        echo "<td class='celda-vacia'></td>";
                    }
                }
                echo "</tr>";
            }
        }
        ?>
    </table>
</div>