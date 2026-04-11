<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
mysqli_query($bd, "SET SESSION sql_mode=''");

$sql = "SELECT p.id, p.nombre, p.codigo, s.nombre_es as sector, tp.nombre_es as tipo,
         pr.nombre_es as procedencia, p.iva,
         (SELECT COUNT(*) FROM producto_fotos pf WHERE pf.idProducto = p.id) as tiene_foto
         FROM productos p
         LEFT JOIN sectores s ON p.idSector = s.id
         LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
         LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
         WHERE p.activo = 1
         AND p.id NOT IN (SELECT idProducto FROM producto_textos WHERE tipo = 'descripcion')
         ORDER BY p.id";
$r = mysqli_query($bd, $sql);
$count = 0;
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['id'] . ' | ' . $row['nombre'] . ' | ' . $row['sector'] . ' | ' . $row['tipo'] . ' | ' . $row['procedencia'] . ' | foto:' . $row['tiene_foto'] . "\n";
    $count++;
}
echo "\n--- Total: $count products without descriptions ---\n";
