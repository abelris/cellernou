<?php
/**
 * El Celler Nou - Funciones principales
 * Todo se carga dinámicamente desde la BD
 */

/**
 * Obtener tipos de producto que tienen productos activos en un sector
 */
function getTiposBySector($idSector) {
    global $bd;

    $sql = "SELECT DISTINCT tp.id, tp.nombre_es, tp.nombre_cat
            FROM tipo_producto tp
            INNER JOIN productos p ON p.idTipo = tp.id
            WHERE p.idSector = " . intval($idSector) . "
            AND p.activo = 1
            AND tp.nombre_es != ''
            ORDER BY tp.nombre_es";

    $result = mysqli_query($bd, $sql);
    $tipos = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tipos[] = $row;
        }
    }
    return $tipos;
}

/**
 * Obtener tipos de producto para el menú (dinámico)
 */
function getTiposParaMenu($categoria) {
    switch($categoria) {
        case 'vinos':
            return getTiposBySector(8);
        case 'licores':
            return getTiposBySector(2);
        case 'cavas':
            return getTiposBySector(14);
        case 'cervezas':
            return getTiposBySector(21);
        case 'conservas':
            return getTiposBySector(12);
        default:
            return [];
    }
}

/**
 * Obtener productos por sector
 */
function getProductosBySector($idSector, $limit = 0, $offset = 0) {
    global $bd;

    $sql = "SELECT p.*, pf.foto as foto,
                   COALESCE(ppt.pvp, 0) as precio_venta,
                   IF(COALESCE(sp_web.stock, 0) > 0 OR lc_rec.idProducto IS NOT NULL, 1, 0) as tiene_stock,
                   tp.nombre_es as tipo_nombre,
                   tp.nombre_cat as tipo_nombre_cat,
                   pr.nombre_es as procedencia_nombre,
                   s.nombre_es as sector_nombre
            FROM productos p
            LEFT JOIN (SELECT idProducto, MIN(foto) AS foto FROM producto_fotos
                       WHERE principal = 1 GROUP BY idProducto) pf ON pf.idProducto = p.id
            LEFT JOIN (SELECT idProducto, MIN(pvp) AS pvp FROM precios_producto_tienda
                       WHERE idTienda = 1 GROUP BY idProducto) ppt ON ppt.idProducto = p.id
            LEFT JOIN (SELECT idProducto, COUNT(*) as stock FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) sp_web ON sp_web.idProducto = p.id
            LEFT JOIN (SELECT DISTINCT idProducto FROM linea_compra WHERE fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) lc_rec ON lc_rec.idProducto = p.id
            LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
            LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
            LEFT JOIN sectores s ON p.idSector = s.id
            WHERE p.idSector = " . intval($idSector) . "
            AND p.activo = 1
            ORDER BY p.nombre";

    if ($limit > 0) {
        $sql .= " LIMIT " . intval($offset) . ", " . intval($limit);
    }

    $result = mysqli_query($bd, $sql);
    $productos = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $productos[] = $row;
        }
    }
    return $productos;
}

/**
 * Obtener productos por tipo
 */
function getProductosByTipo($idTipo, $idSector = null, $limit = 0) {
    global $bd;

    $sql = "SELECT p.*, pf.foto as foto,
                   COALESCE(ppt.pvp, 0) as precio_venta,
                   IF(COALESCE(sp_web.stock, 0) > 0 OR lc_rec.idProducto IS NOT NULL, 1, 0) as tiene_stock,
                   tp.nombre_es as tipo_nombre,
                   pr.nombre_es as procedencia_nombre,
                   s.nombre_es as sector_nombre
            FROM productos p
            LEFT JOIN (SELECT idProducto, MIN(foto) AS foto FROM producto_fotos
                       WHERE principal = 1 GROUP BY idProducto) pf ON pf.idProducto = p.id
            LEFT JOIN (SELECT idProducto, MIN(pvp) AS pvp FROM precios_producto_tienda
                       WHERE idTienda = 1 GROUP BY idProducto) ppt ON ppt.idProducto = p.id
            LEFT JOIN (SELECT idProducto, COUNT(*) as stock FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) sp_web ON sp_web.idProducto = p.id
            LEFT JOIN (SELECT DISTINCT idProducto FROM linea_compra WHERE fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) lc_rec ON lc_rec.idProducto = p.id
            LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
            LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
            LEFT JOIN sectores s ON p.idSector = s.id
            WHERE p.idTipo = " . intval($idTipo) . "
            AND p.activo = 1
            AND p.idSector IN (" . implode(',', SECTORES_PERMITIDOS) . ")";

    if ($idSector) {
        $sql .= " AND p.idSector = " . intval($idSector);
    }

    $sql .= " ORDER BY p.nombre";

    if ($limit > 0) {
        $sql .= " LIMIT " . intval($limit);
    }

    $result = mysqli_query($bd, $sql);
    $productos = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $productos[] = $row;
        }
    }
    return $productos;
}

/**
 * Obtener productos por procedencia (D.O.)
 */
