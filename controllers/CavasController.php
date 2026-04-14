<?php
/**
 * El Celler Nou - Cavas Controller
 */

$tipoId = Router::param('id') ? intval(Router::param('id')) : null;
$filtroNombre = '';
$productos = [];

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Cavas y Espumosos', 'url' => Router::url('cavas')]
];

if ($tipoId) {
    $tipo = getTipoById($tipoId);
    if ($tipo) {
        $filtroNombre = $tipo['nombre_es'];
        $productos = getProductosByTipo($tipoId, 14);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => Router::url('cavas.tipo', ['id' => $tipoId, 'slug' => createSlug($filtroNombre)])];
        $pageTitle = $filtroNombre . ' | Cavas y Espumosos - El Celler Nou';
        $pageDescription = 'Descubre nuestra selección de ' . strtolower($filtroNombre) . '. Los mejores espumosos para celebrar.';
    }
} else {
    $productos = getProductosBySector(14);
    $pageTitle = 'Cavas y Espumosos | El Celler Nou';
    $pageDescription = 'Selección de cavas, champagnes y espumosos de calidad. Encuentra el espumoso perfecto para cada ocasión.';
}

$tiposCavas = getTiposBySector(14);

$schemaExtra = schemaBreadcrumb($breadcrumbItems);
if (!empty($productos)) {
    $schemaExtra .= schemaListaProductos($productos, $filtroNombre ?: 'Cavas y Espumosos - El Celler Nou');
}

include ROOT_PATH . '/includes/header.php';
?>

<section class="category-header category-header--cavas">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1><?php echo $filtroNombre ?: 'Cavas y Espumosos'; ?></h1>
        <p>
            <?php if ($filtroNombre): ?>
                <?php echo count($productos); ?> productos encontrados en <?php echo htmlspecialchars($filtroNombre); ?>
            <?php else: ?>
                Los mejores cavas, champagnes y espumosos para tus celebraciones
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
                    <option value="<?php echo Router::url('cavas'); ?>">Todos los tipos</option>
                    <?php foreach ($tiposCavas as $t): ?>
                    <option value="<?php echo Router::url('cavas.tipo', ['id' => $t['id'], 'slug' => createSlug($t['nombre_es'])]); ?>"
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
            <?php $productoUrl = Router::url('producto', ['id' => $producto['id'], 'slug' => createSlug($producto['nombre'])]); ?>
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
                    <span class="product-card-category"><?php echo htmlspecialchars($producto['tipo_nombre']); ?></span>
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
            <a href="<?php echo Router::url('cavas'); ?>" class="btn btn-primary">Ver todos los cavas</a>
        </div>
        <?php endif; ?>
    </div>
</section>

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
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
