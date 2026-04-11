<?php
/**
 * OCR Factures - Guardar factura de proveïdor
 * Crea la cadena completa: compra → línies → stock → factura → línies factura
 */

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/connexio/connexio.php';

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/ocr/'));
    exit;
}

// CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['ocr_error'] = 'Error de seguretat: token CSRF invàlid.';
    header('Location: ' . getAdminUrl('modules/ocr/'));
    exit;
}

// Recoger datos
$idProveedor = (int)($_POST['idProveedor'] ?? 0);
$numFactura = trim($_POST['numFactura'] ?? '');
$fechaEmision = trim($_POST['fechaEmision'] ?? '');
$fechaVencimiento = trim($_POST['fechaVencimiento'] ?? '');
$idFormaPago = (int)($_POST['idFormaPago'] ?? 0);

// Validación
$errors = [];
if ($idProveedor <= 0) $errors[] = 'Cal seleccionar un proveïdor.';
if (empty($numFactura)) $errors[] = 'El número de factura és obligatori.';

// Líneas
$lineaProducto = $_POST['linea_producto'] ?? [];
$lineaCantidad = $_POST['linea_cantidad'] ?? [];
$lineaPrecio = $_POST['linea_precio'] ?? [];
$lineaDescuento = $_POST['linea_descuento'] ?? [];
$lineaIva = $_POST['linea_iva'] ?? [];

$lineas = [];
for ($i = 0; $i < count($lineaProducto); $i++) {
    $idProducto = (int)($lineaProducto[$i] ?? 0);
    $cantidad = (float)str_replace(',', '.', $lineaCantidad[$i] ?? 0);
    $precioUnidadSinDesc = (float)str_replace(',', '.', $lineaPrecio[$i] ?? 0);
    $descuento = (float)str_replace(',', '.', $lineaDescuento[$i] ?? 0);
    $iva = (float)str_replace(',', '.', $lineaIva[$i] ?? 0);

    if ($idProducto <= 0 || $cantidad <= 0) continue;

    $precioUnidad = $precioUnidadSinDesc * (1 - $descuento / 100);

    $lineas[] = [
        'idProducto' => $idProducto,
        'cantidad' => $cantidad,
        'precio_unidad' => $precioUnidad,
        'precio_unidad_sin_desc' => $precioUnidadSinDesc,
        'descuento' => $descuento,
        'iva' => $iva
    ];
}

if (empty($lineas)) $errors[] = 'Cal afegir almenys una línia de compra.';

if (!empty($errors)) {
    $_SESSION['ocr_error'] = implode('<br>', $errors);
    header('Location: ' . getAdminUrl('modules/ocr/'));
    exit;
}

// Calcular totales
$totalBase = 0;
$totalIva = 0;
foreach ($lineas as $l) {
    $lineaBase = $l['cantidad'] * $l['precio_unidad'];
    $totalBase += $lineaBase;
    $totalIva += $lineaBase * ($l['iva'] / 100);
}
$total = $totalBase + $totalIva;

// Fechas
$fechaE = !empty($fechaEmision) ? date('Y-m-d', strtotime($fechaEmision)) : date('Y-m-d');
$fechaV = !empty($fechaVencimiento) ? date('Y-m-d', strtotime($fechaVencimiento)) : null;

// --- TRANSACCIÓN ---
mysqli_begin_transaction($bd);

