<?php
include 'comprobar_sesion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);
include 'conexion.php';

// Obtener parámetros GET
$materia_id = isset($_GET['materia_id']) ? intval($_GET['materia_id']) : 0;
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';

// Validar parámetros
if ($materia_id <= 0 || empty($seccion)) {
    echo '<p>Parámetros inválidos.</p>';
    exit;
}

// Obtener nombre de la materia
$stmt = $conn->prepare('SELECT nombre FROM materias WHERE id = ?');
$stmt->bind_param('i', $materia_id);
$stmt->execute();
$stmt->bind_result($materia_nombre);
$stmt->fetch();
$stmt->close();

$query = "SELECT d.usuario_id AS id, d.nombres, d.apellidos FROM datos_usuario d
          JOIN inscripciones i ON d.usuario_id = i.id_estudiante
          JOIN materias m ON i.id_materia = m.id
          WHERE i.id_materia = ? AND m.seccion = ?
          ORDER BY d.apellidos, d.nombres";
$stmt = $conn->prepare($query);
$stmt->bind_param('is', $materia_id, $seccion);
$stmt->execute();
$result = $stmt->get_result();

$estudiantes = [];
while ($row = $result->fetch_assoc()) {
    $estudiantes[] = $row;
}
$stmt->close();
$conn->close();
?>

<?php include 'navAdmin.php'; ?>
<head>
    <link rel="stylesheet" href="css/admin_asistencias.css">
</head>
<main class="main-content">
    <h2 class="section-title">Asistencia - <?php echo htmlspecialchars($materia_nombre); ?> (Sección <?php echo htmlspecialchars($seccion); ?>)</h2>
    <div style="text-align:center; margin-bottom:2rem;">
        <button type="button" class="btn-asistencia" id="clase-btn" onclick="toggleClase()">Iniciar clase</button>
    </div>
    <?php if (empty($estudiantes)): ?>
        <p>No hay estudiantes inscritos en esta materia y sección.</p>
    <?php else: ?>
        <table class="tabla-asistencia">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Asistencia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estudiantes as $idx => $estudiante): ?>
                    <tr>
                        <?php $nombre_simple = explode(' ', $estudiante['nombres'])[0]; ?>
                        <?php $apellido_simple = explode(' ', $estudiante['apellidos'])[0]; ?>
                        <td><?php echo htmlspecialchars($nombre_simple); ?></td>
                        <td><?php echo htmlspecialchars($apellido_simple); ?></td>
                        <td>
                            <button type="button" class="btn-asistencia" id="asistencia-btn-<?php echo $idx; ?>" onclick="toggleAsistencia(this)" disabled>Asistir</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <script>
        function toggleClase() {
            var btn = document.getElementById('clase-btn');
            var asistenciaBtns = document.querySelectorAll('.btn-asistencia');
            if (btn.textContent === 'Iniciar clase') {
                btn.textContent = 'Terminar clase';
                btn.classList.add('retirado');
                asistenciaBtns.forEach(function(b) {
                    if (b.id.startsWith('asistencia-btn-')) b.disabled = false;
                });
                // Crear archivo TXT con los nombres de los estudiantes, usando timestamp único
                var fecha = new Date();
                var dd = fecha.getDate().toString().padStart(2,'0');
                var mm = (fecha.getMonth()+1).toString().padStart(2,'0');
                var yyyy = fecha.getFullYear();
                var HH = fecha.getHours().toString().padStart(2,'0');
                var MM = fecha.getMinutes().toString().padStart(2,'0');
                var SS = fecha.getSeconds().toString().padStart(2,'0');
                var timestamp = dd + '-' + mm + '-' + yyyy + '_' + HH + '-' + MM + '-' + SS;
                window._asistenciaFile = 'lista_' + <?php echo $materia_id; ?> + '_' + '<?php echo preg_replace("/[^a-zA-Z0-9]/", "_", $seccion); ?>' + '_' + timestamp + '.txt';
                fetch('crear_lista_clase.php?materia_id=<?php echo $materia_id; ?>&seccion=<?php echo urlencode($seccion); ?>&file=' + window._asistenciaFile)
                    .then(response => response.text())
                    .then(data => {
                        // Opcional: mostrar mensaje de éxito
                        // alert('Archivo de clase creado');
                    });
            } else {
                btn.textContent = 'Iniciar clase';
                btn.classList.remove('retirado');
                asistenciaBtns.forEach(function(b) {
                    if (b.id.startsWith('asistencia-btn-')) b.disabled = true;
                    b.textContent = 'Asistir';
                    b.classList.remove('retirado');
                });
                var filas = document.querySelectorAll('.tabla-asistencia tbody tr');
                var materia_id = <?php echo $materia_id; ?>;
                var seccion = '<?php echo addslashes($seccion); ?>';
                var now = new Date();
                var hora = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
                var file = window._asistenciaFile;
                // Registrar R para todos los estudiantes SOLO si no tienen ninguna R
                fetch(file)
                    .then(response => response.text())
                    .then(txt => {
                        filas.forEach(function(fila) {
                            var nombre = fila.querySelector('td').textContent.trim();
                            var apellido = fila.querySelectorAll('td')[1].textContent.trim();
                            var estudiante = nombre + ' ' + apellido;
                            var regex = new RegExp('^' + estudiante.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, '\\$1') + '.*\(.*R.*\)', 'm');
                            if (!regex.test(txt)) {
                                registrarAsistencia(estudiante, materia_id, seccion, hora, 'R', file);
                            }
                        });
                    });
                // Registrar hora de cierre de clase
                fetch('registrar_asistencia.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'file=' + encodeURIComponent(file) + '&hora_fin=' + encodeURIComponent(now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0'))
                });
                // Generar PDF automáticamente
                setTimeout(function() {
                    window.open('generar_pdf_asistencia.php?file=' + encodeURIComponent(file), '_blank');
                    btn.textContent = 'Iniciar clase';
                }, 500);
            }
        }
        function toggleAsistencia(btn) {
            var nombre = btn.closest('tr').querySelector('td').textContent.trim();
            var apellido = btn.closest('tr').querySelectorAll('td')[1].textContent.trim();
            var estudiante = nombre + ' ' + apellido;
            var materia_id = <?php echo $materia_id; ?>;
            var seccion = '<?php echo addslashes($seccion); ?>';
            var now = new Date();
            var hora = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            var file = window._asistenciaFile;
            if (btn.textContent === 'Asistir') {
                btn.textContent = 'Retirar';
                btn.classList.add('retirado');
                registrarAsistencia(estudiante, materia_id, seccion, hora, 'A', file);
            } else {
                btn.textContent = 'Asistir';
                btn.classList.remove('retirado');
                registrarAsistencia(estudiante, materia_id, seccion, hora, 'R', file);
            }
        }

        function registrarAsistencia(estudiante, materia_id, seccion, hora, tipo, file) {
            // tipo: 'A' o 'R'
            fetch('registrar_asistencia.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'materia_id=' + encodeURIComponent(materia_id) +
                      '&seccion=' + encodeURIComponent(seccion) +
                      '&estudiante=' + encodeURIComponent(estudiante) +
                      '&hora=' + encodeURIComponent(hora) +
                      '&tipo=' + encodeURIComponent(tipo) +
                      (file ? ('&file=' + encodeURIComponent(file)) : '')
            });
        }
        </script>
    <?php endif; ?>
</main>
