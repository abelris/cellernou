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
            LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
            LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
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
            LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
            LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
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
            LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
            LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
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
            LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
            LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
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
            LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
            LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
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
            LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
            LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
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
            LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
            LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
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
    if (!empty($producto['foto'])) {
        return getBaseUrl() . '/images/productos/' . $producto['foto'];
    }
    return getBaseUrl() . '/images/no-image.svg';
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
            $resultado[] = mb_convert_case($palabra, MB_CASE_TITLE, 'UTF-8');
            $esPrimera = false;
        }
        // Si es una excepción, mantener en minúscula
        elseif (in_array($palabra, $excepciones)) {
            $resultado[] = $palabra;
        }
        // El resto en Title Case
        else {
            $resultado[] = mb_convert_case($palabra, MB_CASE_TITLE, 'UTF-8');
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
