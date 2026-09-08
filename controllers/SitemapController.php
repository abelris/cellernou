<?php
/**
 * El Celler Nou - Sitemap Controller
 *
 * Construye el sitemap en memoria antes de escribirlo, así que:
 *  - no se repiten URLs (un producto que aparecía en dos sectores salía dos veces),
 *  - se incluyen todas las páginas indexables, no solo las de vinos y licores,
 *  - las estáticas llevan lastmod.
 *
 * Fuera del sitemap queda todo lo que va con noindex: /buscar y los errores.
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

/** Añadir las páginas 2..N de un listado paginado. */
$addPaginas = function ($path, $totalProductos, $changefreq, $priority) use ($add) {
    $paginas = (int) ceil($totalProductos / PRODUCTOS_POR_PAGINA);
    for ($p = 2; $p <= $paginas; $p++) {
        $add($path . '?page=' . $p, $changefreq, $priority);
    }
};

// =============================================
// PÁGINAS ESTÁTICAS
// =============================================
$add('/', 'daily', '1.0', $hoy);
$add('/contacto', 'monthly', '0.6', $hoy);

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

// Vinos: por tipo y por D.O.
$vinos = getProductosBySector(8);
$add('/vinos', 'daily', '0.9', $hoy);
$addPaginas('/vinos', count($vinos), 'daily', '0.6');

foreach (getTiposBySector(8) as $tipo) {
    $path = '/vinos/tipo/' . $tipo['id'] . '/' . createSlug($tipo['nombre_es']);
    $add($path, 'weekly', '0.7');
    $addPaginas($path, count(getProductosByTipo($tipo['id'], 8)), 'weekly', '0.5');
}

foreach (getProcedencias(8) as $proc) {
    if (strpos($proc['nombre_es'], 'D.O.') === false) continue;
    $path = '/vinos/do/' . $proc['id'] . '/' . createSlug($proc['nombre_es']);
    $add($path, 'weekly', '0.7');
    $addPaginas($path, count(getProductosByProcedencia($proc['id'], 8)), 'weekly', '0.5');
}

// Cavas y espumosos (faltaban los filtros por tipo)
$cavas = getProductosBySector(14);
$add('/cavas', 'weekly', '0.8', $hoy);
$addPaginas('/cavas', count($cavas), 'weekly', '0.5');
foreach (getTiposBySector(14) as $tipo) {
    $path = '/cavas/tipo/' . $tipo['id'] . '/' . createSlug($tipo['nombre_es']);
    $add($path, 'weekly', '0.6');
    $addPaginas($path, count(getProductosByTipo($tipo['id'], 14)), 'weekly', '0.4');
}

// Licores
$licores = getProductosBySector(2);
$add('/licores', 'weekly', '0.8', $hoy);
$addPaginas('/licores', count($licores), 'weekly', '0.5');
foreach (getTiposBySector(2) as $tipo) {
    $path = '/licores/tipo/' . $tipo['id'] . '/' . createSlug($tipo['nombre_es']);
    $add($path, 'weekly', '0.6');
    $addPaginas($path, count(getProductosByTipo($tipo['id'], 2)), 'weekly', '0.4');
}

// Cervezas (faltaban los filtros por origen)
$cervezas = getProductosBySector(21);
$add('/cervezas', 'weekly', '0.7', $hoy);
$addPaginas('/cervezas', count($cervezas), 'weekly', '0.5');
foreach (getProcedencias(21) as $proc) {
    $path = '/cervezas/origen/' . $proc['id'] . '/' . createSlug($proc['nombre_es']);
    $add($path, 'weekly', '0.6');
    $addPaginas($path, count(getProductosByProcedencia($proc['id'], 21)), 'weekly', '0.4');
}

// Conservas (faltaban los filtros por tipo)
$conservas = getProductosBySector(12);
$add('/conservas', 'weekly', '0.7', $hoy);
$addPaginas('/conservas', count($conservas), 'weekly', '0.5');
foreach (getTiposBySector(12) as $tipo) {
    $path = '/conservas/tipo/' . $tipo['id'] . '/' . createSlug($tipo['nombre_es']);
    $add($path, 'weekly', '0.6');
    $addPaginas($path, count(getProductosByTipo($tipo['id'], 12)), 'weekly', '0.4');
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
// Un mismo producto podía estar en dos sectores permitidos y salía repetido;
// al indexar por URL el duplicado desaparece solo.
foreach (SECTORES_PERMITIDOS as $sectorId) {
    foreach (getProductosBySector($sectorId) as $producto) {
        $lastmod = (!empty($producto['alta']) && strpos($producto['alta'], '0000') !== 0)
            ? date('Y-m-d', strtotime($producto['alta']))
            : null;
        $add('/producto/' . $producto['id'] . '/' . createSlug($producto['nombre']), 'weekly', '0.5', $lastmod);
    }
}

// Productos del catálogo Komkal sin vincular: tienen ficha pública propia.
foreach (['vinos', 'licores'] as $komkalTipo) {
    foreach (getKomkalProductosNoVinculats($komkalTipo) as $producto) {
        $add('/producto/k/' . $komkalTipo . '/' . $producto['id'] . '/' . createSlug($producto['nombre']), 'weekly', '0.4');
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
