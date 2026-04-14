<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';

$ids = [204, 534, 579, 580, 581, 588, 595, 1090, 644, 854, 855, 1103, 1104, 805, 806, 817, 1098, 1096];
$idList = implode(',', $ids);

// Check for duplicates
$r = mysqli_query($bd, "SELECT idProducto, COUNT(*) as cnt FROM producto_fotos WHERE idProducto IN ($idList) GROUP BY idProducto HAVING cnt > 1");
$dupes = 0;
while ($row = mysqli_fetch_assoc($r)) {
    $dupes++;
    $id = $row['idProducto'];
    // Keep only the latest row (highest id), delete the rest
    $r2 = mysqli_query($bd, "SELECT MIN(id) as min_id FROM producto_fotos WHERE idProducto = $id");
    $min = mysqli_fetch_assoc($r2)['min_id'];
    mysqli_query($bd, "DELETE FROM producto_fotos WHERE idProducto = $id AND id = $min");
    echo "Removed duplicate for product $id (deleted row id=$min)\n";
}

if ($dupes === 0) {
    echo "No duplicates found.\n";
} else {
    echo "\nCleaned $dupes duplicates.\n";
}

// Final count
$r3 = mysqli_query($bd, "SELECT idProducto, foto FROM producto_fotos WHERE idProducto IN ($idList) ORDER BY idProducto");
echo "\nFinal state:\n";
while ($row3 = mysqli_fetch_assoc($r3)) {
    echo $row3['idProducto'] . ': ' . $row3['foto'] . "\n";
}
