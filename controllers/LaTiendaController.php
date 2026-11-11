<?php
/**
 * El Celler Nou - La tienda (quiénes somos)
 *
 * /la-tienda   Historia, quién está al frente, el vino a granel, cómo elegimos
 *              los vinos, el micro abierto y Premià como pueblo de vino.
 *
 * Datos confirmados por el usuario el 11-nov-2026 (respuestas y un reportaje
 * de prensa que aportó; el texto es propio, el reportaje no se copia):
 *  - abrió en 1995 (la portada dice «Desde 1995»; el reportaje decía 1994 y el
 *    usuario confirmó 1995), en otro local de Premià de Mar;
 *  - hoy en C/ Ramón Llull 15, que antes fue un taller mecánico;
 *  - Victoria Ris lleva seis años al frente (no se pone año de inicio);
 *  - botas de vino a granel en la entrada; vinos de bodegas familiares;
 *    buena selección de Alella, algunos de muy poca producción;
 *  - micro abierto un jueves al mes (el usuario confirmó el 11-nov-2026 que
 *    sigue; sin fecha fija publicada: «pregúntanos cuándo es el próximo»).
 * Fotos: images/tienda/ (del usuario, sin metadatos). La de las botas es de
 * 2015 y se ven precios de entonces escritos con tiza.
 */

$aniosTienda = (int) date('Y') - 1995;

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'La tienda', 'url' => Router::url('la-tienda')]
];

$pageTitle = 'La tienda: vinos en Premià de Mar desde 1995 | El Celler Nou';
$pageDescription = 'Desde 1995 en Premià de Mar: vino a granel de la bota, vinos de bodegas familiares y de la D.O. Alella, '
    . 'Victoria Ris al frente y un micro abierto un jueves al mes. Ven a conocernos.';
$pageImage = getBaseUrl() . '/images/tienda/interior-celler-nou-premia.jpg';
$pageImageWidth = 1400;
$pageImageHeight = 788;

$faqs = [
    [
        'pregunta'  => '¿Desde cuándo existe El Celler Nou?',
        'respuesta' => 'Desde 1995. Empezó en otro local de Premià de Mar y hoy está en ' . SITE_STREET . ', en el centro del pueblo.',
    ],
    [
        'pregunta'  => '¿Se puede comprar vino a granel?',
        'respuesta' => 'Sí. En la entrada de la tienda tenemos las botas del vino a granel y te lo servimos por litros o en garrafa. '
            . 'También lo servimos a restaurantes y bares.',
    ],
    [
        'pregunta'  => '¿Qué es el micro abierto de El Celler Nou?',
        'respuesta' => 'Un jueves al mes la tienda se convierte en escenario: quien quiera puede cantar, recitar o hacer su número. '
            . 'Pregúntanos por WhatsApp o en la tienda cuándo es el próximo.',
    ],
    [
        'pregunta'  => '¿Qué tipo de vinos tenéis?',
        'respuesta' => 'Sobre todo vinos de bodegas familiares, con una buena selección de la D.O. Alella, la de nuestra comarca, '
            . 'y de las principales denominaciones de Cataluña y del resto de España. También cavas, licores, cervezas y conservas.',
    ],
];

$schemaPagina = [
    '@context'   => 'https://schema.org',
    '@type'      => 'AboutPage',
    '@id'        => rtrim(SITE_URL, '/') . '/la-tienda',
    'url'        => rtrim(SITE_URL, '/') . '/la-tienda',
    'name'       => 'La tienda',
    'inLanguage' => 'es',
    'about'      => ['@id' => schemaTiendaId()],
    'mainEntity' => ['@id' => schemaTiendaId()],
    'image'      => schemaUrl('/images/tienda/interior-celler-nou-premia.jpg'),
];
$schemaExtra = schemaBreadcrumb($breadcrumbItems)
    . schemaJson($schemaPagina)
    . schemaFAQ($faqs);

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header domicilio-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Tu tienda de vinos en Premià de Mar desde 1995</h1>
        <p>Una tienda de las de siempre: botas de vino a granel en la entrada, estanterías llenas de vinos de bodegas familiares y alguien que te pregunta qué vas a comer antes de recomendarte una botella.</p>
    </div>
</section>

