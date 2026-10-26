<?php
/**
 * El Celler Nou - Mòdul Factures - Detall AJAX
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo '<p style="color: var(--gray-400);">Factura no trobada.</p>';
    exit;
}

// Obtener factura
$sql = "SELECT f.*, c.nombre as cliente_nombre, c.nombre_fiscal, c.nif, c.direccion as cliente_direccion,
        c.cp, c.poblacion, c.provincia, c.profesional, c.recarrec_equiv
        FROM facturas f
        LEFT JOIN clientes c ON f.idCliente = c.id
        WHERE f.id = $id";
$result = mysqli_query($bd, $sql);
$factura = $result ? mysqli_fetch_assoc($result) : null;

if (!$factura) {
    echo '<p style="color: var(--gray-400);">Factura no trobada.</p>';
    exit;
}

$numFactura = 'FA' . str_pad((date('Y', strtotime($factura['fecha'])) * 100) . $id, 10, '0', STR_PAD_LEFT);
$isPagada = !empty($factura['fecha_pago']) && $factura['fecha_pago'] !== '0000-00-00';

// Obtener pagos registrados
$pagos = [];
$totalPagado = 0;
$rsPagos = mysqli_query($bd, "SELECT * FROM pagos_factura WHERE idFactura = $id ORDER BY fecha ASC, id ASC");
if ($rsPagos) {
    while ($pago = mysqli_fetch_assoc($rsPagos)) {
        $pagos[] = $pago;
        $totalPagado += $pago['importe'];
    }
}
$formaPagoLabelsDetail = [1 => 'Efectiu', 2 => 'Transferència', 3 => 'Xec', 4 => 'Domiciliació'];

// Obtener líneas y calcular IVA
$lineas = [];
$totalBase = 0;
$ivaBreakdown = [];

$sql = "SELECT lf.*, p.nombre as producto_nombre, p.codigo
        FROM linea_factura lf
        LEFT JOIN productos p ON lf.idProducto = p.id
        WHERE lf.idFactura = $id
        ORDER BY lf.id";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $subtotal = $row['cantidad'] * $row['precio_unidad'];
        $row['subtotal'] = $subtotal;
        $totalBase += $subtotal;

        $ivaRate = (int)$row['iva'];
        if (!isset($ivaBreakdown[$ivaRate])) {
            $ivaBreakdown[$ivaRate] = ['base' => 0, 'iva' => 0, 'recarrec' => 0];
        }
        $ivaBreakdown[$ivaRate]['base'] += $subtotal;
        $ivaBreakdown[$ivaRate]['iva'] += $subtotal * ($ivaRate / 100);

        // Recàrrec d'equivalència
        if ($factura['recarrec_equiv']) {
            $recarrecRates = [4 => 0.005, 10 => 0.014, 21 => 0.052];
            if (isset($recarrecRates[$ivaRate])) {
                $ivaBreakdown[$ivaRate]['recarrec'] += $subtotal * $recarrecRates[$ivaRate];
            }
        }

        $lineas[] = $row;
    }
}

$totalIva = array_sum(array_column($ivaBreakdown, 'iva'));
$totalRecarrec = array_sum(array_column($ivaBreakdown, 'recarrec'));
$totalFactura = $totalBase + $totalIva + $totalRecarrec;

$formaPagoLabels = [1 => 'Efectiu', 2 => 'Transferència', 3 => 'Xec', 4 => 'Domiciliació'];
?>

<div style="margin-bottom: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <div>
            <strong style="font-family: monospace;"><?php echo $numFactura; ?></strong>
            <?php if ($isPagada): ?>
                <span class="badge badge-success" style="margin-left: 0.5rem;">Pagada</span>
            <?php elseif ($totalPagado > 0): ?>
                <span class="badge badge-partial" style="margin-left: 0.5rem;">Parcial (<?php echo number_format($totalPagado, 2, ',', '.'); ?> &euro;)</span>
            <?php else: ?>
                <span class="badge badge-warning" style="margin-left: 0.5rem;">Pendent</span>
            <?php endif; ?>
        </div>
        <a href="<?php echo getAdminUrl('modules/facturas/pdf.php?id=' . $id); ?>" class="btn btn-sm btn-primary" target="_blank">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF
        </a>
    </div>

    <!-- Client info -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div style="background: var(--gray-50); padding: 0.75rem; border-radius: 6px;">
            <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.25rem;">Client</div>
            <div style="font-weight: 600;"><?php echo e($factura['cliente_nombre']); ?></div>
            <?php if ($factura['nombre_fiscal']): ?>
            <div style="font-size: 0.85rem;"><?php echo e($factura['nombre_fiscal']); ?></div>
            <?php endif; ?>
            <?php if ($factura['nif']): ?>
            <div style="font-size: 0.8rem; color: var(--gray-500);">NIF: <?php echo e($factura['nif']); ?></div>
            <?php endif; ?>
        </div>
        <div style="background: var(--gray-50); padding: 0.75rem; border-radius: 6px;">
            <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.25rem;">Dates</div>
            <div style="font-size: 0.85rem;">Factura: <?php echo date('d/m/Y', strtotime($factura['fecha'])); ?></div>
            <?php if ($factura['fecha_vencimiento'] && $factura['fecha_vencimiento'] !== '0000-00-00'): ?>
            <div style="font-size: 0.85rem;">Venciment: <?php echo date('d/m/Y', strtotime($factura['fecha_vencimiento'])); ?></div>
            <?php endif; ?>
            <div style="font-size: 0.85rem;">Pagament: <?php echo $formaPagoLabels[$factura['idFormaPago']] ?? '-'; ?></div>
        </div>
    </div>
</div>

<!-- Líneas -->
<table class="table" style="margin-bottom: 1rem;">
    <thead>
        <tr>
            <th>Producte</th>
            <th style="width: 60px; text-align: right;">Qty</th>
            <th style="width: 90px; text-align: right;">Preu</th>
            <th style="width: 60px; text-align: right;">Dto%</th>
            <th style="width: 60px; text-align: right;">IVA</th>
            <th style="width: 100px; text-align: right;">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lineas as $l): ?>
        <tr>
            <td><?php echo e($l['producto_nombre'] ?? 'Producte #' . $l['idProducto']); ?></td>
            <td style="text-align: right;"><?php echo number_format($l['cantidad'], 2, ',', ''); ?></td>
            <td style="text-align: right;"><?php echo number_format($l['precio_unidad'], 2, ',', '.'); ?></td>
            <td style="text-align: right;"><?php echo $l['descuento'] > 0 ? number_format($l['descuento'], 0) . '%' : '-'; ?></td>
            <td style="text-align: right;"><?php echo number_format($l['iva'], 0); ?>%</td>
            <td style="text-align: right; font-weight: 600;"><?php echo number_format($l['subtotal'], 2, ',', '.'); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Totals amb desglossament IVA -->
<div style="background: var(--gray-50); padding: 1rem; border-radius: 6px;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
        <span>Base Imposable:</span>
        <strong><?php echo number_format($totalBase, 2, ',', '.'); ?> &euro;</strong>
    </div>

    <?php foreach ($ivaBreakdown as $rate => $data): ?>
    <?php if ($data['base'] > 0): ?>
    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--gray-600);">
        <span>IVA <?php echo $rate; ?>% (Base: <?php echo number_format($data['base'], 2, ',', '.'); ?>)</span>
        <span><?php echo number_format($data['iva'], 2, ',', '.'); ?> &euro;</span>
    </div>
    <?php if ($data['recarrec'] > 0): ?>
    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--gray-600);">
        <?php
        $recarrecPct = [4 => '0,5', 10 => '1,4', 21 => '5,2'];
        ?>
        <span>Recàrrec <?php echo $recarrecPct[$rate] ?? ''; ?>%</span>
        <span><?php echo number_format($data['recarrec'], 2, ',', '.'); ?> &euro;</span>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <?php endforeach; ?>

    <div style="display: flex; justify-content: space-between; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 2px solid var(--gray-300);">
        <strong style="font-size: 1.1rem;">TOTAL:</strong>
        <strong style="font-size: 1.1rem; color: var(--primary);"><?php echo number_format($totalFactura, 2, ',', '.'); ?> &euro;</strong>
    </div>
</div>

<?php if (!empty($pagos)): ?>
<!-- Historial de pagaments -->
<div style="margin-top: 1rem;">
    <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.5rem;">Historial de Pagaments</div>
    <table class="table" style="font-size: 0.85rem;">
        <thead>
            <tr>
                <th>Data</th>
                <th>Mètode</th>
                <th style="text-align: right;">Import</th>
                <th>Comentari</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagos as $p): ?>
            <tr>
                <td><?php echo date('d/m/Y', strtotime($p['fecha'])); ?></td>
                <td><?php echo $formaPagoLabelsDetail[$p['metodo_pago']] ?? '-'; ?></td>
                <td style="text-align: right; font-weight: 600;"><?php echo number_format($p['importe'], 2, ',', '.'); ?> &euro;</td>
                <td style="color: var(--gray-500);"><?php echo e($p['comentario']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: 600;">Total pagat:</td>
                <td style="text-align: right; font-weight: 700; color: var(--primary);"><?php echo number_format($totalPagado, 2, ',', '.'); ?> &euro;</td>
                <td>
                    <?php
                    $pendent = $totalFactura - $totalPagado;
                    if ($pendent > 0.01):
                    ?>
                    <span style="color: #dc2626; font-weight: 600;">Pendent: <?php echo number_format($pendent, 2, ',', '.'); ?> &euro;</span>
                    <?php endif; ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<?php endif; ?>

<style>
.badge { display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.7rem; font-weight: 600; border-radius: 9999px; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-partial { background: #dbeafe; color: #1e40af; }
.badge-success { background: #d1fae5; color: #065f46; }
</style>
