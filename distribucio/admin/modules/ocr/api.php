<?php
/**
 * OCR Factures - API AJAX
 * Endpoints per autocomplete de productes/proveïdors i mapatge OCR
 */

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Connexió BD
require_once dirname(dirname(dirname(__DIR__))) . '/connexio/connexio.php';

// Crear taules OCR si no existeixen
mysqli_query($bd, "CREATE TABLE IF NOT EXISTS ocr_proveedor_map (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ocr_text VARCHAR(255) NOT NULL,
    idProveedor INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (ocr_text)
)");

mysqli_query($bd, "CREATE TABLE IF NOT EXISTS ocr_proveedor_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idProveedor INT NOT NULL,
    col_referencia INT DEFAULT -1,
    col_nombre INT DEFAULT -1,
    col_cantidad INT DEFAULT -1,
    col_precio INT DEFAULT -1,
    col_iva INT DEFAULT -1,
    decimal_sep VARCHAR(1) DEFAULT ',',
    miles_sep VARCHAR(1) DEFAULT '.',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (idProveedor)
)");

// Migració: afegir col_iva si no existeix (per instal·lacions anteriors)
$colCheck = mysqli_query($bd, "SHOW COLUMNS FROM ocr_proveedor_config LIKE 'col_iva'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    mysqli_query($bd, "ALTER TABLE ocr_proveedor_config ADD COLUMN col_iva INT DEFAULT -1 AFTER col_precio");
}

// Migració: afegir linea_inicio, linea_fin, col_gap_px si no existeixen
$colCheck = mysqli_query($bd, "SHOW COLUMNS FROM ocr_proveedor_config LIKE 'linea_inicio'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    mysqli_query($bd, "ALTER TABLE ocr_proveedor_config ADD COLUMN linea_inicio VARCHAR(255) DEFAULT '' AFTER miles_sep");
    mysqli_query($bd, "ALTER TABLE ocr_proveedor_config ADD COLUMN linea_fin VARCHAR(255) DEFAULT '' AFTER linea_inicio");
    mysqli_query($bd, "ALTER TABLE ocr_proveedor_config ADD COLUMN col_gap_px INT DEFAULT -1 AFTER linea_fin");
}

