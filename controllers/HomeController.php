<?php
/**
 * El Celler Nou - Home Controller
 */

// SEO
$pageTitle = 'Vinoteca en Premià de Mar: vinos, cavas y licores | El Celler Nou';
$pageDescription = 'Vinoteca en Premià de Mar: vinos con D.O., cavas, licores y cervezas. Te lo llevamos a casa en todo el Maresme: envío gratis desde ' . SHIPPING_FREE_MIN_ORDER . ' € y ' . ORDER_DISCOUNT_PERCENT . ' % de descuento desde ' . ORDER_DISCOUNT_MIN . ' €.';

// Obtener datos dinámicos
$productosDestacados = getProductosDestacados(8);
$stats = getEstadisticas();

// Schema: tiene que asignarse ANTES del include, porque header.php es quien
// imprime $schemaExtra. Asignarlo después dejaba la portada sin ItemList.
$schemaExtra = schemaListaProductos($productosDestacados, 'Productos Destacados - ' . SITE_NAME);

include ROOT_PATH . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <img src="https://images.unsplash.com/photo-1516594915697-87eb3b1c14ea?w=1920&q=80"
         srcset="https://images.unsplash.com/photo-1516594915697-87eb3b1c14ea?w=800&q=80 800w,
                 https://images.unsplash.com/photo-1516594915697-87eb3b1c14ea?w=1200&q=80 1200w,
                 https://images.unsplash.com/photo-1516594915697-87eb3b1c14ea?w=1920&q=80 1920w"
         sizes="100vw"
         alt=""
         class="hero-bg"
         width="1920" height="1280"
         fetchpriority="high"
         decoding="async">
    <div class="container">
        <div class="hero-content">
            <span class="hero-badge">Desde 1995</span>
            <h1>Tu tienda de vinos en Premià de Mar</h1>
            <p>Vinos con Denominación de Origen, cavas, champagnes, licores y cervezas, elegidos uno a uno. Y si no te va bien venir, te lo llevamos a casa en todo el Maresme.</p>
            <?php echo renderVentajasPedido(); ?>
            <div class="hero-buttons">
                <a href="<?php echo Router::url('vinos'); ?>" class="btn btn-secondary btn-lg">Explorar Vinos</a>
                <a href="<?php echo Router::url('domicilio'); ?>" class="btn btn-outline btn-lg" style="color: white; border-color: rgba(255,255,255,0.5);">Vino a domicilio</a>
            </div>
        </div>
    </div>
</section>

