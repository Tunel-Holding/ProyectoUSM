<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_PROFESOR);
include 'comprobar_sesion.php';
actualizar_actividad();
// Verificar si 'idusuario' está definido en la sesión
if (!isset($_SESSION['idusuario'])) {
    die("Error: ID de usuario no definido en la sesión.");
}

// Obtener la materia_id del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['materia_id'])) {
        $_SESSION['materia_id'] = $_POST['materia_id'];
    }
}

// Verificar si 'materia_id' está definido en la sesión
if (!isset($_SESSION['materia_id'])) {
    die("Error: ID de materia no definida en la sesión.");
}

// Obtener la ID de la materia de la sesión
$materia_id = $_SESSION['materia_id'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="css/icono.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalprofesor.css">
    <script src="js/control_inactividad.js"></script>
    <link rel="stylesheet" href="css/tabla_calificaciones.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/Notas A2.css">
    <title>Modificar Notas - USM</title>
    <style>
        .button-cell {
            text-align: center;
        }

        input[type="text"] {
            width: 100px;
            /* Ajusta el tamaño según sea necesario */
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
    <script>

        function editRow(id) {
            var row = document.getElementById(id);
            var cells = row.getElementsByTagName("td");

            for (var i = 4; i <= 7; i++) {
                var cell = cells[i];
                var input = document.createElement("input");
                input.type = "text";
                input.value = cell.innerHTML;
                cell.innerHTML = "";
                cell.appendChild(input);
            }
            var button = cells[9].getElementsByTagName("button")[0];
            button.innerHTML = "Guardar";
            button.setAttribute('onclick', 'saveRow(' + id + ')');
        }

        function saveRow(id) {
            var row = document.getElementById(id);
            var cells = row.getElementsByTagName("td");

            var parcial1 = cells[4].getElementsByTagName("input")[0].value;
            var parcial2 = cells[5].getElementsByTagName("input")[0].value;
            var parcial3 = cells[6].getElementsByTagName("input")[0].value;
            var parcial4 = cells[7].getElementsByTagName("input")[0].value;

            // Resetear estilos anteriores y remover mensajes de error
            resetErrorStylesAndMessages(cells);

            // Validación de notas
            var error = false;

            if (!isNumeric(parcial1) || parseFloat(parcial1) > 20) {
                displayErrorMessage(cells[4], !isNumeric(parcial1) ? "Introduzca un número válido" : "Nota no puede ser mayor a 20");
                error = true;
            }
            if (!isNumeric(parcial2) || parseFloat(parcial2) > 20) {
                displayErrorMessage(cells[5], !isNumeric(parcial2) ? "Introduzca un número válido" : "Nota no puede ser mayor a 20");
                error = true;
            }
            if (!isNumeric(parcial3) || parseFloat(parcial3) > 20) {
                displayErrorMessage(cells[6], !isNumeric(parcial3) ? "Introduzca un número válido" : "Nota no puede ser mayor a 20");
                error = true;
            }
            if (!isNumeric(parcial4) || parseFloat(parcial4) > 20) {
                displayErrorMessage(cells[7], !isNumeric(parcial4) ? "Introduzca un número válido" : "Nota no puede ser mayor a 20");
                error = true;
            }

            if (error) {
                return;
            }

            var final = (parseFloat(parcial1) + parseFloat(parcial2) + parseFloat(parcial3) + parseFloat(parcial4)) / 4;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    cells[4].innerHTML = parseFloat(parcial1).toFixed(2);
                    cells[5].innerHTML = parseFloat(parcial2).toFixed(2);
                    cells[6].innerHTML = parseFloat(parcial3).toFixed(2);
                    cells[7].innerHTML = parseFloat(parcial4).toFixed(2);
                    cells[8].innerHTML = final.toFixed(2);
                    var button = cells[9].getElementsByTagName("button")[0];
                    button.innerHTML = "Editar";
                    button.setAttribute('onclick', 'editRow(' + id + ')');
                }
            };
            xhr.send("usuario_id=" + id + "&Parcial1=" + parcial1 + "&Parcial2=" + parcial2 + "&Parcial3=" + parcial3 + "&Parcial4=" + parcial4 + "&Final=" + final + "&accion=guardar");
        }

        function resetErrorStylesAndMessages(cells) {
            for (var i = 4; i <= 7; i++) {
                var input = cells[i].getElementsByTagName("input")[0];
                input.style.border = "";
                var errorMessage = cells[i].querySelector(".error-message");
                if (errorMessage) {
                    cells[i].removeChild(errorMessage);
                }
            }
        }

        function displayErrorMessage(cell, message) {
            var input = cell.getElementsByTagName("input")[0];
            input.style.border = "2px solid red";

            var errorMessage = document.createElement("div");
            errorMessage.className = "error-message";
            errorMessage.style.position = "absolute";
            errorMessage.style.backgroundColor = "white";
            errorMessage.style.border = "2px solid red";
            errorMessage.style.padding = "5px";
            errorMessage.style.color = "red";
            errorMessage.style.marginLeft = "5px";
            errorMessage.style.zIndex = "1000";
            errorMessage.innerHTML = message;

            cell.appendChild(errorMessage);
        }

        function isNumeric(value) {
            return !isNaN(value) && isFinite(value) && /^\d+(\.\d+)?$/.test(value);
        }


        function addNewRow() {
            var table = document.getElementById("notas");
            var rowCount = table.rows.length;
            var row = table.insertRow(rowCount);

            var celdaUsuario = row.insertCell(0);
            celdaUsuario.innerHTML = "<input type='text' id='newUserId'>";

            var celdaNombre = row.insertCell(1);
            celdaNombre.innerHTML = "<input type='text' id='newNombre'>";

            var celdaApellido = row.insertCell(2);
            celdaApellido.innerHTML = "<input type='text' id='newApellido'>";

            var celdaCedula = row.insertCell(3);
            celdaCedula.innerHTML = "<input type='text' id='newCedula'>";

            for (var i = 4; i <= 7; i++) {
                var cell = row.insertCell(i);
                cell.innerHTML = "<input type='text' id='newParcial" + (i - 3) + "'>";
            }

            var celdaFinal = row.insertCell(8);
            celdaFinal.innerHTML = "0.00";

            var celdaAccion = row.insertCell(9);
            celdaAccion.innerHTML = "<button onclick='saveNewRow(" + rowCount + ")'>Guardar</button>";
        }

        function saveNewRow(rowCount) {
            var newUserId = document.getElementById("newUserId").value;
            var newNombre = document.getElementById("newNombre").value;
            var newApellido = document.getElementById("newApellido").value;
            var newCedula = document.getElementById("newCedula").value;
            var newParcial1 = parseFloat(document.getElementById("newParcial1").value);
            var newParcial2 = parseFloat(document.getElementById("newParcial2").value);
            var newParcial3 = parseFloat(document.getElementById("newParcial3").value);
            var newParcial4 = parseFloat(document.getElementById("newParcial4").value);
            var newfinal = (newParcial1 + newParcial2 + newParcial3 + newParcial4) / 4;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var table = document.getElementById("notas");
                    var row = table.rows[rowCount];
                    row.cells[0].innerHTML = newUserId;
                    row.cells[1].innerHTML = newNombre;
                    row.cells[2].innerHTML = newApellido;
                    row.cells[3].innerHTML = newCedula;
                    row.cells[4].innerHTML = newParcial1.toFixed(2);
                    row.cells[5].innerHTML = newParcial2.toFixed(2);
                    row.cells[6].innerHTML = newParcial3.toFixed(2);
                    row.cells[7].innerHTML = newParcial4.toFixed(2);
                    row.cells[8].innerHTML = newfinal.toFixed(2);
                    row.cells[9].innerHTML = "<button onclick='editRow(" + newUserId + ")'>Editar</button>";
                }
            };
            xhr.send("usuario_id=" + newUserId + "&Nombre=" + newNombre + "&Apellido=" + newApellido + "&Cedula=" + newCedula + "&Parcial1=" + newParcial1 + "&Parcial2=" + newParcial2 + "&Parcial3=" + newParcial3 + "&Parcial4=" + newParcial4 + "&Final=" + newfinal);
        }
        function uploadPartial(usuario_id) {
            if (document.getElementById('partial-buttons-' + usuario_id)) {
                return; // Si la fila ya existe, no hacer nada
            }

            var overlay = document.createElement("div");
            overlay.setAttribute("id", "overlay");
            overlay.style.position = "fixed";
            overlay.style.top = "0";
            overlay.style.left = "0";
            overlay.style.width = "100%";
            overlay.style.height = "100%";
            overlay.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
            overlay.style.zIndex = "5";
            document.body.appendChild(overlay);

            var row = document.getElementById(usuario_id);

            var buttonContainer = document.createElement("div");
            buttonContainer.setAttribute("id", 'partial-buttons-' + usuario_id);
            buttonContainer.style.backgroundColor = "white";
            buttonContainer.style.padding = "15px";
            buttonContainer.style.border = "2px solid #007BFF";
            buttonContainer.style.borderRadius = "10px";
            buttonContainer.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.1)";
            buttonContainer.style.position = "absolute";
            buttonContainer.style.zIndex = "10";
            buttonContainer.style.left = "50%";
            buttonContainer.style.transform = "translateX(-50%)";
            buttonContainer.style.marginTop = "10px";
            buttonContainer.style.textAlign = "center";
            buttonContainer.style.display = "grid";
            buttonContainer.style.gridTemplateColumns = "repeat(4, 1fr)";
            buttonContainer.style.gap = "10px";
            buttonContainer.style.borderBottom = "1px solid #007BFF";

            var titles = ["Parcial 1", "Parcial 2", "Parcial 3", "Parcial 4"];
            var columnContainers = [];
            titles.forEach(function (title, index) {
                var columnContainer = document.createElement("div");
                columnContainer.style.display = "flex";
                columnContainer.style.flexDirection = "column";
                columnContainer.style.alignItems = "center";
                columnContainer.style.borderLeft = index === 0 ? "none" : "1px solid #007BFF";
                columnContainer.style.paddingLeft = "10px";
                columnContainer.style.height = "100%";
                buttonContainer.appendChild(columnContainer);

                var titleElement = document.createElement("div");
                titleElement.innerHTML = title;
                titleElement.style.fontWeight = "bold";
                titleElement.style.marginBottom = "10px";
                columnContainer.appendChild(titleElement);

                // Función para crear botón de selección de archivo
                function createSelectButton() {
                    var selectButton = document.createElement("button");
                    selectButton.innerHTML = "Seleccionar archivo";
                    selectButton.style.margin = "5px";
                    selectButton.onclick = function () {
                        var fileInput = document.createElement("input");
                        fileInput.type = "file";
                        fileInput.accept = ".jpg,.png,.pdf";
                        fileInput.onchange = function () {
                            var file = fileInput.files[0];
                            var formData = new FormData();
                            formData.append("usuario_id", usuario_id);
                            formData.append("materia_id", <?php echo $materia_id; ?>);
                            formData.append("parcial", index + 1); // Indicar el parcial
                            formData.append("file", file);

                            var xhr = new XMLHttpRequest();
                            xhr.open("POST", "subir_archivo.php", true);
                            xhr.onload = function () {
                                if (xhr.status === 200) {
                                    // En lugar de una alerta, actualiza el DOM
                                    var indicativo = document.createElement("span");
                                    indicativo.innerHTML = "Archivo subido";
                                    indicativo.style.color = "green";
                                    indicativo.style.fontWeight = "bold";
                                    columnContainer.appendChild(indicativo);
                                    selectButton.remove(); // Eliminar el botón de selección de archivo

                                    // Crear botón de eliminación
                                    createDeleteButton(indicativo);
                                } else {
                                    alert("Error al subir el archivo");
                                }
                            };
                            xhr.send(formData);
                        };
                        fileInput.click();
                    };
                    columnContainer.appendChild(selectButton);
                }

                // Función para crear botón de eliminación
                function createDeleteButton(indicativo) {
                    var deleteButton = document.createElement("button");
                    deleteButton.innerHTML = "Eliminar archivo";
                    deleteButton.style.margin = "5px";
                    deleteButton.onclick = function () {
                        var deleteXhr = new XMLHttpRequest();
                        deleteXhr.open("POST", "eliminar_archivo.php", true);
                        deleteXhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        deleteXhr.onload = function () {
                            if (deleteXhr.status === 200) {
                                indicativo.remove(); // Remover el mensaje "Archivo subido"
                                deleteButton.remove(); // Remover el botón de eliminar
                                createSelectButton(); // Añadir de nuevo el botón "Seleccionar archivo"
                            } else {
                                alert("Error al eliminar el archivo");
                            }
                        };
                        deleteXhr.send("usuario_id=" + usuario_id + "&materia_id=" + <?php echo $materia_id; ?> + "&parcial=" + (index + 1));
                    };
                    columnContainer.appendChild(deleteButton);
                    columnContainer.appendChild(indicativo); // Añadir el mensaje debajo del botón de eliminar
                }

                // Verificar si ya hay archivo subido
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "verificar_archivo.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.responseText.trim() === "Archivo subido") {
                        var indicativo = document.createElement("span");
                        indicativo.innerHTML = "Archivo subido";
                        indicativo.style.color = "green";
                        indicativo.style.fontWeight = "bold";

                        createDeleteButton(indicativo);
                    } else {
                        createSelectButton();
                    }
                };
                xhr.send("usuario_id=" + usuario_id + "&materia_id=" + <?php echo $materia_id; ?> + "&parcial=" + (index + 1));
                columnContainers.push(columnContainer);
            });

            var closeButton = document.createElement("span");
            closeButton.innerHTML = "&times;";
            closeButton.style.position = "absolute";
            closeButton.style.top = "-10px";
            closeButton.style.right = "-5px";
            closeButton.style.padding = "5px";
            closeButton.style.cursor = "pointer";
            closeButton.style.fontSize = "30px";
            closeButton.style.color = "#007BFF";
            closeButton.onclick = function () {
                buttonContainer.remove();
                overlay.remove();
            };
            buttonContainer.appendChild(closeButton);

            var uploadButtonDiv = row.querySelector("td:nth-child(10) > div:nth-child(2)");
            uploadButtonDiv.insertAdjacentElement('beforebegin', buttonContainer);
        }
    </script>
