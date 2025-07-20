<?php
include 'comprobar_sesion.php';
actualizar_actividad();
    require 'conexion.php';
    if (isset($_GET['valor'])) { 

        $id_estudiante = $_SESSION['idusuario']; // La variable con la id del estudiante
        $id_materia = $_GET['valor']; // La variable con la id de la seccion

        function getAvailableCredits($id_usuario) { 
            actualizar_actividad();
            global $conn; 
            $sql = "SELECT creditosdisponibles FROM estudiantes WHERE id_usuario = ?"; 
            $stmt = $conn->prepare($sql); 
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute(); 
            $stmt->bind_result($creditosdisponibles); 
            $stmt->fetch(); 
            $stmt->close(); 
            return $creditosdisponibles; 
        }

        function getRequiredCredits($id_materia) { 
            actualizar_actividad();
            global $conn; 
            $sql = "SELECT creditos FROM materias WHERE id = ?"; 
            $stmt = $conn->prepare($sql); 
            $stmt->bind_param("i", $id_materia); 
            $stmt->execute(); 
            $stmt->bind_result($creditos_necesarios); 
            $stmt->fetch(); 
            $stmt->close(); 
            return $creditos_necesarios; 
        }

        function hasEnoughCredits($id_usuario, $id_materia) { 
            $creditos_disponibles = getAvailableCredits($id_usuario); 
            $creditos_necesarios = getRequiredCredits($id_materia); 
            return $creditos_disponibles >= $creditos_necesarios; 
        }

        function updateAvailableCredits($id_usuario, $creditos_nuevos) { 
            global $conn; 
            $sql = "UPDATE estudiantes SET creditosdisponibles = ? WHERE id_usuario = ?"; 
            $stmt = $conn->prepare($sql); 
            $stmt->bind_param("ii", $creditos_nuevos, $id_usuario); 
            $stmt->execute(); 
            $stmt->close();
        }

        if (hasEnoughCredits($id_estudiante, $id_materia)) { 
            // Verificar si el estudiante ya está inscrito en la materia
            $stmt = $conn->prepare("SELECT * FROM inscripciones WHERE id_estudiante = ? AND id_materia = ?");
            $stmt->bind_param("ii", $id_estudiante, $id_materia);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                actualizar_actividad();
                // Insertar la inscripción en la base de datos
                $stmt = $conn->prepare("INSERT INTO inscripciones (id_estudiante, id_materia, fecha_inscripcion) VALUES (?, ?, NOW())");
                $stmt->bind_param("ii", $id_estudiante, $id_materia);
                $stmt->execute();
                actualizar_actividad();
                if ($stmt->affected_rows > 0) {
                    actualizar_actividad();
                    $stmt = $conn->prepare("SELECT dia, hora_inicio, hora_fin FROM horariosmateria WHERE id_materia = ?"); 
                    $stmt->bind_param("i", $id_materia); 
                    $stmt->execute(); 
                    $result = $stmt->get_result(); // Insertar el horario del estudiante en la tabla horarios 
                    while ($row = $result->fetch_assoc()) {
                        $stmt_insert = $conn->prepare("INSERT INTO horarios (id_estudiante, id_materia) VALUES (?, ?)"); 
                        $stmt_insert->bind_param("ii", $id_estudiante, $id_materia); 
                        $stmt_insert->execute(); 
                    }

                    $creditos_nuevos= getAvailableCredits($id_estudiante) - getRequiredCredits($id_materia);
                    updateAvailableCredits($id_estudiante,$creditos_nuevos);
                    $_SESSION['mensaje'] = "Inscripción realizada con éxito.";
                } else {
                    $_SESSION['mensaje'] = "Error al inscribir al estudiante.";
                }
            } else {
                $_SESSION['mensaje'] = "El estudiante ya está inscrito en esta materia.";
            }
        } else {
            $_SESSION['mensaje'] = "No posees creditos suficientes para inscribir esta materia"; 
        }
        $conn->close();
        header("Location: inscripcion.php");
        exit();
 
        } else { 
            $_SESSION['mensaje'] =  "Solicitud no válida.";
        }
        actualizar_actividad();
        $conn->close();
        
?>