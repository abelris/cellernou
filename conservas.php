<?php
/**
 * El Celler Nou - Página de Conservas
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
    ['name' => 'Conservas', 'url' => getBaseUrl() . '/conservas']
];

if ($tipoId) {
    $tipo = getTipoById($tipoId);
    if ($tipo) {
        $filtroNombre = $tipo['nombre_es'];
        $productos = getProductosByTipo($tipoId, 12);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => getBaseUrl() . '/conservas/tipo/' . $tipoId . '/' . createSlug($filtroNombre)];
        $pageTitle = $filtroNombre . ' | Conservas - El Celler Nou';
        $pageDescription = 'Las mejores ' . strtolower($filtroNombre) . ' en conserva. Productos gourmet de máxima calidad.';
    }
} else {
    $productos = getProductosBySector(12);
    $pageTitle = 'Conservas | El Celler Nou - Conservas gourmet de marisco';
    $pageDescription = 'Selección de conservas gourmet: mejillones, berberechos, navajas, calamares y más. Productos del mar de la mejor calidad.';
}

// Obtener filtros disponibles
$tiposConservas = getTiposBySector(12);

// Schema
$schemaExtra = schemaBreadcrumb($breadcrumbItems);
if (!empty($productos)) {
    $schemaExtra .= schemaListaProductos($productos, $filtroNombre ?: 'Conservas - El Celler Nou');
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
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

<!-- Main Content -->
<section class="section">
    <div class="container">
        <!-- Filters -->
        <div class="filters-bar">
            <div class="filter-group">
                <label for="filter-tipo">Tipo:</label>
                <select id="filter-tipo" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo getBaseUrl(); ?>/conservas">Todos los tipos</option>
                    <?php foreach ($tiposConservas as $t): ?>
                    <option value="<?php echo getBaseUrl(); ?>/conservas/tipo/<?php echo $t['id']; ?>/<?php echo createSlug($t['nombre_es']); ?>"
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
                    <span class="product-card-category"><?php echo htmlspecialchars($producto['tipo_nombre'] ?? 'Conserva'); ?></span>
                    <h2 class="product-card-title" itemprop="name">
                        <a href="<?php echo getProductoUrl($producto); ?>" itemprop="url"><?php echo htmlspecialchars($producto['nombre']); ?></a>
                    </h2>
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
            <a href="<?php echo getBaseUrl(); ?>/conservas" class="btn btn-primary">Ver todas las conservas</a>
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
                    <p>El maridaje perfecto</p>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo getBaseUrl(); ?>/cervezas" class="category-card" style="background: linear-gradient(135deg, #d4a017 0%, #9a7512 100%);">
                <div class="category-card-content">
                    <h3>Cervezas</h3>
                    <p>Para acompañar</p>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