function getProductosByProcedencia($idProcedencia, $idSector = null, $limit = 0) {
    global $bd;

    $sql = "SELECT p.*, pf.foto as foto,
                   COALESCE(ppt.pvp, 0) as precio_venta,
                   IF(COALESCE(sp_web.stock, 0) > 0 OR lc_rec.idProducto IS NOT NULL, 1, 0) as tiene_stock,
                   tp.nombre_es as tipo_nombre,
                   pr.nombre_es as procedencia_nombre,
                   s.nombre_es as sector_nombre
            FROM productos p
            LEFT JOIN (SELECT idProducto, MIN(foto) AS foto FROM producto_fotos
                       WHERE principal = 1 GROUP BY idProducto) pf ON pf.idProducto = p.id
            LEFT JOIN (SELECT idProducto, MIN(pvp) AS pvp FROM precios_producto_tienda
                       WHERE idTienda = 1 GROUP BY idProducto) ppt ON ppt.idProducto = p.id
            LEFT JOIN (SELECT idProducto, COUNT(*) as stock FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) sp_web ON sp_web.idProducto = p.id
            LEFT JOIN (SELECT DISTINCT idProducto FROM linea_compra WHERE fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) lc_rec ON lc_rec.idProducto = p.id
            LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
            LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
            LEFT JOIN sectores s ON p.idSector = s.id
            WHERE p.idProcedencia = " . intval($idProcedencia) . "
            AND p.activo = 1
            AND p.idSector IN (" . implode(',', SECTORES_PERMITIDOS) . ")";

    if ($idSector) {
        $sql .= " AND p.idSector = " . intval($idSector);
    }

    $sql .= " ORDER BY p.nombre";

    if ($limit > 0) {
        $sql .= " LIMIT " . intval($limit);
    }

    $result = mysqli_query($bd, $sql);
    $productos = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $productos[] = $row;
        }
    }
    return $productos;
}

/**
 * Obtener un producto por ID
 */
function getProductoById($id) {
    global $bd;

    $sql = "SELECT p.*, pf.foto as foto,
                   COALESCE(ppt.pvp, 0) as precio_venta,
                   IF(COALESCE(sp_web.stock, 0) > 0 OR lc_rec.idProducto IS NOT NULL, 1, 0) as tiene_stock,
                   tp.nombre_es as tipo_nombre,
                   tp.nombre_cat as tipo_nombre_cat,
                   pr.nombre_es as procedencia_nombre,
                   s.nombre_es as sector_nombre
            FROM productos p
            LEFT JOIN (SELECT idProducto, MIN(foto) AS foto FROM producto_fotos
                       WHERE principal = 1 GROUP BY idProducto) pf ON pf.idProducto = p.id
            LEFT JOIN (SELECT idProducto, MIN(pvp) AS pvp FROM precios_producto_tienda
                       WHERE idTienda = 1 GROUP BY idProducto) ppt ON ppt.idProducto = p.id
            LEFT JOIN (SELECT idProducto, COUNT(*) as stock FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) sp_web ON sp_web.idProducto = p.id
            LEFT JOIN (SELECT DISTINCT idProducto FROM linea_compra WHERE fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) lc_rec ON lc_rec.idProducto = p.id
            LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
            LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
            LEFT JOIN sectores s ON p.idSector = s.id
            WHERE p.id = " . intval($id) . "
            AND p.activo = 1
            AND p.idSector IN (" . implode(',', SECTORES_PERMITIDOS) . ")";

    $result = mysqli_query($bd, $sql);
    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Buscar productos
 */
function buscarProductos($query, $limit = 50) {
    global $bd;

    $query = sanitize($query);

    $sql = "SELECT p.*, pf.foto as foto,
                   COALESCE(ppt.pvp, 0) as precio_venta,
                   IF(COALESCE(sp_web.stock, 0) > 0 OR lc_rec.idProducto IS NOT NULL, 1, 0) as tiene_stock,
                   tp.nombre_es as tipo_nombre,
                   pr.nombre_es as procedencia_nombre,
                   s.nombre_es as sector_nombre
            FROM productos p
            LEFT JOIN (SELECT idProducto, MIN(foto) AS foto FROM producto_fotos
                       WHERE principal = 1 GROUP BY idProducto) pf ON pf.idProducto = p.id
            LEFT JOIN (SELECT idProducto, MIN(pvp) AS pvp FROM precios_producto_tienda
                       WHERE idTienda = 1 GROUP BY idProducto) ppt ON ppt.idProducto = p.id
            LEFT JOIN (SELECT idProducto, COUNT(*) as stock FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) sp_web ON sp_web.idProducto = p.id
            LEFT JOIN (SELECT DISTINCT idProducto FROM linea_compra WHERE fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) lc_rec ON lc_rec.idProducto = p.id
            LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
            LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
            LEFT JOIN sectores s ON p.idSector = s.id
            WHERE p.activo = 1
            AND p.idSector IN (" . implode(',', SECTORES_PERMITIDOS) . ")
            AND (p.nombre LIKE '%$query%'
                 OR p.descripcion LIKE '%$query%'
                 OR tp.nombre_es LIKE '%$query%'
                 OR pr.nombre_es LIKE '%$query%')
            ORDER BY
                CASE WHEN p.nombre LIKE '$query%' THEN 1
                     WHEN p.nombre LIKE '%$query%' THEN 2
                     ELSE 3 END,
                p.nombre
            LIMIT " . intval($limit);

    $result = mysqli_query($bd, $sql);
    $productos = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $productos[] = $row;
        }
    }
    return $productos;
}

/**
 * Obtener productos destacados (aleatorios con imagen)
 */
function getProductosDestacados($limit = 12) {
    global $bd;

    $sql = "SELECT p.*, pf.foto as foto,
                   COALESCE(ppt.pvp, 0) as precio_venta,
                   IF(COALESCE(sp_web.stock, 0) > 0 OR lc_rec.idProducto IS NOT NULL, 1, 0) as tiene_stock,
                   tp.nombre_es as tipo_nombre,
                   pr.nombre_es as procedencia_nombre,
                   s.nombre_es as sector_nombre
            FROM productos p
            LEFT JOIN (SELECT idProducto, MIN(foto) AS foto FROM producto_fotos
                       WHERE principal = 1 GROUP BY idProducto) pf ON pf.idProducto = p.id
            LEFT JOIN (SELECT idProducto, MIN(pvp) AS pvp FROM precios_producto_tienda
                       WHERE idTienda = 1 GROUP BY idProducto) ppt ON ppt.idProducto = p.id
            LEFT JOIN (SELECT idProducto, COUNT(*) as stock FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) sp_web ON sp_web.idProducto = p.id
            LEFT JOIN (SELECT DISTINCT idProducto FROM linea_compra WHERE fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) lc_rec ON lc_rec.idProducto = p.id
            LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
            LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
            LEFT JOIN sectores s ON p.idSector = s.id
            WHERE p.activo = 1
            AND p.idSector IN (" . implode(',', SECTORES_PERMITIDOS) . ")
            AND pf.foto IS NOT NULL
            ORDER BY RAND()
            LIMIT " . intval($limit);

    $result = mysqli_query($bd, $sql);
    $productos = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $productos[] = $row;
        }
    }
    return $productos;
}

