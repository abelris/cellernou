<?php
/**
 * OnDigitalRis - Mòdul Factures de Compra - Marcar com a pagada
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=error'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$fechaPago = trim($_POST['fecha_pago'] ?? date('Y-m-d'));

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=error'));
    exit;
}

$fechaPagoSafe = mysqli_real_escape_string($bd, $fechaPago);
$result = mysqli_query($bd, "UPDATE odr_facturas_compra SET estado = 'pagada', fecha_pago = '$fechaPagoSafe', updated_at = NOW() WHERE id = $id");

if ($result) {
    logActivity('odr_factura_compra_pagar', "ODR Factura compra marcada com a pagada (ID: $id, Data: $fechaPago)");
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=paid'));
} else {
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=error'));
}
exit;
