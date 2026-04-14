<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Debug facturas</h3>";

echo "<p>PHP: " . phpversion() . "</p>";

// Test connexio
echo "<p>Loading connexio...</p>";
require_once dirname(dirname(dirname(__DIR__))) . '/connexio/connexio.php';
echo "<p>DB connected: " . ($bd ? 'YES' : 'NO') . "</p>";

// Test sql_mode
$r = mysqli_query($bd, "SELECT @@sql_mode as m");
$mode = mysqli_fetch_assoc($r)['m'];
echo "<p>sql_mode: <code>$mode</code></p>";

// Test pagos_factura exists
$r = mysqli_query($bd, "SHOW TABLES LIKE 'pagos_factura'");
echo "<p>pagos_factura table: " . (mysqli_num_rows($r) > 0 ? 'EXISTS' : 'MISSING') . "</p>";

// Test basic count
$r = mysqli_query($bd, "SELECT COUNT(*) as t FROM facturas");
if ($r) echo "<p>Total facturas: " . mysqli_fetch_assoc($r)['t'] . "</p>";
else echo "<p>ERROR facturas count: " . mysqli_error($bd) . "</p>";

// Test the problematic query with CAST
$r = mysqli_query($bd, "SELECT COUNT(*) as t FROM facturas WHERE (fecha_pago IS NULL OR CAST(fecha_pago AS CHAR) = '0000-00-00 00:00:00')");
if ($r) echo "<p>CAST query OK: " . mysqli_fetch_assoc($r)['t'] . "</p>";
else echo "<p>CAST query ERROR: " . mysqli_error($bd) . "</p>";

// Test subquery
$r = mysqli_query($bd, "SELECT COUNT(*) as t FROM facturas WHERE (fecha_pago IS NULL OR CAST(fecha_pago AS CHAR) = '0000-00-00 00:00:00') AND (SELECT COALESCE(SUM(pf.importe),0) FROM pagos_factura pf WHERE pf.idFactura = facturas.id) = 0");
if ($r) echo "<p>Full stats query OK: " . mysqli_fetch_assoc($r)['t'] . "</p>";
else echo "<p>Full stats query ERROR: " . mysqli_error($bd) . "</p>";

// Test auth include
echo "<p>Testing auth.php include...</p>";
try {
    require_once dirname(dirname(__DIR__)) . '/includes/config.php';
    echo "<p>config.php OK</p>";
} catch (Throwable $e) {
    echo "<p>config.php ERROR: " . $e->getMessage() . "</p>";
}

echo "<p>Done.</p>";
