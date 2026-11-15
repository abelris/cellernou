<?php
/**
 * El Celler Nou - Espinaler
 *
 * /espinaler   Página de la marca: vermut, Salsa Espinaler y conservas de
 *              Espinaler (Vilassar de Mar, 1896), a cinco minutos de la tienda.
 *
 * Busca «Espinaler» + dónde comprar / Premià / Maresme, «salsa Espinaler»,
 * «vermut Espinaler». Estudio del 15-nov-2026: esas búsquedas las ganan tiendas
 * online; ninguna tienda física del Maresme las trabaja.
 *
 * 15-nov-2026: el usuario confirma que Espinaler permite usar su material.
 * Fotos y logo de espinaler.com en images/espinaler (sin personas reconocibles;
 * la ilustración es del fundador). Textos propios. Historia según
 * espinaler.com/historia-espinaler (año de la Salsa: la marca da 1950 y 1952,
 * así que se dice «a principios de los años cincuenta»; la dirección de la
 * taberna no se pone porque las fuentes no coinciden).
 * Los productos salen del catálogo (getProductosMarca), no de una lista fija.
 */

require_once ROOT_PATH . '/includes/poblaciones.php';

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Espinaler', 'url' => Router::url('espinaler')]
];

$pageTitle = 'Espinaler en Premià de Mar: vermut, salsa y conservas';
$pageDescription = 'Vermut, Salsa Espinaler y conservas de marisco de Espinaler, la casa de Vilassar de Mar desde 1896. '
    . 'En nuestra tienda de Premià de Mar y con reparto en el Maresme.';

$productos = getProductosMarca('espinaler');

$faqs = [
    [
        'pregunta'  => '¿Dónde comprar Salsa Espinaler en el Maresme?',
        'respuesta' => 'En El Celler Nou, en la calle Ramón Llull, 15, de Premià de Mar, a cinco minutos de Vilassar. Tenemos la Salsa Espinaler en varios formatos, el vermut y las conservas de la casa, y te lo llevamos a casa con nuestro reparto en el Maresme.',
    ],
    [
        'pregunta'  => '¿Qué es la Salsa Espinaler?',
        'respuesta' => 'Es la salsa de aperitivo de la casa Espinaler, ideada a principios de los años cincuenta por Ventureta Roldós en la taberna familiar de Vilassar de Mar. La receta es secreta: la etiqueta solo declara vinagre, pimentón rojo y especias. Se echan unas gotas sobre berberechos, almejas, mejillones, patatas o aceitunas.',
    ],
    [
        'pregunta'  => '¿Cómo se toma el vermut Espinaler?',
        'respuesta' => 'Muy frío, con hielo y una rodaja de naranja o una aceituna, y acompañado de conservas con unas gotas de Salsa Espinaler y unas patatas. Es el aperitivo de los domingos en el Maresme.',
    ],
    [
        'pregunta'  => '¿Vermut negro o blanco?',
        'respuesta' => 'El negro (rojo) es el clásico: más intenso, con notas de hierbas, especias y un punto amargo. El blanco es más suave y floral. Si no lo has probado nunca, empieza por el negro, que es el de la taberna.',
    ],
    [
        'pregunta'  => '¿Qué significan los números de las latas, como 35/45?',
        'respuesta' => 'Es el calibre: el número aproximado de piezas por lata. Cuantas menos piezas, más grandes son. Unos berberechos 25/35 son más grandes que unos 55/65.',
    ],
    [
        'pregunta'  => '¿Preparáis lotes con productos Espinaler?',
        'respuesta' => 'Sí. Un vermut, unas latas de berberechos o mejillones y una Salsa Espinaler hacen un lote de aperitivo muy del Maresme. Lo montamos a tu medida, lo envolvemos gratis y te lo llevamos a casa.',
    ],
];

$url = rtrim(SITE_URL, '/') . '/espinaler';
$schemaExtra = schemaBreadcrumb($breadcrumbItems)
    . schemaJson([
        '@context'    => 'https://schema.org',
        '@type'       => 'WebPage',
        '@id'         => $url . '#pagina',
        'url'         => $url,
        'name'        => 'Espinaler en Premià de Mar',
        'description' => $pageDescription,
        'about'       => [
            '@type'        => 'Organization',
            'name'         => 'Espinaler',
            'url'          => 'https://espinaler.com/',
            'foundingDate' => '1896',
            'logo'         => schemaUrl('/images/espinaler/logo-400.webp'),
            'location'     => ['@type' => 'Place', 'name' => 'Vilassar de Mar'],
        ],
        'provider'    => ['@id' => schemaTiendaId()],
    ])
    . schemaFAQ($faqs);

