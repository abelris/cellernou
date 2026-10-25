<?php
/**
 * Poblaciones con reparto a domicilio.
 *
 * Alimenta /vino-a-domicilio, las páginas de cada población, el areaServed
 * del schema de la tienda, el sitemap y la tabla de /politica-de-envios.
 * Todo sale de aquí: si cambia una zona o un plazo, se cambia solo en este
 * archivo.
 *
 * Distancias: en línea recta desde la tienda (C/ Ramón Llull 15) hasta el
 * centro del municipio, con las coordenadas de OpenStreetMap. Se publican
 * redondeadas y como "unos X km": por carretera siempre es algo más.
 *
 * Los textos de cada población son propios. Una página por pueblo con el
 * mismo párrafo cambiando el nombre es lo que Google trata como "doorway
 * pages", así que cada una tiene que decir algo que solo vale para ella.
 */

/**
 * Zonas de reparto. La zona 1 es lo que /politica-de-envios llamaba
 * "Premià de Mar y alrededores"; la 2, el resto de poblaciones cercanas.
 */
define('ZONAS_REPARTO', [
    1 => [
        'nombre'      => 'Entrega rápida',
        'plazo'       => 'Mismo día o día siguiente',
        'gratisDesde' => SHIPPING_FREE_MIN_ORDER,
        'coste'       => 'Gratis a partir de ' . SHIPPING_FREE_MIN_ORDER . ' €',
    ],
    2 => [
        'nombre'      => 'Entrega en 24-48 h',
        'plazo'       => '24 - 48 horas laborables',
        'gratisDesde' => SHIPPING_FREE_MIN_ORDER_ZONA2,
        'coste'       => 'Gratis a partir de ' . SHIPPING_FREE_MIN_ORDER_ZONA2 . ' €',
    ],
]);

/** "5 % de descuento en pedidos a domicilio a partir de 100 €" */
function textoDescuentoPedido() {
    return ORDER_DISCOUNT_PERCENT . ' % de descuento en pedidos a domicilio a partir de ' . ORDER_DISCOUNT_MIN . ' €';
}

/**
 * Franja con las dos ventajas del pedido: envío gratis y descuento.
 * Con $zona, solo el mínimo de esa zona; sin ella, el de las dos.
 */
function renderVentajasPedido($zona = null) {
    if ($zona) {
        $envio = 'Envío gratis a partir de ' . ZONAS_REPARTO[$zona]['gratisDesde'] . ' €';
    } else {
        $envio = 'Envío gratis a partir de ' . SHIPPING_FREE_MIN_ORDER . ' € en Premià y alrededores ('
            . SHIPPING_FREE_MIN_ORDER_ZONA2 . ' € en el resto del Maresme)';
    }
    return '<ul class="ventajas-pedido">'
        . '<li><svg class="icon" aria-hidden="true"><use href="#icon-location"></use></svg>'
        . '<span>' . htmlspecialchars($envio) . '</span></li>'
        . '<li><strong>-' . ORDER_DISCOUNT_PERCENT . ' %</strong>'
        . '<span>' . htmlspecialchars(textoDescuentoPedido()) . '</span></li>'
        . '</ul>';
}

/** Id de la D.O. Alella en `procedencia`. */
define('PROCEDENCIA_DO_ALELLA', 33);

/**
 * Poblaciones, ordenadas por cercanía.
 *
 * - cp:        códigos postales (van al shippingDestination de su zona en el
 *              schema, así que tienen que ser exactos)
 * - km:        distancia aproximada a la tienda
 * - zona:      clave de ZONAS_REPARTO
 * - doAlella:  el municipio está dentro de la D.O. Alella
 * - seleccion: qué productos se enseñan en su página
 *              ['procedencia' => id] | ['tipo' => id, 'sector' => id]
 * - intro:     párrafos propios (sin HTML)
 * - cerca:     slugs de las poblaciones vecinas, para el enlazado interno
 */
