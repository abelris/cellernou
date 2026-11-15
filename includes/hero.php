<?php
/**
 * El Celler Nou - Hero de la portada (carrusel)
 *
 * Cada visita a la portada empieza por una diapositiva al azar (se elige en el
 * servidor: la portada no se cachea en Cloudflare, cf-cache-status DYNAMIC) y
 * luego pasan todas en bucle cada 7 s. Se para al pasar el ratón o al usar el
 * teclado, tiene botón de pausa y no se mueve solo si el sistema pide reducir
 * el movimiento.
 *
 * El h1 de la portada es fijo (fuera de las diapositivas): así Google ve
 * siempre el mismo título aunque cada visita empiece por una distinta. Los
 * títulos de las diapositivas son h2.
 *
 * Temporadas: 'navidad' = del 1 de noviembre al 6 de enero. Las diapositivas
 * sin temporada salen todo el año.
 *
 * Fotos en images/home/ en WebP (800, 1400 y 1920 px, sin metadatos):
 *   navidad-lotes     Pexels 6127621
 *   navidad-jamon     Pexels 4871280
 *   navidad-cava      Pexels 6517944 (la misma del artículo cava o champagne)
 *   vino-a-domicilio  Pexels 31550195
 *   tienda-interior   foto propia de la tienda (images/tienda)
 * Licencia Pexels: uso comercial libre, sin atribución obligatoria.
 *
 * Textos: solo servicios confirmados por el usuario
 * (docs/seo/datos-productos-usuario.md).
 */

require_once ROOT_PATH . '/includes/poblaciones.php';

/** ¿Estamos en la temporada indicada? */
function heroEnTemporada($temporada) {
    if ($temporada === null) return true;
    $mes = (int) date('n');
    $dia = (int) date('j');
    if ($temporada === 'navidad') {
        return $mes >= 11 || ($mes === 1 && $dia <= 6);
    }
    return false;
}

