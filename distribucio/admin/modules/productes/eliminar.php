<?php
/**
 * El Celler Nou - Mòdul Productes - Eliminar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/productes/'));
    exit;
}

// Verificar CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/productes/?msg=error'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/productes/?msg=error'));
    exit;
}

// Obtener nombre del producto para el log
$result = mysqli_query($bd, "SELECT nombre FROM productos WHERE id = $id");
$producto = mysqli_fetch_assoc($result);

if (!$producto) {
    header('Location: ' . getAdminUrl('modules/productes/?msg=error'));
    exit;
}

// Eliminar producto (o marcar como inactivo si prefieres soft delete)
// Opción 1: Eliminación real
// $result = mysqli_query($bd, "DELETE FROM productos WHERE id = $id");

// Opción 2: Soft delete (marcar como inactivo)
$result = mysqli_query($bd, "UPDATE productos SET activo = 0 WHERE id = $id");

if ($result) {
    logActivity('producto_eliminar', "Producte eliminat/desactivat: {$producto['nombre']} (ID: $id)");
    header('Location: ' . getAdminUrl('modules/productes/?msg=deleted'));
} else {
    header('Location: ' . getAdminUrl('modules/productes/?msg=error'));
}
exit;
