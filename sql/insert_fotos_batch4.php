<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
mysqli_set_charset($bd, 'utf8mb4');

// Products that have image files but no producto_fotos entry
$fotos = [
    204 => 'protos-verdejo.jpg',
    534 => 'anna-codorniu-blanc-de-noirs.jpg',
    579 => 'llopart-brut-nature-reserva.jpg',
    580 => 'llopart-imperial-brut-gran-reserva.jpg',
    581 => 'agusti-torello-mata-trepat-rosat.jpg',
    588 => 'privat-laieta-gran-reserva.jpg',
    595 => 'bertha-brut-nature-reserva.jpg',
    644 => 'champagne-aiglemont.jpg',
    805 => 'delirium-tremens.jpg',
    806 => 'delirium-nocturnum.jpg',
    817 => 'brewdog-punk-ipa.jpg',
    854 => 'vivanco-reserva.jpg',
    855 => 'vivanco-crianza.jpg',
    872 => 'gran-vina-sol.jpg',
    1090 => 'moet-chandon-rose-imperial.jpg',
    1096 => 'la-bella-lola.jpg',
    1098 => 'la-chouffe.jpg',
    1103 => 'vina-ardanza-reserva.jpg',
    1104 => 'pago-de-carraovejas-crianza.jpg',
    1251 => 'vina-esmeralda.png',
    1265 => 'azpilicueta-crianza.png',
];

$ok = 0;
foreach ($fotos as $id => $foto) {
    $file = dirname(__DIR__) . '/images/productos/' . $foto;
    if (!file_exists($file)) {
        echo "SKIP $id - file not found: $foto\n";
        continue;
    }
    $fotoEsc = mysqli_real_escape_string($bd, $foto);
    $sql = "INSERT INTO producto_fotos (idProducto, foto, principal) VALUES ($id, '$fotoEsc', 1)
            ON DUPLICATE KEY UPDATE foto = '$fotoEsc'";
    if (mysqli_query($bd, $sql)) {
        echo "OK: $id => $foto\n";
        $ok++;
    } else {
        echo "ERROR $id: " . mysqli_error($bd) . "\n";
    }
}
echo "\nInserted: $ok foto entries\n";
