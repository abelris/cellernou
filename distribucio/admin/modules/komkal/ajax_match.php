<?php
/**
 * El Celler Nou - Mòdul Komkal - AJAX Match Endpoint
 * Actions: match, unmatch, search, automatch
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

// Table whitelist
$allowedTables = ['vinos', 'licores'];

/**
 * Create URL slug from a string (same logic as public site's createSlug).
 */
function createSlugAdmin(string $string): string {
    $string = strtolower($string);
    $string = preg_replace('/[áàäâ]/u', 'a', $string);
    $string = preg_replace('/[éèëê]/u', 'e', $string);
    $string = preg_replace('/[íìïî]/u', 'i', $string);
    $string = preg_replace('/[óòöô]/u', 'o', $string);
    $string = preg_replace('/[úùüû]/u', 'u', $string);
    $string = preg_replace('/ñ/u', 'n', $string);
    $string = preg_replace('/ç/u', 'c', $string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    return trim($string, '-');
}

/**
 * Normalize a product name for fuzzy comparison.
 */
function normalizeForMatch(string $name): string {
    $name = mb_strtoupper($name, 'UTF-8');
    $accents = ['À','Á','Â','Ã','Ä','Å','È','É','Ê','Ë','Ì','Í','Î','Ï','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ñ','Ç'];
    $plain   = ['A','A','A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','N','C'];
    $name = str_replace($accents, $plain, $name);
    $name = preg_replace('/\b(75|70|37\.5|50)\s*CL\b/i', '', $name);
    $name = preg_replace('/\b1\s*L\b/i', '', $name);
    $name = preg_replace('/\b1\.5\s*L\b/i', '', $name);
    $name = preg_replace('/\s+/', ' ', trim($name));
    return $name;
}

switch ($action) {

    case 'match':
        $taula = $_POST['taula'] ?? '';
        if (!in_array($taula, $allowedTables)) {
            echo json_encode(['success' => false, 'error' => 'Taula no vàlida.']);
            exit;
        }
        $komkalId = (int)($_POST['komkal_id'] ?? 0);
        $productoId = (int)($_POST['producto_id'] ?? 0);

        if ($komkalId <= 0 || $productoId <= 0) {
            echo json_encode(['success' => false, 'error' => 'IDs invàlids.']);
            exit;
        }

        // Verify producto exists
        $r = mysqli_query($bd, "SELECT id FROM productos WHERE id = $productoId");
        if (!$r || mysqli_num_rows($r) === 0) {
            echo json_encode(['success' => false, 'error' => 'Producte no trobat.']);
            exit;
        }

        $taulaDb = 'komkal_' . $taula;
        $stmt = mysqli_prepare($bd, "UPDATE $taulaDb SET id_producto_match = ?, match_tipo = 'manual' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $productoId, $komkalId);
        $ok = mysqli_stmt_execute($stmt);

        if ($ok) {
            // Sync to permanent komkal_matches table
            $rCode = mysqli_query($bd, "SELECT codigo_komkal FROM $taulaDb WHERE id = $komkalId");
            if ($rCode && $rowCode = mysqli_fetch_assoc($rCode)) {
                $codigoKomkal = $rowCode['codigo_komkal'];
                if (!empty($codigoKomkal)) {
                    $stmtPerm = mysqli_prepare($bd, "INSERT INTO komkal_matches (codigo_komkal, tipo_catalogo, id_producto, match_tipo)
                        VALUES (?, ?, ?, 'manual')
                        ON DUPLICATE KEY UPDATE id_producto = VALUES(id_producto), match_tipo = 'manual', updated_at = CURRENT_TIMESTAMP");
                    mysqli_stmt_bind_param($stmtPerm, 'ssi', $codigoKomkal, $taula, $productoId);
                    mysqli_stmt_execute($stmtPerm);
                }
            }

            logActivity('komkal_match', "Vinculat komkal_$taula ID $komkalId -> producto ID $productoId (manual)");
            echo json_encode(['success' => true]);

            // Create 301 redirect (best-effort, non-blocking — response already sent)
            try {
                $rKomkal = @mysqli_query($bd, "SELECT nombre FROM $taulaDb WHERE id = $komkalId");
                $rProd = @mysqli_query($bd, "SELECT nombre FROM productos WHERE id = $productoId");
                if ($rKomkal && $rProd) {
                    $komkalNom = mysqli_fetch_assoc($rKomkal)['nombre'];
                    $prodNom = mysqli_fetch_assoc($rProd)['nombre'];
                    $urlFrom = '/producto/k/' . $taula . '/' . $komkalId . '/' . createSlugAdmin($komkalNom);
                    $urlTo = '/producto/' . $productoId . '/' . createSlugAdmin($prodNom);
                    @mysqli_query($bd, "INSERT INTO redirects_301 (url_from, url_to) VALUES ('" . mysqli_real_escape_string($bd, $urlFrom) . "', '" . mysqli_real_escape_string($bd, $urlTo) . "') ON DUPLICATE KEY UPDATE url_to = VALUES(url_to)");
                }
            } catch (Exception $e) {
                // Silent — redirect is nice-to-have, match is what matters
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Error SQL: ' . mysqli_error($bd)]);
        }
        break;

    case 'unmatch':
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

        // Get codigo_komkal before unmatching, to delete from permanent table
        $rCode = mysqli_query($bd, "SELECT codigo_komkal FROM $taulaDb WHERE id = $komkalId");
        $codigoKomkal = null;
        if ($rCode && $rowCode = mysqli_fetch_assoc($rCode)) {
            $codigoKomkal = $rowCode['codigo_komkal'];
        }

        $stmt = mysqli_prepare($bd, "UPDATE $taulaDb SET id_producto_match = NULL, match_tipo = NULL, precio_actualizado = 0 WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $komkalId);
        $ok = mysqli_stmt_execute($stmt);

        if ($ok) {
            // Delete from permanent komkal_matches table
            if (!empty($codigoKomkal)) {
                $stmtDel = mysqli_prepare($bd, "DELETE FROM komkal_matches WHERE codigo_komkal = ? AND tipo_catalogo = ?");
                mysqli_stmt_bind_param($stmtDel, 'ss', $codigoKomkal, $taula);
                mysqli_stmt_execute($stmtDel);
            }

            logActivity('komkal_unmatch', "Desvinculat komkal_$taula ID $komkalId");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error SQL: ' . mysqli_error($bd)]);
        }
        break;

    case 'search':
        $query = trim($_POST['query'] ?? '');
        if (strlen($query) < 2) {
            echo json_encode(['success' => true, 'results' => []]);
            exit;
        }

        $querySafe = mysqli_real_escape_string($bd, $query);
        $sql = "SELECT p.id, p.nombre, p.codigo, p.precio_medio, s.nombre_es as sector
                FROM productos p
                LEFT JOIN sectores s ON p.idSector = s.id
                WHERE p.activo = 1
                AND (p.nombre LIKE '%$querySafe%' OR p.codigo LIKE '%$querySafe%')
                ORDER BY p.nombre ASC
                LIMIT 20";

        $results = [];
        $r = mysqli_query($bd, $sql);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $results[] = $row;
            }
        }

        echo json_encode(['success' => true, 'results' => $results]);
        break;

    case 'automatch':
        $taula = $_POST['taula'] ?? '';
        if (!in_array($taula, $allowedTables)) {
            echo json_encode(['success' => false, 'error' => 'Taula no vàlida.']);
            exit;
        }

        $taulaDb = 'komkal_' . $taula;
        $counts = ['codigo' => 0, 'fuzzy' => 0];

        // Phase 1: Code match
        $sql = "UPDATE $taulaDb k
                JOIN productos p ON k.codigo_komkal = p.codigo AND p.codigo != '' AND p.codigo IS NOT NULL
                SET k.id_producto_match = p.id, k.match_tipo = 'auto_codigo'
                WHERE k.id_producto_match IS NULL AND p.activo = 1";
        mysqli_query($bd, $sql);
        $counts['codigo'] = mysqli_affected_rows($bd);

        // Save new auto_codigo matches to permanent komkal_matches
        if ($counts['codigo'] > 0) {
            $sqlSaveCodigo = "INSERT INTO komkal_matches (codigo_komkal, tipo_catalogo, id_producto, match_tipo)
                SELECT k.codigo_komkal, '$taula', k.id_producto_match, 'auto_codigo'
                FROM $taulaDb k
                WHERE k.match_tipo = 'auto_codigo'
                AND k.codigo_komkal != '' AND k.codigo_komkal IS NOT NULL
                ON DUPLICATE KEY UPDATE id_producto = VALUES(id_producto), match_tipo = 'auto_codigo', updated_at = CURRENT_TIMESTAMP";
            mysqli_query($bd, $sqlSaveCodigo);
        }

        // Phase 2: Fuzzy name match
        $unmatched = [];
        $r = mysqli_query($bd, "SELECT id, nombre FROM $taulaDb WHERE id_producto_match IS NULL");
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $unmatched[] = $row;
            }
        }

        if (!empty($unmatched)) {
            $productos = [];
            $rp = mysqli_query($bd, "SELECT id, nombre FROM productos WHERE activo = 1");
            if ($rp) {
                while ($row = mysqli_fetch_assoc($rp)) {
                    $productos[] = $row;
                }
            }

            foreach ($unmatched as $komkal) {
                $normK = normalizeForMatch($komkal['nombre']);
                if (strlen($normK) < 3) continue;

                $bestId = null;
                $bestScore = 0;
                $ambiguous = false;

                foreach ($productos as $prod) {
                    $normP = normalizeForMatch($prod['nombre']);
                    if (strlen($normP) < 3) continue;

                    $score = 0;
                    similar_text($normK, $normP, $score);

                    if ($score >= 75) {
                        if ($bestId === null || $score > $bestScore + 2) {
                            $bestId = $prod['id'];
                            $bestScore = $score;
                            $ambiguous = false;
                        } elseif (abs($score - $bestScore) <= 2 && $prod['id'] !== $bestId) {
                            $ambiguous = true;
                        }
                    }
                }

                if ($bestId !== null && !$ambiguous) {
                    $stmt = mysqli_prepare($bd, "UPDATE $taulaDb SET id_producto_match = ?, match_tipo = 'auto_nom' WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, 'ii', $bestId, $komkal['id']);
                    mysqli_stmt_execute($stmt);
                    if (mysqli_affected_rows($bd) > 0) {
                        $counts['fuzzy']++;
                        // Save new auto-match to permanent komkal_matches
                        $rCode2 = mysqli_query($bd, "SELECT codigo_komkal FROM $taulaDb WHERE id = " . (int)$komkal['id']);
                        if ($rCode2 && $rowCode2 = mysqli_fetch_assoc($rCode2)) {
                            $ck = $rowCode2['codigo_komkal'];
                            if (!empty($ck)) {
                                $stmtPerm2 = mysqli_prepare($bd, "INSERT INTO komkal_matches (codigo_komkal, tipo_catalogo, id_producto, match_tipo)
                                    VALUES (?, ?, ?, 'auto_nom')
                                    ON DUPLICATE KEY UPDATE id_producto = VALUES(id_producto), match_tipo = 'auto_nom', updated_at = CURRENT_TIMESTAMP");
                                mysqli_stmt_bind_param($stmtPerm2, 'ssi', $ck, $taula, $bestId);
                                mysqli_stmt_execute($stmtPerm2);
                            }
                        }
                    }
                }
            }
        }

        logActivity('komkal_automatch', "Auto-match $taula: codi={$counts['codigo']}, nom={$counts['fuzzy']}");
        echo json_encode([
            'success' => true,
            'codigo' => $counts['codigo'],
            'fuzzy' => $counts['fuzzy']
        ]);
        break;

    case 'crear_tots':
        // Bulk create: all unmatched komkal entries as new products
        // Vinos: 25% margin, Licores: 20% margin, IVA 21%
        $taula = $_POST['taula'] ?? '';
        if (!in_array($taula, $allowedTables)) {
            echo json_encode(['success' => false, 'error' => 'Taula no vàlida.']);
            exit;
        }

        $taulaDb = 'komkal_' . $taula;
        $defaultSector = ($taula === 'vinos') ? 8 : 2;
        $margen = ($taula === 'vinos') ? 0.25 : 0.20;
        $iva = 21;

        // Get unmatched entries with price
        $r = mysqli_query($bd, "SELECT id, codigo_komkal, nombre, precio_sin_iva
                                FROM $taulaDb
                                WHERE id_producto_match IS NULL
                                AND precio_sin_iva IS NOT NULL
                                AND precio_sin_iva > 0
                                ORDER BY nombre");
        $entries = [];
        while ($row = mysqli_fetch_assoc($r)) {
            $entries[] = $row;
        }

        if (empty($entries)) {
            echo json_encode(['success' => true, 'count' => 0, 'message' => 'No hi ha productes pendents per crear.']);
            exit;
        }

        $stmtProd = mysqli_prepare($bd, "INSERT INTO productos (codigo, nombre, idSector, precio_medio, precio_venta_dis, iva, activo, alta) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
        $stmtPvp = mysqli_prepare($bd, "INSERT INTO precios_producto_tienda (idTienda, idProducto, pvp) VALUES (1, ?, ?)");
        $stmtLink = mysqli_prepare($bd, "UPDATE $taulaDb SET id_producto_match = ?, match_tipo = 'nuevo' WHERE id = ?");
        $stmtMatch = mysqli_prepare($bd, "INSERT INTO komkal_matches (codigo_komkal, tipo_catalogo, id_producto, match_tipo) VALUES (?, ?, ?, 'nuevo') ON DUPLICATE KEY UPDATE id_producto = VALUES(id_producto), match_tipo = 'nuevo'");

        $count = 0;
        $errorsArr = [];

        foreach ($entries as $e) {
            $precio = (float)$e['precio_sin_iva'];
            $ventaDis = round($precio * (1 + $margen), 2);
            $pvp = round($ventaDis * (1 + $iva / 100), 2);
            $nombre = $e['nombre'];
            $codigo = $e['codigo_komkal'];

            mysqli_stmt_bind_param($stmtProd, 'ssiddi', $codigo, $nombre, $defaultSector, $precio, $ventaDis, $iva);
            if (mysqli_stmt_execute($stmtProd)) {
                $newId = mysqli_insert_id($bd);

                // PVP
                mysqli_stmt_bind_param($stmtPvp, 'id', $newId, $pvp);
                mysqli_stmt_execute($stmtPvp);

                // Link komkal entry
                $komkalId = (int)$e['id'];
                mysqli_stmt_bind_param($stmtLink, 'ii', $newId, $komkalId);
                mysqli_stmt_execute($stmtLink);

                // Permanent match
                mysqli_stmt_bind_param($stmtMatch, 'ssi', $codigo, $taula, $newId);
                mysqli_stmt_execute($stmtMatch);

                $count++;
            } else {
                $errorsArr[] = $nombre . ': ' . mysqli_error($bd);
            }
        }

        logActivity('komkal_crear_bulk', "Creats $count productes nous des de komkal_$taula (marge " . ($margen * 100) . "%)");

        echo json_encode([
            'success' => true,
            'count' => $count,
            'total' => count($entries),
            'errors' => array_slice($errorsArr, 0, 10)
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Acció no vàlida.']);
        break;
}
