<?php
/**
 * El Celler Nou - Gestoria - Detall Factura AJAX
 * Retorna HTML per al popup de detall d'una factura.
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo '<p style="color: var(--gray-400); text-align: center; padding: 1rem;">Factura no trobada.</p>';
    exit;
}

// Obtenir factura amb dades del client
$sql = "SELECT f.*, c.nombre AS cliente_nombre, c.nombre_fiscal, c.nif,
        c.direccion AS cliente_direccion, c.cp, c.poblacion, c.provincia, c.recarrec_equiv
        FROM facturas f
        LEFT JOIN clientes c ON f.idCliente = c.id
        WHERE f.id = $id";
$result = mysqli_query($bd, $sql);
$factura = $result ? mysqli_fetch_assoc($result) : null;

if (!$factura) {
    echo '<p style="color: var(--gray-400); text-align: center; padding: 1rem;">Factura no trobada.</p>';
    exit;
}

// Número de factura
$numFactura = 'FA' . str_pad((date('Y', strtotime($factura['fecha'])) * 100) . $id, 10, '0', STR_PAD_LEFT);
$isPagada = !empty($factura['fecha_pago']) && $factura['fecha_pago'] !== '0000-00-00';

$formaPagoLabels = [1 => 'Efectiu', 2 => 'Transferència', 3 => 'Xec', 4 => 'Domiciliació'];
$recarrecRates   = [4 => 0.005, 10 => 0.014, 21 => 0.052];
$recarrecPct     = [4 => '0,5', 10 => '1,4', 21 => '5,2'];

// Comprovar si existeix la taula pagos_factura
$hasPagosFactura = false;
$chkTable = mysqli_query($bd, "SHOW TABLES LIKE 'pagos_factura'");
if ($chkTable && mysqli_num_rows($chkTable) > 0) {
    $hasPagosFactura = true;
}

// Obtenir pagaments si la taula existeix
$pagos = [];
$totalPagado = 0;
if ($hasPagosFactura) {
    $rsPagos = mysqli_query($bd, "SELECT * FROM pagos_factura WHERE idFactura = $id ORDER BY fecha ASC, id ASC");
    if ($rsPagos) {
        while ($pago = mysqli_fetch_assoc($rsPagos)) {
            $pagos[] = $pago;
            $totalPagado += $pago['importe'];
        }
    }
}

// Obtenir línies i calcular desglossament IVA
$lineas = [];
$totalBase = 0;
$ivaBreakdown = [];

$sql = "SELECT lf.*, p.nombre AS producto_nombre, p.codigo
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
        $ivaBreakdown[$ivaRate]['iva']  += $subtotal * ($ivaRate / 100);

        // Recàrrec d'equivalència
        if ($factura['recarrec_equiv']) {
            if (isset($recarrecRates[$ivaRate])) {
                $ivaBreakdown[$ivaRate]['recarrec'] += $subtotal * $recarrecRates[$ivaRate];
            }
        }

        $lineas[] = $row;
    }
}

ksort($ivaBreakdown);
$totalIva      = array_sum(array_column($ivaBreakdown, 'iva'));
$totalRecarrec = array_sum(array_column($ivaBreakdown, 'recarrec'));
$totalFactura  = $totalBase + $totalIva + $totalRecarrec;

if (!function_exists('fmtEur')) {
    function fmtEur($v) { return number_format($v, 2, ',', '.') . ' €'; }
}
?>

<!-- Capçalera: número de factura + estat -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div>
        <strong style="font-family: monospace; font-size: 1rem;"><?php echo $numFactura; ?></strong>
        <?php if ($isPagada): ?>
            <span style="display: inline-block; margin-left: 0.5rem; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600; border-radius: 9999px; background: #d1fae5; color: #065f46;">Pagada</span>
        <?php elseif ($totalPagado > 0): ?>
            <span style="display: inline-block; margin-left: 0.5rem; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600; border-radius: 9999px; background: #dbeafe; color: #1e40af;">Parcial (<?php echo fmtEur($totalPagado); ?>)</span>
        <?php else: ?>
            <span style="display: inline-block; margin-left: 0.5rem; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600; border-radius: 9999px; background: #fef3c7; color: #92400e;">Pendent</span>
        <?php endif; ?>
    </div>
</div>

<!-- Info client + dates -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
    <div style="background: var(--gray-50); padding: 0.75rem; border-radius: 6px;">
        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.25rem;">Client</div>
        <div style="font-weight: 600;"><?php echo e($factura['cliente_nombre'] ?? '-'); ?></div>
        <?php if (!empty($factura['nombre_fiscal'])): ?>
        <div style="font-size: 0.85rem; color: var(--gray-600);"><?php echo e($factura['nombre_fiscal']); ?></div>
        <?php endif; ?>
        <?php if (!empty($factura['nif'])): ?>
        <div style="font-size: 0.8rem; color: var(--gray-500);">NIF: <?php echo e($factura['nif']); ?></div>
        <?php endif; ?>
    </div>
    <div style="background: var(--gray-50); padding: 0.75rem; border-radius: 6px;">
        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.25rem;">Dates</div>
        <div style="font-size: 0.85rem;">Factura: <?php echo date('d/m/Y', strtotime($factura['fecha'])); ?></div>
        <?php if (!empty($factura['fecha_vencimiento']) && $factura['fecha_vencimiento'] !== '0000-00-00'): ?>
        <div style="font-size: 0.85rem;">Venciment: <?php echo date('d/m/Y', strtotime($factura['fecha_vencimiento'])); ?></div>
        <?php endif; ?>
        <div style="font-size: 0.85rem;">Pagament: <?php echo $formaPagoLabels[$factura['idFormaPago']] ?? '-'; ?></div>
    </div>
</div>

<!-- Línies de factura -->
<table class="table" style="margin-bottom: 1rem; font-size: 0.875rem;">
    <thead>
        <tr>
            <th>Producte</th>
            <th style="width: 60px; text-align: right;">Qty</th>
            <th style="width: 90px; text-align: right;">Preu</th>
            <th style="width: 60px; text-align: right;">Dto%</th>
            <th style="width: 60px; text-align: right;">IVA%</th>
            <th style="width: 100px; text-align: right;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($lineas)): ?>
        <tr><td colspan="6" style="text-align: center; padding: 1rem; color: var(--gray-400);">Sense línies</td></tr>
        <?php else: ?>
        <?php foreach ($lineas as $l): ?>
        <tr>
            <td>
                <?php echo e($l['producto_nombre'] ?? 'Producte #' . $l['idProducto']); ?>
                <?php if (!empty($l['codigo'])): ?>
                <span style="font-size: 0.75rem; color: var(--gray-400); margin-left: 0.25rem;">(<?php echo e($l['codigo']); ?>)</span>
                <?php endif; ?>
            </td>
            <td style="text-align: right;"><?php echo number_format($l['cantidad'], 2, ',', ''); ?></td>
            <td style="text-align: right;"><?php echo number_format($l['precio_unidad'], 2, ',', '.'); ?> €</td>
            <td style="text-align: right;"><?php echo $l['descuento'] > 0 ? number_format($l['descuento'], 0) . '%' : '-'; ?></td>
            <td style="text-align: right;"><?php echo number_format($l['iva'], 0); ?>%</td>
            <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($l['subtotal']); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Desglossament IVA i totals -->
<div style="background: var(--gray-50); padding: 1rem; border-radius: 6px;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
        <span>Base Imposable:</span>
        <strong><?php echo fmtEur($totalBase); ?></strong>
    </div>

    <?php foreach ($ivaBreakdown as $rate => $data): ?>
    <?php if ($data['base'] > 0): ?>
    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--gray-600);">
        <span>IVA <?php echo $rate; ?>% (Base: <?php echo number_format($data['base'], 2, ',', '.'); ?> €)</span>
        <span><?php echo fmtEur($data['iva']); ?></span>
    </div>
    <?php if ($data['recarrec'] > 0): ?>
    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--gray-600);">
        <span>Recàrrec <?php echo $recarrecPct[$rate] ?? ''; ?>%</span>
        <span><?php echo fmtEur($data['recarrec']); ?></span>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <?php endforeach; ?>

    <div style="display: flex; justify-content: space-between; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 2px solid var(--gray-300);">
        <strong style="font-size: 1.1rem;">TOTAL:</strong>
        <strong style="font-size: 1.1rem; color: var(--primary);"><?php echo fmtEur($totalFactura); ?></strong>
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
                <td><?php echo $formaPagoLabels[$p['metodo_pago']] ?? '-'; ?></td>
                <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($p['importe']); ?></td>
                <td style="color: var(--gray-500);"><?php echo e($p['comentario'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: 600;">Total pagat:</td>
                <td style="text-align: right; font-weight: 700; color: var(--primary);"><?php echo fmtEur($totalPagado); ?></td>
                <td>
                    <?php
                    $pendent = $totalFactura - $totalPagado;
                    if ($pendent > 0.01):
                    ?>
                    <span style="color: #dc2626; font-weight: 600;">Pendent: <?php echo fmtEur($pendent); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<?php endif; ?>
