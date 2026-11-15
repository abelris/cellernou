<?php
/**
 * El Celler Nou - Jamones Sierra Morena
 *
 * /jamones-sierra-morena   Página de la marca: jamones y paletas Sierra Morena
 *                          (Hinojosa del Duque, valle de Los Pedroches, Córdoba).
 * /jamon-iberico           URL anterior (14-nov-2026): redirige aquí con 301.
 *
 * Busca a la vez la marca («jamones Sierra Morena», «jamón Sierra Morena
 * dónde comprar») y el jamón ibérico en Premià y el Maresme. Fija todo el año y
 * destacada en Navidad. El artículo del blog sobre el Mundial del Jamón y Los
 * Pedroches es informativo y enlaza aquí: no compiten.
 *
 * Confirmado por el usuario (14-nov-2026): venden jamones y paletas ibéricas y
 * serranas/Duroc de Sierra Morena, y cualquier pieza de la marca por encargo;
 * en pieza entera, en sobres loncheados y dentro de los lotes de Navidad.
 * NO cortan a cuchillo. Sin precios en la web (cambian con el peso y la añada).
 * 15-nov-2026: el usuario dice que la marca le permite usar su web; fotos de
 * producto, banner de la dehesa y distintivo del premio en images/sierra-morena
 * (de jamonessierramorena.com). Los textos son propios.
 *
 * Premio (comprobado en prensa, 15-nov-2026): I Mundial del Jamón, Ronda, 4-6
 * de septiembre de 2026; lo ganó el Valle de Los Pedroches (D.O.P.), al que
 * representaron Jamones Sierra Morena e Ibedul (grupo Hnos. Rodríguez
 * Barbancho); votó el público. Se dice así: el premio es de la zona, no de una
 * pieza concreta.
 */

require_once ROOT_PATH . '/includes/poblaciones.php';

// URL antigua: 301 a la nueva (la página nació el 14-nov como /jamon-iberico).
if (strpos(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/jamon-iberico') === 0) {
    Router::redirect(Router::url('jamon'), 301);
}

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Jamones Sierra Morena', 'url' => Router::url('jamon')]
];

$pageTitle = 'Jamones Sierra Morena en Premià de Mar: jamón ibérico';
$pageDescription = 'Jamones y paletas Sierra Morena, de Los Pedroches, la zona ganadora del I Mundial del Jamón 2026. '
    . 'Pieza entera, loncheado y lotes, con reparto en el Maresme.';

$premioFuente = 'https://www.vinetur.com/20260910106915/valle-de-los-pedroches-gana-en-ronda-el-titulo-de-mejor-jamon-del-mundo.html';