// Taula de mapatge referència proveïdor → producte
mysqli_query($bd, "CREATE TABLE IF NOT EXISTS ocr_producto_map (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idProveedor INT NOT NULL,
    ref_proveedor VARCHAR(255) NOT NULL,
    idProducto INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (idProveedor, ref_proveedor)
)");

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {

    // --- Cerca productes ---
    case 'search_products':
        $query = trim($_GET['q'] ?? '');
        if (mb_strlen($query) < 2) { echo json_encode([]); exit; }
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT id, nombre, codigo, pvp, iva FROM productos WHERE (nombre LIKE ? OR codigo LIKE ?) ORDER BY nombre LIMIT 20";
        $stmt = mysqli_prepare($bd, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $products = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = [
                'id' => (int)$row['id'],
                'nombre' => $row['nombre'],
                'codigo' => $row['codigo'],
                'pvp' => (float)$row['pvp'],
                'iva' => (float)$row['iva']
            ];
        }
        echo json_encode($products);
        break;

    // --- Cerca proveïdors ---
    case 'search_providers':
        $query = trim($_GET['q'] ?? '');
        if (mb_strlen($query) < 2) { echo json_encode([]); exit; }
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT id, nombre FROM proveedores WHERE nombre LIKE ? ORDER BY nombre LIMIT 20";
        $stmt = mysqli_prepare($bd, $sql);
        mysqli_stmt_bind_param($stmt, 's', $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $providers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $providers[] = [
                'id' => (int)$row['id'],
                'nombre' => $row['nombre']
            ];
        }
        echo json_encode($providers);
        break;

    // --- Buscar match automàtic per text OCR (CIF/NIF o nom) ---
    case 'match_provider':
        $texts = $_GET['texts'] ?? '';
        if (empty($texts)) { echo json_encode(['found' => false]); exit; }

        // texts pot ser una llista separada per | (CIF|nom empresa)
        $candidates = array_filter(array_map('trim', explode('|', $texts)));
        if (empty($candidates)) { echo json_encode(['found' => false]); exit; }

        // Buscar en la taula de mapatge
        $placeholders = implode(',', array_fill(0, count($candidates), '?'));
        $types = str_repeat('s', count($candidates));
        $sql = "SELECT m.ocr_text, m.idProveedor, p.nombre
                FROM ocr_proveedor_map m
                JOIN proveedores p ON p.id = m.idProveedor
                WHERE m.ocr_text IN ($placeholders)
                LIMIT 1";
        $stmt = mysqli_prepare($bd, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$candidates);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode([
                'found' => true,
                'idProveedor' => (int)$row['idProveedor'],
                'nombre' => $row['nombre'],
                'matched_text' => $row['ocr_text']
            ]);
        } else {
            echo json_encode(['found' => false]);
        }
        break;

    // --- Guardar mapatge OCR→proveïdor ---
    case 'save_provider_map':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Mètode no permès']);
            exit;
        }
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Token CSRF invàlid']);
            exit;
        }

        $ocrText = trim($_POST['ocr_text'] ?? '');
        $idProveedor = (int)($_POST['idProveedor'] ?? 0);

        if (empty($ocrText) || $idProveedor <= 0) {
            echo json_encode(['success' => false, 'error' => 'Dades incompletes']);
            exit;
        }

        // INSERT OR UPDATE (per si ja existia amb un altre proveïdor)
        $sql = "INSERT INTO ocr_proveedor_map (ocr_text, idProveedor) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE idProveedor = VALUES(idProveedor)";
        $stmt = mysqli_prepare($bd, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $ocrText, $idProveedor);

        if (mysqli_stmt_execute($stmt)) {
            logActivity('ocr_map_proveidor', "Mapatge OCR guardat: '$ocrText' → Proveïdor #$idProveedor");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($bd)]);
        }
        break;

    // --- Obtenir configuració OCR d'un proveïdor ---
    case 'get_provider_config':
        $idProveedor = (int)($_GET['idProveedor'] ?? 0);
        if ($idProveedor <= 0) { echo json_encode(['found' => false]); exit; }

        $sql = "SELECT col_referencia, col_nombre, col_cantidad, col_precio, col_iva, decimal_sep, miles_sep, linea_inicio, linea_fin, col_gap_px FROM ocr_proveedor_config WHERE idProveedor = ?";
        $stmt = mysqli_prepare($bd, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $idProveedor);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode([
                'found' => true,
                'col_referencia' => (int)$row['col_referencia'],
                'col_nombre' => (int)$row['col_nombre'],
                'col_cantidad' => (int)$row['col_cantidad'],
                'col_precio' => (int)$row['col_precio'],
                'col_iva' => (int)$row['col_iva'],
                'decimal_sep' => $row['decimal_sep'],
                'miles_sep' => $row['miles_sep'],
                'linea_inicio' => $row['linea_inicio'] ?? '',
                'linea_fin' => $row['linea_fin'] ?? '',
                'col_gap_px' => (int)($row['col_gap_px'] ?? -1)
            ]);
        } else {
            echo json_encode(['found' => false]);
        }
        break;

    // --- Guardar configuració OCR d'un proveïdor ---
    case 'save_provider_config':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Mètode no permès']);
            exit;
        }
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Token CSRF invàlid']);
            exit;
        }

        $idProveedor = (int)($_POST['idProveedor'] ?? 0);
        $colRef = (int)($_POST['col_referencia'] ?? -1);
        $colNom = (int)($_POST['col_nombre'] ?? -1);
        $colQty = (int)($_POST['col_cantidad'] ?? -1);
        $colPreu = (int)($_POST['col_precio'] ?? -1);
        $colIva = (int)($_POST['col_iva'] ?? -1);
        $decSep = substr(trim($_POST['decimal_sep'] ?? ','), 0, 1);
        $milSep = substr(trim($_POST['miles_sep'] ?? '.'), 0, 1);
        $lineaInicio = trim($_POST['linea_inicio'] ?? '');
        $lineaFin = trim($_POST['linea_fin'] ?? '');
        $colGapPx = (int)($_POST['col_gap_px'] ?? -1);

        if ($idProveedor <= 0) {
            echo json_encode(['success' => false, 'error' => 'Proveïdor invàlid']);
            exit;
        }

        $sql = "INSERT INTO ocr_proveedor_config (idProveedor, col_referencia, col_nombre, col_cantidad, col_precio, col_iva, decimal_sep, miles_sep, linea_inicio, linea_fin, col_gap_px)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE col_referencia=VALUES(col_referencia), col_nombre=VALUES(col_nombre),
                col_cantidad=VALUES(col_cantidad), col_precio=VALUES(col_precio), col_iva=VALUES(col_iva),
                decimal_sep=VALUES(decimal_sep), miles_sep=VALUES(miles_sep),
                linea_inicio=VALUES(linea_inicio), linea_fin=VALUES(linea_fin), col_gap_px=VALUES(col_gap_px)";
        $stmt = mysqli_prepare($bd, $sql);
        mysqli_stmt_bind_param($stmt, 'iiiiiissssi', $idProveedor, $colRef, $colNom, $colQty, $colPreu, $colIva, $decSep, $milSep, $lineaInicio, $lineaFin, $colGapPx);

        if (mysqli_stmt_execute($stmt)) {
            logActivity('ocr_config_proveidor', "Config OCR guardada per proveïdor #$idProveedor: ref=$colRef nom=$colNom qty=$colQty preu=$colPreu iva=$colIva dec=$decSep mil=$milSep inicio=$lineaInicio fin=$lineaFin gap=$colGapPx");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($bd)]);
        }
        break;

    // --- Buscar match automàtic de productes per referències d'un proveïdor ---
    case 'match_products':
        $idProveedor = (int)($_GET['idProveedor'] ?? 0);
        $refsJson = $_GET['refs'] ?? '[]';
        $refs = json_decode($refsJson, true);

        if ($idProveedor <= 0 || !is_array($refs) || empty($refs)) {
            echo json_encode(['matches' => new \stdClass()]);
            exit;
        }

        // Netejar i filtrar refs buides
        $refs = array_values(array_filter(array_map('trim', $refs), function($r) { return $r !== ''; }));
        if (empty($refs)) {
            echo json_encode(['matches' => new \stdClass()]);
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($refs), '?'));
        $types = 'i' . str_repeat('s', count($refs));
        $sql = "SELECT m.ref_proveedor, m.idProducto, p.nombre
                FROM ocr_producto_map m
                JOIN productos p ON p.id = m.idProducto
                WHERE m.idProveedor = ? AND m.ref_proveedor IN ($placeholders)";
        $stmt = mysqli_prepare($bd, $sql);
        $params = array_merge([$idProveedor], $refs);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $matches = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $matches[$row['ref_proveedor']] = [
                'idProducto' => (int)$row['idProducto'],
                'nombre' => $row['nombre']
            ];
        }
        echo json_encode(['matches' => empty($matches) ? new \stdClass() : $matches]);
        break;

    // --- Guardar mapatge referència proveïdor → producte ---
    case 'save_product_map':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Mètode no permès']);
            exit;
        }
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Token CSRF invàlid']);
            exit;
        }

        $idProveedor = (int)($_POST['idProveedor'] ?? 0);
        $refProveedor = trim($_POST['ref_proveedor'] ?? '');
        $idProducto = (int)($_POST['idProducto'] ?? 0);

        if ($idProveedor <= 0 || empty($refProveedor) || $idProducto <= 0) {
            echo json_encode(['success' => false, 'error' => 'Dades incompletes']);
            exit;
        }

        $sql = "INSERT INTO ocr_producto_map (idProveedor, ref_proveedor, idProducto) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE idProducto = VALUES(idProducto)";
        $stmt = mysqli_prepare($bd, $sql);
        mysqli_stmt_bind_param($stmt, 'isi', $idProveedor, $refProveedor, $idProducto);

        if (mysqli_stmt_execute($stmt)) {
            logActivity('ocr_map_producte', "Mapatge producte guardat: Prov #$idProveedor ref='$refProveedor' → Producte #$idProducto");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($bd)]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acció no vàlida']);
        break;
}
