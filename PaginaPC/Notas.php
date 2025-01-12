<?php
session_start(); // Iniciar la sesión al principio del archivo

// Verificar si 'idusuario' está definido en la sesión
if (!isset($_SESSION['idusuario'])) {
    die("Error: ID de usuario no definido en la sesión.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias</title>
    <link rel="stylesheet" href="css/NotasP.css">
</head>
<body>
    <div class="container">
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "proyectousm";

        // Crear conexión
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Verificar conexión
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }

        // Obtener el ID del usuario de la sesión
        $user_id = $_SESSION['idusuario'];

        // Obtener el ID del profesor correspondiente al usuario
        $sql_profesor = "SELECT id FROM profesores WHERE id_usuario = $user_id";
        $result_profesor = $conn->query($sql_profesor);

        if ($result_profesor === false) {
            echo "Error en la consulta: " . $conn->error;
        } else {
            if ($result_profesor->num_rows > 0) {
                $row_profesor = $result_profesor->fetch_assoc();
                $profesor_id = $row_profesor['id'];

                // Obtener las materias del profesor
                $sql_materias = "SELECT id, nombre FROM materias WHERE id_profesor = $profesor_id";
                $result_materias = $conn->query($sql_materias);

                if ($result_materias === false) {
                    echo "Error en la consulta: " . $conn->error;
                } else {
                    if ($result_materias->num_rows > 0) {
                        while ($row_materias = $result_materias->fetch_assoc()) {
                            $materia_id = $row_materias["id"];
                            $nombre = $row_materias["nombre"];
                            // Usar un formulario para enviar la materia_id
                            echo '<form method="POST" action="modificar_notas.php" style="display:inline;">
                                    <input type="hidden" name="materia_id" value="' . $materia_id . '">
                                    <button type="submit" aria-label="Ir a ' . $nombre . '">' . $nombre . '</button>
                                  </form>';
                        }
                    } else {
                        echo "No se encontraron materias.";
                    }
                }
            } else {
                echo "Usuario no encontrado o no es profesor.";
            }
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
