<?php
/**
 * OnDigitalRis - Mòdul Factures de Compra - Veure Detall
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/'));
    exit;
}

// Obtenir factura
$result = mysqli_query($bd, "SELECT fc.*, p.nombre as proveedor_nombre, p.nif as proveedor_nif, p.direccion as proveedor_direccion
                             FROM odr_facturas_compra fc
                             LEFT JOIN odr_proveedores p ON fc.idProveedor = p.id
                             WHERE fc.id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=error'));
    exit;
}

$factura = mysqli_fetch_assoc($result);
$pageTitle = 'ODR - Factura ' . ($factura['numero_factura'] ?: '#' . $id);

// Obtenir línies
$lineas = [];
$result = mysqli_query($bd, "SELECT * FROM odr_linea_factura_compra WHERE idFactura = $id ORDER BY id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $lineas[] = $row;
    }
}

// Calcular totals
$totalBase = 0;
$totalIva = 0;
foreach ($lineas as $l) {
    $base = $l['cantidad'] * $l['precio_unidad'];
    $iva = $base * ($l['iva'] / 100);
    $totalBase += $base;
    $totalIva += $iva;
}
$totalGeneral = $totalBase + $totalIva;

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <a href="<?php echo getAdminUrl('modules/odr_factures_compra/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar
    </a>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?php echo getAdminUrl('modules/odr_factures_compra/crear.php?id=' . $id); ?>" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>
        <button onclick="window.print();" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir
        </button>
        <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
        </button>
    </div>
</div>

<!-- Capçalera de la factura -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">
            Factura <?php echo e($factura['numero_factura'] ?: '#' . $id); ?>
        </h3>
        <?php if ($factura['estado'] === 'pendent'): ?>
        <span class="badge badge-warning">Pendent</span>
        <?php else: ?>
        <span class="badge badge-success">Pagada</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Proveïdor</span>
                <span class="info-value"><?php echo e($factura['proveedor_nombre'] ?? '-'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">NIF / CIF</span>
                <span class="info-value"><?php echo e($factura['proveedor_nif'] ?: '-'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Núm. Factura</span>
                <span class="info-value"><?php echo e($factura['numero_factura'] ?: '-'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Data d'Emissió</span>
                <span class="info-value"><?php echo $factura['fecha'] ? date('d/m/Y', strtotime($factura['fecha'])) : '-'; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Venciment</span>
                <span class="info-value">
                    <?php
                    if ($factura['fecha_vencimiento']) {
                        $vencimiento = strtotime($factura['fecha_vencimiento']);
                        $isVencida = $vencimiento < time() && $factura['estado'] === 'pendent';
                        echo '<span' . ($isVencida ? ' style="color: var(--danger, #dc2626); font-weight: 700;"' : '') . '>';
                        echo date('d/m/Y', $vencimiento);
                        if ($isVencida) echo ' (Vençuda)';
                        echo '</span>';
                    } else {
                        echo '-';
                    }
                    ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Forma de Pagament</span>
                <span class="info-value"><?php echo e($factura['forma_pago'] ?: '-'); ?></span>
            </div>
            <?php if ($factura['estado'] === 'pagada' && $factura['fecha_pago']): ?>
            <div class="info-item">
                <span class="info-label">Data de Pagament</span>
                <span class="info-value"><?php echo date('d/m/Y', strtotime($factura['fecha_pago'])); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($factura['archivo'])): ?>
            <div class="info-item">
                <span class="info-label">Arxiu Adjunt</span>
                <span class="info-value">
                    <a href="<?php echo getBaseUrl('../uploads/ondigitalris/' . $factura['archivo']); ?>" target="_blank" class="btn btn-sm btn-secondary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        Obrir arxiu
                    </a>
                </span>
            </div>
            <?php endif; ?>
            <?php if (!empty($factura['notas'])): ?>
            <div class="info-item" style="grid-column: span 3;">
                <span class="info-label">Notes</span>
                <span class="info-value"><?php echo nl2br(e($factura['notas'])); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Línies de la factura -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detall <span class="badge badge-info"><?php echo count($lineas); ?> línies</span></h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($lineas)): ?>
        <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
            Aquesta factura no té línies de detall.
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Concepte</th>
                        <th style="text-align: center; width: 100px;">Quantitat</th>
                        <th style="text-align: right; width: 120px;">Preu Unitat</th>
                        <th style="text-align: center; width: 80px;">IVA</th>
                        <th style="text-align: right; width: 120px;">Base</th>
                        <th style="text-align: right; width: 130px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineas as $l): ?>
                    <?php
                    $base = $l['cantidad'] * $l['precio_unidad'];
                    $ivaAmt = $base * ($l['iva'] / 100);
                    $totalLinea = $base + $ivaAmt;
                    ?>
                    <tr>
                        <td><?php echo e($l['concepto']); ?></td>
                        <td style="text-align: center;"><?php echo number_format($l['cantidad'], 2, ',', '.'); ?></td>
                        <td style="text-align: right;"><?php echo number_format($l['precio_unidad'], 2, ',', '.'); ?> &euro;</td>
                        <td style="text-align: center;"><?php echo number_format($l['iva'], 0); ?>%</td>
                        <td style="text-align: right;"><?php echo number_format($base, 2, ',', '.'); ?> &euro;</td>
                        <td style="text-align: right;"><strong><?php echo number_format($totalLinea, 2, ',', '.'); ?> &euro;</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: 500;">Base imposable:</td>
                        <td style="text-align: right;"><?php echo number_format($totalBase, 2, ',', '.'); ?> &euro;</td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: 500;">Total IVA:</td>
                        <td style="text-align: right;"><?php echo number_format($totalIva, 2, ',', '.'); ?> &euro;</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right; font-weight: 600;">TOTAL:</td>
                        <td style="text-align: right; font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                            <?php echo number_format($totalGeneral, 2, ',', '.'); ?> &euro;
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.info-label { display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem; }
.info-value { font-size: 0.95rem; font-weight: 500; }
.total-row { background: var(--gray-50); }
.total-row td { padding: 1rem !important; }
.btn-danger { background: var(--danger, #dc2626); color: white; border: none; }
.btn-danger:hover { background: #b91c1c; }
.badge-warning { background: #fef3c7; color: #92400e; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.badge-success { background: #d1fae5; color: #065f46; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
@media (max-width: 768px) { .info-grid { grid-template-columns: repeat(2, 1fr); } }
@media print { .page-header, .btn { display: none !important; } .card { box-shadow: none; border: 1px solid #ddd; } }
.modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-backdrop { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); }
.modal-content { position: relative; background: white; border-radius: 8px; width: 100%; max-width: 450px; margin: 1rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-200); }
.modal-header h3 { margin: 0; font-size: 1.125rem; }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.5rem; border-top: 1px solid var(--gray-200); background: var(--gray-50); border-radius: 0 0 8px 8px; }
</style>

<!-- Modal de confirmació -->
<div id="modalEliminar" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModal()"></div>
    <div class="modal-content">
        <div class="modal-header"><h3>Eliminar Factura</h3></div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar la factura <strong><?php echo e($factura['numero_factura'] ?: '#' . $id); ?></strong>?</p>
            <p style="color: var(--danger, #dc2626); font-size: 0.875rem;">
                Aquesta acció no es pot desfer. S'eliminaran totes les línies associades.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form method="POST" action="<?php echo getAdminUrl('modules/odr_factures_compra/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmarEliminar() { document.getElementById('modalEliminar').style.display = 'flex'; }
function tancarModal() { document.getElementById('modalEliminar').style.display = 'none'; }
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') tancarModal(); });
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
