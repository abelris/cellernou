<?php
$bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
echo "=== CLIENTS ===\n";
$r = mysqli_query($bd, 'SELECT id, nombre, nif FROM odr_clientes ORDER BY id');
while ($row = mysqli_fetch_assoc($r)) echo $row['id'] . ' | ' . $row['nombre'] . ' | ' . $row['nif'] . "\n";

echo "\n=== FACTURAS ===\n";
$r = mysqli_query($bd, 'SELECT COUNT(*) as t FROM odr_facturas');
echo 'Total facturas: ' . mysqli_fetch_assoc($r)['t'] . "\n";

$r = mysqli_query($bd, 'SELECT COUNT(*) as t FROM odr_linea_factura');
echo 'Total lineas: ' . mysqli_fetch_assoc($r)['t'] . "\n";

$r = mysqli_query($bd, 'SELECT SUM(lf.cantidad * lf.precio_unidad) as base FROM odr_linea_factura lf');
echo 'Base total: ' . number_format(mysqli_fetch_assoc($r)['base'], 2, ',', '.') . " EUR\n";

echo "\n=== PER CLIENT ===\n";
$r = mysqli_query($bd, "SELECT c.nombre, COUNT(f.id) as num_fact, SUM(lf.total) as total_base
    FROM odr_clientes c
    LEFT JOIN odr_facturas f ON f.idCliente = c.id
    LEFT JOIN (SELECT idFactura, SUM(cantidad * precio_unidad) as total FROM odr_linea_factura GROUP BY idFactura) lf ON lf.idFactura = f.id
    GROUP BY c.id ORDER BY total_base DESC");
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['nombre'] . ' | ' . $row['num_fact'] . ' fact. | ' . number_format($row['total_base'] ?? 0, 2, ',', '.') . " EUR\n";
}
