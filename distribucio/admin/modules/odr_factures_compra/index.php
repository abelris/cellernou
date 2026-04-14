<?php
/**
 * OnDigitalRis - Mòdul Factures de Compra - Llistat
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

$pageTitle = 'ODR - Factures Compra';

// --- Marcar com a pagada via POST (modal) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_pagada'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $idFactura = (int)($_POST['idFactura'] ?? 0);
        $fechaPago = trim($_POST['fecha_pago'] ?? date('Y-m-d'));
        if ($idFactura > 0) {
            $fechaPagoSafe = mysqli_real_escape_string($bd, $fechaPago);
            mysqli_query($bd, "UPDATE odr_facturas_compra SET estado = 'pagada', fecha_pago = '$fechaPagoSafe', updated_at = NOW() WHERE id = $idFactura");
            logActivity('odr_factura_compra_pagar', "ODR Factura compra #$idFactura marcada com a pagada ($fechaPago)");
            header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=paid'));
            exit;
        }
    }
}

// --- Estadístiques ---
$stats = ['total' => 0, 'pendents' => 0, 'pagades' => 0, 'mes_import' => 0];
$firstDayOfMonth = date('Y-m-01');

$sql = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN fc.estado = 'pendent' THEN 1 ELSE 0 END) as pendents,
    SUM(CASE WHEN fc.estado = 'pagada' THEN 1 ELSE 0 END) as pagades
FROM odr_facturas_compra fc";
$result = @mysqli_query($bd, $sql);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $stats = array_merge($stats, $row);
}

// Import del mes (suma de línies)
$sql2 = "SELECT COALESCE(SUM(l.cantidad * l.precio_unidad * (1 + l.iva / 100)), 0) as mes_import
         FROM odr_linea_factura_compra l
         INNER JOIN odr_facturas_compra fc ON l.idFactura = fc.id
         WHERE fc.fecha >= '$firstDayOfMonth'";
$result2 = @mysqli_query($bd, $sql2);
if ($result2 && $row2 = mysqli_fetch_assoc($result2)) {
    $stats['mes_import'] = $row2['mes_import'];
}

// --- Paràmetres de cerca i paginació ---
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$proveedor = isset($_GET['proveedor']) ? (int)$_GET['proveedor'] : 0;
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'tots';
$fechaDesde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
$fechaHasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$porPagina = 25;
$offset = ($pagina - 1) * $porPagina;

// Construir consulta
$where = [];

if ($estado === 'pendent') {
    $where[] = "fc.estado = 'pendent'";
} elseif ($estado === 'pagada') {
    $where[] = "fc.estado = 'pagada'";
}

if ($buscar !== '') {
    $buscarSafe = mysqli_real_escape_string($bd, $buscar);
    $where[] = "(fc.numero_factura LIKE '%$buscarSafe%' OR p.nombre LIKE '%$buscarSafe%')";
}

if ($proveedor > 0) {
    $where[] = "fc.idProveedor = $proveedor";
}

if ($fechaDesde !== '') {
    $fdSafe = mysqli_real_escape_string($bd, $fechaDesde);
    $where[] = "fc.fecha >= '$fdSafe'";
}
if ($fechaHasta !== '') {
    $fhSafe = mysqli_real_escape_string($bd, $fechaHasta);
    $where[] = "fc.fecha <= '$fhSafe'";
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Contar total
$sqlCount = "SELECT COUNT(*) as total FROM odr_facturas_compra fc LEFT JOIN odr_proveedores p ON fc.idProveedor = p.id $whereClause";
$resultCount = mysqli_query($bd, $sqlCount);
$totalFacturas = $resultCount ? (int)mysqli_fetch_assoc($resultCount)['total'] : 0;
$totalPaginas = ceil($totalFacturas / $porPagina);

// Obtenir factures amb import calculat des de línies
$facturas = [];
$sql = "SELECT fc.*,
        p.nombre as proveedor,
        COALESCE(SUM(l.cantidad * l.precio_unidad * (1 + l.iva / 100)), 0) as import_total
        FROM odr_facturas_compra fc
        LEFT JOIN odr_proveedores p ON fc.idProveedor = p.id
        LEFT JOIN odr_linea_factura_compra l ON l.idFactura = fc.id
        $whereClause
        GROUP BY fc.id
        ORDER BY fc.fecha DESC, fc.id DESC
        LIMIT $offset, $porPagina";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $facturas[] = $row;
    }
}

// Obtenir proveïdors per al filtre
$proveedores = [];
$result = mysqli_query($bd, "SELECT id, nombre FROM odr_proveedores ORDER BY nombre");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $proveedores[] = $row;
    }
}

// Missatges flash
$mensaje = '';
$tipoMensaje = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':
            $mensaje = 'Factura de compra creada correctament.';
            $tipoMensaje = 'success';
            break;
        case 'updated':
            $mensaje = 'Factura de compra actualitzada correctament.';
            $tipoMensaje = 'success';
            break;
        case 'deleted':
            $mensaje = 'Factura de compra eliminada correctament.';
            $tipoMensaje = 'success';
            break;
        case 'paid':
            $mensaje = 'Factura marcada com a pagada correctament.';
            $tipoMensaje = 'success';
            break;
        case 'error':
            $mensaje = 'Hi ha hagut un error. Torna a intentar-ho.';
            $tipoMensaje = 'error';
            break;
    }
}

$csrfToken = generateCsrfToken();

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipoMensaje; ?>" style="margin-bottom: 1rem;">
    <?php echo e($mensaje); ?>
</div>
<?php endif; ?>

<!-- Stats Grid -->
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
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
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
            <div class="stat-value"><?php echo number_format($stats['mes_import'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Import del Mes</div>
        </div>
    </div>
</div>

<!-- Filtres i accions -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" action="">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Cercar</label>
                    <input type="text" name="buscar" class="form-input" placeholder="Núm. factura o proveïdor..."
                           value="<?php echo e($buscar); ?>">
                </div>
                <div style="min-width: 180px;">
                    <label class="form-label">Proveïdor</label>
                    <select name="proveedor" class="form-input">
                        <option value="0">Tots els proveïdors</option>
                        <?php foreach ($proveedores as $prov): ?>
                        <option value="<?php echo $prov['id']; ?>" <?php echo $proveedor == $prov['id'] ? 'selected' : ''; ?>>
                            <?php echo e($prov['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width: 130px;">
                    <label class="form-label">Estat</label>
                    <select name="estado" class="form-input">
                        <option value="tots" <?php echo $estado === 'tots' ? 'selected' : ''; ?>>Tots</option>
                        <option value="pendent" <?php echo $estado === 'pendent' ? 'selected' : ''; ?>>Pendents</option>
                        <option value="pagada" <?php echo $estado === 'pagada' ? 'selected' : ''; ?>>Pagades</option>
                    </select>
                </div>
                <div style="min-width: 140px;">
                    <label class="form-label">Des de</label>
                    <input type="date" name="fecha_desde" class="form-input" value="<?php echo e($fechaDesde); ?>">
                </div>
                <div style="min-width: 140px;">
                    <label class="form-label">Fins a</label>
                    <input type="date" name="fecha_hasta" class="form-input" value="<?php echo e($fechaHasta); ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-secondary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filtrar
                    </button>
                </div>
                <div>
                    <a href="<?php echo getAdminUrl('modules/odr_factures_compra/'); ?>" class="btn btn-outline">Netejar</a>
                </div>
                <div style="margin-left: auto;">
                    <a href="<?php echo getAdminUrl('modules/odr_factures_compra/crear.php'); ?>" class="btn btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nova Factura Compra
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resultats -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Factures de Compra ODR
            <span class="badge badge-info" style="margin-left: 0.5rem;"><?php echo number_format($totalFacturas); ?></span>
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($facturas)): ?>
        <div class="empty-state" style="padding: 3rem; text-align: center;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; color: var(--gray-400);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 style="margin: 1rem 0 0.5rem; color: var(--gray-600);">No s'han trobat factures</h3>
            <p style="color: var(--gray-500);">Prova amb altres filtres o crea una nova factura.</p>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Núm. Factura</th>
                        <th>Proveïdor</th>
                        <th>Data</th>
                        <th>Venciment</th>
                        <th style="text-align: right;">Import</th>
                        <th style="text-align: center; min-width: 120px;">Estat</th>
                        <th style="width: 120px;">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facturas as $factura): ?>
                    <tr>
                        <td><strong>#<?php echo $factura['id']; ?></strong></td>
                        <td><strong><?php echo e($factura['numero_factura'] ?: '-'); ?></strong></td>
                        <td><?php echo e($factura['proveedor'] ?? '-'); ?></td>
                        <td><?php echo $factura['fecha'] ? date('d/m/Y', strtotime($factura['fecha'])) : '-'; ?></td>
                        <td>
                            <?php
                            if ($factura['fecha_vencimiento']) {
                                $vencimiento = strtotime($factura['fecha_vencimiento']);
                                $isVencida = $vencimiento < time() && $factura['estado'] === 'pendent';
                                echo '<span' . ($isVencida ? ' style="color: var(--danger, #dc2626); font-weight: 600;"' : '') . '>';
                                echo date('d/m/Y', $vencimiento);
                                echo '</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td style="text-align: right;">
                            <strong><?php echo number_format($factura['import_total'] ?? 0, 2, ',', '.'); ?> &euro;</strong>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($factura['estado'] === 'pendent'): ?>
                                <span class="badge badge-warning">Pendent</span>
                            <?php else: ?>
                                <span class="badge badge-success">Pagada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.25rem;">
                                <a href="<?php echo getAdminUrl('modules/odr_factures_compra/crear.php?id=' . $factura['id']); ?>"
                                   class="btn btn-sm btn-secondary" title="Editar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <?php if ($factura['estado'] === 'pendent'): ?>
                                <button type="button" class="btn btn-sm btn-success" title="Marcar com a pagada"
                                        onclick="obrirModalPagar(<?php echo $factura['id']; ?>, '<?php echo e(addslashes($factura['numero_factura'] ?: '#' . $factura['id'])); ?>')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                        onclick="confirmarEliminar(<?php echo $factura['id']; ?>, '<?php echo e(addslashes($factura['numero_factura'] ?: '#' . $factura['id'])); ?>')">
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
        <?php endif; ?>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
        <div style="color: var(--gray-500); font-size: 0.875rem;">
            Mostrant <?php echo $offset + 1; ?> - <?php echo min($offset + $porPagina, $totalFacturas); ?> de <?php echo $totalFacturas; ?>
        </div>
        <div class="pagination">
            <?php
            $queryParams = $_GET;
            unset($queryParams['pagina']);
            $baseUrl = getAdminUrl('modules/odr_factures_compra/') . '?' . http_build_query($queryParams);
            ?>
            <?php if ($pagina > 1): ?>
            <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $pagina - 1; ?>" class="btn btn-sm btn-outline">&laquo; Anterior</a>
            <?php endif; ?>
            <?php
            $inicio = max(1, $pagina - 2);
            $fin = min($totalPaginas, $pagina + 2);
            for ($i = $inicio; $i <= $fin; $i++):
            ?>
            <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $i; ?>"
               class="btn btn-sm <?php echo $i === $pagina ? 'btn-primary' : 'btn-outline'; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            <?php if ($pagina < $totalPaginas): ?>
            <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $pagina + 1; ?>" class="btn btn-sm btn-outline">Seguent &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal marcar com a pagada -->
<div id="payModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModals()"></div>
    <div class="modal-content">
        <div class="modal-header"><h3>Marcar com a pagada</h3></div>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="marcar_pagada" value="1">
            <input type="hidden" name="idFactura" id="payFacturaId" value="">
            <div class="modal-body">
                <p>Marcar la factura <strong id="payFacturaName"></strong> com a pagada?</p>
                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label" for="fecha_pago">Data de pagament</label>
                    <input type="date" id="fecha_pago" name="fecha_pago" class="form-input" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="tancarModals()">Cancel·lar</button>
                <button type="submit" class="btn btn-success">Marcar com a pagada</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmació d'eliminació -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModals()"></div>
    <div class="modal-content">
        <div class="modal-header"><h3>Confirmar eliminació</h3></div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar la factura <strong id="deleteFacturaName"></strong>?</p>
            <p style="color: var(--gray-500); font-size: 0.875rem;">Aquesta acció no es pot desfer. S'eliminaran totes les línies associades.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModals()">Cancel·lar</button>
            <form id="deleteForm" method="POST" action="<?php echo getAdminUrl('modules/odr_factures_compra/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="id" id="deleteFacturaId" value="">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<style>
.alert { padding: 1rem; border-radius: 8px; font-size: 0.875rem; }
.alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.form-label { display: block; font-size: 0.75rem; font-weight: 500; color: var(--gray-600); margin-bottom: 0.25rem; text-transform: uppercase; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; transition: all 0.2s; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
.card-footer { padding: 1rem 1.5rem; background: var(--gray-50); border-top: 1px solid var(--gray-200); }
.pagination { display: flex; gap: 0.25rem; }
.badge-warning { background: #fef3c7; color: #92400e; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.badge-success { background: #d1fae5; color: #065f46; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.btn-success { background: #059669; color: white; border: none; cursor: pointer; }
.btn-success:hover { background: #047857; }
.modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-backdrop { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); }
.modal-content { position: relative; background: white; border-radius: 12px; width: 100%; max-width: 450px; margin: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-200); }
.modal-header h3 { margin: 0; font-size: 1.125rem; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 0.75rem; }
.form-group { margin-bottom: 0; }
</style>

<script>
function obrirModalPagar(id, ref) {
    document.getElementById('payFacturaId').value = id;
    document.getElementById('payFacturaName').textContent = ref;
    document.getElementById('payModal').style.display = 'flex';
}

function confirmarEliminar(id, ref) {
    document.getElementById('deleteFacturaId').value = id;
    document.getElementById('deleteFacturaName').textContent = ref;
    document.getElementById('deleteModal').style.display = 'flex';
}

function tancarModals() {
    document.getElementById('payModal').style.display = 'none';
    document.getElementById('deleteModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tancarModals();
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
