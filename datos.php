<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_USUARIO);

include 'comprobar_sesion.php';

// Conexión a la base de datos
include 'conexion.php';

actualizar_actividad();

// Obtener el ID del usuario de la sesión
$id_usuario = $_SESSION['idusuario'];

// Obtener los datos del usuario
$sql = "SELECT cedula, nombres, apellidos, sexo, telefono, correo, direccion 
        FROM datos_usuario 
        WHERE usuario_id = ?";

// Preparar la sentencia
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error en la consulta: " . $conn->error);
}
$stmt->bind_param("i", $id_usuario); // Enlazar el ID del usuario

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

// Obtener la foto del usuario usando prepared statement
$sql_foto = "SELECT foto FROM fotousuario WHERE id_usuario = ?";
$stmt_foto = $conn->prepare($sql_foto);
$foto = "css/perfil.png"; // Foto por defecto

if ($stmt_foto) {
    $stmt_foto->bind_param("i", $id_usuario);
    $stmt_foto->execute();
    $result_foto = $stmt_foto->get_result();
    
    if ($result_foto->num_rows > 0) {
        $row_foto = $result_foto->fetch_assoc();
        $foto = $row_foto['foto'];
    }
    $stmt_foto->close();
}

// Verificar si se encontraron datos del usuario
if ($result->num_rows > 0) {
    $estudiante = $result->fetch_assoc();
} else { 
    // Redirigir a la página de llenado de datos si no hay datos del usuario 
    header("Location: llenar_datos.php");
    exit();
}

