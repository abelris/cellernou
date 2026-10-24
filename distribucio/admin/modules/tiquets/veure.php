<?php
/**
 * El Celler Nou - Veure Tiquet
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php'));
    exit;
}

// Obtener tiquet
$result = mysqli_query($bd, "SELECT t.*, c.nombre as cliente_nombre, c.nif as cliente_nif,
                                    c.direccion as cliente_direccion, c.telefono as cliente_telefono
                             FROM tiquets t
                             LEFT JOIN clientes c ON t.idCliente = c.id
                             WHERE t.id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php'));
    exit;
}

$tiquet = mysqli_fetch_assoc($result);
$pageTitle = 'Tiquet #' . $id;

// Obtener líneas del tiquet
$lineas = [];
$sql = "SELECT lt.*, p.nombre as producto_nombre, p.codigo as producto_codigo
        FROM linea_tiquet lt
        LEFT JOIN productos p ON lt.idProducto = p.id
        WHERE lt.idVenta = $id
        ORDER BY lt.id";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $lineas[] = $row;
    }
}

// Calcular totales
// precio_unidad_sin_desc = preu de tarifa; precio_unidad = preu amb el descompte ja aplicat.
$totalBrut = 0;
$totalDescompte = 0;
$subtotal = 0;
$totalIva = 0;
foreach ($lineas as $l) {
    $tarifa = $l['precio_unidad_sin_desc'] > 0 ? $l['precio_unidad_sin_desc'] : $l['precio_unidad'];
    $brutLinea = $l['cantidad'] * $tarifa;
    $lineaTotal = $l['cantidad'] * $l['precio_unidad'];

    $totalBrut += $brutLinea;
    $totalDescompte += $brutLinea - $lineaTotal;
    $subtotal += $lineaTotal;
    $totalIva += $lineaTotal - ($lineaTotal / (1 + ($l['iva'] / 100)));
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<?php
// Avís de crear.php / modificar.php: productes venuts sense cost conegut.
$senseCost = $_SESSION['tiquet_sense_cost'][$id] ?? [];
unset($_SESSION['tiquet_sense_cost']);
if ($senseCost): ?>
<div class="alert" style="margin-bottom: 1rem; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 0.875rem 1rem; border-radius: 8px;">
    <strong>Productes sense cost de compra:</strong> <?php echo e(implode(', ', $senseCost)); ?>.
    S'han desat amb cost 0 i el marge d'aquest tiquet sortirà inflat. Posa'ls el preu de compra a la fitxa del producte.
</div>
<?php endif; ?>

<div class="page-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <a href="<?php echo getAdminUrl('modules/tiquets/llistat.php'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar
    </a>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?php echo getAdminUrl('modules/tiquets/modificar.php?id=' . $id); ?>" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>
        <a href="<?php echo getAdminUrl('modules/tiquets/pdf.php?id=' . $id); ?>" class="btn btn-secondary" target="_blank">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            PDF Tiquet
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

<div class="tiquet-container">
    <!-- Cabecera del tiquet -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Tiquet #<?php echo $id; ?></h3>
            <?php
            $tipoPago = $tiquet['tipo_pago'];
            $nombrePago = 'Efectiu';
            $badgeClass = 'badge-secondary';
            if ($tipoPago === 'T') { $nombrePago = 'Targeta'; $badgeClass = 'badge-success'; }
            elseif ($tipoPago === 'C') { $nombrePago = 'Compte'; $badgeClass = 'badge-warning'; }
            ?>
            <span class="badge <?php echo $badgeClass; ?>"><?php echo $nombrePago; ?></span>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Data</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($tiquet['fecha'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Client</span>
                    <span class="info-value"><?php echo e($tiquet['cliente_nombre'] ?? 'Client general'); ?></span>
                </div>
                <?php if (!empty($tiquet['cliente_nif'])): ?>
                <div class="info-item">
                    <span class="info-label">NIF</span>
                    <span class="info-value"><?php echo e($tiquet['cliente_nif']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($tiquet['cliente_telefono'])): ?>
                <div class="info-item">
                    <span class="info-label">Telèfon</span>
                    <span class="info-value"><?php echo e($tiquet['cliente_telefono']); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <span class="info-label">Botiga</span>
                    <span class="info-value"><?php echo $tiquet['idTienda'] == 1 ? 'Botiga 1' : 'Botiga 2'; ?></span>
                </div>
                <?php if (!empty($tiquet['comentarios'])): ?>
                <div class="info-item" style="grid-column: span 2;">
                    <span class="info-label">Comentaris</span>
                    <span class="info-value"><?php echo e($tiquet['comentarios']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Líneas del tiquet -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detall <span class="badge badge-info"><?php echo count($lineas); ?> línies</span></h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($lineas)): ?>
            <div class="empty-state" style="padding: 2rem; text-align: center;">
                <p>Aquest tiquet no té línies.</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Codi</th>
                            <th>Producte</th>
                            <th style="text-align: center;">Quantitat</th>
                            <th style="text-align: right;">Preu tarifa</th>
                            <th style="text-align: center;">IVA</th>
                            <th style="text-align: right;">Descompte</th>
                            <th style="text-align: right;">Preu net</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lineas as $l): ?>
                        <?php
                        $tarifa = $l['precio_unidad_sin_desc'] > 0 ? $l['precio_unidad_sin_desc'] : $l['precio_unidad'];
                        $lineaTotal = $l['cantidad'] * $l['precio_unidad'];
                        $descompteLinea = ($l['cantidad'] * $tarifa) - $lineaTotal;
                        ?>
                        <tr>
                            <td><code><?php echo e($l['producto_codigo'] ?? '-'); ?></code></td>
                            <td><?php echo e($l['producto_nombre'] ?? 'Producte #' . $l['idProducto']); ?></td>
                            <td style="text-align: center;"><?php echo number_format($l['cantidad'], 2, ',', '.'); ?></td>
                            <td style="text-align: right;"><?php echo number_format($tarifa, 2, ',', '.'); ?> &euro;</td>
                            <td style="text-align: center;"><?php echo (int)$l['iva']; ?>%</td>
                            <td style="text-align: right;">
                                <?php if ($l['descuento'] > 0): ?>
                                <span class="badge badge-warning"><?php echo number_format($l['descuento'], 0); ?>%</span>
                                <span style="color: var(--danger, #dc2626); font-size: 0.85em;">
                                    -<?php echo number_format($descompteLinea, 2, ',', '.'); ?> &euro;
                                </span>
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
                        <?php if ($totalDescompte > 0.001): ?>
                        <tr>
                            <td colspan="7" style="text-align: right;">Subtotal (sense descompte):</td>
                            <td style="text-align: right;"><?php echo number_format($totalBrut, 2, ',', '.'); ?> &euro;</td>
                        </tr>
                        <tr>
                            <td colspan="7" style="text-align: right; color: var(--danger, #dc2626);">Descompte:</td>
                            <td style="text-align: right; color: var(--danger, #dc2626);">
                                -<?php echo number_format($totalDescompte, 2, ',', '.'); ?> &euro;
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="7" style="text-align: right; color: var(--gray-500);">Base imposable:</td>
                            <td style="text-align: right; color: var(--gray-500);"><?php echo number_format($subtotal - $totalIva, 2, ',', '.'); ?> &euro;</td>
                        </tr>
                        <tr>
                            <td colspan="7" style="text-align: right; color: var(--gray-500);">IVA:</td>
                            <td style="text-align: right; color: var(--gray-500);"><?php echo number_format($totalIva, 2, ',', '.'); ?> &euro;</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="7" style="text-align: right; font-weight: 600;">TOTAL:</td>
                            <td style="text-align: right; font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                                <?php echo number_format($subtotal, 2, ',', '.'); ?> &euro;
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.info-item { }
.info-label { display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem; }
.info-value { font-size: 0.95rem; font-weight: 500; }
.total-row { background: var(--gray-50); }
.total-row td { padding: 1rem !important; }
@media (max-width: 768px) {
    .info-grid { grid-template-columns: repeat(2, 1fr); }
}
@media print {
    .page-header, .btn { display: none !important; }
    .card { box-shadow: none; border: 1px solid #ddd; }
}
.btn-danger { background: var(--danger, #dc2626); color: white; border: none; }
.btn-danger:hover { background: #b91c1c; }
</style>

<!-- Modal de confirmació -->
<div id="modalEliminar" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Eliminar Tiquet</h3>
            <button type="button" class="modal-close" onclick="tancarModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar el tiquet <strong>#<?php echo $id; ?></strong>?</p>
            <p style="color: var(--danger, #dc2626); font-size: 0.875rem;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Aquesta acció no es pot desfer. S'eliminaran totes les línies associades i es restaurarà l'estoc dels productes.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form method="POST" action="<?php echo getAdminUrl('modules/tiquets/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<style>
.modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-backdrop { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); }
.modal-content { position: relative; background: white; border-radius: 8px; width: 100%; max-width: 450px; margin: 1rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-200); }
.modal-header h3 { margin: 0; font-size: 1.125rem; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-500); }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.5rem; border-top: 1px solid var(--gray-200); background: var(--gray-50); border-radius: 0 0 8px 8px; }
</style>

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