/** Diapositivas activas hoy, en orden. */
function getHeroSlides() {
    $navidad = heroEnTemporada('navidad');

    $slides = [
        [
            'id'        => 'lotes',
            'aviso'     => ['etiqueta' => 'Navidad', 'texto' => 'Lotes de Navidad a tu medida, envueltos gratis', 'corto' => 'Lotes de Navidad a medida', 'url' => Router::url('lotes')],
            'temporada' => 'navidad',
            'etiqueta'  => 'Navidad',
            'titulo'    => 'Lotes de Navidad a tu medida',
            'texto'     => 'Dinos para quién es y cuánto quieres gastar: lo preparamos con vino, cava, licores o ibéricos, '
                         . 'lo envolvemos gratis y te lo llevamos en el Maresme. También regalos de empresa con factura.',
            'cta'       => ['texto' => 'Prepara tu lote', 'url' => Router::url('lotes')],
            'cta2'      => ['texto' => 'Encárgalo por WhatsApp', 'url' => whatsappUrl('Hola, quiero preparar un lote de Navidad.'), 'externo' => true],
            'imagen'    => 'navidad-lotes',
            'anchos'    => [800, 1400, 1920],
            'alt'       => 'Regalo de Navidad envuelto junto a una copa de vino',
            'foco'      => 'center',
        ],
        [
            'id'        => 'jamon',
            'aviso'     => ['etiqueta' => 'Mundial del Jamón', 'texto' => 'Jamones Sierra Morena, de Los Pedroches, zona ganadora del Mundial del Jamón 2026', 'corto' => 'Sierra Morena: Mundial del Jamón 2026', 'url' => Router::url('jamon')],
            'temporada' => null,
            'etiqueta'  => 'Mundial del Jamón 2026',
            'titulo'    => 'Jamones Sierra Morena, de Los Pedroches',
            'texto'     => 'Los Pedroches ganó el I Mundial del Jamón (Ronda, 2026) con las piezas de Sierra Morena e Ibedul. '
                         . 'Jamón y paleta de bellota, de cebo de campo y serranos: en pieza entera, loncheado o en tu lote'
                         . ($navidad ? '. Encárgalo con tiempo para Navidad.' : '.'),
            'cta'       => ['texto' => 'Ver los jamones Sierra Morena', 'url' => Router::url('jamon')],
            'cta2'      => ['texto' => 'Pregúntanos por WhatsApp', 'url' => whatsappUrl('Hola, quiero información sobre jamón o paleta ibérica.'), 'externo' => true],
            'imagen'    => 'navidad-jamon',
            'anchos'    => [800, 1400, 1920],
            'alt'       => 'Lonchas de jamón ibérico sobre una tabla de madera junto a una copa de vino tinto',
            'foco'      => 'center',
        ],
        [
            'id'        => 'domicilio',
            'aviso'     => ['etiqueta' => 'A domicilio', 'texto' => 'Envío gratis desde ' . SHIPPING_FREE_MIN_ORDER . ' € y -' . ORDER_DISCOUNT_PERCENT . ' % desde ' . ORDER_DISCOUNT_MIN . ' €', 'corto' => 'Envío gratis desde ' . SHIPPING_FREE_MIN_ORDER . ' €', 'url' => Router::url('domicilio')],
            'temporada' => null,
            'etiqueta'  => 'Vino a domicilio',
            'titulo'    => 'Te llevamos el vino a casa',
            'texto'     => 'Con nuestro reparto en Premià y todo el Maresme, el mismo día o el siguiente. Gratis desde '
                         . SHIPPING_FREE_MIN_ORDER . ' € (' . SHIPPING_FREE_MIN_ORDER_ZONA2 . ' € en el resto del Maresme) y un '
                         . ORDER_DISCOUNT_PERCENT . ' % menos a partir de ' . ORDER_DISCOUNT_MIN . ' €.',
            'cta'       => ['texto' => 'Cómo pedir a domicilio', 'url' => Router::url('domicilio')],
            'cta2'      => ['texto' => 'Pide por WhatsApp', 'url' => whatsappUrl('Hola, quiero hacer un pedido a domicilio.'), 'externo' => true],
            'imagen'    => 'vino-a-domicilio',
            'anchos'    => [800, 1400, 1920],
            'alt'       => 'Caja de madera llena de botellas de vino',
            'foco'      => 'center',
        ],
        [
            'id'        => 'cava',
            'aviso'     => ['etiqueta' => 'Navidad', 'texto' => 'Cava y vino para las fiestas: te aconsejamos', 'corto' => 'Cava y vino para las fiestas', 'url' => Router::url('cavas')],
            'temporada' => 'navidad',
            'etiqueta'  => 'Navidad',
            'titulo'    => 'Cava y vino para las fiestas',
            'texto'     => 'Cavas, champagnes y vinos para cada comida de Navidad. Cuéntanos el menú y te decimos qué botella poner con cada plato.',
            'cta'       => ['texto' => 'Ver cavas', 'url' => Router::url('cavas')],
            'cta2'      => ['texto' => 'Qué vino servir en Navidad', 'url' => Router::url('wineworld.articulo', ['cat_slug' => 'maridajes', 'art_slug' => 'que-vino-servir-comidas-navidad'])],
            'imagen'    => 'navidad-cava',
            'anchos'    => [800, 1400, 1920],
            'alt'       => 'Sirviendo cava en copas en una mesa de Navidad',
            'foco'      => 'center',
        ],
        [
            'id'        => 'tienda',
            'aviso'     => $navidad
                ? ['etiqueta' => 'Festivos', 'texto' => 'Abrimos todos los festivos de 10:00 a 14:00', 'corto' => 'Abrimos festivos de 10 a 14 h', 'url' => Router::url('contacto')]
                : ['etiqueta' => 'Desde 1995', 'texto' => 'Ven a la tienda: C/ Ramón Llull, 15, Premià de Mar', 'corto' => 'C/ Ramón Llull, 15, Premià', 'url' => Router::url('la-tienda')],
            'temporada' => null,
            'etiqueta'  => 'Desde 1995',
            'titulo'    => $navidad ? 'Abrimos todos los festivos por la mañana' : 'Ven a vernos a la tienda',
            'texto'     => $navidad
                ? 'Festivos de 10:00 a 14:00, como los domingos (solo cerramos el 1 de enero). En la calle Ramón Llull, 15, con más de 30 años eligiendo vinos y vino a granel.'
                : 'En la calle Ramón Llull, 15, de Premià de Mar: vinos con D.O., cavas, licores, cervezas y vino a granel, y te aconsejamos en persona.',
            'cta'       => ['texto' => 'Cómo llegar', 'url' => SITE_GOOGLE_MAPS_URL, 'externo' => true],
            'cta2'      => ['texto' => 'Conoce la tienda', 'url' => Router::url('la-tienda')],
            'imagen'    => 'tienda-interior',
            'anchos'    => [800, 1400],
            'alt'       => 'Interior de El Celler Nou en Premià de Mar, con estanterías de vino y botas de vino a granel',
            'foco'      => 'center',
        ],
    ];

    return array_values(array_filter($slides, function ($s) {
        return heroEnTemporada($s['temporada']);
    }));
}

/**
 * Avisos de la barra negra de arriba (todas las páginas): los mismos
 * destacados que el hero, en versión corta. El de envío gratis va primero.
 */
function getAvisosBarra() {
    $avisos = [];
    foreach (getHeroSlides() as $s) {
        if (empty($s['aviso'])) continue;
        if ($s['id'] === 'domicilio') array_unshift($avisos, $s['aviso']);
        else $avisos[] = $s['aviso'];
    }
    return $avisos;
}

/**
 * Barra de avisos en cinta continua (la pinta includes/header.php).
 * La lista va dos veces seguidas: la cinta se desplaza la mitad de su ancho y
 * vuelve a empezar, así no se nota el salto. La segunda copia es decorativa
 * (aria-hidden y sin foco) para que los lectores de pantalla no la repitan.
 */
