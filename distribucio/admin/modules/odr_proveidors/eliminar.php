<?php
/**
 * OnDigitalRis - Modul Proveidors - Eliminar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

// Nomes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/odr_proveidors/'));
    exit;
}

// Verificar CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/odr_proveidors/?msg=error'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/odr_proveidors/?msg=error'));
    exit;
}

// Obtenir nom del proveidor per al log
$result = mysqli_query($bd, "SELECT nombre FROM odr_proveedores WHERE id = $id");
$prov = mysqli_fetch_assoc($result);

if (!$prov) {
    header('Location: ' . getAdminUrl('modules/odr_proveidors/?msg=error'));
    exit;
}

// Eliminar proveidor
$result = mysqli_query($bd, "DELETE FROM odr_proveedores WHERE id = $id");

if ($result) {
    logActivity('odr_proveidor_eliminar', "Proveidor eliminat: {$prov['nombre']} (ID: $id)");
    header('Location: ' . getAdminUrl('modules/odr_proveidors/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/odr_proveidors/?msg=error'));
}
exit;