/**
 * Obtener todas las procedencias (D.O.) que tienen productos activos
 */
function getProcedencias($idSector = null) {
    global $bd;

    $sql = "SELECT DISTINCT pr.id, pr.nombre_es, pr.nombre_ca
            FROM procedencia pr
            INNER JOIN productos p ON p.idProcedencia = pr.id
            WHERE p.activo = 1
            AND p.idSector IN (" . implode(',', SECTORES_PERMITIDOS) . ")";

    if ($idSector) {
        $sql .= " AND p.idSector = " . intval($idSector);
    }

    $sql .= " AND pr.nombre_es != ''
              AND pr.nombre_es != ' '
              ORDER BY pr.nombre_es";

    $result = mysqli_query($bd, $sql);
    $procedencias = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $procedencias[] = $row;
        }
    }
    return $procedencias;
}

/**
 * Obtener tipo de producto por ID
 */
function getTipoById($id) {
    global $bd;

    $sql = "SELECT * FROM tipo_producto WHERE id = " . intval($id);
    $result = mysqli_query($bd, $sql);
    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Obtener procedencia por ID
 */
function getProcedenciaById($id) {
    global $bd;

    $sql = "SELECT * FROM procedencia WHERE id = " . intval($id);
    $result = mysqli_query($bd, $sql);
    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Contar productos por sector
 */
function contarProductosBySector($idSector) {
    global $bd;

    $sql = "SELECT COUNT(*) as total FROM productos
            WHERE idSector = " . intval($idSector) . " AND activo = 1";
    $result = mysqli_query($bd, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

/**
 * Obtener productos relacionados
 */
function getProductosRelacionados($producto, $limit = 4) {
    global $bd;

    $sql = "SELECT p.*, pf.foto as foto,
                   COALESCE(ppt.pvp, 0) as precio_venta,
                   IF(COALESCE(sp_web.stock, 0) > 0 OR lc_rec.idProducto IS NOT NULL, 1, 0) as tiene_stock,
                   tp.nombre_es as tipo_nombre,
                   pr.nombre_es as procedencia_nombre
            FROM productos p
            LEFT JOIN (SELECT idProducto, MIN(foto) AS foto FROM producto_fotos
                       WHERE principal = 1 GROUP BY idProducto) pf ON pf.idProducto = p.id
            LEFT JOIN (SELECT idProducto, MIN(pvp) AS pvp FROM precios_producto_tienda
                       WHERE idTienda = 1 GROUP BY idProducto) ppt ON ppt.idProducto = p.id
            LEFT JOIN (SELECT idProducto, COUNT(*) as stock FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) sp_web ON sp_web.idProducto = p.id
            LEFT JOIN (SELECT DISTINCT idProducto FROM linea_compra WHERE fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) lc_rec ON lc_rec.idProducto = p.id
            LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
            LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
            WHERE p.activo = 1
            AND p.id != " . intval($producto['id']) . "
            AND p.idSector IN (" . implode(',', SECTORES_PERMITIDOS) . ")
            AND (p.idTipo = " . intval($producto['idTipo']) . "
                 OR p.idProcedencia = " . intval($producto['idProcedencia']) . ")
            ORDER BY RAND()
            LIMIT " . intval($limit);

    $result = mysqli_query($bd, $sql);
    $productos = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $productos[] = $row;
        }
    }
    return $productos;
}

/**
 * Generar URL de producto
 */
function getProductoUrl($producto) {
    $slug = createSlug($producto['nombre']);
    return getBaseUrl() . '/producto/' . $producto['id'] . '/' . $slug;
}

/**
 * Obtener imagen del producto
 */
function getProductoImagen($producto, $size = 'medium') {
    return getBaseUrl() . rutaFoto($producto);
}

/**
 * Formatear precio
 */
function formatPrecio($precio) {
    return number_format($precio, 2, ',', '.') . ' €';
}

// =============================================
// KOMKAL - Productes del catàleg Komkal no vinculats
// =============================================

/**
 * Get unmatched Komkal products for public display.
 * Returns array formatted like productos rows (same keys) so templates work.
 * @param string $tipo 'vinos' or 'licores'
 * @param int $limit 0 = no limit
 */
function getKomkalProductosNoVinculats($tipo, $limit = 0) {
    global $bd;
    if (!in_array($tipo, ['vinos', 'licores'])) return [];

    $tabla = 'komkal_' . $tipo;
    $margen = ($tipo === 'vinos') ? 1.25 : 1.20;
    $iva = 1.21;

    $sql = "SELECT k.id, k.codigo_komkal as codigo, k.nombre,
                   k.precio_sin_iva as precio_medio,
                   ROUND(k.precio_sin_iva * $margen * $iva, 2) as precio_venta,
                   1 as tiene_stock,
                   k.disponible,
                   NULL as foto,
                   NULL as tipo_nombre,
                   NULL as procedencia_nombre,
                   '$tipo' as komkal_tipo
            FROM $tabla k
            WHERE k.id_producto_match IS NULL
            AND k.disponible = 1
            AND k.precio_sin_iva IS NOT NULL
            AND k.precio_sin_iva > 0
            ORDER BY k.nombre";

    if ($limit > 0) $sql .= " LIMIT " . intval($limit);

    $result = mysqli_query($bd, $sql);
    $productos = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Mark as Komkal product for URL generation
            $row['es_komkal'] = true;
            $row['komkal_url'] = '/producto/k/' . $tipo . '/' . $row['id'] . '/' . createSlug($row['nombre']);
            $productos[] = $row;
        }
    }
    return $productos;
}

