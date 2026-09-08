<?php
/**
 * El Celler Nou - 404 Controller
 */

$pageTitle = 'Página no encontrada | El Celler Nou';
$pageDescription = 'La página que buscas no existe. Explora nuestro catálogo de vinos, licores, cavas y más.';

// Una página de error no se indexa ni emite schema: el BreadcrumbList que
// había aquí llevaba además un item "#", que no es una URL válida.
$pageRobots = 'noindex, follow';

include ROOT_PATH . '/includes/header.php';
?>

<section class="section" style="min-height: 60vh; display: flex; align-items: center;">
    <div class="container">
        <div class="text-center">
            <h1 style="font-size: 8rem; color: var(--color-primary); margin-bottom: 0;">404</h1>
            <h2 style="margin-bottom: var(--spacing-lg);">Página no encontrada</h2>
            <p style="color: var(--color-text-light); margin-bottom: var(--spacing-xl); max-width: 500px; margin-left: auto; margin-right: auto;">
                Lo sentimos, la página que buscas no existe o ha sido movida. Pero no te preocupes, puedes explorar nuestro catálogo de productos.
            </p>

            <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: center;">
                <a href="<?php echo Router::url('home'); ?>" class="btn btn-primary btn-lg">Ir al inicio</a>
                <a href="<?php echo Router::url('vinos'); ?>" class="btn btn-outline btn-lg">Ver vinos</a>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Explora nuestras categorías</h2>
        </div>

        <div class="categories-grid">
            <a href="<?php echo Router::url('vinos'); ?>" class="category-card" style="background: linear-gradient(135deg, #722F37 0%, #4a1f24 100%);">
                <div class="category-card-content">
                    <h3>Vinos</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo Router::url('cavas'); ?>" class="category-card" style="background: linear-gradient(135deg, #C9A962 0%, #8b7343 100%);">
                <div class="category-card-content">
                    <h3>Cavas y Espumosos</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo Router::url('licores'); ?>" class="category-card" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);">
                <div class="category-card-content">
                    <h3>Licores</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>

            <a href="<?php echo Router::url('cervezas'); ?>" class="category-card" style="background: linear-gradient(135deg, #d4a017 0%, #9a7512 100%);">
                <div class="category-card-content">
                    <h3>Cervezas</h3>
                    <span class="category-card-link">Explorar <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
