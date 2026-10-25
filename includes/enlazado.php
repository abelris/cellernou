<?php
/**
 * Textos de categoría y enlazado blog <-> categorías <-> fichas.
 *
 * categoria_textos guarda la introducción, el texto largo y las preguntas
 * frecuentes de cada listado; ww_articulo_relaciones, qué categorías trata
 * cada artículo. Con eso cada artículo nuevo enlaza a su categoría y queda
 * enlazado desde ella sin tocar las páginas a mano:
 *   listado  -> bloque «Del blog» con sus artículos
 *   artículo -> «Dónde comprarlo» (categoría principal + 4 fichas con stock)
 *   ficha    -> la guía de su tipo o su D.O.
 *
 * Ámbitos: 'sector' (sectores.id), 'tipo' (tipo_producto.id) y 'procedencia'
 * (procedencia.id: D.O. de vino y orígenes de cerveza).
 * Migración: sql/migration_categoria_textos.sql.
 */

const AMBITOS_CATEGORIA = ['sector', 'tipo', 'procedencia'];

/** Listados de sector: ruta y nombre. */
const SECTORES_LISTADO = [
    8  => ['ruta' => 'vinos',     'nombre' => 'Vinos'],
    14 => ['ruta' => 'cavas',     'nombre' => 'Cavas y espumosos'],
    2  => ['ruta' => 'licores',   'nombre' => 'Licores'],
    21 => ['ruta' => 'cervezas',  'nombre' => 'Cervezas'],
    12 => ['ruta' => 'conservas', 'nombre' => 'Conservas'],
];

/**
 * Texto publicado de un listado, o null. faqs ya viene decodificado.
 */
