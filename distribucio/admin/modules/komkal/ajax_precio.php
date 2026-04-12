<?php
/**
 * El Celler Nou - Mòdul Komkal - AJAX Price Update Endpoint
 * Actions: update_one, update_all
 */
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/config.php';
requireAuth();

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Token CSRF invàlid.']);
    exit;
}

$allowedTables = ['vinos', 'licores'];

switch ($action) {

    case 'update_one':
        $taula = $_POST['taula'] ?? '';
        if (!in_array($taula, $allowedTables)) {
            echo json_encode(['success' => false, 'error' => 'Taula no vàlida.']);
            exit;
        }
        $komkalId = (int)($_POST['komkal_id'] ?? 0);
        if ($komkalId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID invàlid.']);
            exit;
        }

        $taulaDb = 'komkal_' . $taula;

        // Get komkal entry
        $r = mysqli_query($bd, "SELECT id, id_producto_match, precio_sin_iva, nombre FROM $taulaDb WHERE id = $komkalId");
        if (!$r || mysqli_num_rows($r) === 0) {
            echo json_encode(['success' => false, 'error' => 'Registre Komkal no trobat.']);
            exit;
        }
        $komkal = mysqli_fetch_assoc($r);

        if (!$komkal['id_producto_match']) {
            echo json_encode(['success' => false, 'error' => 'Producte no vinculat.']);
            exit;
        }
        if ($komkal['precio_sin_iva'] === null) {
            echo json_encode(['success' => false, 'error' => 'Preu Komkal no disponible.']);
            exit;
        }

        $productoId = (int)$komkal['id_producto_match'];
        $newPrice = (float)$komkal['precio_sin_iva'];

        // Get current price for logging
        $rp = mysqli_query($bd, "SELECT precio_medio, nombre FROM productos WHERE id = $productoId");
        $prod = mysqli_fetch_assoc($rp);
        $oldPrice = (float)$prod['precio_medio'];

        // Update producto price (works regardless of stock - used for individual confirmations)
        $stmt = mysqli_prepare($bd, "UPDATE productos SET precio_medio = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'di', $newPrice, $productoId);
        $ok = mysqli_stmt_execute($stmt);

        if ($ok) {
            // Mark as updated in komkal
            $stmt2 = mysqli_prepare($bd, "UPDATE $taulaDb SET precio_actualizado = 1 WHERE id = ?");
            mysqli_stmt_bind_param($stmt2, 'i', $komkalId);
            mysqli_stmt_execute($stmt2);

            logActivity('komkal_precio', sprintf(
                "Preu actualitzat: %s (ID %d) %.2f -> %.2f (des de komkal_%s ID %d)",
                $prod['nombre'], $productoId, $oldPrice, $newPrice, $taula, $komkalId
            ));

            echo json_encode([
                'success' => true,
                'producto_id' => $productoId,
                'old_price' => $oldPrice,
                'new_price' => $newPrice
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error SQL: ' . mysqli_error($bd)]);
        }
        break;

    case 'update_all':
        $taula = $_POST['taula'] ?? '';
        if (!in_array($taula, $allowedTables)) {
            echo json_encode(['success' => false, 'error' => 'Taula no vàlida.']);
            exit;
        }

        $taulaDb = 'komkal_' . $taula;

        // Get all matched entries with price difference + stock info
        $sql = "SELECT k.id as komkal_id, k.precio_sin_iva,
                       m.id_producto as prod_id, p.nombre as prod_nombre, p.precio_medio,
                       (SELECT COUNT(*) FROM stock_producto sp WHERE sp.idProducto = p.id AND sp.vendido = 0) AS stock
                FROM $taulaDb k
                JOIN komkal_matches m ON k.codigo_komkal = m.codigo_komkal AND m.tipo_catalogo = '$taula'
                JOIN productos p ON m.id_producto = p.id
                WHERE k.precio_sin_iva IS NOT NULL
                AND ABS(k.precio_sin_iva - p.precio_medio) > 0.01";

        $updates = [];
        $r = mysqli_query($bd, $sql);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $stock = (int)$row['stock'];
                $komkalPrice = (float)$row['precio_sin_iva'];
                $currentPrice = (float)$row['precio_medio'];

                // Regla: stock 0 → sempre actualitza. Stock > 0 → només si Komkal és més car.
                if ($stock === 0 || $komkalPrice > $currentPrice + 0.01) {
                    $updates[] = $row;
                }
            }
        }

        if (empty($updates)) {
            echo json_encode(['success' => true, 'count' => 0, 'message' => 'No hi ha preus per actualitzar.']);
            exit;
        }

        $count = 0;
        $details = [];

        $stmtProd = mysqli_prepare($bd, "UPDATE productos SET precio_medio = ? WHERE id = ?");
        $stmtKomkal = mysqli_prepare($bd, "UPDATE $taulaDb SET precio_actualizado = 1 WHERE id = ?");

        foreach ($updates as $u) {
            $newPrice = (float)$u['precio_sin_iva'];
            $prodId = (int)$u['prod_id'];
            $komkalId = (int)$u['komkal_id'];
            $stock = (int)$u['stock'];

            mysqli_stmt_bind_param($stmtProd, 'di', $newPrice, $prodId);
            if (mysqli_stmt_execute($stmtProd)) {
                mysqli_stmt_bind_param($stmtKomkal, 'i', $komkalId);
                mysqli_stmt_execute($stmtKomkal);
                $count++;
                $tag = $stock > 0 ? ' [stock:'.$stock.']' : '';
                $details[] = sprintf("%s%s: %.2f -> %.2f", $u['prod_nombre'], $tag, (float)$u['precio_medio'], $newPrice);
            }
        }

        mysqli_stmt_close($stmtProd);
        mysqli_stmt_close($stmtKomkal);

        logActivity('komkal_precio_bulk', "Actualitzats $count preus des de komkal_$taula");

        echo json_encode([
            'success' => true,
            'count' => $count,
            'details' => array_slice($details, 0, 50)
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Acció no vàlida.']);
        break;
}
