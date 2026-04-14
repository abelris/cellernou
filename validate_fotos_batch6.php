<?php
require __DIR__ . '/distribucio/connexio/connexio.php';
mysqli_query($bd, "SET SESSION sql_mode=''");

$destDir = __DIR__ . '/images/productos/';

// Get all batch 6 wine IDs
$ids = [2697,5103,2047,4797,732,3293,4822,5045,4333,4283,
        3909,4819,5143,4920,5139,4960,3287,3418,
        4785,4109,5491,4619,4888,
        1182,2871,4537,4538,2406,
        4494,4840,2286,3118,
        4254,5034,2148,4732,
        3881,4361,4590,
        5039,4780,4823,
        3602,3603,2093,
        3269,3441,
        3349,1558,
        4650];

echo "=== BATCH 6 PHOTO VALIDATION ===\n\n";
$good = 0; $bad = 0; $missing = 0;
$knownPlaceholderMd5 = '714ba97ff850dce1bf6366bcda70ba04';

foreach ($ids as $id) {
    $r = mysqli_query($bd, "SELECT p.nombre, pf.foto FROM productos p LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1 WHERE p.id = $id");
    $row = mysqli_fetch_assoc($r);
    $nombre = $row['nombre'];
    $foto = $row['foto'];

    if (!$foto) {
        echo "  MISSING-DB  ID $id: $nombre\n";
        $missing++;
        continue;
    }

    $path = $destDir . $foto;
    if (!file_exists($path)) {
        echo "  MISSING-FILE ID $id: $nombre ($foto)\n";
        $missing++;
        continue;
    }

    $size = filesize($path);
    $md5 = md5_file($path);

    if ($md5 === $knownPlaceholderMd5) {
        echo "  PLACEHOLDER  ID $id: $nombre ($foto, {$size}b)\n";
        $bad++;
    } elseif ($size < 2000) {
        echo "  TOO-SMALL    ID $id: $nombre ($foto, {$size}b)\n";
        $bad++;
    } else {
        echo "  OK           ID $id: $nombre ($foto, " . number_format($size) . "b)\n";
        $good++;
    }
}

echo "\n=== RESULTS ===\n";
echo "Good: $good | Bad: $bad | Missing: $missing | Total: " . count($ids) . "\n";
