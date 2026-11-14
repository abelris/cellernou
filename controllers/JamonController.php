<?php
/**
 * El Celler Nou - Jamón y paleta ibérica
 *
 * /jamon-iberico   Jamones y paletas de la marca Sierra Morena (Hinojosa del
 *                  Duque, valle de Los Pedroches, Córdoba).
 *
 * Página fija todo el año y destacada en Navidad (portada, lotes, ficha de
 * Google): quien busca «comprar paleta ibérica» quiere una página de tienda, y
 * una página que solo existiera en Navidad perdería cada año lo ganado.
 *
 * Confirmado por el usuario (14-nov-2026): venden jamones y paletas ibéricas y
 * serranas/Duroc de Sierra Morena, y cualquier pieza de la marca por encargo;
 * en pieza entera, en sobres loncheados y dentro de los lotes de Navidad.
 * NO cortan a cuchillo. Sin precios en la web (cambian con el peso y la añada).
 * Gama y curaciones: catálogo de jamonessierramorena.com leído el 14-nov-2026
 * (mínimos que da la marca en cada ficha). Los textos son propios.
 */

require_once ROOT_PATH . '/includes/poblaciones.php';

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Jamón ibérico', 'url' => Router::url('jamon')]
];

$pageTitle = 'Jamón y paleta ibérica en Premià de Mar | Sierra Morena';
$pageDescription = 'Jamón y paleta ibérica Sierra Morena, de Los Pedroches: de bellota, de cebo de campo y serranos. '
    . 'Pieza entera, sobres loncheados y lotes de Navidad, con reparto en el Maresme.';

// La gama de la marca, de más a menos ibérico. Curación: mínimo que indica
// Sierra Morena en la ficha de cada pieza.
$gama = [
    [
        'titulo' => 'De bellota 100 % ibérico',
        'texto'  => 'Cerdo de raza ibérica pura criado en libertad en la dehesa y engordado con bellota durante la montanera. Es la pieza de más categoría: grasa que se funde en la boca y un sabor largo.',
        'piezas' => ['Jamón: curación mínima de 30 meses', 'Paleta: de 18 a 24 meses'],
    ],
    [
        'titulo' => 'De bellota ibérico, D.O.P. Los Pedroches',
        'texto'  => 'Jamón y paleta de bellota con el sello de la Denominación de Origen Protegida Los Pedroches, la dehesa del norte de Córdoba donde está la casa.',
        'piezas' => ['Jamón: curación mínima de 30 meses', 'Paleta: 18 meses'],
    ],
    [
        'titulo' => 'De cebo de campo ibérico (50 % raza ibérica)',
        'texto'  => 'Cerdos que viven en el campo y comen pastos, pienso y cereales. Un ibérico muy completo para el día a día y para compartir en las fiestas.',
        'piezas' => ['Jamón: 24 meses', 'Paleta: 16 meses'],
    ],
    [
        'titulo' => 'De cebo ibérico (50 % raza ibérica)',
        'texto'  => 'El ibérico de entrada: alimentado con pienso y cereales, con el sabor y la veta del cerdo ibérico a un precio más asequible.',
        'piezas' => ['Jamón: 24 meses', 'Paleta: 16 meses'],
    ],
    [
        'titulo' => 'Series Platino y Azabache',
        'texto'  => 'Las dos líneas de alta selección de la casa, pensadas también para regalar.',
        'piezas' => ['Jamón: 24 meses', 'Paleta: 16 meses'],
    ],
    [
        'titulo' => 'Serranos y Duroc',
        'texto'  => 'Jamón serrano de cerdo blanco en tres curaciones (de bodega, reserva y gran reserva) y la línea de raza Duroc, más jugosa y con más infiltración de grasa.',
        'piezas' => ['Jamón serrano de bodega y reserva: 14 meses', 'Gran reserva y gran reserva Duroc: 16 meses', 'Paleta serrana Duroc'],
    ],
];

$faqs = [
    [
        'pregunta'  => '¿Qué diferencia hay entre jamón y paleta (paletilla)?',
        'respuesta' => 'El jamón es la pata trasera del cerdo y la paleta o paletilla, la delantera. La paleta es más pequeña, se cura menos tiempo y tiene un sabor más intenso y algo más de grasa; el jamón da más lonchas y es más uniforme. Para una familia en Navidad, una paleta suele bastar; para muchas comidas o para regalar, el jamón.',
    ],
    [
        'pregunta'  => '¿Qué significan los colores del precinto?',
        'respuesta' => 'La norma de calidad del ibérico obliga a precintar cada pieza con un color: negro para el de bellota 100 % ibérico, rojo para el de bellota ibérico, verde para el de cebo de campo ibérico y blanco para el de cebo ibérico. Solo el de bellota 100 % ibérico puede llamarse «pata negra».',
    ],
    [
        'pregunta'  => '¿Lo vendéis cortado?',
        'respuesta' => 'Tenemos sobres de ibérico loncheado, listos para abrir y servir. Las piezas enteras se venden con hueso, para cortarlas en casa.',
    ],
    [
        'pregunta'  => '¿Podéis conseguir una pieza que no tengáis en la tienda?',
        'respuesta' => 'Sí. Trabajamos con Sierra Morena y te encargamos cualquier jamón o paleta de su gama. Escríbenos qué buscas y te decimos cuándo lo tendríamos.',
    ],
    [
        'pregunta'  => '¿Me lo podéis llevar a casa o meterlo en un lote?',
        'respuesta' => 'Sí. Lo recoges en la tienda de Premià de Mar o te lo llevamos con nuestro reparto en el Maresme. Y si es para regalar, lo ponemos en un lote de Navidad con vino o cava, envuelto sin coste.',
    ],
    [
        'pregunta'  => '¿Cuánto cuesta?',
        'respuesta' => 'Depende del tipo y del peso de cada pieza, por eso no publicamos precios. Pregúntanos por WhatsApp, por teléfono o en la tienda y te decimos qué tenemos y a qué precio.',
    ],
];

