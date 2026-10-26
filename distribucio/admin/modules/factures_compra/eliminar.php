<?php
/**
 * El Celler Nou - Mòdul Factures de Compra - Eliminar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/factures_compra/'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/factures_compra/?msg=error'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/factures_compra/?msg=error'));
    exit;
}

// Obtener la factura para desenlazar la compra si existe
$result = mysqli_query($bd, "SELECT idCompra FROM facturas_compra WHERE id = $id");
if ($result && $row = mysqli_fetch_assoc($result)) {
    if ($row['idCompra'] > 0) {
        // Volver a poner la compra como pendiente
        mysqli_query($bd, "UPDATE compras SET idEstado = 0 WHERE id = " . (int)$row['idCompra']);
    }
}

// Eliminar líneas de factura
mysqli_query($bd, "DELETE FROM linea_factura_compra WHERE idFactura = $id");

// Eliminar factura
$result = mysqli_query($bd, "DELETE FROM facturas_compra WHERE id = $id");

if ($result) {
    logActivity('factura_compra_eliminar', "Factura de compra eliminada (ID: $id)");
    header('Location: ' . getAdminUrl('modules/factures_compra/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/factures_compra/?msg=error'));
}
exit;
