<?php
/**
 * El Celler Nou - Sitemap Controller
 *
 * Construye el sitemap en memoria antes de escribirlo, así que:
 *  - no se repiten URLs (un producto que aparecía en dos sectores salía dos veces),
 *  - se incluyen todas las páginas indexables, no solo las de vinos y licores,
 *  - las estáticas llevan lastmod.
 *
 * Fuera del sitemap queda todo lo que va con noindex: /buscar, los errores,
 * las fichas y filtros no indexables (ver INDEXACIÓN en functions.php) y las
 * fichas Komkal.
 */

header('Content-Type: application/xml; charset=utf-8');

// Caché en disco: construirlo recorre todo el catálogo y tarda ~30 s. Se
// regenera como mucho una vez cada 6 horas; el resto de peticiones se sirven
// del fichero. Borrar uploads/cache/sitemap.xml fuerza la regeneración.
define('SITEMAP_CACHE_TTL', 6 * 3600);
$cacheDir  = ROOT_PATH . '/uploads/cache';
$cacheFile = $cacheDir . '/sitemap.xml';

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < SITEMAP_CACHE_TTL) {
    header('X-Sitemap-Cache: hit');
    readfile($cacheFile);
    exit;
}

$base = rtrim(SITE_URL, '/');
$hoy  = date('Y-m-d');

/** @var array<string, array{changefreq:string, priority:string, lastmod:?string}> */
$urls = [];

/**
 * Añadir una URL al sitemap. La clave es la propia URL, así que una segunda
 * llamada con la misma loc no genera un duplicado: se queda la prioridad
 * más alta de las dos.
 */
$add = function ($path, $changefreq, $priority, $lastmod = null) use (&$urls, $base) {
    $loc = $base . $path;
    if (isset($urls[$loc]) && $urls[$loc]['priority'] >= $priority) {
        if ($lastmod && empty($urls[$loc]['lastmod'])) {
            $urls[$loc]['lastmod'] = $lastmod;
        }
        return;
    }
    $urls[$loc] = [
        'changefreq' => $changefreq,
        'priority'   => $priority,
        'lastmod'    => $lastmod ?: ($urls[$loc]['lastmod'] ?? null)
    ];
};

// =============================================
// PÁGINAS ESTÁTICAS
// =============================================
$add('/', 'daily', '1.0', $hoy);
$add('/contacto', 'monthly', '0.6', $hoy);
$add('/servicios', 'monthly', '0.8', $hoy);

// Vino a domicilio: la página general y una por población.
$add('/vino-a-domicilio', 'monthly', '0.9', $hoy);
foreach (array_keys(getPoblacionesReparto()) as $slugPob) {
    $add('/vino-a-domicilio/' . $slugPob, 'monthly', '0.8', $hoy);
}

// Legales: faltaban por completo en el sitemap anterior.
$add('/aviso-legal', 'yearly', '0.3', $hoy);
$add('/politica-privacidad', 'yearly', '0.3', $hoy);
$add('/politica-cookies', 'yearly', '0.3', $hoy);
$add('/politica-devoluciones', 'yearly', '0.4', $hoy);
$add('/condiciones-de-venta', 'yearly', '0.4', $hoy);
$add('/politica-de-envios', 'yearly', '0.4', $hoy);

// =============================================
// CATEGORÍAS Y SUS FILTROS
// =============================================

// Solo los filtros indexables (filtroIndexable): con stock suficiente y en su
// sector. Las paginaciones ?page=N no van: Google llega a ellas por los
// enlaces de la propia página y no aportan nada al sitemap.

// Vinos: por tipo y por D.O.
$add('/vinos', 'daily', '0.9', $hoy);

foreach (getTiposBySector(8) as $tipo) {
    if (!filtroIndexable('idTipo', $tipo['id'], 8)) continue;
    $add('/vinos/tipo/' . $tipo['id'] . '/' . createSlug($tipo['nombre_es']), 'weekly', '0.7');
}

foreach (getProcedencias(8) as $proc) {
    // D.O. Cava redirige a /cavas.
    if (strpos($proc['nombre_es'], 'D.O.') === false || (int) $proc['id'] === PROCEDENCIA_DO_CAVA) continue;
    if (!filtroIndexable('idProcedencia', $proc['id'], 8)) continue;
    $add('/vinos/do/' . $proc['id'] . '/' . createSlug($proc['nombre_es']), 'weekly', '0.7');
}

