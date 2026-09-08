<?php
/**
 * El Celler Nou - Producto Controller
 */

$id = Router::param('id') ? intval(Router::param('id')) : 0;

if (!$id) {
    Router::redirect(Router::url('home'));
}

$producto = getProductoById($id);
$productoTextos = $producto ? getProductoTextos($id) : [];

if (!$producto) {
    http_response_code(404);
    require ROOT_PATH . '/controllers/404.php';
    return;
}

// Determinar categoría para breadcrumb
$categoriaNombre = 'Productos';
$categoriaRoute = 'home';

switch ($producto['idSector']) {
    case 8:
        $categoriaNombre = 'Vinos';
        $categoriaRoute = 'vinos';
        break;
    case 14:
        $categoriaNombre = 'Cavas y Espumosos';
        $categoriaRoute = 'cavas';
        break;
    case 2:
        $categoriaNombre = 'Licores';
        $categoriaRoute = 'licores';
        break;
    case 21:
        $categoriaNombre = 'Cervezas';
        $categoriaRoute = 'cervezas';
        break;
    case 12:
        $categoriaNombre = 'Conservas';
        $categoriaRoute = 'conservas';
        break;
}

// Slug canónico: cualquier otra variante (/producto/3269, /producto/3269/lo-que-sea)
// redirige 301 a la URL buena. Sin esto cada variante devolvía 200 con un
// canonical apuntándose a sí misma: duplicados infinitos.
$slugCanonico = createSlug($producto['nombre']);
$slugActual   = Router::param('slug');
if ($slugActual !== $slugCanonico) {
    Router::redirect(Router::url('producto', ['id' => $producto['id'], 'slug' => $slugCanonico]), 301);
}

// El canonical se construye desde la ruta nombrada, nunca desde REQUEST_URI.
$currentUrl = Router::urlAbs('producto', ['id' => $producto['id'], 'slug' => $slugCanonico]);

// SEO
$pageTitle = trim(normalizarNombre($producto['nombre'])) . ' | ' . $categoriaNombre . ' - El Celler Nou';
$pageDescription = !empty($productoTextos['descripcion'])
    ? strip_tags(substr($productoTextos['descripcion'], 0, 160))
    : (!empty($producto['descripcion'])
        ? strip_tags(substr($producto['descripcion'], 0, 160))
        : $producto['nombre'] . ' - ' . ($producto['tipo_nombre'] ?? '') . ' de ' . ($producto['procedencia_nombre'] ?? 'calidad') . '. Compra en El Celler Nou.');
$pageType = 'product';
// og:image: la foto del producto solo si el archivo existe; si no, la imagen
// social por defecto. Una og:image rota deja la tarjeta al compartir en blanco.
if (productoTieneFoto($producto)) {
    $pageImage = getBaseUrl() . rutaFoto($producto);
} else {
    $pageImage = getBaseUrl() . '/images/og-default.jpg';
    $pageImageWidth = 1200;
    $pageImageHeight = 630;
}

// Breadcrumb
$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => $categoriaNombre, 'url' => Router::url($categoriaRoute)],
    ['name' => $producto['nombre'], 'url' => Router::url('producto', ['id' => $producto['id'], 'slug' => createSlug($producto['nombre'])])]
];

// Productos relacionados
$productosRelacionados = getProductosRelacionados($producto, 4);

// Schema
$schemaExtra = schemaBreadcrumb($breadcrumbItems);
$schemaExtra .= schemaProducto($producto, $productoTextos);

include ROOT_PATH . '/includes/header.php';
?>