try {
    // 1. INSERT compras (idEstado=3 directamente, ya que creamos compra+factura juntos)
    $sql = "INSERT INTO compras (idProveedor, referencia, fecha, oferta, total_base, total_iva, total, idEstado, idFormaPago)
            VALUES (?, ?, ?, ?, ?, ?, ?, 3, ?)";
    $stmt = mysqli_prepare($bd, $sql);
    $referencia = 'OCR-' . $numFactura;
    $oferta = '';
    mysqli_stmt_bind_param($stmt, 'isssdddi',
        $idProveedor,
        $referencia,
        $fechaE,
        $oferta,
        $totalBase,
        $totalIva,
        $total,
        $idFormaPago
    );
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al crear la compra: ' . mysqli_error($bd));
    }
    $idCompra = mysqli_insert_id($bd);

    // 2. INSERT linea_compra + stock_producto
    foreach ($lineas as $l) {
        $sqlLinea = "INSERT INTO linea_compra (idCompra, idProducto, idProveedor, cantidad, precio_unidad, descuento, precio_unidad_sin_desc, iva)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtLinea = mysqli_prepare($bd, $sqlLinea);
        mysqli_stmt_bind_param($stmtLinea, 'iiiddddd',
            $idCompra,
            $l['idProducto'],
            $idProveedor,
            $l['cantidad'],
            $l['precio_unidad'],
            $l['descuento'],
            $l['precio_unidad_sin_desc'],
            $l['iva']
        );
        if (!mysqli_stmt_execute($stmtLinea)) {
            throw new Exception('Error al crear línia de compra: ' . mysqli_error($bd));
        }

        // Stock: 1 fila per unitat
        for ($s = 0; $s < $l['cantidad']; $s++) {
            $sqlStock = "INSERT INTO stock_producto (idTienda, idCompra, idProducto, fecha_entrada, precio_compra) VALUES (1, ?, ?, NOW(), ?)";
            $stmtStock = mysqli_prepare($bd, $sqlStock);
            mysqli_stmt_bind_param($stmtStock, 'iid', $idCompra, $l['idProducto'], $l['precio_unidad_sin_desc']);
            if (!mysqli_stmt_execute($stmtStock)) {
                throw new Exception('Error al crear stock: ' . mysqli_error($bd));
            }
        }
    }

    // 3. INSERT facturas_compra
    $sqlFactura = "INSERT INTO facturas_compra (numFactura, idProveedor, descuento, precio_desc, coste, precio_coste,
                   idEstado, idCompra, fecha, fecha_vencimiento, total_base, total_iva, total, comentarios)
                   VALUES (?, ?, '', 0, '', 0, 0, ?, ?, ?, ?, ?, ?, 'Creada des de OCR')";
    $stmtFactura = mysqli_prepare($bd, $sqlFactura);
    mysqli_stmt_bind_param($stmtFactura, 'siissdds',
        $numFactura,
        $idProveedor,
        $idCompra,
        $fechaE,
        $fechaV,
        $totalBase,
        $totalIva,
        $total
    );
    if (!mysqli_stmt_execute($stmtFactura)) {
        throw new Exception('Error al crear la factura: ' . mysqli_error($bd));
    }
    $idFactura = mysqli_insert_id($bd);

    // 4. Copiar línies de compra a línies de factura
    $sqlCopiar = "INSERT INTO linea_factura_compra (idFactura, idProducto, idProveedor, cantidad, precio_unidad, descuento, precio_unidad_sin_desc, iva)
                  SELECT $idFactura, idProducto, idProveedor, cantidad, precio_unidad, descuento, precio_unidad_sin_desc, iva
                  FROM linea_compra WHERE idCompra = $idCompra";
    if (!mysqli_query($bd, $sqlCopiar)) {
        throw new Exception('Error al copiar línies de factura: ' . mysqli_error($bd));
    }

    // COMMIT
    mysqli_commit($bd);

    logActivity('ocr_factura_crear', "Factura OCR creada: #$idFactura ($numFactura) - Compra #$idCompra - Proveïdor: $idProveedor");
    header('Location: ' . getAdminUrl("modules/factures_compra/veure.php?id=$idFactura"));
    exit;

} catch (Exception $e) {
    mysqli_rollback($bd);
    $_SESSION['ocr_error'] = $e->getMessage();
    header('Location: ' . getAdminUrl('modules/ocr/'));
    exit;
}
