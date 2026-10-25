<?php
/**
 * El Celler Nou - Servicios
 *
 * /servicios   Lo que hace la tienda además de vender: asesoramiento, reparto
 *              propio en el Maresme, envíos al resto de España, catas,
 *              restaurantes, bodas y eventos, y regalos.
 *
 * Los textos salen SOLO de lo que el usuario ha confirmado
 * (docs/seo/datos-productos-usuario.md, apartado Servicios): nada de precios,
 * plazos o condiciones que no estén ahí. Lo que falta por concretar se
 * resuelve con «pregúntanos».
 */

require_once ROOT_PATH . '/includes/poblaciones.php';

// En revisión: fuera de Google, del sitemap y de los menús hasta que el
// usuario dé el visto bueno al texto.
define('SERVICIOS_PUBLICADA', false);

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Servicios', 'url' => Router::url('servicios')]
];

$pageTitle = 'Servicios: asesoramiento, catas, bodas y vino a domicilio | El Celler Nou';
$pageDescription = 'Te aconsejamos en la tienda, por WhatsApp o por teléfono, te llevamos el vino a casa en el Maresme '
    . 'y preparamos catas, regalos y los vinos de restaurantes, bodas y eventos.';
if (!SERVICIOS_PUBLICADA) {
    $pageRobots = 'noindex, follow';
}

$zona1 = getPoblacionesPorZona(1);

// Cada servicio: ancla, título, párrafos y mensaje de WhatsApp ya escrito.
$servicios = [
    [
        'id'      => 'asesoramiento',
        'titulo'  => 'Asesoramiento personal',
        'texto'   => [
            'Elegir un vino no tendría que ser una lotería. Cuéntanos qué vas a comer, para cuántas personas, qué te gusta y cuánto quieres gastar, y te proponemos dos o tres opciones que encajen.',
            'Puedes hacerlo como prefieras: pasando por la tienda, por WhatsApp o por teléfono. Si ya sabes la botella que buscas y no la tenemos, te decimos si podemos conseguirla.',
        ],
        'whatsapp' => 'Hola, me gustaría que me aconsejarais un vino.',
        'enlace'   => null,
    ],
    [
        'id'      => 'domicilio',
        'titulo'  => 'Vino a domicilio en el Maresme, con nuestro reparto',
        'texto'   => [
            'Repartimos con transporte propio desde la tienda de Premià de Mar. En ' . listaNombresPoblaciones($zona1)
                . ' te lo llevamos el mismo día o al siguiente; en el resto del Maresme cercano, en 24-48 horas.',
            'El envío es gratis a partir de ' . SHIPPING_FREE_MIN_ORDER . ' € en Premià y alrededores y a partir de '
                . SHIPPING_FREE_MIN_ORDER_ZONA2 . ' € en el resto del Maresme, y los pedidos a domicilio de '
                . ORDER_DISCOUNT_MIN . ' € o más tienen un ' . ORDER_DISCOUNT_PERCENT . ' % de descuento.',
        ],
        'whatsapp' => 'Hola, quiero hacer un pedido a domicilio.',
        'enlace'   => ['url' => Router::url('domicilio'), 'texto' => 'Poblaciones, plazos y condiciones'],
    ],
    [
        'id'      => 'espana',
        'titulo'  => 'Envíos al resto de España',
        'texto'   => [
            'Si vives fuera del Maresme también podemos enviarte el pedido. Escríbenos con lo que quieres y la dirección, y te decimos el plazo y el coste antes de confirmarlo.',
        ],
        'whatsapp' => 'Hola, quiero hacer un pedido con envío fuera del Maresme.',
        'enlace'   => ['url' => Router::url('legal.envios'), 'texto' => 'Política de envíos'],
    ],
    [
        'id'      => 'catas',
        'titulo'  => 'Catas de vino',
        'texto'   => [
            'Organizamos catas para conocer vinos de una zona, de una uva o de un estilo, probándolos con calma y con alguien que te los explica.',
            'Pregúntanos por las próximas catas.',
        ],
        'whatsapp' => 'Hola, quiero información sobre las catas.',
        'enlace'   => null,
    ],
    [
        'id'      => 'restaurantes',
        'titulo'  => 'Asesoramiento a restaurantes',
        'texto'   => [
            'Asesoramos a restaurantes y negocios de hostelería del Maresme que quieren trabajar el vino: qué referencias tener, cómo encajarlas con la cocina y con el tipo de cliente.',
            'Si tienes un restaurante, escríbenos y lo hablamos.',
        ],
        'whatsapp' => 'Hola, tengo un restaurante y me interesa vuestro asesoramiento.',
        'enlace'   => null,
    ],
    [
        'id'      => 'bodas',
        'titulo'  => 'Bodas, celebraciones y eventos',
        'texto'   => [
            'Una boda, una comunión, una cena de empresa o una fiesta en casa: te ayudamos a elegir los vinos y el cava y a calcular cuánto hace falta según los invitados y el menú.',
            'Sirve para cualquiera que necesite poner vino en una celebración, grande o pequeña.',
        ],
        'whatsapp' => 'Hola, necesito vino para una celebración.',
        'enlace'   => null,
    ],
    [
        'id'      => 'regalos',
        'titulo'  => 'Regalos y lotes',
        'texto'   => [
            'Preparamos regalos a medida: una botella especial o un lote de vino, cava y algún capricho gourmet.',
            'Dinos para quién es y el presupuesto, y lo dejamos listo para recoger o te lo llevamos.',
        ],
        'whatsapp' => 'Hola, quiero preparar un regalo.',
        'enlace'   => null,
    ],
];

