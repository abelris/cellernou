<?php
/**
 * IndexNow: avisa a Bing (y al resto de buscadores que usan IndexNow:
 * Yandex, Seznam, Naver…) de las URL nuevas o cambiadas de cellernou.com,
 * para que no esperen a volver a pasar por el sitemap.
 *
 * Uso (CLI, en producción desde el cron como cellernou_com):
 *   php scripts/indexnow.php              envía lo nuevo o cambiado desde la última vez
 *   php scripts/indexnow.php --simular    enseña lo que enviaría, sin enviar ni guardar
 *   php scripts/indexnow.php --sitemap=http://localhost:8000/sitemap.xml   (pruebas en local)
 *
 * Qué cuenta como nuevo o cambiado:
 *  - URL que aparece en el sitemap y no estaba la vez anterior (artículo que
 *    se publica, ficha que pasa a indexable, página nueva…);
 *  - artículos cuyo lastmod del sitemap ha cambiado (editados);
 *  - fichas con texto o foto nuevos (producto_textos.updated_at, producto_fotos.created_at);
 *  - categorías, tipos, D.O. y orígenes con texto nuevo o editado (categoria_textos.updated_at).
 * Solo se envían URL que están en el sitemap, es decir, indexables.
 * La primera vez (sin estado guardado) se envía el sitemap entero.
 *
 * Estado: uploads/cache/indexnow.json (URL del último envío y fecha).
 * Clave: INDEXNOW_KEY en includes/config.php, publicada en /<clave>.txt.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

define('ROOT_PATH', dirname(__DIR__));
// connexio.php elige las credenciales de producción por HTTP_HOST, que en CLI
// no existe: en el servidor (/var/www/…) se hace pasar por cellernou.com.
if (!isset($_SERVER['HTTP_HOST']) && strpos(ROOT_PATH, '/var/www/') === 0) {
    $_SERVER['HTTP_HOST'] = 'cellernou.com';
}
require_once ROOT_PATH . '/includes/config.php';

$opciones = getopt('', ['simular', 'sitemap:']);
$simular  = isset($opciones['simular']);
$base     = rtrim(SITE_URL, '/');
$host     = parse_url($base, PHP_URL_HOST);
$estadoArchivo = ROOT_PATH . '/uploads/cache/indexnow.json';

function salir(string $mensaje, int $codigo = 1): void
{
    fwrite(STDERR, date('[Y-m-d H:i:s] ') . "indexnow: $mensaje\n");
    exit($codigo);
}

function decir(string $mensaje): void
{
    echo date('[Y-m-d H:i:s] ') . "indexnow: $mensaje\n";
}

// La hora de referencia es la de la base de datos (puede no coincidir con
// la zona horaria de PHP) y se toma antes de leer nada: lo que cambie mientras
// el script corre se envía la próxima vez.
$ahora = mysqli_fetch_row(mysqli_query($bd, 'SELECT NOW()'))[0];

// ---------------------------------------------------------------
// 1. URL indexables: las del sitemap, recién generado.
// ---------------------------------------------------------------
$sitemapUrl = $opciones['sitemap'] ?? $base . '/sitemap.xml';
if (!isset($opciones['sitemap'])) {
    // El sitemap se guarda en caché 6 horas: se borra para que salga al día
    // (un artículo programado para hoy tiene que aparecer ya).
    @unlink(ROOT_PATH . '/uploads/cache/sitemap.xml');
}
$xml = @file_get_contents($sitemapUrl, false, stream_context_create(['http' => [
    'timeout' => 180,
    'header'  => "User-Agent: cellernou-indexnow
",
]]));
if ($xml === false || strpos($xml, '<urlset') === false) {
    salir("no he podido leer el sitemap ($sitemapUrl)");
}
$sitemap = [];
foreach (simplexml_load_string($xml)->url as $u) {
    $loc = trim((string) $u->loc);
    // Con --sitemap en local el sitemap lleva igualmente las URL de producción
    // (SITE_URL), que son las que se avisan.
    if (strpos($loc, $base . '/') === 0 || $loc === $base) {
        $sitemap[$loc] = trim((string) $u->lastmod);
    }
}
if (count($sitemap) < 50) {
    salir('el sitemap trae solo ' . count($sitemap) . ' URL: no sigo, algo va mal');
}

// ---------------------------------------------------------------
// 2. Qué ha cambiado desde el último envío.
// ---------------------------------------------------------------
$estado = is_file($estadoArchivo) ? json_decode((string) file_get_contents($estadoArchivo), true) : null;
$primeraVez = !is_array($estado) || empty($estado['urls']);
$enviar = [];

if ($primeraVez) {
    $enviar = array_keys($sitemap);
    decir('primera vez: se envía el sitemap entero');
} else {
    $antes = $estado['urls'];
    $desde = $estado['ultima'];
    foreach ($sitemap as $loc => $lastmod) {
        // El lastmod solo dice algo en los artículos: las páginas fijas llevan
        // la fecha del día y se enviarían todos los días sin haber cambiado.
        $articulo = strpos($loc, $base . '/wineworld/') === 0;
        if (!array_key_exists($loc, $antes) || ($articulo && $antes[$loc] !== $lastmod)) {
            $enviar[$loc] = true;
        }
    }

    $desdeSql = mysqli_real_escape_string($bd, $desde);
    $ids = function (string $sql) use ($bd): array {
        $r = mysqli_query($bd, $sql);
        $out = [];
        while ($r && ($f = mysqli_fetch_row($r))) {
            $out[(int) $f[0]] = true;
        }
        return $out;
    };
    $productos = $ids("SELECT DISTINCT idProducto FROM producto_textos WHERE updated_at > '$desdeSql'")
               + $ids("SELECT DISTINCT idProducto FROM producto_fotos WHERE created_at > '$desdeSql'");

    $categorias = [];
    $r = mysqli_query($bd, "SELECT ambito, id_ref FROM categoria_textos WHERE updated_at > '$desdeSql'");
    while ($r && ($f = mysqli_fetch_assoc($r))) {
        $categorias[$f['ambito'] . ':' . (int) $f['id_ref']] = true;
    }
    $sectores = ['/vinos' => 8, '/cavas' => 14, '/licores' => 2, '/cervezas' => 21, '/conservas' => 12];

    foreach ($sitemap as $loc => $lastmod) {
        $ruta = substr($loc, strlen($base)) ?: '/';
        if (preg_match('#^/producto/(\d+)/#', $ruta, $m)) {
            $cambiado = isset($productos[(int) $m[1]]);
        } elseif (preg_match('#^/(?:vinos|cavas|licores|conservas)/tipo/(\d+)/#', $ruta, $m)) {
            $cambiado = isset($categorias['tipo:' . $m[1]]);
        } elseif (preg_match('#^/vinos/do/(\d+)/#', $ruta, $m)) {
            $cambiado = isset($categorias['procedencia:' . $m[1]]);
        } elseif (preg_match('#^/cervezas/origen/(\d+)/#', $ruta, $m)) {
            $cambiado = isset($categorias['origen:' . $m[1]]);
        } elseif (isset($sectores[$ruta])) {
            $cambiado = isset($categorias['sector:' . $sectores[$ruta]]);
        } else {
            $cambiado = false;
        }
        if ($cambiado) {
            $enviar[$loc] = true;
        }
    }
    $enviar = array_keys($enviar);
}

decir(count($sitemap) . ' URL en el sitemap, ' . count($enviar) . ' que avisar');

// ---------------------------------------------------------------
// 3. Envío (como mucho 10.000 URL por petición).
// ---------------------------------------------------------------
if ($simular) {
    foreach (array_slice($enviar, 0, 50) as $loc) {
        echo "  $loc\n";
    }
    if (count($enviar) > 50) {
        echo '  … y ' . (count($enviar) - 50) . " más\n";
    }
    decir('simulación: no se envía nada ni se guarda el estado');
    exit(0);
}

foreach (array_chunk($enviar, 10000) as $lote) {
    $cuerpo = json_encode([
        'host'        => $host,
        'key'         => INDEXNOW_KEY,
        'keyLocation' => $base . '/' . INDEXNOW_KEY . '.txt',
        'urlList'     => $lote,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/json; charset=utf-8\r\n",
        'content'       => $cuerpo,
        'timeout'       => 60,
        'ignore_errors' => true,
    ]]);
    $respuesta = @file_get_contents('https://api.indexnow.org/indexnow', false, $ctx);
    $codigo = 0;
    foreach ($http_response_header ?? [] as $linea) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $linea, $m)) {
            $codigo = (int) $m[1];
        }
    }
    // 200 = recibido; 202 = recibido, la clave se validará después.
    if ($codigo !== 200 && $codigo !== 202) {
        salir("IndexNow respondió $codigo al enviar " . count($lote) . ' URL: ' . substr((string) $respuesta, 0, 300));
    }
    decir('enviadas ' . count($lote) . " URL (HTTP $codigo)");
}

// Solo se guarda el estado si todo ha ido bien: si falla, la próxima vez se reintenta.
@mkdir(dirname($estadoArchivo), 0775, true);
file_put_contents($estadoArchivo, json_encode(['ultima' => $ahora, 'urls' => $sitemap], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
