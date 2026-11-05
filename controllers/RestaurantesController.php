<?php
/**
 * El Celler Nou - Restaurantes y hostelería
 *
 * /restaurantes   Carta de vinos y suministro de vino para restaurantes y bares.
 *
 * Confirmado por el usuario (docs/seo/datos-productos-usuario.md, apartado
 * Servicios): les hacemos la carta de vinos (26-oct-2026) y les vendemos el
 * vino, que les llevamos a granel o embotellado, ajustándonos a lo que
 * necesitan (5-nov-2026). Nada de precios, condiciones, plazos, formación ni
 * clientes de ejemplo mientras el usuario no los confirme.
 */

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Servicios', 'url' => Router::url('servicios')],
    ['name' => 'Restaurantes', 'url' => Router::url('restaurantes')]
];

$pageTitle = 'Vino para restaurantes y carta de vinos en el Maresme | El Celler Nou';
$pageDescription = 'Hacemos la carta de vinos de tu restaurante o bar y te servimos el vino, a granel o embotellado, '
    . 'según lo que necesites. Tienda de vinos en Premià de Mar.';

$waRestaurante = 'Hola, tengo un restaurante y me interesa vuestro servicio de vinos.';

$servicios = [
    [
        'titulo' => 'La carta de vinos',
        'texto'  => [
            'Elegimos contigo los vinos que encajan con tu cocina, con tu cliente y con lo que quieres ofrecer: cuántas referencias, de qué estilos y en qué franjas de precio.',
            'Una carta corta y bien pensada vende más que una larga que nadie lee. La revisamos contigo cuando cambias de menú o de temporada.',
        ],
    ],
    [
        'titulo' => 'El vino, a granel o embotellado',
        'texto'  => [
            'Te servimos los vinos de la carta y nos ajustamos a lo que necesitas: vino a granel, por ejemplo para el vino de la casa o el menú del día, o vino embotellado para la carta.',
            'También tenemos cavas, licores y cervezas, por si quieres resolverlo todo con un mismo proveedor.',
        ],
    ],
    [
        'titulo' => 'Te lo llevamos',
        'texto'  => [
            'Llevamos el vino al restaurante con nuestro propio reparto. Si estás fuera del Maresme, pregúntanos y lo vemos.',
            'Y si un día te falta una botella concreta, la tienda está en Premià de Mar: nos llamas o nos escribes por WhatsApp.',
        ],
    ],
];

$faqs = [
    [
        'pregunta'  => '¿Qué hacéis exactamente para un restaurante?',
        'respuesta' => 'Dos cosas: te ayudamos a hacer la carta de vinos, eligiendo contigo las referencias que encajan con tu cocina y tu cliente, y te servimos el vino, a granel o embotellado, según lo que necesites.',
    ],
    [
        'pregunta'  => '¿Puedo comprar vino a granel para el vino de la casa?',
        'respuesta' => 'Sí. Te servimos vino a granel o embotellado y nos ajustamos a lo que necesites. Cuéntanos qué vino buscas y cuánto mueves, y te proponemos opciones.',
    ],
    [
        'pregunta'  => '¿Lleváis el vino al restaurante?',
        'respuesta' => 'Sí, con nuestro propio reparto en el Maresme. Si tu restaurante está fuera, pregúntanos.',
    ],
    [
        'pregunta'  => '¿También trabajáis con bares?',
        'respuesta' => 'Sí: restaurantes, bares y cualquier negocio de hostelería que sirva vino. Escríbenos y lo hablamos.',
    ],
];

$url = rtrim(SITE_URL, '/') . '/restaurantes';
$schemaExtra = schemaBreadcrumb($breadcrumbItems)
    . schemaJson([
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        '@id'         => $url . '#servicio',
        'name'        => 'Carta de vinos y vino para restaurantes',
        'serviceType' => 'Asesoramiento y suministro de vino para hostelería',
        'description' => $pageDescription,
        'provider'    => ['@id' => schemaTiendaId()],
        'areaServed'  => ['@type' => 'AdministrativeArea', 'name' => 'Maresme'],
        'audience'    => ['@type' => 'BusinessAudience', 'audienceType' => 'Restaurantes y bares'],
        'url'         => $url,
    ])
    . schemaFAQ($faqs);

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header domicilio-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Vino para restaurantes: la carta y el vino, en el Maresme</h1>
        <p>Si tienes un restaurante o un bar, te ayudamos a hacer la carta de vinos y te servimos el vino, a granel o embotellado, según lo que necesites. Somos una tienda de vinos de Premià de Mar y te lo llevamos al local.</p>
        <div class="domicilio-cta">
            <a href="<?php echo htmlspecialchars(whatsappUrl($waRestaurante)); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer">
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
        <div class="section-header">
            <h2>Qué hacemos por tu restaurante</h2>
        </div>
        <div class="servicios-lista">
            <?php foreach ($servicios as $s): ?>
            <article class="servicio">
                <h3><?php echo htmlspecialchars($s['titulo']); ?></h3>
                <?php foreach ($s['texto'] as $p): ?>
                <p><?php echo htmlspecialchars($p); ?></p>
                <?php endforeach; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Cómo empezamos</h2>
        </div>
        <ol class="domicilio-pasos">
            <li>
                <h3>Nos cuentas tu restaurante</h3>
                <p>Qué cocina haces, qué cliente tienes y qué quieres ofrecer en vino. Por WhatsApp, por teléfono o pasando por la tienda.</p>
            </li>
            <li>
                <h3>Te proponemos la carta</h3>
                <p>Elegimos contigo las referencias y el formato de cada vino: a granel, embotellado o las dos cosas.</p>
            </li>
            <li>
                <h3>Te servimos el vino</h3>
                <p>Te lo llevamos al local y, cuando cambies el menú o la temporada, revisamos la carta contigo.</p>
            </li>
        </ol>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="servicios-lista">
            <article class="servicio">
                <h2>Vinos de aquí en tu carta</h2>
                <p>El Maresme tiene su propia denominación de origen, la D.O. Alella, con la pansa blanca como uva propia. Muchos clientes agradecen encontrar en la carta un vino hecho a pocos kilómetros de la mesa, y te ayudamos a elegirlo.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo Router::url('vinos.do', ['id' => 33, 'slug' => 'd-o-alella']); ?>">Vinos de la D.O. Alella</a>
                    <a href="<?php echo Router::url('wineworld.articulo', ['cat_slug' => 'guias-de-vino', 'art_slug' => 'guia-vinos-do-alella']); ?>">Guía de los vinos de Alella</a>
                </p>
            </article>
            <article class="servicio">
                <h2>Todo el catálogo</h2>
                <p>Tintos, blancos, rosados, cavas y champagnes, licores y cervezas: puedes ver lo que tenemos en la web y pedirnos lo que no encuentres.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo Router::url('vinos'); ?>">Ver vinos</a>
                    <a href="<?php echo Router::url('cavas'); ?>">Ver cavas</a>
                    <a href="<?php echo Router::url('servicios'); ?>">Todos nuestros servicios</a>
                </p>
            </article>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes de restaurantes'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
