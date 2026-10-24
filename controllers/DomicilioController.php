<?php
/**
 * El Celler Nou - Vino a domicilio
 *
 * /vino-a-domicilio          Página general: zonas, cómo pedir y poblaciones.
 * /vino-a-domicilio/{slug}   Página de cada población.
 *
 * Los datos salen de includes/poblaciones.php.
 */

require_once ROOT_PATH . '/includes/poblaciones.php';

$slug      = Router::param('slug');
$poblacion = $slug ? getPoblacionReparto($slug) : null;

if ($slug && !$poblacion) {
    http_response_code(404);
    require ROOT_PATH . '/controllers/404.php';
    return;
}

$poblaciones = getPoblacionesReparto();
$zona1       = getPoblacionesPorZona(1);
$zona2       = getPoblacionesPorZona(2);

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Vino a domicilio', 'url' => Router::url('domicilio')]
];

if ($poblacion) {
    $nombre = $poblacion['nombre'];
    $zona   = ZONAS_REPARTO[$poblacion['zona']];
    $breadcrumbItems[] = ['name' => $nombre, 'url' => Router::url('domicilio.poblacion', ['slug' => $slug])];

    $pageTitle = 'Vino a domicilio en ' . $nombre . ' | El Celler Nou';
    $pageDescription = $poblacion['zona'] === 1
        ? 'Te llevamos vinos, cavas y licores a casa en ' . $nombre . ' el mismo día o al siguiente. '
            . 'Gratis en pedidos de más de ' . SHIPPING_FREE_MIN_ORDER . ' €. Pide por WhatsApp o teléfono.'
        : 'Vinos, cavas y licores a domicilio en ' . $nombre . ' en 24-48 horas desde nuestra tienda de '
            . 'Premià de Mar. Pide por WhatsApp o teléfono y te asesoramos.';

    $seleccion = getSeleccionPoblacion($poblacion);
    $vecinasSlugs = [];
    foreach ($poblacion['cerca'] as $s) {
        if ($p = getPoblacionReparto($s)) $vecinasSlugs[$s] = $p;
    }

    if (!empty($poblacion['seleccion']['procedencia'])) {
        $tituloSeleccion = 'Vinos de la D.O. Alella';
        $subSeleccion    = $poblacion['doAlella']
            ? 'Vinos de tu propia comarca, a pocos kilómetros de ' . $nombre
            : 'Los vinos del Maresme';
        $verTodoUrl = Router::url('vinos.do', ['id' => PROCEDENCIA_DO_ALELLA, 'slug' => 'do-alella']);
        $verTodoTxt = 'Ver todos los vinos de Alella';
    } elseif (!empty($poblacion['seleccion']['tipo']) && $poblacion['seleccion']['tipo'] == 2) {
        $tituloSeleccion = 'Vinos blancos para la cocina de mar';
        $subSeleccion    = 'Una selección de blancos que solemos recomendar';
        $verTodoUrl = Router::url('vinos.tipo', ['id' => 2, 'slug' => 'vino-blanco']);
        $verTodoTxt = 'Ver todos los blancos';
    } elseif (!empty($poblacion['seleccion']['tipo'])) {
        $tituloSeleccion = 'Vinos tintos que te podemos llevar';
        $subSeleccion    = 'Una muestra de los tintos que tenemos en la tienda';
        $verTodoUrl = Router::url('vinos.tipo', ['id' => 1, 'slug' => 'vino-tinto']);
        $verTodoTxt = 'Ver todos los tintos';
    } else {
        $tituloSeleccion = 'Cavas y espumosos para celebrar';
        $subSeleccion    = 'Una muestra de los espumosos que tenemos en la tienda';
        $verTodoUrl = Router::url('cavas');
        $verTodoTxt = 'Ver todos los cavas';
    }

    // FAQ de la población: datos reales de la zona de reparto.
    $faqs = [
        [
            'pregunta'  => '¿Hacéis entregas de vino a domicilio en ' . $nombre . '?',
            'respuesta' => $poblacion['zona'] === 1
                ? 'Sí. ' . $nombre . ' está dentro de nuestra zona de entrega rápida: te llevamos el pedido a '
                    . 'casa el mismo día o al siguiente. En pedidos de más de ' . SHIPPING_FREE_MIN_ORDER
                    . ' € la entrega es gratuita.'
                : 'Sí. Llevamos el pedido a ' . $nombre . ' en 24 a 48 horas laborables desde nuestra tienda '
                    . 'de Premià de Mar. El coste depende del tamaño del pedido: escríbenos y te lo decimos '
                    . 'antes de confirmarlo.'
        ],
        [
            'pregunta'  => '¿Cómo hago un pedido desde ' . $nombre . '?',
            'respuesta' => 'Escríbenos por WhatsApp al ' . SITE_PHONE_MOBILE . ' o llámanos al ' . SITE_PHONE
                . '. Dinos qué botellas quieres (o qué vas a celebrar y el presupuesto, y te aconsejamos), la '
                . 'dirección de entrega y la franja horaria que te va mejor. Te confirmamos el pedido y el '
                . 'día de entrega.'
        ],
        [
            'pregunta'  => '¿Puedo recoger el pedido en la tienda?',
            'respuesta' => 'Claro. Estamos en ' . SITE_STREET . ', ' . SITE_POSTAL_CODE . ' ' . SITE_LOCALITY
                . ', a unos ' . max(1, $poblacion['km']) . ' km de ' . $nombre . '. Te lo dejamos preparado '
                . 'y lo recoges cuando te vaya bien dentro del horario de la tienda.'
        ],
        faqsTienda()['devolucion'],
    ];
    if ($poblacion['km'] === 0) {
        // En Premià de Mar la "distancia" no tiene sentido.
        $faqs[2]['respuesta'] = 'Claro. Estamos en ' . SITE_STREET . ', ' . SITE_POSTAL_CODE . ' '
            . SITE_LOCALITY . '. Te lo dejamos preparado y lo recoges cuando te vaya bien dentro del '
            . 'horario de la tienda.';
    }

    $schemaExtra = schemaBreadcrumb($breadcrumbItems)
        . schemaServicioDomicilio([$poblacion], rtrim(SITE_URL, '/') . '/vino-a-domicilio/' . $slug)
        . schemaFAQ($faqs);
} else {
    $pageTitle = 'Vino a domicilio en el Maresme | El Celler Nou, Premià de Mar';
    $pageDescription = 'Vinos, cavas, licores y cervezas a domicilio en Premià de Mar, Vilassar, El Masnou, '
        . 'Alella, Mataró y todo el Maresme. Entrega el mismo día o al siguiente. Pide por WhatsApp.';

    $faqs = [
        [
            'pregunta'  => '¿En qué poblaciones hacéis entregas a domicilio?',
            'respuesta' => 'El mismo día o el siguiente: ' . listaNombresPoblaciones($zona1)
                . '. En 24 a 48 horas laborables: ' . listaNombresPoblaciones($zona2)
                . '. Si tu población no aparece, pregúntanos: también enviamos al resto de Cataluña y de la península.'
        ],
        [
            'pregunta'  => '¿Cuánto cuesta la entrega?',
            'respuesta' => 'En las poblaciones de entrega rápida la entrega es gratuita en pedidos de más de '
                . SHIPPING_FREE_MIN_ORDER . ' €. En el resto depende de la distancia y del tamaño del pedido: '
                . 'te decimos el coste antes de confirmarlo.'
        ],
        [
            'pregunta'  => '¿Cómo hago el pedido?',
            'respuesta' => 'Por WhatsApp al ' . SITE_PHONE_MOBILE . ', por teléfono al ' . SITE_PHONE
                . ' o desde la página de contacto. Dinos qué quieres, la dirección y la franja horaria. Si no '
                . 'sabes qué elegir, cuéntanos la ocasión y el presupuesto y te proponemos opciones.'
        ],
        [
            'pregunta'  => '¿Preparáis lotes de regalo y pedidos para empresas?',
            'respuesta' => 'Sí. Preparamos lotes y cajas de regalo, pedidos para celebraciones y encargos de '
                . 'empresa, y te los llevamos a casa o a la oficina dentro de nuestra zona de reparto. Para '
                . 'Navidad conviene pedirlos con unos días de margen.'
        ],
        faqsTienda()['devolucion'],
    ];

    $schemaExtra = schemaBreadcrumb($breadcrumbItems)
        . schemaServicioDomicilio(array_values($poblaciones), rtrim(SITE_URL, '/') . '/vino-a-domicilio')
        . schemaFAQ($faqs);
}

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header domicilio-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <?php if ($poblacion): ?>
        <h1>Vino a domicilio en <?php echo htmlspecialchars($nombre); ?></h1>
        <p>Vinos, cavas, licores y cervezas de El Celler Nou, la tienda de vinos de Premià de Mar, en la puerta de tu casa.</p>
        <?php else: ?>
        <h1>Vino a domicilio en el Maresme</h1>
        <p>Elige entre más de mil vinos, cavas, licores y cervezas y te lo llevamos a casa desde nuestra tienda de Premià de Mar.</p>
        <?php endif; ?>

        <div class="domicilio-cta">
            <a href="<?php echo htmlspecialchars(whatsappPedidoUrl($poblacion['nombre'] ?? null)); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer">
                <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                Pedir por WhatsApp
            </a>
            <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>" class="btn btn-outline btn-lg">
                <svg class="icon" aria-hidden="true"><use href="#icon-phone"></use></svg>
                Llamar al <?php echo SITE_PHONE; ?>
            </a>
        </div>
    </div>