function getPoblacionesReparto() {
    static $poblaciones = null;
    if ($poblaciones !== null) return $poblaciones;

    $poblaciones = [
        'premia-de-mar' => [
            'nombre' => 'Premià de Mar',
            'cp' => ['08330'],
            'km' => 0,
            'zona' => 1,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Nuestra tienda está en Premià de Mar, en la calle Ramón Llull, 15. Si vives en el pueblo, '
                . 'puedes pasar a elegir la botella con calma o pedírnosla por WhatsApp o por teléfono y te la '
                . 'llevamos a casa el mismo día o al siguiente.',
                'Premià de Mar es uno de los municipios de la D.O. Alella, la denominación de origen del Maresme. '
                . 'Por eso tenemos siempre una buena selección de vinos de la zona, hechos a pocos kilómetros de '
                . 'tu casa, junto a los grandes de Rioja, Ribera del Duero, Priorat o Penedès.',
            ],
            'cerca' => ['premia-de-dalt', 'vilassar-de-mar', 'vilassar-de-dalt', 'teia'],
        ],
        'premia-de-dalt' => [
            'nombre' => 'Premià de Dalt',
            'cp' => ['08338'],
            'km' => 2,
            'zona' => 1,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Premià de Dalt está a unos 2 km de la tienda: es la población a la que más rápido llegamos. '
                . 'Haz el pedido por WhatsApp o por teléfono y te lo subimos a casa, también a las '
                . 'urbanizaciones de la montaña, el mismo día o al siguiente.',
                'Como Premià de Mar, Premià de Dalt forma parte de la D.O. Alella. Si quieres un vino de tu propia '
                . 'tierra para una comida o para regalar, te aconsejamos entre los que tenemos de la zona.',
            ],
            'cerca' => ['premia-de-mar', 'vilassar-de-dalt', 'teia', 'cabrils'],
        ],
        'vilassar-de-dalt' => [
            'nombre' => 'Vilassar de Dalt',
            'cp' => ['08339'],
            'km' => 3,
            'zona' => 1,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Vilassar de Dalt está a unos 3 km de nuestra tienda de Premià de Mar. Te llevamos el vino a '
                . 'casa el mismo día o al siguiente, y a partir de ' . SHIPPING_FREE_MIN_ORDER
                . ' € el reparto no te cuesta nada.',
                'Vilassar de Dalt es municipio de la D.O. Alella, así que aquí el vino tiene raíces. Además de los '
                . 'vinos de la zona, preparamos lotes para celebraciones, cajas variadas para probar y botellas '
                . 'de regalo con su envoltorio.',
            ],
            'cerca' => ['premia-de-dalt', 'cabrils', 'vilassar-de-mar', 'premia-de-mar'],
        ],
        'vilassar-de-mar' => [
            'nombre' => 'Vilassar de Mar',
            'cp' => ['08340'],
            'km' => 3,
            'zona' => 1,
            'doAlella' => false,
            'seleccion' => ['tipo' => 2, 'sector' => 8],
            'intro' => [
                'Vilassar de Mar está a unos 3 km de la tienda, siguiendo la costa. Si vives allí, haz el pedido '
                . 'por WhatsApp o por teléfono y te lo llevamos a casa el mismo día o al siguiente, sin gastos de '
                . 'envío a partir de ' . SHIPPING_FREE_MIN_ORDER . ' €.',
                'En un pueblo de mar se come pescado y marisco, y para eso lo mejor es un buen blanco o un cava. '
                . 'Te recomendamos blancos de Rías Baixas, Rueda, Penedès o Alella, y conservas de marisco para '
                . 'el aperitivo.',
            ],
            'cerca' => ['premia-de-mar', 'cabrils', 'cabrera-de-mar', 'vilassar-de-dalt'],
        ],
        'teia' => [
            'nombre' => 'Teià',
            'cp' => ['08329'],
            'km' => 3,
            'zona' => 1,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Teià está a unos 3 km de El Celler Nou. Repartimos en el pueblo y en sus urbanizaciones el '
                . 'mismo día o al siguiente; pídenos por WhatsApp, por teléfono o pasa por la tienda.',
                'Teià es tierra de viña de la D.O. Alella. Si te interesan los vinos de la zona, pregúntanos: '
                . 'tenemos blancos de pansa blanca, tintos y espumosos de bodegas del Maresme.',
            ],
            'cerca' => ['el-masnou', 'alella', 'premia-de-dalt', 'premia-de-mar'],
        ],
        'cabrils' => [
            'nombre' => 'Cabrils',
            'cp' => ['08348'],
            'km' => 4,
            'zona' => 1,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Cabrils está a unos 4 km de nuestra tienda. Llevamos el pedido a casa el mismo día o al '
                . 'siguiente, también a las urbanizaciones, y a partir de ' . SHIPPING_FREE_MIN_ORDER
                . ' € el reparto es gratis.',
                'Cabrils forma parte de la D.O. Alella. Para una cena en casa o una reunión con amigos, te '
                . 'preparamos una selección a tu gusto: dinos cuántos sois, qué vais a comer y el presupuesto.',
            ],
            'cerca' => ['vilassar-de-dalt', 'cabrera-de-mar', 'vilassar-de-mar', 'premia-de-dalt'],
        ],
        'el-masnou' => [
            'nombre' => 'El Masnou',
            'cp' => ['08320'],
            'km' => 4,
            'zona' => 1,
            'doAlella' => true,
            'seleccion' => ['tipo' => 2, 'sector' => 8],
            'intro' => [
                'El Masnou está a unos 4 km de la tienda. Repartimos en todo el municipio, también en Ocata y '
                . 'cerca del puerto, el mismo día o al siguiente, y sin gastos de envío a partir de '
                . SHIPPING_FREE_MIN_ORDER . ' €.',
                'El Masnou está dentro de la D.O. Alella y mira al mar: blancos frescos, rosados y cavas para el '
                . 'verano, y tintos con crianza para el invierno. Si no sabes qué elegir, te aconsejamos por '
                . 'WhatsApp.',
            ],
            'cerca' => ['teia', 'alella', 'montgat', 'premia-de-mar'],
        ],
        'cabrera-de-mar' => [
            'nombre' => 'Cabrera de Mar',
            'cp' => ['08349'],
            'km' => 5,
            'zona' => 1,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Cabrera de Mar, a los pies del castillo de Burriac, está a unos 5 km de nuestra tienda de '
                . 'Premià de Mar. Te llevamos el vino a casa el mismo día o al siguiente.',
                'Cabrera de Mar es municipio de la D.O. Alella. Además de vinos de la zona, tenemos cavas, '
                . 'champagnes, licores y cervezas, y preparamos lotes de regalo para empresas y celebraciones.',
            ],
            'cerca' => ['vilassar-de-mar', 'cabrils', 'argentona', 'mataro'],
        ],
        'alella' => [
            'nombre' => 'Alella',
            'cp' => ['08328'],
            'km' => 6,
            'zona' => 1,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Alella, a unos 6 km de la tienda, da nombre a la denominación de origen del Maresme y a su uva '
                . 'más característica, la pansa blanca, como se conoce aquí al xarel·lo. Repartimos en el pueblo '
                . 'y en sus urbanizaciones el mismo día o al siguiente.',
                'Si vives en Alella seguramente conoces bien los vinos de casa. Por eso, junto a los de la '
                . 'D.O. Alella, te proponemos lo que no tienes tan a mano: Priorat, Ribera del Duero, Rioja, '
                . 'champagnes, destilados y conservas gourmet.',
            ],
            'cerca' => ['teia', 'el-masnou', 'tiana', 'montgat'],
        ],
        'orrius' => [
            'nombre' => 'Òrrius',
            'cp' => ['08317'],
            'km' => 7,
            'zona' => 2,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Òrrius está a unos 7 km de nuestra tienda, al otro lado de la sierra. Llevamos el pedido a '
                . 'casa en 24 a 48 horas, sin gastos de envío a partir de ' . SHIPPING_FREE_MIN_ORDER_ZONA2
                . ' €; escríbenos por WhatsApp y te confirmamos el día.',
                'Òrrius también forma parte de la D.O. Alella. Si buscas vino de la zona, cava para una '
                . 'celebración o un buen destilado, te lo llevamos sin que tengas que bajar a la costa.',
            ],
            'cerca' => ['argentona', 'vilassar-de-dalt', 'cabrils', 'cabrera-de-mar'],
        ],
        'montgat' => [
            'nombre' => 'Montgat',
            'cp' => ['08390'],
            'km' => 7,
            'zona' => 2,
            'doAlella' => true,
            'seleccion' => ['sector' => 14],
            'intro' => [
                'Montgat está a unos 7 km de la tienda, siguiendo la costa hacia Barcelona. Llevamos el pedido '
                . 'a casa en 24 a 48 horas laborables; escríbenos y te confirmamos el día de entrega.',
                'Montgat es el municipio de la D.O. Alella más cercano a Barcelona. Para una comida junto al mar, '
                . 'un cava o un blanco fresco; para las fiestas, champagne y lotes de regalo preparados en la '
                . 'tienda.',
            ],
            'cerca' => ['el-masnou', 'tiana', 'badalona', 'alella'],
        ],
        'argentona' => [
            'nombre' => 'Argentona',
            'cp' => ['08310'],
            'km' => 8,
            'zona' => 2,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Argentona está a unos 8 km de nuestra tienda de Premià de Mar. Te llevamos el vino a casa en '
                . '24 a 48 horas laborables; para fechas concretas, como la Festa del Càntir de agosto o las '
                . 'Navidades, avísanos con unos días de margen.',
                'Argentona forma parte de la D.O. Alella. Tenemos vinos de la zona y del resto de España, cavas, '
                . 'licores y cervezas, y preparamos pedidos grandes para comidas familiares y empresas.',
            ],
            'cerca' => ['cabrera-de-mar', 'mataro', 'orrius', 'cabrils'],
        ],
        'tiana' => [
            'nombre' => 'Tiana',
            'cp' => ['08391'],
            'km' => 8,
            'zona' => 2,
            'doAlella' => true,
            'seleccion' => ['procedencia' => PROCEDENCIA_DO_ALELLA],
            'intro' => [
                'Tiana está a unos 8 km de la tienda. Repartimos en el pueblo y en sus urbanizaciones en 24 a '
                . '48 horas laborables; pídenos por WhatsApp o por teléfono.',
                'Tiana es municipio de la D.O. Alella, con viñedos entre la sierra de Marina y el mar. Te '
                . 'aconsejamos entre los vinos de la zona y te ayudamos a montar una bodega en casa con botellas '
                . 'para cada ocasión.',
            ],
            'cerca' => ['montgat', 'alella', 'el-masnou', 'badalona'],
        ],
        'mataro' => [
            'nombre' => 'Mataró',
            'cp' => ['08301', '08302', '08303', '08304'],
            'km' => 9,
            'zona' => 2,
            'doAlella' => false,
            'seleccion' => ['tipo' => 1, 'sector' => 8],
            'intro' => [
                'Mataró, la capital del Maresme, está a unos 9 km de nuestra tienda. Llevamos el pedido a '
                . 'cualquier barrio en 24 a 48 horas laborables. Para Les Santes, a finales de julio, o para '
                . 'Navidad, pídelo con unos días de antelación.',
                'Somos una tienda de barrio con más de 1.000 referencias: tintos de Rioja, Ribera del Duero y '
                . 'Priorat, blancos, cavas y champagnes, whisky, ron y ginebra, cervezas y conservas. Te '
                . 'asesoramos por WhatsApp como si estuvieras en el mostrador.',
            ],
            'cerca' => ['argentona', 'cabrera-de-mar', 'sant-andreu-de-llavaneres', 'vilassar-de-mar'],
        ],
        'badalona' => [
            'nombre' => 'Badalona',
            'cp' => ['08911', '08912', '08913', '08914', '08915', '08916', '08917', '08918'],
            'km' => 11,
            'zona' => 2,
            'doAlella' => false,
            'seleccion' => ['tipo' => 1, 'sector' => 8],
            'intro' => [
                'Badalona está a unos 11 km de la tienda. Repartimos en 24 a 48 horas laborables; para fechas '
                . 'señaladas, como la Nit de Sant Anastasi en mayo o las fiestas de fin de año, reserva el '
                . 'pedido con margen.',
                'Si buscas una botella concreta que no encuentras en el supermercado, pregúntanos: tenemos '
                . 'más de 1.000 referencias y, si no la tenemos, te decimos si podemos conseguirla.',
            ],
            'cerca' => ['montgat', 'tiana', 'el-masnou', 'alella'],
        ],
        'sant-andreu-de-llavaneres' => [
            'nombre' => 'Sant Andreu de Llavaneres',
            'cp' => ['08392'],
            'km' => 14,
            'zona' => 2,
            'doAlella' => false,
            'seleccion' => ['tipo' => 2, 'sector' => 8],
            'intro' => [
                'Sant Andreu de Llavaneres está a unos 14 km de nuestra tienda, pasado Mataró. Llevamos el '
                . 'pedido a casa en 24 a 48 horas laborables, y a partir de ' . SHIPPING_FREE_MIN_ORDER_ZONA2
                . ' € el envío es gratis; escríbenos y te confirmamos el día.',
                'Llavaneres es tierra del pèsol garrofal, y con un plato de guisantes de temporada pocas cosas '
                . 'quedan mejor que un blanco con algo de cuerpo o un cava brut nature. Te ayudamos a elegir.',
            ],
            'cerca' => ['mataro', 'sant-vicenc-de-montalt', 'argentona', 'cabrera-de-mar'],
        ],
        'sant-vicenc-de-montalt' => [
            'nombre' => 'Sant Vicenç de Montalt',
            'cp' => ['08394'],
            'km' => 16,
            'zona' => 2,
            'doAlella' => false,
            'seleccion' => ['sector' => 14],
            'intro' => [
                'Sant Vicenç de Montalt está a unos 16 km de la tienda. Llevamos el pedido a casa en 24 a 48 '
                . 'horas laborables, también a las urbanizaciones; pídenos por WhatsApp o por teléfono.',
                'Para una celebración en casa preparamos el pedido completo: cava o champagne para el brindis, '
                . 'vino para la comida y un destilado para la sobremesa. Dinos cuántos sois y el presupuesto.',
            ],
            'cerca' => ['sant-andreu-de-llavaneres', 'mataro', 'argentona', 'cabrera-de-mar'],
        ],
    ];

    return $poblaciones;
}

