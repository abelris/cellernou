<?php
/**
 * El Celler Nou - Mòdul Clients - Eliminar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/clients/'));
    exit;
}

// Verificar CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/clients/?msg=error'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/clients/?msg=error'));
    exit;
}

// Obtener nombre del cliente para el log
$result = mysqli_query($bd, "SELECT nombre FROM clientes WHERE id = $id");
$client = mysqli_fetch_assoc($result);

if (!$client) {
    header('Location: ' . getAdminUrl('modules/clients/?msg=error'));
    exit;
}

// Eliminar precios especiales asociados
mysqli_query($bd, "DELETE FROM precios_cliente WHERE idCliente = $id");

// Eliminar cliente
$result = mysqli_query($bd, "DELETE FROM clientes WHERE id = $id");

if ($result) {
    logActivity('client_eliminar', "Client eliminat: {$client['nombre']} (ID: $id)");
    header('Location: ' . getAdminUrl('modules/clients/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/clients/?msg=error'));
}
exit;
