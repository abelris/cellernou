<?php
/**
 * El Celler Nou - Conservas Controller
 */

$tipoId = Router::param('id') ? intval(Router::param('id')) : null;
$filtroNombre = '';
$productos = [];

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Conservas', 'url' => Router::url('conservas')]
];

if ($tipoId) {
    $tipo = getTipoById($tipoId);
    if ($tipo) {
        $filtroNombre = $tipo['nombre_es'];
        $productos = getProductosByTipo($tipoId, 12);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => Router::url('conservas.tipo', ['id' => $tipoId, 'slug' => createSlug($filtroNombre)])];
        $pageTitle = $filtroNombre . ' | Conservas - El Celler Nou';
        $pageDescription = 'Las mejores ' . strtolower($filtroNombre) . ' en conserva. Productos gourmet de máxima calidad.';
    }
} else {
    $productos = getProductosBySector(12);
    $pageTitle = 'Conservas | El Celler Nou - Conservas gourmet de marisco';
    $pageDescription = 'Selección de conservas gourmet: mejillones, berberechos, navajas, calamares y más. Productos del mar de la mejor calidad.';
}

$tiposConservas = getTiposBySector(12);

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
    $schemaExtra .= schemaListaProductos($productosPagina, $filtroNombre ?: 'Conservas - El Celler Nou', $paginacion['offset']);
}

// FAQ: solo en el listado principal sin filtrar y en la página 1. En las
// páginas 2..N sería un FAQPage duplicado, y con filtro aplicado las
// preguntas no encajan con lo que el usuario está mirando.
$faqs = ($paginacion['actual'] === 1 && !$tipoId)
    ? getFaqsCategoria('conservas')
    : [];
if (!empty($faqs)) {
    $schemaExtra .= schemaFAQ($faqs);
}

include ROOT_PATH . '/includes/header.php';
?>

<section class="category-header category-header--conservas">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1><?php echo $filtroNombre ?: 'Conservas'; ?></h1>
        <p>
            <?php if ($filtroNombre): ?>
                <?php echo count($productos); ?> productos encontrados en <?php echo htmlspecialchars($filtroNombre); ?>
            <?php else: ?>
                Conservas gourmet de marisco de la mejor calidad
            <?php endif; ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filters-bar">
            <div class="filter-group">
                <label for="filter-tipo">Tipo:</label>
                <select id="filter-tipo" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo Router::url('conservas'); ?>">Todos los tipos</option>
                    <?php foreach ($tiposConservas as $t): ?>
                    <option value="<?php echo Router::url('conservas.tipo', ['id' => $t['id'], 'slug' => createSlug($t['nombre_es'])]); ?>"
                            <?php echo ($tipoId == $t['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['nombre_es']); ?>
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
                    <span class="product-card-category"><?php echo htmlspecialchars($producto['tipo_nombre'] ?? 'Conserva'); ?></span>
                    <h2 class="product-card-title">
                        <?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>
                    </h2>
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
            <a href="<?php echo Router::url('conservas'); ?>" class="btn btn-primary">Ver todas las conservas</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header"><h2>También te puede interesar</h2></div>
        <div class="categories-grid">
            <a href="<?php echo Router::url('vinos'); ?>" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content"><h3>Vinos</h3><p>El maridaje perfecto</p><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
            <a href="<?php echo Router::url('cervezas'); ?>" class="category-card" style="background: linear-gradient(135deg, #d4a017 0%, #9a7512 100%);">
                <div class="category-card-content"><h3>Cervezas</h3><p>Para acompañar</p><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes sobre las conservas'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
