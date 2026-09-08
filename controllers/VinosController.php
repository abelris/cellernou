<?php
/**
 * El Celler Nou - Vinos Controller
 */

// Obtener parámetros de la ruta
$tipoId = Router::param('id') ? intval(Router::param('id')) : null;
$doId = null;
$filtroNombre = '';
$productos = [];

// Detectar si es filtro por D.O.
if (Router::is('vinos.do')) {
    $doId = $tipoId;
    $tipoId = null;
}

// Breadcrumb base
$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Vinos', 'url' => Router::url('vinos')]
];

if ($tipoId) {
    $tipo = getTipoById($tipoId);
    if ($tipo) {
        $filtroNombre = $tipo['nombre_es'];
        $productos = getProductosByTipo($tipoId, 8);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => Router::url('vinos.tipo', ['id' => $tipoId, 'slug' => createSlug($filtroNombre)])];
        $pageTitle = $filtroNombre . ' | Vinos - El Celler Nou';
        $pageDescription = 'Descubre nuestra selección de ' . strtolower($filtroNombre) . '. Vinos de calidad con las mejores Denominaciones de Origen.';
    }
} elseif ($doId) {
    $procedencia = getProcedenciaById($doId);
    if ($procedencia) {
        $filtroNombre = $procedencia['nombre_es'];
        $productos = getProductosByProcedencia($doId, 8);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => Router::url('vinos.do', ['id' => $doId, 'slug' => createSlug($filtroNombre)])];
        $pageTitle = $filtroNombre . ' | Vinos - El Celler Nou';
        $pageDescription = 'Vinos ' . $filtroNombre . '. Selección de vinos con denominación de origen de la mejor calidad.';
    }
} else {
    $productos = getProductosBySector(8);
    $pageTitle = 'Vinos | El Celler Nou - Tu tienda de vinos de calidad';
    $pageDescription = 'Amplia selección de vinos tintos, blancos y rosados. Vinos con D.O. La Rioja, Ribera del Duero, Priorat, Penedès y más.';
}

// Append unmatched Komkal wines (only on unfiltered main listing)
if (!$tipoId && !$doId) {
    $komkalVinos = getKomkalProductosNoVinculats('vinos');
    $productos = array_merge($productos, $komkalVinos);
}

// Obtener filtros disponibles
$tiposVinos = getTiposBySector(8);
$procedenciasVinos = getProcedencias(8);

// Schema
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
    $schemaExtra .= schemaListaProductos($productosPagina, $filtroNombre ?: 'Vinos - El Celler Nou', $paginacion['offset']);
}

// FAQ: solo en el listado principal sin filtrar y en la página 1. En las
// páginas 2..N sería un FAQPage duplicado, y con filtro aplicado las
// preguntas no encajan con lo que el usuario está mirando.
$faqs = ($paginacion['actual'] === 1 && !$tipoId && !$doId)
    ? getFaqsCategoria('vinos')
    : [];
if (!empty($faqs)) {
    $schemaExtra .= schemaFAQ($faqs);
}

include ROOT_PATH . '/includes/header.php';
?>

<!-- Category Header -->
<section class="category-header category-header--vinos">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1><?php echo $filtroNombre ?: 'Vinos'; ?></h1>
        <p>
            <?php if ($filtroNombre): ?>
                <?php echo count($productos); ?> productos encontrados en <?php echo htmlspecialchars($filtroNombre); ?>
            <?php else: ?>
                Descubre nuestra selección de vinos con las mejores Denominaciones de Origen
            <?php endif; ?>
        </p>
    </div>
</section>

<!-- Main Content -->
<section class="section">
    <div class="container">
        <!-- Filters -->
        <div class="filters-bar">
            <div class="filter-group">
                <label for="filter-tipo">Tipo:</label>
                <select id="filter-tipo" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo Router::url('vinos'); ?>">Todos los tipos</option>
                    <?php foreach ($tiposVinos as $t): ?>
                    <option value="<?php echo Router::url('vinos.tipo', ['id' => $t['id'], 'slug' => createSlug($t['nombre_es'])]); ?>"
                            <?php echo ($tipoId == $t['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['nombre_es']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter-do">D.O.:</label>
                <select id="filter-do" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo Router::url('vinos'); ?>">Todas las D.O.</option>
                    <?php foreach ($procedenciasVinos as $p): ?>
                    <option value="<?php echo Router::url('vinos.do', ['id' => $p['id'], 'slug' => createSlug($p['nombre_es'])]); ?>"
                            <?php echo ($doId == $p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nombre_es']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <span class="results-count"><?php echo count($productos); ?> productos</span>
        </div>

        <!-- Products Grid -->
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
                    <?php if (!empty($producto['procedencia_nombre']) && strpos($producto['procedencia_nombre'], 'D.O.') !== false): ?>
                    <span class="product-card-badge"><?php echo htmlspecialchars($producto['procedencia_nombre']); ?></span>
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
                            Ver más
                            <svg class="icon"><use href="#icon-arrow-right"></use></svg>
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
            <p>Prueba a cambiar los filtros o explorar otras categorías.</p>
            <a href="<?php echo Router::url('vinos'); ?>" class="btn btn-primary">Ver todos los vinos</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Related Categories -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Explora más categorías</h2>
        </div>

        <div class="categories-grid">
            <a href="<?php echo Router::url('vinos.tipo', ['id' => 1, 'slug' => 'vino-tinto']); ?>" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content">
                    <h3>Vinos Tintos</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo Router::url('vinos.tipo', ['id' => 2, 'slug' => 'vino-blanco']); ?>" class="category-card" style="background: linear-gradient(135deg, #C9A962 0%, #8b7343 100%);">
                <div class="category-card-content">
                    <h3>Vinos Blancos</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo Router::url('vinos.tipo', ['id' => 3, 'slug' => 'vino-rosado']); ?>" class="category-card" style="background: linear-gradient(135deg, #d4a5a5 0%, #b08080 100%);">
                <div class="category-card-content">
                    <h3>Vinos Rosados</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo Router::url('cavas'); ?>" class="category-card" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);">
                <div class="category-card-content">
                    <h3>Cavas y Espumosos</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes sobre los vinos'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
