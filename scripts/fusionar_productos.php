<?php
/**
 * Fusiona dos fichas del mismo producto en una.
 *
 * Uso (CLI):  php fusionar_productos.php <bd> <idA> <idB> [--aplicar]
 *   Local:    php scripts/fusionar_productos.php cellernodb 4752 6255
 *   Prod:     php fusionar_productos.php cellernou_com 4752 6255 --aplicar   (root por socket)
 *
 * Cuál se queda (regla del usuario, 26-oct-2026): el que tiene ventas; si las
 * tienen los dos, el más caro (PVP de la tienda 1).
 * Todo lo que apunta al que se va (stock, tiquets, facturas, compras, precios,
 * fotos, textos, tráfico…) pasa al que se queda; donde los dos tienen fila
 * (precio de tienda, texto del mismo tipo) gana la del que se queda. La
 * clasificación se toma del que la tenga en un sector público si el que se
 * queda no. El que se va queda inactivo y su URL redirige 301
 * (PRODUCTOS_UNIFICADOS en includes/config.php).
 *
 * Sin --aplicar solo enseña lo que haría.
 */

if (PHP_SAPI !== 'cli' || $argc < 4) {
    fwrite(STDERR, "Uso: php fusionar_productos.php <bd> <idA> <idB> [--aplicar]\n");
    exit(1);
}
[$bdNombre, $idA, $idB] = [$argv[1], (int) $argv[2], (int) $argv[3]];
$aplicar = in_array('--aplicar', $argv, true);
$sectoresPublicos = [8, 10, 2, 21, 12, 14];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$bd = mysqli_connect('localhost', 'root', '', $bdNombre);
mysqli_set_charset($bd, 'utf8mb4');
mysqli_query($bd, "SET SESSION sql_mode = ''");

$val = function ($sql) use ($bd) {
    $r = mysqli_query($bd, $sql);
    $row = mysqli_fetch_row($r);
    return $row ? $row[0] : null;
};
$ficha = function ($id) use ($bd, $val) {
    $p = mysqli_fetch_assoc(mysqli_query($bd, "SELECT id, nombre, activo, idSector, idTipo, idProcedencia FROM productos WHERE id = $id"));
    if (!$p) return null;
    $p['ventas'] = (int) $val("SELECT COUNT(*) FROM linea_tiquet WHERE idProducto = $id")
                 + (int) $val("SELECT COUNT(*) FROM linea_factura WHERE idProducto = $id");
    $p['pvp'] = (float) $val("SELECT pvp FROM precios_producto_tienda WHERE idProducto = $id AND idTienda = 1 LIMIT 1");
    $p['stock'] = (int) $val("SELECT COUNT(*) FROM stock_producto WHERE idProducto = $id AND vendido = 0");
    return $p;
};

$a = $ficha($idA);
$b = $ficha($idB);
if (!$a || !$b) {
    fwrite(STDERR, "No existe alguno de los dos productos.\n");
    exit(1);
}
foreach ([$a, $b] as $p) {
    printf("%5d  %-40s ventas %3d  pvp %6.2f  stock %3d  sector %s tipo %s proc %s activo %s\n",
        $p['id'], $p['nombre'], $p['ventas'], $p['pvp'], $p['stock'], $p['idSector'], $p['idTipo'], $p['idProcedencia'], $p['activo']);
}

if (($a['ventas'] > 0) !== ($b['ventas'] > 0)) {
    [$queda, $va] = $a['ventas'] > 0 ? [$a, $b] : [$b, $a];
    $motivo = 'es el único con ventas';
} else {
    [$queda, $va] = $a['pvp'] >= $b['pvp'] ? [$a, $b] : [$b, $a];
    $motivo = $a['ventas'] > 0 ? 'los dos tienen ventas y es el más caro' : 'ninguno tiene ventas y es el más caro';
}
$K = (int) $queda['id'];
$D = (int) $va['id'];
echo "\nSe queda $K ({$queda['nombre']}): $motivo. Se va $D.\n";