/**
 * Get a single Komkal product by ID for the detail page.
 */
function getKomkalProductoById($tipo, $id) {
    global $bd;
    if (!in_array($tipo, ['vinos', 'licores'])) return null;

    $tabla = 'komkal_' . $tipo;
    $margen = ($tipo === 'vinos') ? 1.25 : 1.20;
    $iva = 1.21;
    $id = intval($id);

    $sql = "SELECT k.id, k.codigo_komkal as codigo, k.nombre,
                   k.precio_sin_iva as precio_medio,
                   ROUND(k.precio_sin_iva * $margen * $iva, 2) as precio_venta,
                   1 as tiene_stock,
                   k.disponible, k.url as komkal_url_origen,
                   NULL as foto, NULL as tipo_nombre, NULL as procedencia_nombre,
                   NULL as descripcion, NULL as uvas_es,
                   '$tipo' as komkal_tipo
            FROM $tabla k
            WHERE k.id = $id
            AND k.id_producto_match IS NULL
            AND k.disponible = 1
            AND k.precio_sin_iva IS NOT NULL";

    $result = mysqli_query($bd, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $row['es_komkal'] = true;
        return $row;
    }
    return null;
}

/**
 * Search unmatched Komkal products by name.
 */
function buscarKomkalProductos($query, $limit = 50) {
    global $bd;
    $query = sanitize($query);
    if (empty($query)) return [];

    $results = [];
    foreach (['vinos' => 1.25, 'licores' => 1.20] as $tipo => $margen) {
        $tabla = 'komkal_' . $tipo;
        $iva = 1.21;
        $sql = "SELECT k.id, k.codigo_komkal as codigo, k.nombre,
                       k.precio_sin_iva as precio_medio,
                       ROUND(k.precio_sin_iva * $margen * $iva, 2) as precio_venta,
                       1 as tiene_stock,
                       NULL as foto, NULL as tipo_nombre, NULL as procedencia_nombre,
                       '$tipo' as komkal_tipo
                FROM $tabla k
                WHERE k.id_producto_match IS NULL
                AND k.disponible = 1
                AND k.precio_sin_iva IS NOT NULL
                AND k.nombre LIKE '%$query%'
                ORDER BY
                    CASE WHEN k.nombre LIKE '$query%' THEN 1
                         WHEN k.nombre LIKE '%$query%' THEN 2
                         ELSE 3 END,
                    k.nombre
                LIMIT " . intval($limit);

        $r = mysqli_query($bd, $sql);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $row['es_komkal'] = true;
                $row['komkal_url'] = '/producto/k/' . $tipo . '/' . $row['id'] . '/' . createSlug($row['nombre']);
                $results[] = $row;
            }
        }
    }
    return $results;
}

/**
 * Generate URL for a product (works for both regular and Komkal).
 */
function getProductoUrlUniversal($producto) {
    if (!empty($producto['es_komkal'])) {
        return $producto['komkal_url'] ?? '/producto/k/' . $producto['komkal_tipo'] . '/' . $producto['id'] . '/' . createSlug($producto['nombre']);
    }
    return '/producto/' . $producto['id'] . '/' . createSlug($producto['nombre']);
}

/**
 * ¿Existe en disco el archivo de foto de este producto?
 *
 * producto_fotos guarda nombres de archivo que no siempre se llegaron a
 * descargar: hay filas apuntando a imágenes que no están en images/productos.
 * Publicar esas rutas deja un <img> roto en la página y, peor, un
 * Product.image apuntando a un 404 en el schema. Este guard evita las dos cosas.
 *
 * El resultado se cachea en memoria por petición: un listado de 36 productos
 * hace 36 comprobaciones, no una por cada sitio donde se consulta.
 */
function productoTieneFoto($producto) {
    static $cache = [];

    if (empty($producto['foto'])) return false;

    $foto = $producto['foto'];
    if (!isset($cache[$foto])) {
        $cache[$foto] = is_file(ROOT_PATH . '/images/productos/' . $foto);
    }

    return $cache[$foto];
}

/**
 * Ruta pública de la foto de un producto, con el nombre de archivo codificado.
 *
 * Hay imágenes guardadas con espacios y paréntesis ("terras gauda.jpg",
 * "pacharan_zoco-500x500 (1).jpg"). Los navegadores toleran un espacio en un
 * src, pero un espacio sin codificar hace inválido el URI de Product.image y
 * rompe a los scrapers de Open Graph. rawurlencode solo el nombre, nunca las
 * barras de la ruta.
 *
 * Devuelve la ruta relativa a la raíz del sitio, empezando por "/".
 */
function rutaFoto($producto) {
    if (!productoTieneFoto($producto)) {
        return '/images/no-image.svg';
    }
    return '/images/productos/' . rawurlencode($producto['foto']);
}

/**
 * URL absoluta de un producto (regular o Komkal).
 * Para schema.org, canonical, og:url y sitemap: siempre con dominio.
 */
function getProductoUrlAbs($producto) {
    return rtrim(SITE_URL, '/') . getProductoUrlUniversal($producto);
}

/**
 * URL absoluta de la imagen de un producto.
 */
function getProductoImagenAbs($producto) {
    return rtrim(SITE_URL, '/') . rutaFoto($producto);
}