// Imagen para compartir en redes (1200x630, images/paginas).
$pageImage = getBaseUrl() . '/images/paginas/og-espinaler.jpg';
$pageImageWidth = 1200;
$pageImageHeight = 630;

$img = function ($nombre, $ancho, $alto, $alt, $clase = '', $srcset = null) {
    $base = getBaseUrl() . '/images/espinaler/' . $nombre;
    return '<img src="' . $base . '-' . $ancho . '.webp"'
        . ($srcset ? ' srcset="' . $srcset . '"' : '')
        . ' width="' . $ancho . '" height="' . $alto . '" alt="' . htmlspecialchars($alt) . '" loading="lazy" decoding="async"'
        . ($clase ? ' class="' . $clase . '"' : '') . '>';
};

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header domicilio-header cabecera-foto">
    <?php echo cabeceraFotoHtml('/images/espinaler/aperitivo-terraza', 'Aperitivo con vermut, Salsa Espinaler, patatas y aceitunas en una terraza junto al mar', [800, 1400, 1920], 9 / 16); ?>
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Espinaler en Premià de Mar: vermut, salsa y conservas</h1>
        <p>El aperitivo del Maresme tiene nombre: Espinaler. La casa nació en 1896 como una taberna de Vilassar de Mar, a cinco minutos de nuestra tienda, y hoy su vermut, su Salsa Espinaler y sus conservas de marisco se conocen en medio mundo. En El Celler Nou los tienes cerca de casa y te los llevamos en el Maresme.</p>
        <div class="domicilio-cta">
            <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, quiero productos Espinaler.')); ?>" class="btn btn-primary btn-lg" target="_blank" rel="noopener noreferrer">
                <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                Pídelos por WhatsApp
            </a>
            <a href="#productos-espinaler" class="btn btn-outline btn-lg">Ver los productos</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container esp-dos">
        <div>
            <h2>El aperitivo del Maresme</h2>
            <p>Un vermut negro muy frío, unas latas de berberechos o mejillones recién abiertas, unas gotas de Salsa Espinaler por encima y unas patatas. Así es el aperitivo de los domingos en el Maresme desde que, en 1946, la taberna Espinaler empezó a servir su vermut con conservas gallegas.</p>
            <ul class="esp-lista">
                <li><strong>El vermut:</strong> negro, blanco o rosado, con hielo y una rodaja de naranja o una aceituna.</li>
                <li><strong>Las conservas:</strong> berberechos, almejas, navajas y mejillones al natural o en escabeche.</li>
                <li><strong>La salsa:</strong> unas gotas de Salsa Espinaler sobre el marisco, las patatas o las aceitunas.</li>
            </ul>
            <p>Si quieres prepararlo en casa, dinos para cuántos es y te lo dejamos listo.</p>
        </div>
        <?php echo $img('aperitivo-mesa', 600, 500, 'Mesa de aperitivo con vermut Espinaler, conservas, patatas y Salsa Espinaler', 'esp-foto', getBaseUrl() . '/images/espinaler/aperitivo-mesa-600.webp 600w, ' . getBaseUrl() . '/images/espinaler/aperitivo-mesa-1200.webp 1200w'); ?>
    </div>
</section>

