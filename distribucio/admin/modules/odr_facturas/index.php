<?php
/**
 * OnDigitalRis - Factures Venda - Llistat
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

$pageTitle = 'ODR - Factures Venda';

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

if ($search !== '') {
    $searchEsc = mysqli_real_escape_string($bd, $search);
    $where[] = "(c.nombre LIKE '%$searchEsc%' OR f.numero LIKE '%$searchEsc%' OR f.id = '$searchEsc')";
}

if ($filterCliente > 0) {
    $where[] = "f.idCliente = $filterCliente";
}

if ($filterEstado !== '') {
    $estadoEsc = mysqli_real_escape_string($bd, $filterEstado);
    $where[] = "f.estado = '$estadoEsc'";
}

if ($filterDesde !== '') {
    $desdeEsc = mysqli_real_escape_string($bd, $filterDesde);
    $where[] = "f.fecha >= '$desdeEsc'";
}

if ($filterHasta !== '') {
    $hastaEsc = mysqli_real_escape_string($bd, $filterHasta);
    $where[] = "f.fecha <= '$hastaEsc 23:59:59'";
}

$whereClause = implode(' AND ', $where);

// Contar total
$countSql = "SELECT COUNT(*) as total FROM odr_facturas f LEFT JOIN odr_clientes c ON f.idCliente = c.id WHERE $whereClause";
$countResult = mysqli_query($bd, $countSql);
$totalRows = $countResult ? mysqli_fetch_assoc($countResult)['total'] : 0;
$totalPages = ceil($totalRows / $perPage);

// Obtener facturas
$sql = "SELECT f.*, c.nombre as cliente_nombre,
        (SELECT SUM(lf.cantidad * lf.precio_unidad * (1 + lf.iva/100)) FROM odr_linea_factura lf WHERE lf.idFactura = f.id) as total_iva,
        (SELECT SUM(lf.cantidad * lf.precio_unidad) FROM odr_linea_factura lf WHERE lf.idFactura = f.id) as base_total,
        (SELECT COUNT(*) FROM odr_linea_factura lf WHERE lf.idFactura = f.id) as num_lineas
        FROM odr_facturas f
        LEFT JOIN odr_clientes c ON f.idCliente = c.id
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
$rsClients = mysqli_query($bd, "SELECT id, nombre FROM odr_clientes ORDER BY nombre");
if ($rsClients) {
    while ($row = mysqli_fetch_assoc($rsClients)) {
        $clientes[] = $row;
    }
}

// Estadísticas
$stats = ['total' => 0, 'pendents' => 0, 'pagades' => 0, 'import_mes' => 0];

$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM odr_facturas");
if ($rsStat) $stats['total'] = mysqli_fetch_assoc($rsStat)['total'];

$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM odr_facturas WHERE estado = 'pendent'");
if ($rsStat) $stats['pendents'] = mysqli_fetch_assoc($rsStat)['total'];

$rsStat = mysqli_query($bd, "SELECT COUNT(*) as total FROM odr_facturas WHERE estado = 'pagada'");
if ($rsStat) $stats['pagades'] = mysqli_fetch_assoc($rsStat)['total'];

$mesActual = date('Y-m');
$rsStat = mysqli_query($bd, "SELECT COALESCE(SUM(lf.cantidad * lf.precio_unidad * (1 + lf.iva/100)), 0) as total
    FROM odr_facturas f JOIN odr_linea_factura lf ON lf.idFactura = f.id
    WHERE f.fecha LIKE '$mesActual%'");
if ($rsStat) $stats['import_mes'] = mysqli_fetch_assoc($rsStat)['total'];

// Flash messages
$msg = $_GET['msg'] ?? '';
$flashMessages = [
    'created' => ['type' => 'success', 'text' => 'Factura creada correctament.'],
    'updated' => ['type' => 'success', 'text' => 'Factura actualitzada correctament.'],
    'deleted' => ['type' => 'success', 'text' => 'Factura eliminada correctament.'],
    'paid'    => ['type' => 'success', 'text' => 'Factura marcada com a pagada.'],
    'error'   => ['type' => 'error', 'text' => 'S\'ha produit un error.'],
];

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<?php if (isset($flashMessages[$msg])): ?>
<div class="alert alert-<?php echo $flashMessages[$msg]['type']; ?>" style="margin-bottom: 1rem;">
    <?php echo e($flashMessages[$msg]['text']); ?>
</div>
<?php endif; ?>

<!-- Estadistiques -->
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

<!-- Accions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div style="color: var(--gray-500); font-size: 0.875rem;">
        <?php echo number_format($totalRows); ?> factures trobades
    </div>
    <a href="<?php echo getAdminUrl('modules/odr_facturas/crear.php'); ?>" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px; margin-right: 0.25rem; vertical-align: -3px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nova Factura
    </a>
</div>

<!-- Filtres -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" style="display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 150px;">
                <label class="filter-label">Cercar</label>
                <input type="text" name="q" class="form-input" placeholder="Nom client o num. factura..." value="<?php echo e($search); ?>">
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
                    <option value="pagada" <?php echo $filterEstado === 'pagada' ? 'selected' : ''; ?>>Pagada</option>
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
                <a href="<?php echo getAdminUrl('modules/odr_facturas/'); ?>" class="btn btn-secondary btn-sm">Netejar</a>
            </div>
        </form>
    </div>
</div>

<!-- Taula -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 120px;">Numero</th>
                    <th>Client</th>
                    <th style="width: 100px;">Data</th>
                    <th style="width: 100px;">Venciment</th>
                    <th style="width: 110px; text-align: right;">Import</th>
                    <th style="width: 90px;">Estat</th>
                    <th style="width: 210px;">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($facturas)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--gray-400);">
                        No s'han trobat factures.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($facturas as $f): ?>
                <?php
                    $numFactura = $f['numero'] ?: 'FV-' . str_pad($f['id'], 4, '0', STR_PAD_LEFT);
                    $isPagada = ($f['estado'] === 'pagada');
                ?>
                <tr>
                    <td><strong style="font-family: monospace;"><?php echo e($numFactura); ?></strong></td>
                    <td>
                        <?php if ($f['cliente_nombre']): ?>
                            <?php echo e($f['cliente_nombre']); ?>
                        <?php else: ?>
                            <span style="color: var(--gray-400);">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $f['fecha'] ? date('d/m/Y', strtotime($f['fecha'])) : '-'; ?></td>
                    <td>
                        <?php if ($f['fecha_vencimiento'] && $f['fecha_vencimiento'] !== '0000-00-00'): ?>
                            <?php echo date('d/m/Y', strtotime($f['fecha_vencimiento'])); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right; font-weight: 600;">
                        <?php echo number_format($f['total_iva'] ?? 0, 2, ',', '.'); ?> &euro;
                    </td>
                    <td>
                        <?php if ($isPagada): ?>
                            <span class="badge badge-success">Pagada</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pendent</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.25rem;">
                            <a href="<?php echo getAdminUrl('modules/odr_facturas/editar.php?id=' . $f['id']); ?>" class="btn btn-sm btn-secondary" title="Editar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="veureFactura(<?php echo $f['id']; ?>)" title="Veure detall">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                            <a href="<?php echo getAdminUrl('modules/odr_facturas/pdf.php?id=' . $f['id']); ?>" class="btn btn-sm btn-secondary" target="_blank" title="PDF">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="imprimirFactura(<?php echo $f['id']; ?>)" title="Imprimir">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="confirmDuplicate(<?php echo $f['id']; ?>, <?php echo htmlspecialchars(json_encode($f['numero'] ?: '#' . $f['id']), ENT_QUOTES, 'UTF-8'); ?>)" title="Duplicar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                            <?php if (!$isPagada): ?>
                            <button type="button" class="btn btn-sm btn-primary" onclick="marcarPagada(<?php echo $f['id']; ?>)" title="Marcar pagada">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
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

<!-- Paginacio -->
<?php if ($totalPages > 1): ?>
<div class="pagination" style="margin-top: 1rem;">
    <?php
    $queryParams = $_GET;
    unset($queryParams['page']);
    $baseUrl = getAdminUrl('modules/odr_facturas/') . '?' . http_build_query($queryParams);
    ?>
    <?php if ($page > 1): ?>
    <a href="<?php echo $baseUrl . '&page=' . ($page - 1); ?>" class="btn btn-sm btn-secondary">&laquo; Anterior</a>
    <?php endif; ?>
    <span style="padding: 0.5rem; color: var(--gray-500); font-size: 0.875rem;">
        Pagina <?php echo $page; ?> de <?php echo $totalPages; ?>
    </span>
    <?php if ($page < $totalPages): ?>
    <a href="<?php echo $baseUrl . '&page=' . ($page + 1); ?>" class="btn btn-sm btn-secondary">Seguent &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Hidden iframe for printing -->
<iframe id="printFrame" name="printFrame" style="display: none;"></iframe>

<!-- Modal Detall -->
<div id="detailModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 750px;">
        <div class="modal-header">
            <h3 class="modal-title">Detall de la Factura</h3>
            <button type="button" class="modal-close" onclick="closeModal('detailModal')">&times;</button>
        </div>
        <div class="modal-body" id="detailContent" style="max-height: 70vh; overflow-y: auto;">
            <p style="text-align: center; color: var(--gray-400);">Carregant...</p>
        </div>
        <div class="modal-footer" id="detailFooter" style="display: none;">
            <a id="detailPdfLink" href="#" target="_blank" class="btn btn-secondary" style="background: #0066FF; color: #fff; border-color: #0066FF;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 4px; vertical-align: -2px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                PDF
            </a>
            <button type="button" class="btn btn-secondary" onclick="imprimirDesdeModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 4px; vertical-align: -2px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </button>
            <button type="button" class="btn btn-secondary" onclick="closeModal('detailModal')">Tancar</button>
        </div>
    </div>
</div>

<!-- Modal Pagar -->
<div id="payModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">Marcar com a Pagada</h3>
            <button type="button" class="modal-close" onclick="closeModal('payModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo getAdminUrl('modules/odr_facturas/pagar.php'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="id" id="payFacturaId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Data de Pagament</label>
                    <input type="date" name="fecha_pago" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('payModal')">Cancel&middot;lar</button>
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Duplicar -->
<div id="duplicateModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 440px;">
        <div class="modal-header">
            <h3 class="modal-title">Duplicar Factura</h3>
            <button type="button" class="modal-close" onclick="closeModal('duplicateModal')">&times;</button>
        </div>
        <form method="POST" action="<?php echo getAdminUrl('modules/odr_facturas/duplicar.php'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="id" id="duplicateFacturaId">
            <div class="modal-body">
                <p>Es crear&agrave; una c&ograve;pia de la factura <strong id="duplicateFacturaNum"></strong> amb el mateix client i les mateixes l&iacute;nies.</p>
                <p style="color: var(--gray-500); font-size: 0.875rem; margin-bottom: 0;">
                    La c&ograve;pia tindr&agrave; un n&uacute;mero nou de la s&egrave;rie INF2XXXX, la data d'avui i estat pendent. S'obrir&agrave; per editar-la.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('duplicateModal')">Cancel&middot;lar</button>
                <button type="submit" class="btn btn-primary">Duplicar</button>
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
        <form method="POST" action="<?php echo getAdminUrl('modules/odr_facturas/eliminar.php'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="id" id="deleteFacturaId">
            <div class="modal-body">
                <p>Estas segur que vols eliminar aquesta factura? Aquesta accio no es pot desfer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel&middot;lar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>

<script>
var currentFacturaId = null;
var detallBaseUrl = '<?php echo getAdminUrl("modules/odr_facturas/detall_ajax.php"); ?>';
var pdfBaseUrl = '<?php echo getAdminUrl("modules/odr_facturas/pdf.php"); ?>';

function veureFactura(id) {
    currentFacturaId = id;
    var content = document.getElementById('detailContent');
    var footer = document.getElementById('detailFooter');
    content.innerHTML = '<p style="text-align: center; color: var(--gray-400); padding: 2rem;">Carregant...</p>';
    footer.style.display = 'none';
    document.getElementById('detailModal').style.display = 'flex';

    fetch(detallBaseUrl + '?id=' + id)
        .then(function(resp) {
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            return resp.text();
        })
        .then(function(html) {
            if (!html || html.trim() === '') throw new Error('Resposta buida');
            content.innerHTML = html;
            document.getElementById('detailPdfLink').href = pdfBaseUrl + '?id=' + id;
            footer.style.display = 'flex';
        })
        .catch(function(err) {
            content.innerHTML = '<p style="text-align: center; color: #cc0000; padding: 2rem;">Error al carregar la factura: ' + err.message + '</p>';
        });
}

function imprimirFactura(id) {
    var iframe = document.getElementById('printFrame');
    iframe.src = detallBaseUrl + '?id=' + id + '&print=1';
    iframe.onload = function() {
        try {
            iframe.contentWindow.print();
        } catch(e) {
            window.open(detallBaseUrl + '?id=' + id, '_blank');
        }
    };
}

function imprimirDesdeModal() {
    if (currentFacturaId) {
        imprimirFactura(currentFacturaId);
    }
}

function marcarPagada(id) {
    document.getElementById('payFacturaId').value = id;
    document.getElementById('payModal').style.display = 'flex';
}

function confirmDuplicate(id, numero) {
    document.getElementById('duplicateFacturaId').value = id;
    document.getElementById('duplicateFacturaNum').textContent = numero;
    document.getElementById('duplicateModal').style.display = 'flex';
}

function confirmDelete(id) {
    document.getElementById('deleteFacturaId').value = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

document.querySelectorAll('.modal-overlay').forEach(function(modal) {
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
.form-group { margin-bottom: 1rem; }
.badge { display: inline-block; padding: 0.25rem 0.625rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-success { background: #d1fae5; color: #065f46; }
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
