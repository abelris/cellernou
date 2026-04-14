<?php
/**
 * El Celler Nou - Mòdul Compres - Veure Detall
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/compres/'));
    exit;
}

// Obtener compra
$result = mysqli_query($bd, "SELECT c.*, p.nombre as proveedor_nombre
                             FROM compras c
                             LEFT JOIN proveedores p ON c.idProveedor = p.id
                             WHERE c.id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/compres/?msg=error'));
    exit;
}

$compra = mysqli_fetch_assoc($result);
$pageTitle = 'Compra #' . $id;

// Obtener líneas de la compra
$lineas = [];
$sql = "SELECT lc.*, pr.nombre as producto_nombre, pr.codigo as producto_codigo
        FROM linea_compra lc
        LEFT JOIN productos pr ON lc.idProducto = pr.id
        WHERE lc.idCompra = $id
        ORDER BY lc.id";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $lineas[] = $row;
    }
}

// Calcular totales
$totalBase = 0;
foreach ($lineas as $l) {
    $totalBase += $l['cantidad'] * $l['precio_unidad'];
}

// Verificar si tiene factura asociada
$facturaAsociada = null;
$resultFact = mysqli_query($bd, "SELECT id, numFactura FROM facturas_compra WHERE idCompra = $id LIMIT 1");
if ($resultFact && mysqli_num_rows($resultFact) > 0) {
    $facturaAsociada = mysqli_fetch_assoc($resultFact);
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <a href="<?php echo getAdminUrl('modules/compres/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar
    </a>
    <div style="display: flex; gap: 0.5rem;">
        <?php if ($compra['idEstado'] != 3): ?>
        <a href="<?php echo getAdminUrl('modules/compres/modificar.php?id=' . $id); ?>" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>
        <a href="<?php echo getAdminUrl('modules/compres/facturar.php?id=' . $id); ?>" class="btn btn-success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Convertir a Factura
        </a>
        <?php endif; ?>
        <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
        </button>
    </div>
</div>

<!-- Cabecera de la compra -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Compra #<?php echo $id; ?></h3>
        <?php if ($compra['idEstado'] == 0): ?>
        <span class="badge badge-warning">Pendent</span>
        <?php elseif ($compra['idEstado'] == 3): ?>
        <span class="badge badge-success">Facturada</span>
        <?php else: ?>
        <span class="badge badge-info">En curs</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Proveïdor</span>
                <span class="info-value"><?php echo e($compra['proveedor_nombre'] ?? '-'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Referència</span>
                <span class="info-value"><?php echo e($compra['referencia'] ?: '-'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Data</span>
                <span class="info-value"><?php echo $compra['fecha'] ? date('d/m/Y', strtotime($compra['fecha'])) : '-'; ?></span>
            </div>
            <?php if ($compra['fecha_vencimiento']): ?>
            <div class="info-item">
                <span class="info-label">Venciment</span>
                <span class="info-value"><?php echo date('d/m/Y', strtotime($compra['fecha_vencimiento'])); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($compra['oferta'])): ?>
            <div class="info-item" style="grid-column: span 2;">
                <span class="info-label">Oferta / Notes</span>
                <span class="info-value"><?php echo e($compra['oferta']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($facturaAsociada): ?>
            <div class="info-item">
                <span class="info-label">Factura Associada</span>
                <span class="info-value">
                    <a href="<?php echo getAdminUrl('modules/factures_compra/veure.php?id=' . $facturaAsociada['id']); ?>">
                        <?php echo e($facturaAsociada['numFactura'] ?: '#' . $facturaAsociada['id']); ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Líneas de la compra -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Línies de Compra <span class="badge badge-info"><?php echo count($lineas); ?> línies</span></h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($lineas)): ?>
        <div style="padding: 2rem; text-align: center; color: var(--gray-500);">
            Aquesta compra no té línies.
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Codi</th>
                        <th>Producte</th>
                        <th style="text-align: center;">Quantitat</th>
                        <th style="text-align: right;">Preu s/desc</th>
                        <th style="text-align: center;">Descompte</th>
                        <th style="text-align: right;">Preu final</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineas as $l): ?>
                    <?php $lineaTotal = $l['cantidad'] * $l['precio_unidad']; ?>
                    <tr>
                        <td><code><?php echo e($l['producto_codigo'] ?? '-'); ?></code></td>
                        <td><?php echo e($l['producto_nombre'] ?? 'Producte #' . $l['idProducto']); ?></td>
                        <td style="text-align: center;"><?php echo number_format($l['cantidad'], 0); ?></td>
                        <td style="text-align: right;"><?php echo number_format($l['precio_unidad_sin_desc'] ?? $l['precio_unidad'], 2, ',', '.'); ?> &euro;</td>
                        <td style="text-align: center;">
                            <?php if ($l['descuento'] > 0): ?>
                            <span class="badge badge-warning"><?php echo number_format($l['descuento'], 1); ?>%</span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;"><?php echo number_format($l['precio_unidad'], 2, ',', '.'); ?> &euro;</td>
                        <td style="text-align: right;"><strong><?php echo number_format($lineaTotal, 2, ',', '.'); ?> &euro;</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <?php if ($compra['total_base'] > 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: right; font-weight: 500;">Base imposable:</td>
                        <td style="text-align: right;"><?php echo number_format($compra['total_base'], 2, ',', '.'); ?> &euro;</td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: right; font-weight: 500;">IVA:</td>
                        <td style="text-align: right;"><?php echo number_format($compra['total_iva'], 2, ',', '.'); ?> &euro;</td>
                    </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="6" style="text-align: right; font-weight: 600;">TOTAL:</td>
                        <td style="text-align: right; font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                            <?php echo number_format($compra['total'] ?: $totalBase, 2, ',', '.'); ?> &euro;
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
.info-item { }
.info-label { display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem; }
.info-value { font-size: 0.95rem; font-weight: 500; }
.total-row { background: var(--gray-50); }
.total-row td { padding: 1rem !important; }
.btn-success { background: var(--success, #10b981); color: white; border: none; }
.btn-success:hover { background: #059669; }
.btn-danger { background: var(--danger, #dc2626); color: white; border: none; }
.btn-danger:hover { background: #b91c1c; }
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
        <div class="modal-header">
            <h3>Eliminar Compra</h3>
        </div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar la compra <strong>#<?php echo $id; ?></strong>?</p>
            <p style="color: var(--danger, #dc2626); font-size: 0.875rem;">
                Aquesta acció no es pot desfer. S'eliminaran totes les línies associades.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form method="POST" action="<?php echo getAdminUrl('modules/compres/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmarEliminar() {
    document.getElementById('modalEliminar').style.display = 'flex';
}
function tancarModal() {
    document.getElementById('modalEliminar').style.display = 'none';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tancarModal();
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
