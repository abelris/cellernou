<?php
/**
 * El Celler Nou - Eliminar Tiquet
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php?error=csrf'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php'));
    exit;
}

// Verificar que existe
$result = mysqli_query($bd, "SELECT id FROM tiquets WHERE id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php?error=notfound'));
    exit;
}

// Restaurar stock de las líneas (desmarcar como vendido)
$lineas = mysqli_query($bd, "SELECT idProducto, cantidad FROM linea_tiquet WHERE idVenta = $id");
if ($lineas) {
    while ($linea = mysqli_fetch_assoc($lineas)) {
        $cantRestore = intval(ceil($linea['cantidad']));
        $rsRestore = mysqli_query($bd, "SELECT id FROM stock_producto WHERE vendido=1 AND idProducto={$linea['idProducto']} ORDER BY fecha_vendido DESC LIMIT $cantRestore");
        if ($rsRestore) {
            while ($rowRestore = mysqli_fetch_assoc($rsRestore)) {
                mysqli_query($bd, "UPDATE stock_producto SET vendido=0, fecha_vendido='0000-00-00 00:00:00' WHERE id={$rowRestore['id']}");
            }
        }
    }
}

// Eliminar líneas
mysqli_query($bd, "DELETE FROM linea_tiquet WHERE idVenta = $id");

// Eliminar tiquet
$result = mysqli_query($bd, "DELETE FROM tiquets WHERE id = $id");

if ($result) {
    logActivity('tiquet_delete', "Tiquet eliminat: #$id");
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php?error=delete'));
}
exit;
