<?php
/**
 * Preguntas frecuentes por categoría.
 *
 * Se pintan VISIBLES al pie de cada listado y alimentan el schema FAQPage.
 * Marcar un FAQ que el usuario no puede leer incumple las directrices de
 * Google, así que las dos cosas salen siempre de este mismo array.
 *
 * Los datos de tienda (plazos, devoluciones, envío, horario) se toman de las
 * constantes de config.php y de las páginas legales: si cambian allí, hay que
 * cambiarlos aquí.
 */

/**
 * Respuestas comunes a todas las categorías, construidas con datos reales.
 */
function faqsTienda() {
    $envioMin = SHIPPING_FREE_MIN_ORDER;
    $dias     = RETURN_POLICY_DAYS;

    return [
        'envio' => [
            'pregunta'  => '¿Hacéis envíos y cuánto tardan?',
            'respuesta' => 'Sí. En Premià de Mar y alrededores ('
                . listaNombresPoblaciones(getPoblacionesPorZona(1)) . ') el envío es gratuito a partir de '
                . $envioMin . ' € y se entrega el mismo día o el siguiente. '
                . 'En ' . listaNombresPoblaciones(getPoblacionesPorZona(2)) . ' entregamos en 24 a 48 horas, '
                . 'gratis a partir de ' . SHIPPING_FREE_MIN_ORDER_ZONA2 . ' €. En estas poblaciones, los pedidos a '
                . 'domicilio de ' . ORDER_DISCOUNT_MIN . ' € o más tienen además un ' . ORDER_DISCOUNT_PERCENT
                . ' % de descuento. '
                . 'En el área metropolitana de Barcelona y el resto de Cataluña el plazo es de '
                . '24 a 48 horas laborables, y en la península de 48 a 72 horas. Todos los pedidos '
                . 'se preparan en cajas reforzadas con separadores individuales para cada botella. '
                . 'También puedes recoger el pedido gratis en la tienda de ' . SITE_STREET . ', ' . SITE_LOCALITY . '.'
        ],
        'devolucion' => [
            'pregunta'  => '¿Puedo devolver una botella si no me convence?',
            'respuesta' => 'Tienes ' . $dias . ' días naturales desde la recepción para desistir de la compra. '
                . 'La botella debe estar sin abrir, con el precinto original intacto y en un embalaje que '
                . 'garantice su protección durante el transporte. Los gastos de la devolución corren a cargo '
                . 'del cliente salvo que el producto llegara dañado, defectuoso o equivocado: en ese caso '
                . 'asumimos nosotros la recogida y el reenvío. Si alguna botella llega rota, indícalo en el '
                . 'albarán del transportista y avísanos en 24 horas con fotos.'
        ],
        'horario' => [
            'pregunta'  => '¿Cuál es el horario de la tienda?',
            'respuesta' => 'Abrimos de lunes a jueves de 9:30 a 14:00 y de 17:00 a 20:30; los viernes de '
                . '9:30 a 13:30 y de 17:00 a 20:30; los sábados de 9:30 a 14:00 y los domingos de 10:00 a 14:00. '
                . 'Estamos en ' . SITE_STREET . ', ' . SITE_POSTAL_CODE . ' ' . SITE_LOCALITY . '. '
                . 'También puedes consultarnos por teléfono en el ' . SITE_PHONE . '.'
        ],
        'asesoramiento' => [
            'pregunta'  => '¿Podéis aconsejarme si no sé qué elegir?',
            'respuesta' => 'Sí, es parte de lo que hacemos. Cuéntanos la ocasión, el plato con el que quieres '
                . 'acompañarlo y el presupuesto, y te proponemos opciones concretas. Puedes escribirnos desde '
                . 'la página de contacto, llamarnos al ' . SITE_PHONE . ' o pasar por la tienda.'
        ],
    ];
}

/**
 * Devolver el bloque de FAQ de una categoría.
 * @param string $categoria vinos | cavas | licores | cervezas | conservas
 * @return array<int, array{pregunta:string, respuesta:string}>
 */
