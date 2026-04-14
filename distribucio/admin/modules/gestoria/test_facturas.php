<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(dirname(__DIR__))) . '/connexio/connexio.php';
mysqli_query($bd, "SET SESSION sql_mode=''");

echo "Connection OK\n";

// Test 1: facturas table
$r = mysqli_query($bd, "SELECT COUNT(*) as c FROM facturas");
if (!$r) { echo "ERROR facturas: " . mysqli_error($bd) . "\n"; }
else { echo "facturas count: " . mysqli_fetch_assoc($r)['c'] . "\n"; }

// Test 2: pagos_factura table
$r = mysqli_query($bd, "SELECT COUNT(*) as c FROM pagos_factura");
if (!$r) { echo "ERROR pagos_factura: " . mysqli_error($bd) . "\n"; }
else { echo "pagos_factura count: " . mysqli_fetch_assoc($r)['c'] . "\n"; }

// Test 3: the main query
$year = date('Y');
$sql = "SELECT f.*, c.nombre as cliente_nombre, c.recarrec_equiv,
        (SELECT SUM(lf.cantidad * lf.precio_unidad) FROM linea_factura lf WHERE lf.idFactura = f.id) as base_total,
        (SELECT SUM(lf.cantidad * lf.precio_unidad * lf.iva / 100) FROM linea_factura lf WHERE lf.idFactura = f.id) as iva_total,
        (SELECT COALESCE(SUM(pf.importe), 0) FROM pagos_factura pf WHERE pf.idFactura = f.id) as total_pagado
        FROM facturas f
        LEFT JOIN clientes c ON f.idCliente = c.id
        WHERE 1=1 AND YEAR(f.fecha) = $year
        ORDER BY f.fecha DESC, f.id DESC
        LIMIT 5 OFFSET 0";
$r = mysqli_query($bd, $sql);
if (!$r) { echo "ERROR main query: " . mysqli_error($bd) . "\n"; }
else { echo "main query rows: " . mysqli_num_rows($r) . "\n"; }

// Test 4: recarrec_equiv column
$r = mysqli_query($bd, "SHOW COLUMNS FROM clientes LIKE 'recarrec_equiv'");
if (!$r || mysqli_num_rows($r) == 0) { echo "ERROR: clientes.recarrec_equiv column NOT FOUND\n"; }
else { echo "clientes.recarrec_equiv: OK\n"; }

// Test 5: fecha_pago column
$r = mysqli_query($bd, "SHOW COLUMNS FROM facturas LIKE 'fecha_pago'");
if (!$r || mysqli_num_rows($r) == 0) { echo "ERROR: facturas.fecha_pago column NOT FOUND\n"; }
else { echo "facturas.fecha_pago: OK\n"; }

// Test 6: fmtEur function conflict
if (function_exists('fmtEur')) { echo "fmtEur already defined!\n"; }
else { echo "fmtEur not yet defined: OK\n"; }

// Test 7: Check if gestoria/index.php defines fmtEur
echo "\nChecking for fmtEur in index.php...\n";
$indexContent = file_get_contents(dirname(__FILE__) . '/index.php');
if (strpos($indexContent, 'function fmtEur') !== false) { echo "fmtEur IS defined in index.php\n"; }
