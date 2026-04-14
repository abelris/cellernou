<?php
$bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
mysqli_query($bd, "SET SESSION sql_mode = ''");

$tables = ['facturas', 'linea_factura', 'tiquets', 'linea_tiquet', 'compras', 'linea_compra', 'facturas_compra', 'linea_factura_compra'];
foreach ($tables as $t) {
    echo "=== $t ===\n";
    $r = mysqli_query($bd, "DESCRIBE $t");
    if ($r) { while ($row = mysqli_fetch_assoc($r)) echo "  {$row['Field']} ({$row['Type']})\n"; }
    else echo "  TABLE NOT FOUND\n";
    echo "\n";
}

// Check data ranges
echo "=== DATA RANGES ===\n";
$checks = [
    "SELECT MIN(fecha) as mn, MAX(fecha) as mx, COUNT(*) as cnt FROM tiquets WHERE fecha > '2000-01-01'",
    "SELECT MIN(fecha) as mn, MAX(fecha) as mx, COUNT(*) as cnt FROM facturas WHERE fecha > '2000-01-01'",
    "SELECT MIN(fecha) as mn, MAX(fecha) as mx, COUNT(*) as cnt FROM compras WHERE fecha > '2000-01-01'",
    "SELECT MIN(fecha) as mn, MAX(fecha) as mx, COUNT(*) as cnt FROM odr_facturas",
    "SELECT MIN(fecha) as mn, MAX(fecha) as mx, COUNT(*) as cnt FROM odr_facturas_compra",
];
$labels = ['tiquets', 'facturas', 'compras', 'odr_facturas', 'odr_facturas_compra'];
foreach ($checks as $i => $sql) {
    $r = mysqli_query($bd, $sql);
    if ($r) { $row = mysqli_fetch_assoc($r); echo "{$labels[$i]}: {$row['mn']} -> {$row['mx']} ({$row['cnt']} rows)\n"; }
}

// Check if linea_tiquet has fecha and precio_compra
echo "\n=== linea_tiquet sample ===\n";
$r = mysqli_query($bd, "SELECT * FROM linea_tiquet LIMIT 1");
if ($r && $row = mysqli_fetch_assoc($r)) print_r($row);

echo "\n=== linea_factura sample ===\n";
$r = mysqli_query($bd, "SELECT * FROM linea_factura LIMIT 1");
if ($r && $row = mysqli_fetch_assoc($r)) print_r($row);

// Check facturas_compra structure
echo "\n=== facturas_compra ===\n";
$r = mysqli_query($bd, "DESCRIBE facturas_compra");
if ($r) { while ($row = mysqli_fetch_assoc($r)) echo "  {$row['Field']} ({$row['Type']})\n"; }
else echo "  TABLE NOT FOUND\n";
