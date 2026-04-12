<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. PHP OK<br>";

require __DIR__ . '/includes/config.php';
echo "2. Config OK<br>";

require __DIR__ . '/includes/functions.php';
echo "3. Functions OK<br>";

echo "4. getKomkalProductoById exists: " . (function_exists('getKomkalProductoById') ? 'YES' : 'NO') . "<br>";
echo "5. getKomkalProductosNoVinculats exists: " . (function_exists('getKomkalProductosNoVinculats') ? 'YES' : 'NO') . "<br>";
echo "6. getProductoUrlUniversal exists: " . (function_exists('getProductoUrlUniversal') ? 'YES' : 'NO') . "<br>";

$prod = getKomkalProductoById('vinos', 1895);
echo "7. Query result: " . ($prod ? $prod['nombre'] . " - PVP: " . $prod['precio_venta'] : 'NULL (id 1895 not found)') . "<br>";

// Try first available
$r = mysqli_query($GLOBALS['bd'], "SELECT id, nombre FROM komkal_vinos WHERE id_producto_match IS NULL AND disponible=1 LIMIT 1");
$row = mysqli_fetch_assoc($r);
echo "8. First unmatched: id=" . ($row['id'] ?? '?') . " " . ($row['nombre'] ?? '?') . "<br>";

if ($row) {
    $prod2 = getKomkalProductoById('vinos', $row['id']);
    echo "9. getKomkalProductoById({$row['id']}): " . ($prod2 ? 'OK - ' . $prod2['nombre'] : 'NULL') . "<br>";
}
