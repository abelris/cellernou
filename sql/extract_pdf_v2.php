<?php
function extractPdfTextV2($file) {
    $content = file_get_contents($file);
    $texts = [];

    // Find all streams and try multiple decompression
    preg_match_all('/stream\r?\n([\s\S]+?)\r?\nendstream/m', $content, $streams);
    foreach ($streams[1] as $stream) {
        // Try various decompressions
        $decoded = @gzuncompress($stream);
        if ($decoded === false) $decoded = @gzinflate($stream);
        if ($decoded === false) {
            // Try with offset
            for ($i = 0; $i < min(10, strlen($stream)); $i++) {
                $decoded = @gzinflate(substr($stream, $i));
                if ($decoded !== false) break;
            }
        }

        if ($decoded !== false) {
            // BT...ET blocks
            preg_match_all('/BT\s*(.*?)\s*ET/s', $decoded, $btBlocks);
            foreach ($btBlocks[1] as $block) {
                // Tj operator
                preg_match_all('/\(([^)]*)\)\s*Tj/s', $block, $tj);
                foreach ($tj[1] as $t) {
                    $t = trim($t);
                    if (strlen($t) > 0) $texts[] = $t;
                }
                // TJ operator (array)
                preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $tjArr);
                foreach ($tjArr[1] as $arr) {
                    preg_match_all('/\(([^)]*)\)/', $arr, $parts);
                    $line = implode('', $parts[1]);
                    $line = trim($line);
                    if (strlen($line) > 0) $texts[] = $line;
                }
            }

            // If no BT/ET found, try direct extraction
            if (empty($texts)) {
                preg_match_all('/\(([^)]{1,})\)/', $decoded, $parens);
                foreach ($parens[1] as $t) {
                    $t = trim($t);
                    if (strlen($t) > 1 && preg_match('/[a-zA-Z0-9]/', $t)) {
                        $texts[] = $t;
                    }
                }
            }
        }
    }

    return array_unique($texts);
}

$dir = 'C:/laragon/www/cellernou/distribucio/admin/facturas_ondigital_prov_old/';

// Test with one BR file and one INV file
$testFiles = [
    'Factura_BR240090.pdf',
    'Factura_BR250058.pdf',
    'INV_2024_0091.pdf',
];

foreach ($testFiles as $name) {
    echo "\n========== $name ==========\n";
    $texts = extractPdfTextV2($dir . $name);
    if (empty($texts)) {
        echo "(no text found)\n";
        // Show raw info about the PDF
        $raw = file_get_contents($dir . $name);
        echo "File size: " . strlen($raw) . "\n";

        // Check for /Type /Font entries to understand encoding
        preg_match_all('/\/BaseFont\s*\/([^\s\/]+)/', $raw, $fonts);
        if ($fonts[1]) echo "Fonts: " . implode(', ', $fonts[1]) . "\n";

        // Check for ToUnicode maps
        $hasUnicode = strpos($raw, '/ToUnicode') !== false;
        echo "ToUnicode: " . ($hasUnicode ? 'yes' : 'no') . "\n";

        // Check for CIDFont
        $hasCID = strpos($raw, '/CIDFont') !== false;
        echo "CIDFont: " . ($hasCID ? 'yes' : 'no') . "\n";

        // Show filter types
        preg_match_all('/\/Filter\s*\/([^\s\/]+)/', $raw, $filters);
        if ($filters[1]) echo "Filters: " . implode(', ', array_unique($filters[1])) . "\n";

        // Try to find any readable text in the raw content
        preg_match_all('/\/T\s*\(([^)]+)\)/', $raw, $formFields);
        if ($formFields[1]) echo "Form fields: " . implode(', ', $formFields[1]) . "\n";
    } else {
        echo implode(" | ", $texts) . "\n";
    }
}
