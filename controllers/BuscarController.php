<?php
/**
 * El Celler Nou - Buscar Controller
 */

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$productos = [];

if (!empty($query)) {
    $productos = buscarProductos($query);
    $komkalResults = buscarKomkalProductos($query);
    $productos = array_merge($productos, $komkalResults);
    $pageTitle = 'Resultados de búsqueda: "' . htmlspecialchars($query) . '" | El Celler Nou';
    $pageDescription = 'Resultados de búsqueda para "' . htmlspecialchars($query) . '" en El Celler Nou. Encuentra vinos, licores, cavas y más.';
} else {
    $pageTitle = 'Buscar productos | El Celler Nou';
    $pageDescription = 'Busca en nuestro catálogo de vinos, licores, cavas, cervezas y conservas.';
}

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Búsqueda', 'url' => Router::url('buscar')]
];

$schemaExtra = schemaBreadcrumb($breadcrumbItems);
if (!empty($productos)) {
    $schemaExtra .= schemaListaProductos($productos, 'Resultados de búsqueda: ' . $query);
}

include ROOT_PATH . '/includes/header.php';
?>

<section class="category-header category-header--buscar">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Buscar productos</h1>
        <p>Encuentra vinos, licores, cavas, cervezas y conservas</p>
    </div>
</section>

<section class="search-page">
    <div class="container">

        <div class="search-box">
            <form action="<?php echo Router::url('buscar'); ?>" method="GET" class="search-form" role="search">
                <label for="search-main" class="sr-only">Buscar productos</label>
                <input type="search" id="search-main" name="q" placeholder="Buscar vinos, licores, cavas..."
                       value="<?php echo htmlspecialchars($query); ?>" autocomplete="off" required>
                <button type="submit" aria-label="Buscar">
                    <svg class="icon"><use href="#icon-search"></use></svg>
                </button>
            </form>
        </div>

        <?php if (!empty($query)): ?>
            <div class="search-results-info">
                <p>
                    <?php if (count($productos) > 0): ?>
                        Se encontraron <strong><?php echo count($productos); ?></strong> resultados para "<strong><?php echo htmlspecialchars($query); ?></strong>"
                    <?php else: ?>
                        No se encontraron resultados para "<strong><?php echo htmlspecialchars($query); ?></strong>"
                    <?php endif; ?>
                </p>
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
                        <?php if (!empty($producto['procedencia_nombre']) && strpos($producto['procedencia_nombre'], 'D.O.') !== false): ?>
                        <span class="product-card-badge"><?php echo htmlspecialchars($producto['procedencia_nombre']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-content">
                        <span class="product-card-category"><?php echo htmlspecialchars($producto['tipo_nombre'] ?? $producto['sector_nombre']); ?></span>
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
                <h2>No hemos encontrado resultados</h2>
                <p>Prueba con otras palabras clave o explora nuestras categorías:</p>
                <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: center; margin-top: var(--spacing-xl);">
                    <a href="<?php echo Router::url('vinos'); ?>" class="btn btn-outline">Vinos</a>
                    <a href="<?php echo Router::url('cavas'); ?>" class="btn btn-outline">Cavas</a>
                    <a href="<?php echo Router::url('licores'); ?>" class="btn btn-outline">Licores</a>
                    <a href="<?php echo Router::url('cervezas'); ?>" class="btn btn-outline">Cervezas</a>
                    <a href="<?php echo Router::url('conservas'); ?>" class="btn btn-outline">Conservas</a>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="section-header" style="margin-top: var(--spacing-2xl);">
                <h2>Búsquedas populares</h2>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-sm); justify-content: center;">
                <a href="<?php echo Router::url('buscar'); ?>?q=rioja" class="btn btn-outline">Rioja</a>
                <a href="<?php echo Router::url('buscar'); ?>?q=ribera" class="btn btn-outline">Ribera del Duero</a>
                <a href="<?php echo Router::url('buscar'); ?>?q=whisky" class="btn btn-outline">Whisky</a>
                <a href="<?php echo Router::url('buscar'); ?>?q=cava" class="btn btn-outline">Cava</a>
                <a href="<?php echo Router::url('buscar'); ?>?q=ginebra" class="btn btn-outline">Ginebra</a>
                <a href="<?php echo Router::url('buscar'); ?>?q=vermut" class="btn btn-outline">Vermut</a>
                <a href="<?php echo Router::url('buscar'); ?>?q=priorat" class="btn btn-outline">Priorat</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header"><h2>Explora por categorías</h2></div>
        <div class="categories-grid">
            <a href="<?php echo Router::url('vinos'); ?>" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content"><h3>Vinos</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
            <a href="<?php echo Router::url('cavas'); ?>" class="category-card" style="background: linear-gradient(135deg, #C9A962 0%, #8b7343 100%);">
                <div class="category-card-content"><h3>Cavas y Espumosos</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
            <a href="<?php echo Router::url('licores'); ?>" class="category-card" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);">
                <div class="category-card-content"><h3>Licores</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
            <a href="<?php echo Router::url('cervezas'); ?>" class="category-card" style="background: linear-gradient(135deg, #d4a017 0%, #9a7512 100%);">
                <div class="category-card-content"><h3>Cervezas</h3><span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>
            </a>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