<section class="section section-alt" id="productos-espinaler">
    <div class="container">
        <div class="section-header">
            <h2>Productos Espinaler en El Celler Nou</h2>
            <p>Toda la gama Espinaler. Lo que está en la tienda sale con su precio; lo demás te lo pedimos («Por encargo»).</p>
        </div>
        <?php if ($productos): ?>
        <?php
        // Agrupados por tipo (más productos primero); dentro de cada grupo, el
        // orden de escaparate. Con todo el catálogo son unos 200: sin grupos
        // la página era una lista interminable.
        $grupos = [];
        foreach ($productos as $prod) {
            $grupos[$prod['tipo_nombre'] ?: 'Otros'][] = $prod;
        }
        uasort($grupos, function ($a, $b) { return count($b) <=> count($a); });
        ?>
        <nav class="esp-atajos" aria-label="Tipos de producto Espinaler">
            <?php foreach ($grupos as $tipoNombre => $lista): ?>
            <a href="#esp-<?php echo createSlug($tipoNombre); ?>"><?php echo htmlspecialchars($tipoNombre); ?> <span>(<?php echo count($lista); ?>)</span></a>
            <?php endforeach; ?>
        </nav>
        <?php foreach ($grupos as $tipoNombre => $lista): ?>
        <h3 class="esp-grupo" id="esp-<?php echo createSlug($tipoNombre); ?>"><?php echo htmlspecialchars($tipoNombre); ?></h3>
        <?php echo renderTarjetasMarca($lista); ?>
        <?php endforeach; ?>
        <?php else: ?>
        <p>Pregúntanos por WhatsApp o por teléfono qué productos Espinaler tenemos hoy.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>La gama Espinaler</h2>
        </div>
        <div class="esp-gama">
            <article>
                <?php echo $img('vermuts', 426, 398, 'Vermuts Espinaler: negro, reserva y blanco'); ?>
                <h3>Vermut</h3>
                <p>El negro es el de siempre, intenso, de hierbas y especias; el blanco, más suave y floral. También hay reserva, vintage y rosado.</p>
            </article>
            <article>
                <?php echo $img('salsas', 700, 561, 'Botellines de Salsa Espinaler: clásica, picante y otras variedades'); ?>
                <h3>Salsa Espinaler</h3>
                <p>La salsa de aperitivo de la casa, de receta secreta: vinagre, pimentón rojo y especias. En botellín, en botella y en versión picante.</p>
            </article>
            <article>
                <?php echo $img('conservas', 550, 398, 'Latas de conservas Espinaler de berberechos, mejillones y navajas'); ?>
                <h3>Conservas de marisco</h3>
                <p>Berberechos, almejas, navajas y mejillones, con el calibre en la lata: cuantas menos piezas, más grandes.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container esp-dos esp-dos--inversa">
        <?php echo $img('miquel-riera', 444, 490, 'Ilustración de Miquel Riera i Prat, fundador de Espinaler', 'esp-foto esp-foto--retrato', getBaseUrl() . '/images/espinaler/miquel-riera-444.webp 444w, ' . getBaseUrl() . '/images/espinaler/miquel-riera-888.webp 888w'); ?>
        <div>
            <?php echo $img('logo', 400, 237, 'Espinaler, desde 1896', 'esp-logo'); ?>
            <h2>Cinco generaciones en Vilassar de Mar</h2>
            <ol class="esp-historia">
                <li><strong>1896.</strong> Miquel Riera i Prat abre la taberna Espinaler en Vilassar de Mar, donde sirve vino y vermut a la gente del pueblo.</li>
                <li><strong>1946.</strong> Su nieto Joan Tapias apuesta por el vermut y empieza a servirlo con conservas gallegas: nace el aperitivo Espinaler.</li>
                <li><strong>Años cincuenta.</strong> Ventureta Roldós, su mujer, crea la Salsa Espinaler, que hoy sigue siendo un secreto de familia.</li>
                <li><strong>Después.</strong> Miquel Tapias viaja a Galicia a escoger el marisco y lanza las conservas con la marca de la casa.</li>
                <li><strong>Hoy.</strong> La quinta generación, David y Miki Tapias, lleva Espinaler por todo el mundo.</li>
            </ol>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="servicios-lista">
            <article class="servicio">
                <h2>Un lote de aperitivo del Maresme</h2>
                <p>Un vermut Espinaler, unas latas de berberechos y mejillones y una Salsa Espinaler: un regalo de Navidad muy de aquí. Lo montamos a tu medida, solo o con vino y cava, y lo envolvemos sin coste.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo Router::url('lotes'); ?>">Lotes de Navidad y regalos de empresa</a>
                    <a href="<?php echo Router::url('jamon'); ?>">Y un jamón Sierra Morena para completarlo</a>
                </p>
            </article>
            <article class="servicio">
                <h2>Te lo llevamos a casa</h2>
                <p>Con nuestro reparto en Premià y todo el Maresme, el mismo día o el siguiente. Gratis desde <?php echo SHIPPING_FREE_MIN_ORDER; ?> € en Premià y alrededores.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo Router::url('domicilio'); ?>">Vino y aperitivo a domicilio</a>
                    <a href="<?php echo Router::url('conservas'); ?>">Todas las conservas</a>
                </p>
            </article>
        </div>
        <p class="sm-nota">Las fotos de producto y de ambiente son de Espinaler.</p>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes sobre Espinaler'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