// Cavas y espumosos
$add('/cavas', 'weekly', '0.8', $hoy);
foreach (getTiposBySector(14) as $tipo) {
    if (!filtroIndexable('idTipo', $tipo['id'], 14)) continue;
    $add('/cavas/tipo/' . $tipo['id'] . '/' . createSlug($tipo['nombre_es']), 'weekly', '0.6');
}

// Licores
$add('/licores', 'weekly', '0.8', $hoy);
foreach (getTiposBySector(2) as $tipo) {
    if (!filtroIndexable('idTipo', $tipo['id'], 2)) continue;
    $add('/licores/tipo/' . $tipo['id'] . '/' . createSlug($tipo['nombre_es']), 'weekly', '0.6');
}

// Cervezas
$add('/cervezas', 'weekly', '0.7', $hoy);
foreach (getProcedencias(21) as $proc) {
    if (!filtroIndexable('idProcedencia', $proc['id'], 21)) continue;
    $add('/cervezas/origen/' . $proc['id'] . '/' . createSlug($proc['nombre_es']), 'weekly', '0.6');
}

// Conservas
$add('/conservas', 'weekly', '0.7', $hoy);
foreach (getTiposBySector(12) as $tipo) {
    if (!filtroIndexable('idTipo', $tipo['id'], 12)) continue;
    $add('/conservas/tipo/' . $tipo['id'] . '/' . createSlug($tipo['nombre_es']), 'weekly', '0.6');
}

// =============================================
// WINEWORLD
// =============================================
$add('/wineworld', 'weekly', '0.8', $hoy);

foreach (getWWCategorias() as $wwCat) {
    $add('/wineworld/' . $wwCat['slug'], 'weekly', '0.7');

    foreach (getWWArticulosByCategoria($wwCat['id']) as $wwArt) {
        $lastmod = null;
        if (!empty($wwArt['updated_at']) && strpos($wwArt['updated_at'], '0000') !== 0) {
            $lastmod = date('Y-m-d', strtotime($wwArt['updated_at']));
        } elseif (!empty($wwArt['fecha_publicacion'])) {
            $lastmod = date('Y-m-d', strtotime($wwArt['fecha_publicacion']));
        }
        $add('/wineworld/' . $wwCat['slug'] . '/' . $wwArt['slug'], 'monthly', '0.8', $lastmod);
    }
}

// =============================================
// FICHAS DE PRODUCTO
// =============================================
// Solo las indexables (productoIndexable). Un mismo producto podía estar en dos
// sectores permitidos y salía repetido; al indexar por URL el duplicado
// desaparece solo. Las fichas del catálogo Komkal sin vincular no van: son de
// productos que no tenemos, con precio de la última importación, y llevan noindex.
foreach (SECTORES_PERMITIDOS as $sectorId) {
    foreach (getProductosIndexablesPorSector($sectorId) as $producto) {
        $lastmod = (!empty($producto['alta']) && strpos($producto['alta'], '0000') !== 0)
            ? date('Y-m-d', strtotime($producto['alta']))
            : null;
        $add('/producto/' . $producto['id'] . '/' . createSlug($producto['nombre']), 'weekly', '0.5', $lastmod);
    }
}

// =============================================
// SALIDA
// =============================================
$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($urls as $loc => $meta) {
    $xml .= "    <url>\n";
    $xml .= '        <loc>' . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
    if (!empty($meta['lastmod'])) {
        $xml .= '        <lastmod>' . $meta['lastmod'] . "</lastmod>\n";
    }
    $xml .= '        <changefreq>' . $meta['changefreq'] . "</changefreq>\n";
    $xml .= '        <priority>' . $meta['priority'] . "</priority>\n";
    $xml .= "    </url>\n";
}

$xml .= '</urlset>';

// Guardar en caché. Si el directorio no es escribible se sirve igualmente.
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0775, true);
}
@file_put_contents($cacheFile, $xml, LOCK_EX);

header('X-Sitemap-Cache: miss');
echo $xml;
exit;
