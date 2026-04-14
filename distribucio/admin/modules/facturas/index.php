<?php
/**
 * El Celler Nou - Mòdul Factures - Llistat
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Factures';

// Parámetros de filtro
$search = trim($_GET['q'] ?? '');
$filterCliente = (int)($_GET['client'] ?? 0);
$filterEstado = $_GET['estat'] ?? '';
$filterDesde = $_GET['desde'] ?? '';
$filterHasta = $_GET['hasta'] ?? '';
$filterPagament = $_GET['pagament'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Construir WHERE
$where = ['1=1'];

if ($search !== '') {
    $searchEsc = mysqli_real_escape_string($bd, $search);
    $where[] = "(c.nombre LIKE '%$searchEsc%' OR f.id = '$searchEsc')";
}

if ($filterCliente > 0) {
    $where[] = "f.idCliente = $filterCliente";
}

if ($filterEstado !== '') {
    if ($filterEstado === 'pagada') {
        $where[] = "f.fecha_pago IS NOT NULL AND CAST(f.fecha_pago AS CHAR) != '0000-00-00 00:00:00'";
    } elseif ($filterEstado === 'pendent') {
        $where[] = "(f.fecha_pago IS NULL OR CAST(f.fecha_pago AS CHAR) = '0000-00-00 00:00:00') AND (SELECT COALESCE(SUM(pf.importe),0) FROM pagos_factura pf WHERE pf.idFactura = f.id) = 0";
    } elseif ($filterEstado === 'parcial') {
        $where[] = "(f.fecha_pago IS NULL OR CAST(f.fecha_pago AS CHAR) = '0000-00-00 00:00:00') AND (SELECT COALESCE(SUM(pf.importe),0) FROM pagos_factura pf WHERE pf.idFactura = f.id) > 0";
    }
}

if ($filterDesde !== '') {
    $desdeEsc = mysqli_real_escape_string($bd, $filterDesde);
    $where[] = "f.fecha >= '$desdeEsc'";
}

if ($filterHasta !== '') {
    $hastaEsc = mysqli_real_escape_string($bd, $filterHasta);
    $where[] = "f.fecha <= '$hastaEsc 23:59:59'";
}

if ($filterPagament !== '') {
    $where[] = "f.idFormaPago = " . (int)$filterPagament;
}

$whereClause = implode(' AND ', $where);

// Contar total
$countSql = "SELECT COUNT(*) as total FROM facturas f LEFT JOIN clientes c ON f.idCliente = c.id WHERE $whereClause";
$countResult = mysqli_query($bd, $countSql);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $perPage);

// Obtener facturas
$sql = "SELECT f.*, c.nombre as cliente_nombre, c.recarrec_equiv,
        (SELECT SUM(lf.cantidad * lf.precio_unidad) FROM linea_factura lf WHERE lf.idFactura = f.id) as base_total,
        (SELECT COUNT(*) FROM linea_factura lf WHERE lf.idFactura = f.id) as num_lineas,
        (SELECT COALESCE(SUM(pf.importe), 0) FROM pagos_factura pf WHERE pf.idFactura = f.id) as total_pagado
        FROM facturas f
        LEFT JOIN clientes c ON f.idCliente = c.id
        WHERE $whereClause
        ORDER BY f.fecha DESC, f.id DESC
        LIMIT $perPage OFFSET $offset";
$result = mysqli_query($bd, $sql);
$facturas = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $facturas[] = $row;
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
$stats = ['total' => 0, 'pendents' => 0, 'parcials' => 0, 'pagades' => 0, 'import_mes' => 0];
$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM facturas");
if ($rsStat) $stats['total'] = mysqli_fetch_assoc($rsStat)['total'];

$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM facturas WHERE (fecha_pago IS NULL OR CAST(fecha_pago AS CHAR) = '0000-00-00 00:00:00') AND (SELECT COALESCE(SUM(pf.importe),0) FROM pagos_factura pf WHERE pf.idFactura = facturas.id) = 0");
if ($rsStat) $stats['pendents'] = mysqli_fetch_assoc($rsStat)['total'];

$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM facturas WHERE (fecha_pago IS NULL OR CAST(fecha_pago AS CHAR) = '0000-00-00 00:00:00') AND (SELECT COALESCE(SUM(pf.importe),0) FROM pagos_factura pf WHERE pf.idFactura = facturas.id) > 0");
if ($rsStat) $stats['parcials'] = mysqli_fetch_assoc($rsStat)['total'];

$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM facturas WHERE fecha_pago IS NOT NULL AND CAST(fecha_pago AS CHAR) != '0000-00-00 00:00:00'");
if ($rsStat) $stats['pagades'] = mysqli_fetch_assoc($rsStat)['total'];

$mesActual = date('Y-m');
$rsStat = mysqli_query($bd, "SELECT COALESCE(SUM(lf.cantidad * lf.precio_unidad), 0) as total
    FROM facturas f JOIN linea_factura lf ON lf.idFactura = f.id
    WHERE f.fecha LIKE '$mesActual%'");
if ($rsStat) $stats['import_mes'] = mysqli_fetch_assoc($rsStat)['total'];

$formaPagoLabels = [1 => 'Efectiu', 2 => 'Transferència', 3 => 'Xec', 4 => 'Domiciliació'];

// Flash messages
$msg = $_GET['msg'] ?? '';
$flashMessages = [
    'deleted' => ['type' => 'success', 'text' => 'Factura eliminada correctament.'],
    'paid' => ['type' => 'success', 'text' => 'Factura marcada com a pagada.'],
    'partial' => ['type' => 'success', 'text' => 'Pagament a compte registrat correctament.'],
    'error' => ['type' => 'error', 'text' => 'S\'ha produït un error.'],
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
            <div class="stat-label">Total Factures</div>
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
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v1"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['parcials']); ?></div>
            <div class="stat-label">Parcials</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['pagades']); ?></div>
            <div class="stat-label">Pagades</div>
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

<!-- Filtres -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" style="display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 150px;">
                <label class="filter-label">Cercar</label>
                <input type="text" name="q" class="form-input" placeholder="Nom client o # factura..." value="<?php echo e($search); ?>">
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
            <div style="min-width: 120px;">
                <label class="filter-label">Estat</label>
                <select name="estat" class="form-input">
                    <option value="">Totes</option>
                    <option value="pendent" <?php echo $filterEstado === 'pendent' ? 'selected' : ''; ?>>Pendent</option>
                    <option value="parcial" <?php echo $filterEstado === 'parcial' ? 'selected' : ''; ?>>Parcial</option>
                    <option value="pagada" <?php echo $filterEstado === 'pagada' ? 'selected' : ''; ?>>Pagada</option>
                </select>
            </div>
            <div style="min-width: 120px;">
                <label class="filter-label">Pagament</label>
                <select name="pagament" class="form-input">
                    <option value="">Tots</option>
                    <?php foreach ($formaPagoLabels as $fpId => $fpName): ?>
                    <option value="<?php echo $fpId; ?>" <?php echo $filterPagament == $fpId ? 'selected' : ''; ?>>
                        <?php echo $fpName; ?>
                    </option>
                    <?php endforeach; ?>
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
                <a href="<?php echo getAdminUrl('modules/facturas/'); ?>" class="btn btn-secondary btn-sm">Netejar</a>
            </div>
        </form>
    </div>
</div>

<!-- Info -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div style="color: var(--gray-500); font-size: 0.875rem;">
        <?php echo number_format($totalRows); ?> factures trobades
    </div>
</div>

<!-- Taula -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 110px;">N. Factura</th>
                    <th>Client</th>
                    <th style="width: 100px;">Data</th>
                    <th style="width: 100px;">Venciment</th>
                    <th style="width: 110px;">Pagament</th>
                    <th style="width: 110px; text-align: right;">Base</th>
                    <th style="width: 90px;">Estat</th>
                    <th style="width: 170px;">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($facturas)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem; color: var(--gray-400);">
                        No s'han trobat factures.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($facturas as $f): ?>
                <?php
                    $numFactura = 'FA' . str_pad((date('Y', strtotime($f['fecha'])) * 100) . $f['id'], 10, '0', STR_PAD_LEFT);
                    $isPagada = !empty($f['fecha_pago']) && $f['fecha_pago'] !== '0000-00-00 00:00:00' && $f['fecha_pago'] !== '0000-00-00';
                ?>
                <tr>
                    <td><strong style="font-family: monospace;"><?php echo $numFactura; ?></strong></td>
                    <td>
                        <?php if ($f['cliente_nombre']): ?>
                            <a href="<?php echo getAdminUrl('modules/clients/modificar.php?id=' . $f['idCliente']); ?>">
                                <?php echo e($f['cliente_nombre']); ?>
                            </a>
                        <?php else: ?>
                            <span style="color: var(--gray-400);">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($f['fecha'])); ?></td>
                    <td>
                        <?php if ($f['fecha_vencimiento'] && $f['fecha_vencimiento'] !== '0000-00-00'): ?>
                            <?php echo date('d/m/Y', strtotime($f['fecha_vencimiento'])); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?php echo $formaPagoLabels[$f['idFormaPago']] ?? '-'; ?></td>
                    <td style="text-align: right; font-weight: 600;">
                        <?php echo number_format($f['base_total'] ?? 0, 2, ',', '.'); ?> &euro;
                    </td>
                    <td>
                        <?php if ($isPagada): ?>
                            <span class="badge badge-success">Pagada</span>
                        <?php elseif ($f['total_pagado'] > 0): ?>
                            <span class="badge badge-partial">Parcial</span>
                            <div style="font-size: 0.7rem; color: var(--gray-500); margin-top: 2px;">
                                <?php echo number_format($f['total_pagado'], 2, ',', '.'); ?> &euro;
                            </div>
                        <?php else: ?>
                            <span class="badge badge-warning">Pendent</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.25rem;">
                            <a href="<?php echo getAdminUrl('modules/facturas/pdf.php?id=' . $f['id']); ?>" class="btn btn-sm btn-secondary" target="_blank" title="PDF">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="viewFactura(<?php echo $f['id']; ?>)" title="Veure detall">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <?php if (!$isPagada): ?>
                            <button type="button" class="btn btn-sm btn-primary" onclick="obrirPagament(<?php echo $f['id']; ?>, <?php echo htmlspecialchars(json_encode($f['total_pagado']), ENT_QUOTES); ?>)" title="Registrar pagament">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v1"/>
                                </svg>
                            </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $f['id']; ?>)" title="Eliminar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
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
    $baseUrl = getAdminUrl('modules/facturas/') . '?' . http_build_query($queryParams);
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
    <div class="modal-content" style="max-width: 750px;">
        <div class="modal-header">
            <h3 class="modal-title">Detall de la Factura</h3>
            <button type="button" class="modal-close" onclick="closeModal('detailModal')">&times;</button>
        </div>
        <div class="modal-body" id="detailContent">
            <p style="text-align: center; color: var(--gray-400);">Carregant...</p>
        </div>
    </div>
</div>

<!-- Modal Pagament -->
<div id="payModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header">
            <h3 class="modal-title">Registrar Pagament</h3>
            <button type="button" class="modal-close" onclick="closeModal('payModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo getAdminUrl('modules/facturas/pagar.php'); ?>" id="payForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="id" id="payFacturaId">
            <input type="hidden" name="mode" id="payMode" value="full">
            <div class="modal-body">
                <!-- Info de pagament acumulat -->
                <div id="payInfoParcial" style="display: none; background: #fef3c7; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem;">
                    Ja s'han pagat <strong id="payInfoAmount">0,00</strong> &euro; a compte.
                </div>

                <!-- Selector de mode -->
                <div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem;">
                    <button type="button" class="pay-mode-btn active" id="btnModeFull" onclick="setPayMode('full')">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Pagar al complet
                    </button>
                    <button type="button" class="pay-mode-btn" id="btnModePartial" onclick="setPayMode('partial')">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v1"/>
                        </svg>
                        Pagament a compte
                    </button>
                </div>

                <!-- Camps comuns -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Data del pagament</label>
                    <input type="date" name="fecha_pago" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Mètode de pagament</label>
                    <select name="metodo_pago" class="form-input">
                        <?php foreach ($formaPagoLabels as $fpId => $fpName): ?>
                        <option value="<?php echo $fpId; ?>"><?php echo $fpName; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Camps exclusius de pagament parcial -->
                <div id="partialFields" style="display: none;">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label">Import a compte (&euro;)</label>
                        <input type="number" name="importe" id="payImporte" class="form-input" min="0.01" step="0.01" placeholder="0,00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Comentari (opcional)</label>
                        <input type="text" name="comentario" class="form-input" placeholder="Ex: Transferència parcial...">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('payModal')">Cancel·lar</button>
                <button type="submit" class="btn btn-primary" id="paySubmitBtn">Confirmar pagament</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="deleteModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">Eliminar Factura</h3>
            <button type="button" class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo getAdminUrl('modules/facturas/eliminar.php'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="id" id="deleteFacturaId">
            <div class="modal-body">
                <p>Estàs segur que vols eliminar aquesta factura? La venda associada tornarà a estat pendent.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel·lar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<script>
function viewFactura(id) {
    document.getElementById('detailContent').innerHTML = '<p style="text-align:center;color:var(--gray-400);">Carregant...</p>';
    document.getElementById('detailModal').style.display = 'flex';

    fetch('<?php echo getAdminUrl('modules/facturas/detall_ajax.php'); ?>?id=' + id)
        .then(r => r.text())
        .then(html => document.getElementById('detailContent').innerHTML = html);
}

function obrirPagament(id, totalPagado) {
    document.getElementById('payFacturaId').value = id;
    document.getElementById('payMode').value = 'full';
    document.getElementById('payImporte').value = '';

    // Mostrar info si ya hay pagos parciales
    var infoParcial = document.getElementById('payInfoParcial');
    if (totalPagado > 0) {
        document.getElementById('payInfoAmount').textContent = parseFloat(totalPagado).toFixed(2).replace('.', ',');
        infoParcial.style.display = 'block';
    } else {
        infoParcial.style.display = 'none';
    }

    setPayMode('full');
    document.getElementById('payModal').style.display = 'flex';
}

function setPayMode(mode) {
    document.getElementById('payMode').value = mode;
    var partialFields = document.getElementById('partialFields');
    var btnFull = document.getElementById('btnModeFull');
    var btnPartial = document.getElementById('btnModePartial');
    var importeInput = document.getElementById('payImporte');

    if (mode === 'partial') {
        partialFields.style.display = 'block';
        importeInput.required = true;
        btnPartial.classList.add('active');
        btnFull.classList.remove('active');
    } else {
        partialFields.style.display = 'none';
        importeInput.required = false;
        btnFull.classList.add('active');
        btnPartial.classList.remove('active');
    }
}

function confirmDelete(id) {
    document.getElementById('deleteFacturaId').value = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

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
.badge-partial { background: #dbeafe; color: #1e40af; }
.badge-success { background: #d1fae5; color: #065f46; }

.pay-mode-btn {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    padding: 0.625rem 0.75rem; font-size: 0.8125rem; font-weight: 600;
    border: 2px solid var(--gray-200); border-radius: 8px;
    background: white; color: var(--gray-500); cursor: pointer; transition: all 0.2s;
}
.pay-mode-btn:hover { border-color: var(--gray-300); color: var(--gray-700); }
.pay-mode-btn.active { border-color: var(--primary); color: var(--primary); background: rgba(114, 47, 55, 0.04); }
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