actualizar_actividad();
$conn->close(); // Cerrar la conexión

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="icon" href="css/icono.png" type="image/png"> -->
     <link rel="icon" href="css/logounihubblanco.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/principalalumnostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Datos - UniHub</title>
    <style>
        /* Sección de Foto de Perfil */
        .perfil-container {
            display: flex;
            flex-direction: column; /* Apila la imagen y el botón verticalmente */
            align-items: center;
            margin-bottom: 40px; /* Más espacio debajo de la sección de perfil */
        }

        .perfil-foto {
            width: 180px; /* Foto ligeramente más pequeña */
            height: 180px;
            border-radius: 50%;
            object-fit: cover; /* Asegura que la imagen cubra el área sin distorsión */
            border: 5px solid #ffd700; /* Borde dorado alrededor de la foto */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* Sombra más suave para la foto */
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .perfil-boton {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            cursor: pointer;
            width: 400px;
            height: 150px;
            transition: all 0.3s ease-in-out;
            font-size: 40px;
        }

        .perfil-foto:hover {
            transform: scale(1.05); /* Ligeramente más grande al pasar el ratón */
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3); /* Sombra más pronunciada al pasar el ratón */
        }
        .contenedor-principal {
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Alinea los elementos al inicio para que el contenido fluya naturalmente */
            padding: 20px;
            min-height: calc(100vh - 120px); /* Ajusta según la altura del encabezado/pie de página */
        }

        /* Contenedor Principal de Datos (wecontainer) */
        .wecontainer {
            background: #ffffff; /* Fondo blanco para el contenedor de datos */
            padding: 40px; /* Aumento del padding para más espacio */
            border-radius: 12px; /* Esquinas más redondeadas */
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); /* Sombra más suave y extendida */
            max-width: 900px; /* Ancho máximo ajustado para una mejor legibilidad */
            width: 100%; /* Asegura que ocupe todo el ancho dentro de su ancho máximo */
            border-top: 8px solid #004c97; /* Borde superior azul, más grueso y prominente */
            border-bottom: 8px solid #004c97; /* Borde inferior azul, más grueso y prominente */
            border-left: none !important; /* Elimina los bordes laterales para una apariencia más limpia */
            border-right: none !important; /* Elimina los bordes laterales para una apariencia más limpia */
            box-sizing: border-box; /* Incluye padding y borde en el ancho y alto total del elemento */
            margin-top: 30px; /* Espacio desde la parte superior */
        }


        .wecontainer h1 {
            text-align: center;
            color: #004c97; /* Azul oscuro */
            margin-bottom: 30px; /* Espacio debajo del título */
            font-size: 2.2em; /* Título más grande */
            font-weight: 700;
            font-family: 'Poppins', sans-serif; /* Aplica Poppins al título también */
        }

        /* Lista de Datos (UL y LI) */
        .wecontainer ul {
            list-style: none;
            padding: 0;
            display: grid; /* Usa CSS Grid para un diseño más estructurado */
            grid-template-columns: 1fr 1fr; /* Dos columnas */
            gap: 20px; /* Espacio entre los elementos de la cuadrícula */
            margin-bottom: 30px; /* Espacio antes del botón */
        }

        .wecontainer li {
            background: #f8f9fa; /* Fondo gris muy claro para cada elemento de dato */
            padding: 18px 25px; /* Padding cómodo */
            border-radius: 8px; /* Esquinas ligeramente redondeadas */
            border: 1px solid #e0e0e0; /* Borde sutil */
            display: flex;
            flex-direction: row; /* **Alinea los ítems en fila (horizontalmente)** */
            justify-content: space-between; /* Espacio entre el strong y el span */
            align-items: center; /* Centra verticalmente */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Sombra muy ligera */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            font-family: 'Poppins', sans-serif; /* Aplica Poppins a los elementos de la lista */
        }

        .wecontainer li:hover {
            transform: translateY(-3px); /* Efecto de ligero levantamiento al pasar el ratón */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Sombra más visible al pasar el ratón */
        }

        .wecontainer li strong {
            color: #004c97; /* Azul oscuro para las etiquetas */
            font-size: 0.9em; /* Fuente ligeramente más pequeña para la etiqueta */
            /* margin-bottom: 5px; REMOVED: Ya no es necesario con flex-direction: row */
            font-weight: 600;
            white-space: nowrap; /* Evita que el texto de la etiqueta se rompa */
            margin-right: 10px; /* Espacio entre la etiqueta y el valor */

        }

        .wecontainer li span {
            color: #555; /* Gris más oscuro para los valores */
            font-size: 1.1em; /* Fuente ligeramente más grande para el valor */
            word-wrap: break-word; /* Asegura que las palabras largas se rompan */
            white-space: normal; /* Permite que el texto se ajuste naturalmente */
            text-align: right; /* Alinea el valor a la derecha si hay espacio */
            flex-grow: 1; /* Permite que el span ocupe el espacio restante */
        }

        .wecontainer a {
            font-weight: 700;
        }

        /* Estilos del Botón */
        .button {
            display: block;
            width: fit-content; /* Ajusta el ancho al contenido */
            margin: 0 auto; /* Centra el botón */
            padding: 12px 30px; /* Más padding para un botón más grande */
            background-color: #ffd700; /* Dorado */
            color: #004c97; /* Texto azul oscuro */
            border: none;
            border-radius: 30px; /* Botón en forma de píldora */
            cursor: pointer;
            text-align: center;
            font-size: 1.1em; /* Tamaño de fuente más grande para el botón */
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            font-family: 'Poppins', sans-serif; /* Aplica Poppins al botón */
        }
        .button:hover {
            background-color: #ffcc00; /* Dorado más oscuro al pasar el ratón */
            transform: translateY(-2px); /* Ligero levantamiento */
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2); /* Sombra más pronunciada */
        }

        /* Ajustes Responsivos */
        @media (max-width: 768px) {
            .wecontainer ul {
                grid-template-columns: 1fr; /* Una sola columna en pantallas más pequeñas */
            }

            .wecontainer {
                padding: 25px;
                margin-top: 20px;
            }

            .wecontainer h1 {
                font-size: 1.8em;
            }

            .perfil-foto {
                width: 150px;
                height: 150px;
            }
        }

        @media (max-width: 480px) {
            .wecontainer {
                padding: 15px;
            }

            .wecontainer h1 {
                font-size: 1.5em;
            }

            .wecontainer li {
                padding: 15px;
                font-size: 1em;
            }

            .button {
                padding: 10px 20px;
                font-size: 1em;
            }
        }

        .soporte-flotante-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .soporte-flotante {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            background-color: #446ad3;
            padding: 12px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            overflow: hidden;
            width: 60px;            /* ✅ suficiente para mostrar solo el ícono */
            height: 50px;
            transition: width 0.4s ease, background-color 0.3s ease;
        }


        .soporte-flotante:hover {
            width: 210px; /* ✅ se expande hacia la izquierda */
            background-color: #365ac0;
        }

        .soporte-mensaje {
            flex: 1; /* ✅ ocupa todo el espacio disponible */
            opacity: 0;
            white-space: nowrap;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            transform: translateX(30px); /* animación desde la derecha */
            transition: transform 0.4s ease, opacity 0.4s ease;
            text-align: left; /* ✅ texto alineado a la izquierda */
            margin-right: auto;
            font-family: 'Poppins', sans-serif;
        }

        .soporte-flotante:hover .soporte-mensaje {
            opacity: 1;
            transform: translateX(0);
        }

        .soporte-flotante img {
            width: 30px;
            height: 30px;
            filter: brightness(0) invert(1);
            flex-shrink: 0;
            z-index: 2;
        }

        /* Contenedor principal en modo oscuro */
        body.dark-mode .wecontainer {
            background-color: #292942;
            border-top: 8px solid #ffd700; /* Mantiene el dorado en modo oscuro */
            border-bottom: 8px solid #ffd700;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
        }

        /* Título en modo oscuro */
        body.dark-mode .wecontainer h1 {
            color: #ffd700;
        }

        /* Cada cuadro de datos en modo oscuro */
        body.dark-mode .wecontainer li {
            background-color: #3a3a55;
            border: 1px solid #555574;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        /* Etiquetas y contenido de los datos */
        body.dark-mode .wecontainer li strong {
            color: #f0f0f0;
        }

        body.dark-mode .wecontainer li span {
            color: #d4d4d4;
        }

        /* Botón en modo oscuro */
        body.dark-mode .button {
            background-color: #ffd700;
            color: #292942;
        }

        body.dark-mode .button:hover {
            background-color: #ffcc00;
        }

    </style>
</head>

<body>
    
    <div class="cabecera">

        <button type="button" id="logoButton">
           <!-- <img src="css/logoazul.png" alt="Logo"> -->

            <img src="css/menu.png" alt="Menú" class="logo-menu">
            
        </button>
        <div class="logoempresa">
            <img src="css/logounihubblanco.png" alt="Logo" class="logounihub">
            <p>UniHub</p>
        </div>

    </div>

    <?php include 'menu_alumno.php'; ?>

    <div class="contenedor-principal">
        <div class="wecontainer">
            <h1>Datos del estudiante</h1>
            <div class="perfil-container">
        <img src="<?php echo $foto; ?>" alt="Foto de perfil" class="perfil-foto" id="perfilFoto">
       
    </div class="datos-grid">
            <ul>
                <li><strong>Número de Cédula:</strong> <span><?php echo $estudiante['cedula']; ?></span></li>
                <li><strong>Nombres:</strong> <span><?php echo $estudiante['nombres']; ?></span></li>
                <li><strong>Apellidos:</strong> <span><?php echo $estudiante['apellidos']; ?></span></li>
                <li><strong>Sexo:</strong> <span><?php echo $estudiante['sexo']; ?></span></li>
                <li><strong>Teléfono:</strong> <span><?php echo $estudiante['telefono']; ?></span></li>
                <li><strong>Correo:</strong> <span>&nbsp;&nbsp;&nbsp;<?php echo $estudiante['correo']; ?></span></li>
                <li><strong>Dirección:</strong> <span><?php echo $estudiante['direccion']; ?></span></li>
            </ul>
            <a href="modificar_datos.php" class="button">Modificar datos</a>
        </div>
    </div>

    <script>
         document.getElementById('editarPerfilBoton').addEventListener('click', function () {
            alert('La foto debe ser cuadrada (igual de altura y anchura).');
            document.getElementById('fotoInput').click();
        });

        document.getElementById('fotoInput').addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const img = new Image();
                img.onload = function () {
                    if (img.width !== img.height) {
                        alert('La foto debe ser cuadrada (igual de altura y anchura).');
                    } else {
                        document.getElementById('uploadForm').submit();
                    }
                };
                img.src = URL.createObjectURL(file);
            }
        });
    </script>
    <div class="soporte-flotante-container">
        <a href="contacto.php" class="soporte-flotante" title="Soporte">
            <span class="soporte-mensaje">Contacto soporte</span>
            <img src="css/audifonos-blanco.png" alt="Soporte">
        </a>
    </div>
</body>

</html>