function renderAvisosBarra() {
    $avisos = getAvisosBarra();
    if (!$avisos) return '';
    $lista = function ($copia) use ($avisos) {
        $html = '<ul class="avisos-lista"' . ($copia ? ' aria-hidden="true"' : '') . '>';
        foreach ($avisos as $a) {
            $html .= '<li class="aviso"><a href="' . htmlspecialchars($a['url']) . '"' . ($copia ? ' tabindex="-1"' : '') . '>'
                . '<span class="aviso-etiqueta">' . htmlspecialchars($a['etiqueta']) . '</span>'
                . '<span class="aviso-texto">' . htmlspecialchars($a['texto']) . '</span>'
                . '<span class="aviso-flecha" aria-hidden="true">&rsaquo;</span></a></li>';
        }
        return $html . '</ul>';
    };
    return '<div class="avisos-barra" role="region" aria-label="Novedades de El Celler Nou">'
        . '<div class="avisos-pista">' . $lista(false) . $lista(true) . '</div></div>';
}

/** <img> responsive de una diapositiva. La inicial carga con prioridad. */
function heroImagen(array $s, $esInicial) {
    $base = getBaseUrl() . '/images/home/' . $s['imagen'];
    $srcset = [];
    foreach ($s['anchos'] as $w) {
        $srcset[] = $base . '-' . $w . '.webp ' . $w . 'w';
    }
    $mayor = end($s['anchos']);
    // Todas las fotos son 3:2 salvo la de la tienda (16:9).
    $alto = $s['imagen'] === 'tienda-interior' ? round($mayor * 9 / 16) : round($mayor * 2 / 3);
    return '<img src="' . $base . '-' . $mayor . '.webp" srcset="' . implode(', ', $srcset) . '" sizes="100vw"'
        . ' alt="' . htmlspecialchars($s['alt']) . '" class="hero-bg" width="' . $mayor . '" height="' . $alto . '"'
        . ' style="object-position: ' . $s['foco'] . '"'
        . ($esInicial ? ' fetchpriority="high"' : ' loading="lazy"') . ' decoding="async">';
}

/** Hero completo. */
function renderHero() {
    $slides = getHeroSlides();
    $total = count($slides);
    $inicio = $total > 1 ? random_int(0, $total - 1) : 0;

    ob_start();
    ?>
<section class="hero hero-carrusel" aria-roledescription="carrusel" aria-label="Destacados de El Celler Nou" data-inicio="<?php echo $inicio; ?>">
    <div class="hero-escena">
    <div class="hero-slides">
        <?php foreach ($slides as $i => $s): $activa = $i === $inicio; ?>
        <div class="hero-slide<?php echo $activa ? ' is-active' : ''; ?>" role="group" aria-roledescription="diapositiva"
             aria-label="<?php echo ($i + 1) . ' de ' . $total; ?>" data-slide="<?php echo htmlspecialchars($s['id']); ?>"<?php echo $activa ? '' : ' aria-hidden="true" inert'; ?>>
            <?php echo heroImagen($s, $activa); ?>
            <div class="container">
                <div class="hero-content">
                    <span class="hero-badge"><?php echo htmlspecialchars($s['etiqueta']); ?></span>
                    <h2 class="hero-titulo"><?php echo htmlspecialchars($s['titulo']); ?></h2>
                    <p><?php echo htmlspecialchars($s['texto']); ?></p>
                    <div class="hero-buttons">
                        <a href="<?php echo htmlspecialchars($s['cta']['url']); ?>" class="btn btn-secondary btn-lg"<?php echo !empty($s['cta']['externo']) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo htmlspecialchars($s['cta']['texto']); ?></a>
                        <?php if (!empty($s['cta2'])): ?>
                        <a href="<?php echo htmlspecialchars($s['cta2']['url']); ?>" class="btn btn-outline btn-lg hero-btn-claro"<?php echo !empty($s['cta2']['externo']) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo htmlspecialchars($s['cta2']['texto']); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if ($total > 1): ?>
    <div class="hero-controles">
        <button type="button" class="hero-flecha hero-prev" aria-label="Anterior">&#8249;</button>
        <div class="hero-puntos">
            <?php foreach ($slides as $i => $s): ?>
            <button type="button" class="hero-punto<?php echo $i === $inicio ? ' is-active' : ''; ?>" data-ir="<?php echo $i; ?>"
                    aria-label="<?php echo htmlspecialchars($s['titulo']); ?>"<?php echo $i === $inicio ? ' aria-current="true"' : ''; ?>></button>
            <?php endforeach; ?>
        </div>
        <button type="button" class="hero-flecha hero-next" aria-label="Siguiente">&#8250;</button>
        <button type="button" class="hero-pausa" aria-label="Pausar"><svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path fill="currentColor" d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg></button>
    </div>
    <?php endif; ?>
    </div>

    <div class="hero-fijo">
        <div class="container">
            <h1 class="hero-h1">El Celler Nou, tu tienda de vinos en Premià de Mar</h1>
            <?php echo renderVentajasPedido(); ?>
        </div>
    </div>

</section>
    <?php
    return ob_get_clean();
}
