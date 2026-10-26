<?php
/**
 * OnDigitalRis - Detall factura (AJAX fragment)
 * Returns HTML fragment for modal/iframe embedding - no header/footer
 * With ?print=1 returns a full HTML page for iframe printing
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';
requireAuth();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isPrint = isset($_GET['print']);

if ($id <= 0) {
    echo '<div style="padding: 2rem; text-align: center; color: #cc0000;">ID de factura no vàlid.</div>';
    exit;
}

// Load invoice + client
$stmt = $bd->prepare("
    SELECT f.*, c.nombre as cliente_nombre, c.nombre_fiscal, c.nif as cliente_nif,
           c.direccion as cliente_direccion, c.cp as cliente_cp, c.poblacion as cliente_poblacion,
           c.provincia as cliente_provincia, c.telefono as cliente_telefono, c.email as cliente_email
    FROM odr_facturas f
    LEFT JOIN odr_clientes c ON f.idCliente = c.id
    WHERE f.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$factura = $result->fetch_assoc();
$stmt->close();

if (!$factura) {
    echo '<div style="padding: 2rem; text-align: center; color: #cc0000;">Factura no trobada.</div>';
    exit;
}

// Load lines
$stmt = $bd->prepare("SELECT * FROM odr_linea_factura WHERE idFactura = ? ORDER BY id");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$lineas = [];
while ($row = $result->fetch_assoc()) {
    $lineas[] = $row;
}
$stmt->close();

// Calculate totals
$base_total = 0;
$iva_total = 0;
foreach ($lineas as &$linea) {
    $linea['subtotal'] = $linea['cantidad'] * $linea['precio_unidad'];
    $linea['iva_importe'] = $linea['subtotal'] * $linea['iva'] / 100;
    $base_total += $linea['subtotal'];
    $iva_total += $linea['iva_importe'];
}
unset($linea);
$total_final = $base_total + $iva_total;

// Format date helper
function formatDateOdr($date) {
    if (empty($date) || $date === '0000-00-00') return '-';
    return date('d/m/Y', strtotime($date));
}

// Estado badge
function estadoBadgeOdr($estado) {
    switch ($estado) {
        case 'pagada':
            return '<span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; background: #d4edda; color: #155724;">Pagada</span>';
        case 'vencida':
            return '<span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; background: #f8d7da; color: #721c24;">Vençuda</span>';
        case 'anulada':
            return '<span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; background: #e2e3e5; color: #383d41;">Anul·lada</span>';
        default:
            return '<span style="display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; background: #fff3cd; color: #856404;">Pendent</span>';
    }
}
?>
<?php if ($isPrint): ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Factura <?= e($factura['numero']) ?></title>
<style>
    @media print { body { margin: 0; } @page { margin: 1.5cm; } }
</style>
</head>
<body>
<?php endif; ?>
<div class="factura-preview" id="facturaPrintArea" style="font-family: 'Inter', 'Segoe UI', Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 1.5rem; color: #333; font-size: 0.9rem; line-height: 1.5;">

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #0066FF;">
        <div>
            <div style="font-size: 1.4rem; font-weight: 700; color: #0066FF; letter-spacing: -0.5px;">ondigitalris</div>
            <small style="color: #666; line-height: 1.6;">
                ALMABRIS GROUP 2024 S.L.<br>
                CIF: B66318684<br>
                C/ Ramón Llull, 15<br>
                08330 Premià de Mar (Barcelona)<br>
                Tel: 93 752 02 71<br>
                info@ondigitalris.com
            </small>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 1.3rem; font-weight: 700; color: #0066FF; text-transform: uppercase; letter-spacing: 1px;">Factura</div>
            <div style="font-size: 1.1rem; font-weight: 600; margin: 0.25rem 0;"><?= e($factura['numero']) ?></div>
            <div style="color: #666;">
                Data: <?= formatDateOdr($factura['fecha']) ?><br>
                Venciment: <?= formatDateOdr($factura['fecha_vencimiento']) ?>
            </div>
        </div>
    </div>

    <!-- Client info -->
    <div style="background: #f0f6ff; border-left: 4px solid #0066FF; padding: 1rem 1.25rem; margin: 1rem 0 1.5rem 0; border-radius: 0 4px 4px 0;">
        <strong style="font-size: 0.95rem;">Client:</strong><br>
        <span style="font-size: 1.05rem; font-weight: 600;"><?= e($factura['nombre_fiscal'] ?: $factura['cliente_nombre']) ?></span><br>
        <?php if (!empty($factura['cliente_nif'])): ?>
            NIF: <?= e($factura['cliente_nif']) ?><br>
        <?php endif; ?>
        <?php if (!empty($factura['cliente_direccion'])): ?>
            <?= e($factura['cliente_direccion']) ?>
            <?php if (!empty($factura['cliente_cp']) || !empty($factura['cliente_poblacion'])): ?>
                <br><?= e($factura['cliente_cp']) ?> <?= e($factura['cliente_poblacion']) ?>
                <?php if (!empty($factura['cliente_provincia'])): ?>
                    (<?= e($factura['cliente_provincia']) ?>)
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($factura['cliente_telefono'])): ?>
            <br>Tel: <?= e($factura['cliente_telefono']) ?>
        <?php endif; ?>
        <?php if (!empty($factura['cliente_email'])): ?>
            <br>Email: <?= e($factura['cliente_email']) ?>
        <?php endif; ?>
    </div>

    <!-- Lines table -->
    <table style="width: 100%; border-collapse: collapse; margin: 1rem 0 1.5rem 0;">
        <thead>
            <tr style="background: #0066FF; color: #ffffff;">
                <th style="padding: 10px 12px; text-align: left; font-weight: 600; font-size: 0.85rem;">Concepte</th>
                <th style="padding: 10px 12px; text-align: center; font-weight: 600; font-size: 0.85rem; width: 80px;">Quantitat</th>
                <th style="padding: 10px 12px; text-align: right; font-weight: 600; font-size: 0.85rem; width: 100px;">Preu/Ut.</th>
                <th style="padding: 10px 12px; text-align: center; font-weight: 600; font-size: 0.85rem; width: 70px;">IVA</th>
                <th style="padding: 10px 12px; text-align: right; font-weight: 600; font-size: 0.85rem; width: 110px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($lineas)): ?>
                <tr>
                    <td colspan="5" style="padding: 1.5rem; text-align: center; color: #999; font-style: italic;">No hi ha línies en aquesta factura.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($lineas as $i => $linea): ?>
                    <tr style="background: <?= $i % 2 === 0 ? '#ffffff' : '#f8fafc' ?>; border-bottom: 1px solid #e9ecef;">
                        <td style="padding: 9px 12px;"><?= e($linea['concepto']) ?></td>
                        <td style="padding: 9px 12px; text-align: center;"><?= number_format($linea['cantidad'], 2, ',', '.') ?></td>
                        <td style="padding: 9px 12px; text-align: right;"><?= number_format($linea['precio_unidad'], 2, ',', '.') ?> &euro;</td>
                        <td style="padding: 9px 12px; text-align: center;"><?= number_format($linea['iva'], 0) ?>%</td>
                        <td style="padding: 9px 12px; text-align: right; font-weight: 500;"><?= number_format($linea['subtotal'], 2, ',', '.') ?> &euro;</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem;">
        <table style="min-width: 280px; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 12px; color: #555;">Base imponible:</td>
                <td style="padding: 6px 12px; text-align: right; font-weight: 600;"><?= number_format($base_total, 2, ',', '.') ?> &euro;</td>
            </tr>
            <tr>
                <td style="padding: 6px 12px; color: #555;">IVA:</td>
                <td style="padding: 6px 12px; text-align: right; font-weight: 600;"><?= number_format($iva_total, 2, ',', '.') ?> &euro;</td>
            </tr>
            <tr style="border-top: 2px solid #0066FF;">
                <td style="padding: 10px 12px; font-weight: 700; font-size: 1.05rem;">TOTAL:</td>
                <td style="padding: 10px 12px; text-align: right; font-weight: 700; font-size: 1.15rem; color: #0066FF;"><?= number_format($total_final, 2, ',', '.') ?> &euro;</td>
            </tr>
        </table>
    </div>

    <!-- Payment info & status -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: #f8f9fa; border-radius: 4px; margin-bottom: 1.5rem;">
        <div>
            <span style="color: #666;">Forma de pagament:</span>
            <strong><?= e($factura['forma_pago'] ?: '-') ?></strong>
        </div>
        <div>
            <span style="color: #666; margin-right: 0.5rem;">Estat:</span>
            <?= estadoBadgeOdr($factura['estado']) ?>
        </div>
    </div>

    <?php if (!empty($factura['notas'])): ?>
        <div style="padding: 0.75rem 1rem; background: #fffdf0; border-left: 3px solid #f0c040; border-radius: 0 4px 4px 0; margin-bottom: 1.5rem; font-size: 0.85rem; color: #665500;">
            <strong>Notes:</strong> <?= nl2br(e($factura['notas'])) ?>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div style="text-align: center; color: #999; font-size: 0.8rem; margin-top: 1.5rem; border-top: 1px solid #eee; padding-top: 0.75rem;">
        ondigitalris &middot; ALMABRIS GROUP 2024 S.L. &middot; CIF: B66318684
    </div>

</div>
<?php if ($isPrint): ?>
</body>
</html>
<?php endif; ?>