$faqs = [
    [
        'pregunta'  => '¿A qué poblaciones repartís con vuestro transporte?',
        'respuesta' => 'Repartimos nosotros mismos en Premià de Mar y en el Maresme: el mismo día o al siguiente en '
            . listaNombresPoblaciones($zona1) . ', y en 24-48 horas en el resto de poblaciones cercanas. '
            . 'Para el resto de España, escríbenos y te decimos el plazo y el coste.',
    ],
    [
        'pregunta'  => '¿Podéis calcular cuánto vino hace falta para una boda o un evento?',
        'respuesta' => 'Sí. Con el número de invitados, el menú y el tipo de celebración te proponemos qué vinos y cava poner y cuántas botellas de cada uno.',
    ],
    [
        'pregunta'  => '¿Cómo me apunto a una cata?',
        'respuesta' => 'Escríbenos por WhatsApp o llámanos y te decimos cuándo es la próxima.',
    ],
];

// Schema: un Service por servicio, todos de la tienda.
$base = rtrim(SITE_URL, '/') . '/servicios';
$grafo = [];
foreach ($servicios as $s) {
    $grafo[] = [
        '@type'       => 'Service',
        '@id'         => $base . '#' . $s['id'],
        'name'        => $s['titulo'],
        'description' => $s['texto'][0],
        'provider'    => ['@id' => schemaTiendaId()],
        'areaServed'  => $s['id'] === 'espana'
            ? ['@type' => 'Country', 'name' => 'España']
            : ['@type' => 'AdministrativeArea', 'name' => 'Maresme'],
        'url'         => $base . '#' . $s['id'],
    ];
}
$schemaExtra = schemaBreadcrumb($breadcrumbItems)
    . schemaJson(['@context' => 'https://schema.org', '@graph' => $grafo])
    . schemaFAQ($faqs);

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header domicilio-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Te aconsejamos, te lo preparamos y te lo llevamos</h1>
        <p>En El Celler Nou no solo vendemos vino: te ayudamos a elegirlo, lo llevamos a casa en todo el Maresme y nos ocupamos de los vinos de tus catas, celebraciones, regalos y de tu restaurante.</p>
        <?php echo renderVentajasPedido(); ?>
        <div class="domicilio-cta">
            <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, quiero hablar con vosotros sobre un vino.')); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer">
                <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                Escríbenos por WhatsApp
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
        <nav class="servicios-indice" aria-label="Servicios">
            <?php foreach ($servicios as $s): ?>
            <a href="#<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['titulo']); ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="servicios-lista">
            <?php foreach ($servicios as $s): ?>
            <article class="servicio" id="<?php echo $s['id']; ?>">
                <h2><?php echo htmlspecialchars($s['titulo']); ?></h2>
                <?php foreach ($s['texto'] as $parrafo): ?>
                <p><?php echo htmlspecialchars($parrafo); ?></p>
                <?php endforeach; ?>
                <p class="servicio-acciones">
                    <a href="<?php echo htmlspecialchars(whatsappUrl($s['whatsapp'])); ?>" target="_blank" rel="noopener noreferrer">
                        <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                        Pregúntanos por WhatsApp
                    </a>
                    <?php if ($s['enlace']): ?>
                    <a href="<?php echo $s['enlace']['url']; ?>"><?php echo htmlspecialchars($s['enlace']['texto']); ?></a>
                    <?php endif; ?>
                </p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Y si prefieres venir</h2>
            <p>Estamos en <?php echo SITE_STREET; ?>, <?php echo SITE_POSTAL_CODE . ' ' . SITE_LOCALITY; ?>. Pasa a ver la tienda y elegimos juntos.</p>
        </div>
        <div class="text-center">
            <a href="<?php echo Router::url('contacto'); ?>" class="btn btn-primary btn-lg">Cómo llegar y horario</a>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes sobre nuestros servicios'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
