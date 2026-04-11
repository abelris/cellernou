<?php
/**
 * El Celler Nou - Página de Vinos
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

// Determinar si estamos filtrando por tipo o D.O.
$tipoId = isset($_GET['tipo']) ? intval($_GET['tipo']) : null;
$doId = isset($_GET['do']) ? intval($_GET['do']) : null;
$filtroNombre = '';
$productos = [];

// Breadcrumb base
$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => getBaseUrl()],
    ['name' => 'Vinos', 'url' => getBaseUrl() . '/vinos']
];

if ($tipoId) {
    $tipo = getTipoById($tipoId);
    if ($tipo) {
        $filtroNombre = $tipo['nombre_es'];
        $productos = getProductosByTipo($tipoId, 8);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => getBaseUrl() . '/vinos/tipo/' . $tipoId . '/' . createSlug($filtroNombre)];
        $pageTitle = $filtroNombre . ' | Vinos - El Celler Nou';
        $pageDescription = 'Descubre nuestra selección de ' . strtolower($filtroNombre) . '. Vinos de calidad con las mejores Denominaciones de Origen.';
    }
} elseif ($doId) {
    $procedencia = getProcedenciaById($doId);
    if ($procedencia) {
        $filtroNombre = $procedencia['nombre_es'];
        $productos = getProductosByProcedencia($doId, 8);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => getBaseUrl() . '/vinos/do/' . $doId . '/' . createSlug($filtroNombre)];
        $pageTitle = $filtroNombre . ' | Vinos - El Celler Nou';
        $pageDescription = 'Vinos ' . $filtroNombre . '. Selección de vinos con denominación de origen de la mejor calidad.';
    }
} else {
    $productos = getProductosBySector(8);
    $pageTitle = 'Vinos | El Celler Nou - Tu tienda de vinos de calidad';
    $pageDescription = 'Amplia selección de vinos tintos, blancos y rosados. Vinos con D.O. La Rioja, Ribera del Duero, Priorat, Penedès y más.';
}

// Obtener filtros disponibles
$tiposVinos = getTiposBySector(8);
$procedenciasVinos = getProcedencias(8);

// Schema
$schemaExtra = schemaBreadcrumb($breadcrumbItems);
if (!empty($productos)) {
    $schemaExtra .= schemaListaProductos($productos, $filtroNombre ?: 'Vinos - El Celler Nou');
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
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
                    <option value="<?php echo getBaseUrl(); ?>/vinos">Todos los tipos</option>
                    <?php foreach ($tiposVinos as $t): ?>
                    <option value="<?php echo getBaseUrl(); ?>/vinos/tipo/<?php echo $t['id']; ?>/<?php echo createSlug($t['nombre_es']); ?>"
                            <?php echo ($tipoId == $t['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['nombre_es']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter-do">D.O.:</label>
                <select id="filter-do" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo getBaseUrl(); ?>/vinos">Todas las D.O.</option>
                    <?php foreach ($procedenciasVinos as $p): ?>
                    <option value="<?php echo getBaseUrl(); ?>/vinos/do/<?php echo $p['id']; ?>/<?php echo createSlug($p['nombre_es']); ?>"
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
                    <?php if (!empty($producto['procedencia_nombre']) && strpos($producto['procedencia_nombre'], 'D.O.') !== false): ?>
                    <span class="product-card-badge"><?php echo htmlspecialchars($producto['procedencia_nombre']); ?></span>
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
                        <span class="product-card-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                            <span itemprop="price" content="<?php echo $producto['precio_venta']; ?>"><?php echo formatPrecio($producto['precio_venta']); ?></span>
                            <meta itemprop="priceCurrency" content="EUR">
                        </span>
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
            <a href="<?php echo getBaseUrl(); ?>/vinos" class="btn btn-primary">Ver todos los vinos</a>
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
            <a href="<?php echo getBaseUrl(); ?>/vinos/tipo/1/vino-tinto" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content">
                    <h3>Vinos Tintos</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo getBaseUrl(); ?>/vinos/tipo/2/vino-blanco" class="category-card" style="background: linear-gradient(135deg, #C9A962 0%, #8b7343 100%);">
                <div class="category-card-content">
                    <h3>Vinos Blancos</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo getBaseUrl(); ?>/vinos/tipo/3/vino-rosado" class="category-card" style="background: linear-gradient(135deg, #d4a5a5 0%, #b08080 100%);">
                <div class="category-card-content">
                    <h3>Vinos Rosados</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo getBaseUrl(); ?>/cavas" class="category-card" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);">
                <div class="category-card-content">
                    <h3>Cavas y Espumosos</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
