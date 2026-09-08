<?php
/**
 * Funciones SEO - Schema.org, Open Graph, Meta tags
 *
 * Reglas de la casa:
 *  - Toda URL que salga en un schema, en el canonical o en Open Graph es ABSOLUTA.
 *    schema.org espera URIs, y Facebook/WhatsApp no resuelven rutas relativas.
 *  - Las entidades comparten @id (#tienda, #website) para que Google las una
 *    en un solo grafo en lugar de tratarlas como islas sueltas.
 *  - Nada de propiedades vacías: si el dato no existe, la propiedad no se emite.
 *  - Lo que se marca tiene que coincidir con lo que ve el usuario (precio,
 *    disponibilidad). Un desajuste suprime el resultado enriquecido.
 */

// =============================================================
// HELPERS
// =============================================================

/**
 * Convertir una ruta en URL absoluta sobre SITE_URL.
 * Independiente del entorno: en local getBaseUrl() devuelve '' para que los
 * assets carguen del host actual, pero el schema siempre lleva dominio.
 */
function schemaUrl($path) {
    if ($path === null || $path === '') return null;
    if (preg_match('#^https?://#i', $path)) return $path;
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/** @id estable de la tienda: todas las referencias apuntan aquí. */
function schemaTiendaId() {
    return rtrim(SITE_URL, '/') . '/#tienda';
}

/** @id estable del sitio web. */
function schemaWebSiteId() {
    return rtrim(SITE_URL, '/') . '/#website';
}

/** Serializar un array de schema como bloque <script>. */
function schemaJson(array $schema) {
    return '<script type="application/ld+json">'
        . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";
}

/** Quitar del array las claves con valor null o cadena vacía (recursivo). */
function schemaLimpiar(array $a) {
    foreach ($a as $k => $v) {
        if (is_array($v)) {
            $v = schemaLimpiar($v);
            if (empty($v)) { unset($a[$k]); continue; }
            $a[$k] = $v;
        } elseif ($v === null || $v === '') {
            unset($a[$k]);
        }
    }
    return $a;
}

/** Normalizar una fecha de MySQL a ISO 8601 con zona horaria. */
function schemaFecha($fecha) {
    if (empty($fecha) || strpos($fecha, '0000-00-00') === 0) return null;
    $ts = strtotime($fecha);
    if (!$ts) return null;
    return date('c', $ts);
}

// =============================================================
// META TAGS
// =============================================================

/**
 * Generar meta tags SEO.
 *
 * @param array $datos title, description, url, image, type y opcionalmente:
 *        'robots'      => 'noindex, follow' para búsquedas internas y errores
 *        'imageWidth'  => ancho de la og:image (mejora la vista previa)
 *        'imageHeight' => alto de la og:image
 *        'prev' / 'next' => URLs de paginación
 */
function generarMetaTags($datos) {
    $title       = isset($datos['title']) ? htmlspecialchars($datos['title']) : SITE_NAME;
    $description = isset($datos['description']) ? htmlspecialchars($datos['description']) : SITE_DESCRIPTION;
    $url         = schemaUrl($datos['url'] ?? $_SERVER['REQUEST_URI']);
    $image       = schemaUrl($datos['image'] ?? '/images/og-default.jpg');
    $type        = $datos['type'] ?? 'website';

    // index/follow por defecto; las páginas de baja calidad (búsqueda interna,
    // 404) pasan 'noindex, follow' para no ensuciar el índice.
    $robots = $datos['robots'] ?? 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';

    $meta = '';

    // Meta básicos
    $meta .= '<meta name="description" content="' . $description . '">' . "\n";
    $meta .= '<meta name="robots" content="' . htmlspecialchars($robots) . '">' . "\n";
    $meta .= '<link rel="canonical" href="' . htmlspecialchars($url) . '">' . "\n";

    // Paginación: Google ya no la usa como señal, pero Bing y otros sí.
    if (!empty($datos['prev'])) {
        $meta .= '<link rel="prev" href="' . htmlspecialchars(schemaUrl($datos['prev'])) . '">' . "\n";
    }
    if (!empty($datos['next'])) {
        $meta .= '<link rel="next" href="' . htmlspecialchars(schemaUrl($datos['next'])) . '">' . "\n";
    }

    // Open Graph
    $meta .= '<meta property="og:type" content="' . $type . '">' . "\n";
    $meta .= '<meta property="og:title" content="' . $title . '">' . "\n";
    $meta .= '<meta property="og:description" content="' . $description . '">' . "\n";
    $meta .= '<meta property="og:url" content="' . htmlspecialchars($url) . '">' . "\n";
    $meta .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
    if (!empty($datos['imageWidth']) && !empty($datos['imageHeight'])) {
        $meta .= '<meta property="og:image:width" content="' . intval($datos['imageWidth']) . '">' . "\n";
        $meta .= '<meta property="og:image:height" content="' . intval($datos['imageHeight']) . '">' . "\n";
    }
    $meta .= '<meta property="og:image:alt" content="' . $title . '">' . "\n";
    $meta .= '<meta property="og:site_name" content="' . SITE_NAME . '">' . "\n";
    $meta .= '<meta property="og:locale" content="es_ES">' . "\n";

    // Twitter Card
    $meta .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $meta .= '<meta name="twitter:title" content="' . $title . '">' . "\n";
    $meta .= '<meta name="twitter:description" content="' . $description . '">' . "\n";
    $meta .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">' . "\n";

    return $meta;
}

// =============================================================
// ENTIDADES GLOBALES (se emiten en todas las páginas)
// =============================================================

/**
 * Ficha del negocio local.
 *
 * WineStore es un subtipo de LocalBusiness que describe exactamente lo que es
 * la tienda; Store era demasiado genérico. El @id permite que el seller de
 * cada producto y el publisher de cada artículo apunten a esta misma entidad
 * en lugar de crear organizaciones duplicadas.
 */
function schemaOrganizacion() {
    $direccion = schemaLimpiar([
        '@type'           => 'PostalAddress',
        'streetAddress'   => SITE_STREET,
        'addressLocality' => SITE_LOCALITY,
        'addressRegion'   => SITE_REGION,
        'postalCode'      => SITE_POSTAL_CODE,
        'addressCountry'  => SITE_COUNTRY
    ]);

    $horarios = [];
    foreach (SITE_OPENING_HOURS as $tramo) {
        $horarios[] = [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => $tramo['dias'],
            'opens'     => $tramo['opens'],
            'closes'    => $tramo['closes']
        ];
    }

    $schema = [
        '@context'          => 'https://schema.org',
        '@type'             => 'WineStore',
        '@id'               => schemaTiendaId(),
        'name'              => SITE_NAME,
        'legalName'         => COMPANY_LEGAL_NAME,
        'vatID'             => COMPANY_VAT_ID,
        'description'       => SITE_DESCRIPTION,
        'url'               => SITE_URL,
        'email'             => SITE_EMAIL,
        'telephone'         => SITE_PHONE,
        'image'             => schemaUrl('/images/og-default.jpg'),
        'logo'              => [
            '@type'  => 'ImageObject',
            'url'    => schemaUrl('/images/logo.png'),
            'width'  => 600,
            'height' => 600
        ],
        'address'           => $direccion,
        'priceRange'        => '€€',
        'currenciesAccepted' => 'EUR',
        'paymentAccepted'   => 'Efectivo, Tarjeta de crédito, Transferencia bancaria',
        'areaServed'        => [
            '@type' => 'AdministrativeArea',
            'name'  => 'Barcelona, Catalunya'
        ],
        'openingHoursSpecification' => $horarios
    ];

    // Coordenadas solo si están verificadas: unas inventadas mandan a los
    // usuarios de Maps al sitio equivocado.
    if (SITE_GEO_LAT !== null && SITE_GEO_LNG !== null) {
        $schema['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => SITE_GEO_LAT,
            'longitude' => SITE_GEO_LNG
        ];
        $schema['hasMap'] = 'https://www.google.com/maps/search/?api=1&query='
            . SITE_GEO_LAT . ',' . SITE_GEO_LNG;
    }

    if (!empty(SITE_SAME_AS)) {
        $schema['sameAs'] = SITE_SAME_AS;
    }

    return schemaJson(schemaLimpiar($schema));
}

/**
 * WebSite con SearchAction (caja de búsqueda de enlaces de sitio).
 */
function schemaWebSite() {
    $schema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'WebSite',
        '@id'        => schemaWebSiteId(),
        'name'       => SITE_NAME,
        'url'        => SITE_URL,
        'inLanguage' => 'es-ES',
        'publisher'  => ['@id' => schemaTiendaId()],
        'potentialAction' => [
            '@type'  => 'SearchAction',
            'target' => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => rtrim(SITE_URL, '/') . '/buscar?q={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];

    return schemaJson($schema);
}

// =============================================================
// PRODUCTO
// =============================================================

/**
 * Política de devoluciones, común a todas las ofertas.
 * Los valores salen de /politica-devoluciones (config.php los centraliza).
 */
function schemaPoliticaDevolucion() {
    return schemaLimpiar([
        '@type'                => 'MerchantReturnPolicy',
        'applicableCountry'    => SITE_COUNTRY,
        'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
        'merchantReturnDays'   => RETURN_POLICY_DAYS,
        'returnMethod'         => 'https://schema.org/ReturnByMail',
        'returnFees'           => RETURN_POLICY_FEES_CUSTOMER
            ? 'https://schema.org/ReturnShippingFees'
            : 'https://schema.org/FreeReturn'
    ]);
}

/**
 * Condiciones de envío.
 *
 * Solo se declara el envío local gratuito, que es el único tramo con tarifa
 * cerrada en /politica-de-envios. El resto de zonas figura como "Consultar",
 * así que no se inventa un shippingRate: Google prefiere un aviso por dato
 * ausente antes que una tarifa que luego no se cumple.
 */
function schemaEnvio() {
    return schemaLimpiar([
        '@type' => 'OfferShippingDetails',
        'shippingRate' => [
            '@type'    => 'MonetaryAmount',
            'value'    => 0,
            'currency' => 'EUR'
        ],
        'shippingDestination' => [
            '@type'          => 'DefinedRegion',
            'addressCountry' => SITE_COUNTRY,
            'postalCode'     => SHIPPING_FREE_POSTAL_CODE
        ],
        'eligibleTransactionVolume' => [
            '@type'     => 'PriceSpecification',
            'minPrice'  => SHIPPING_FREE_MIN_ORDER,
            'priceCurrency' => 'EUR'
        ],
        'deliveryTime' => [
            '@type'        => 'ShippingDeliveryTime',
            'handlingTime' => [
                '@type'    => 'QuantitativeValue',
                'minValue' => SHIPPING_HANDLING_DAYS[0],
                'maxValue' => SHIPPING_HANDLING_DAYS[1],
                'unitCode' => 'DAY'
            ],
            'transitTime' => [
                '@type'    => 'QuantitativeValue',
                'minValue' => SHIPPING_TRANSIT_DAYS[0],
                'maxValue' => SHIPPING_TRANSIT_DAYS[1],
                'unitCode' => 'DAY'
            ]
        ]
    ]);
}

/**
 * Schema.org para producto.
 *
 * @param array $producto Fila de getProductoById() o de un listado.
 * @param array $textos   Fila de getProductoTextos(): la descripción buena vive
 *                        en producto_textos, no en la columna legacy
 *                        productos.descripcion.
 */
function schemaProducto($producto, $textos = []) {
    $nombre = trim(normalizarNombre($producto['nombre']));
    $url    = getProductoUrlAbs($producto);

    // Descripción: producto_textos primero, columna legacy después, y solo
    // como último recurso una frase construida con los datos que haya.
    $descripcion = '';
    if (!empty($textos['descripcion'])) {
        $descripcion = $textos['descripcion'];
    } elseif (!empty($producto['descripcion'])) {
        $descripcion = $producto['descripcion'];
    } else {
        $partes = array_filter([
            $nombre,
            $producto['tipo_nombre'] ?? null,
            !empty($producto['procedencia_nombre']) ? 'de ' . $producto['procedencia_nombre'] : null
        ]);
        $descripcion = implode(', ', $partes) . '. Disponible en ' . SITE_NAME . '.';
    }
    $descripcion = trim(preg_replace('/\s+/u', ' ', strip_tags($descripcion)));

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        '@id'         => $url . '#producto',
        'name'        => $nombre,
        'description' => $descripcion,
        'category'    => $producto['tipo_nombre'] ?? 'Bebidas',
        'url'         => $url
    ];

    // sku solo si existe de verdad: "sku": "" es una propiedad inválida.
    if (!empty($producto['codigo'])) {
        $schema['sku'] = $producto['codigo'];
    }

    // La procedencia (D.O. / origen) es el mejor identificador de marca
    // disponible en el catálogo.
    if (!empty($producto['procedencia_nombre'])) {
        $schema['brand'] = [
            '@type' => 'Brand',
            'name'  => $producto['procedencia_nombre']
        ];
    }

    // Solo se declara image si el archivo existe de verdad: hay filas en
    // producto_fotos que apuntan a imágenes nunca descargadas, y un
    // Product.image hacia un 404 es peor que no declarar la propiedad.
    if (productoTieneFoto($producto)) {
        $schema['image'] = getProductoImagenAbs($producto);
    }

    // Variedades de uva: dato real del catálogo que enriquece la ficha.
    if (!empty($producto['uvas_es'])) {
        $schema['additionalProperty'] = [
            '@type' => 'PropertyValue',
            'name'  => 'Variedades',
            'value' => $producto['uvas_es']
        ];
    }

    // Oferta.
    //
    // Solo se emite cuando la ficha muestra precio de verdad, es decir cuando
    // hay precio Y hay stock: sin stock la plantilla imprime "Consultar precio",
    // y marcar un importe que el usuario no ve es justo el desajuste que hace
    // que Google suprima el resultado enriquecido. El precio va con el descuento
    // web ya aplicado, igual que formatPrecioWeb() en la plantilla.
    if (!empty($producto['precio_venta']) && $producto['precio_venta'] > 0 && !empty($producto['tiene_stock'])) {
        $precio = aplicarDescuento($producto['precio_venta']);

        $schema['offers'] = schemaLimpiar([
            '@type'         => 'Offer',
            'url'           => $url,
            'priceCurrency' => 'EUR',
            'price'         => number_format($precio, 2, '.', ''),
            'priceValidUntil' => date('Y-m-d', strtotime('+1 year')),
            'itemCondition' => 'https://schema.org/NewCondition',
            'availability'  => 'https://schema.org/InStock',
            'seller'        => ['@id' => schemaTiendaId()],
            'hasMerchantReturnPolicy' => schemaPoliticaDevolucion(),
            'shippingDetails'         => schemaEnvio()
        ]);
    }

    return schemaJson(schemaLimpiar($schema));
}

// =============================================================
// LISTADOS
// =============================================================

/**
 * ItemList para páginas de categoría y resultados.
 *
 * Describe solo los productos de la página actual: un ItemList con el catálogo
 * entero (antes eran 1.479 elementos en /vinos) no aporta nada y dispara el
 * peso del HTML. $offset mantiene la numeración correcta al paginar.
 */
function schemaListaProductos($productos, $nombreLista, $offset = 0) {
    $items = [];
    $position = $offset + 1;

    foreach ($productos as $producto) {
        $item = [
            '@type' => 'ListItem',
            'position' => $position,
            'item' => schemaLimpiar([
                '@type' => 'Product',
                'name'  => trim(normalizarNombre($producto['nombre'])),
                'url'   => getProductoUrlAbs($producto),
                'image' => productoTieneFoto($producto) ? getProductoImagenAbs($producto) : null
            ])
        ];
        $items[] = $item;
        $position++;
    }

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => $nombreLista,
        'numberOfItems'   => count($items),
        'itemListOrder'   => 'https://schema.org/ItemListUnordered',
        'itemListElement' => $items
    ];

    return schemaJson($schema);
}

