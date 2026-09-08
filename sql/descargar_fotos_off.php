<?php
/**
 * Descarga de fotos de producto desde Open Food Facts.
 *
 * Contexto: las fuentes que se usaron en lotes anteriores (Bodeboca, Vinissimus,
 * Decántalo, The Whisky Exchange) devuelven ahora 403/429 a peticiones
 * automatizadas. Open Food Facts es una base abierta (imágenes bajo CC-BY-SA)
 * que sí permite consulta programática, aunque su cobertura de vinos y
 * destilados es parcial y la calidad es irregular.
 *
 * El script es conservador a propósito:
 *  - solo toca productos cuya fila en producto_fotos apunta a un archivo ausente,
 *  - descarta imágenes pequeñas o que no sean JPEG/PNG,
 *  - guarda con el nombre de archivo que ya espera la base de datos,
 *  - no modifica ni una fila: si la descarga falla, no pasa nada.
 *
 * Uso:  php sql/descargar_fotos_off.php [limite]        (simulación)
 *       php sql/descargar_fotos_off.php [limite] --write (descarga de verdad)
 */

$LIMITE   = isset($argv[1]) ? (int) $argv[1] : 25;
$ESCRIBIR = in_array('--write', $argv, true);
$DIR      = __DIR__ . '/../images/productos/';
$UA       = 'CellerNouCatalog/1.0 (cellernou@cellernou.com)';
$MIN_BYTES = 6000;   // por debajo de esto suele ser un placeholder
$MIN_LADO  = 350;    // Google pide imágenes de al menos 300 px de lado

$bd = mysqli_connect('localhost', 'root', '', 'cellernodb') or die("Sin conexión\n");
mysqli_query($bd, "SET SESSION sql_mode=''");
mysqli_set_charset($bd, 'utf8mb4');

/** Petición HTTP sencilla con user-agent identificado. */
function http($url, $ua, $timeout = 25) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_USERAGENT      => $ua,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $body];
}

/** Limpiar el nombre del producto para buscarlo: fuera formatos y grados. */
function terminoBusqueda($nombre) {
    $n = mb_strtolower($nombre, 'UTF-8');
    $n = preg_replace('/\b\d+\s*(cl|ml|l|cc)\b/u', ' ', $n);
    $n = preg_replace('/\b\d+(\.\d+)?\s*º\b/u', ' ', $n);
    $n = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $n);
    $n = preg_replace('/\s+/u', ' ', $n);
    return trim($n);
}

// Productos con precio y stock cuya foto está referenciada pero no descargada:
// son los que de verdad pueden ganar un resultado enriquecido.
$sql = "SELECT DISTINCT p.id, p.nombre, pf.foto, ppt.pvp
        FROM productos p
        JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
        JOIN (SELECT idProducto FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) st
             ON st.idProducto = p.id
        JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
        WHERE p.activo = 1
          AND p.idSector IN (8,10,2,21,12,14)
          AND ppt.pvp > 0
          AND pf.foto IS NOT NULL AND pf.foto <> ''
        ORDER BY ppt.pvp DESC";

$res = mysqli_query($bd, $sql) or die(mysqli_error($bd));

$pendientes = [];
while ($x = mysqli_fetch_assoc($res)) {
    if (!is_file($DIR . $x['foto'])) $pendientes[] = $x;
}

printf("Pendientes totales: %d  |  se intentarán: %d  |  modo: %s\n\n",
    count($pendientes), min($LIMITE, count($pendientes)),
    $ESCRIBIR ? 'DESCARGA' : 'simulación');

$ok = 0; $sin = 0; $malas = 0; $i = 0;

foreach ($pendientes as $prod) {
    if ($i++ >= $LIMITE) break;

    $termino = terminoBusqueda($prod['nombre']);
    $url = 'https://world.openfoodfacts.org/cgi/search.pl?'
        . http_build_query([
            'search_terms' => $termino,
            'search_simple' => 1,
            'action' => 'process',
            'json' => 1,
            'page_size' => 5,
            'fields' => 'product_name,brands,image_front_url,code',
        ]);

    [$code, $body] = http($url, $UA);
    $data = $body ? json_decode($body, true) : null;

    if ($code !== 200 || !is_array($data)) {
        printf("  [%3d] %-42s  API %s\n", $prod['id'], mb_substr($prod['nombre'], 0, 42), $code);
        $sin++;
        usleep(1200000);
        continue;
    }

    $imagen = null;
    foreach ($data['products'] ?? [] as $p) {
        if (!empty($p['image_front_url'])) { $imagen = $p['image_front_url']; break; }
    }

    if (!$imagen) {
        printf("  [%3d] %-42s  sin imagen en OFF\n", $prod['id'], mb_substr($prod['nombre'], 0, 42));
        $sin++;
        usleep(1200000);
        continue;
    }

    // Pedir la versión grande en lugar de la miniatura de 400 px
    $imagenGrande = preg_replace('/\.(\d+)\.400\.jpg$/', '.$1.full.jpg', $imagen);

    [$c2, $bin] = http($imagenGrande, $UA, 40);
    if ($c2 !== 200 || strlen((string) $bin) < $MIN_BYTES) {
        [$c2, $bin] = http($imagen, $UA, 40);
    }

    $bytes = strlen((string) $bin);
    $magic = $bin ? strtoupper(bin2hex(substr($bin, 0, 2))) : '';
    $esImagen = in_array($magic, ['FFD8', '8950'], true);

    if ($c2 !== 200 || $bytes < $MIN_BYTES || !$esImagen) {
        printf("  [%3d] %-42s  descarte (%d B, %s)\n", $prod['id'], mb_substr($prod['nombre'], 0, 42), $bytes, $magic ?: '--');
        $malas++;
        usleep(1200000);
        continue;
    }

    $info = @getimagesizefromstring($bin);
    if (!$info || min($info[0], $info[1]) < $MIN_LADO) {
        printf("  [%3d] %-42s  descarte (%dx%d, muy pequeña)\n",
            $prod['id'], mb_substr($prod['nombre'], 0, 42), $info[0] ?? 0, $info[1] ?? 0);
        $malas++;
        usleep(1200000);
        continue;
    }

    if ($ESCRIBIR) {
        file_put_contents($DIR . $prod['foto'], $bin);
    }
    printf("  [%3d] %-42s  OK %dx%d -> %s\n",
        $prod['id'], mb_substr($prod['nombre'], 0, 42), $info[0], $info[1], $prod['foto']);
    $ok++;

    usleep(1200000); // ~1 req/s: la API pide no saturarla
}

printf("\nDescargadas: %d   |   sin resultado: %d   |   descartadas por calidad: %d\n", $ok, $sin, $malas);
if (!$ESCRIBIR) echo "(simulación: no se ha escrito ningún archivo; añade --write)\n";
