<?php
/**
 * El Celler Nou - Página de Cervezas
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

// Obtener productos
$productos = getProductosBySector(21);

// SEO
$pageTitle = 'Cervezas | El Celler Nou - Cervezas artesanales y de importación';
$pageDescription = 'Descubre nuestra selección de cervezas artesanales, de importación y nacionales. Las mejores cervezas del mundo en El Celler Nou.';

// Breadcrumb
$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => getBaseUrl()],
    ['name' => 'Cervezas', 'url' => getBaseUrl() . '/cervezas']
];

// Filtros
$procedenciasCervezas = getProcedencias(21);

// Schema
$schemaExtra = schemaBreadcrumb($breadcrumbItems);
if (!empty($productos)) {
    $schemaExtra .= schemaListaProductos($productos, 'Cervezas - El Celler Nou');
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Cervezas</h1>
        <p>Selección de cervezas artesanales, de importación y nacionales</p>
    </div>
</section>

<!-- Main Content -->
<section class="section">
    <div class="container">
        <!-- Filters -->
        <div class="filters-bar">
            <div class="filter-group">
                <label for="filter-origen">Origen:</label>
                <select id="filter-origen" class="filter-select">
                    <option value="">Todos los orígenes</option>
                    <?php foreach ($procedenciasCervezas as $p): ?>
                    <option value="<?php echo $p['id']; ?>">
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
            <?php foreach ($productos as $producto): ?>
            <article class="product-card" itemscope itemtype="https://schema.org/Product">
                <div class="product-card-image">
                    <?php if (!empty($producto['foto'])): ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/productos/<?php echo htmlspecialchars($producto['foto']); ?>"
                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                         loading="lazy"
                         itemprop="image">
                    <?php else: ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/no-image.svg"
                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                         loading="lazy">
                    <?php endif; ?>
                </div>
                <div class="product-card-content">
                    <span class="product-card-category">Cerveza</span>
                    <h2 class="product-card-title" itemprop="name">
                        <a href="<?php echo getProductoUrl($producto); ?>" itemprop="url"><?php echo htmlspecialchars($producto['nombre']); ?></a>
                    </h2>
                    <?php if (!empty($producto['procedencia_nombre'])): ?>
                    <p class="product-card-origin" itemprop="brand"><?php echo htmlspecialchars($producto['procedencia_nombre']); ?></p>
                    <?php endif; ?>
                    <div class="product-card-footer">
                        <?php if ($producto['precio_venta'] > 0): ?>
                        <span class="product-card-price"><?php echo formatPrecio($producto['precio_venta']); ?></span>
                        <?php else: ?>
                        <span class="product-card-price">Consultar</span>
                        <?php endif; ?>
                        <a href="<?php echo getProductoUrl($producto); ?>" class="product-card-link">
                            Ver más
                            <svg class="icon"><use href="#icon-arrow-right"></use></svg>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-results">
            <h2>No se encontraron productos</h2>
            <p>Pronto tendremos más cervezas disponibles.</p>
            <a href="<?php echo getBaseUrl(); ?>/" class="btn btn-primary">Volver al inicio</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Related Categories -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>También te puede interesar</h2>
        </div>

        <div class="categories-grid">
            <a href="<?php echo getBaseUrl(); ?>/vinos" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content">
                    <h3>Vinos</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo getBaseUrl(); ?>/licores" class="category-card" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);">
                <div class="category-card-content">
                    <h3>Licores</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo getBaseUrl(); ?>/conservas" class="category-card" style="background: linear-gradient(135deg, #4a7c59 0%, #2d4d37 100%);">
                <div class="category-card-content">
                    <h3>Conservas</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