/**
 * Agrupar SITE_OPENING_HOURS por día de la semana.
 *
 * Devuelve ['Monday' => ['09:30 - 14:00', '17:00 - 20:30'], ...] en orden
 * natural de lunes a domingo. Se usa para pintar el horario en /contacto, de
 * modo que lo que se muestra sea exactamente lo que declara el schema.
 */
function getHorarioPorDia() {
    $orden = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $porDia = array_fill_keys($orden, []);

    foreach (SITE_OPENING_HOURS as $tramo) {
        foreach ($tramo['dias'] as $dia) {
            if (!isset($porDia[$dia])) continue;
            $porDia[$dia][] = $tramo['opens'] . ' - ' . $tramo['closes'];
        }
    }

    foreach ($porDia as $dia => $tramos) {
        sort($tramos);
        $porDia[$dia] = $tramos;
    }

    return $porDia;
}

/**
 * ¿Está la tienda abierta ahora mismo? Devuelve true/false.
 * Zona horaria: la del sitio (Europe/Madrid, fijada en config.php).
 */
function tiendaAbiertaAhora() {
    $dia  = date('l');
    $hora = date('H:i');

    foreach (SITE_OPENING_HOURS as $tramo) {
        if (!in_array($dia, $tramo['dias'], true)) continue;
        if ($hora >= $tramo['opens'] && $hora < $tramo['closes']) return true;
    }
    return false;
}

/**
 * Existe en disco la imagen de un articulo o categoria de WineWorld?
 *
 * Mismo problema que con producto_fotos: hay filas que apuntan a imagenes que
 * nunca se subieron. Publicar esa ruta deja un <img> roto y, peor, un
 * Article.image y una og:image apuntando a un 404.
 */
function imagenWineWorldExiste($imagen) {
    static $cache = [];
    if (empty($imagen)) return false;
    if (!isset($cache[$imagen])) {
        $cache[$imagen] = is_file(ROOT_PATH . '/images/wineworld/' . $imagen);
    }
    return $cache[$imagen];
}

/**
 * Completar las imagenes del cuerpo de un articulo de WineWorld.
 *
 * El HTML del articulo se guarda en la base de datos y sus <img> vienen sin
 * width/height ni loading, asi que el layout salta al cargar. En lugar de
 * pedir que se escriban a mano en cada articulo, se leen aqui las dimensiones
 * reales del fichero y se inyectan al vuelo.
 *
 * Solo toca las etiquetas que no declaren ya un width, y solo si el archivo
 * existe en disco: una imagen que falta se deja como esta.
 */
function completarImagenesArticulo($html) {
    if (empty($html) || stripos($html, '<img') === false) {
        return $html;
    }

    return preg_replace_callback('/<img\b[^>]*>/i', function ($m) {
        $tag = $m[0];

        if (stripos($tag, 'width=') !== false) return $tag;
        if (!preg_match('/src="([^"]+)"/i', $tag, $src)) return $tag;

        // Solo imagenes locales del propio sitio
        $ruta = parse_url($src[1], PHP_URL_PATH);
        if (!$ruta || strpos($ruta, '/images/') !== 0) return $tag;

        // Si el archivo no esta, se retira la etiqueta en lugar de pintar un
        // icono de imagen rota. En cuanto se suba el fichero vuelve a salir
        // sola: no hay que tocar el contenido del articulo.
        $fichero = ROOT_PATH . rawurldecode($ruta);
        if (!is_file($fichero)) return '';

        $info = @getimagesize($fichero);
        if (!$info) return $tag;

        $extra = ' width="' . $info[0] . '" height="' . $info[1] . '" decoding="async"';
        if (stripos($tag, 'loading=') === false) {
            $extra .= ' loading="lazy"';
        }

        return rtrim(substr($tag, 0, -1)) . $extra . '>';
    }, $html);
}

// =============================================
// INDEXACIÓN
// =============================================
// Google mide la web por la media de lo que indexa: fichas sin stock y sin
// texto o filtros con un solo producto restan. Esas páginas siguen abiertas
// (200) pero van con noindex y fuera del sitemap.
//
// Son consultas ligeras a propósito: getProductosBySector() y compañía traen
// 7 LEFT JOIN y todas las columnas, bien para pintar un listado pero no para
// que el sitemap recorra el catálogo entero decenas de veces.

/** Mínimo de productos con stock para indexar una página de tipo, D.O. u origen. */
define('MIN_PRODUCTOS_FILTRO', 3);

/**
 * Subconsulta con los productos "con stock" para la web: unidades sin vender
 * o compras del último mes. Es el mismo criterio que tiene_stock en los listados.
 */
function sqlProductosConStock() {
    return "(SELECT idProducto FROM stock_producto WHERE vendido = 0
             UNION
             SELECT idProducto FROM linea_compra WHERE fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH))";
}

/**
 * Productos activos con stock por filtro y sector: [idFiltro][idSector] => n.
 * $campo es 'idTipo' o 'idProcedencia'.
 */
function getConteosConStock($campo) {
    global $bd;
    static $cache = [];
    if (!in_array($campo, ['idTipo', 'idProcedencia'], true)) return [];
    if (isset($cache[$campo])) return $cache[$campo];

    $sql = "SELECT p.$campo AS f, p.idSector AS s, COUNT(*) AS n
            FROM productos p
            JOIN " . sqlProductosConStock() . " st ON st.idProducto = p.id
            WHERE p.activo = 1 AND p.idSector IN (" . implode(',', SECTORES_PERMITIDOS) . ")
            GROUP BY p.$campo, p.idSector";
    $out = [];
    $r = mysqli_query($bd, $sql);
    if ($r) while ($row = mysqli_fetch_assoc($r)) $out[(int) $row['f']][(int) $row['s']] = (int) $row['n'];
    return $cache[$campo] = $out;
}

