<?php
/**
 * El Celler Nou - Página de Cavas y Espumosos
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

// Determinar si estamos filtrando por tipo
$tipoId = isset($_GET['tipo']) ? intval($_GET['tipo']) : null;
$filtroNombre = '';
$productos = [];

// Breadcrumb base
$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => getBaseUrl()],
    ['name' => 'Cavas y Espumosos', 'url' => getBaseUrl() . '/cavas']
];

if ($tipoId) {
    $tipo = getTipoById($tipoId);
    if ($tipo) {
        $filtroNombre = $tipo['nombre_es'];
        $productos = getProductosByTipo($tipoId, 14);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => getBaseUrl() . '/cavas/tipo/' . $tipoId . '/' . createSlug($filtroNombre)];
        $pageTitle = $filtroNombre . ' | Cavas y Espumosos - El Celler Nou';
        $pageDescription = 'Descubre nuestra selección de ' . strtolower($filtroNombre) . '. Los mejores espumosos para celebrar.';
    }
} else {
    $productos = getProductosBySector(14);
    $pageTitle = 'Cavas y Espumosos | El Celler Nou';
    $pageDescription = 'Selección de cavas, champagnes y espumosos de calidad. Encuentra el espumoso perfecto para cada ocasión.';
}

// Obtener filtros disponibles
$tiposCavas = getTiposBySector(14);
$procedenciasCavas = getProcedencias(14);

// Schema
$schemaExtra = schemaBreadcrumb($breadcrumbItems);
if (!empty($productos)) {
    $schemaExtra .= schemaListaProductos($productos, $filtroNombre ?: 'Cavas y Espumosos - El Celler Nou');
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
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

<!-- Main Content -->
<section class="section">
    <div class="container">
        <!-- Filters -->
        <div class="filters-bar">
            <div class="filter-group">
                <label for="filter-tipo">Tipo:</label>
                <select id="filter-tipo" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo getBaseUrl(); ?>/cavas">Todos los tipos</option>
                    <?php foreach ($tiposCavas as $t): ?>
                    <option value="<?php echo getBaseUrl(); ?>/cavas/tipo/<?php echo $t['id']; ?>/<?php echo createSlug($t['nombre_es']); ?>"
                            <?php echo ($tipoId == $t['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['nombre_es']); ?>
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
                    <span class="product-card-category"><?php echo htmlspecialchars($producto['tipo_nombre']); ?></span>
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
            <p>Prueba a cambiar los filtros o explorar otras categorías.</p>
            <a href="<?php echo getBaseUrl(); ?>/cavas" class="btn btn-primary">Ver todos los cavas</a>
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
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
