<?php
// Simple PDF text extractor - reads stream objects
function extractPdfText($file) {
    $content = file_get_contents($file);

    // Method 1: Extract text between BT and ET markers
    $texts = [];

    // Try to decompress FlateDecode streams
    preg_match_all('/stream\r?\n(.+?)\r?\nendstream/s', $content, $streams);
    foreach ($streams[1] as $stream) {
        $decoded = @gzuncompress($stream);
        if ($decoded === false) {
            $decoded = @gzinflate($stream);
        }
        if ($decoded !== false) {
            // Extract text from decoded stream
            preg_match_all('/\(([^)]+)\)\s*Tj/s', $decoded, $matches);
            foreach ($matches[1] as $t) {
                $t = trim($t);
                if (strlen($t) > 1) $texts[] = $t;
            }
            preg_match_all('/\[(.*?)\]\s*TJ/s', $decoded, $matches);
            foreach ($matches[1] as $arr) {
                preg_match_all('/\(([^)]*)\)/', $arr, $parts);
                $line = implode('', $parts[1]);
                $line = trim($line);
                if (strlen($line) > 1) $texts[] = $line;
            }
        }
    }

    // Also try raw text (non-compressed)
    preg_match_all('/\(([^)]{2,})\)\s*Tj/s', $content, $matches);
    foreach ($matches[1] as $t) {
        $t = trim($t);
        if (strlen($t) > 1 && !in_array($t, $texts)) $texts[] = $t;
    }

    return $texts;
}

$dir = 'C:/laragon/www/cellernou/distribucio/admin/facturas_ondigital_prov_old/';
$files = glob($dir . 'Factura_BR*.pdf');
sort($files);

foreach ($files as $file) {
    $name = basename($file);
    echo "\n========== $name ==========\n";
    $texts = extractPdfText($file);
    echo implode("\n", $texts) . "\n";
}

// Also extract INV_2024_0091 and INV_2024_0094
echo "\n========== INV_2024_0091.pdf ==========\n";
$texts = extractPdfText($dir . 'INV_2024_0091.pdf');
echo implode("\n", $texts) . "\n";

echo "\n========== 2DA BOLSA 40 H INV_2024_0092.pdf ==========\n";
$texts = extractPdfText($dir . '2DA BOLSA 40 H INV_2024_0092.pdf');
echo implode("\n", $texts) . "\n";

echo "\n========== INV_2024_0094 ALMABRIS GROUP 2024 S.L.pdf ==========\n";
$texts = extractPdfText($dir . 'INV_2024_0094 ALMABRIS GROUP 2024 S.L.pdf');
echo implode("\n", $texts) . "\n";
