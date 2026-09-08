<?php
/**
 * AJAX endpoint to deactivate a product (set activo=0).
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

header('Content-Type: application/json; charset=utf-8');

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Token CSRF invàlid.']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID invàlid.']);
    exit;
}

$r = mysqli_query($bd, "SELECT nombre FROM productos WHERE id = $id");
if (!$r || mysqli_num_rows($r) === 0) {
    echo json_encode(['success' => false, 'error' => 'Producte no trobat.']);
    exit;
}
$nombre = mysqli_fetch_assoc($r)['nombre'];

if (mysqli_query($bd, "UPDATE productos SET activo = 0 WHERE id = $id")) {
    logActivity('desactivar_producte', "Producte desactivat: $nombre (ID: $id)");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($bd)]);
}
