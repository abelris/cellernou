<?php
/**
 * OnDigitalRis - Mòdul Factures de Compra - Eliminar
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
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=error'));
    exit;
}

// Obtenir arxiu adjunt per eliminar-lo si existeix
$uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/ondigitalris/';
$result = mysqli_query($bd, "SELECT archivo FROM odr_facturas_compra WHERE id = $id");
if ($result && $row = mysqli_fetch_assoc($result)) {
    if (!empty($row['archivo'])) {
        $filePath = $uploadDir . $row['archivo'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }
}

// Eliminar línies de factura
mysqli_query($bd, "DELETE FROM odr_linea_factura_compra WHERE idFactura = $id");

// Eliminar factura
$result = mysqli_query($bd, "DELETE FROM odr_facturas_compra WHERE id = $id");

if ($result) {
    logActivity('odr_factura_compra_eliminar', "ODR Factura compra eliminada (ID: $id)");
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=error'));
}
exit;
