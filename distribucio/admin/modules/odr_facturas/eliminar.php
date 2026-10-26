<?php
/**
 * OnDigitalRis - Factures Venda - Eliminar
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

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
    exit;
}

// Verificar que la factura existe
$check = mysqli_query($bd, "SELECT id, numero FROM odr_facturas WHERE id = $id");
$factura = $check ? mysqli_fetch_assoc($check) : null;

if (!$factura) {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
    exit;
}

// Eliminar lineas primero
mysqli_query($bd, "DELETE FROM odr_linea_factura WHERE idFactura = $id");

// Eliminar factura
$result = mysqli_query($bd, "DELETE FROM odr_facturas WHERE id = $id");

if ($result) {
    $numRef = $factura['numero'] ?: '#' . $id;
    logActivity('odr_factura_eliminar', "Factura venda eliminada: $numRef");
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
}
exit;
