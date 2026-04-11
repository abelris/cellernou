<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
$r = mysqli_query($bd, 'DESCRIBE producto_textos');
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['Field'] . ' | ' . $row['Type'] . "\n";
}
// Check if batch3 was already run
echo "\n--- Existing texts count ---\n";
$r2 = mysqli_query($bd, 'SELECT COUNT(*) as cnt FROM producto_textos');
$row2 = mysqli_fetch_assoc($r2);
echo "Total: " . $row2['cnt'] . "\n";

// Check which products already have texts
$r3 = mysqli_query($bd, 'SELECT DISTINCT idProducto FROM producto_textos ORDER BY idProducto');
$ids = [];
while ($row3 = mysqli_fetch_assoc($r3)) $ids[] = $row3['idProducto'];
echo "Products with texts: " . implode(',', $ids) . "\n";