/** Una población por su slug, o null. */
function getPoblacionReparto($slug) {
    $poblaciones = getPoblacionesReparto();
    return $poblaciones[$slug] ?? null;
}

/** Poblaciones de una zona de reparto. */
function getPoblacionesPorZona($zona) {
    return array_filter(getPoblacionesReparto(), function ($p) use ($zona) {
        return $p['zona'] === $zona;
    });
}

/** Códigos postales de una zona (para el shippingDestination del schema). */
function getCodigosPostalesZona($zona) {
    $cps = [];
    foreach (getPoblacionesPorZona($zona) as $p) {
        $cps = array_merge($cps, $p['cp']);
    }
    return array_values(array_unique($cps));
}

/** Lista legible: "A, B, C y D". */
function listaNombresPoblaciones(array $poblaciones) {
    $nombres = array_values(array_map(function ($p) { return $p['nombre']; }, $poblaciones));
    if (count($nombres) <= 1) return implode('', $nombres);
    $ultimo = array_pop($nombres);
    return implode(', ', $nombres) . ' y ' . $ultimo;
}

/** Enlace de WhatsApp con el mensaje del pedido ya escrito. */
function whatsappPedidoUrl($poblacion = null) {
    $numero = preg_replace('/[^0-9]/', '', SITE_PHONE_MOBILE);
    $texto  = $poblacion
        ? 'Hola, quiero pedir vino a domicilio en ' . $poblacion . '.'
        : 'Hola, quiero hacer un pedido a domicilio.';
    return 'https://wa.me/' . $numero . '?text=' . rawurlencode($texto);
}

/**
 * Productos para la página de una población: solo con foto y con precio
 * visible, para que el escaparate no se llene de "Consultar".
 */
function getSeleccionPoblacion($poblacion, $limit = 8) {
    $sel = $poblacion['seleccion'];

    if (!empty($sel['procedencia'])) {
        $productos = getProductosByProcedencia($sel['procedencia']);
    } elseif (!empty($sel['tipo'])) {
        $productos = getProductosByTipo($sel['tipo'], $sel['sector'] ?? null);
    } else {
        $productos = getProductosBySector($sel['sector']);
    }

    $productos = array_values(array_filter($productos, function ($p) {
        return productoTieneFoto($p) && $p['precio_venta'] > 0 && !empty($p['tiene_stock']);
    }));

    // Orden estable por población: la página no cambia en cada visita (lo
    // que confunde a Google), pero cada pueblo enseña botellas distintas.
    $semilla = crc32($poblacion['nombre']);
    usort($productos, function ($a, $b) use ($semilla) {
        return crc32($semilla . '-' . $a['id']) <=> crc32($semilla . '-' . $b['id']);
    });

    return array_slice($productos, 0, $limit);
}
