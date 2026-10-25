<?php
/**
 * El Celler Nou - WineWorld Controller
 * Sección de artículos organizados por categorías
 */

// Determinar qué vista mostrar según la ruta
$catSlug = Router::param('cat_slug') ?: null;
$artSlug = Router::param('art_slug') ?: null;

// ============================================================
// VISTA 3: Artículo individual
// ============================================================
if ($catSlug && $artSlug) {
    $categoria = getWWCategoria($catSlug);
    $articulo = getWWArticulo($artSlug);

    if (!$categoria || !$articulo || $articulo['id_categoria'] != $categoria['id'] || !$articulo['publicado']
        || (!empty($articulo['fecha_publicacion']) && strtotime($articulo['fecha_publicacion']) > time())) {
        http_response_code(404);
        $pageTitle = 'Artículo no encontrado | ' . SITE_NAME;
        $pageRobots = 'noindex, follow';
        include ROOT_PATH . '/includes/header.php';
        echo '<section class="section"><div class="container"><div class="no-results"><h1>Artículo no encontrado</h1><p>El artículo que buscas no existe o ha sido retirado.</p><a href="' . Router::url('wineworld') . '" class="btn btn-primary">Volver a WineWorld</a></div></div></section>';
        include ROOT_PATH . '/includes/footer.php';
        return;
    }

    $pageTitle = htmlspecialchars($articulo['titulo']) . ' | WineWorld - ' . SITE_NAME;
    $pageDescription = !empty($articulo['extracto']) ? mb_substr(strip_tags($articulo['extracto']), 0, 160) : 'Lee este artículo en WineWorld de ' . SITE_NAME;
    $pageType = 'article';
    if (imagenWineWorldExiste($articulo['imagen'] ?? null)) {
        $pageImage = getBaseUrl() . '/images/wineworld/' . $articulo['imagen'];
    }

    $breadcrumbItems = [
        ['name' => 'Inicio', 'url' => Router::url('home')],
        ['name' => 'WineWorld', 'url' => Router::url('wineworld')],
        ['name' => $categoria['nombre'], 'url' => Router::url('wineworld.categoria', ['cat_slug' => $categoria['slug']])],
        ['name' => $articulo['titulo'], 'url' => Router::url('wineworld.articulo', ['cat_slug' => $categoria['slug'], 'art_slug' => $articulo['slug']])]
    ];

    $currentUrl = Router::urlAbs('wineworld.articulo', [
        'cat_slug' => $categoria['slug'],
        'art_slug' => $articulo['slug']
    ]);

    $schemaExtra = schemaBreadcrumb($breadcrumbItems);
    $schemaExtra .= schemaArticulo($articulo, $categoria);

    // Plantilla de artículo: consejo de la tienda, FAQ (visibles y en schema)
    // y «Dónde comprarlo» con la categoría que trata (ww_articulo_relaciones).
    $faqsArticulo = decodificarFaqs($articulo['faqs'] ?? null);
    if (!empty($faqsArticulo)) {
        $schemaExtra .= schemaFAQ($faqsArticulo);
    }
    $relacionesArticulo = getRelacionesArticulo($articulo['id']);

    // Artículos relacionados de la misma categoría
    $relacionados = getWWArticulosRelacionados($articulo['id'], $articulo['id_categoria'], 3);

    include ROOT_PATH . '/includes/header.php';
    ?>

    <!-- Article Header -->
    <section class="ww-article-header" style="--cat-color: <?php echo htmlspecialchars($categoria['color'] ?: '#722F37'); ?>">
        <div class="container">
            <?php echo generarBreadcrumb($breadcrumbItems); ?>
            <div class="ww-article-meta-top">
                <a href="<?php echo Router::url('wineworld.categoria', ['cat_slug' => $categoria['slug']]); ?>" class="ww-article-category-badge"><?php echo htmlspecialchars($categoria['nombre']); ?></a>
                <?php if ($articulo['fecha_publicacion']): ?>
                <time datetime="<?php echo $articulo['fecha_publicacion']; ?>" class="ww-article-date">
                    <?php echo date('d/m/Y', strtotime($articulo['fecha_publicacion'])); ?>
                </time>
                <?php endif; ?>
            </div>
            <h1><?php echo htmlspecialchars($articulo['titulo']); ?></h1>
            <?php if (!empty($articulo['extracto'])): ?>
            <p class="ww-article-lead"><?php echo htmlspecialchars($articulo['extracto']); ?></p>
            <?php endif; ?>
            <?php if (!empty($articulo['autor'])): ?>
            <div class="ww-article-author">
                <span>Por <?php echo htmlspecialchars($articulo['autor']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (imagenWineWorldExiste($articulo['imagen'] ?? null)): ?>
    <div class="ww-article-featured-image">
        <div class="container">
            <img src="<?php echo getBaseUrl(); ?>/images/wineworld/<?php echo htmlspecialchars($articulo['imagen']); ?>"
                 alt="<?php echo htmlspecialchars($articulo['titulo']); ?>"
                 width="1200" height="800" decoding="async"
                 fetchpriority="high">
        </div>
    </div>
    <?php endif; ?>

    <!-- Article Content -->
    <section class="section">
        <div class="container">
            <div class="ww-article-content">
                <?php echo completarImagenesArticulo($articulo['contenido']); ?>
            </div>
            <?php echo renderTeLoAconsejamos('Hola, he leído «' . $articulo['titulo'] . '» y me gustaría que me aconsejarais.', $articulo['consejo'] ?? null); ?>
            <?php echo renderAutorArticulo($articulo); ?>
        </div>
    </section>

    <?php echo renderDondeComprarlo($relacionesArticulo); ?>

    <?php echo renderFaqs($faqsArticulo, 'Preguntas frecuentes'); ?>

    <!-- Related Articles -->
    <?php if (!empty($relacionados)): ?>
    <section class="section section-alt">
        <div class="container">
            <div class="section-header">
                <h2>Artículos relacionados</h2>
            </div>
            <div class="ww-articles-grid">
                <?php foreach ($relacionados as $rel): ?>
                <?php $relCategoria = getWWCategoriaById($rel['id_categoria']); ?>
                <a href="<?php echo Router::url('wineworld.articulo', ['cat_slug' => $relCategoria['slug'], 'art_slug' => $rel['slug']]); ?>" class="ww-article-card">
                    <div class="ww-article-card-image">
                        <?php if (imagenWineWorldExiste($rel['imagen'] ?? null)): ?>
                        <img src="<?php echo getBaseUrl(); ?>/images/wineworld/<?php echo htmlspecialchars($rel['imagen']); ?>"
                             alt="<?php echo htmlspecialchars($rel['titulo']); ?>"
                             width="1200" height="800" decoding="async" loading="lazy">
                        <?php else: ?>
                        <div class="ww-article-card-placeholder" style="background: <?php echo htmlspecialchars($relCategoria['color'] ?: '#722F37'); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="ww-article-card-content">
                        <span class="ww-article-card-category"><?php echo htmlspecialchars($relCategoria['nombre']); ?></span>
                        <h3><?php echo htmlspecialchars($rel['titulo']); ?></h3>
                        <?php if (!empty($rel['extracto'])): ?>
                        <p><?php echo htmlspecialchars(mb_substr($rel['extracto'], 0, 120)); ?>...</p>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php
    include ROOT_PATH . '/includes/footer.php';
    return;
}

// ============================================================
// VISTA 2: Categoría con sus artículos
// ============================================================
if ($catSlug) {
    $categoria = getWWCategoria($catSlug);

    if (!$categoria || !$categoria['activo']) {
        http_response_code(404);
        $pageTitle = 'Categoría no encontrada | ' . SITE_NAME;
        $pageRobots = 'noindex, follow';
        include ROOT_PATH . '/includes/header.php';
        echo '<section class="section"><div class="container"><div class="no-results"><h1>Categoría no encontrada</h1><p>La categoría que buscas no existe.</p><a href="' . Router::url('wineworld') . '" class="btn btn-primary">Volver a WineWorld</a></div></div></section>';
        include ROOT_PATH . '/includes/footer.php';
        return;
    }

    $articulos = getWWArticulosByCategoria($categoria['id']);

    $pageTitle = htmlspecialchars($categoria['nombre']) . ' | WineWorld - ' . SITE_NAME;
    $pageDescription = !empty($categoria['descripcion']) ? mb_substr(strip_tags($categoria['descripcion']), 0, 160) : 'Artículos sobre ' . $categoria['nombre'] . ' en WineWorld de ' . SITE_NAME;

    $breadcrumbItems = [
        ['name' => 'Inicio', 'url' => Router::url('home')],
        ['name' => 'WineWorld', 'url' => Router::url('wineworld')],
        ['name' => $categoria['nombre'], 'url' => Router::url('wineworld.categoria', ['cat_slug' => $categoria['slug']])]
    ];

    $currentUrl = Router::urlAbs('wineworld.categoria', ['cat_slug' => $categoria['slug']]);

    $schemaExtra = schemaBreadcrumb($breadcrumbItems);

    include ROOT_PATH . '/includes/header.php';
    ?>

    <!-- Category Header -->
    <section class="ww-category-header" style="--cat-color: <?php echo htmlspecialchars($categoria['color'] ?: '#722F37'); ?>">
        <div class="container">
            <?php echo generarBreadcrumb($breadcrumbItems); ?>
            <h1><?php echo htmlspecialchars($categoria['nombre']); ?></h1>
            <?php if (!empty($categoria['descripcion'])): ?>
            <p><?php echo htmlspecialchars($categoria['descripcion']); ?></p>
            <?php endif; ?>
            <span class="ww-count"><?php echo count($articulos); ?> artículo<?php echo count($articulos) != 1 ? 's' : ''; ?></span>
        </div>
    </section>

    <!-- Articles Grid -->
    <section class="section">
        <div class="container">
            <?php if (!empty($articulos)): ?>
            <div class="ww-articles-grid">
                <?php foreach ($articulos as $art): ?>
                <a href="<?php echo Router::url('wineworld.articulo', ['cat_slug' => $categoria['slug'], 'art_slug' => $art['slug']]); ?>" class="ww-article-card">
                    <div class="ww-article-card-image">
                        <?php if (imagenWineWorldExiste($art['imagen'] ?? null)): ?>
                        <img src="<?php echo getBaseUrl(); ?>/images/wineworld/<?php echo htmlspecialchars($art['imagen']); ?>"
                             alt="<?php echo htmlspecialchars($art['titulo']); ?>"
                             width="1200" height="800" decoding="async" loading="lazy">
                        <?php else: ?>
                        <div class="ww-article-card-placeholder" style="background: <?php echo htmlspecialchars($categoria['color'] ?: '#722F37'); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="ww-article-card-content">
                        <span class="ww-article-card-category"><?php echo htmlspecialchars($categoria['nombre']); ?></span>
                        <h3><?php echo htmlspecialchars($art['titulo']); ?></h3>
                        <?php if (!empty($art['extracto'])): ?>
                        <p><?php echo htmlspecialchars(mb_substr($art['extracto'], 0, 150)); ?>...</p>
                        <?php endif; ?>
                        <div class="ww-article-card-footer">
                            <?php if ($art['fecha_publicacion']): ?>
                            <time datetime="<?php echo $art['fecha_publicacion']; ?>"><?php echo date('d/m/Y', strtotime($art['fecha_publicacion'])); ?></time>
                            <?php endif; ?>
                            <span class="ww-read-more">Leer más <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <h2>Próximamente</h2>
                <p>Estamos preparando artículos para esta categoría. ¡Vuelve pronto!</p>
                <a href="<?php echo Router::url('wineworld'); ?>" class="btn btn-primary">Volver a WineWorld</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Other Categories -->
    <?php
    $otrasCategorias = getWWCategorias();
    $otrasCategorias = array_filter($otrasCategorias, function($c) use ($categoria) { return $c['id'] != $categoria['id']; });
    if (!empty($otrasCategorias)):
    ?>
    <section class="section section-alt">
        <div class="container">
            <div class="section-header">
                <h2>Más categorías</h2>
            </div>
            <div class="ww-categories-grid">
                <?php foreach ($otrasCategorias as $cat): ?>
                <a href="<?php echo Router::url('wineworld.categoria', ['cat_slug' => $cat['slug']]); ?>" class="ww-category-card" style="--cat-color: <?php echo htmlspecialchars($cat['color'] ?: '#722F37'); ?>">
                    <div class="ww-category-card-bg">
                        <?php if (imagenWineWorldExiste($cat['imagen'] ?? null)): ?>
                        <img src="<?php echo getBaseUrl(); ?>/images/wineworld/<?php echo htmlspecialchars($cat['imagen']); ?>" alt=""
                         width="1200" height="800" decoding="async" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="ww-category-card-content">
                        <h3><?php echo htmlspecialchars($cat['nombre']); ?></h3>
                        <?php if (!empty($cat['descripcion'])): ?>
                        <p><?php echo htmlspecialchars(mb_substr($cat['descripcion'], 0, 100)); ?></p>
                        <?php endif; ?>
                        <span class="ww-category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php
    include ROOT_PATH . '/includes/footer.php';
    return;
}

// ============================================================
// VISTA 1: Landing WineWorld - Todas las categorías
// ============================================================

$categorias = getWWCategorias();

$pageTitle = 'WineWorld | ' . SITE_NAME . ' - Artículos sobre vinos, destilados y más';
$pageDescription = 'Explora WineWorld: guías de vino, artículos sobre destilados, maridajes y cultura enológica. Todo el conocimiento del mundo del vino en El Celler Nou.';

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'WineWorld', 'url' => Router::url('wineworld')]
];