// Las piezas de la marca. Curación: mínimo que indica Sierra Morena en la ficha
// de cada una (si su web se contradice, no se pone). Precinto: el que obliga la
// norma de calidad del ibérico según el tipo (solo donde el tipo es inequívoco).
$piezas = [
    'Jamones' => [
        ['img' => 'jamon-bellota-100-iberico', 'nombre' => 'Jamón de bellota 100 % ibérico', 'detalle' => 'Raza ibérica pura y montanera en la dehesa. Precinto negro.', 'curacion' => '30 meses'],
        ['img' => 'jamon-bellota-iberico-dop-los-pedroches', 'nombre' => 'Jamón de bellota ibérico D.O.P. Los Pedroches', 'detalle' => 'Con el sello de la Denominación de Origen Protegida de la zona.', 'curacion' => '30 meses'],
        ['img' => 'jamon-cebo-campo-iberico', 'nombre' => 'Jamón de cebo de campo ibérico 50 %', 'detalle' => 'Cerdos en el campo, con pastos, pienso y cereales. Precinto verde.', 'curacion' => '24 meses'],
        ['img' => 'jamon-cebo-iberico', 'nombre' => 'Jamón de cebo ibérico 50 %', 'detalle' => 'El ibérico de cada día, con pienso y cereales. Precinto blanco.', 'curacion' => '24 meses'],
        ['img' => 'jamon-serie-platino', 'nombre' => 'Jamón Serie Platino', 'detalle' => 'Línea de alta selección de la casa.', 'curacion' => '24 meses'],
        ['img' => 'jamon-serie-azabache', 'nombre' => 'Jamón Serie Azabache', 'detalle' => 'Línea de alta selección de la casa, pensada también para regalar.', 'curacion' => '24 meses'],
        ['img' => 'jamon-serrano-gran-reserva-duroc', 'nombre' => 'Jamón serrano gran reserva Duroc', 'detalle' => 'Cerdo blanco de raza Duroc, más jugoso y con más grasa infiltrada.', 'curacion' => '16 meses'],
    ],
    'Paletas' => [
        ['img' => 'paleta-bellota-100-iberica', 'nombre' => 'Paleta de bellota 100 % ibérica', 'detalle' => 'Raza ibérica pura y montanera. Precinto negro.', 'curacion' => '18 a 24 meses'],
        ['img' => 'paleta-bellota-iberica-dop-los-pedroches', 'nombre' => 'Paleta de bellota ibérica D.O.P. Los Pedroches', 'detalle' => 'Con el sello de la Denominación de Origen Protegida.', 'curacion' => '18 meses'],
        ['img' => 'paleta-cebo-campo-iberica', 'nombre' => 'Paleta de cebo de campo ibérica 50 %', 'detalle' => 'Pastos, pienso y cereales. Precinto verde.', 'curacion' => '16 meses'],
        ['img' => 'paleta-serie-platino', 'nombre' => 'Paleta Serie Platino', 'detalle' => 'Alta selección de la casa.', 'curacion' => '16 meses'],
        ['img' => 'paleta-serrana-duroc', 'nombre' => 'Paleta serrana Duroc', 'detalle' => 'Cerdo blanco de raza Duroc: la paleta más asequible de la gama.', 'curacion' => null],
    ],
];

$faqs = [
    [
        'pregunta'  => '¿Dónde comprar jamón Sierra Morena en el Maresme?',
        'respuesta' => 'En El Celler Nou, en la calle Ramón Llull, 15, de Premià de Mar. Tenemos jamones y paletas Sierra Morena en la tienda, te encargamos cualquier pieza de la marca y te lo llevamos a casa con nuestro reparto en el Maresme.',
    ],
    [
        'pregunta'  => '¿Qué premio ha ganado Sierra Morena?',
        'respuesta' => 'En el I Mundial del Jamón, celebrado en Ronda en septiembre de 2026, ganó el Valle de Los Pedroches, la zona de la D.O.P. a la que pertenece Sierra Morena. Las piezas que representaron a Los Pedroches fueron las de Jamones Sierra Morena y su marca hermana Ibedul, y el ganador lo eligió el público en una cata frente a Guijuelo, Jabugo y Extremadura.',
    ],
    [
        'pregunta'  => '¿De dónde son los jamones Sierra Morena?',
        'respuesta' => 'De Hinojosa del Duque, en el Valle de Los Pedroches, al norte de Córdoba. Es una casa familiar con más de tres generaciones haciendo ibérico en la dehesa de la D.O.P. Los Pedroches.',
    ],
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
        'pregunta'  => '¿Cuánto cuesta un jamón Sierra Morena?',
        'respuesta' => 'Depende del tipo y del peso de cada pieza, por eso no publicamos precios. Pregúntanos por WhatsApp, por teléfono o en la tienda y te decimos qué tenemos y a qué precio.',
    ],
];

