<?php
$bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
mysqli_set_charset($bd, 'utf8mb4');

echo "=== odr_proveedores STRUCTURE ===\n";
$r = mysqli_query($bd, "DESCRIBE odr_proveedores");
while ($row = mysqli_fetch_assoc($r)) echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Default'] . "\n";

echo "\n=== odr_facturas_compra STRUCTURE ===\n";
$r = mysqli_query($bd, "DESCRIBE odr_facturas_compra");
while ($row = mysqli_fetch_assoc($r)) echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Default'] . "\n";

echo "\n=== odr_linea_factura_compra STRUCTURE ===\n";
$r = mysqli_query($bd, "DESCRIBE odr_linea_factura_compra");
while ($row = mysqli_fetch_assoc($r)) echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Default'] . "\n";

echo "\n=== EXISTING PROVEEDORES ===\n";
$r = mysqli_query($bd, "SELECT * FROM odr_proveedores ORDER BY id");
while ($row = mysqli_fetch_assoc($r)) print_r($row);

echo "\n=== EXISTING FACTURAS COMPRA ===\n";
$r = mysqli_query($bd, "SELECT COUNT(*) as total FROM odr_facturas_compra");
echo "Total: " . mysqli_fetch_assoc($r)['total'] . "\n";
