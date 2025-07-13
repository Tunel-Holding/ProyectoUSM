<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_profesor = $_POST['id_profesor'];
    $id_materia = $_POST['id_materia'];

    // Verificar que los datos no estén vacíos
    if (!empty($id_profesor) && !empty($id_materia)) {
        // Incluir la conexión a la base de datos
        require "conexion.php";

        // Preparar y ejecutar la consulta
        if ($stmt = $conn->prepare("UPDATE materias SET id_profesor = ? WHERE id = ?")) {
            $stmt->bind_param("ii", $id_profesor, $id_materia);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo "<script>
                            alert('Materia asignada exitosamente al profesor.');
                            window.location.href = 'admin_profesores.php';
                          </script>";
                } else {
                    echo "<script>
                            alert('La materia ya ha sido Asignada .');
                            window.history.back();
                          </script>";
                }
            } else {
                echo "Error en la ejecución de la consulta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }

        $conn->close();
    } else {
        echo $id_profesor;
        echo $id_materia;
        echo "Datos inválidos. Por favor, rellena todos los campos.";
    }
} else {
    echo "Método de solicitud no válido.";
}
?>