$url = rtrim(SITE_URL, '/') . '/jamones-sierra-morena';
$schemaExtra = schemaBreadcrumb($breadcrumbItems)
    . schemaJson([
        '@context'    => 'https://schema.org',
        '@type'       => 'WebPage',
        '@id'         => $url . '#pagina',
        'url'         => $url,
        'name'        => 'Jamones Sierra Morena en Premià de Mar',
        'description' => $pageDescription,
        'about'       => [
            '@type'  => 'Brand',
            'name'   => 'Sierra Morena',
            'url'    => 'https://jamonessierramorena.com/',
            'logo'   => schemaUrl('/images/sierra-morena/logotipo-sierra-morena.webp'),
        ],
        'mentions'    => [
            '@type' => 'Event',
            'name'  => 'I Mundial del Jamón',
            'startDate' => '2026-09-04',
            'endDate'   => '2026-09-06',
            'location'  => ['@type' => 'Place', 'name' => 'Convento de Santo Domingo, Ronda'],
        ],
        'provider'    => ['@id' => schemaTiendaId()],
    ])
    . schemaFAQ($faqs);

// En Navidad (1-nov a 6-ene) la página lo dice arriba, igual que la portada.
$mesHoy = (int) date('n');
$esNavidad = $mesHoy >= 11 || ($mesHoy === 1 && (int) date('j') <= 6);

// Imagen para compartir en redes (1200x630, images/paginas).
$pageImage = getBaseUrl() . '/images/paginas/og-jamon.jpg';
$pageImageWidth = 1200;
$pageImageHeight = 630;

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header domicilio-header cabecera-foto">
    <?php echo cabeceraFotoHtml('/images/home/navidad-jamon', 'Lonchas de jamón ibérico sobre una tabla junto a una copa de vino tinto', [800, 1400, 1920], 2 / 3); ?>
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <img src="<?php echo getBaseUrl(); ?>/images/sierra-morena/distintivo-mundial-jamon-2026.webp" alt="Sierra Morena, Mundial del Jamón 2026" width="400" height="115" class="sm-distintivo">
        <h1>Jamones Sierra Morena en Premià de Mar</h1>
        <p>Jamones y paletas ibéricas de Sierra Morena, una casa familiar de Hinojosa del Duque, en la dehesa de Los Pedroches (Córdoba), la zona que ganó el <strong>I Mundial del Jamón</strong> en 2026. De bellota, de cebo de campo y serranos: en pieza entera, en sobres loncheados o dentro de un lote de Navidad.</p>
        <?php if ($esNavidad): ?>
        <p><strong>Para Navidad, encárgalo con tiempo:</strong> te guardamos la pieza o la pedimos a la casa si no la tenemos en la tienda.</p>
        <?php endif; ?>
        <div class="domicilio-cta">
            <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, quiero información sobre los jamones Sierra Morena.')); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer">
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