$schemaExtra = schemaBreadcrumb($breadcrumbItems);

include ROOT_PATH . '/includes/header.php';
?>

<!-- WineWorld Hero -->
<section class="ww-hero">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <div class="ww-hero-content">
            <h1>WineWorld</h1>
            <p>Explora el fascinante mundo del vino y las bebidas premium. Guías, maridajes, historia y todo lo que necesitas saber.</p>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="section">
    <div class="container">
        <?php if (!empty($categorias)): ?>
        <div class="ww-categories-grid ww-categories-landing">
            <?php foreach ($categorias as $cat):
                $numArticulos = getWWCountArticulos($cat['id']);
            ?>
            <a href="<?php echo Router::url('wineworld.categoria', ['cat_slug' => $cat['slug']]); ?>" class="ww-category-card ww-category-card--large" style="--cat-color: <?php echo htmlspecialchars($cat['color'] ?: '#722F37'); ?>">
                <div class="ww-category-card-bg">
                    <?php if (imagenWineWorldExiste($cat['imagen'] ?? null)): ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/wineworld/<?php echo htmlspecialchars($cat['imagen']); ?>" alt=""
                         width="1200" height="800" decoding="async" loading="lazy">
                    <?php endif; ?>
                </div>
                <div class="ww-category-card-content">
                    <h2><?php echo htmlspecialchars($cat['nombre']); ?></h2>
                    <?php if (!empty($cat['descripcion'])): ?>
                    <p><?php echo htmlspecialchars($cat['descripcion']); ?></p>
                    <?php endif; ?>
                    <div class="ww-category-card-meta">
                        <span><?php echo $numArticulos; ?> artículo<?php echo $numArticulos != 1 ? 's' : ''; ?></span>
                        <span class="ww-category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-results">
            <h2>Próximamente</h2>
            <p>Estamos preparando contenido para WineWorld. ¡Vuelve pronto!</p>
            <a href="<?php echo Router::url('home'); ?>" class="btn btn-primary">Volver al inicio</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Latest Articles across all categories -->
