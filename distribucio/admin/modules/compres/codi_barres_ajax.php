<?php
/**
 * AJAX del lector de codis de barres de les compres (crear.php i modificar.php).
 *
 * El codi de barres és productos.codigo, que la resta de l'admin ja tracta
 * com a únic (productes/crear.php, modificar.php i inventari.php ho
 * comproven). Aquí es manté la mateixa regla.
 *
 * Accions:
 *  - buscar    (GET)  codigo                   Busca també entre els inactius.
 *  - associar  (POST) codigo, id, substituir   Posa el codi a un producte existent.
 *  - reactivar (POST) id                       Torna a activar un producte inactiu.
 *  - crear     (POST) codigo, nombre, idSector, idTipo, idProcedencia, iva, precio_medio, pvp
 *
 * Totes tornen {success, producte?, error?}; producte té els mateixos camps
 * que l'array `productes` de les pàgines de compra.
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

header('Content-Type: application/json; charset=utf-8');

function respon(array $dades, int $codi = 200): void {
    http_response_code($codi);
    echo json_encode($dades, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Producte amb els camps que fa servir el selector de línies. */
function producteCompra(int $id): ?array {
    global $bd;
    $r = mysqli_query($bd, "SELECT p.id, p.nombre, p.codigo, p.precio_medio, p.iva, p.precio_venta_dis,
                                   COALESCE(pt.pvp, 0) as pvp, p.activo
                            FROM productos p
                            LEFT JOIN precios_producto_tienda pt ON pt.idProducto = p.id AND pt.idTienda = 1
                            WHERE p.id = $id
                            LIMIT 1");
    return ($r && ($row = mysqli_fetch_assoc($r))) ? $row : null;
}

/** Id del producte que ja té aquest codi (excloent-ne un), o 0. */
function idPerCodi(string $codigo, int $excepte = 0): int {
    global $bd;
    $stmt = mysqli_prepare($bd, "SELECT id FROM productos WHERE codigo = ? AND id != ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'si', $codigo, $excepte);
    mysqli_stmt_execute($stmt);
    // bind_result i no get_result: get_result exigeix mysqlnd.
    $trobat = 0;
    mysqli_stmt_bind_result($stmt, $trobat);
    $ok = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $ok ? (int)$trobat : 0;
}

/** Un codi de barres vàlid: sense espais i dins dels 20 caràcters de la columna. */
function netejaCodi(string $codigo): string {
    return preg_replace('/\s+/', '', $codigo);
}

$accio  = $_REQUEST['accio'] ?? '';
$codigo = netejaCodi($_REQUEST['codigo'] ?? '');

if ($accio !== 'buscar' && !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    respon(['success' => false, 'error' => 'Token CSRF invàlid. Recarrega la pàgina.'], 403);
}

if (in_array($accio, ['buscar', 'associar', 'crear'], true)) {
    if ($codigo === '') {
        respon(['success' => false, 'error' => 'Falta el codi de barres.'], 400);
    }
    if (mb_strlen($codigo) > 20) {
        respon(['success' => false, 'error' => 'El codi té més de 20 caràcters.'], 400);
    }
}

switch ($accio) {

    case 'buscar':
        $id = idPerCodi($codigo);
        respon(['success' => true, 'producte' => $id ? producteCompra($id) : null]);

    case 'associar':
        $id = (int)($_POST['id'] ?? 0);
        $producte = $id > 0 ? producteCompra($id) : null;
        if (!$producte) {
            respon(['success' => false, 'error' => 'Producte no trobat.'], 404);
        }

        $altre = idPerCodi($codigo, $id);
        if ($altre) {
            $p = producteCompra($altre);
            respon(['success' => false, 'error' => 'El codi ' . $codigo . ' ja és de «' . $p['nombre'] . '».'], 409);
        }

        // Un producte només té un codi: si ja en té un altre, cal confirmar
        // que se substitueix (el TPV deixarà de trobar-lo pel codi antic).
        $actual = trim($producte['codigo'] ?? '');
        if ($actual !== '' && $actual !== $codigo && empty($_POST['substituir'])) {
            respon(['success' => false, 'confirmar' => true, 'codiActual' => $actual,
                    'error' => '«' . $producte['nombre'] . '» ja té el codi ' . $actual . '.'], 409);
        }

        $stmt = mysqli_prepare($bd, "UPDATE productos SET codigo = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $codigo, $id);
        if (!mysqli_stmt_execute($stmt)) {
            respon(['success' => false, 'error' => mysqli_error($bd)], 500);
        }
        logActivity('producte_codi', "Codi $codigo associat a {$producte['nombre']} (ID: $id)"
            . ($actual !== '' && $actual !== $codigo ? " (abans: $actual)" : ''));
        respon(['success' => true, 'producte' => producteCompra($id)]);

    case 'reactivar':
        $id = (int)($_POST['id'] ?? 0);
        $producte = $id > 0 ? producteCompra($id) : null;
        if (!$producte) {
            respon(['success' => false, 'error' => 'Producte no trobat.'], 404);
        }
        mysqli_query($bd, "UPDATE productos SET activo = 1 WHERE id = $id");
        logActivity('producte_reactivar', "Producte reactivat des de compres: {$producte['nombre']} (ID: $id)");
        respon(['success' => true, 'producte' => producteCompra($id)]);

    case 'crear':
        $nombre        = trim($_POST['nombre'] ?? '');
        $idSector      = (int)($_POST['idSector'] ?? 0);
        $idTipo        = (int)($_POST['idTipo'] ?? 0);
        $idProcedencia = (int)($_POST['idProcedencia'] ?? 0);
        $iva           = (float)str_replace(',', '.', $_POST['iva'] ?? 21);
        $precioMedio   = (float)str_replace(',', '.', $_POST['precio_medio'] ?? 0);
        $pvp           = (float)str_replace(',', '.', $_POST['pvp'] ?? 0);

        $errors = [];
        if ($nombre === '')   $errors[] = 'El nom és obligatori.';
        if (mb_strlen($nombre) > 100) $errors[] = 'El nom té més de 100 caràcters.';
        if ($idSector <= 0)   $errors[] = 'Has de triar un sector.';
        if ($idTipo <= 0)     $errors[] = 'Has de triar un tipus.';
        if ($altre = idPerCodi($codigo)) {
            $p = producteCompra($altre);
            $errors[] = 'El codi ' . $codigo . ' ja és de «' . $p['nombre'] . '».';
        }
        if ($errors) {
            respon(['success' => false, 'error' => implode(' ', $errors)], 400);
        }

        // Mateixos camps i valors per defecte que productes/crear.php.
        $descripcion = '';
        $uvas        = '';
        $unidades    = 0;
        $pvd         = 0;
        $activo      = 1;
        $stmt = mysqli_prepare($bd, "INSERT INTO productos (codigo, nombre, descripcion, idSector, idTipo, idProcedencia,
                                     unidades, precio_medio, precio_venta_dis, iva, activo, uvas_es, alta)
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, 'sssiiidddiss',
            $codigo, $nombre, $descripcion, $idSector, $idTipo, $idProcedencia,
            $unidades, $precioMedio, $pvd, $iva, $activo, $uvas);
        if (!mysqli_stmt_execute($stmt)) {
            respon(['success' => false, 'error' => mysqli_error($bd)], 500);
        }
        $nouId = mysqli_insert_id($bd);

        if ($pvp > 0) {
            $stmtPvp = mysqli_prepare($bd, "INSERT INTO precios_producto_tienda (idTienda, idProducto, pvp) VALUES (1, ?, ?)");
            mysqli_stmt_bind_param($stmtPvp, 'id', $nouId, $pvp);
            mysqli_stmt_execute($stmtPvp);
        }

        logActivity('producto_crear', "Producte creat des de compres: $nombre (ID: $nouId, codi $codigo)");
        respon(['success' => true, 'producte' => producteCompra($nouId)]);

    default:
        respon(['success' => false, 'error' => 'Acció desconeguda.'], 400);
}