$clasif = !in_array((int) $queda['idSector'], $sectoresPublicos, true) && in_array((int) $va['idSector'], $sectoresPublicos, true);
if ($clasif) {
    echo "Clasificación de $D (sector {$va['idSector']}, tipo {$va['idTipo']}, procedencia {$va['idProcedencia']}): la de $K no es de un sector público.\n";
}

// Tablas con columna de producto (salvo productos y copias de seguridad).
$tablas = [];
$r = mysqli_query($bd, "SELECT table_name, column_name FROM information_schema.columns
                        WHERE table_schema = '" . mysqli_real_escape_string($bd, $bdNombre) . "'
                          AND column_name IN ('idProducto', 'id_producto')
                          AND table_name <> 'productos' AND table_name NOT LIKE '%copia%'
                          AND table_name NOT LIKE '%backup%' AND table_name NOT LIKE '%bak%'");
while ($row = mysqli_fetch_row($r)) {
    $n = (int) $val("SELECT COUNT(*) FROM `{$row[0]}` WHERE `{$row[1]}` = $D");
    if ($n > 0) $tablas[] = [$row[0], $row[1], $n];
}
echo "\nFilas que pasan de $D a $K:\n";
foreach ($tablas as [$t, $c, $n]) echo "  $t.$c: $n\n";
if (!$tablas) echo "  (ninguna)\n";

if (!$aplicar) {
    echo "\nSimulación. Para hacerlo: añade --aplicar\n";
    exit(0);
}

mysqli_begin_transaction($bd);
try {
    // Fotos: si el que se queda ya tiene principal, las del otro entran como secundarias.
    if ((int) $val("SELECT COUNT(*) FROM producto_fotos WHERE idProducto = $K AND principal = 1") > 0) {
        mysqli_query($bd, "UPDATE producto_fotos SET principal = 0 WHERE idProducto = $D");
    }
    // Precio por tienda (sin clave única): donde los dos tienen, se queda el suyo.
    mysqli_query($bd, "DELETE d FROM precios_producto_tienda d
                       JOIN precios_producto_tienda k ON k.idTienda = d.idTienda AND k.idProducto = $K
                       WHERE d.idProducto = $D");
    foreach ($tablas as [$t, $c, $n]) {
        mysqli_query($bd, "UPDATE IGNORE `$t` SET `$c` = $K WHERE `$c` = $D");
        $resto = (int) $val("SELECT COUNT(*) FROM `$t` WHERE `$c` = $D");
        if ($resto > 0) {
            // Chocan con una fila propia del que se queda: gana la suya.
            mysqli_query($bd, "DELETE FROM `$t` WHERE `$c` = $D");
            echo "  $t: $resto filas repetidas, se queda la de $K\n";
        }
    }
    if ($clasif) {
        mysqli_query($bd, "UPDATE productos SET idSector = {$va['idSector']}, idTipo = {$va['idTipo']},
                           idProcedencia = {$va['idProcedencia']} WHERE id = $K");
    }
    mysqli_query($bd, "UPDATE productos SET activo = 1 WHERE id = $K");
    mysqli_query($bd, "UPDATE productos SET activo = 0 WHERE id = $D");
    mysqli_commit($bd);
} catch (Throwable $e) {
    mysqli_rollback($bd);
    fwrite(STDERR, 'Error, no se ha cambiado nada: ' . $e->getMessage() . "\n");
    exit(1);
}

$fin = $ficha($K);
printf("\nHecho. %d %s: ventas %d, stock %d, pvp %.2f, sector %s tipo %s.\n",
    $K, $fin['nombre'], $fin['ventas'], $fin['stock'], $fin['pvp'], $fin['idSector'], $fin['idTipo']);
echo "Añade $D => $K a PRODUCTOS_UNIFICADOS en includes/config.php para la redirección 301.\n";
