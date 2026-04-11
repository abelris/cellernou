<?php
$bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
if (!$bd) { echo 'Error: ' . mysqli_connect_error(); exit(1); }

// Paso 1: productos con pvp=0 en los sectores objetivo
$sql1 = "SELECT p.id, p.nombre, p.idSector, s.nombre_es as sector, COALESCE(ppt.pvp, 0) as pvp
FROM productos p
LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
LEFT JOIN sectores s ON p.idSector = s.id
WHERE p.activo = 1
AND p.idSector IN (8, 14, 2, 21)
AND COALESCE(ppt.pvp, 0) = 0";

$r1 = mysqli_query($bd, $sql1);
$sinPrecio = [];
while ($row = mysqli_fetch_assoc($r1)) {
    $sinPrecio[$row['id']] = $row;
}

// Paso 2: productos con pvp>0 pero sin compra en 60 días
$sql2 = "SELECT p.id, p.nombre, p.idSector, s.nombre_es as sector, ppt.pvp
FROM productos p
INNER JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1 AND ppt.pvp > 0
LEFT JOIN sectores s ON p.idSector = s.id
LEFT JOIN linea_compra lc ON lc.idProducto = p.id AND lc.fecha >= DATE_SUB(NOW(), INTERVAL 60 DAY)
WHERE p.activo = 1
AND p.idSector IN (8, 14, 2, 21)
AND lc.id IS NULL";

$r2 = mysqli_query($bd, $sql2);
$sinCompra = [];
while ($row = mysqli_fetch_assoc($r2)) {
    if (!isset($sinPrecio[$row['id']])) {
        $sinCompra[$row['id']] = $row;
    }
}

$all = array_merge(array_values($sinPrecio), array_values($sinCompra));

file_put_contents(__DIR__ . '/productos_target.json', json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Sin precio (pvp=0): " . count($sinPrecio) . "\n";
echo "Con precio pero sin compra 60d: " . count($sinCompra) . "\n";
echo "Total exportados: " . count($all) . "\n";
