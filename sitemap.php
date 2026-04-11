<?php
/**
 * El Celler Nou - Sitemap XML Generator
 * Access via /sitemap.xml
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Static Pages -->
    <url>
        <loc><?php echo SITE_URL; ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/vinos</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/cavas</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/licores</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/cervezas</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/conservas</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/contacto</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

    <!-- Wine Types -->
    <?php
    $tiposVinos = getTiposBySector(8);
    foreach ($tiposVinos as $tipo):
    ?>
    <url>
        <loc><?php echo SITE_URL; ?>/vinos/tipo/<?php echo $tipo['id']; ?>/<?php echo createSlug($tipo['nombre_es']); ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>

    <!-- D.O. Pages -->
    <?php
    $procedencias = getProcedencias(8);
    foreach ($procedencias as $proc):
        if (strpos($proc['nombre_es'], 'D.O.') !== false):
    ?>
    <url>
        <loc><?php echo SITE_URL; ?>/vinos/do/<?php echo $proc['id']; ?>/<?php echo createSlug($proc['nombre_es']); ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php
        endif;
    endforeach;
    ?>

    <!-- Licor Types -->
    <?php
    $tiposLicores = getTiposBySector(2);
    foreach ($tiposLicores as $tipo):
    ?>
    <url>
        <loc><?php echo SITE_URL; ?>/licores/tipo/<?php echo $tipo['id']; ?>/<?php echo createSlug($tipo['nombre_es']); ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>

    <!-- Products -->
    <?php
    // Get all products from allowed sectors
    $sectores = SECTORES_PERMITIDOS;
    foreach ($sectores as $sectorId):
        $productos = getProductosBySector($sectorId);
        foreach ($productos as $producto):
    ?>
    <url>
        <loc><?php echo SITE_URL; ?>/producto/<?php echo $producto['id']; ?>/<?php echo createSlug($producto['nombre']); ?></loc>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
        <?php if (!empty($producto['alta'])): ?>
        <lastmod><?php echo date('Y-m-d', strtotime($producto['alta'])); ?></lastmod>
        <?php endif; ?>
    </url>
    <?php
        endforeach;
    endforeach;
    ?>
</urlset>