function getTextoCategoria($ambito, $idRef) {
    global $bd;
    static $cache = [];
    if (!in_array($ambito, AMBITOS_CATEGORIA, true) || !$idRef) return null;
    $clave = $ambito . ':' . (int) $idRef;
    if (array_key_exists($clave, $cache)) return $cache[$clave];

    $texto = null;
    try {
        $r = mysqli_query($bd, "SELECT * FROM categoria_textos
                                WHERE ambito = '$ambito' AND id_ref = " . (int) $idRef . " AND publicado = 1");
        if ($r) $texto = mysqli_fetch_assoc($r) ?: null;
    } catch (mysqli_sql_exception $e) {
        // Sin la tabla (BD sin migrar) el listado sale como antes.
    }
    if ($texto) $texto['faqs'] = decodificarFaqs($texto['faqs']);
    return $cache[$clave] = $texto;
}

/** FAQ guardadas como JSON [{pregunta, respuesta}] -> array limpio. */
function decodificarFaqs($json) {
    $faqs = json_decode((string) $json, true);
    if (!is_array($faqs)) return [];
    return array_values(array_filter($faqs, function ($f) {
        return is_array($f) && trim($f['pregunta'] ?? '') !== '' && trim($f['respuesta'] ?? '') !== '';
    }));
}

/**
 * Aplica el texto de un listado a la página: title (si tiene uno propio),
 * meta description (la primera frase de la introducción, regla del mapa de
 * contenidos) y FAQ. El texto y sus FAQ solo salen en la página 1: en las
 * demás serían contenido y FAQPage duplicados.
 *
 * Llamar antes de añadir schemaFAQ($faqs) al $schemaExtra.
 * Devuelve el texto para pintarlo en la página 1, o null.
 */
function aplicarTextoCategoria($ambito, $idRef, array $paginacion, &$pageTitle, &$pageDescription, &$faqs) {
    $texto = getTextoCategoria($ambito, $idRef);
    if (!$texto) return null;

    if (!empty($texto['titulo_seo'])) {
        $pageTitle = $texto['titulo_seo'] . ($paginacion['actual'] > 1 ? ' - Página ' . $paginacion['actual'] : '');
    }
    if (!empty($texto['intro'])) {
        $pageDescription = resumenMeta($texto['intro'], 155);
    }
    if ($paginacion['actual'] !== 1) return null;

    $faqs = array_merge($texto['faqs'], $faqs);
    return $texto;
}

/**
 * HTML permitido en los textos largos: párrafos, subtítulos, listas, énfasis
 * y enlaces. Se quitan atributos salvo href, y los href que no sean rutas o
 * http(s).
 */
function limpiarHtmlTexto($html) {
    $html = strip_tags((string) $html, '<p><h2><h3><ul><ol><li><strong><em><a><br>');
    $html = preg_replace_callback('/<(\w+)([^>]*)>/', function ($m) {
        $tag = strtolower($m[1]);
        if ($tag !== 'a') return '<' . $tag . '>';
        if (!preg_match('/href\s*=\s*"([^"]*)"/i', $m[2], $h)
            || !preg_match('#^(/|https?://)#i', $h[1])) {
            return '<a>';
        }
        $externo = stripos($h[1], 'http') === 0 && stripos($h[1], 'cellernou.com') === false;
        return '<a href="' . htmlspecialchars($h[1]) . '"' . ($externo ? ' target="_blank" rel="noopener"' : '') . '>';
    }, $html);
    return $html;
}

/** Párrafos sueltos (texto plano con líneas en blanco) o HTML ya formado. */
function htmlParrafos($texto) {
    $texto = trim((string) $texto);
    if ($texto === '') return '';
    if (preg_match('/^<(p|h2|h3|ul|ol)\b/i', $texto)) return limpiarHtmlTexto($texto);
    $parrafos = preg_split('/\n\s*\n/', $texto);
    return implode('', array_map(function ($p) {
        return '<p>' . limpiarHtmlTexto(nl2br(trim($p), false)) . '</p>';
    }, $parrafos));
}

/** Introducción bajo el H1 (solo el primer párrafo: el resto va al texto largo). */
function renderIntroCategoria($texto) {
    if (empty($texto['intro'])) return '';
    return '<p class="category-intro">' . limpiarHtmlTexto(nl2br(trim($texto['intro']), false)) . '</p>';
}

/**
 * Texto largo del listado, con el consejo de la tienda y los artículos del
 * blog relacionados. $nombre es el de la página ("Vino tinto", "D.O. Alella").
 */
function renderTextoCategoria($texto, $ambito, $idRef, $nombre) {
    $articulos = getArticulosDeCategoria($ambito, $idRef, 3);
    $cuerpo = $texto ? htmlParrafos($texto['cuerpo'] ?? '') : '';
    if ($cuerpo === '' && empty($articulos)) return '';

    $html = '<section class="section categoria-texto"><div class="container">';
    if ($cuerpo !== '') {
        $html .= '<div class="categoria-texto-cuerpo">' . $cuerpo . '</div>';
        $html .= renderTeLoAconsejamos('Hola, busco ' . mb_strtolower($nombre) . ' y me gustaría que me aconsejarais.', null, 'categoria-consejo');
    }
    $html .= renderBloqueBlog($articulos);
    $html .= '</div></section>';
    return $html;
}

/**
 * «Te lo aconsejamos»: WhatsApp con el mensaje escrito, teléfono y tienda.
 * Solo servicios confirmados (docs/seo/datos-productos-usuario.md).
 */
function renderTeLoAconsejamos($mensajeWhatsapp, $consejo = null, $clase = '') {
    $texto = $consejo
        ? htmlParrafos($consejo)
        : '<p>Cuéntanos la ocasión, el plato y lo que quieres gastar, y te proponemos dos o tres botellas que encajen. '
            . 'En la tienda de ' . htmlspecialchars(SITE_STREET . ', ' . SITE_LOCALITY) . ', por WhatsApp o por teléfono.</p>';

    return '<aside class="consejo-tienda ' . $clase . '">'
        . '<h2>Te lo aconsejamos</h2>'
        . $texto
        . '<p class="consejo-tienda-acciones">'
        . '<a href="' . htmlspecialchars(whatsappUrl($mensajeWhatsapp)) . '" class="btn btn-primary" target="_blank" rel="noopener noreferrer">'
        . '<svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg> Pregúntanos por WhatsApp</a>'
        . '<a href="tel:' . preg_replace('/[^0-9+]/', '', SITE_PHONE) . '" class="btn btn-outline">Llámanos al ' . htmlspecialchars(SITE_PHONE) . '</a>'
        . '<a href="' . Router::url('contacto') . '">Cómo llegar a la tienda</a>'
        . '</p></aside>';
}

// ---------------------------------------------------------------------------
// Relaciones artículo <-> categoría
// ---------------------------------------------------------------------------

/**
 * URL y nombre del listado de una relación, o null si no tiene página.
 * Un tipo va a la sección de su sector; una procedencia, a la D.O. de vinos
 * o al origen de cervezas según dónde tenga más productos con stock.
 */
function urlCategoria($ambito, $idRef) {
    static $cache = [];
    $clave = $ambito . ':' . (int) $idRef;
    if (array_key_exists($clave, $cache)) return $cache[$clave];

    $out = null;
    if ($ambito === 'sector' && isset(SECTORES_LISTADO[$idRef])) {
        $s = SECTORES_LISTADO[$idRef];
        $out = ['url' => Router::url($s['ruta']), 'nombre' => $s['nombre']];
    } elseif ($ambito === 'tipo' && ($tipo = getTipoById($idRef))) {
        $sector = (int) $tipo['idSector'];
        $porSector = getConteosConStock('idTipo')[(int) $idRef] ?? [];
        if ($porSector) $sector = array_search(max($porSector), $porSector);
        if (isset(SECTORES_LISTADO[$sector]) && $sector !== 21) {
            $out = [
                'url'    => Router::url(SECTORES_LISTADO[$sector]['ruta'] . '.tipo', ['id' => (int) $idRef, 'slug' => createSlug($tipo['nombre_es'])]),
                'nombre' => $tipo['nombre_es'],
            ];
        }
    } elseif ($ambito === 'procedencia' && ($proc = getProcedenciaById($idRef))) {
        if ((int) $idRef === PROCEDENCIA_DO_CAVA) {
            $out = ['url' => Router::url('cavas'), 'nombre' => $proc['nombre_es']];
        } else {
            $porSector = getConteosConStock('idProcedencia')[(int) $idRef] ?? [];
            $esCerveza = $porSector && array_search(max($porSector), $porSector) === 21;
            $out = [
                'url'    => Router::url($esCerveza ? 'cervezas.origen' : 'vinos.do', ['id' => (int) $idRef, 'slug' => createSlug($proc['nombre_es'])]),
                'nombre' => $proc['nombre_es'],
            ];
        }
    }
    return $cache[$clave] = $out;
}

/** Relaciones de un artículo, la principal primero. */
function getRelacionesArticulo($idArticulo) {
    global $bd;
    $rel = [];
    try {
        $r = mysqli_query($bd, "SELECT ambito, id_ref, principal FROM ww_articulo_relaciones
                                WHERE id_articulo = " . (int) $idArticulo . " ORDER BY principal DESC, ambito, id_ref");
        if ($r) while ($row = mysqli_fetch_assoc($r)) $rel[] = $row;
    } catch (mysqli_sql_exception $e) {
    }
    return $rel;
}

/**
 * Artículos publicados que tratan alguna de estas categorías.
 * $pares: [[ambito, id_ref], ...]. Los que la tienen como principal, primero.
 */
function getArticulosDeCategorias(array $pares, $limit = 3) {
    global $bd;
    $cond = [];
    foreach ($pares as $p) {
        if (in_array($p[0], AMBITOS_CATEGORIA, true) && (int) $p[1] > 0) {
            $cond[] = "(r.ambito = '" . $p[0] . "' AND r.id_ref = " . (int) $p[1] . ")";
        }
    }
    if (!$cond) return [];

    $arts = [];
    try {
        $r = mysqli_query($bd, "SELECT a.*, c.slug AS cat_slug, c.nombre AS cat_nombre, MAX(r.principal) AS es_principal
                                FROM ww_articulo_relaciones r
                                JOIN ww_articulos a ON a.id = r.id_articulo
                                JOIN ww_categorias c ON c.id = a.id_categoria
                                WHERE (" . implode(' OR ', $cond) . ") AND " . sqlWWVisible('a.') . "
                                GROUP BY a.id
                                ORDER BY es_principal DESC, a.fecha_publicacion DESC
                                LIMIT " . (int) $limit);
        if ($r) while ($row = mysqli_fetch_assoc($r)) $arts[] = $row;
    } catch (mysqli_sql_exception $e) {
    }
    return $arts;
}

function getArticulosDeCategoria($ambito, $idRef, $limit = 3) {
    return getArticulosDeCategorias([[$ambito, $idRef]], $limit);
}

/** Bloque «Del blog» de un listado. */
function renderBloqueBlog(array $articulos, $titulo = 'Del blog: te lo explicamos') {
    if (empty($articulos)) return '';
    $html = '<div class="bloque-blog"><h2>' . htmlspecialchars($titulo) . '</h2><ul>';
    foreach ($articulos as $a) {
        $url = Router::url('wineworld.articulo', ['cat_slug' => $a['cat_slug'], 'art_slug' => $a['slug']]);
        $html .= '<li><a href="' . $url . '"><strong>' . htmlspecialchars($a['titulo']) . '</strong>';
        if (!empty($a['extracto'])) {
            $html .= '<span>' . htmlspecialchars(resumenMeta($a['extracto'], 140)) . '</span>';
        }
        $html .= '</a></li>';
    }
    return $html . '</ul></div>';
}

/**
 * Hasta $limit fichas con stock, precio y foto de una categoría, repartidas
 * por precio (de la más asequible a la más especial). Las que tienen texto
 * propio van primero: son las que mejor responden a quien llega del artículo.
 */
function getProductosParaComprar($ambito, $idRef, $limit = 4) {
    global $bd;
    if ($ambito === 'tipo') {
        $lista = getProductosByTipo($idRef);
    } elseif ($ambito === 'procedencia') {
        $lista = getProductosByProcedencia($idRef);
    } elseif ($ambito === 'sector') {
        $lista = getProductosBySector($idRef);
    } else {
        return [];
    }
    $lista = array_values(array_filter($lista, function ($p) {
        return !empty($p['tiene_stock']) && $p['precio_venta'] > 0 && productoTieneFoto($p);
    }));
    if (!$lista) return [];

    $conTexto = [];
    $r = mysqli_query($bd, "SELECT idProducto FROM producto_textos WHERE tipo = 'descripcion'");
    if ($r) while ($row = mysqli_fetch_row($r)) $conTexto[(int) $row[0]] = true;
    $buenos = array_values(array_filter($lista, function ($p) use ($conTexto) { return isset($conTexto[(int) $p['id']]); }));
    $pool = count($buenos) >= $limit ? $buenos : $lista;

    usort($pool, function ($a, $b) { return $a['precio_venta'] <=> $b['precio_venta']; });
    $n = count($pool);
    if ($n <= $limit) return $pool;
    $out = [];
    for ($i = 0; $i < $limit; $i++) {
        $out[] = $pool[(int) floor(($i + 0.5) * $n / $limit)];
    }
    return $out;
}

/** Tarjetas de producto (mismo marcado que los listados). */
function renderTarjetasProducto(array $productos) {
    $html = '<div class="products-grid products-grid--compacto">';
    foreach ($productos as $p) {
        $nombre = htmlspecialchars(normalizarNombre($p['nombre']));
        $html .= '<a href="' . getProductoUrlUniversal($p) . '" class="product-card">'
            . '<div class="product-card-image"><img src="' . getBaseUrl() . rutaFoto($p) . '" alt="' . $nombre . '" width="300" height="300" decoding="async" loading="lazy"></div>'
            . '<div class="product-card-content">'
            . '<span class="product-card-category">' . htmlspecialchars($p['tipo_nombre'] ?? '') . '</span>'
            . '<h3 class="product-card-title">' . $nombre . '</h3>'
            . (!empty($p['procedencia_nombre']) ? '<p class="product-card-origin">' . htmlspecialchars($p['procedencia_nombre']) . '</p>' : '')
            . '<div class="product-card-footer"><span class="product-card-price">' . formatPrecioWeb($p['precio_venta']) . '</span>'
            . '<span class="product-card-link">Ver más <svg class="icon"><use href="#icon-arrow-right"></use></svg></span></div>'
            . '</div></a>';
    }
    return $html . '</div>';
}

/**
 * «Dónde comprarlo» de un artículo: su categoría principal con 4 fichas con
 * stock, y enlaces al resto de categorías que trata.
 */
function renderDondeComprarlo(array $relaciones) {
    if (!$relaciones) return '';
    $principal = $relaciones[0];
    $cat = urlCategoria($principal['ambito'], $principal['id_ref']);
    if (!$cat) return '';

    $productos = getProductosParaComprar($principal['ambito'], $principal['id_ref'], 4);
    $html = '<section class="section section-alt donde-comprarlo"><div class="container">'
        . '<div class="section-header"><h2>Dónde comprarlo</h2>'
        . '<p>En la tienda de Premià de Mar o a domicilio en el Maresme. Algunas de las que tenemos ahora:</p></div>';
    if ($productos) $html .= renderTarjetasProducto($productos);

    $enlaces = ['<a href="' . $cat['url'] . '" class="btn btn-primary">Ver todo: ' . htmlspecialchars($cat['nombre']) . '</a>'];
    foreach (array_slice($relaciones, 1) as $rel) {
        if ($otra = urlCategoria($rel['ambito'], $rel['id_ref'])) {
            $enlaces[] = '<a href="' . $otra['url'] . '" class="btn btn-outline">' . htmlspecialchars($otra['nombre']) . '</a>';
        }
    }
    $html .= '<p class="donde-comprarlo-enlaces">' . implode('', $enlaces) . '</p>';
    return $html . '</div></section>';
}

/** La guía del blog que le toca a una ficha: por su tipo, su D.O. o su sector. */
function getGuiaParaProducto($producto) {
    $pares = [];
    if (!empty($producto['idTipo'])) $pares[] = ['tipo', $producto['idTipo']];
    if (!empty($producto['idProcedencia'])) $pares[] = ['procedencia', $producto['idProcedencia']];
    $arts = getArticulosDeCategorias($pares, 1);
    if (!$arts && !empty($producto['idSector'])) {
        $arts = getArticulosDeCategoria('sector', $producto['idSector'], 1);
    }
    return $arts[0] ?? null;
}
