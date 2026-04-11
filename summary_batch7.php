<?php
require __DIR__ . '/distribucio/connexio/connexio.php';
mysqli_query($bd, "SET SESSION sql_mode=''");

$ids = [4138,4136,4148,5112,2754,5111,5129,1390,
        4145,2818,2202,4897,4260,4002,4842,
        4818,3416,3381,3407,
        3660,3659,3657,5086,
        2874,4604,1638,
        3525,2595,4391,4196,
        4003,3888,3504,
        5961,5056,3723,
        2092,747,4799,
        3500,353,
        3610,3609,
        4377,4378,
        3083,2824,
        3666,5107,3937];

echo "=== BATCH 7 SUMMARY: 50 wines ===\n\n";
$textos = 0; $notas = 0; $fotos = 0;
foreach ($ids as $id) {
    $r = mysqli_query($bd, "SELECT p.nombre,
        (SELECT COUNT(*) FROM producto_textos pt WHERE pt.idProducto = p.id AND pt.tipo='descripcion') as has_desc,
        (SELECT COUNT(*) FROM producto_textos pt WHERE pt.idProducto = p.id AND pt.tipo='nota_cata') as has_nota,
        (SELECT COUNT(*) FROM producto_fotos pf WHERE pf.idProducto = p.id) as has_foto
    FROM productos p WHERE p.id = $id");
    $row = mysqli_fetch_assoc($r);
    if ($row['has_desc']) $textos++;
    if ($row['has_nota']) $notas++;
    if ($row['has_foto']) $fotos++;
}

echo "Descriptions: $textos/50\n";
echo "Tasting notes: $notas/50\n";
echo "Photos: $fotos/50\n";

$r = mysqli_query($bd, "SELECT COUNT(DISTINCT idProducto) as c FROM producto_textos");
$totalTextos = mysqli_fetch_assoc($r)['c'];
$r = mysqli_query($bd, "SELECT COUNT(DISTINCT idProducto) as c FROM producto_fotos");
$totalFotos = mysqli_fetch_assoc($r)['c'];
echo "\nTotal products with texts (all batches): $totalTextos\n";
echo "Total products with photos (all batches): $totalFotos\n";

// Show missing
$idList = implode(',', $ids);
$r = mysqli_query($bd, "SELECT p.id, p.nombre FROM productos p WHERE p.id IN ($idList) AND p.id NOT IN (SELECT DISTINCT idProducto FROM producto_fotos)");
if (mysqli_num_rows($r) > 0) {
    echo "\nMissing photos:\n";
    while ($row = mysqli_fetch_assoc($r)) echo "  ID {$row['id']}: {$row['nombre']}\n";
}