<section class="product-detail">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>

        <div class="product-detail-grid">
            <div class="product-gallery">
                <div class="product-main-image">
                    <?php if (productoTieneFoto($producto)): ?>
                    <img src="<?php echo getBaseUrl() . rutaFoto($producto); ?>"
                         alt="<?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>"
                         width="600" height="600"
                         fetchpriority="high" decoding="async">
                    <?php else: ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/no-image.svg"
                         alt="<?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>"
                         width="600" height="600">
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-info">
                <h1><?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?></h1>

                <div class="product-meta">
                    <?php if (!empty($producto['tipo_nombre'])): ?>
                    <span class="product-meta-item">
                        <strong>Tipo:</strong>
                        <span><?php echo htmlspecialchars($producto['tipo_nombre']); ?></span>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($producto['procedencia_nombre'])): ?>
                    <span class="product-meta-item">
                        <strong>Origen:</strong>
                        <span><?php echo htmlspecialchars($producto['procedencia_nombre']); ?></span>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($producto['uvas_es'])): ?>
                    <span class="product-meta-item">
                        <strong>Uvas:</strong>
                        <?php echo htmlspecialchars($producto['uvas_es']); ?>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($producto['codigo'])): ?>
                    <span class="product-meta-item">
                        <strong>Ref:</strong>
                        <?php echo htmlspecialchars($producto['codigo']); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="product-price-box">

                    <?php if ($producto['precio_venta'] > 0 && !empty($producto['tiene_stock'])): ?>
                    <?php $precioFinal = aplicarDescuento($producto['precio_venta']); ?>
                    <div class="product-price">
                        <span>
                            <?php echo formatPrecioWeb($producto['precio_venta']); ?>
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

                <?php if (!empty($productoTextos['descripcion'])): ?>
                <div class="product-description">
                    <h2>Descripción</h2>
                    <?php echo nl2br(htmlspecialchars($productoTextos['descripcion'])); ?>
                </div>
                <?php elseif (!empty($producto['descripcion'])): ?>
                <div class="product-description">
                    <h2>Descripción</h2>
                    <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($productoTextos['nota_cata'])): ?>
                <div class="product-tasting-note">
                    <h2>Nota de cata</h2>
                    <?php echo nl2br(htmlspecialchars($productoTextos['nota_cata'])); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($productoTextos['texto_libre'])): ?>
                <div class="product-free-text">
                    <?php echo nl2br(htmlspecialchars($productoTextos['texto_libre'])); ?>
                </div>
                <?php endif; ?>

                <div class="product-links" style="padding: var(--spacing-lg); background: var(--color-bg-alt); border-radius: var(--radius-lg);">
                    <h3 style="font-size: 1rem; margin-bottom: var(--spacing-md);">Explora más</h3>
                    <ul style="display: flex; flex-wrap: wrap; gap: var(--spacing-sm);">
                        <?php if (!empty($producto['tipo_nombre'])): ?>
                        <li>
                            <a href="<?php echo Router::url($categoriaRoute . '.tipo', ['id' => $producto['idTipo'], 'slug' => createSlug($producto['tipo_nombre'])]); ?>"
                               class="btn btn-outline" style="padding: var(--spacing-sm) var(--spacing-md); font-size: 0.875rem;">
                                Más <?php echo htmlspecialchars($producto['tipo_nombre']); ?>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if (!empty($producto['procedencia_nombre']) && strpos($producto['procedencia_nombre'], 'D.O.') !== false): ?>
                        <li>
                            <a href="<?php echo Router::url('vinos.do', ['id' => $producto['idProcedencia'], 'slug' => createSlug($producto['procedencia_nombre'])]); ?>"
                               class="btn btn-outline" style="padding: var(--spacing-sm) var(--spacing-md); font-size: 0.875rem;">
                                Más de <?php echo htmlspecialchars($producto['procedencia_nombre']); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($productosRelacionados)): ?>
<section class="related-products">
    <div class="container">
        <div class="section-header">
            <h2>Productos relacionados</h2>
            <p>Otros productos que te pueden interesar</p>
        </div>

        <div class="products-grid">
            <?php foreach ($productosRelacionados as $rel): ?>
            <?php $relUrl = Router::url('producto', ['id' => $rel['id'], 'slug' => createSlug($rel['nombre'])]); ?>
            <a href="<?php echo $relUrl; ?>" class="product-card">
                <div class="product-card-image">
                    <?php if (productoTieneFoto($rel)): ?>
                    <img src="<?php echo getBaseUrl() . rutaFoto($rel); ?>"
                         alt="<?php echo htmlspecialchars(normalizarNombre($rel['nombre'])); ?>"
                         width="300" height="300" loading="lazy" decoding="async">
                    <?php else: ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/no-image.svg"
                         alt="<?php echo htmlspecialchars(normalizarNombre($rel['nombre'])); ?>"
                         width="300" height="300" loading="lazy" decoding="async">
                    <?php endif; ?>
                </div>
                <div class="product-card-content">
                    <span class="product-card-category"><?php echo htmlspecialchars($rel['tipo_nombre']); ?></span>
                    <h3 class="product-card-title">
                        <?php echo htmlspecialchars(normalizarNombre($rel['nombre'])); ?>
                    </h3>
                    <?php if (!empty($rel['procedencia_nombre'])): ?>
                    <p class="product-card-origin"><?php echo htmlspecialchars($rel['procedencia_nombre']); ?></p>
                    <?php endif; ?>
                    <div class="product-card-footer">
                        <?php if ($rel['precio_venta'] > 0 && !empty($rel['tiene_stock'])): ?>
                        <span class="product-card-price"><?php echo formatPrecioWeb($rel['precio_venta']); ?></span>
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
    </div>
</section>
<?php endif; ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
