<?php
/**
 * Script para crear/verificar usuario admin
 * ELIMINAR DESPUÉS DE USAR
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Setup Usuario Admin</h1>";

// Conectar a BD - ruta correcta
require_once __DIR__ . "/../connexio/connexio.php";

if (!$bd) {
    die("<p style='color:red'>Error: No se pudo conectar a la base de datos</p>");
}

echo "<p style='color:green'>Conexión a BD: OK</p>";

// Verificar estructura de tabla usuarios
$result = mysqli_query($bd, "DESCRIBE usuarios");
echo "<h3>Estructura tabla usuarios:</h3><ul>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<li><strong>{$row['Field']}</strong> - {$row['Type']}</li>";
}
echo "</ul>";

// Ver usuarios existentes
$result = mysqli_query($bd, "SELECT id, nombre, usuario, password FROM usuarios");
echo "<h3>Usuarios existentes:</h3>";
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Password</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td>{$row['usuario']}</td>";
        echo "<td>" . substr($row['password'], 0, 20) . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay usuarios</p>";
}

// Crear o actualizar usuario
echo "<h3>Creando/Actualizando usuario...</h3>";

$nombre = 'Admin';
$usuario = 'admin';
$password = 'admin123';

// Verificar si existe
$check = mysqli_query($bd, "SELECT id FROM usuarios WHERE usuario = '$usuario'");
if (mysqli_num_rows($check) > 0) {
    $sql = "UPDATE usuarios SET password = '$password', nombre = '$nombre' WHERE usuario = '$usuario'";
    $action = "actualizado";
} else {
    $sql = "INSERT INTO usuarios (nombre, usuario, password) VALUES ('$nombre', '$usuario', '$password')";
    $action = "creado";
}

if (mysqli_query($bd, $sql)) {
    echo "<p style='color:green; font-size: 18px;'>Usuario $action correctamente!</p>";
    echo "<div style='background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h2>Credenciales de acceso:</h2>";
    echo "<p><strong>Usuario:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "</div>";
} else {
    echo "<p style='color:red'>Error: " . mysqli_error($bd) . "</p>";
}

// Verificar que se guardó correctamente
$verify = mysqli_query($bd, "SELECT * FROM usuarios WHERE usuario = '$usuario' AND password = '$password'");
if (mysqli_num_rows($verify) > 0) {
    echo "<p style='color:green; font-weight: bold;'>Verificación: Usuario encontrado correctamente en BD</p>";
} else {
    echo "<p style='color:red'>Verificación: FALLO - El usuario no se encuentra con esas credenciales</p>";
}

echo "<hr>";
echo "<p><a href='index.php' style='font-size: 18px;'>Ir al Login</a></p>";
echo "<p style='color:red'><strong>IMPORTANTE:</strong> Elimina este archivo después de usarlo!</p>";
?>
