<?php
/**
 * El Celler Nou - Mòdul Vendes - Detall AJAX
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo '<p style="color: var(--gray-400);">Venda no trobada.</p>';
    exit;
}

// Obtener venta
$sql = "SELECT v.*, c.nombre as cliente_nombre, c.profesional, c.nif
        FROM ventas v
        LEFT JOIN clientes c ON v.idCliente = c.id
        WHERE v.id = $id";
$result = mysqli_query($bd, $sql);
$venta = $result ? mysqli_fetch_assoc($result) : null;

if (!$venta) {
    echo '<p style="color: var(--gray-400);">Venda no trobada.</p>';
    exit;
}

// Obtener líneas
$lineas = [];
$totalVenta = 0;
$sql = "SELECT lv.*, p.nombre as producto_nombre, p.codigo
        FROM linea_venta lv
        LEFT JOIN productos p ON lv.idProducto = p.id
        WHERE lv.idVenta = $id
        ORDER BY lv.id";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['subtotal'] = $row['cantidad'] * $row['precio_unidad'];
        $totalVenta += $row['subtotal'];
        $lineas[] = $row;
    }
}

$estadoLabels = [0 => 'Pendent', 5 => 'Facturada'];
?>

<div style="margin-bottom: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
        <div>
            <strong>Venda #<?php echo $id; ?></strong>
            <span class="badge <?php echo $venta['idEstado'] == 5 ? 'badge-success' : 'badge-warning'; ?>" style="margin-left: 0.5rem;">
                <?php echo $estadoLabels[$venta['idEstado']] ?? 'Desconegut'; ?>
            </span>
        </div>
        <div style="color: var(--gray-500); font-size: 0.875rem;">
            <?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?>
        </div>
    </div>

    <?php if ($venta['cliente_nombre']): ?>
    <div style="background: var(--gray-50); padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem;">
        <div style="font-weight: 600;"><?php echo e($venta['cliente_nombre']); ?></div>
        <?php if ($venta['nif']): ?>
        <div style="font-size: 0.8rem; color: var(--gray-500);">NIF: <?php echo e($venta['nif']); ?></div>
        <?php endif; ?>
        <?php if ($venta['profesional']): ?>
        <span class="badge badge-info" style="margin-top: 0.25rem;">Professional</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<table class="table" style="margin-bottom: 1rem;">
    <thead>
        <tr>
            <th>Producte</th>
            <th style="width: 60px; text-align: right;">Qty</th>
            <th style="width: 90px; text-align: right;">Preu</th>
            <th style="width: 60px; text-align: right;">Dto%</th>
            <th style="width: 90px; text-align: right;">IVA%</th>
            <th style="width: 100px; text-align: right;">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lineas as $l): ?>
        <tr>
            <td>
                <div style="font-weight: 500;"><?php echo e($l['producto_nombre'] ?? 'Producte #' . $l['idProducto']); ?></div>
                <?php if ($l['codigo']): ?>
                <div style="font-size: 0.75rem; color: var(--gray-400);"><?php echo e($l['codigo']); ?></div>
                <?php endif; ?>
            </td>
            <td style="text-align: right;"><?php echo number_format($l['cantidad'], 2, ',', ''); ?></td>
            <td style="text-align: right;"><?php echo number_format($l['precio_unidad'], 2, ',', '.'); ?> &euro;</td>
            <td style="text-align: right;"><?php echo $l['descuento'] > 0 ? number_format($l['descuento'], 0) . '%' : '-'; ?></td>
            <td style="text-align: right;"><?php echo number_format($l['iva'], 0); ?>%</td>
            <td style="text-align: right; font-weight: 600;"><?php echo number_format($l['subtotal'], 2, ',', '.'); ?> &euro;</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align: right; font-weight: 700;">TOTAL:</td>
            <td style="text-align: right; font-weight: 700; font-size: 1.1rem; color: var(--primary);">
                <?php echo number_format($totalVenta, 2, ',', '.'); ?> &euro;
            </td>
        </tr>
    </tfoot>
</table>

<?php if (!empty($venta['comentarios'])): ?>
<div style="background: var(--gray-50); padding: 0.75rem; border-radius: 6px; font-size: 0.875rem; margin-bottom: 1rem;">
    <strong>Comentaris:</strong> <?php echo e($venta['comentarios']); ?>
</div>
<?php endif; ?>

<div style="text-align: right;">
    <a href="<?php echo getAdminUrl('modules/vendes/pdf.php?id=' . $id); ?>" target="_blank" class="btn btn-primary btn-sm">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; vertical-align: middle;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Imprimir PDF
    </a>
</div>

<style>
.badge-info { background: #dbeafe; color: #1e40af; display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.7rem; font-weight: 600; border-radius: 9999px; }
.badge-warning { background: #fef3c7; color: #92400e; display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.7rem; font-weight: 600; border-radius: 9999px; }
.badge-success { background: #d1fae5; color: #065f46; display: inline-block; padding: 0.2rem 0.5rem; font-size: 0.7rem; font-weight: 600; border-radius: 9999px; }
</style>
