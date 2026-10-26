<?php
/**
 * El Celler Nou - Mòdul Factures - Eliminar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/facturas/'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/facturas/?msg=error'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/facturas/?msg=error'));
    exit;
}

// Obtener factura
$result = mysqli_query($bd, "SELECT id, idVenta, idCliente FROM facturas WHERE id = $id");
$factura = mysqli_fetch_assoc($result);

if (!$factura) {
    header('Location: ' . getAdminUrl('modules/facturas/?msg=error'));
    exit;
}

// Si la factura tiene venta asociada, revertir estado de la venta
if ($factura['idVenta'] > 0) {
    mysqli_query($bd, "UPDATE ventas SET idEstado = 0 WHERE id = {$factura['idVenta']}");
}

// Eliminar líneas de factura
mysqli_query($bd, "DELETE FROM linea_factura WHERE idFactura = $id");

// Eliminar factura
$result = mysqli_query($bd, "DELETE FROM facturas WHERE id = $id");

if ($result) {
    logActivity('factura_eliminar', "Factura eliminada: #$id");
    header('Location: ' . getAdminUrl('modules/facturas/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/facturas/?msg=error'));
}
exit;
