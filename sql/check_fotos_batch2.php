<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
$ids = [204, 534, 579, 580, 581, 588, 595, 1090, 644, 854, 855, 1103, 1104, 805, 806, 817, 1098, 1096];
$idList = implode(',', $ids);
$r = mysqli_query($bd, "SELECT idProducto, foto FROM producto_fotos WHERE idProducto IN ($idList)");
while ($row = mysqli_fetch_assoc($r)) echo $row['idProducto'] . ': ' . $row['foto'] . "\n";
echo "---\n";
$r2 = mysqli_query($bd, "SELECT id, nombre FROM productos WHERE id IN ($idList) AND id NOT IN (SELECT idProducto FROM producto_fotos)");
while ($row2 = mysqli_fetch_assoc($r2)) echo "NO ENTRY: " . $row2['id'] . ' - ' . $row2['nombre'] . "\n";
