<?php
/**
 * El Celler Nou - Licores Controller
 */

$tipoId = Router::param('id') ? intval(Router::param('id')) : null;
$filtroNombre = '';
$productos = [];

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Licores', 'url' => Router::url('licores')]
];

if ($tipoId) {
    $tipo = getTipoById($tipoId);
    if ($tipo) {
        $filtroNombre = $tipo['nombre_es'];
        $productos = getProductosByTipo($tipoId, 2);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => Router::url('licores.tipo', ['id' => $tipoId, 'slug' => createSlug($filtroNombre)])];
        $pageTitle = $filtroNombre . ' | Licores - El Celler Nou';
        $pageDescription = 'Selección de ' . strtolower($filtroNombre) . ' de las mejores marcas. Licores premium para los paladares más exigentes.';
        if (!filtroIndexable('idTipo', $tipoId, 2)) $pageRobots = 'noindex, follow';
    }
} else {
    $productos = getProductosBySector(2);
    $pageTitle = 'Licores y destilados en Premià de Mar | El Celler Nou';
    $pageDescription = 'Whisky, ron, ginebra, vodka, tequila, brandy y vermut en nuestra tienda de Premià de Mar, con entrega a domicilio en el Maresme.';
}

// Append unmatched Komkal liquors (only on unfiltered main listing)
if (!$tipoId) {
    $komkalLicores = getKomkalProductosNoVinculats('licores');
    $productos = array_merge($productos, $komkalLicores);
}

$tiposLicores = getTiposBySector(2);
$procedenciasLicores = getProcedencias(2);

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
    $schemaExtra .= schemaListaProductos($productosPagina, $filtroNombre ?: 'Licores - El Celler Nou', $paginacion['offset']);
}

// FAQ: solo en el listado principal sin filtrar y en la página 1. En las
// páginas 2..N sería un FAQPage duplicado, y con filtro aplicado las
// preguntas no encajan con lo que el usuario está mirando.
$faqs = ($paginacion['actual'] === 1 && !$tipoId)
    ? getFaqsCategoria('licores')
    : [];
// Texto propio del listado (tabla categoria_textos): intro, texto largo y
// FAQ, que van delante de las generales de la categoría.
$ambitoTexto = $tipoId ? 'tipo' : 'sector';
$idTexto = $tipoId ?: 2;
$textoCat = aplicarTextoCategoria($ambitoTexto, $idTexto, $paginacion, $pageTitle, $pageDescription, $faqs);
if (!empty($faqs)) {
    $schemaExtra .= schemaFAQ($faqs);
}

include ROOT_PATH . '/includes/header.php';
?>

<section class="category-header category-header--licores">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1><?php echo $filtroNombre ?: 'Licores'; ?></h1>
        <?php if ($textoCat && !empty($textoCat['intro'])): ?>
        <?php echo renderIntroCategoria($textoCat); ?>
        <?php else: ?>
        <p>
            <?php if ($filtroNombre): ?>
                <?php echo count($productos); ?> productos encontrados en <?php echo htmlspecialchars($filtroNombre); ?>
            <?php else: ?>
                Destilados premium y licores de las mejores marcas del mundo
            <?php endif; ?>
        </p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filters-bar">
            <div class="filter-group">
                <label for="filter-tipo">Tipo:</label>
                <select id="filter-tipo" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo Router::url('licores'); ?>">Todos los tipos</option>
                    <?php foreach ($tiposLicores as $t): ?>
                    <option value="<?php echo Router::url('licores.tipo', ['id' => $t['id'], 'slug' => createSlug($t['nombre_es'])]); ?>"
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
            <?php $productoUrl = getProductoUrlUniversal($producto); ?>
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
                    <span class="product-card-category"><?php echo htmlspecialchars($producto['tipo_nombre'] ?? ''); ?></span>
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
            <a href="<?php echo Router::url('licores'); ?>" class="btn btn-primary">Ver todos los licores</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($paginacion['actual'] === 1) echo renderTextoCategoria($textoCat, $ambitoTexto, $idTexto, $filtroNombre ?: 'Licores'); ?>

<section class="section section-alt">
    <div class="container">
        <div class="section-header"><h2>Categorías populares de licores</h2></div>
        <div class="categories-grid">
            <a href="<?php echo Router::url('licores.tipo', ['id' => 6, 'slug' => 'whisky']); ?>" class="category-card" style="background: linear-gradient(135deg, #8B4513 0%, #5a2d0d 100%);">
                <div class="category-card-content"><h3>Whisky</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
            <a href="<?php echo Router::url('licores.tipo', ['id' => 13, 'slug' => 'ron']); ?>" class="category-card" style="background: linear-gradient(135deg, #654321 0%, #3d2814 100%);">
                <div class="category-card-content"><h3>Ron</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
            <a href="<?php echo Router::url('licores.tipo', ['id' => 5, 'slug' => 'ginebra']); ?>" class="category-card" style="background: linear-gradient(135deg, #4a90a4 0%, #2d5a68 100%);">
                <div class="category-card-content"><h3>Ginebra</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
            <a href="<?php echo Router::url('licores.tipo', ['id' => 8, 'slug' => 'vermut']); ?>" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content"><h3>Vermut</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, $filtroNombre ? 'Preguntas frecuentes: ' . $filtroNombre : 'Preguntas frecuentes sobre los licores'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