$url = rtrim(SITE_URL, '/') . '/jamon-iberico';
$schemaExtra = schemaBreadcrumb($breadcrumbItems)
    . schemaJson([
        '@context'    => 'https://schema.org',
        '@type'       => 'WebPage',
        '@id'         => $url . '#pagina',
        'url'         => $url,
        'name'        => 'Jamón y paleta ibérica Sierra Morena en Premià de Mar',
        'description' => $pageDescription,
        'about'       => ['@type' => 'Brand', 'name' => 'Sierra Morena'],
        'provider'    => ['@id' => schemaTiendaId()],
    ])
    . schemaFAQ($faqs);

// En Navidad (1-nov a 6-ene) la página lo dice arriba, igual que la portada.
$mesHoy = (int) date('n');
$esNavidad = $mesHoy >= 11 || ($mesHoy === 1 && (int) date('j') <= 6);

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header domicilio-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Jamón y paleta ibérica Sierra Morena en Premià de Mar</h1>
        <p>Jamones y paletas de Sierra Morena, una casa familiar de Hinojosa del Duque, en la dehesa de Los Pedroches (Córdoba), con más de tres generaciones haciendo ibérico. De bellota, de cebo de campo y serranos: en pieza entera, en sobres loncheados o dentro de un lote de Navidad.</p>
        <?php if ($esNavidad): ?>
        <p><strong>Para Navidad, encárgalo con tiempo:</strong> te guardamos la pieza o la pedimos a la casa si no la tenemos en la tienda.</p>
        <?php endif; ?>
        <div class="domicilio-cta">
            <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, quiero información sobre jamón o paleta ibérica.')); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer">
                <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                Pregúntanos por WhatsApp
            </a>
            <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>" class="btn btn-outline btn-lg">
                <svg class="icon" aria-hidden="true"><use href="#icon-phone"></use></svg>
                Llamar al <?php echo SITE_PHONE; ?>
            </a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>La gama de Sierra Morena</h2>
            <p>Tenemos jamones y paletas en la tienda y te encargamos cualquier pieza de la marca. Pregúntanos qué hay hoy y te aconsejamos cuál encaja con lo que buscas.</p>
        </div>
        <div class="servicios-lista">
            <?php foreach ($gama as $g): ?>
            <article class="servicio">
                <h3><?php echo htmlspecialchars($g['titulo']); ?></h3>
                <p><?php echo htmlspecialchars($g['texto']); ?></p>
                <ul>
                    <?php foreach ($g['piezas'] as $pieza): ?>
                    <li><?php echo htmlspecialchars($pieza); ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Cómo lo puedes llevar</h2>
        </div>
        <ol class="domicilio-pasos">
            <li>
                <h3>Pieza entera</h3>
                <p>Jamón o paleta con hueso, para cortar en casa. Si no tenemos la que quieres, la pedimos a Sierra Morena.</p>
            </li>
            <li>
                <h3>Sobres loncheados</h3>
                <p>Ibérico ya loncheado y envasado, para abrir y servir: para el aperitivo o para quien no quiere una pieza entera.</p>
            </li>
            <li>
                <h3>En un lote de Navidad</h3>
                <p>Con un vino, un cava o un licor, al presupuesto que nos digas y envuelto sin coste. También para regalos de empresa con factura.</p>
            </li>
        </ol>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="servicios-lista">
            <article class="servicio">
                <h2>Qué vino poner con el jamón</h2>
                <p>Con el ibérico de bellota funcionan muy bien un fino o una manzanilla, un cava brut nature y los tintos jóvenes y frescos, que no tapan la grasa. Pregúntanos y te proponemos la botella.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo Router::url('cavas.tipo', ['id' => 22, 'slug' => 'cava']); ?>">Ver cavas</a>
                    <a href="<?php echo Router::url('vinos.tipo', ['id' => 1, 'slug' => 'vino-tinto']); ?>">Ver vinos tintos</a>
                    <a href="<?php echo Router::url('wineworld.articulo', ['cat_slug' => 'maridajes', 'art_slug' => 'que-vino-servir-comidas-navidad']); ?>">Qué vino servir en las comidas de Navidad</a>
                </p>
            </article>
            <article class="servicio">
                <h2>Para regalar</h2>
                <p>Un jamón o una paleta es de los regalos de Navidad que más se agradecen. Lo preparamos solo o en un lote, para particulares o para empresas, y te lo llevamos en el Maresme.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo Router::url('lotes'); ?>">Lotes de Navidad y regalos de empresa</a>
                    <a href="<?php echo Router::url('domicilio'); ?>">Dónde repartimos</a>
                </p>
            </article>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes sobre el jamón y la paleta'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