</head>

<body>

    <div class="cabecera">
        <button type="button" id="logoButton">
            <img src="css/logo.png" alt="Logo">
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>
    </div>

    <?php include 'menu_profesor.php'; ?>

    <div class="content">
        <?php
        // Conexión a la base de datos
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "proyectousm";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }

        // Obtener datos de la materia
        $sql_materia = "SELECT seccion, nombre FROM materias WHERE id = ?";
        $stmt_materia = $conn->prepare($sql_materia);
        $stmt_materia->bind_param("i", $materia_id);
        $stmt_materia->execute();
        $result_materia = $stmt_materia->get_result();

        if ($result_materia->num_rows > 0) {
            $row_materia = $result_materia->fetch_assoc();
            $seccion = htmlspecialchars($row_materia['seccion']);
            $nombre = htmlspecialchars($row_materia['nombre']);
        } else {
            die("Error: No se encontraron datos para la materia con ID: " . htmlspecialchars($materia_id));
        }
        ?>
        <h1>Bienvenidos a la sección "<?php echo $seccion; ?>" de "<?php echo $nombre; ?>"</h1>
        <div class="tabla">
            <?php
            // Manejar actualización de notas
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $accion = isset($_POST["accion"]) ? $_POST["accion"] : '';
                $num_lista = isset($_POST["usuario_id"]) ? $_POST["usuario_id"] : '';
                $parcial1 = isset($_POST["Parcial1"]) ? $_POST["Parcial1"] : 0;
                $parcial2 = isset($_POST["Parcial2"]) ? $_POST["Parcial2"] : 0;
                $parcial3 = isset($_POST["Parcial3"]) ? $_POST["Parcial3"] : 0;
                $parcial4 = isset($_POST["Parcial4"]) ? $_POST["Parcial4"] : 0;
                $final = ($parcial1 + $parcial2 + $parcial3 + $parcial4) / 4;

                // Redondear la nota final
                if (($final - floor($final)) >= 0.5) {
                    $final = ceil($final); // Redondea hacia arriba si el decimal es .5 o mayor
                } else {
                    $final = floor($final); // Redondea hacia abajo si el decimal es menor a .5
                }

                // Validación de notas
                if (
                    !is_numeric($parcial1) || !is_numeric($parcial2) || !is_numeric($parcial3) || !is_numeric($parcial4) ||
                    $parcial1 > 20 || $parcial2 > 20 || $parcial3 > 20 || $parcial4 > 20
                ) {
                    echo "Error: Asegúrese de que todas las notas son números válidos y no mayores a 20.";
                    exit;
                }

                // Obtener semestre del estudiante
                $sql_semestre = "SELECT semestre FROM estudiantes WHERE id_usuario = ?";
                $stmt_semestre = $conn->prepare($sql_semestre);
                $stmt_semestre->bind_param("i", $num_lista);
                $stmt_semestre->execute();
                $result_semestre = $stmt_semestre->get_result();
                if ($result_semestre->num_rows > 0) {
                    $semestre = $result_semestre->fetch_assoc()["semestre"];
                } else {
                    $semestre = null; // Manejar el caso donde no se encuentra el semestre
                }

                if ($accion == "guardar") {
                    // Verificar si el usuario ya existe en la tabla Notas
                    $check_sql = "SELECT * FROM notas WHERE usuario_id = ? AND materia_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("ii", $num_lista, $materia_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows > 0) {
                        // Si existe, actualizamos el registro
                        $update_sql = "UPDATE notas SET Parcial1 = ?, Parcial2 = ?, Parcial3 = ?, Parcial4 = ?, Final = ?, semestre = ? WHERE usuario_id = ? AND materia_id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("ddddiiii", $parcial1, $parcial2, $parcial3, $parcial4, $final, $semestre, $num_lista, $materia_id);
                        if ($update_stmt->execute() !== TRUE) {
                            echo "Error al actualizar el registro: " . $update_stmt->error;
                        }
                    } else {
                        // Si no existe, insertamos un nuevo registro
                        $insert_sql = "INSERT INTO notas (usuario_id, Parcial1, Parcial2, Parcial3, Parcial4, Final, semestre, materia_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $insert_stmt = $conn->prepare($insert_sql);
                        $insert_stmt->bind_param("iiiiiiii", $num_lista, $parcial1, $parcial2, $parcial3, $parcial4, $final, $semestre, $materia_id);
                        if ($insert_stmt->execute() !== TRUE) {
                            echo "Error al insertar el registro: " . $insert_stmt->error;
                        }
                    }
                }
            }

            // Consulta SQL para obtener los estudiantes inscritos en la materia
            $sql_inscritos = "SELECT id_estudiante FROM inscripciones WHERE id_materia = ?";
            $stmt_inscritos = $conn->prepare($sql_inscritos);
            $stmt_inscritos->bind_param("i", $materia_id);
            $stmt_inscritos->execute();
            $result_inscritos = $stmt_inscritos->get_result();

            // Verificar si la consulta fue exitosa
            if ($result_inscritos === false) {
                die("Error en la consulta SQL a inscripciones: " . $conn->error);
            }

            // Almacenar los IDs de estudiantes en un array
            $estudiantes = [];
            while ($row_inscritos = $result_inscritos->fetch_assoc()) {
                $estudiantes[] = $row_inscritos["id_estudiante"];
            }

            // Consulta SQL para obtener los datos de los estudiantes inscritos y sus notas
            if (!empty($estudiantes)) {
                $in = str_repeat('?,', count($estudiantes) - 1) . '?';
                $sql_datos = "SELECT datos_usuario.usuario_id, datos_usuario.cedula, datos_usuario.Nombres, datos_usuario.Apellidos, notas.Parcial1, notas.Parcial2, notas.Parcial3, notas.Parcial4, notas.Final
                          FROM datos_usuario 
                          LEFT JOIN notas ON datos_usuario.usuario_id = notas.usuario_id AND notas.materia_id = ?
                          LEFT JOIN estudiantes ON datos_usuario.usuario_id = estudiantes.id_usuario
                          WHERE datos_usuario.usuario_id IN ($in)";
                $stmt_datos = $conn->prepare($sql_datos);
                $types = str_repeat('i', count($estudiantes) + 1);
                $params = array_merge([$materia_id], $estudiantes);
                $stmt_datos->bind_param($types, ...$params);
                $stmt_datos->execute();
                $result_datos = $stmt_datos->get_result();

                // Verificar si la consulta fue exitosa
                if ($result_datos === false) {
                    die("Error en la consulta SQL a datos_usuario: " . $conn->error);
                }

                // Mostrar los datos en una tabla HTML
                echo "<table>
                <tr>
                    <th class='th'>Número de Lista</th>
                    <th class='th'>Nombres</th>
                    <th class='th'>Apellidos</th>
                    <th class='th'>Cédula</th>
                    <th class='th'>Parcial 1</th>
                    <th class='th'>Parcial 2</th>
                    <th class='th'>Parcial 3</th>
                    <th class='th'>Parcial 4</th>
                    <th class='th'>Final</th>
                    <th class='th'>Acción</th>
                </tr>";

                while ($row_datos = $result_datos->fetch_assoc()) {
                    $parcial1 = isset($row_datos["Parcial1"]) ? number_format($row_datos["Parcial1"], 2) : 0;
                    $parcial2 = isset($row_datos["Parcial2"]) ? number_format($row_datos["Parcial2"], 2) : 0;
                    $parcial3 = isset($row_datos["Parcial3"]) ? number_format($row_datos["Parcial3"], 2) : 0;
                    $parcial4 = isset($row_datos["Parcial4"]) ? number_format($row_datos["Parcial4"], 2) : 0;
                    $final = isset($row_datos["Final"]) ? number_format($row_datos["Final"], 2) : 0;

                    echo "<tr id='" . htmlspecialchars($row_datos["usuario_id"]) . "'>
            <td>" . htmlspecialchars($row_datos["usuario_id"]) . "</td>
            <td>" . htmlspecialchars($row_datos["Nombres"]) . "</td>
            <td>" . htmlspecialchars($row_datos["Apellidos"]) . "</td>
            <td>" . htmlspecialchars($row_datos["cedula"]) . "</td>
            <td>$parcial1</td>
            <td>$parcial2</td>
            <td>$parcial3</td>
            <td>$parcial4</td>
            <td>$final</td>
            <td class='td button-cell'>
                <div>
                    <button onclick='editRow(" . htmlspecialchars($row_datos["usuario_id"]) . ")'>Editar</button>
                </div>
                <div>
                    <button onclick='uploadPartial(" . htmlspecialchars($row_datos["usuario_id"]) . ")'>Cargar Parcial</button>
                </div>
            </td>
        </tr>";
                }
                echo "</table>";

            } else {
                echo "No hay estudiantes inscritos para esta materia.";
            }


            $conn->close();
            ?>
        </div>
    </div>

    <script>
        // Solo JS exclusivo para edición de notas y carga de parciales
        // (editRow, saveRow, addNewRow, saveNewRow, uploadPartial, etc.)
        // ... (todo el JS de edición y carga de parciales que ya está en el archivo) ...
    </script>

</body>

</html>