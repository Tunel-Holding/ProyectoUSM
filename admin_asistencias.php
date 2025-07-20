<?php
include 'comprobar_sesion.php';
require_once 'authGuard.php';
$auth = AuthGuard::getInstance();
$auth->checkAccess(AuthGuard::NIVEL_ADMIN);
include 'conexion.php';

// Obtener todas las materias y sus secciones (asumiendo columna 'seccion' en la tabla materias)
$query = "SELECT id, nombre, seccion FROM materias ORDER BY nombre, seccion";
$result = $conn->query($query);

$materias = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $materia_id = $row['id'];
        $nombre = $row['nombre'];
        $seccion = $row['seccion'];

        if (!isset($materias[$materia_id])) {
            $materias[$materia_id] = [
                'nombre' => $nombre,
                'secciones' => []
            ];
        }
        // Evitar duplicados de secciones
        if (!in_array($seccion, $materias[$materia_id]['secciones'])) {
            $materias[$materia_id]['secciones'][] = $seccion;
        }
    }
}
$conn->close();
?>


<?php include 'navAdmin.php'; ?>
<head>
    <link rel="stylesheet" href="css/admin_asistencias.css">
</head>
<main class="main-content">
    <h2 class="section-title">Gestión de Asistencias</h2>
    <?php if (empty($materias)): ?>
        <p>No hay materias registradas.</p>
    <?php else: ?>
        <div class="materias-list">
            <?php foreach ($materias as $materia_id => $materia): ?>
                <div class="materia-card">
                    <h3><?php echo htmlspecialchars($materia['nombre']); ?></h3>
                    <?php if (!empty($materia['secciones'])): ?>
                        <ul class="secciones-list">
                            <?php foreach ($materia['secciones'] as $seccion): ?>
                                <li class="seccion-item">
                                    <span class="seccion-nombre"><?php echo htmlspecialchars($seccion); ?></span>
                                    <form action="asistencia_seccion.php" method="get" class="asistencia-form">
                                        <input type="hidden" name="materia_id" value="<?php echo $materia_id; ?>">
                                        <input type="hidden" name="seccion" value="<?php echo htmlspecialchars($seccion); ?>">
                                        <button type="submit" class="btn-primary">Asistencia</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="sin-secciones">Sin secciones registradas.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>