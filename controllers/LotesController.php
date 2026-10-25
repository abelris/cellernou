<?php
/**
 * El Celler Nou - Lotes y regalos
 *
 * /lotes-y-regalos   Lotes de Navidad y regalos de vino a medida, para
 *                    particulares y empresas.
 *
 * Confirmado por el usuario (26-oct-2026, docs/seo/datos-productos-usuario.md):
 * los lotes se montan a medida (sin lotes cerrados ni precios en la web),
 * regalos de empresa con factura y entrega en la oficina en el Maresme,
 * envoltorio gratis y sin fecha límite de pedido publicada.
 * Esta página absorbe el artículo previsto «Lotes de Navidad a domicilio»
 * para que no compitan.
 */

require_once ROOT_PATH . '/includes/poblaciones.php';

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Servicios', 'url' => Router::url('servicios')],
    ['name' => 'Lotes y regalos', 'url' => Router::url('lotes')]
];

$pageTitle = 'Lotes de Navidad y regalos de vino para empresas | El Celler Nou';
$pageDescription = 'Lotes de vino, cava y licores a tu medida y con el presupuesto que digas: los envolvemos gratis '
    . 'y te los llevamos en el Maresme. También regalos de empresa con factura.';

// Ideas para empezar: el lote final se monta con quien lo encarga.
$ideas = [
    [
        'titulo' => 'Para quien disfruta de un buen tinto',
        'texto'  => 'Un Rioja o un Ribera para la comida de Navidad, o un Priorat para guardar. Uno, dos o tres tintos, según el presupuesto.',
        'enlace' => ['url' => Router::url('vinos.tipo', ['id' => 1, 'slug' => 'vino-tinto']), 'texto' => 'Ver vinos tintos'],
    ],
    [
        'titulo' => 'Cava y aperitivo',
        'texto'  => 'Un cava reserva o un champagne con conservas de marisco para el aperitivo de las fiestas: un lote que se abre y se comparte.',
        'enlace' => ['url' => Router::url('cavas.tipo', ['id' => 22, 'slug' => 'cava']), 'texto' => 'Ver cavas'],
    ],
    [
        'titulo' => 'Vinos de aquí',
        'texto'  => 'Vinos de la D.O. Alella y de Catalunya para quien vive fuera o para quien quiere conocer lo que se hace cerca de casa.',
        'enlace' => ['url' => Router::url('vinos.do', ['id' => 33, 'slug' => 'd-o-alella']), 'texto' => 'Ver vinos de Alella'],
    ],
    [
        'titulo' => 'Un licor especial',
        'texto'  => 'Un whisky, un ron o una ginebra que no se compraría para sí mismo, solo o acompañado de otra botella.',
        'enlace' => ['url' => Router::url('licores'), 'texto' => 'Ver licores'],
    ],
];

$faqs = [
    [
        'pregunta'  => '¿Cuánto cuesta un lote de Navidad?',
        'respuesta' => 'Lo que tú quieras gastar. No tenemos lotes cerrados: nos dices el presupuesto y para quién es, y montamos el lote con vinos, cavas, licores y conservas que encajen en ese precio.',
    ],
    [
        'pregunta'  => '¿Puedo elegir qué lleva el lote?',
        'respuesta' => 'Sí. Puedes decirnos las botellas exactas o dejarlo en nuestras manos: te proponemos una selección y la cambiamos hasta que te guste.',
    ],
    [
        'pregunta'  => '¿Hacéis regalos para empresas con factura?',
        'respuesta' => 'Sí. Preparamos lotes para clientes, proveedores o equipos, con factura a nombre de la empresa, y los llevamos a la oficina si está en el Maresme.',
    ],
    [
        'pregunta'  => '¿El envoltorio se cobra aparte?',
        'respuesta' => 'No. Envolvemos el regalo sin coste, tanto si te lo llevas de la tienda como si te lo llevamos nosotros.',
    ],
    [
        'pregunta'  => '¿Cuándo tengo que encargarlo?',
        'respuesta' => 'Cuanto antes, mejor, sobre todo si son muchos lotes o buscas una botella concreta: así tenemos tiempo de conseguirla. Escríbenos y te decimos para cuándo lo tendríamos.',
    ],
];

