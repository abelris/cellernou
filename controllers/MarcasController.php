<?php
/**
 * El Celler Nou - Marcas
 *
 * /marcas   Índice de las marcas con página propia (MARCAS en includes/functions.php).
 *           Enlazado desde el menú («Marcas») y el pie. Al crear la página de
 *           una marca nueva, basta con añadirla a MARCAS.
 *
 * Objetivo del usuario (15-nov-2026): que las marcas quieran aparecer en la
 * web; de ahí el bloque final para las marcas.
 */

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Marcas', 'url' => Router::url('marcas')]
];

$pageTitle = 'Nuestras marcas: Sierra Morena, Espinaler y más | El Celler Nou';
$pageDescription = 'Las marcas que tenemos en El Celler Nou, en Premià de Mar: jamones Sierra Morena, vermut y conservas Espinaler y más, con reparto en el Maresme.';

$marcas = MARCAS;

$items = [];
$pos = 1;
foreach ($marcas as $ruta => $m) {
    $items[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $m['nombre'], 'url' => Router::urlAbs($ruta)];
}
$schemaExtra = schemaBreadcrumb($breadcrumbItems)
    . schemaJson([
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => 'Marcas de El Celler Nou',
        'itemListElement' => $items,
    ]);

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Nuestras marcas</h1>
        <p>Algunas de las casas que tenemos en la tienda, con su historia, su gama y cómo te aconsejamos cada producto. Las tienes en Premià de Mar y te las llevamos a casa en el Maresme.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="marcas-grid">
            <?php foreach ($marcas as $ruta => $m): ?>
            <a href="<?php echo Router::url($ruta); ?>" class="marca-card">
                <img src="<?php echo getBaseUrl() . $m['imagen']; ?>" alt="<?php echo htmlspecialchars($m['alt']); ?>" width="800" height="<?php echo (int) $m['alto']; ?>" loading="lazy" decoding="async">
                <div class="marca-card-texto">
                    <h2><?php echo htmlspecialchars($m['nombre']); ?></h2>
                    <p><?php echo htmlspecialchars($m['texto']); ?></p>
                    <span class="marca-card-link">Ver la marca <svg class="icon" aria-hidden="true"><use href="#icon-arrow-right"></use></svg></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="servicio marcas-pro">
            <h2>¿Eres una marca?</h2>
            <p>Si vendemos tus productos o quieres que estén en El Celler Nou, escríbenos: preparamos tu página con tu historia y tu gama, y te contamos cómo la encuentra la gente del Maresme.</p>
            <p class="servicio-acciones">
                <a href="mailto:<?php echo SITE_EMAIL; ?>?subject=<?php echo rawurlencode('Mi marca en El Celler Nou'); ?>">Escríbenos un correo</a>
            </p>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