</section>

<?php if ($poblacion): ?>

<section class="section">
    <div class="container domicilio-intro">
        <div class="domicilio-texto">
            <?php foreach ($poblacion['intro'] as $parrafo): ?>
            <p><?php echo htmlspecialchars($parrafo); ?></p>
            <?php endforeach; ?>
        </div>

        <aside class="domicilio-datos" aria-label="Condiciones de entrega en <?php echo htmlspecialchars($nombre); ?>">
            <h2>Entrega en <?php echo htmlspecialchars($nombre); ?></h2>
            <dl>
                <dt>Plazo</dt>
                <dd><?php echo htmlspecialchars($zona['plazo']); ?></dd>
                <dt>Coste</dt>
                <dd><?php echo htmlspecialchars($zona['coste']); ?></dd>
                <?php if ($poblacion['km'] > 0): ?>
                <dt>Distancia a la tienda</dt>
                <dd>Unos <?php echo $poblacion['km']; ?> km</dd>
                <?php endif; ?>
                <?php if (!empty($poblacion['cp'])): ?>
                <dt>Código postal</dt>
                <dd><?php echo htmlspecialchars(implode(', ', $poblacion['cp'])); ?></dd>
                <?php endif; ?>
                <dt>Recogida en tienda</dt>
                <dd>Gratis, en <?php echo SITE_STREET; ?>, <?php echo SITE_LOCALITY; ?></dd>
            </dl>
        </aside>
    </div>
