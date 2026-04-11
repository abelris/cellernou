<?php
/**
 * Batch resize de imágenes de productos.
 *
 * Uso:
 *   php scripts/resize_productos.php [--dry-run] [--max=1200]
 *
 * Qué hace:
 * - Lista /images/productos/
 * - Para cada imagen cuya dimensión mayor supere $maxSize (por defecto 1200 px),
 *   la redimensiona preservando aspect ratio.
 * - Antes de sobreescribir, copia el original a /images/productos.backup.{timestamp}/
 * - Si el guardado falla, restaura desde backup.
 *
 * Formatos soportados: JPG, JPEG, PNG, WebP, GIF.
 *
 * NO es accesible vía web (bloqueado en .htaccess).
 */

// Rechazar ejecución por web (seguridad extra)
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script must be run from the command line.\n");
}

ini_set('memory_limit', '512M');
set_time_limit(0);

// -----------------------------------------------------------------------------
// Config
// -----------------------------------------------------------------------------
$srcDir = realpath(__DIR__ . '/../images/productos');
$maxSize = 1200;
$jpegQuality = 82;
$pngCompression = 9; // 0-9, 9 = max compression (lossless)
$webpQuality = 82;
$dryRun = in_array('--dry-run', $argv, true);

// Permitir override de --max=N
foreach ($argv as $arg) {
    if (preg_match('/^--max=(\d+)$/', $arg, $m)) {
        $maxSize = (int) $m[1];
    }
}

if (!$srcDir || !is_dir($srcDir)) {
    fwrite(STDERR, "ERROR: directorio no encontrado: " . __DIR__ . "/../images/productos\n");
    exit(1);
}

$backupDir = dirname($srcDir) . '/productos.backup.' . date('Y-m-d_His');

echo "Source:   $srcDir\n";
echo "Max size: {$maxSize}px\n";
echo "Mode:     " . ($dryRun ? "DRY-RUN (no escribirá nada)" : "LIVE") . "\n";

if (!$dryRun) {
    if (!mkdir($backupDir, 0755, true)) {
        fwrite(STDERR, "ERROR: no se pudo crear backup dir: $backupDir\n");
        exit(1);
    }
    echo "Backup:   $backupDir\n";
}

echo str_repeat('-', 70) . "\n";

// -----------------------------------------------------------------------------
// Procesar ficheros
// -----------------------------------------------------------------------------
$stats = [
    'total'        => 0,
    'resized'      => 0,
    'skipped'      => 0,
    'errors'       => 0,
    'bytes_before' => 0,
    'bytes_after'  => 0,
];

$files = scandir($srcDir);
sort($files);

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = $srcDir . '/' . $file;
    if (!is_file($path)) continue;

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) continue;

    $stats['total']++;
    $sizeBefore = filesize($path);
    $stats['bytes_before'] += $sizeBefore;

    $info = @getimagesize($path);
    if (!$info) {
        fwrite(STDERR, "WARN: no se pudo leer info de $file\n");
        $stats['errors']++;
        $stats['bytes_after'] += $sizeBefore;
        continue;
    }

    [$width, $height] = $info;

    if (max($width, $height) <= $maxSize) {
        $stats['skipped']++;
        $stats['bytes_after'] += $sizeBefore;
        continue;
    }

    // Nuevas dimensiones preservando aspect ratio
    if ($width >= $height) {
        $newWidth = $maxSize;
        $newHeight = (int) round($height * ($maxSize / $width));
    } else {
        $newHeight = $maxSize;
        $newWidth = (int) round($width * ($maxSize / $height));
    }

    if ($dryRun) {
        printf("[DRY] %-50s %dx%d (%s KB) -> %dx%d\n",
            substr($file, 0, 50), $width, $height,
            number_format($sizeBefore / 1024, 1), $newWidth, $newHeight);
        $stats['bytes_after'] += $sizeBefore;
        continue;
    }

    // Cargar imagen original
    $src = null;
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $src = @imagecreatefromjpeg($path);
            break;
        case 'png':
            $src = @imagecreatefrompng($path);
            break;
        case 'webp':
            $src = @imagecreatefromwebp($path);
            break;
        case 'gif':
            $src = @imagecreatefromgif($path);
            break;
    }

    if (!$src) {
        fwrite(STDERR, "WARN: no se pudo cargar $file\n");
        $stats['errors']++;
        $stats['bytes_after'] += $sizeBefore;
        continue;
    }

    // Canvas destino
    $dst = imagecreatetruecolor($newWidth, $newHeight);

    // Preservar transparencia en formatos que la soporten
    if (in_array($ext, ['png', 'gif', 'webp'], true)) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Backup del original antes de sobreescribir
    $backupPath = $backupDir . '/' . $file;
    if (!copy($path, $backupPath)) {
        fwrite(STDERR, "ERROR: backup falló para $file\n");
        imagedestroy($src);
        imagedestroy($dst);
        $stats['errors']++;
        $stats['bytes_after'] += $sizeBefore;
        continue;
    }

    // Guardar resized
    $saved = false;
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $saved = imagejpeg($dst, $path, $jpegQuality);
            break;
        case 'png':
            $saved = imagepng($dst, $path, $pngCompression);
            break;
        case 'webp':
            $saved = imagewebp($dst, $path, $webpQuality);
            break;
        case 'gif':
            $saved = imagegif($dst, $path);
            break;
    }

    imagedestroy($src);
    imagedestroy($dst);

    if (!$saved) {
        fwrite(STDERR, "ERROR: guardado falló para $file, restaurando backup\n");
        copy($backupPath, $path);
        $stats['errors']++;
        $stats['bytes_after'] += $sizeBefore;
        continue;
    }

    clearstatcache(true, $path);
    $sizeAfter = filesize($path);
    $stats['bytes_after'] += $sizeAfter;
    $stats['resized']++;

    printf("%-50s %dx%d -> %dx%d  (%s KB -> %s KB, -%d%%)\n",
        substr($file, 0, 50), $width, $height, $newWidth, $newHeight,
        number_format($sizeBefore / 1024, 1),
        number_format($sizeAfter / 1024, 1),
        round(100 - ($sizeAfter / $sizeBefore * 100)));
}

// -----------------------------------------------------------------------------
// Resumen
// -----------------------------------------------------------------------------
echo str_repeat('-', 70) . "\n";
echo "Resumen\n";
printf("  Total:         %d ficheros\n", $stats['total']);
printf("  Redimensionados: %d\n", $stats['resized']);
printf("  Saltados:      %d (ya eran <= {$maxSize}px)\n", $stats['skipped']);
printf("  Errores:       %d\n", $stats['errors']);
printf("  Antes:         %s MB\n", number_format($stats['bytes_before'] / 1024 / 1024, 1));
printf("  Después:       %s MB\n", number_format($stats['bytes_after'] / 1024 / 1024, 1));

$savedBytes = $stats['bytes_before'] - $stats['bytes_after'];
$savedPct = $stats['bytes_before'] > 0 ? (100 - ($stats['bytes_after'] / $stats['bytes_before'] * 100)) : 0;
printf("  Ahorro:        %s MB (%.0f%%)\n",
    number_format($savedBytes / 1024 / 1024, 1), $savedPct);

if (!$dryRun) {
    echo "\nBackup de originales: $backupDir\n";
    echo "Si algo salió mal, puedes restaurar copiando los ficheros de vuelta.\n";
}