/**
 * Una página de filtro (tipo, D.O. u origen dentro de un sector) se indexa si
 * tiene MIN_PRODUCTOS_FILTRO productos con stock. Los tipos, además, solo en
 * el sector donde más hay: "Vino rosado" vive en vinos, no en licores, aunque
 * allí haya un rosado mal clasificado; así el mismo tipo no compite consigo
 * mismo en dos secciones. Las procedencias no lo necesitan: solo vinos (D.O.)
 * y cervezas (origen) tienen página de procedencia.
 */
function filtroIndexable($campo, $id, $idSector) {
    $porSector = getConteosConStock($campo)[(int) $id] ?? [];
    $n = $porSector[(int) $idSector] ?? 0;
    if ($n < MIN_PRODUCTOS_FILTRO) return false;
    return $campo !== 'idTipo' || $n === max($porSector);
}

/** Ficha indexable: producto con stock o con descripción propia. */
function productoIndexable($producto, $textos) {
    return !empty($producto['tiene_stock']) || !empty($textos['descripcion']);
}

/** Ids de los productos con stock para la web, como claves: [id => true]. */
function getIdsProductosConStock() {
    global $bd;
    static $ids = null;
    if ($ids !== null) return $ids;
    $ids = [];
    $r = mysqli_query($bd, "SELECT idProducto FROM " . sqlProductosConStock() . " st");
    if ($r) while ($row = mysqli_fetch_row($r)) $ids[(int) $row[0]] = true;
    return $ids;
}

/**
 * id, nombre y fecha de alta de los productos indexables de un sector (ver
 * productoIndexable). Es todo lo que el sitemap necesita para <loc> y <lastmod>.
 * El stock se cruza en PHP: con "p.id IN (subconsulta)" MariaDB recorría
 * stock_producto (sin índice por producto) una vez por fila y el sitemap
 * pasaba del límite de tiempo de nginx.
 */
function getProductosIndexablesPorSector($idSector) {
    global $bd;
    $conStock = getIdsProductosConStock();
    $sql = "SELECT p.id, p.nombre, p.alta, pt.idProducto IS NOT NULL AS con_texto
            FROM productos p
            LEFT JOIN producto_textos pt ON pt.idProducto = p.id AND pt.tipo = 'descripcion'
            WHERE p.idSector = " . intval($idSector) . " AND p.activo = 1
            ORDER BY p.id";
    $r = mysqli_query($bd, $sql);
    $out = [];
    if ($r) while ($row = mysqli_fetch_assoc($r)) {
        if ($row['con_texto'] || isset($conStock[(int) $row['id']])) $out[] = $row;
    }
    return $out;
}

// =============================================
// PAGINACIÓN
// =============================================

/**
 * Paginar un array de productos.
 *
 * Devuelve la porción de la página actual más los metadatos necesarios para
 * el canonical, los rel prev/next y el offset del ItemList.
 *
 * @param array  $items    Colección completa.
 * @param string $baseUrl  Ruta base sin query string (p. ej. '/vinos').
 * @param int    $porPagina
 */
function paginar(array $items, $baseUrl, $porPagina = null) {
    $porPagina = $porPagina ?: PRODUCTOS_POR_PAGINA;
    $total     = count($items);
    $paginas   = max(1, (int) ceil($total / $porPagina));

    $actual = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($actual < 1) $actual = 1;
    if ($actual > $paginas) $actual = $paginas;

    $offset = ($actual - 1) * $porPagina;

    // La página 1 se canonicaliza sin ?page=1 para no duplicar /vinos.
    $urlDe = function ($n) use ($baseUrl) {
        return $n <= 1 ? $baseUrl : $baseUrl . '?page=' . $n;
    };

    return [
        'items'      => array_slice($items, $offset, $porPagina),
        'total'      => $total,
        'porPagina'  => $porPagina,
        'paginas'    => $paginas,
        'actual'     => $actual,
        'offset'     => $offset,
        'baseUrl'    => $baseUrl,
        'canonical'  => $urlDe($actual),
        'prev'       => $actual > 1 ? $urlDe($actual - 1) : null,
        'next'       => $actual < $paginas ? $urlDe($actual + 1) : null,
        'urlDe'      => $urlDe
    ];
}

/**
 * Render de la navegación de paginación.
 * Muestra primera, última, y una ventana alrededor de la página actual.
 */
function renderPaginacion(array $p) {
    if ($p['paginas'] <= 1) return '';

    $urlDe   = $p['urlDe'];
    $actual  = $p['actual'];
    $paginas = $p['paginas'];

    $html = '<nav class="pagination" aria-label="Paginación de resultados">';

    if ($p['prev']) {
        $html .= '<a class="pagination-link pagination-prev" rel="prev" href="' . htmlspecialchars($p['prev']) . '">&larr; Anterior</a>';
    }

    $numeros = [1, $paginas];
    for ($i = $actual - 2; $i <= $actual + 2; $i++) {
        if ($i >= 1 && $i <= $paginas) $numeros[] = $i;
    }
    $numeros = array_unique($numeros);
    sort($numeros);

    $anterior = 0;
    foreach ($numeros as $n) {
        if ($anterior && $n > $anterior + 1) {
            $html .= '<span class="pagination-gap" aria-hidden="true">&hellip;</span>';
        }
        if ($n === $actual) {
            $html .= '<span class="pagination-link is-current" aria-current="page">' . $n . '</span>';
        } else {
            $html .= '<a class="pagination-link" href="' . htmlspecialchars($urlDe($n)) . '">' . $n . '</a>';
        }
        $anterior = $n;
    }

    if ($p['next']) {
        $html .= '<a class="pagination-link pagination-next" rel="next" href="' . htmlspecialchars($p['next']) . '">Siguiente &rarr;</a>';
    }

    $html .= '</nav>';

    return $html;
}

