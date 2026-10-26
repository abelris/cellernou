<?php
/**
 * OnDigitalRis - Mòdul Clients - Eliminar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';
requireAuth();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/odr_clients/'));
    exit;
}

// Verificar CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/odr_clients/?msg=error'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/odr_clients/?msg=error'));
    exit;
}

// Obtener nombre del cliente para el log
$stmt = mysqli_prepare($bd, "SELECT nombre FROM odr_clientes WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$client = mysqli_fetch_assoc($result);

if (!$client) {
    header('Location: ' . getAdminUrl('modules/odr_clients/?msg=error'));
    exit;
}

// Eliminar cliente
$stmt = mysqli_prepare($bd, "DELETE FROM odr_clientes WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    logActivity('odr_client_eliminar', "Client ODR eliminat: {$client['nombre']} (ID: $id)");
    header('Location: ' . getAdminUrl('modules/odr_clients/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/odr_clients/?msg=error'));
}
exit;
