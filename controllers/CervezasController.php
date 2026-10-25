<?php
/**
 * El Celler Nou - Cervezas Controller
 */

$origenId = Router::param('id') ? intval(Router::param('id')) : null;
$filtroNombre = '';
$productos = [];

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Cervezas', 'url' => Router::url('cervezas')]
];

if ($origenId) {
    $procedencia = getProcedenciaById($origenId);
    if ($procedencia) {
        $filtroNombre = $procedencia['nombre_es'];
        $productos = getProductosByProcedencia($origenId, 21);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => Router::url('cervezas.origen', ['id' => $origenId, 'slug' => createSlug($filtroNombre)])];
        $pageTitle = $filtroNombre . ' | Cervezas - El Celler Nou';
        $pageDescription = 'Selección de cervezas de ' . $filtroNombre . '. Las mejores cervezas en El Celler Nou.';
        if (!filtroIndexable('idProcedencia', $origenId, 21)) $pageRobots = 'noindex, follow';
    }
} else {
    $productos = getProductosBySector(21);
    $pageTitle = 'Cervezas artesanales y de importación en Premià de Mar | El Celler Nou';
    $pageDescription = 'Descubre nuestra selección de cervezas artesanales, de importación y nacionales. Las mejores cervezas del mundo en El Celler Nou.';
}

$procedenciasCervezas = getProcedencias(21);

// Paginación. Antes se volcaba el sector entero en una sola página
// (/vinos llegaba a 1.479 productos y 2,4 MB de HTML). La URL base sale del
// último breadcrumb, que siempre es la página actual sin query string.
$rutaActual  = $breadcrumbItems[count($breadcrumbItems) - 1]['url'];
$paginacion  = paginar($productos, $rutaActual);
$productosPagina = $paginacion['items'];

// El canonical apunta a la página real (?page=N), no a REQUEST_URI.
$currentUrl = Router::abs($paginacion['canonical']);
$pagePrev   = $paginacion['prev'];
$pageNext   = $paginacion['next'];

// Las páginas 2+ llevan su número en el title para no duplicar el de la 1.
if ($paginacion['actual'] > 1) {
    $pageTitle .= ' - Página ' . $paginacion['actual'];
}

$schemaExtra = schemaBreadcrumb($breadcrumbItems);
if (!empty($productosPagina)) {
    $schemaExtra .= schemaListaProductos($productosPagina, 'Cervezas - El Celler Nou', $paginacion['offset']);
}

// FAQ: solo en el listado principal sin filtrar y en la página 1. En las
// páginas 2..N sería un FAQPage duplicado, y con filtro aplicado las
// preguntas no encajan con lo que el usuario está mirando.
$faqs = ($paginacion['actual'] === 1 && !$origenId)
    ? getFaqsCategoria('cervezas')
    : [];
// Texto propio del listado (tabla categoria_textos): intro, texto largo y
// FAQ, que van delante de las generales de la categoría.
$ambitoTexto = $origenId ? 'procedencia' : 'sector';
$idTexto = $origenId ?: 21;
$textoCat = aplicarTextoCategoria($ambitoTexto, $idTexto, $paginacion, $pageTitle, $pageDescription, $faqs);
if (!empty($faqs)) {
    $schemaExtra .= schemaFAQ($faqs);
}

include ROOT_PATH . '/includes/header.php';
?>

<section class="category-header category-header--cervezas">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1><?php echo $filtroNombre ?: 'Cervezas'; ?></h1>
        <?php if ($textoCat && !empty($textoCat['intro'])): ?>
        <?php echo renderIntroCategoria($textoCat); ?>
        <?php else: ?>
        <p>
            <?php if ($filtroNombre): ?>
                <?php echo count($productos); ?> productos encontrados en <?php echo htmlspecialchars($filtroNombre); ?>
            <?php else: ?>
                Selección de cervezas artesanales, de importación y nacionales
            <?php endif; ?>
        </p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filters-bar">
            <div class="filter-group">
                <label for="filter-origen">Origen:</label>
                <select id="filter-origen" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo Router::url('cervezas'); ?>">Todos los orígenes</option>
                    <?php foreach ($procedenciasCervezas as $p): ?>
                    <option value="<?php echo Router::url('cervezas.origen', ['id' => $p['id'], 'slug' => createSlug($p['nombre_es'])]); ?>"
                            <?php echo ($origenId == $p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nombre_es']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <span class="results-count"><?php echo count($productos); ?> productos</span>
        </div>

        <?php if (!empty($productos)): ?>
        <div class="products-grid">
            <?php foreach ($productosPagina as $iProd => $producto): ?>
            <?php $productoUrl = Router::url('producto', ['id' => $producto['id'], 'slug' => createSlug($producto['nombre'])]); ?>
            <a href="<?php echo $productoUrl; ?>" class="product-card">
                <div class="product-card-image">
                    <?php $imgCarga = $iProd < 8 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
                    <?php if (productoTieneFoto($producto)): ?>
                    <img src="<?php echo getBaseUrl() . rutaFoto($producto); ?>"
                         alt="<?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>"
                         width="300" height="300" decoding="async" <?php echo $imgCarga; ?>>
                    <?php else: ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/no-image.svg"
                         alt="<?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>"
                         width="300" height="300" decoding="async" <?php echo $imgCarga; ?>>
                    <?php endif; ?>
                </div>
                <div class="product-card-content">
                    <span class="product-card-category">Cerveza</span>
                    <h2 class="product-card-title">
                        <?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>
                    </h2>
                    <?php if (!empty($producto['procedencia_nombre'])): ?>
                    <p class="product-card-origin"><?php echo htmlspecialchars($producto['procedencia_nombre']); ?></p>
                    <?php endif; ?>
                    <div class="product-card-footer">
                        <?php if ($producto['precio_venta'] > 0 && !empty($producto['tiene_stock'])): ?>
                        <span class="product-card-price"><?php echo formatPrecioWeb($producto['precio_venta']); ?></span>
                        <?php else: ?>
                        <span class="product-card-price">Consultar</span>
                        <?php endif; ?>
                        <span class="product-card-link">
                            Ver más <svg class="icon"><use href="#icon-arrow-right"></use></svg>
                        </span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php echo renderPaginacion($paginacion); ?>
        <?php else: ?>
        <div class="no-results">
            <h2>No se encontraron productos</h2>
            <a href="<?php echo Router::url('home'); ?>" class="btn btn-primary">Volver al inicio</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($paginacion['actual'] === 1) echo renderTextoCategoria($textoCat, $ambitoTexto, $idTexto, $filtroNombre ?: 'Cervezas'); ?>

<section class="section section-alt">
    <div class="container">
        <div class="section-header"><h2>También te puede interesar</h2></div>
        <div class="categories-grid">
            <a href="<?php echo Router::url('vinos'); ?>" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content"><h3>Vinos</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
            <a href="<?php echo Router::url('licores'); ?>" class="category-card" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);">
                <div class="category-card-content"><h3>Licores</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
            <a href="<?php echo Router::url('conservas'); ?>" class="category-card" style="background: linear-gradient(135deg, #4a7c59 0%, #2d4d37 100%);">
                <div class="category-card-content"><h3>Conservas</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, $filtroNombre ? 'Preguntas frecuentes: ' . $filtroNombre : 'Preguntas frecuentes sobre las cervezas'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
