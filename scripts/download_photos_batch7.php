<?php
/**
 * Batch 7 photo downloader.
 * Same logic as batch 6: curl → GD resize ≤1200x1200 q82 → save → register in producto_fotos.
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only.\n"); }

ini_set('memory_limit', '512M');
set_time_limit(0);

require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
mysqli_set_charset($bd, 'utf8mb4');

$imgDir  = realpath(dirname(__DIR__) . '/images/productos');
$maxSize = 1200;
$quality = 82;

$downloads = [
    // === VINOS ===
    [674,  'ramon-bilbao-crianza-magnum.jpg',   'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/1/v148190.jpg'],
    [739,  'gaba-do-xil-mencia.jpg',            'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/1/v139662.jpg'],
    [830,  'entrelobos.jpg',                    'https://lafuente.es/wp-content/uploads/2019/10/6616.jpg'],

    // === CAVAS / ESPUMOSOS ===
    [1477, 'mastinell-gran-reserva.jpg',        'https://lafuente.es/wp-content/uploads/2019/10/1357.jpg'],
    [1525, 'champagne-delamotte-blanc-blancs.jpg','https://www.vilaviniteca.es/media/catalog/product/v/0/v004875.jpg'],
    [1526, 'champagne-delamotte-blanc-blancs-2.jpg','https://www.vilaviniteca.es/media/catalog/product/v/0/v004875.jpg'],

    // === LICORES / DESTILADOS ===
    [1030, 'martini-extra-dry.jpg',             'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v003656.jpg'],
    [1107, 'malibu.jpg',                        'https://www.vilaviniteca.es/media/catalog/product/v/0/v005732.jpg'],
    [1108, 'knockando-12.jpg',                  'https://lafuente.es/wp-content/uploads/2015/11/KNOCKANDO-12-YEARS.jpg'],
    [1165, 'buchanans-12.jpg',                  'https://lafuente.es/wp-content/uploads/2015/11/BUCHANANS-12-YEARS.jpg'],
    [1179, 'gin-gvine-nouaison.jpg',            'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v026273.jpg'],
    [1372, 'lustau-fino-jarana.jpg',            'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v021450.jpg'],
    [1403, 'wild-turkey.jpg',                   'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v024682.jpg'],
    // Fallback: 1443 es "Grappa del Contadino" (no está en shops), uso Grappa Gagliano di Moscato de Lafuente
    [1443, 'grappa-contadino.jpg',              'https://lafuente.es/wp-content/uploads/2015/11/GRAPPA-GAGLIANO-DI-MOSCATO.jpg'],
    [1487, 'glen-grant-12.jpg',                 'https://lafuente.es/wp-content/uploads/2019/10/GLEN-GRANT-12-YEARS.jpg'],
    [1539, 'chivas-regal-21.jpg',               'https://lafuente.es/wp-content/uploads/2015/11/CHIVAS-REGAL-21-YEARS-ROYAL-SALUTE.jpg'],
    [1545, 'manzanilla-papirusa-lustau.jpg',    'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v021448.jpg'],
    [1550, 'campari.jpg',                       'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v003634.jpg'],
    [1551, 'valdespino-sello-azul.jpg',         'https://lafuente.es/wp-content/uploads/2015/11/VALDESPINO-SELLO-AZUL-SOLERA.jpg'],
    [1552, 'baileys-chocolat-luxe.jpg',         'https://lafuente.es/wp-content/uploads/2019/10/baileys-chocolat.jpg'],

    // === CERVEZAS ===
    [1462, 'moritz.jpg',                        'https://lafuente.es/wp-content/uploads/2020/04/CERVEZA-MORITZ.jpg'],
];

// === Procesamiento (mismo código de batch 6) ===
$ok = 0; $fail = 0; $errors = [];

foreach ($downloads as [$id, $filename, $url]) {
    echo "--- [$id] $filename\n";
    echo "    URL: $url\n";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => ['Accept: image/webp,image/apng,image/*,*/*;q=0.8', 'Accept-Language: es-ES,es;q=0.9', 'Referer: https://www.google.com/'],
    ]);
    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($raw === false || $httpCode !== 200) { echo "    FAIL: HTTP $httpCode\n"; $errors[] = "[$id] HTTP $httpCode"; $fail++; continue; }
    if (strlen($raw) < 1000) { echo "    FAIL: too small (".strlen($raw)." bytes)\n"; $errors[] = "[$id] too small"; $fail++; continue; }
    echo "    downloaded: " . number_format(strlen($raw)/1024, 1) . " KB\n";

    $tmp = tempnam(sys_get_temp_dir(), 'img_');
    file_put_contents($tmp, $raw);
    $info = @getimagesize($tmp);
    if (!$info) { echo "    FAIL: not valid image\n"; unlink($tmp); $errors[] = "[$id] invalid image"; $fail++; continue; }
    [$w, $h, $type] = $info;
    echo "    source: {$w}x{$h}\n";

    $src = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($tmp); break;
        case IMAGETYPE_PNG:  $src = @imagecreatefrompng($tmp); break;
        case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($tmp); break;
        case IMAGETYPE_GIF:  $src = @imagecreatefromgif($tmp); break;
    }
    unlink($tmp);
    if (!$src) { echo "    FAIL: GD decode\n"; $errors[] = "[$id] GD decode"; $fail++; continue; }

    if (max($w,$h) > $maxSize) {
        $newW = ($w>=$h) ? $maxSize : (int)round($w*($maxSize/$h));
        $newH = ($h>=$w) ? $maxSize : (int)round($h*($maxSize/$w));
    } else { $newW=$w; $newH=$h; }

    $dst = imagecreatetruecolor($newW, $newH);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);
    if ($type===IMAGETYPE_PNG||$type===IMAGETYPE_WEBP) imagealphablending($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
    imagedestroy($src);

    $outPath = $imgDir . '/' . $filename;
    $saved = imagejpeg($dst, $outPath, $quality);
    imagedestroy($dst);
    if (!$saved) { echo "    FAIL: save\n"; $errors[] = "[$id] save"; $fail++; continue; }

    clearstatcache(true, $outPath);
    echo "    saved: $filename ({$newW}x{$newH}, " . number_format(filesize($outPath)/1024,1) . " KB)\n";

    $filenameEsc = mysqli_real_escape_string($bd, $filename);
    mysqli_query($bd, "UPDATE producto_fotos SET principal=0 WHERE idProducto=$id");
    $sql = "INSERT INTO producto_fotos (idProducto, foto, principal, orden) VALUES ($id, '$filenameEsc', 1, 0)";
    if (!mysqli_query($bd, $sql)) echo "    WARN: " . mysqli_error($bd) . "\n";
    else echo "    DB OK\n";
    $ok++;
}

echo "\n=== Summary: OK=$ok, Failed=$fail ===\n";
if ($errors) { echo "Errors:\n"; foreach ($errors as $e) echo "  - $e\n"; }
