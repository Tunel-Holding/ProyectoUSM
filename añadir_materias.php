<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);
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
    <title>Añadir Materia</title>
</head>

<body>
    <?php include 'navAdmin.php'; ?>

    <h1>Añadir Materia</h1>

    <form class="form-materia" action="procesar_materia.php" method="POST">
        <div>
            <label for="nombre">Nombre de la Materia:</label>
            <input type="text" id="nombre" name="nombre" required maxlength="50">
        </div>
        <div>
            <label for="salon">Salón:</label>
            <input type="text" id="salon" name="salon" required maxlength="50">
        </div>
        <div>
            <label for="secciones">Número de Secciones:</label>
            <select id="secciones" name="secciones" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>
        <div>
            <label for="creditos">Número de Créditos:</label>
            <input type="number" id="creditos" name="creditos" required min="0" max="50">
        </div>
        <div>
            <label for="semestre">Semestre:</label>
            <select id="semestre" name="semestre" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
            </select>
        </div>
        <div>
            <button type="submit" id="agregar">Agregar Materia</button>
        </div>
    </form>

</body>

</html>