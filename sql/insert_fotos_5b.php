<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';

$fotos = [
    457 => 'mirgin-reserva-rose.png',
    589 => 'anna-codorniu-blanc-de-blancs.jpg',
    630 => 'llopart-brut-nature-integral.jpg',
    676 => 'juve-camps-cinta-purpura.png',
    720 => 'codorniu-pinot-noir.jpg',
    743 => 'juve-camps-reserva-familia.png',
    856 => 'anna-codorniu-brut-nature.png',
    809 => 'cerveza-piraat.png',
    808 => 'guineu-jack-the-ripa.jpg',
    904 => 'brewdog-five-am-red-ale.jpg',
    1095 => 'cerdos-voladores.jpg',
    1174 => 'grevensteiner.webp',
];

foreach ($fotos as $id => $foto) {
    $sql = "INSERT INTO producto_fotos (idProducto, foto, principal) VALUES ($id, '$foto', 1)
            ON DUPLICATE KEY UPDATE foto = '$foto'";
    if (mysqli_query($bd, $sql)) {
        echo "OK: ID $id => $foto\n";
    } else {
        echo "ERROR: ID $id => " . mysqli_error($bd) . "\n";
    }
}
