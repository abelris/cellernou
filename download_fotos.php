<?php
/**
 * Script para descargar fotos de productos desde retailers online
 * Busca en múltiples fuentes y descarga la primera imagen válida
 */

$destDir = __DIR__ . '/images/productos/';

// Producto ID => [nombre búsqueda, nombre archivo]
$products = [
    881 => ['Oban 14 Year Old whisky', 'oban-14.jpg'],
    1090 => ['Moet Chandon Rose Imperial champagne', 'moet-rose-imperial.jpg'],
    1459 => ['Gramona Imperial Gran Reserva cava', 'gramona-imperial.jpg'],
    1516 => ['Vega Sicilia Valbuena 5', 'vega-sicilia-valbuena.jpg'],
    1521 => ['Ron Zacapa 23 solera', 'ron-zacapa-23.jpg'],
    1525 => ['Champagne Delamotte Blanc de Blancs', 'champagne-delamotte.jpg'],
    1539 => ['Chivas Regal 21 Year Old', 'chivas-regal-21.jpg'],
    1637 => ['Bollinger Special Cuvee champagne', 'bollinger-brut.jpg'],
    1639 => ['Louis Roederer Brut Vintage champagne', 'louis-roederer-brut.jpg'],
    1643 => ['Veuve Clicquot Yellow Label Brut', 'veuve-clicquot-brut.jpg'],
    1701 => ['Monkey 47 Gin', 'gin-monkey-47.jpg'],
    1843 => ['Conde de Osborne Dali brandy', 'conde-osborne-dali.jpg'],
    1859 => ['Chivas Regal 18 Year Old', 'chivas-regal-18.jpg'],
    1862 => ['Ruinart Blanc de Blancs champagne', 'ruinart-blanc-de-blancs.jpg'],
    2051 => ['Johnnie Walker Platinum 18', 'johnnie-walker-platinum.jpg'],
    2208 => ['Llopart Ex Vite cava', 'llopart-ex-vite.jpg'],
    2329 => ['Luis Felipe brandy jerez', 'cognac-luis-felipe.jpg'],
    2449 => ['Yamazaki Distillers Reserve', 'yamazaki-reserve.jpg'],
    2450 => ['Suntory Yamazaki 12', 'yamazaki-12.jpg'],
    2451 => ['Nikka Yoichi Single Malt', 'yoichi-single-malt.jpg'],
    2453 => ['Ardbeg 10 Year Old', 'ardbeg-10.jpg'],
    2662 => ['Dalmore 12 Year Old', 'dalmore-12.jpg'],
    2948 => ['Don Julio Reposado tequila', 'don-julio-reposado.jpg'],
    2957 => ['Macallan Double Cask 12', 'macallan-double-cask-12.jpg'],
    3028 => ['Lagavulin 16 Year Old', 'lagavulin-16.jpg'],
    3029 => ['Vizcarra Celia Ribera del Duero', 'vizcarra-celia.jpg'],
    3030 => ['Vizcarra Ines Ribera del Duero', 'vizcarra-ines.jpg'],
    3182 => ['Las Lamas Descendientes Palacios Bierzo', 'las-lamas-palacios.jpg'],
    3193 => ['Ballantines 30 Year Old whisky', 'ballantines-30.jpg'],
    3202 => ['Glenfiddich 15 Solera', 'glenfiddich-15.jpg'],
    3372 => ['Vall Llach Vi de Vila Priorat', 'vall-llach-vi-de-vila.jpg'],
    3407 => ['Cims de Porrera Garnatxa Priorat', 'cims-porrera-garnatxa.jpg'],
    3556 => ['Aberlour 12 Year Old whisky', 'aberlour-12.jpg'],
    3571 => ['1800 Anejo tequila', 'tequila-1800-anejo.jpg'],
    3623 => ['Finca Dofi Alvaro Palacios Priorat', 'finca-dofi.jpg'],
    3624 => ['Gratallops Alvaro Palacios Priorat', 'gratallops-palacios.jpg'],
    3638 => ['Termanthia Toro wine', 'termanthia.jpg'],
    3731 => ['Taittinger Brut Reserve champagne', 'taittinger-brut-reserva.jpg'],
    3732 => ['Bushmills 16 Year Old whiskey', 'bushmills-16.jpg'],
    3734 => ['Bushmills 21 Year Old whiskey', 'bushmills-21.jpg'],
    3743 => ['Kamiki Blended Malt whisky', 'kamiki-blended-malt.jpg'],
    3781 => ['Caol Ila 12 Year Old whisky', 'caol-ila-12.jpg'],
    3840 => ['Azabu Pure Malt whisky', 'azabu-pure-malta.jpg'],
    3877 => ['Herradura Reposado tequila', 'herradura-reposado.jpg'],
    3913 => ['Glenrothes 10 Year Old whisky', 'glenrothes-10.jpg'],
    4060 => ['Chivas Regal 12 Year Old', 'chivas-12.jpg'],
    4063 => ['Voltons Conreria Scala Dei Priorat', 'voltons-scala-dei.jpg'],
    4109 => ['Alvarez Duran Vi de Vila Porrera', 'alvarez-duran-porrera.jpg'],
    4166 => ['Recaredo Reserva Particular cava', 'recaredo-reserva-particular.jpg'],
    4185 => ['Ron Matusalem Gran Reserva 23', 'ron-matusalem-23.jpg'],
    4212 => ['Cardenal Mendoza de Lujo brandy', 'cardenal-mendoza-lujo.jpg'],
    4288 => ['Placet Palacios Remondo Rioja blanco', 'placet-palacios-remondo.jpg'],
    4290 => ['Johnnie Walker 18 Year Old', 'johnnie-walker-18.jpg'],
    4370 => ['Glenrothes 12 Year Old whisky', 'glenrothes-12.jpg'],
    4385 => ['Laphroaig Four Oak whisky', 'laphroaig-four-oak.jpg'],
    4470 => ['Tokaji 5 Puttonyos Aszu', 'tokaji-5-puttonyos.jpg'],
    4506 => ['Cardenal Mendoza Carta Real brandy', 'cardenal-mendoza-carta-real.jpg'],
    4544 => ['Dom Perignon Vintage champagne', 'dom-perignon.jpg'],
    4582 => ['Jacquart Mosaique Brut champagne', 'jacquart-mosaique.jpg'],
    4590 => ['Blecua Vinas del Vero Somontano', 'blecua-vinas-vero.jpg'],
    4603 => ['Jaime I Torres brandy', 'jaime-i-torres.jpg'],
    4606 => ['Bladnoch Vinaya Single Malt', 'bladnoch-vinaya.jpg'],
    4609 => ['Tamdhu 12 Year Old whisky', 'tamdhu-12.jpg'],
    4619 => ['Clos Abella Priorat wine', 'clos-abella.jpg'],
    4697 => ['Redbreast 12 Year Old whiskey', 'redbreast-12.jpg'],
    4741 => ['Taittinger Prestige Rose champagne', 'taittinger-rose.jpg'],
    4757 => ['Bib Tucker Small Batch Bourbon', 'bib-tucker-bourbon.jpg'],
    4894 => ['Basque Moonshiners Bikkun whisky', 'bikkun-blended-malt.jpg'],
    4895 => ['AGOT Pioneer Single Malt whisky', 'agot-pioneer.jpg'],
    4948 => ['1000 Acordes wine Spain', '1000-acordes.jpg'],
    4991 => ['Moet Chandon Ice Imperial champagne', 'moet-ice-imperial.jpg'],
    5083 => ['Alta Alella Mirgin Laieta cava', 'mirgin-laieta.jpg'],
    5157 => ['Roca Grisa Priorat Cal Pla', 'roca-grisa.jpg'],
    5182 => ['Remy Martin VSOP cognac', 'remy-martin-vsop.jpg'],
    5183 => ['Hennessy VS cognac', 'hennessy-vs.jpg'],
    5205 => ['Dominio del Aguila Canta la Perdiz', 'canta-la-perdiz.jpg'],
    5496 => ['Glenrothes Whisky Makers Cut', 'glenrothes-100-proof.jpg'],
    5539 => ['Louis Roederer Collection champagne', 'louis-roederer-collection.jpg'],
    5641 => ['Ron Abuelo Panama', 'ron-abuelo.jpg'],
    5731 => ['Jack Daniels Old No 7 Tennessee', 'jack-daniels.jpg'],
    5733 => ['Loch Lomond 12 Year Old whisky', 'loch-lomond-12.jpg'],
    5786 => ['Celler Pascona Petito Montsant', 'petito-pascona.jpg'],
    5799 => ['Plantation XO 20th Anniversary rum', 'plantation-xo.jpg'],
    5809 => ['Suntory The Chita whisky', 'the-chita.jpg'],
    5818 => ['Tamdhu 15 Year Old whisky', 'tamdhu-15.jpg'],
    5819 => ['Smokehead Sherry Cask Blast whisky', 'smokehead-sherry-cask.jpg'],
    5881 => ['Anis del Mono Dulce', 'anis-del-mono.jpg'],
    5886 => ['Cardenal Mendoza Carta Real brandy', 'cardenal-mendoza-carta-real-2.jpg'],
    6070 => ['Clement VSOP rhum agricole', 'clement-vsop.jpg'],
    6071 => ['Henriques Henriques Madeira', 'rodney-princessa.jpg'],
    6099 => ['Ruinart Blanc de Blancs champagne', 'ruinart-blanc-blancs.jpg'],
    6102 => ['Glenmorangie Accord 12 whisky', 'glenmorangie-accord-12.jpg'],
    6202 => ['Chicken Cock Straight Bourbon', 'chicken-cock-bourbon.jpg'],
    6297 => ['Macan Clasico Rioja Benjamin Rothschild', 'macan-clasico.jpg'],
    6327 => ['200 Monges Reserva Rioja', '200-monges-reserva.jpg'],
    6400 => ['Clos Galena Priorat wine', 'clos-galena.jpg'],
    6401 => ['Tokaji Oremus Late Harvest', 'tokaji-oremus.jpg'],
    6403 => ['Pommery Brut Royal champagne', 'pommery-brut-royal.jpg'],
    1526 => ['Champagne Delamotte Brut', 'champagne-delamotte-brut.jpg'],
];