/**
 * BreadcrumbList. Las URLs se emiten absolutas y se descartan los items sin
 * destino real (un item: "#" no es una URL válida).
 */
function schemaBreadcrumb($items) {
    $listItems = [];
    $position = 1;

    foreach ($items as $item) {
        $entry = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => $item['name']
        ];

        if (!empty($item['url']) && $item['url'] !== '#') {
            $entry['item'] = schemaUrl($item['url']);
        }

        $listItems[] = $entry;
        $position++;
    }

    return schemaJson([
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $listItems
    ]);
}

// =============================================================
// OTRAS PÁGINAS
// =============================================================

/**
 * ContactPage. mainEntity referencia la tienda ya declarada en la cabecera
 * en lugar de crear una Organization nueva.
 */
function schemaContacto() {
    return schemaJson([
        '@context'   => 'https://schema.org',
        '@type'      => 'ContactPage',
        'name'       => 'Contacto - ' . SITE_NAME,
        'description' => 'Ponte en contacto con ' . SITE_NAME . '. Estamos aquí para ayudarte.',
        'url'        => rtrim(SITE_URL, '/') . '/contacto',
        'isPartOf'   => ['@id' => schemaWebSiteId()],
        'mainEntity' => ['@id' => schemaTiendaId()]
    ]);
}

