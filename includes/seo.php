<?php
/**
 * Funciones SEO - Schema.org, Open Graph, Meta tags
 */

/**
 * Generar meta tags SEO
 */
function generarMetaTags($datos) {
    $title = isset($datos['title']) ? htmlspecialchars($datos['title']) : SITE_NAME;
    $description = isset($datos['description']) ? htmlspecialchars($datos['description']) : SITE_DESCRIPTION;
    $url = isset($datos['url']) ? $datos['url'] : getBaseUrl() . $_SERVER['REQUEST_URI'];
    $image = isset($datos['image']) ? $datos['image'] : getBaseUrl() . '/images/og-default.jpg';
    $type = isset($datos['type']) ? $datos['type'] : 'website';

    $meta = '';

    // Meta básicos
    $meta .= '<meta name="description" content="' . $description . '">' . "\n";
    $meta .= '<meta name="robots" content="index, follow">' . "\n";
    $meta .= '<link rel="canonical" href="' . $url . '">' . "\n";

    // Open Graph
    $meta .= '<meta property="og:type" content="' . $type . '">' . "\n";
    $meta .= '<meta property="og:title" content="' . $title . '">' . "\n";
    $meta .= '<meta property="og:description" content="' . $description . '">' . "\n";
    $meta .= '<meta property="og:url" content="' . $url . '">' . "\n";
    $meta .= '<meta property="og:image" content="' . $image . '">' . "\n";
    $meta .= '<meta property="og:site_name" content="' . SITE_NAME . '">' . "\n";
    $meta .= '<meta property="og:locale" content="es_ES">' . "\n";

    // Twitter Card
    $meta .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $meta .= '<meta name="twitter:title" content="' . $title . '">' . "\n";
    $meta .= '<meta name="twitter:description" content="' . $description . '">' . "\n";
    $meta .= '<meta name="twitter:image" content="' . $image . '">' . "\n";

    return $meta;
}

/**
 * Generar Schema.org para organización
 */
function schemaOrganizacion() {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Store',
        'name' => SITE_NAME,
        'description' => SITE_DESCRIPTION,
        'url' => SITE_URL,
        'email' => SITE_EMAIL,
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Catalunya',
            'addressCountry' => 'ES'
        ],
        'priceRange' => '€€',
        'openingHoursSpecification' => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'opens' => '09:00',
            'closes' => '20:00'
        ]
    ];

    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

/**
 * Generar Schema.org para producto
 */
function schemaProducto($producto) {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $producto['nombre'],
        'description' => !empty($producto['descripcion']) ? strip_tags($producto['descripcion']) : $producto['nombre'] . ' - ' . SITE_NAME,
        'sku' => $producto['codigo'],
        'brand' => [
            '@type' => 'Brand',
            'name' => !empty($producto['procedencia_nombre']) ? $producto['procedencia_nombre'] : SITE_NAME
        ],
        'category' => $producto['tipo_nombre'] ?? 'Bebidas',
        'url' => getProductoUrl($producto)
    ];

    if (!empty($producto['foto'])) {
        $schema['image'] = getProductoImagen($producto);
    }

    if ($producto['precio_venta'] > 0) {
        $schema['offers'] = [
            '@type' => 'Offer',
            'priceCurrency' => 'EUR',
            'price' => number_format($producto['precio_venta'], 2, '.', ''),
            'availability' => 'https://schema.org/InStock',
            'seller' => [
                '@type' => 'Organization',
                'name' => SITE_NAME
            ]
        ];
    }

    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

/**
 * Generar Schema.org para lista de productos (ItemList)
 */
function schemaListaProductos($productos, $nombreLista) {
    $items = [];
    $position = 1;

    foreach ($productos as $producto) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'item' => [
                '@type' => 'Product',
                'name' => $producto['nombre'],
                'url' => getProductoUrl($producto)
            ]
        ];
        $position++;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $nombreLista,
        'numberOfItems' => count($productos),
        'itemListElement' => $items
    ];

    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

/**
 * Generar Schema.org BreadcrumbList
 */
function schemaBreadcrumb($items) {
    $listItems = [];
    $position = 1;

    foreach ($items as $item) {
        $listItems[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $item['name'],
            'item' => $item['url']
        ];
        $position++;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $listItems
    ];

    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

/**
 * Generar Schema.org para página de contacto
 */
function schemaContacto() {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'ContactPage',
        'name' => 'Contacto - ' . SITE_NAME,
        'description' => 'Ponte en contacto con ' . SITE_NAME . '. Estamos aquí para ayudarte.',
        'url' => SITE_URL . '/contacto',
        'mainEntity' => [
            '@type' => 'Organization',
            'name' => SITE_NAME,
            'email' => SITE_EMAIL,
            'url' => SITE_URL
        ]
    ];

    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

/**
 * Generar Schema.org WebSite con SearchAction
 */
function schemaWebSite() {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => SITE_NAME,
        'url' => SITE_URL,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => SITE_URL . '/buscar?q={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];

    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

/**
 * Generar Schema.org para artículo de WineWorld
 */
function schemaArticulo($articulo, $categoria) {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $articulo['titulo'],
        'description' => !empty($articulo['extracto']) ? strip_tags($articulo['extracto']) : '',
        'datePublished' => $articulo['fecha_publicacion'],
        'dateModified' => $articulo['updated_at'],
        'articleSection' => $categoria['nombre'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => SITE_NAME,
            'url' => SITE_URL
        ]
    ];

    if (!empty($articulo['autor'])) {
        $schema['author'] = [
            '@type' => 'Person',
            'name' => $articulo['autor']
        ];
    }

    if (!empty($articulo['imagen'])) {
        $schema['image'] = SITE_URL . '/images/wineworld/' . $articulo['imagen'];
    }

    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

/**
 * Generar breadcrumb HTML
 */
function generarBreadcrumb($items) {
    $html = '<nav aria-label="Breadcrumb" class="breadcrumb-nav">';
    $html .= '<ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">';

    $position = 1;
    $total = count($items);

    foreach ($items as $item) {
        $isLast = ($position === $total);

        $html .= '<li class="breadcrumb-item' . ($isLast ? ' active' : '') . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

        if ($isLast) {
            $html .= '<span itemprop="name">' . htmlspecialchars($item['name']) . '</span>';
        } else {
            $html .= '<a href="' . $item['url'] . '" itemprop="item"><span itemprop="name">' . htmlspecialchars($item['name']) . '</span></a>';
        }

        $html .= '<meta itemprop="position" content="' . $position . '">';
        $html .= '</li>';

        $position++;
    }

    $html .= '</ol>';
    $html .= '</nav>';

    return $html;
}