</section>

<?php else: ?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Zonas y plazos de entrega</h2>
            <p>Todos los pedidos salen de nuestra tienda de Premià de Mar</p>
        </div>

        <div class="domicilio-zonas">
            <?php foreach ([1 => $zona1, 2 => $zona2] as $z => $lista): ?>
            <div class="domicilio-zona">
                <h3><?php echo htmlspecialchars(ZONAS_REPARTO[$z]['plazo']); ?></h3>
                <p class="domicilio-zona-coste"><?php echo htmlspecialchars(ZONAS_REPARTO[$z]['coste']); ?></p>
                <ul>
                    <?php foreach ($lista as $s => $p): ?>
                    <li><a href="<?php echo Router::url('domicilio.poblacion', ['slug' => $s]); ?>">Vino a domicilio en <?php echo htmlspecialchars($p['nombre']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="text-center mt-2">¿Tu población no está en la lista? También enviamos al resto de Cataluña y de la península: consulta la <a href="<?php echo Router::url('legal.envios'); ?>">política de envíos</a>.</p>
    </div>
</section>

<?php endif; ?>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Cómo pedir</h2>
        </div>
        <ol class="domicilio-pasos">
            <li>
                <h3>Dinos qué quieres</h3>
                <p>Por WhatsApp o por teléfono. Si buscas algo concreto, dinos la botella; si no, la ocasión, el plato y el presupuesto, y te proponemos opciones.</p>
            </li>
            <li>
                <h3>Te confirmamos el pedido</h3>
                <p>Precio, disponibilidad, día de entrega y forma de pago, antes de prepararlo.</p>
            </li>
            <li>
                <h3>Te lo llevamos a casa</h3>
                <p>Bien embalado, en la franja que te vaya mejor<?php echo $poblacion ? ' en ' . htmlspecialchars($nombre) : ''; ?>. O lo recoges en la tienda.</p>
            </li>
        </ol>
    </div>
</section>

<?php if ($poblacion && !empty($seleccion)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo htmlspecialchars($tituloSeleccion); ?></h2>
            <p><?php echo htmlspecialchars($subSeleccion); ?></p>
        </div>

        <div class="products-grid">
            <?php foreach ($seleccion as $producto): ?>
            <a href="<?php echo getProductoUrl($producto); ?>" class="product-card">
                <div class="product-card-image">
                    <img src="<?php echo getBaseUrl() . rutaFoto($producto); ?>"
                         alt="<?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?>"
                         width="300" height="300" decoding="async" loading="lazy">
                </div>
                <div class="product-card-content">
                    <span class="product-card-category"><?php echo htmlspecialchars($producto['tipo_nombre'] ?? ''); ?></span>
                    <h3 class="product-card-title"><?php echo htmlspecialchars(normalizarNombre($producto['nombre'])); ?></h3>
                    <?php if (!empty($producto['procedencia_nombre'])): ?>
                    <p class="product-card-origin"><?php echo htmlspecialchars($producto['procedencia_nombre']); ?></p>
                    <?php endif; ?>
                    <div class="product-card-footer">
                        <span class="product-card-price"><?php echo formatPrecioWeb($producto['precio_venta']); ?></span>
                        <span class="product-card-link">Ver más <svg class="icon"><use href="#icon-arrow-right"></use></svg></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-2">
            <a href="<?php echo $verTodoUrl; ?>" class="btn btn-primary btn-lg"><?php echo $verTodoTxt; ?></a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($poblacion): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>También repartimos cerca de <?php echo htmlspecialchars($nombre); ?></h2>
        </div>
        <ul class="domicilio-vecinas">
            <?php foreach ($vecinasSlugs as $s => $p): ?>
            <li><a href="<?php echo Router::url('domicilio.poblacion', ['slug' => $s]); ?>">Vino a domicilio en <?php echo htmlspecialchars($p['nombre']); ?></a></li>
            <?php endforeach; ?>
            <li><a href="<?php echo Router::url('domicilio'); ?>">Todas las poblaciones</a></li>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php echo renderFaqs($faqs, $poblacion ? 'Preguntas sobre la entrega en ' . $nombre : 'Preguntas frecuentes sobre la entrega a domicilio'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
