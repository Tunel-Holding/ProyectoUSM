<?php
// Archivo de prueba para verificar la base de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Prueba de Conexión a Base de Datos</h2>";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectousm";

// Crear conexión
$db = mysqli_connect($servername, $username, $password, $dbname);

// Verificar conexión
if (!$db) {
    echo "<p style='color: red;'>Error de conexión: " . mysqli_connect_error() . "</p>";
    exit();
}

echo "<p style='color: green;'>✓ Conexión exitosa a la base de datos</p>";

// Verificar que las tablas existen
$tablas = ['usuarios', 'estudiantes'];

foreach ($tablas as $tabla) {
    $sql = "SHOW TABLES LIKE '$tabla'";
    $result = mysqli_query($db, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✓ Tabla '$tabla' existe</p>";
        
        // Mostrar estructura de la tabla
        $sql = "DESCRIBE $tabla";
        $result = mysqli_query($db, $sql);
        
        echo "<h3>Estructura de la tabla '$tabla':</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>✗ Tabla '$tabla' NO existe</p>";
    }
}

// Verificar permisos de inserción
echo "<h3>Prueba de inserción:</h3>";

$nombre_test = "test_user_" . time();
$email_test = "test" . time() . "@test.com";
$password_test = password_hash("test123", PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre_usuario, email, contrasena, nivel_usuario) VALUES (?, ?, ?, 'usuario')";
$stmt = $db->prepare($sql);

if ($stmt) {
    $stmt->bind_param("sss", $nombre_test, $email_test, $password_test);
    
    if ($stmt->execute()) {
        $user_id = $db->insert_id;
        echo "<p style='color: green;'>✓ Inserción en tabla 'usuarios' exitosa. ID: $user_id</p>";
        
        // Probar inserción en estudiantes
        $carrera = "Ingenieria en Sistemas";
        $semestre = 1;
        $creditos = 20;
        
        $sql = "INSERT INTO estudiantes (id_usuario, carrera, semestre, creditosdisponibles) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("isis", $user_id, $carrera, $semestre, $creditos);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✓ Inserción en tabla 'estudiantes' exitosa</p>";
            } else {
                echo "<p style='color: red;'>✗ Error al insertar en 'estudiantes': " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Error al preparar inserción en 'estudiantes': " . $db->error . "</p>";
        }
        
        // Limpiar datos de prueba
        $sql = "DELETE FROM estudiantes WHERE id_usuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        echo "<p style='color: blue;'>✓ Datos de prueba eliminados</p>";
        
    } else {
        echo "<p style='color: red;'>✗ Error al insertar en 'usuarios': " . $stmt->error . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Error al preparar inserción en 'usuarios': " . $db->error . "</p>";
}

mysqli_close($db);
echo "<p><strong>Prueba completada.</strong></p>";
?> 