/**
 * Normalizar nombre de producto
 * Primera letra de cada palabra en mayúscula, excepto artículos y preposiciones
 */
function normalizarNombre($nombre) {
    // Lista de palabras que deben permanecer en minúscula (artículos y preposiciones)
    $excepciones = [
        // Artículos español
        'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas',
        // Artículos catalán
        'el', 'els', 'la', 'les', 'un', 'una', 'uns', 'unes', "l'",
        // Preposiciones español
        'de', 'del', 'a', 'al', 'en', 'con', 'por', 'para', 'sin', 'sobre',
        'entre', 'hacia', 'desde', 'durante', 'mediante',
        // Preposiciones catalán
        'de', 'del', 'a', 'al', 'en', 'amb', 'per', 'sense', 'sobre',
        'entre', 'cap', 'des', 'durant',
        // Conjunciones
        'y', 'e', 'o', 'u', 'i', 'ni', 'que'
    ];

    // Convertir todo a minúsculas primero
    $nombre = mb_strtolower(trim($nombre), 'UTF-8');

    // Dividir en palabras
    $palabras = preg_split('/(\s+)/', $nombre, -1, PREG_SPLIT_DELIM_CAPTURE);

    // Apóstrofo elidido catalán/francés (d'Encis, l'Ermita, s'Albufera).
    // mb_convert_case lo trata como fin de palabra y devolvía "D'encis":
    // hay que poner en mayúscula la parte que va DESPUÉS del apóstrofo.
    $titularConApostrofo = function ($palabra, $prefijoEnMayuscula) {
        if (!preg_match("/^([dlnstm])['\xe2\x80\x99](.+)$/u", $palabra, $m)) {
            return null;
        }
        $prefijo = $prefijoEnMayuscula ? mb_strtoupper($m[1], 'UTF-8') : $m[1];
        return $prefijo . "'" . mb_convert_case($m[2], MB_CASE_TITLE, 'UTF-8');
    };

    $resultado = [];
    $esPrimera = true;

    foreach ($palabras as $palabra) {
        // Si es solo espacios, mantener
        if (preg_match('/^\s+$/', $palabra)) {
            $resultado[] = $palabra;
            continue;
        }

        // La primera palabra siempre en mayúscula
        if ($esPrimera) {
            $resultado[] = $titularConApostrofo($palabra, true)
                ?? mb_convert_case($palabra, MB_CASE_TITLE, 'UTF-8');
            $esPrimera = false;
        }
        // Si es una excepción, mantener en minúscula
        elseif (in_array($palabra, $excepciones)) {
            $resultado[] = $palabra;
        }
        // El resto en Title Case
        else {
            $resultado[] = $titularConApostrofo($palabra, false)
                ?? mb_convert_case($palabra, MB_CASE_TITLE, 'UTF-8');
        }
    }

    return implode('', $resultado);
}

/**
 * Obtener categorías del menú con sus tipos (todo dinámico)
 */
function getMenuCategorias() {
    $categorias = [
        'vinos' => [
            'nombre' => 'Vinos',
            'url' => '/vinos',
            'sector' => 8,
            'tipos' => getTiposBySector(8),
            'procedencias' => getProcedencias(8)
        ],
        'cavas' => [
            'nombre' => 'Cavas y Espumosos',
            'url' => '/cavas',
            'sector' => 14,
            'tipos' => getTiposBySector(14),
            'procedencias' => []
        ],
        'licores' => [
            'nombre' => 'Licores',
            'url' => '/licores',
            'sector' => 2,
            'tipos' => getTiposBySector(2),
            'procedencias' => getProcedencias(2)
        ],
        'cervezas' => [
            'nombre' => 'Cervezas',
            'url' => '/cervezas',
            'sector' => 21,
            'tipos' => getTiposBySector(21),
            'procedencias' => getProcedencias(21)
        ],
        'conservas' => [
            'nombre' => 'Conservas',
            'url' => '/conservas',
            'sector' => 12,
            'tipos' => getTiposBySector(12),
            'procedencias' => []
        ]
    ];

    return $categorias;
}

/**
 * Obtener estadísticas generales
 */
function getEstadisticas() {
    global $bd;

    $stats = [
        'total_productos' => 0,
        'total_vinos' => 0,
        'total_licores' => 0,
        'total_cavas' => 0,
        'total_cervezas' => 0,
        'total_conservas' => 0,
        'total_do' => 0
    ];

    // Total productos
    $sql = "SELECT COUNT(*) as total FROM productos WHERE activo = 1 AND idSector IN (" . implode(',', SECTORES_PERMITIDOS) . ")";
    $result = mysqli_query($bd, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['total_productos'] = $row['total'];
    }

    // Por sector
    $stats['total_vinos'] = contarProductosBySector(8);
    $stats['total_licores'] = contarProductosBySector(2);
    $stats['total_cavas'] = contarProductosBySector(14);
    $stats['total_cervezas'] = contarProductosBySector(21);
    $stats['total_conservas'] = contarProductosBySector(12);

    // Total D.O.
    $sql = "SELECT COUNT(DISTINCT pr.id) as total
            FROM procedencia pr
            INNER JOIN productos p ON p.idProcedencia = pr.id
            WHERE p.activo = 1
            AND pr.nombre_es LIKE '%D.O.%'";
    $result = mysqli_query($bd, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['total_do'] = $row['total'];
    }

    return $stats;
}

/**
 * Obtener configuración web
 */
function getConfiguracionWeb($clave = null) {
    global $bd;

    // Crear tabla si no existe
    $sql = "CREATE TABLE IF NOT EXISTS configuracion_web (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT,
        descripcion VARCHAR(255),
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    mysqli_query($bd, $sql);

    if ($clave) {
        $claveSafe = mysqli_real_escape_string($bd, $clave);
        $result = mysqli_query($bd, "SELECT valor FROM configuracion_web WHERE clave = '$claveSafe'");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['valor'];
        }
        return null;
    }

    $result = mysqli_query($bd, "SELECT * FROM configuracion_web ORDER BY clave");
    $config = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $config[$row['clave']] = $row;
        }
    }
    return $config;
}

