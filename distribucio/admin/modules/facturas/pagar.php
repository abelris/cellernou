<?php
/**
 * El Celler Nou - Mòdul Factures - Gestió de Pagaments
 * Suporta pagament complet i pagaments a compte (parcials)
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/facturas/'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/facturas/?msg=error'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$mode = $_POST['mode'] ?? 'full'; // 'full' o 'partial'

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/facturas/?msg=error'));
    exit;
}

// Verificar que la factura existe
$result = mysqli_query($bd, "SELECT id, idCliente, fecha, fecha_pago FROM facturas WHERE id = $id");
$factura = $result ? mysqli_fetch_assoc($result) : null;

if (!$factura) {
    header('Location: ' . getAdminUrl('modules/facturas/?msg=error'));
    exit;
}

// Calcular total factura (base + IVA + recàrrec)
$totalFactura = 0;
$rsClient = mysqli_query($bd, "SELECT recarrec_equiv FROM clientes WHERE id = {$factura['idCliente']}");
$clientData = $rsClient ? mysqli_fetch_assoc($rsClient) : null;
$recarrecEquiv = $clientData['recarrec_equiv'] ?? 0;

$rsLineas = mysqli_query($bd, "SELECT cantidad, precio_unidad, iva FROM linea_factura WHERE idFactura = $id");
if ($rsLineas) {
    while ($l = mysqli_fetch_assoc($rsLineas)) {
        $base = $l['cantidad'] * $l['precio_unidad'];
        $iva = $base * ($l['iva'] / 100);
        $recarrec = 0;
        if ($recarrecEquiv) {
            $recarrecRates = [4 => 0.005, 10 => 0.014, 21 => 0.052];
            $ivaRate = (int)$l['iva'];
            if (isset($recarrecRates[$ivaRate])) {
                $recarrec = $base * $recarrecRates[$ivaRate];
            }
        }
        $totalFactura += $base + $iva + $recarrec;
    }
}

$fechaPago = mysqli_real_escape_string($bd, $_POST['fecha_pago'] ?? date('Y-m-d'));
$metodoPago = (int)($_POST['metodo_pago'] ?? 1);

if ($mode === 'full') {
    // Pagament complet: registrar pago por el total pendiente y marcar como pagada
    $totalPagado = 0;
    $rsPagos = mysqli_query($bd, "SELECT COALESCE(SUM(importe), 0) as total FROM pagos_factura WHERE idFactura = $id");
    if ($rsPagos) $totalPagado = mysqli_fetch_assoc($rsPagos)['total'];

    $importePendiente = $totalFactura - $totalPagado;
    if ($importePendiente > 0) {
        $stmt = mysqli_prepare($bd, "INSERT INTO pagos_factura (idFactura, fecha, importe, metodo_pago, comentario) VALUES (?, ?, ?, ?, 'Pagament complet')");
        mysqli_stmt_bind_param($stmt, 'isdi', $id, $fechaPago, $importePendiente, $metodoPago);
        mysqli_stmt_execute($stmt);
    }

    mysqli_query($bd, "UPDATE facturas SET fecha_pago = '$fechaPago' WHERE id = $id");

    logActivity('factura_pagar', "Factura #$id marcada com a pagada (complet: " . number_format($importePendiente, 2) . " EUR)");
    header('Location: ' . getAdminUrl('modules/facturas/?msg=paid'));

} else {
    // Pagament parcial (a compte)
    $importe = (float)str_replace(',', '.', $_POST['importe'] ?? 0);
    $comentario = mysqli_real_escape_string($bd, trim($_POST['comentario'] ?? ''));

    if ($importe <= 0) {
        header('Location: ' . getAdminUrl('modules/facturas/?msg=error'));
        exit;
    }

    $stmt = mysqli_prepare($bd, "INSERT INTO pagos_factura (idFactura, fecha, importe, metodo_pago, comentario) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isdis', $id, $fechaPago, $importe, $metodoPago, $comentario);
    mysqli_stmt_execute($stmt);

    // Comprobar si ya se ha pagado todo
    $rsPagos = mysqli_query($bd, "SELECT COALESCE(SUM(importe), 0) as total FROM pagos_factura WHERE idFactura = $id");
    $totalPagado = $rsPagos ? mysqli_fetch_assoc($rsPagos)['total'] : 0;

    if ($totalPagado >= $totalFactura) {
        // Auto-marcar como pagada
        mysqli_query($bd, "UPDATE facturas SET fecha_pago = '$fechaPago' WHERE id = $id");
        logActivity('factura_pagar', "Factura #$id completament pagada (últim pagament a compte: " . number_format($importe, 2) . " EUR)");
        header('Location: ' . getAdminUrl('modules/facturas/?msg=paid'));
    } else {
        logActivity('factura_pagar', "Factura #$id pagament a compte: " . number_format($importe, 2) . " EUR (total pagat: " . number_format($totalPagado, 2) . " / " . number_format($totalFactura, 2) . ")");
        header('Location: ' . getAdminUrl('modules/facturas/?msg=partial'));
    }
}

exit;