$url = rtrim(SITE_URL, '/') . '/lotes-y-regalos';
$schemaExtra = schemaBreadcrumb($breadcrumbItems)
    . schemaJson([
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        '@id'         => $url . '#servicio',
        'name'        => 'Lotes y regalos de vino a medida',
        'serviceType' => 'Lotes de Navidad y regalos de empresa',
        'description' => $pageDescription,
        'provider'    => ['@id' => schemaTiendaId()],
        'areaServed'  => ['@type' => 'AdministrativeArea', 'name' => 'Maresme'],
        'audience'    => [
            ['@type' => 'Audience', 'audienceType' => 'Particulares'],
            ['@type' => 'BusinessAudience', 'audienceType' => 'Empresas'],
        ],
        'url'         => $url,
    ])
    . schemaFAQ($faqs);

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header domicilio-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Lotes de Navidad y regalos de vino, a tu medida</h1>
        <p>Dinos para quién es y cuánto quieres gastar, y te preparamos el lote con vino, cava, licores o conservas. Lo envolvemos gratis y lo recoges en la tienda o te lo llevamos en el Maresme. También para empresas, con factura.</p>
        <?php echo renderVentajasPedido(); ?>
        <div class="domicilio-cta">
            <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, quiero preparar un lote de regalo.')); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer">
                <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                Encárgalo por WhatsApp
            </a>
            <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>" class="btn btn-outline btn-lg">
                <svg class="icon" aria-hidden="true"><use href="#icon-phone"></use></svg>
                Llamar al <?php echo SITE_PHONE; ?>
            </a>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Cómo lo preparamos</h2>
        </div>
        <ol class="domicilio-pasos">
            <li>
                <h3>Nos cuentas para quién es</h3>
                <p>Qué le gusta, si es para una persona, una familia o una empresa, y cuánto quieres gastar por lote. Por WhatsApp, por teléfono o en la tienda.</p>
            </li>
            <li>
                <h3>Te proponemos el lote</h3>
                <p>Elegimos las botellas y los complementos que encajan en tu presupuesto. Si quieres cambiar algo, lo cambiamos.</p>
            </li>
            <li>
                <h3>Lo envolvemos y te lo damos</h3>
                <p>Envuelto sin coste, para recoger en Premià de Mar o con nuestro reparto en el Maresme, donde nos digas.</p>
            </li>
        </ol>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Ideas para empezar</h2>
            <p>No hay lotes cerrados: aquí tienes algunas ideas, y a partir de ellas montamos el tuyo.</p>
        </div>
        <div class="servicios-lista">
            <?php foreach ($ideas as $idea): ?>
            <article class="servicio">
                <h3><?php echo htmlspecialchars($idea['titulo']); ?></h3>
                <p><?php echo htmlspecialchars($idea['texto']); ?></p>
                <p class="servicio-acciones">
                    <a href="<?php echo $idea['enlace']['url']; ?>"><?php echo htmlspecialchars($idea['enlace']['texto']); ?></a>
                </p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt" id="empresas">
    <div class="container">
        <div class="servicios-lista">
            <article class="servicio">
                <h2>Regalos de empresa</h2>
                <p>Si quieres tener un detalle con tus clientes, tus proveedores o tu equipo, preparamos los lotes que necesites, iguales o de distintos niveles según a quién van, con factura a nombre de la empresa.</p>
                <p>Te los llevamos a la oficina si está en el Maresme. Si tienes que enviarlos fuera, pregúntanos y lo vemos.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, quiero preparar regalos de empresa.')); ?>" target="_blank" rel="noopener noreferrer">
                        <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                        Pídenos presupuesto por WhatsApp
                    </a>
                    <a href="mailto:<?php echo SITE_EMAIL; ?>?subject=<?php echo rawurlencode('Regalos de empresa'); ?>">O escríbenos un correo</a>
                </p>
            </article>
            <article class="servicio">
                <h2>¿No sabes qué regalar?</h2>
                <p>En nuestra guía te contamos cómo acertar según la persona y el presupuesto. Y si prefieres que lo elijamos nosotros, cuéntanos un poco de quien lo recibirá.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo Router::url('wineworld.articulo', ['cat_slug' => 'guias-de-vino', 'art_slug' => 'guia-definitiva-regalar-vino-cava-licores']); ?>">Guía para regalar vino, cava y licores</a>
                    <a href="<?php echo Router::url('domicilio'); ?>">Dónde repartimos</a>
                </p>
            </article>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes sobre los lotes y regalos'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
