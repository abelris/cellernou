<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';

$fotos = [
    204  => 'protos-verdejo.jpg',
    534  => 'anna-codorniu-blanc-de-noirs.jpg',
    579  => 'llopart-brut-nature-reserva.jpg',
    580  => 'llopart-imperial-brut-gran-reserva.jpg',
    581  => 'agusti-torello-mata-trepat-rosat.jpg',
    588  => 'privat-laieta-gran-reserva.jpg',
    595  => 'bertha-brut-nature-reserva.jpg',
    1090 => 'moet-chandon-rose-imperial.jpg',
    644  => 'champagne-daiglemont-brut.jpg',
    854  => 'vivanco-reserva.jpg',
    855  => 'vivanco-crianza.jpg',
    1103 => 'vina-ardanza-reserva.jpg',
    1104 => 'pago-de-carraovejas-crianza.jpg',
    805  => 'delirium-tremens.jpg',
    806  => 'delirium-nocturnum.jpg',
    817  => 'brewdog-punk-ipa.jpg',
    1098 => 'la-chouffe.jpg',
    1096 => 'la-bella-lola.jpg',
];

$ok = 0;
$fail = 0;

foreach ($fotos as $idProducto => $foto) {
    $stmt = mysqli_prepare($bd, "INSERT INTO producto_fotos (idProducto, foto, principal) VALUES (?, ?, 1)");
    mysqli_stmt_bind_param($stmt, 'is', $idProducto, $foto);
    if (mysqli_stmt_execute($stmt)) {
        echo "OK: ID $idProducto => $foto\n";
        $ok++;
    } else {
        echo "FAIL: ID $idProducto => " . mysqli_error($bd) . "\n";
        $fail++;
    }
    mysqli_stmt_close($stmt);
}

echo "\n--- Done: $ok OK, $fail FAIL ---\n";
