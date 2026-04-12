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
    }
} else {
    $productos = getProductosBySector(2);
    $pageTitle = 'Licores | El Celler Nou - Destilados y licores premium';
    $pageDescription = 'Amplia selección de licores: whisky, ron, ginebra, vodka, tequila, brandy, vermut y más. Marcas premium y destilados de calidad.';
}

// Append unmatched Komkal liquors (only on unfiltered main listing)
if (!$tipoId) {
    $komkalLicores = getKomkalProductosNoVinculats('licores');
    $productos = array_merge($productos, $komkalLicores);
}

$tiposLicores = getTiposBySector(2);
$procedenciasLicores = getProcedencias(2);

$schemaExtra = schemaBreadcrumb($breadcrumbItems);
if (!empty($productos)) {
    $schemaExtra .= schemaListaProductos($productos, $filtroNombre ?: 'Licores - El Celler Nou');
}

include ROOT_PATH . '/includes/header.php';
?>

<section class="category-header category-header--licores">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1><?php echo $filtroNombre ?: 'Licores'; ?></h1>
        <p>
            <?php if ($filtroNombre): ?>
                <?php echo count($productos); ?> productos encontrados en <?php echo htmlspecialchars($filtroNombre); ?>
            <?php else: ?>
                Destilados premium y licores de las mejores marcas del mundo
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
            <?php foreach ($productos as $producto): ?>
            <?php $productoUrl = getProductoUrlUniversal($producto); ?>
            <a href="<?php echo $productoUrl; ?>" class="product-card">
                <div class="product-card-image">
                    <?php if (!empty($producto['foto'])): ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/productos/<?php echo htmlspecialchars($producto['foto']); ?>"
                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>" loading="lazy">
                    <?php else: ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/no-image.svg"
                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>" loading="lazy">
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
        <?php else: ?>
        <div class="no-results">
            <h2>No se encontraron productos</h2>
            <a href="<?php echo Router::url('licores'); ?>" class="btn btn-primary">Ver todos los licores</a>
        </div>
        <?php endif; ?>
    </div>
</section>

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

<?php include ROOT_PATH . '/includes/footer.php'; ?>