<!-- Vino a domicilio -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Te llevamos el vino a casa</h2>
            <p>En Premià de Mar y alrededores, el mismo día o el siguiente y gratis a partir de <?php echo SHIPPING_FREE_MIN_ORDER; ?> € (<?php echo SHIPPING_FREE_MIN_ORDER_ZONA2; ?> € en el resto del Maresme). Y a partir de <?php echo ORDER_DISCOUNT_MIN; ?> €, un <?php echo ORDER_DISCOUNT_PERCENT; ?> % de descuento. Pídelo por WhatsApp o por teléfono.</p>
        </div>
        <ul class="domicilio-vecinas">
            <?php foreach (getPoblacionesReparto() as $slugPob => $pob): ?>
            <li><a href="<?php echo Router::url('domicilio.poblacion', ['slug' => $slugPob]); ?>"><?php echo htmlspecialchars($pob['nombre']); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <div class="text-center mt-2">
            <a href="<?php echo Router::url('domicilio'); ?>" class="btn btn-primary btn-lg">Cómo pedir a domicilio</a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Nuestras Categorías</h2>
            <p>Explora nuestra amplia selección de productos organizados por categorías</p>
        </div>

        <div class="categories-grid">
            <a href="<?php echo Router::url('vinos'); ?>" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content">
                    <h3>Vinos</h3>
                    <p><?php echo $stats['total_vinos']; ?> productos</p>
                    <span class="category-card-link">Ver vinos <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo Router::url('cavas'); ?>" class="category-card" style="background: linear-gradient(135deg, #C9A962 0%, #8b7343 100%);">
                <div class="category-card-content">
                    <h3>Cavas y Espumosos</h3>
                    <p><?php echo $stats['total_cavas']; ?> productos</p>
                    <span class="category-card-link">Ver cavas <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo Router::url('licores'); ?>" class="category-card" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);">
                <div class="category-card-content">
                    <h3>Licores</h3>
                    <p><?php echo $stats['total_licores']; ?> productos</p>
                    <span class="category-card-link">Ver licores <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo Router::url('cervezas'); ?>" class="category-card" style="background: linear-gradient(135deg, #d4a017 0%, #9a7512 100%);">
                <div class="category-card-content">
                    <h3>Cervezas</h3>
                    <p><?php echo $stats['total_cervezas']; ?> productos</p>
                    <span class="category-card-link">Ver cervezas <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($productosDestacados)): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Productos Destacados</h2>
            <p>Una selección de nuestros mejores productos</p>
        </div>

        <div class="products-grid">
            <?php foreach ($productosDestacados as $iProd => $producto): ?>
            <?php $productoUrl = Router::url('producto', ['id' => $producto['id'], 'slug' => createSlug($producto['nombre'])]); ?>
            <a href="<?php echo $productoUrl; ?>" class="product-card">
                <div class="product-card-image">
                    <?php $imgCarga = $iProd < 4 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
                    <?php if (productoTieneFoto($producto)): ?>
                    <img src="<?php echo getBaseUrl() . rutaFoto($producto); ?>"
                         alt="<?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>"
                         width="300" height="300" decoding="async" <?php echo $imgCarga; ?>>
                    <?php else: ?>
                    <img src="<?php echo getBaseUrl(); ?>/images/no-image.svg"
                         alt="<?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>"
                         width="300" height="300" decoding="async" <?php echo $imgCarga; ?>>
                    <?php endif; ?>
                    <?php if (!empty($producto['procedencia_nombre']) && strpos($producto['procedencia_nombre'], 'D.O.') !== false): ?>
                    <span class="product-card-badge"><?php echo htmlspecialchars($producto['procedencia_nombre']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="product-card-content">
                    <span class="product-card-category"><?php echo htmlspecialchars($producto['tipo_nombre'] ?? $producto['sector_nombre'] ?? 'Producto'); ?></span>
                    <h3 class="product-card-title">
                        <?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>
                    </h3>
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

        <div class="text-center mt-2">
            <a href="<?php echo Router::url('vinos'); ?>" class="btn btn-primary btn-lg">Ver todos los productos</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- D.O. Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Denominaciones de Origen</h2>
            <p>Vinos con la garantía de las mejores D.O. españolas</p>
        </div>

        <?php
        // Obtener D.O. dinámicamente
        $denominaciones = getProcedencias(8);
        $doDestacadas = array_filter($denominaciones, function($do) {
            return strpos($do['nombre_es'], 'D.O.') !== false;
        });
        $doDestacadas = array_slice($doDestacadas, 0, 4);
        ?>

        <div class="categories-grid">
            <?php
            $colores = [
                'linear-gradient(135deg, #8B0000 0%, #5c0000 100%)',
                'linear-gradient(135deg, #4a0e0e 0%, #2d0909 100%)',
                'linear-gradient(135deg, #3d0c0c 0%, #1f0606 100%)',
                'linear-gradient(135deg, #5a3d2b 0%, #3d2a1d 100%)'
            ];
            $i = 0;
            foreach ($doDestacadas as $do):
            ?>
            <a href="<?php echo Router::url('vinos.do', ['id' => $do['id'], 'slug' => createSlug($do['nombre_es'])]); ?>" class="category-card" style="background: <?php echo $colores[$i % 4]; ?>;">
                <div class="category-card-content">
                    <h3><?php echo htmlspecialchars($do['nombre_es']); ?></h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>
            <?php $i++; endforeach; ?>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>¿Por qué El Celler Nou?</h2>
            <p>Más de 20 años seleccionando los mejores productos para nuestros clientes</p>
        </div>

        <div class="categories-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="text-center">
                <h3 style="font-size: 3rem; color: var(--color-primary); margin-bottom: 0.5rem;">+<?php echo $stats['total_productos']; ?></h3>
                <p>Referencias en catálogo</p>
            </div>
            <div class="text-center">
                <h3 style="font-size: 3rem; color: var(--color-primary); margin-bottom: 0.5rem;">+25</h3>
                <p>Años de experiencia</p>
            </div>
            <div class="text-center">
                <h3 style="font-size: 3rem; color: var(--color-primary); margin-bottom: 0.5rem;"><?php echo $stats['total_do']; ?></h3>
                <p>D.O. disponibles</p>
            </div>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