<?php
$ultimosArticulos = getWWUltimosArticulos(6);
if (!empty($ultimosArticulos)):
?>
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Últimos artículos</h2>
        </div>
        <div class="ww-articles-grid">
            <?php foreach ($ultimosArticulos as $art):
                $artCat = getWWCategoriaById($art['id_categoria']);
            ?>
            <a href="<?php echo Router::url('wineworld.articulo', ['cat_slug' => $artCat['slug'], 'art_slug' => $art['slug']]); ?>" class="ww-article-card">
                <div class="ww-article-card-image">
                    <?php if (imagenWineWorldExiste($art['imagen'] ?? null)): ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/wineworld/<?php echo htmlspecialchars($art['imagen']); ?>"
                         alt="<?php echo htmlspecialchars($art['titulo']); ?>"
                         width="1200" height="800" decoding="async" loading="lazy">
                    <?php else: ?>
                    <div class="ww-article-card-placeholder" style="background: <?php echo htmlspecialchars($artCat['color'] ?: '#722F37'); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="ww-article-card-content">
                    <span class="ww-article-card-category"><?php echo htmlspecialchars($artCat['nombre']); ?></span>
                    <h3><?php echo htmlspecialchars($art['titulo']); ?></h3>
                    <?php if (!empty($art['extracto'])): ?>
                    <p><?php echo htmlspecialchars(mb_substr($art['extracto'], 0, 150)); ?>...</p>
                    <?php endif; ?>
                    <div class="ww-article-card-footer">
                        <?php if ($art['fecha_publicacion']): ?>
                        <time datetime="<?php echo $art['fecha_publicacion']; ?>"><?php echo date('d/m/Y', strtotime($art['fecha_publicacion'])); ?></time>
                        <?php endif; ?>
                        <span class="ww-read-more">Leer más <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
