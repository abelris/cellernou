<?php
/**
 * El Celler Nou - Mòdul Factures de Compra - Llistat
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Factures de Compra';

// Procesar cambio de estado via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['canvi_estat'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $idFactura = (int)($_POST['idFactura'] ?? 0);
        $idEstado = (int)($_POST['idEstado'] ?? 0);
        if ($idFactura > 0) {
            $stmt = mysqli_prepare($bd, "UPDATE facturas_compra SET idEstado = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $idEstado, $idFactura);
            mysqli_stmt_execute($stmt);
            logActivity('factura_compra_estat', "Factura compra #$idFactura - Estat canviat a $idEstado");
        }
    }
    if (!empty($_POST['ajax'])) {
        echo 'ok';
        exit;
    }
}

// Estadísticas
$stats = ['total' => 0, 'pendents' => 0, 'pagades' => 0, 'mes_import' => 0];
$firstDayOfMonth = date('Y-m-01');

$sql = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN idEstado = 0 THEN 1 ELSE 0 END) as pendents,
    SUM(CASE WHEN idEstado > 0 THEN 1 ELSE 0 END) as pagades,
    COALESCE(SUM(CASE WHEN fecha >= '$firstDayOfMonth' THEN total ELSE 0 END), 0) as mes_import
FROM facturas_compra";
$result = @mysqli_query($bd, $sql);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $stats = array_merge($stats, $row);
}

// Obtener estados de factura
$estadosFactura = [];
$result = mysqli_query($bd, "SELECT * FROM estados_facturas ORDER BY id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $estadosFactura[] = $row;
    }
}

// Parámetros de búsqueda y paginación
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$proveedor = isset($_GET['proveedor']) ? (int)$_GET['proveedor'] : 0;
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'tots';
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$porPagina = 25;
$offset = ($pagina - 1) * $porPagina;

// Construir consulta
$where = [];

if ($estado === 'pendents') {
    $where[] = "f.idEstado = 0";
} elseif ($estado === 'pagades') {
    $where[] = "f.idEstado > 0";
}

if ($buscar !== '') {
    $buscarSafe = mysqli_real_escape_string($bd, $buscar);
    $where[] = "(f.numFactura LIKE '%$buscarSafe%' OR p.nombre LIKE '%$buscarSafe%')";
}

if ($proveedor > 0) {
    $where[] = "f.idProveedor = $proveedor";
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Contar total
$sqlCount = "SELECT COUNT(*) as total FROM facturas_compra f LEFT JOIN proveedores p ON f.idProveedor = p.id $whereClause";
$resultCount = mysqli_query($bd, $sqlCount);
$totalFacturas = mysqli_fetch_assoc($resultCount)['total'];
$totalPaginas = ceil($totalFacturas / $porPagina);

// Obtener facturas
$facturas = [];
$sql = "SELECT f.*, p.nombre as proveedor
        FROM facturas_compra f
        LEFT JOIN proveedores p ON f.idProveedor = p.id
        $whereClause
        ORDER BY f.fecha DESC, f.id DESC
        LIMIT $offset, $porPagina";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $facturas[] = $row;
    }
}

// Obtener proveedores para filtro
$proveedores = [];
$result = mysqli_query($bd, "SELECT id, nombre FROM proveedores ORDER BY nombre");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $proveedores[] = $row;
    }
}

// Mensajes flash
$mensaje = '';
$tipoMensaje = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':
            $mensaje = 'Factura creada correctament.';
            $tipoMensaje = 'success';
            break;
        case 'updated':
            $mensaje = 'Factura actualitzada correctament.';
            $tipoMensaje = 'success';
            break;
        case 'deleted':
            $mensaje = 'Factura eliminada correctament.';
            $tipoMensaje = 'success';
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
            <div class="stat-label">Pagades / Tancades</div>
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

<!-- Filtros y acciones -->
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
                <div style="min-width: 140px;">
                    <label class="form-label">Estat</label>
                    <select name="estado" class="form-input">
                        <option value="tots" <?php echo $estado === 'tots' ? 'selected' : ''; ?>>Tots</option>
                        <option value="pendents" <?php echo $estado === 'pendents' ? 'selected' : ''; ?>>Pendents</option>
                        <option value="pagades" <?php echo $estado === 'pagades' ? 'selected' : ''; ?>>Pagades</option>
                    </select>
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
                    <a href="<?php echo getAdminUrl('modules/factures_compra/'); ?>" class="btn btn-outline">Netejar</a>
                </div>
                <div style="margin-left: auto;">
                    <a href="<?php echo getAdminUrl('modules/factures_compra/crear.php'); ?>" class="btn btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nova Factura
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resultados -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Factures de Compra
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
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: center; min-width: 160px;">Estat</th>
                        <th style="width: 120px;">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facturas as $factura): ?>
                    <tr>
                        <td>
                            <a href="<?php echo getAdminUrl('modules/factures_compra/veure.php?id=' . $factura['id']); ?>">
                                <strong>#<?php echo $factura['id']; ?></strong>
                            </a>
                        </td>
                        <td><strong><?php echo e($factura['numFactura'] ?: '-'); ?></strong></td>
                        <td><?php echo e($factura['proveedor'] ?? '-'); ?></td>
                        <td><?php echo $factura['fecha'] ? date('d/m/Y', strtotime($factura['fecha'])) : '-'; ?></td>
                        <td>
                            <?php
                            if ($factura['fecha_vencimiento']) {
                                $vencimiento = strtotime($factura['fecha_vencimiento']);
                                $isVencida = $vencimiento < time() && $factura['idEstado'] == 0;
                                echo '<span' . ($isVencida ? ' style="color: var(--danger, #dc2626); font-weight: 600;"' : '') . '>';
                                echo date('d/m/Y', $vencimiento);
                                echo '</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td style="text-align: right;"><strong><?php echo number_format($factura['total'] ?? 0, 2, ',', '.'); ?> &euro;</strong></td>
                        <td style="text-align: center;">
                            <select class="estat-select <?php echo $factura['idEstado'] == 0 ? 'estat-pendent' : 'estat-pagada'; ?>"
                                    onchange="canviarEstat(<?php echo $factura['id']; ?>, this.value, this)"
                                    data-id="<?php echo $factura['id']; ?>">
                                <?php foreach ($estadosFactura as $ef): ?>
                                <option value="<?php echo $ef['id']; ?>" <?php echo $ef['id'] == $factura['idEstado'] ? 'selected' : ''; ?>>
                                    <?php echo e($ef['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.25rem;">
                                <a href="<?php echo getAdminUrl('modules/factures_compra/veure.php?id=' . $factura['id']); ?>"
                                   class="btn btn-sm btn-secondary" title="Veure detall">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="<?php echo getAdminUrl('modules/factures_compra/modificar.php?id=' . $factura['id']); ?>"
                                   class="btn btn-sm btn-secondary" title="Editar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                        onclick="confirmarEliminar(<?php echo $factura['id']; ?>, '<?php echo e(addslashes($factura['numFactura'] ?: '#' . $factura['id'])); ?>')">
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
            $baseUrl = getAdminUrl('modules/factures_compra/') . '?' . http_build_query($queryParams);
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
            <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $pagina + 1; ?>" class="btn btn-sm btn-outline">Següent &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmación de eliminación -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModal()"></div>
    <div class="modal-content">
        <div class="modal-header"><h3>Confirmar eliminació</h3></div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar la factura <strong id="deleteFacturaName"></strong>?</p>
            <p style="color: var(--gray-500); font-size: 0.875rem;">Aquesta acció no es pot desfer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form id="deleteForm" method="POST" action="<?php echo getAdminUrl('modules/factures_compra/eliminar.php'); ?>" style="display: inline;">
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
.form-input { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; transition: all 0.2s; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
.card-footer { padding: 1rem 1.5rem; background: var(--gray-50); border-top: 1px solid var(--gray-200); }
.pagination { display: flex; gap: 0.25rem; }
.estat-select { padding: 0.25rem 0.5rem; font-size: 0.8125rem; border-radius: 6px; border: 2px solid; cursor: pointer; font-weight: 600; transition: all 0.2s; }
.estat-pendent { border-color: #f59e0b; background: #fef3c7; color: #92400e; }
.estat-pagada { border-color: #10b981; background: #d1fae5; color: #065f46; }
.modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-backdrop { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); }
.modal-content { position: relative; background: white; border-radius: 12px; width: 100%; max-width: 400px; margin: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-200); }
.modal-header h3 { margin: 0; font-size: 1.125rem; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 0.75rem; }
</style>

<script>
const csrfToken = '<?php echo $csrfToken; ?>';

function canviarEstat(idFactura, idEstado, selectEl) {
    // Actualizar estilo visual
    selectEl.className = 'estat-select ' + (idEstado == 0 ? 'estat-pendent' : 'estat-pagada');

    // Enviar cambio al servidor
    const formData = new FormData();
    formData.append('canvi_estat', '1');
    formData.append('csrf_token', csrfToken);
    formData.append('idFactura', idFactura);
    formData.append('idEstado', idEstado);
    formData.append('ajax', '1');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).catch(err => console.error('Error canviant estat:', err));
}

function confirmarEliminar(id, ref) {
    document.getElementById('deleteFacturaId').value = id;
    document.getElementById('deleteFacturaName').textContent = ref;
    document.getElementById('deleteModal').style.display = 'flex';
}

function tancarModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tancarModal();
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
