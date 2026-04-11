<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';

$fotos = [
    456 => 'heus-blanc.jpg',
    531 => 'pago-capellanes-reserva.jpg',
    537 => 'mas-donis.png',
    576 => 'matsu-viejo.jpg',
    577 => 'castell-del-remei-oda-negre.jpg',
    612 => 'alta-alella-garnatxa-negre.png',
    628 => 'jose-pariente-verdejo.jpg',
    680 => 'sangre-de-toro.jpg',
    748 => 'ramon-bilbao-verdejo.webp',
    932 => 'mauro-crianza.png',
    992 => 'martin-codax.jpg',
    1050 => 'llavors.jpg',
    1183 => 'paco-y-lola.png',
    447 => 'licor-43.jpg',
    582 => 'gin-xoriguer.jpg',
    620 => 'vermu-cinzano.jpg',
    633 => 'chartreuse-amarillo.png',
    637 => 'gin-fifty-pounds.jpg',
    881 => 'whisky-oban-14.jpg',
    986 => 'whisky-isle-of-jura-10.jpg',
    988 => 'ron-flor-de-cana-7.jpg',
    990 => 'cognac-courvoisier-napoleon.webp',
    1177 => 'whisky-glenfiddich-12.png',
];

$ok = 0;
$fail = 0;
foreach ($fotos as $id => $foto) {
    $sql = "INSERT INTO producto_fotos (idProducto, foto, principal) VALUES ($id, '" . mysqli_real_escape_string($bd, $foto) . "', 1) ON DUPLICATE KEY UPDATE foto = '" . mysqli_real_escape_string($bd, $foto) . "'";
    if (mysqli_query($bd, $sql)) {
        echo "OK: $id => $foto\n";
        $ok++;
    } else {
        echo "FAIL: $id => $foto - " . mysqli_error($bd) . "\n";
        $fail++;
    }
}
echo "---\nDone: $ok OK, $fail FAIL\n";