function getFaqsCategoria($categoria) {
    $tienda = faqsTienda();

    $porCategoria = [

        // ---------------------------------------------------------
        'vinos' => [
            [
                'pregunta'  => '¿Qué significa D.O. en un vino?',
                'respuesta' => 'D.O. son las siglas de Denominación de Origen: un sello que certifica que la uva '
                    . 'procede de una zona delimitada y que el vino se ha elaborado siguiendo el reglamento de su '
                    . 'consejo regulador, que fija variedades permitidas, rendimientos máximos y tiempos mínimos de '
                    . 'crianza. No es una nota de calidad, sino una garantía de origen y de método. En España hay '
                    . 'cerca de setenta, desde Rioja o Ribera del Duero hasta denominaciones pequeñas como Terra Alta '
                    . 'o Montsant. Por encima está la D.O.Ca. (Denominación de Origen Calificada), que solo tienen '
                    . 'Rioja y Priorat.'
            ],
            [
                'pregunta'  => '¿Cuál es la diferencia entre crianza, reserva y gran reserva?',
                'respuesta' => 'Es el tiempo mínimo de envejecimiento antes de salir a la venta. En tintos, un crianza '
                    . 'envejece al menos 24 meses, de los que 6 como mínimo van en barrica de roble. Un reserva suma '
                    . '36 meses con un mínimo de 12 en barrica. Un gran reserva llega a 60 meses, con al menos 18 en '
                    . 'barrica y el resto en botella. En blancos y rosados los plazos son más cortos. Más crianza no '
                    . 'significa mejor vino: significa un perfil distinto, con más notas de madera, especias y cuero, '
                    . 'y menos fruta fresca.'
            ],
            [
                'pregunta'  => '¿A qué temperatura debo servir cada vino?',
                'respuesta' => 'Los blancos jóvenes y los rosados, entre 8 y 10 °C. Los blancos con crianza en barrica, '
                    . 'entre 10 y 12 °C, porque en exceso de frío pierden aroma. Los tintos jóvenes, entre 14 y 16 °C. '
                    . 'Los tintos con crianza, entre 16 y 18 °C. La "temperatura ambiente" de la que se habla en los '
                    . 'libros antiguos era la de una casa sin calefacción: un tinto a 22 °C sabe alcohólico y plano. '
                    . 'Si dudas, es mejor pasarse de frío y dejar que la copa se temple en la mano.'
            ],
            [
                'pregunta'  => '¿Cómo debo guardar el vino en casa?',
                'respuesta' => 'Tumbado, para que el corcho siga en contacto con el líquido y no se seque; en un sitio '
                    . 'oscuro, sin vibraciones y con temperatura estable entre 12 y 16 °C. Lo que más daña una botella '
                    . 'no es el calor en sí, sino los cambios bruscos de temperatura. Evita la parte alta de la cocina '
                    . 'y las zonas cercanas a electrodomésticos. Una botella abierta aguanta de tres a cinco días en la '
                    . 'nevera con un tapón hermético; los tintos también, sacándolos un rato antes de servir.'
            ],
            [
                'pregunta'  => '¿Todos los vinos mejoran con los años?',
                'respuesta' => 'No. La gran mayoría de los vinos que se venden están pensados para beberse en los dos o '
                    . 'tres años siguientes a su cosecha, y esperar solo los apaga. Solo guardan bien los vinos con '
                    . 'estructura suficiente: buena acidez, taninos y concentración. Si tienes una botella y no sabes '
                    . 'si aguanta, pregúntanos: es más fácil acertar sabiendo la añada y la bodega.'
            ],
        ],

        // ---------------------------------------------------------
        'cavas' => [
            [
                'pregunta'  => '¿Qué diferencia hay entre cava y champagne?',
                'respuesta' => 'Ambos son espumosos elaborados por el método tradicional, con la segunda fermentación '
                    . 'dentro de la propia botella. Lo que cambia es el origen y la uva. El champagne procede de la '
                    . 'región francesa de Champaña y se elabora sobre todo con chardonnay, pinot noir y pinot meunier. '
                    . 'El cava se elabora en España, mayoritariamente en el Penedès, con macabeo, xarel·lo y parellada, '
                    . 'aunque también admite chardonnay y pinot noir. El clima mediterráneo da un perfil más maduro y '
                    . 'menos afilado que el del champagne.'
            ],
            [
                'pregunta'  => '¿Qué quiere decir brut nature, brut o semi seco?',
                'respuesta' => 'Es el azúcar que lleva el vino después del degüelle, medido en gramos por litro. '
                    . 'Brut nature: menos de 3 g/l y sin azúcar añadido. Extra brut: hasta 6 g/l. Brut: hasta 12 g/l, '
                    . 'que es el estilo más vendido. Extra seco: de 12 a 17. Seco: de 17 a 32. Semi seco: de 32 a 50. '
                    . 'Dulce: más de 50. Cuanto menos azúcar, más se nota la crianza y la acidez, así que un brut nature '
                    . 'exige un vino base mejor: no hay dulzor donde esconderse.'
            ],
            [
                'pregunta'  => '¿Cuánto tiempo de crianza tiene un cava reserva?',
                'respuesta' => 'Un cava de guarda envejece un mínimo de 9 meses sobre sus lías. El cava reserva llega a '
                    . '18 meses y el gran reserva a 30, y este último solo puede ser brut nature, extra brut o brut. '
                    . 'Ese contacto con las lías es lo que aporta las notas de bollería, frutos secos y pan tostado, y '
                    . 'lo que hace que la burbuja sea más fina e integrada.'
            ],
            [
                'pregunta'  => '¿Cómo se sirve y se conserva un espumoso?',
                'respuesta' => 'Entre 6 y 8 °C, en copa tipo tulipa y no en copa ancha: la flauta estrecha conserva la '
                    . 'burbuja pero cierra el aroma, y la copa plana lo pierde todo. Enfría la botella en una cubitera '
                    . 'con agua y hielo durante 20 o 30 minutos, no en el congelador. Al abrir, sujeta el corcho y gira '
                    . 'la botella, no el corcho. Una vez abierta se conserva uno o dos días en la nevera con un tapón '
                    . 'de presión; la cucharilla en el cuello es un mito y no funciona.'
            ],
            [
                'pregunta'  => '¿Qué espumoso elijo para brindar y cuál para toda la comida?',
                'respuesta' => 'Para un brindis o un aperitivo funciona bien un brut joven, fresco y directo. Si quieres '
                    . 'que el espumoso acompañe toda la comida, busca un reserva o gran reserva brut nature: tiene más '
                    . 'cuerpo y crianza y aguanta platos con más peso, desde arroces hasta pescados en salsa. Cuéntanos '
                    . 'el menú y te lo concretamos.'
            ],
        ],

        // ---------------------------------------------------------
        'licores' => [
            [
                'pregunta'  => '¿Qué diferencia hay entre un single malt y un blended?',
                'respuesta' => 'Un single malt es un whisky elaborado con cebada malteada en una sola destilería; '
                    . '"single" se refiere a la destilería, no a un único barril. Un blended mezcla whiskies de malta '
                    . 'con whiskies de grano de varias destilerías, buscando un perfil constante año tras año. El single '
                    . 'malt suele tener más carácter y refleja mejor su origen; el blended es más suave y equilibrado, '
                    . 'y suele funcionar mejor en combinados.'
            ],
            [
                'pregunta'  => '¿Qué significa la edad que aparece en la etiqueta?',
                'respuesta' => 'Es la edad del componente MÁS JOVEN de la botella, no la media. Un whisky de 12 años '
                    . 'puede llevar destilados de 12, 15 y 18 años, pero nunca ninguno de 10. Las botellas sin edad '
                    . 'declarada (NAS, "no age statement") no son necesariamente peores: simplemente el productor ha '
                    . 'preferido no comprometerse con una cifra mínima.'
            ],
            [
                'pregunta'  => '¿Se estropea una botella de licor una vez abierta?',
                'respuesta' => 'Los destilados de alta graduación (whisky, ron, ginebra, vodka, brandy) no se estropean, '
                    . 'pero se oxidan poco a poco y pierden aroma, sobre todo cuando queda poco líquido y mucho aire en '
                    . 'la botella. Guárdalos de pie, no tumbados: el alcohol acabaría dañando el corcho. En cambio, los '
                    . 'vermuts, los licores cremosos y todo lo que baja de 20 grados sí deben ir a la nevera una vez '
                    . 'abiertos y se consumen en semanas, no en años.'
            ],
            [
                'pregunta'  => '¿Qué es una ginebra London Dry?',
                'respuesta' => 'Es una categoría legal, no una indicación geográfica: puede elaborarse en cualquier '
                    . 'parte del mundo. Exige que todos los aromas se incorporen por destilación, que no se añadan '
                    . 'colorantes y que el azúcar sea mínimo. El enebro debe ser el botánico dominante. Frente a ella, '
                    . 'las ginebras contemporáneas o "new western" dan más protagonismo a los cítricos y las flores y '
                    . 'admiten aromatizado posterior.'
            ],
            [
                'pregunta'  => '¿Cómo se sirve el vermut?',
                'respuesta' => 'Frío, entre 6 y 8 °C, en vaso ancho con hielo abundante y una rodaja de naranja; la '
                    . 'aceituna es opcional y más catalana que canónica. Un buen vermut se bebe solo, sin sifón, aunque '
                    . 'rebajarlo con un chorro de soda es costumbre en muchos sitios. Una vez abierto, a la nevera: al '
                    . 'ser vino fortificado se oxida en pocas semanas.'
            ],
        ],

        // ---------------------------------------------------------
        'cervezas' => [
            [
                'pregunta'  => '¿Qué diferencia hay entre una lager y una ale?',
                'respuesta' => 'La levadura y la temperatura de fermentación. Las lager fermentan en frío, entre 7 y '
                    . '13 °C, con levaduras que trabajan en el fondo del tanque, y dan cervezas limpias, secas y con el '
                    . 'malta y el lúpulo en primer plano. Las ale fermentan entre 15 y 24 °C con levaduras de superficie '
                    . 'y generan ésteres: notas afrutadas de plátano, manzana o cítricos. Casi todas las cervezas '
                    . 'industriales españolas son lager; la mayoría de la cerveza artesana es ale.'
            ],
            [
                'pregunta'  => '¿Qué es una IPA y por qué amarga tanto?',
                'respuesta' => 'IPA son las siglas de India Pale Ale, un estilo nacido en el Reino Unido en el siglo XIX '
                    . 'con más lúpulo y más alcohol de lo habitual. El lúpulo aporta el amargor y los aromas cítricos, '
                    . 'resinosos o tropicales que la definen. Hay muchas variantes: la American IPA es amarga y '
                    . 'resinosa, la New England IPA es turbia, suave y muy afrutada, y la Session IPA mantiene el aroma '
                    . 'bajando la graduación.'
            ],
            [
                'pregunta'  => '¿A qué temperatura se sirve cada cerveza?',
                'respuesta' => 'Muy fría solo la lager ligera, entre 4 y 6 °C. Las lager con más cuerpo y las cervezas '
                    . 'de trigo, entre 6 y 8 °C. Las ale, las IPA y las belgas, entre 8 y 12 °C. Las stout, las porter y '
                    . 'las cervezas de alta graduación, entre 10 y 13 °C. El frío extremo anestesia el paladar y esconde '
                    . 'los defectos, pero también los aromas: una buena artesana a 3 °C no sabe a nada.'
            ],
            [
                'pregunta'  => '¿Cómo se guarda la cerveza en casa?',
                'respuesta' => 'De pie, no tumbada, para reducir la superficie en contacto con el aire y evitar que el '
                    . 'poso se reparta. En un sitio oscuro y fresco: la luz, sobre todo a través del vidrio verde o '
                    . 'transparente, degrada el lúpulo en minutos y provoca el llamado sabor a "cerveza pasada". Las IPA '
                    . 'se beben cuanto más frescas mejor, en pocos meses; las cervezas oscuras y fuertes sí pueden '
                    . 'evolucionar bien durante años.'
            ],
        ],

        // ---------------------------------------------------------
        'conservas' => [
            [
                'pregunta'  => '¿Qué significan los números de las latas, como 25/35?',
                'respuesta' => 'Indican cuántas piezas hay en la lata. Un 25/35 de berberechos son entre 25 y 35 piezas '
                    . 'en ese formato: cuanto MENOR es el número, más grande es cada pieza y mayor la calidad y el '
                    . 'precio. Es el criterio de referencia en berberechos, almejas y navajas, más fiable que el peso '
                    . 'total, porque el peso incluye el líquido de gobierno.'
            ],
            [
                'pregunta'  => '¿Es verdad que las conservas mejoran con el tiempo?',
                'respuesta' => 'En algunos casos sí. Los productos en aceite de oliva, sobre todo el bonito, la ventresca '
                    . 'y las sardinas, se afinan durante los primeros años porque la carne va absorbiendo el aceite y la '
                    . 'textura se vuelve más untuosa. La recomendación clásica es voltear la lata cada cierto tiempo para '
                    . 'que el reparto sea uniforme. Los mariscos en salmuera, como los berberechos, no ganan nada '
                    . 'esperando y es mejor consumirlos jóvenes.'
            ],
            [
                'pregunta'  => '¿Cómo se conservan y cómo se sirven?',
                'respuesta' => 'La lata cerrada va en un sitio seco, fresco y oscuro, siempre por debajo de 25 °C. Una '
                    . 'vez abierta, pasa el contenido a un recipiente de vidrio con su propio líquido y consúmelo en dos '
                    . 'o tres días: no guardes nada dentro de la lata abierta. Sácalas de la nevera unos veinte minutos '
                    . 'antes de servir, porque en frío la grasa del aceite se solidifica y apaga el sabor.'
            ],
            [
                'pregunta'  => '¿Con qué se acompañan unas buenas conservas?',
                'respuesta' => 'Con un blanco de acidez marcada o con un espumoso brut nature: la burbuja y la acidez '
                    . 'limpian la untuosidad del aceite. Un albariño o un txakoli funcionan de manual con berberechos y '
                    . 'mejillones. Para los ahumados y las sardinas, un manzanilla o un fino frío. Y para el vermut de '
                    . 'los domingos, mejillones en escabeche: es el maridaje más clásico que hay.'
            ],
        ],
    ];

    if (!isset($porCategoria[$categoria])) {
        return [];
    }

    // Cada categoría cierra con las preguntas de tienda, que son las que
    // resuelven la duda práctica antes de comprar.
    return array_merge($porCategoria[$categoria], [
        $tienda['envio'],
        $tienda['devolucion'],
        $tienda['asesoramiento'],
        $tienda['horario'],
    ]);
}

/**
 * Render del bloque de FAQ.
 *
 * Usa <details>/<summary>, que es plegable sin JavaScript y cuyo contenido
 * Google lee igualmente: no es contenido oculto a efectos de las directrices.
 */
function renderFaqs($faqs, $titulo = 'Preguntas frecuentes') {
    if (empty($faqs)) return '';

    $html  = '<section class="section faq-section">';
    $html .= '<div class="container">';
    $html .= '<div class="section-header"><h2>' . htmlspecialchars($titulo) . '</h2></div>';
    $html .= '<div class="faq-list">';

    foreach ($faqs as $i => $faq) {
        $html .= '<details class="faq-item"' . ($i === 0 ? ' open' : '') . '>';
        $html .= '<summary class="faq-question">' . htmlspecialchars($faq['pregunta']) . '</summary>';
        $html .= '<div class="faq-answer"><p>' . htmlspecialchars($faq['respuesta']) . '</p></div>';
        $html .= '</details>';
    }

    $html .= '</div></div></section>';

    return $html;
}