/**
 * Article de WineWorld.
 *
 * mainEntityOfPage y publisher.logo son requisitos de Google para los
 * resultados de artículo; las fechas van en ISO 8601 con zona horaria.
 */
function schemaArticulo($articulo, $categoria) {
    $url = rtrim(SITE_URL, '/') . '/wineworld/' . $categoria['slug'] . '/' . $articulo['slug'];

    $schema = [
        '@context'         => 'https://schema.org',
        '@type'            => 'Article',
        '@id'              => $url . '#articulo',
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
        'url'              => $url,
        'headline'         => mb_substr(trim($articulo['titulo']), 0, 110),
        'description'      => !empty($articulo['extracto'])
            ? trim(preg_replace('/\s+/u', ' ', strip_tags($articulo['extracto'])))
            : null,
        'datePublished'    => schemaFecha($articulo['fecha_publicacion'] ?? null),
        'dateModified'     => schemaFecha($articulo['updated_at'] ?? null)
            ?: schemaFecha($articulo['fecha_publicacion'] ?? null),
        'articleSection'   => $categoria['nombre'],
        'inLanguage'       => 'es-ES',
        'isPartOf'         => ['@id' => schemaWebSiteId()],
        'publisher'        => ['@id' => schemaTiendaId()],
        // Si el "autor" es la propia tienda, se referencia la organización en
        // lugar de declarar una Person que no existe.
        'author'           => (!empty($articulo['autor']) && $articulo['autor'] !== SITE_NAME)
            ? ['@type' => 'Person', 'name' => $articulo['autor']]
            : ['@id' => schemaTiendaId()]
    ];

    if (!empty($articulo['imagen'])) {
        $schema['image'] = schemaUrl('/images/wineworld/' . $articulo['imagen']);
    }

    return schemaJson(schemaLimpiar($schema));
}