<section class="section">
    <div class="container tienda">
        <figure class="tienda-foto tienda-foto-ancha">
            <img src="<?php echo getBaseUrl(); ?>/images/tienda/interior-celler-nou-premia.jpg"
                 alt="Interior de El Celler Nou en Premià de Mar: estanterías de vino, botas y mesas altas"
                 width="1400" height="788" loading="eager" decoding="async">
        </figure>

        <div class="tienda-texto">
            <h2><?php echo $aniosTienda; ?> años de vino en Premià</h2>
            <p>El Celler Nou abrió en 1995 en otro local de Premià de Mar. Hoy está en el número 15 de la calle Ramón Llull, en lo que antes fue un taller mecánico. Entre el local y las botas de la entrada, quien viene por primera vez suele pensar que la tienda ha estado aquí toda la vida.</p>
            <p>Al frente está <strong>Victoria Ris</strong>, que lleva seis años llevando la tienda. Es quien te atiende, quien elige los vinos y quien firma los artículos de <a href="<?php echo Router::url('wineworld'); ?>">nuestro blog</a>: lo que cuenta allí es lo mismo que explica cada día en el mostrador.</p>

            <h2>Vino a granel, de la bota</h2>
            <p>Nada más entrar están las botas del vino a granel. Te lo servimos por litros o en garrafa, como se ha comprado el vino de cada día en Cataluña toda la vida, y también lo llevamos a restaurantes y bares que lo quieren para el vino de la casa.</p>
            <p><a href="<?php echo Router::url('vinos.do', ['id' => 28, 'slug' => 'granel']); ?>">Ver el vino a granel</a> · <a href="<?php echo Router::url('restaurantes'); ?>">Vino para restaurantes</a></p>
        </div>

        <figure class="tienda-foto">
            <img src="<?php echo getBaseUrl(); ?>/images/tienda/botas-vino-granel-celler-nou.jpg"
                 alt="Las botas del vino a granel de El Celler Nou, con el nombre de cada vino escrito con tiza"
                 width="1400" height="788" loading="lazy" decoding="async">
            <figcaption>Las botas del vino a granel, en la entrada de la tienda.</figcaption>
        </figure>

        <div class="tienda-texto">
            <h2>Vinos de bodegas familiares</h2>
            <p>«Trabajamos vinos de bodegas familiares», dice Victoria, y es lo que nos diferencia de un supermercado: botellas de gente que hace el vino ella misma, muchas veces en cantidades pequeñas, y que no encontrarás en cualquier lineal.</p>
            <p>Tenemos una buena selección de la <a href="<?php echo Router::url('vinos.do', ['id' => 33, 'slug' => 'd-o-alella']); ?>">D.O. Alella</a>, la de nuestra comarca, con algunos vinos de muy poca producción, y de las principales denominaciones de Cataluña y del resto de España. Si quieres empezar por los de aquí, en la <a href="<?php echo Router::url('wineworld.articulo', ['cat_slug' => 'guias-de-vino', 'art_slug' => 'guia-vinos-do-alella']); ?>">guía de los vinos de la D.O. Alella</a> te contamos por dónde.</p>

            <h2>Un jueves al mes, micro abierto</h2>
            <p>Una vez al mes, un jueves, la tienda se convierte en escenario. Hacemos un micro abierto en el que cualquiera puede salir a cantar, recitar, hacer un número de payasos o lo que se le ocurra. Hay nivel, y lo pasamos en grande. Si quieres venir a verlo o actuar, pregúntanos cuándo es el próximo.</p>
            <p class="servicio-acciones">
                <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, ¿cuándo es el próximo micro abierto?')); ?>" target="_blank" rel="noopener noreferrer">
                    <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                    ¿Cuándo es el próximo?
                </a>
                <a href="<?php echo Router::url('catas'); ?>">Y nuestras catas de vino</a>
            </p>

            <h2>Premià de Mar, un pueblo de vino</h2>
            <p>La historia de Premià va unida al vino. A pocos minutos a pie de la tienda, el Museu Romà de Premià de Mar enseña los restos de una villa romana donde ya se hacía vino hace dos mil años. Hoy en el término municipal ya no quedan viñas, pero Premià de Mar sigue siendo uno de los municipios de la D.O. Alella.</p>
        </div>

        <figure class="tienda-foto tienda-foto-fachada">
            <img src="<?php echo getBaseUrl(); ?>/images/tienda/fachada-celler-nou-premia.jpg"
                 alt="Fachada de El Celler Nou en la calle Ramón Llull, 15, de Premià de Mar"
                 width="384" height="438" loading="lazy" decoding="async">
            <figcaption><?php echo SITE_STREET; ?>, <?php echo SITE_POSTAL_CODE . ' ' . SITE_LOCALITY; ?>.</figcaption>
        </figure>

        <div class="tienda-texto">
            <h2>Y además de vender vino</h2>
            <p>Te aconsejamos en la tienda, por WhatsApp o por teléfono; te llevamos el pedido a casa en todo el Maresme con nuestro propio reparto; preparamos lotes y regalos, también para empresas; organizamos catas y hacemos la carta de vinos de restaurantes.</p>
            <p class="servicio-acciones">
                <a href="<?php echo Router::url('servicios'); ?>">Todos los servicios</a>
                <a href="<?php echo Router::url('domicilio'); ?>">Vino a domicilio</a>
                <a href="<?php echo Router::url('lotes'); ?>">Lotes y regalos</a>
            </p>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Ven a conocernos</h2>
            <p>Estamos en <?php echo SITE_STREET; ?>, <?php echo SITE_POSTAL_CODE . ' ' . SITE_LOCALITY; ?>. Pasa a ver la tienda y elegimos juntos.</p>
        </div>
        <div class="domicilio-cta" style="justify-content: center;">
            <a href="<?php echo Router::url('contacto'); ?>" class="btn btn-primary btn-lg">Cómo llegar y horario</a>
            <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, tengo una pregunta.')); ?>" class="btn btn-outline btn-lg" target="_blank" rel="noopener noreferrer">
                <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                Escríbenos por WhatsApp
            </a>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes sobre la tienda'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
