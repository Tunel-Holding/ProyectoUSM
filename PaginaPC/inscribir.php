<?php

    session_start();
    require 'conexion.php';
    if (isset($_GET['valor'])) { 

        $id_estudiante = $_SESSION['idusuario']; // La variable con la id del estudiante
        $id_materia = $_GET['valor']; // La variable con la id de la seccion

        // Verificar si el estudiante ya está inscrito en la materia
        $stmt = $conn->prepare("SELECT * FROM Inscripciones WHERE id_estudiante = ? AND id_materia = ?");
        $stmt->bind_param("ii", $id_estudiante, $id_materia);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Insertar la inscripción en la base de datos
            $stmt = $conn->prepare("INSERT INTO Inscripciones (id_estudiante, id_materia, fecha_inscripcion) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $id_estudiante, $id_materia);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "Inscripción realizada con éxito.";
                $stmt = $conn->prepare("SELECT dia, hora_inicio, hora_fin FROM horariosmateria WHERE id_materia = ?"); 
                $stmt->bind_param("i", $id_materia); 
                $stmt->execute(); 
                $result = $stmt->get_result(); // Insertar el horario del estudiante en la tabla horarios 
                while ($row = $result->fetch_assoc()) {
                     $stmt_insert = $conn->prepare("INSERT INTO horarios (id_estudiante, id_materia, dia, hora_inicio, hora_fin) VALUES (?, ?, ?, ?, ?)"); 
                     $stmt_insert->bind_param("iisss", $id_estudiante, $id_materia, $row['dia'], $row['hora_inicio'], $row['hora_fin']); 
                     $stmt_insert->execute(); 
                    }
            } else {
                echo "Error al inscribir al estudiante.";
            }
        } else {
            echo "El estudiante ya está inscrito en esta materia.";
        }
    } else {
        echo "Solicitud no válida.";
    }

?>