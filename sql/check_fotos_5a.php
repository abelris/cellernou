<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
$ids = [456,531,537,576,577,612,628,680,748,932,992,1050,1183,447,582,620,633,637,881,986,988,990,1177];
$idList = implode(',', $ids);
$r = mysqli_query($bd, "SELECT idProducto, foto FROM producto_fotos WHERE idProducto IN ($idList)");
while ($row = mysqli_fetch_assoc($r)) echo $row['idProducto'] . ': ' . $row['foto'] . "\n";
echo "---\n";
$r2 = mysqli_query($bd, "SELECT id, nombre FROM productos WHERE id IN ($idList) AND id NOT IN (SELECT idProducto FROM producto_fotos)");
while ($row2 = mysqli_fetch_assoc($r2)) echo "NO ENTRY: " . $row2['id'] . ' - ' . $row2['nombre'] . "\n";
