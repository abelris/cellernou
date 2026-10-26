<?php
/**
 * OnDigitalRis - Factures Venda - Marcar com a Pagada
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/odr_facturas/'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$fechaPago = trim($_POST['fecha_pago'] ?? date('Y-m-d'));

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
    exit;
}

// Verificar que la factura existe
$check = mysqli_query($bd, "SELECT id FROM odr_facturas WHERE id = $id");
if (!$check || mysqli_num_rows($check) === 0) {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
    exit;
}

$fechaPagoEsc = mysqli_real_escape_string($bd, $fechaPago);
$result = mysqli_query($bd, "UPDATE odr_facturas SET estado = 'pagada', fecha_pago = '$fechaPagoEsc', updated_at = NOW() WHERE id = $id");

if ($result) {
    logActivity('odr_factura_pagar', "Factura venda #$id marcada com a pagada");
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=paid'));
} else {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
}
exit;
