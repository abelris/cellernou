<?php
/**
 * El Celler Nou - Página de Licores
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
    ['name' => 'Licores', 'url' => getBaseUrl() . '/licores']
];

if ($tipoId) {
    $tipo = getTipoById($tipoId);
    if ($tipo) {
        $filtroNombre = $tipo['nombre_es'];
        $productos = getProductosByTipo($tipoId, 2);
        $breadcrumbItems[] = ['name' => $filtroNombre, 'url' => getBaseUrl() . '/licores/tipo/' . $tipoId . '/' . createSlug($filtroNombre)];
        $pageTitle = $filtroNombre . ' | Licores - El Celler Nou';
        $pageDescription = 'Selección de ' . strtolower($filtroNombre) . ' de las mejores marcas. Licores premium para los paladares más exigentes.';
    }
} else {
    $productos = getProductosBySector(2);
    $pageTitle = 'Licores | El Celler Nou - Destilados y licores premium';
    $pageDescription = 'Amplia selección de licores: whisky, ron, ginebra, vodka, tequila, brandy, vermut y más. Marcas premium y destilados de calidad.';
}

// Obtener filtros disponibles
$tiposLicores = getTiposBySector(2);
$procedenciasLicores = getProcedencias(2);

// Schema
$schemaExtra = schemaBreadcrumb($breadcrumbItems);
if (!empty($productos)) {
    $schemaExtra .= schemaListaProductos($productos, $filtroNombre ?: 'Licores - El Celler Nou');
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
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

<!-- Main Content -->
<section class="section">
    <div class="container">
        <!-- Filters -->
        <div class="filters-bar">
            <div class="filter-group">
                <label for="filter-tipo">Tipo:</label>
                <select id="filter-tipo" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo getBaseUrl(); ?>/licores">Todos los tipos</option>
                    <?php foreach ($tiposLicores as $t): ?>
                    <option value="<?php echo getBaseUrl(); ?>/licores/tipo/<?php echo $t['id']; ?>/<?php echo createSlug($t['nombre_es']); ?>"
                            <?php echo ($tipoId == $t['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['nombre_es']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="filter-origen">Origen:</label>
                <select id="filter-origen" class="filter-select" onchange="window.location.href=this.value">
                    <option value="<?php echo getBaseUrl(); ?>/licores">Todos los orígenes</option>
                    <?php foreach ($procedenciasLicores as $p): ?>
                    <option value="<?php echo getBaseUrl(); ?>/licores?origen=<?php echo $p['id']; ?>">
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
            <a href="<?php echo getBaseUrl(); ?>/licores" class="btn btn-primary">Ver todos los licores</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Popular Categories -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Categorías populares de licores</h2>
        </div>

        <div class="categories-grid">
            <a href="<?php echo getBaseUrl(); ?>/licores/tipo/6/whisky" class="category-card" style="background: linear-gradient(135deg, #8B4513 0%, #5a2d0d 100%);">
                <div class="category-card-content">
                    <h3>Whisky</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo getBaseUrl(); ?>/licores/tipo/13/ron" class="category-card" style="background: linear-gradient(135deg, #654321 0%, #3d2814 100%);">
                <div class="category-card-content">
                    <h3>Ron</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo getBaseUrl(); ?>/licores/tipo/5/ginebra" class="category-card" style="background: linear-gradient(135deg, #4a90a4 0%, #2d5a68 100%);">
                <div class="category-card-content">
                    <h3>Ginebra</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo getBaseUrl(); ?>/licores/tipo/8/vermut" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content">
                    <h3>Vermut</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
