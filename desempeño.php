<?php
session_start();

// Conectar a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$user_id = $_SESSION['idusuario'];

// Consultas SQL para seleccionar notas y nombres de materias
$sql = "SELECT n.final, m.nombre, m.id AS materia_id 
            FROM notas n 
            INNER JOIN materias m ON n.materia_id = m.id 
            WHERE n.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$materias = array();
while ($row = $result->fetch_assoc()) {
    $materias[$row['nombre']] = $row['final'];

    // Insertar datos en la tabla historicoacademico
    $sql_insert = "INSERT INTO historicoacademico (EstudianteID, MateriaID, Calificacion) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iii", $user_id, $row['materia_id'], $row['final']);
    $stmt_insert->execute();
    $stmt_insert->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Desempeño del Estudiante</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            --background-color: #d4d4d4;
            --bg-container: #f9f9f9;

        }

        body.dark-mode {
            --background-color: rgb(50, 50, 50);
            --text-color: white;
            --background-form: rgb(147, 136, 136);
            --bg-container: rgb(90, 90, 90);
        }

        .wecontainer {
            font-family: "Poppins", sans-serif;
            margin: auto;
            width: 100%;
            max-width: 1000px;
            background-color: var(--bg-container);
            padding: 40px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border: 3px solid #ffd700;
            /* Borde amarillo */
            border-top-width: 10px;
            /* Borde superior más grande */
            border-bottom-width: 10px;
            /* Borde inferior más grande */
            transition: 1s background ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            align-content: center;
            height: auto;
            margin-top: 50px;
        }

        .wecontainer h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            /* Tamaño de fuente más grande */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin: 20px 0;
            font-size: 18px;
            /* Tamaño de fuente más grande */
            text-align: left;
            background-color: var(--bg-container);
            /* Fondo azul claro */
            overflow: hidden;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #ffd700;
            /* Fondo amarillo */
            color: #004c97;
            /* Azul oscuro */
            font-weight: bold;
            /* Texto en negrita */
        }

        td {
            background-color: #ffffff;
            /* Fondo blanco */
            color: #004c97;
            /* Azul oscuro */
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="contenedorentrante3">
        <img src="css\logo.png">
    </div>
    <div class="cabecera">

        <button type="button" id="logoButton">
            <img src="css/logo.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>

    <?php include 'menu_alumno.php'; ?>

    <div class="wecontainer">
        <h1>Desempeño del Estudiante</h1>

        <table>
            <tr>
                <th>Materia</th>
                <th>Nota Final</th>
            </tr>
            <?php
            foreach (
                $materias as $materia => $nota_final) {
                echo "<tr><td>$materia</td><td>$nota_final</td></tr>";
            }
            ?>
        </table>
    </div>

    <script>
        // Aquí solo debe ir JS exclusivo de la página, si lo hubiera. Se eliminó la lógica de menú y tema.
    </script>
</body>

</html>