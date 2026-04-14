<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
$ids = [457,589,630,676,720,743,856,809,808,904,1095,1174];
$idList = implode(',', $ids);
$r = mysqli_query($bd, "SELECT idProducto, foto FROM producto_fotos WHERE idProducto IN ($idList)");
while ($row = mysqli_fetch_assoc($r)) echo $row['idProducto'] . ': ' . $row['foto'] . "\n";
echo "---\n";
$r2 = mysqli_query($bd, "SELECT id, nombre FROM productos WHERE id IN ($idList) AND id NOT IN (SELECT idProducto FROM producto_fotos)");
while ($row2 = mysqli_fetch_assoc($r2)) echo "NO ENTRY: " . $row2['id'] . ' - ' . $row2['nombre'] . "\n";