<section class="section sm-premio">
    <div class="container sm-premio-grid">
        <div>
            <span class="sm-premio-etiqueta">I Mundial del Jamón · Ronda, 2026</span>
            <h2>Los Pedroches, la zona ganadora del Mundial del Jamón</h2>
            <p>Del 4 al 6 de septiembre de 2026, el Convento de Santo Domingo de Ronda acogió el primer Mundial del Jamón: cuatro grandes zonas de ibérico, Guijuelo, Jabugo, Extremadura y Los Pedroches, frente a frente en una cata en la que votó el público. Ganó el <strong>Valle de Los Pedroches</strong>, y las piezas que lo representaron fueron las de <strong>Jamones Sierra Morena</strong> y su marca hermana Ibedul.</p>
            <p>Es el jamón que tenemos en El Celler Nou. Ven a probarlo o pregúntanos qué pieza encaja con lo que buscas. Y si quieres saber más, te lo contamos en el blog: <a href="<?php echo Router::url('wineworld.articulo', ['cat_slug' => 'cultura-y-tradicion', 'art_slug' => 'mundial-del-jamon-los-pedroches-sierra-morena']); ?>">el Mundial del Jamón y el ibérico de Los Pedroches</a>.</p>
            <p class="sm-premio-fuente">Fuente: <a href="<?php echo $premioFuente; ?>" target="_blank" rel="noopener">Vinetur, 10 de septiembre de 2026</a></p>
        </div>
        <img src="<?php echo getBaseUrl(); ?>/images/sierra-morena/distintivo-mundial-jamon-2026.webp" alt="Distintivo de Sierra Morena del Mundial del Jamón 2026" width="400" height="115" loading="lazy" class="sm-premio-img">
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Los jamones y paletas de Sierra Morena</h2>
            <p>Tenemos jamones y paletas en la tienda y te encargamos cualquier pieza de la marca. Pregúntanos qué hay hoy y te aconsejamos cuál encaja con lo que buscas.</p>
        </div>
        <?php foreach ($piezas as $grupo => $lista): ?>
        <h3 class="sm-grupo"><?php echo $grupo; ?></h3>
        <div class="sm-piezas">
            <?php foreach ($lista as $p): ?>
            <article class="sm-pieza">
                <img src="<?php echo getBaseUrl(); ?>/images/sierra-morena/<?php echo $p['img']; ?>-300.webp"
                     srcset="<?php echo getBaseUrl(); ?>/images/sierra-morena/<?php echo $p['img']; ?>-300.webp 300w, <?php echo getBaseUrl(); ?>/images/sierra-morena/<?php echo $p['img']; ?>-600.webp 600w"
                     sizes="(max-width: 600px) 45vw, 240px" width="300" height="300" loading="lazy" decoding="async"
                     alt="<?php echo htmlspecialchars($p['nombre'] . ' Sierra Morena'); ?>">
                <h4><?php echo htmlspecialchars($p['nombre']); ?></h4>
                <p><?php echo htmlspecialchars($p['detalle']); ?></p>
                <?php if ($p['curacion']): ?>
                <p class="sm-curacion">Curación mínima: <?php echo $p['curacion']; ?></p>
                <?php endif; ?>
                <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, me interesa el ' . mb_strtolower($p['nombre']) . ' Sierra Morena. ¿Lo tenéis?')); ?>" target="_blank" rel="noopener noreferrer" class="sm-pieza-cta">
                    <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg> Pregúntanos
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <p class="sm-nota">También hay jamón serrano de bodega, reserva y gran reserva. Las fotos de las piezas son de Jamones Sierra Morena.</p>
    </div>
</section>

<section class="sm-dehesa">
    <img src="<?php echo getBaseUrl(); ?>/images/sierra-morena/dehesa-los-pedroches-1920.webp"
         srcset="<?php echo getBaseUrl(); ?>/images/sierra-morena/dehesa-los-pedroches-960.webp 960w, <?php echo getBaseUrl(); ?>/images/sierra-morena/dehesa-los-pedroches-1920.webp 1920w"
         sizes="100vw" width="1920" height="298" loading="lazy" decoding="async" alt="La dehesa del Valle de Los Pedroches, en Córdoba">
    <div class="container">
        <div class="sm-dehesa-texto">
            <img src="<?php echo getBaseUrl(); ?>/images/sierra-morena/logotipo-sierra-morena.webp" alt="Jamones Sierra Morena" width="237" height="43" loading="lazy" class="sm-logo">
            <h2>De la dehesa de Los Pedroches</h2>
            <p>Sierra Morena es una casa familiar de Hinojosa del Duque, en el norte de Córdoba, con más de tres generaciones haciendo ibérico. Sus cerdos se crían en la dehesa de encinas del Valle de Los Pedroches, y los de bellota se engordan en libertad durante la montanera. La sal marina y el tiempo en bodega hacen el resto.</p>
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
                <p>Un jamón o una paleta Sierra Morena es de los regalos de Navidad que más se agradecen. Lo preparamos solo o en un lote, para particulares o para empresas, y te lo llevamos en el Maresme.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo Router::url('lotes'); ?>">Lotes de Navidad y regalos de empresa</a>
                    <a href="<?php echo Router::url('domicilio'); ?>">Dónde repartimos</a>
                </p>
            </article>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes sobre los jamones Sierra Morena'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
