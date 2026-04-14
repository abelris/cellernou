<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';

// Update old entries with correct filenames
$updates = [
    196 => 'whisky-ballantines.jpg',  // was ballantines.jpg (missing)
    200 => 'gin-beefeater.jpg',       // was beefeater.jpg (missing)
    332 => 'ron-brugal.jpg',          // was long old filename (missing)
    463 => 'talisker-10.jpg',         // was old filename (missing)
    464 => 'gin-citadelle.png',       // was citadelle.jpg (missing)
];

foreach ($updates as $id => $foto) {
    $file = dirname(__DIR__) . '/images/productos/' . $foto;
    if (!file_exists($file)) {
        echo "SKIP $id - file not found: $foto\n";
        continue;
    }
    $fotoEsc = mysqli_real_escape_string($bd, $foto);
    // Update existing entry
    $sql = "UPDATE producto_fotos SET foto = '$fotoEsc' WHERE idProducto = $id AND principal = 1";
    if (mysqli_query($bd, $sql)) {
        $affected = mysqli_affected_rows($bd);
        if ($affected > 0) {
            echo "UPDATED: $id => $foto\n";
        } else {
            // No existing entry with principal=1, try inserting
            $sql2 = "UPDATE producto_fotos SET foto = '$fotoEsc' WHERE idProducto = $id LIMIT 1";
            mysqli_query($bd, $sql2);
            if (mysqli_affected_rows($bd) > 0) {
                echo "UPDATED (no principal): $id => $foto\n";
            } else {
                echo "NO ENTRY TO UPDATE: $id\n";
            }
        }
    } else {
        echo "ERROR $id: " . mysqli_error($bd) . "\n";
    }
}
