<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
$ids = [107,196,200,204,332,463,464,529,534,574,575,579,580,581,588,595,607,632,636,638,644,648,653,672,718,805,806,817,854,855,865,872,907,927,1090,1096,1098,1103,1104,1251,1265];
$idList = implode(',', $ids);
$r = mysqli_query($bd, "SELECT pf.idProducto, p.nombre, pf.foto FROM producto_fotos pf INNER JOIN productos p ON pf.idProducto = p.id WHERE pf.idProducto IN ($idList) ORDER BY pf.idProducto");
$found = [];
while ($row = mysqli_fetch_assoc($r)) {
    $file = 'images/productos/' . $row['foto'];
    $exists = file_exists(dirname(__DIR__) . '/' . $file) ? 'EXISTS' : 'MISSING';
    echo $row['idProducto'] . ' | ' . $row['nombre'] . ' | ' . $row['foto'] . ' | ' . $exists . "\n";
    $found[] = $row['idProducto'];
}
echo "\n--- Products WITHOUT foto entry ---\n";
$missing = array_diff($ids, $found);
foreach ($missing as $id) {
    $r2 = mysqli_query($bd, "SELECT nombre FROM productos WHERE id = $id");
    $row2 = mysqli_fetch_assoc($r2);
    echo "$id | " . ($row2['nombre'] ?? '?') . "\n";
}