/**
 * Guardar configuración web
 */
function setConfiguracionWeb($clave, $valor, $descripcion = '') {
    global $bd;

    $claveSafe = mysqli_real_escape_string($bd, $clave);
    $valorSafe = mysqli_real_escape_string($bd, $valor);
    $descSafe = mysqli_real_escape_string($bd, $descripcion);

    $sql = "INSERT INTO configuracion_web (clave, valor, descripcion)
            VALUES ('$claveSafe', '$valorSafe', '$descSafe')
            ON DUPLICATE KEY UPDATE valor = '$valorSafe', descripcion = '$descSafe'";

    return mysqli_query($bd, $sql);
}

/**
 * Obtener textos de un producto (descripcion, nota_cata, texto_libre)
 */
function getProductoTextos($idProducto) {
    global $bd;

    $textos = ['descripcion' => '', 'nota_cata' => '', 'texto_libre' => ''];
    $idProducto = intval($idProducto);

    $result = mysqli_query($bd, "SELECT tipo, contenido FROM producto_textos WHERE idProducto = $idProducto");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $textos[$row['tipo']] = $row['contenido'];
        }
    }
    return $textos;
}

/**
 * Obtener descuento web activo
 */
function getDescuentoWeb() {
    $descuento = getConfiguracionWeb('descuento_web');
    return $descuento !== null ? floatval($descuento) : 0;
}

/**
 * Aplicar descuento al precio
 */
function aplicarDescuento($precio, $descuento = null) {
    if ($descuento === null) {
        $descuento = getDescuentoWeb();
    }
    if ($descuento > 0 && $precio > 0) {
        return $precio * (1 - $descuento / 100);
    }
    return $precio;
}

/**
 * Formatear precio con descuento web aplicado
 */
// ============================================================
// WineWorld - Funciones para artículos
// ============================================================

/**
 * Obtener todas las categorías activas de WineWorld
 */
function getWWCategorias() {
    global $bd;
    $result = mysqli_query($bd, "SELECT * FROM ww_categorias WHERE activo = 1 ORDER BY orden, nombre");
    $cats = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cats[] = $row;
        }
    }
    return $cats;
}

/**
 * Obtener categoría por slug
 */
function getWWCategoria($slug) {
    global $bd;
    $slug = mysqli_real_escape_string($bd, $slug);
    $result = mysqli_query($bd, "SELECT * FROM ww_categorias WHERE slug = '$slug' AND activo = 1");
    if ($result) return mysqli_fetch_assoc($result);
    return null;
}

/**
 * Obtener categoría por ID
 */
function getWWCategoriaById($id) {
    global $bd;
    $id = intval($id);
    $result = mysqli_query($bd, "SELECT * FROM ww_categorias WHERE id = $id");
    if ($result) return mysqli_fetch_assoc($result);
    return null;
}

/**
 * Obtener artículos publicados de una categoría
 */
function getWWArticulosByCategoria($idCategoria) {
    global $bd;
    $id = intval($idCategoria);
    $result = mysqli_query($bd, "SELECT * FROM ww_articulos WHERE id_categoria = $id AND publicado = 1 ORDER BY fecha_publicacion DESC");
    $arts = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arts[] = $row;
        }
    }
    return $arts;
}

/**
 * Obtener artículo por slug
 */
function getWWArticulo($slug) {
    global $bd;
    $slug = mysqli_real_escape_string($bd, $slug);
    $result = mysqli_query($bd, "SELECT * FROM ww_articulos WHERE slug = '$slug'");
    if ($result) return mysqli_fetch_assoc($result);
    return null;
}

/**
 * Contar artículos publicados en una categoría
 */
function getWWCountArticulos($idCategoria) {
    global $bd;
    $id = intval($idCategoria);
    $result = mysqli_query($bd, "SELECT COUNT(*) as total FROM ww_articulos WHERE id_categoria = $id AND publicado = 1");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total'];
    }
    return 0;
}

/**
 * Obtener últimos artículos publicados (cross-categoría)
 */
function getWWUltimosArticulos($limit = 6) {
    global $bd;
    $limit = intval($limit);
    $result = mysqli_query($bd, "SELECT * FROM ww_articulos WHERE publicado = 1 ORDER BY fecha_publicacion DESC LIMIT $limit");
    $arts = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arts[] = $row;
        }
    }
    return $arts;
}

/**
 * Obtener artículos relacionados (misma categoría, excluyendo el actual)
 */
function getWWArticulosRelacionados($idArticulo, $idCategoria, $limit = 3) {
    global $bd;
    $idArt = intval($idArticulo);
    $idCat = intval($idCategoria);
    $limit = intval($limit);
    $result = mysqli_query($bd, "SELECT * FROM ww_articulos WHERE id_categoria = $idCat AND id != $idArt AND publicado = 1 ORDER BY fecha_publicacion DESC LIMIT $limit");
    $arts = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arts[] = $row;
        }
    }
    return $arts;
}

function formatPrecioWeb($precio, $mostrarOriginal = true) {
    $descuento = getDescuentoWeb();

    if ($descuento > 0 && $precio > 0) {
        $precioFinal = aplicarDescuento($precio, $descuento);
        if ($mostrarOriginal) {
            return '<span class="precio-original">' . formatPrecio($precio) . '</span> <span class="precio-descuento">' . formatPrecio($precioFinal) . '</span>';
        }
        return formatPrecio($precioFinal);
    }
    return formatPrecio($precio);
}
