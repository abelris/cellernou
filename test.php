<?php
/**
 * Archivo de diagnóstico - ELIMINAR EN PRODUCCIÓN
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de diagnóstico</h1>";

// Test 1: PHP funciona
echo "<p>✓ PHP funciona correctamente</p>";

// Test 2: Archivos existen
$files = [
    'includes/config.php',
    'includes/functions.php',
    'includes/seo.php',
    'routes/Router.php',
    'routes/web.php',
    'controllers/HomeController.php',
    'includes/header.php',
    'includes/footer.php'
];

echo "<h2>Verificando archivos:</h2>";
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<p>✓ $file existe</p>";
    } else {
        echo "<p style='color:red'>✗ $file NO existe</p>";
    }
}

// Test 3: Conexión a BD
echo "<h2>Conexión a Base de Datos:</h2>";
try {
    require_once __DIR__ . '/distribucio/connexio/connexio.php';
    if ($bd) {
        echo "<p>✓ Conexión a BD exitosa</p>";

        // Test query
        $result = mysqli_query($bd, "SELECT COUNT(*) as total FROM productos WHERE activo = 1");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "<p>✓ Productos activos: " . $row['total'] . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error BD: " . $e->getMessage() . "</p>";
}

// Test 4: Cargar config
echo "<h2>Cargando configuración:</h2>";
try {
    require_once __DIR__ . '/includes/config.php';
    echo "<p>✓ Config cargada</p>";
    echo "<p>SITE_URL: " . SITE_URL . "</p>";
    echo "<p>getBaseUrl(): " . getBaseUrl() . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 5: Router
echo "<h2>Cargando Router:</h2>";
try {
    require_once __DIR__ . '/routes/Router.php';
    require_once __DIR__ . '/routes/web.php';
    echo "<p>✓ Router cargado</p>";
    echo "<p>Router::url('home'): " . Router::url('home') . "</p>";
    echo "<p>Router::url('vinos'): " . Router::url('vinos') . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 6: CSS
echo "<h2>Archivos estáticos:</h2>";
$statics = ['css/style.css', 'js/main.js', 'images/no-image.svg'];
foreach ($statics as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<p>✓ $file existe</p>";
    } else {
        echo "<p style='color:red'>✗ $file NO existe</p>";
    }
}

echo "<hr>";
echo "<p><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";

echo "<hr>";
echo "<p><a href='/cellernou/'>Ir a la web</a></p>";
?>
