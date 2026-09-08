<?php
/**
 * OnDigitalRis - Factures Venda - Duplicar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getAdminUrl('modules/odr_facturas/'));
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
    exit;
}

// Factura original
$rsF = mysqli_query($bd, "SELECT * FROM odr_facturas WHERE id = $id");
$origen = $rsF ? mysqli_fetch_assoc($rsF) : null;

if (!$origen) {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
    exit;
}

// Nou numero correlatiu de la serie INF2XXXX
$rsMax = mysqli_query($bd, "SELECT numero FROM odr_facturas WHERE numero LIKE 'INF2%' ORDER BY numero DESC LIMIT 1");
$lastNum = 20000;
if ($rsMax && $rowMax = mysqli_fetch_assoc($rsMax)) {
    $lastNum = intval(substr($rowMax['numero'], 3));
}
$nouNumero = 'INF' . ($lastNum + 1);

// La copia es una factura nova: data d'avui, pendent i sense data de pagament.
// El venciment manté els mateixos dies de marge que l'original.
$fecha = date('Y-m-d');
$fechaVenc = null;
if (!empty($origen['fecha']) && $origen['fecha'] !== '0000-00-00'
    && !empty($origen['fecha_vencimiento']) && $origen['fecha_vencimiento'] !== '0000-00-00') {
    $dies = (int)round((strtotime($origen['fecha_vencimiento']) - strtotime($origen['fecha'])) / 86400);
    if ($dies > 0) {
        $fechaVenc = date('Y-m-d', strtotime("+$dies days"));
    }
}

$stmt = mysqli_prepare($bd, "INSERT INTO odr_facturas (numero, idCliente, fecha, fecha_vencimiento, estado, forma_pago, notas, created_at, updated_at) VALUES (?, ?, ?, ?, 'pendent', ?, ?, NOW(), NOW())");
mysqli_stmt_bind_param($stmt, 'sissss',
    $nouNumero,
    $origen['idCliente'],
    $fecha,
    $fechaVenc,
    $origen['forma_pago'],
    $origen['notas']
);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ok) {
    header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
    exit;
}

$nouId = mysqli_insert_id($bd);

// Copiar les linies
$rsL = mysqli_query($bd, "SELECT concepto, cantidad, precio_unidad, iva FROM odr_linea_factura WHERE idFactura = $id ORDER BY id");
if ($rsL) {
    $stmtLine = mysqli_prepare($bd, "INSERT INTO odr_linea_factura (idFactura, concepto, cantidad, precio_unidad, iva) VALUES (?, ?, ?, ?, ?)");
    while ($linea = mysqli_fetch_assoc($rsL)) {
        mysqli_stmt_bind_param($stmtLine, 'isddd',
            $nouId,
            $linea['concepto'],
            $linea['cantidad'],
            $linea['precio_unidad'],
            $linea['iva']
        );
        mysqli_stmt_execute($stmtLine);
    }
    mysqli_stmt_close($stmtLine);
}

$numRef = $origen['numero'] ?: '#' . $id;
logActivity('odr_factura_duplicar', "Factura venda $numRef duplicada com a $nouNumero (#$nouId)");

// Obrir directament la copia per revisar-la abans d'enviar-la
header('Location: ' . getAdminUrl('modules/odr_facturas/editar.php?id=' . $nouId));
exit;
