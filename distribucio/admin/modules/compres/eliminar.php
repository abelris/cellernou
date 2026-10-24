<?php
/**
 * El Celler Nou - Mòdul Compres - Eliminar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/costos.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/compres/'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/compres/?msg=error'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/compres/?msg=error'));
    exit;
}

// Productes de la compra, per recalcular-ne el cost un cop esborrada.
$productesCost = [];
$rsAnt = mysqli_query($bd, "SELECT DISTINCT idProducto FROM linea_compra WHERE idCompra = $id");
while ($rsAnt && ($rowAnt = mysqli_fetch_assoc($rsAnt))) {
    $productesCost[] = (int)$rowAnt['idProducto'];
}

// Eliminar líneas de compra
mysqli_query($bd, "DELETE FROM linea_compra WHERE idCompra = $id");

// Eliminar stock asociado
mysqli_query($bd, "DELETE FROM stock_producto WHERE idCompra = $id");

// Eliminar compra
$result = mysqli_query($bd, "DELETE FROM compras WHERE id = $id");

if ($result) {
    foreach ($productesCost as $idProd) {
        actualitzarPreuMig($bd, $idProd);
    }
    logActivity('compra_eliminar', "Compra eliminada (ID: $id)");
    header('Location: ' . getAdminUrl('modules/compres/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/compres/?msg=error'));
}
exit;
