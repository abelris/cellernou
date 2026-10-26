<?php
/**
 * El Celler Nou - Mòdul Vendes - Eliminar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/vendes/'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
    exit;
}

// Verificar que la venta existe y no está facturada
$result = mysqli_query($bd, "SELECT id, idEstado, idCliente FROM ventas WHERE id = $id");
$venta = mysqli_fetch_assoc($result);

if (!$venta) {
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
    exit;
}

if ($venta['idEstado'] == 5) {
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
    exit;
}

// Restaurar stock: revertir las líneas vendidas
$rsLineas = mysqli_query($bd, "SELECT idProducto, cantidad FROM linea_venta WHERE idVenta = $id");
if ($rsLineas) {
    while ($linea = mysqli_fetch_assoc($rsLineas)) {
        $cantStock = intval(ceil($linea['cantidad']));
        // Restaurar stock insertando filas nuevas
        for ($i = 0; $i < $cantStock; $i++) {
            mysqli_query($bd, "INSERT INTO stock_producto (idProducto, vendido, fecha_entrada) VALUES ({$linea['idProducto']}, 0, NOW())");
        }
    }
}

// Eliminar líneas de venta
mysqli_query($bd, "DELETE FROM linea_venta WHERE idVenta = $id");

// Eliminar venta
$result = mysqli_query($bd, "DELETE FROM ventas WHERE id = $id");

if ($result) {
    logActivity('venda_eliminar', "Venda eliminada: #$id");
    header('Location: ' . getAdminUrl('modules/vendes/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
}
exit;