/**
 * FAQPage. Recibe [['pregunta' => ..., 'respuesta' => ...], ...].
 * Las preguntas tienen que estar visibles en la página: marcar un FAQ que el
 * usuario no puede leer incumple las directrices de Google.
 */
function schemaFAQ($faqs) {
    if (empty($faqs)) return '';

    $entidades = [];
    foreach ($faqs as $faq) {
        if (empty($faq['pregunta']) || empty($faq['respuesta'])) continue;
        $entidades[] = [
            '@type' => 'Question',
            'name'  => $faq['pregunta'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $faq['respuesta']
            ]
        ];
    }

    if (empty($entidades)) return '';

    return schemaJson([
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $entidades
    ]);
}

// =============================================================
// BREADCRUMB VISIBLE
// =============================================================

/**
 * Breadcrumb HTML.
 *
 * Sin microdatos: el BreadcrumbList ya se emite en JSON-LD desde
 * schemaBreadcrumb(). Duplicar el marcado en dos formatos solo consigue que
 * Google vea dos entidades donde hay una.
 */
function generarBreadcrumb($items) {
    $html = '<nav aria-label="Breadcrumb" class="breadcrumb-nav">';
    $html .= '<ol class="breadcrumb">';

    $position = 1;
    $total = count($items);

    foreach ($items as $item) {
        $isLast = ($position === $total);

        $html .= '<li class="breadcrumb-item' . ($isLast ? ' active' : '') . '">';

        if ($isLast || empty($item['url']) || $item['url'] === '#') {
            $html .= '<span aria-current="page">' . htmlspecialchars($item['name']) . '</span>';
        } else {
            $html .= '<a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['name']) . '</a>';
        }

        $html .= '</li>';
        $position++;
    }

    $html .= '</ol>';
    $html .= '</nav>';

    return $html;
}
