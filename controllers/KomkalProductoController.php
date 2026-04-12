<?php
/**
 * El Celler Nou - Komkal Producto Controller
 * Shows unmatched Komkal products on the public site.
 */

$tipo = Router::param('tipo');
$id = Router::param('id') ? intval(Router::param('id')) : 0;

if (!$id || !in_array($tipo, ['vinos', 'licores'])) {
    Router::redirect(Router::url('home'));
}

$producto = getKomkalProductoById($tipo, $id);

if (!$producto) {
    http_response_code(404);
    require ROOT_PATH . '/controllers/404.php';
    return;
}

// Category info
$categoriaNombre = ($tipo === 'vinos') ? 'Vinos' : 'Licores';
$categoriaRoute = ($tipo === 'vinos') ? 'vinos' : 'licores';

// SEO
$pageTitle = normalizarNombre($producto['nombre']) . ' | ' . $categoriaNombre . ' - El Celler Nou';
$pageDescription = normalizarNombre($producto['nombre']) . ' - ' . $categoriaNombre . ' disponible en El Celler Nou. Consulta precio y solicita información.';
$pageType = 'product';

// Breadcrumb
$productoUrl = getProductoUrlUniversal($producto);
$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => $categoriaNombre, 'url' => Router::url($categoriaRoute)],
    ['name' => normalizarNombre($producto['nombre']), 'url' => $productoUrl]
];

$schemaExtra = schemaBreadcrumb($breadcrumbItems);

include ROOT_PATH . '/includes/header.php';
?>

<section class="product-detail">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>

        <div class="product-detail-grid">
            <div class="product-gallery">
                <div class="product-main-image">
                    <img src="<?php echo getBaseUrl(); ?>/images/no-image.svg"
                         alt="<?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>">
                </div>
            </div>

            <div class="product-info" itemscope itemtype="https://schema.org/Product">
                <h1 itemprop="name"><?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?></h1>

                <div class="product-meta">
                    <span class="product-meta-item">
                        <strong>Categoría:</strong>
                        <span><?php echo htmlspecialchars($categoriaNombre); ?></span>
                    </span>
                    <?php if (!empty($producto['codigo'])): ?>
                    <span class="product-meta-item">
                        <strong>Ref:</strong>
                        <?php echo htmlspecialchars($producto['codigo']); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="product-price-box" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                    <meta itemprop="priceCurrency" content="EUR">
                    <link itemprop="availability" href="https://schema.org/InStock">

                    <?php if ($producto['precio_venta'] > 0): ?>
                    <div class="product-price">
                        <span itemprop="price" content="<?php echo number_format($producto['precio_venta'], 2, '.', ''); ?>">
                            <?php echo number_format($producto['precio_venta'], 2, ',', '.'); ?> &euro;
                        </span>
                        <small>IVA incluido</small>
                    </div>
                    <?php else: ?>
                    <div class="product-price">Consultar precio</div>
                    <?php endif; ?>

                    <a href="<?php echo Router::url('contacto'); ?>?producto=<?php echo urlencode($producto['nombre']); ?>" class="btn btn-primary btn-lg" style="width: 100%;">
                        Solicitar información
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
