<?php
/**
 * El Celler Nou - Mòdul Vendes - Llistat
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Vendes';

// Parámetros de filtro
$search = trim($_GET['q'] ?? '');
$filterCliente = (int)($_GET['client'] ?? 0);
$filterEstado = $_GET['estat'] ?? '';
$filterDesde = $_GET['desde'] ?? '';
$filterHasta = $_GET['hasta'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Construir WHERE
$where = ['1=1'];
$params = [];

if ($search !== '') {
    $searchEsc = mysqli_real_escape_string($bd, $search);
    $where[] = "(c.nombre LIKE '%$searchEsc%' OR v.id = '$searchEsc')";
}

if ($filterCliente > 0) {
    $where[] = "v.idCliente = $filterCliente";
}

if ($filterEstado !== '') {
    $where[] = "v.idEstado = " . (int)$filterEstado;
}

if ($filterDesde !== '') {
    $desdeEsc = mysqli_real_escape_string($bd, $filterDesde);
    $where[] = "v.fecha >= '$desdeEsc'";
}

if ($filterHasta !== '') {
    $hastaEsc = mysqli_real_escape_string($bd, $filterHasta);
    $where[] = "v.fecha <= '$hastaEsc 23:59:59'";
}

$whereClause = implode(' AND ', $where);

// Contar total
$countSql = "SELECT COUNT(*) as total FROM ventas v LEFT JOIN clientes c ON v.idCliente = c.id WHERE $whereClause";
$countResult = mysqli_query($bd, $countSql);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $perPage);

// Obtener ventas con totales
$sql = "SELECT v.*, c.nombre as cliente_nombre,
        (SELECT SUM(lv.cantidad * lv.precio_unidad) FROM linea_venta lv WHERE lv.idVenta = v.id) as total_venta,
        (SELECT COUNT(*) FROM linea_venta lv WHERE lv.idVenta = v.id) as num_lineas
        FROM ventas v
        LEFT JOIN clientes c ON v.idCliente = c.id
        WHERE $whereClause
        ORDER BY v.fecha DESC, v.id DESC
        LIMIT $perPage OFFSET $offset";
$result = mysqli_query($bd, $sql);
$ventas = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ventas[] = $row;
    }
}

// Obtener clientes para filtro
$clientes = [];
$rsClients = mysqli_query($bd, "SELECT id, nombre FROM clientes ORDER BY nombre");
if ($rsClients) {
    while ($row = mysqli_fetch_assoc($rsClients)) {
        $clientes[] = $row;
    }
}

// Estadísticas
$stats = ['total' => 0, 'pendents' => 0, 'facturades' => 0, 'import_mes' => 0];
$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM ventas");
if ($rsStat) $stats['total'] = mysqli_fetch_assoc($rsStat)['total'];

$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM ventas WHERE idEstado = 0");
if ($rsStat) $stats['pendents'] = mysqli_fetch_assoc($rsStat)['total'];

$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM ventas WHERE idEstado = 5");
if ($rsStat) $stats['facturades'] = mysqli_fetch_assoc($rsStat)['total'];

$mesActual = date('Y-m');
$rsStat = mysqli_query($bd, "SELECT COALESCE(SUM(lv.cantidad * lv.precio_unidad), 0) as total
    FROM ventas v JOIN linea_venta lv ON lv.idVenta = v.id
    WHERE v.fecha LIKE '$mesActual%'");
if ($rsStat) $stats['import_mes'] = mysqli_fetch_assoc($rsStat)['total'];

// Flash messages
$msg = $_GET['msg'] ?? '';
$flashMessages = [
    'created' => ['type' => 'success', 'text' => 'Venda creada correctament.'],
    'updated' => ['type' => 'success', 'text' => 'Venda modificada correctament.'],
    'deleted' => ['type' => 'success', 'text' => 'Venda eliminada correctament.'],
    'invoiced' => ['type' => 'success', 'text' => 'Venda facturada correctament.'],
    'error' => ['type' => 'error', 'text' => 'S\'ha produït un error.'],
];

$estadoLabels = [
    0 => ['text' => 'Pendent', 'class' => 'badge-warning'],
    5 => ['text' => 'Facturada', 'class' => 'badge-success'],
];

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<?php if (isset($flashMessages[$msg])): ?>
<div class="alert alert-<?php echo $flashMessages[$msg]['type']; ?>" style="margin-bottom: 1rem;">
    <?php echo e($flashMessages[$msg]['text']); ?>
</div>
<?php endif; ?>

<!-- Estadístiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
            <div class="stat-label">Total Vendes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['pendents']); ?></div>
            <div class="stat-label">Pendents</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['facturades']); ?></div>
            <div class="stat-label">Facturades</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['import_mes'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Import Mes</div>
        </div>
    </div>
</div>

<!-- Filtres i accions -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" style="display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 150px;">
                <label class="filter-label">Cercar</label>
                <input type="text" name="q" class="form-input" placeholder="Nom client o # venda..." value="<?php echo e($search); ?>">
            </div>
            <div style="min-width: 180px;">
                <label class="filter-label">Client</label>
                <select name="client" class="form-input">
                    <option value="0">Tots els clients</option>
                    <?php foreach ($clientes as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $filterCliente == $c['id'] ? 'selected' : ''; ?>>
                        <?php echo e($c['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="min-width: 130px;">
                <label class="filter-label">Estat</label>
                <select name="estat" class="form-input">
                    <option value="">Tots</option>
                    <option value="0" <?php echo $filterEstado === '0' ? 'selected' : ''; ?>>Pendent</option>
                    <option value="5" <?php echo $filterEstado === '5' ? 'selected' : ''; ?>>Facturada</option>
                </select>
            </div>
            <div style="min-width: 140px;">
                <label class="filter-label">Des de</label>
                <input type="date" name="desde" class="form-input" value="<?php echo e($filterDesde); ?>">
            </div>
            <div style="min-width: 140px;">
                <label class="filter-label">Fins a</label>
                <input type="date" name="hasta" class="form-input" value="<?php echo e($filterHasta); ?>">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="<?php echo getAdminUrl('modules/vendes/'); ?>" class="btn btn-secondary btn-sm">Netejar</a>
            </div>
        </form>
    </div>
</div>

<!-- Accions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div style="color: var(--gray-500); font-size: 0.875rem;">
        <?php echo number_format($totalRows); ?> vendes trobades
    </div>
    <a href="<?php echo getAdminUrl('modules/vendes/crear.php'); ?>" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nova Venda
    </a>
</div>

<!-- Taula de vendes -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 70px;">#</th>
                    <th>Client</th>
                    <th style="width: 120px;">Data</th>
                    <th style="width: 80px;">Línies</th>
                    <th style="width: 120px; text-align: right;">Import</th>
                    <th style="width: 100px;">Estat</th>
                    <th style="width: 160px;">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ventas)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--gray-400);">
                        No s'han trobat vendes.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($ventas as $v): ?>
                <tr>
                    <td><strong><?php echo $v['id']; ?></strong></td>
                    <td>
                        <?php if ($v['cliente_nombre']): ?>
                            <a href="<?php echo getAdminUrl('modules/clients/modificar.php?id=' . $v['idCliente']); ?>">
                                <?php echo e($v['cliente_nombre']); ?>
                            </a>
                        <?php else: ?>
                            <span style="color: var(--gray-400);">Client general</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($v['fecha'])); ?></td>
                    <td><?php echo $v['num_lineas']; ?></td>
                    <td style="text-align: right; font-weight: 600;">
                        <?php echo number_format($v['total_venta'] ?? 0, 2, ',', '.'); ?> &euro;
                    </td>
                    <td>
                        <?php
                        $estado = $estadoLabels[$v['idEstado']] ?? ['text' => 'Desconegut', 'class' => 'badge-default'];
                        ?>
                        <span class="badge <?php echo $estado['class']; ?>"><?php echo $estado['text']; ?></span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.25rem;">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="viewVenda(<?php echo $v['id']; ?>)" title="Veure detall">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <a href="<?php echo getAdminUrl('modules/vendes/pdf.php?id=' . $v['id']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="Imprimir PDF">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </a>
                            <a href="<?php echo getAdminUrl('modules/vendes/modificar.php?id=' . $v['id']); ?>" class="btn btn-sm btn-secondary" title="Modificar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <?php if ($v['idEstado'] == 0): ?>
                            <button type="button" class="btn btn-sm btn-primary" onclick="facturarVenda(<?php echo $v['id']; ?>)" title="Facturar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $v['id']; ?>)" title="Eliminar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Paginació -->
<?php if ($totalPages > 1): ?>
<div class="pagination" style="margin-top: 1rem;">
    <?php
    $queryParams = $_GET;
    unset($queryParams['page']);
    $baseUrl = getAdminUrl('modules/vendes/') . '?' . http_build_query($queryParams);
    ?>
    <?php if ($page > 1): ?>
    <a href="<?php echo $baseUrl . '&page=' . ($page - 1); ?>" class="btn btn-sm btn-secondary">&laquo; Anterior</a>
    <?php endif; ?>
    <span style="padding: 0.5rem; color: var(--gray-500); font-size: 0.875rem;">
        Pàgina <?php echo $page; ?> de <?php echo $totalPages; ?>
    </span>
    <?php if ($page < $totalPages): ?>
    <a href="<?php echo $baseUrl . '&page=' . ($page + 1); ?>" class="btn btn-sm btn-secondary">Següent &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Modal Detall -->
<div id="detailModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title">Detall de la Venda</h3>
            <button type="button" class="modal-close" onclick="closeModal('detailModal')">&times;</button>
        </div>
        <div class="modal-body" id="detailContent">
            <p style="text-align: center; color: var(--gray-400);">Carregant...</p>
        </div>
    </div>
</div>

<!-- Modal Facturar -->
<div id="invoiceModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3 class="modal-title">Facturar Venda</h3>
            <button type="button" class="modal-close" onclick="closeModal('invoiceModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo getAdminUrl('modules/vendes/facturar.php'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="idVenta" id="invoiceVentaId">
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Data Factura</label>
                    <input type="date" name="fecha_emision" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Data Venciment</label>
                    <input type="date" name="fecha_vencimiento" class="form-input" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Forma de Pagament</label>
                    <select name="idFormaPago" class="form-input" required>
                        <option value="1">Efectiu</option>
                        <option value="2">Transferència</option>
                        <option value="3">Xec</option>
                        <option value="4">Domiciliació</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('invoiceModal')">Cancel·lar</button>
                <button type="submit" class="btn btn-primary">Facturar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="deleteModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">Eliminar Venda</h3>
            <button type="button" class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo getAdminUrl('modules/vendes/eliminar.php'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="id" id="deleteVentaId">
            <div class="modal-body">
                <p>Estàs segur que vols eliminar aquesta venda? L'estoc es restaurarà automàticament.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel·lar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<script>
function viewVenda(id) {
    document.getElementById('detailContent').innerHTML = '<p style="text-align:center;color:var(--gray-400);">Carregant...</p>';
    document.getElementById('detailModal').style.display = 'flex';

    fetch('<?php echo getAdminUrl('modules/vendes/detall_ajax.php'); ?>?id=' + id)
        .then(r => r.text())
        .then(html => document.getElementById('detailContent').innerHTML = html);
}

function facturarVenda(id) {
    document.getElementById('invoiceVentaId').value = id;
    document.getElementById('invoiceModal').style.display = 'flex';
}

function confirmDelete(id) {
    document.getElementById('deleteVentaId').value = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Tancar modal amb clic fora
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>

<style>
.filter-label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
.form-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; }
.badge { display: inline-block; padding: 0.25rem 0.625rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-success { background: #d1fae5; color: #065f46; }
.badge-default { background: var(--gray-100); color: var(--gray-600); }
.pagination { display: flex; justify-content: center; align-items: center; gap: 0.5rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; padding: 1rem; border-radius: 8px; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; }

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-content { background: white; border-radius: 12px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-200); }
.modal-title { font-size: 1.125rem; font-weight: 600; margin: 0; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-400); padding: 0; line-height: 1; }
.modal-close:hover { color: var(--gray-600); }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid var(--gray-200); }
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