// Función para descargar imagen desde URL
function downloadImage($url, $destPath) {
    $ctx = stream_context_create([
        'http' => [
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n",
            'timeout' => 15,
            'follow_location' => true,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);

    $data = @file_get_contents($url, false, $ctx);
    if ($data === false || strlen($data) < 5000) return false;

    // Check it's actually an image (not HTML)
    $header = substr($data, 0, 20);
    if (strpos($header, '<') === 0 || strpos($header, '<!') === 0) return false;

    file_put_contents($destPath, $data);
    return true;
}

// Función para buscar imagen en Google y extraer URL
function searchAndDownload($query, $destPath) {
    // Intentar con wine-searcher
    $searchUrl = "https://www.wine-searcher.com/find/" . urlencode($query);
    $ctx = stream_context_create([
        'http' => [
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n",
            'timeout' => 10,
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);

    $html = @file_get_contents($searchUrl, false, $ctx);
    if ($html) {
        // Buscar URLs de imagen en la página
        if (preg_match_all('/src=["\']([^"\']*(?:\.jpg|\.jpeg|\.png|\.webp)[^"\']*)/i', $html, $matches)) {
            foreach ($matches[1] as $imgUrl) {
                if (strpos($imgUrl, 'placeholder') !== false) continue;
                if (strpos($imgUrl, 'icon') !== false) continue;
                if (strpos($imgUrl, 'logo') !== false) continue;
                if (strlen($imgUrl) < 30) continue;

                if (strpos($imgUrl, '//') === 0) $imgUrl = 'https:' . $imgUrl;
                if (downloadImage($imgUrl, $destPath)) return true;
            }
        }
    }

    // Intentar con vivino
    $searchUrl2 = "https://www.vivino.com/search/wines?q=" . urlencode($query);
    $html2 = @file_get_contents($searchUrl2, false, $ctx);
    if ($html2) {
        if (preg_match_all('/src=["\']([^"\']*(?:\.jpg|\.jpeg|\.png|\.webp)[^"\']*)/i', $html2, $matches)) {
            foreach ($matches[1] as $imgUrl) {
                if (strpos($imgUrl, 'placeholder') !== false) continue;
                if (strpos($imgUrl, 'icon') !== false) continue;
                if (strpos($imgUrl, 'logo') !== false) continue;
                if (strlen($imgUrl) < 30) continue;

                if (strpos($imgUrl, '//') === 0) $imgUrl = 'https:' . $imgUrl;
                if (downloadImage($imgUrl, $destPath)) return true;
            }
        }
    }

    return false;
}

$success = 0;
$failed = 0;
$failedList = [];

foreach ($products as $id => $info) {
    $query = $info[0];
    $filename = $info[1];
    $destPath = $destDir . $filename;

    // Skip if file already exists
    if (file_exists($destPath) && filesize($destPath) > 5000) {
        echo "SKIP $id $filename (already exists)\n";
        $success++;
        continue;
    }

    echo "Downloading $id: $query... ";

    if (searchAndDownload($query, $destPath)) {
        echo "OK (" . round(filesize($destPath)/1024) . "KB)\n";
        $success++;
    } else {
        echo "FAILED\n";
        $failed++;
        $failedList[] = "$id|$filename|$query";
    }

    // Small delay to be polite
    usleep(500000);
}

echo "\n=== RESUMEN ===\n";
echo "Descargadas: $success\n";
echo "Fallidas: $failed\n";
if (!empty($failedList)) {
    echo "\nFallidas:\n";
    foreach ($failedList as $f) echo "  $f\n";
}
