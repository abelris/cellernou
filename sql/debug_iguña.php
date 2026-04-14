<?php
$bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
mysqli_set_charset($bd, 'utf8mb4');

echo "=== Client 8 ===\n";
$r = mysqli_query($bd, "SELECT * FROM odr_clientes WHERE id = 8");
$row = mysqli_fetch_assoc($r);
print_r($row);

echo "\n=== HEX of nombre ===\n";
$r = mysqli_query($bd, "SELECT HEX(nombre) as h, nombre FROM odr_clientes WHERE id = 8");
$row = mysqli_fetch_assoc($r);
echo "HEX: " . $row['h'] . "\n";
echo "nombre: " . $row['nombre'] . "\n";
echo "strlen: " . strlen($row['nombre']) . "\n";
echo "mb_strlen: " . mb_strlen($row['nombre'], 'UTF-8') . "\n";

echo "\n=== All clients nombre ===\n";
$r = mysqli_query($bd, "SELECT id, nombre, LENGTH(nombre) as len, CHAR_LENGTH(nombre) as clen FROM odr_clientes ORDER BY id");
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['id'] . ' | ' . $row['nombre'] . ' | bytes:' . $row['len'] . ' | chars:' . $row['clen'] . "\n";
}

echo "\n=== Facturas for client 8 ===\n";
$r = mysqli_query($bd, "SELECT f.id, f.numero, f.idCliente, c.nombre FROM odr_facturas f LEFT JOIN odr_clientes c ON f.idCliente = c.id WHERE f.idCliente = 8");
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['id'] . ' | ' . $row['numero'] . ' | idCliente:' . $row['idCliente'] . ' | client:' . $row['nombre'] . "\n";
}

echo "\n=== LIKE test ===\n";
$r = mysqli_query($bd, "SELECT id, nombre FROM odr_clientes WHERE nombre LIKE '%Igu%'");
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['id'] . ' | ' . $row['nombre'] . "\n";
}
