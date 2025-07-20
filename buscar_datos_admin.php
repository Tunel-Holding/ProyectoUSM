<?php
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="css/icono.png" type="image/png" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/principaladministracion.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@100..1000&family=Noto+Sans+KR:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
      rel="stylesheet"
    />
    <title>Datos - USM</title>

    <style>
      @import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap");

      .container {
        max-width: 1200px; /* Ancho máximo aumentado */
        width: 90%; /* Ancho del contenedor ajustado al 90% de la pantalla */
        background-color: rgba(68, 106, 211, 1); /* Fondo celeste del cuadro */
        padding: 60px; /* Espaciado interno aumentado */
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-width: 10px 2px 10px 2px; /* Bordes superiores e inferiores gruesos, laterales finos */
        border-style: solid;
        border-color: rgba(255, 255, 255, 1);; /* Borde amarillo */
        margin: 20px auto; /* Margen superior e inferior aumentados */
        display: flex;
        flex-direction: column;
        height: auto;
      }

      h1 {
        color: rgba(255, 255, 255, 1); /* Color celeste oscuro para el título */
        text-align: center;
        margin-bottom: 24px;
        font-size: 70px; /* Tamaño del texto aumentado */
        font-weight: bold; /* Negrita */
        font-family: 'Roboto', sans-serif; /* Aplica la fuente Roboto */
      }

      label {
        display: block;
        margin-bottom: 12px; /* Espaciado inferior aumentado */
        font-weight: 700; /* Negrita */
        color: rgba(255, 255, 255, 1);; /* Color celeste para las etiquetas */
        font-size: 24px; /* Tamaño del texto aumentado */
      }

      input[type="text"] {
        width: 100%;
        padding: 16px; /* Espaciado interno aumentado */
        margin-bottom: 24px; /* Espaciado inferior aumentado */
        border: 1px solid #0056b3; /* Borde celeste */
        border-radius: 8px;
        font-size: 20px; /* Tamaño del texto aumentado */
        box-sizing: border-box;
        font-family: "Roboto", sans-serif; /* Fuente Roboto */
      }

      input[type="submit"] {
        background-color: #0056b3; /* Fondo celeste oscuro */
        color: #fff;
        padding: 16px; /* Espaciado interno aumentado */
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 20px; /* Tamaño del texto aumentado */
        width: 100%;
        transition: background-color 0.3s ease;
        font-family: "Roboto", sans-serif; /* Fuente Roboto */
      }

      input[type="submit"]:hover {
        background-color: #003f7f; /* Fondo celeste más oscuro al pasar el mouse */
      }
    </style>
  </head>
  <body>
    <?php include 'navAdmin.php'; ?>
    <div class="container">
      <h1>Buscar Usuario</h1>
      <form action="datos_admin.php" method="get">
        <label for="query"
          >Introduzca la cédula del usuario que quiera consultar:</label
        >
        <input
          type="text"
          id="query"
          name="query"
          placeholder="Buscar por cédula..."
        />
        <input type="submit" value="Buscar" />
      </form>
    </div>
    <script>
      function goBack() {
        window.history.back();
      }
      const contenedor = document.getElementById("contenedor");
      const botonIzquierdo = document.getElementById("boton-izquierdo");
      const botonDerecho = document.getElementById("boton-derecho");
      botonIzquierdo.addEventListener("click", () => {
        contenedor.scrollBy({ left: -94, behavior: "smooth" });
      });
      botonDerecho.addEventListener("click", () => {
        contenedor.scrollBy({ left: 94, behavior: "smooth" });
      });

      document.getElementById("logoButton").addEventListener("click", () => {
        document.getElementById("menu").classList.toggle("toggle");
        event.stopPropagation();
      });
      document.addEventListener("click", function (event) {
        if (
          !container.contains(event.target) &&
          container.classList.contains("toggle")
        ) {
          container.classList.remove("toggle");
        }
      });
      document.addEventListener("click", function (event) {
        var div = document.getElementById("menu");
        if (!div.contains(event.target)) {
          div.classList.remove("toggle");
        }
      });
      document
        .getElementById("switchtema")
        .addEventListener("change", function () {
          if (this.checked) {
            document.body.classList.add("dark-mode");
            localStorage.setItem("theme", "dark");
          } else {
            document.body.classList.remove("dark-mode");
            localStorage.setItem("theme", "light");
          }
        });

      // Aplicar la preferencia guardada del usuario al cargar la p谩gina
      window.addEventListener("load", function () {
        const theme = localStorage.getItem("theme");
        if (theme === "dark") {
          document.body.classList.add("dark-mode");
          document.getElementById("switchtema").checked = true;
        }
      });

      function redirigir(url) {
        window.location.href = url;
        // Cambia esta URL a la página de destino
      }
      window.onload = function () {
        document
          .getElementById("inicio")
          .addEventListener("click", function () {
            redirigir("pagina_administracion.php");
          });
        document.getElementById("datos").addEventListener("click", function () {
          redirigir("buscar_datos_admin.html");
        });
        document
          .getElementById("profesor")
          .addEventListener("click", function () {
            redirigir("admin_profesores.php");
          });
        document
          .getElementById("alumno")
          .addEventListener("click", function () {
            redirigir("admin_alumnos.php");
          });
        document
          .getElementById("materias")
          .addEventListener("click", function () {
            redirigir("admin_materias.php");
          });
      };
    </script>
  </body>
</html>
