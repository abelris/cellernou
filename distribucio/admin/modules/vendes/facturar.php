<?php
/**
 * El Celler Nou - Mòdul Vendes - Facturar (convertir venda a factura)
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/vendes/'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
    exit;
}

$idVenta = isset($_POST['idVenta']) ? (int)$_POST['idVenta'] : 0;
$fechaEmision = mysqli_real_escape_string($bd, $_POST['fecha_emision'] ?? date('Y-m-d'));
$fechaVencimiento = mysqli_real_escape_string($bd, $_POST['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+30 days')));
$idFormaPago = (int)($_POST['idFormaPago'] ?? 1);

if ($idVenta <= 0) {
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
    exit;
}

// Verificar que la venta existe y no está ya facturada
$rsVenta = mysqli_query($bd, "SELECT * FROM ventas WHERE id = $idVenta");
$venta = mysqli_fetch_assoc($rsVenta);

if (!$venta || $venta['idEstado'] == 5) {
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
    exit;
}

// Comprobar si ya existe una factura para esta venta
$rsExist = mysqli_query($bd, "SELECT id FROM facturas WHERE idVenta = $idVenta");
if ($rsExist && mysqli_fetch_assoc($rsExist)) {
    // Ya existe - redirigir a facturas
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
    exit;
}

// Crear la factura copiando datos de la venta
$sqlFactura = "INSERT INTO facturas (idCliente, descuento, precio_desc, coste, precio_coste, idEstado, idVenta, fecha, fecha_vencimiento, idFormaPago, comentarios, albaran)
               SELECT idCliente, descuento, precio_desc, coste, precio_coste, 0, id, '$fechaEmision', '$fechaVencimiento', $idFormaPago, comentarios, albaran
               FROM ventas WHERE id = $idVenta";

if (mysqli_query($bd, $sqlFactura)) {
    $idFactura = mysqli_insert_id($bd);

    // Copiar líneas de venta a líneas de factura
    $sqlLineas = "INSERT INTO linea_factura (idFactura, idProducto, idCliente, fecha, cantidad, precio_unidad, descuento, precio_unidad_sin_desc, precio_compra, iva)
                  SELECT $idFactura, idProducto, idCliente, fecha, cantidad, precio_unidad, descuento, precio_unidad_sin_desc, precio_compra, iva
                  FROM linea_venta WHERE idVenta = $idVenta";
    mysqli_query($bd, $sqlLineas);

    // Marcar la venta como facturada
    mysqli_query($bd, "UPDATE ventas SET idEstado = 5 WHERE id = $idVenta");

    logActivity('venda_facturar', "Venda #$idVenta facturada → Factura #$idFactura");
    header('Location: ' . getAdminUrl('modules/vendes/?msg=invoiced'));
} else {
    header('Location: ' . getAdminUrl('modules/vendes/?msg=error'));
}
exit;
