<?php
/**
 * El Celler Nou - Mòdul Tiquets
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Tiquets';

// Obtener estadísticas - Optimizado: 1 sola consulta para stats
$stats = [
    'tiquets_hoy' => 0,
    'ventas_hoy' => 0,
    'tiquets_mes' => 0,
    'ventas_mes' => 0
];

$today = date('Y-m-d');
$firstDayOfMonth = date('Y-m-01');

// Consulta única para todas las estadísticas usando CASE
$sql = "SELECT
    SUM(CASE WHEN DATE(t.fecha) = '$today' THEN 1 ELSE 0 END) as tiquets_hoy,
    SUM(CASE WHEN t.fecha >= '$firstDayOfMonth' THEN 1 ELSE 0 END) as tiquets_mes,
    (SELECT COALESCE(SUM(cantidad * precio_unidad), 0) FROM linea_tiquet WHERE DATE(fecha) = '$today') as ventas_hoy,
    (SELECT COALESCE(SUM(cantidad * precio_unidad), 0) FROM linea_tiquet WHERE fecha >= '$firstDayOfMonth') as ventas_mes
FROM tiquets t
WHERE t.fecha >= '$firstDayOfMonth'";

$result = @mysqli_query($bd, $sql);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $stats['tiquets_hoy'] = $row['tiquets_hoy'] ?? 0;
    $stats['tiquets_mes'] = $row['tiquets_mes'] ?? 0;
    $stats['ventas_hoy'] = $row['ventas_hoy'] ?? 0;
    $stats['ventas_mes'] = $row['ventas_mes'] ?? 0;
}

// Últimos tiquets - Optimizado: JOIN en lugar de subquery
$ultimosTiquets = [];
$sql = "SELECT t.id, t.fecha, t.tipo_pago, c.nombre as cliente,
               COALESCE(lt.import, 0) as import
        FROM tiquets t
        LEFT JOIN clientes c ON t.idCliente = c.id
        LEFT JOIN (
            SELECT idVenta, SUM(cantidad * precio_unidad) as import
            FROM linea_tiquet
            GROUP BY idVenta
        ) lt ON lt.idVenta = t.id
        ORDER BY t.fecha DESC, t.id DESC
        LIMIT 15";

$result = @mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ultimosTiquets[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['tiquets_hoy']); ?></div>
            <div class="stat-label">Tiquets Avui</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['ventas_hoy'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Vendes Avui</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['tiquets_mes']); ?></div>
            <div class="stat-label">Tiquets del Mes</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['ventas_mes'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Vendes del Mes</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Accions Ràpides</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo getAdminUrl('modules/tiquets/crear.php'); ?>" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nou Tiquet
            </a>
            <a href="<?php echo getAdminUrl('modules/tiquets/llistat.php'); ?>" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Llistat Tiquets
            </a>
        </div>
    </div>
</div>

<!-- Recent Tiquets -->
<?php if (!empty($ultimosTiquets)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Últims Tiquets</h3>
        <a href="<?php echo getAdminUrl('modules/tiquets/llistat.php'); ?>" class="btn btn-sm btn-secondary">Veure tots</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Client</th>
                        <th>Forma Pagament</th>
                        <th style="text-align: right;">Import</th>
                        <th style="width: 170px;">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosTiquets as $tiquet): ?>
                    <tr>
                        <td><strong>#<?php echo $tiquet['id']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($tiquet['fecha'])); ?></td>
                        <td><?php echo e($tiquet['cliente'] ?? 'Client general'); ?></td>
                        <td>
                            <?php
                            $tipoPago = $tiquet['tipo_pago'] ?? 'E';
                            $nombrePago = 'Efectiu';
                            $badgeClass = 'badge-secondary';
                            if ($tipoPago === 'T') { $nombrePago = 'Targeta'; $badgeClass = 'badge-success'; }
                            elseif ($tipoPago === 'C') { $nombrePago = 'Compte'; $badgeClass = 'badge-warning'; }
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $nombrePago; ?></span>
                        </td>
                        <td style="text-align: right;"><strong><?php echo number_format($tiquet['import'] ?? 0, 2, ',', '.'); ?> &euro;</strong></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="button" class="btn btn-sm btn-secondary" title="Veure"
                                        onclick="veureTiquet(<?php echo $tiquet['id']; ?>)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" title="Imprimir"
                                        onclick="imprimirTiquet(<?php echo $tiquet['id']; ?>)">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </button>
                                <a href="<?php echo getAdminUrl('modules/tiquets/modificar.php?id=' . $tiquet['id']); ?>"
                                   class="btn btn-sm btn-secondary" title="Editar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                        onclick="confirmarEliminar(<?php echo $tiquet['id']; ?>, '<?php echo date('d/m/Y', strtotime($tiquet['fecha'])); ?>')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Veure Tiquet -->
<div id="modalVeure" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModalVeure()"></div>
    <div class="modal-content modal-content-lg">
        <div class="modal-header">
            <h3 id="modalVeureTitle">Tiquet</h3>
            <button type="button" class="modal-close" onclick="tancarModalVeure()">&times;</button>
        </div>
        <div class="modal-body" id="modalVeureBody" style="max-height: 70vh; overflow-y: auto;">
            <div class="loading-spinner">Carregant...</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModalVeure()">Tancar</button>
            <a id="modalVeurePdf" href="#" class="btn btn-secondary" target="_blank">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; vertical-align: -3px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                PDF
            </a>
            <button type="button" class="btn btn-primary" onclick="imprimirModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; vertical-align: -3px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Iframe oculto para imprimir -->
<iframe id="printFrame" style="display: none;"></iframe>

<!-- Modal de confirmació eliminar -->
<div id="modalEliminar" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Eliminar Tiquet</h3>
            <button type="button" class="modal-close" onclick="tancarModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar el tiquet <strong id="tiquetInfo"></strong>?</p>
            <p style="color: var(--danger); font-size: 0.875rem;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Aquesta acció no es pot desfer. S'eliminaran totes les línies associades i es restaurarà l'estoc dels productes.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form id="formEliminar" method="POST" action="<?php echo getAdminUrl('modules/tiquets/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="id" id="eliminarId" value="">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<style>
.modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-backdrop { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); }
.modal-content { position: relative; background: white; border-radius: 8px; width: 100%; max-width: 450px; margin: 1rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
.modal-content-lg { max-width: 700px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-200); }
.modal-header h3 { margin: 0; font-size: 1.125rem; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-500); }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.5rem; border-top: 1px solid var(--gray-200); background: var(--gray-50); border-radius: 0 0 8px 8px; }
.btn-danger { background: var(--danger); color: white; border: none; }
.btn-danger:hover { background: #b91c1c; }
.loading-spinner { text-align: center; padding: 2rem; color: var(--gray-500); }
.tiquet-preview-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid var(--gray-200); }
.tiquet-preview-empresa { font-size: 0.8rem; color: var(--gray-600); }
.tiquet-preview-empresa strong { font-size: 1rem; color: var(--gray-900); }
.tiquet-preview-meta { text-align: right; }
.tiquet-preview-id { font-size: 1.1rem; font-weight: 700; color: var(--primary); }
.tiquet-preview-info { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--gray-100); font-size: 0.875rem; }
.tiquet-preview-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.tiquet-preview-table th { padding: 0.5rem 0.5rem; border-bottom: 2px solid var(--gray-300); text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--gray-500); }
.tiquet-preview-table td { padding: 0.5rem 0.5rem; border-bottom: 1px solid var(--gray-100); }
.tiquet-preview-table tfoot td { border-bottom: none; border-top: 2px solid var(--gray-300); padding-top: 0.75rem; }
.tiquet-preview-table .text-center { text-align: center; }
.tiquet-preview-table .text-right { text-align: right; }
.tiquet-preview-total { font-size: 1.15rem; font-weight: 700; color: var(--primary); }
.tiquet-preview-footer { text-align: center; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--gray-300); font-size: 0.8rem; color: var(--gray-500); }
</style>

<script>
var baseUrl = '<?php echo getAdminUrl('modules/tiquets/'); ?>';

function veureTiquet(id) {
    var modal = document.getElementById('modalVeure');
    var body = document.getElementById('modalVeureBody');
    var title = document.getElementById('modalVeureTitle');
    var pdfLink = document.getElementById('modalVeurePdf');

    title.textContent = 'Tiquet #' + id;
    pdfLink.href = baseUrl + 'pdf.php?id=' + id;
    body.innerHTML = '<div class="loading-spinner">Carregant...</div>';
    modal.style.display = 'flex';

    fetch(baseUrl + 'detall_ajax.php?id=' + id)
        .then(function(r) { return r.text(); })
        .then(function(html) { body.innerHTML = html; })
        .catch(function() { body.innerHTML = '<p style="color:red;">Error al carregar el tiquet.</p>'; });
}

function tancarModalVeure() {
    document.getElementById('modalVeure').style.display = 'none';
}

function imprimirModal() {
    var content = document.getElementById('modalVeureBody').innerHTML;
    imprimirHTML(content);
}

function imprimirTiquet(id) {
    fetch(baseUrl + 'detall_ajax.php?id=' + id)
        .then(function(r) { return r.text(); })
        .then(function(html) { imprimirHTML(html); })
        .catch(function() { alert('Error al carregar el tiquet.'); });
}

function imprimirHTML(content) {
    var frame = document.getElementById('printFrame');
    var doc = frame.contentDocument || frame.contentWindow.document;
    doc.open();
    doc.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Tiquet</title><style>' +
        'body { font-family: Arial, sans-serif; margin: 20px; color: #333; }' +
        '.tiquet-preview-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 2px solid #333; }' +
        '.tiquet-preview-empresa { font-size: 11px; }' +
        '.tiquet-preview-empresa strong { font-size: 14px; }' +
        '.tiquet-preview-meta { text-align: right; }' +
        '.tiquet-preview-id { font-size: 16px; font-weight: bold; }' +
        '.tiquet-preview-info { display: flex; flex-wrap: wrap; gap: 4px 18px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #ccc; font-size: 12px; }' +
        '.tiquet-preview-table { width: 100%; border-collapse: collapse; font-size: 12px; }' +
        '.tiquet-preview-table th { padding: 6px 4px; border-bottom: 2px solid #999; text-align: left; font-size: 10px; text-transform: uppercase; }' +
        '.tiquet-preview-table td { padding: 5px 4px; border-bottom: 1px solid #eee; }' +
        '.tiquet-preview-table tfoot td { border-bottom: none; border-top: 2px solid #999; padding-top: 8px; }' +
        '.text-center { text-align: center; }' +
        '.text-right { text-align: right; }' +
        '.tiquet-preview-total { font-size: 16px; font-weight: bold; }' +
        '.tiquet-preview-footer { text-align: center; margin-top: 12px; padding-top: 8px; border-top: 1px dashed #999; font-size: 11px; color: #666; }' +
        '@page { margin: 15mm; }' +
    '</style></head><body>' + content + '</body></html>');
    doc.close();
    frame.contentWindow.focus();
    setTimeout(function() { frame.contentWindow.print(); }, 250);
}

function confirmarEliminar(id, data) {
    document.getElementById('tiquetInfo').textContent = '#' + id + ' (' + data + ')';
    document.getElementById('eliminarId').value = id;
    document.getElementById('modalEliminar').style.display = 'flex';
}

function tancarModal() {
    document.getElementById('modalEliminar').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        tancarModal();
        tancarModalVeure();
    }
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
