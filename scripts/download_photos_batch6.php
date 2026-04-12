<?php
/**
 * Batch 6 photo downloader.
 *
 * Takes a list of [product_id, filename, source_url], downloads each image,
 * resizes to max 1200x1200 at quality 82, saves to images/productos/,
 * and registers in producto_fotos as principal.
 *
 * Respects the project-wide rule: 1200x1200 max, quality 82.
 * Not accessible via web (scripts/ blocked in .htaccess).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

ini_set('memory_limit', '512M');
set_time_limit(0);

require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
mysqli_set_charset($bd, 'utf8mb4');

$imgDir = realpath(dirname(__DIR__) . '/images/productos');
$maxSize = 1200;
$quality = 82;

// -----------------------------------------------------------------------------
// Lista de descargas: [productId, filename, sourceUrl]
// -----------------------------------------------------------------------------
$downloads = [
    // Lista encontrada en busquedas web (Vinissimus, Bodeboca). Uso p500x en Vinissimus
    // para obtener la máxima resolución disponible (el CDN sirve hasta 800px nativo).
    [352,  'celler-mariol-merlot.jpg',          'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/cmmlt20_anv800_1721803395.png'],
    [355,  'celler-mariol-samso.jpg',           'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/cmsms20_anv800_1721797773.png'],
    [366,  'santa-digna-rosado.jpg',            'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/trsdr25_anv800.png'],
    [376,  'cutty-sark.jpg',                    'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/de/cuttysa_anv800.png'],
    [396,  'sumarroca-temps-de-flors.jpg',      'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/tmpfl24_anv800.png'],
    [430,  'valdespino-fino-inocente.jpg',      'https://www.bodeboca.com/sites/default/files/wines/2022-10/bot-valdespino-fino-inocente-2021.jpg'],
    [433,  'raimat-clamor-tinto.jpg',           'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/rclam24_anv800.png'],
    [578,  'can-feixes-negre-seleccio.jpg',     'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/fexns22_anv800.png'],
    [587,  'parxet-brut.jpg',                   'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/parxetb_anv800.png'],
    [631,  'espinaler-vermut-blanco.jpg',       'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/de/espinbl_anv800.png'],
    [727,  'sangria-lolea-blanca.jpg',          'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/de/sgr2blc_anv800_1716905302.png'],
    [728,  'sangria-lolea-tinta.jpg',           'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/de/sgr1tin_anv800_1716902731.png'],
    [793,  'cava-bertha-lounge.jpg',            'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/berloun_anv800.png'],
    [794,  'cava-mirgin-reserva.jpg',           'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/mirgr20_anv800.png'],
    [984,  'vodka-smirnoff.jpg',                'https://www.bodeboca.com/sites/default/files/spirit/2020-07/bot-smirnoff-red-label-70cl.jpg'],
    [1164, 'cava-torello-brut-reserva.jpg',     'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/torbr22_anv800.png'],
    [1260, 'cava-perelada-brut-reserva.jpg',    'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/pebrr23_anv800.png'],
    // Fallbacks razonables (mismo productor, variante cercana) para productos ya descatalogados:
    // - 429 Viñas del Vero Macabeo Chardonnay (descatalogado) -> sustituyo por Viñas del Vero Chardonnay
    [429,  'vinas-del-vero-chardonnay.jpg',     'https://cdn.vinissimus.com/img/unsafe/p500x/plain/local:///prfmtgrande/vi/vvcha25_anv800_1774509088.png'],

    // --- Batch 6b: 7 productos adicionales encontrados en Vila Viniteca y Lafuente ---
    [114, 'larios-triple-sec.jpg',        'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v005907.jpg'],
    [397, 'sumarroca-riesling.jpg',       'https://lafuente.es/wp-content/uploads/2019/10/sumarroca_riesling.jpg'],
    [398, 'sumarroca-gewurztraminer.jpg', 'https://lafuente.es/wp-content/uploads/2015/11/SUMARROCA-GEWUSTRAMINER.jpg'],
    [487, 'ron-pujol.jpg',                'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v006109.jpg'],
    [689, 'whisky-yachting-peche.jpg',    'https://lafuente.es/wp-content/uploads/2015/11/YATCHING-WHISKY-PECHE.jpg'],
    // 796/797 fallback: son "Lambrusco Violetta De Giorgi" (no está en shops), uso Lambrusco genérico de Vila Viniteca
    [796, 'lambrusco-bianco.jpg',         'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v025984.jpg'],
    [797, 'lambrusco-rosso.jpg',          'https://www.vilaviniteca.es/media/catalog/product/cache/f1d8896918e1fa1369f04c5295aeb544/v/0/v031465.jpg'],
];

// -----------------------------------------------------------------------------
// Procesar
// -----------------------------------------------------------------------------
$ok = 0;
$fail = 0;
$errors = [];

foreach ($downloads as [$id, $filename, $url]) {
    echo "--- [$id] $filename\n";
    echo "    URL: $url\n";

    // 1) Descargar con curl (más robusto que file_get_contents con URLs raras como local:///)
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
        CURLOPT_HTTPHEADER     => [
            'Accept: image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
            'Referer: https://www.vinissimus.com/',
        ],
    ]);
    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        echo "    FAIL: curl error: $curlErr\n";
        $errors[] = "[$id] curl error: $curlErr";
        $fail++;
        continue;
    }
    if ($httpCode !== 200) {
        echo "    FAIL: HTTP $httpCode\n";
        $errors[] = "[$id] HTTP $httpCode: $url";
        $fail++;
        continue;
    }
    if ($raw === false || strlen($raw) < 1000) {
        echo "    FAIL: download returned " . ($raw === false ? 'false' : strlen($raw) . ' bytes') . "\n";
        $errors[] = "[$id] download failed: $url";
        $fail++;
        continue;
    }
    echo "    downloaded: " . number_format(strlen($raw) / 1024, 1) . " KB\n";

    // 2) Guardar a temporal y cargar con GD
    $tmp = tempnam(sys_get_temp_dir(), 'img_');
    file_put_contents($tmp, $raw);

    $info = @getimagesize($tmp);
    if (!$info) {
        echo "    FAIL: not a valid image\n";
        unlink($tmp);
        $errors[] = "[$id] invalid image: $url";
        $fail++;
        continue;
    }

    [$w, $h, $type] = $info;
    echo "    source: {$w}x{$h}, type=$type\n";

    $src = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($tmp); break;
        case IMAGETYPE_PNG:  $src = @imagecreatefrompng($tmp); break;
        case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($tmp); break;
        case IMAGETYPE_GIF:  $src = @imagecreatefromgif($tmp); break;
    }
    unlink($tmp);

    if (!$src) {
        echo "    FAIL: GD couldn't decode\n";
        $errors[] = "[$id] GD decode failed: $url";
        $fail++;
        continue;
    }

    // 3) Calcular nuevas dimensiones (solo reducir)
    if (max($w, $h) > $maxSize) {
        if ($w >= $h) {
            $newW = $maxSize;
            $newH = (int) round($h * ($maxSize / $w));
        } else {
            $newH = $maxSize;
            $newW = (int) round($w * ($maxSize / $h));
        }
    } else {
        $newW = $w;
        $newH = $h;
    }

    // 4) Preparar canvas destino
    $dst = imagecreatetruecolor($newW, $newH);

    // Fondo blanco para transparencias (siempre guardamos JPEG)
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);

    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
        imagealphablending($dst, true);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
    imagedestroy($src);

    // 5) Guardar como JPEG q82 (el proyecto usa .jpg/.png indistintamente;
    //    para mantener simple, siempre salida JPEG salvo extensión explícita)
    $outPath = $imgDir . '/' . $filename;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $saved = false;
    if ($ext === 'png') {
        // Si el usuario quiere PNG (transparencia real), guardamos PNG
        $saved = imagepng($dst, $outPath, 9);
    } else {
        $saved = imagejpeg($dst, $outPath, $quality);
    }
    imagedestroy($dst);

    if (!$saved) {
        echo "    FAIL: couldn't save $outPath\n";
        $errors[] = "[$id] save failed: $outPath";
        $fail++;
        continue;
    }

    clearstatcache(true, $outPath);
    $finalSize = filesize($outPath);
    echo "    saved: $filename ({$newW}x{$newH}, " . number_format($finalSize / 1024, 1) . " KB)\n";

    // 6) Registrar en producto_fotos
    $filenameEsc = mysqli_real_escape_string($bd, $filename);

    // Si ya hay una foto principal para este producto, pasarla a no-principal
    mysqli_query($bd, "UPDATE producto_fotos SET principal=0 WHERE idProducto=$id");

    // Insertar como principal
    $sql = "INSERT INTO producto_fotos (idProducto, foto, principal, orden)
            VALUES ($id, '$filenameEsc', 1, 0)";
    if (!mysqli_query($bd, $sql)) {
        echo "    WARN: DB insert failed: " . mysqli_error($bd) . "\n";
        $errors[] = "[$id] DB insert: " . mysqli_error($bd);
    } else {
        echo "    DB OK: producto_fotos row inserted\n";
    }

    $ok++;
}

echo "\n=== Summary ===\n";
echo "OK:     $ok\n";
echo "Failed: $fail\n";
if ($errors) {
    echo "\nErrors:\n";
    foreach ($errors as $e) echo "  - $e\n";
}
