<?php
/**
 * El Celler Nou - Mòdul Clients - Llistat
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Clients';

// Parámetros de búsqueda y paginación
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$tipusClient = isset($_GET['tipus']) ? $_GET['tipus'] : 'tots';
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$porPagina = 25;
$offset = ($pagina - 1) * $porPagina;

// Construir consulta
$where = [];

if ($tipusClient === 'professional') {
    $where[] = "c.profesional = 1";
} elseif ($tipusClient === 'particular') {
    $where[] = "c.profesional = 0";
}

if ($buscar !== '') {
    $buscarSafe = mysqli_real_escape_string($bd, $buscar);
    $where[] = "(c.nombre LIKE '%$buscarSafe%' OR c.nombre_fiscal LIKE '%$buscarSafe%' OR c.nif LIKE '%$buscarSafe%' OR c.email LIKE '%$buscarSafe%' OR c.telefono LIKE '%$buscarSafe%' OR c.poblacion LIKE '%$buscarSafe%')";
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Contar total
$sqlCount = "SELECT COUNT(*) as total FROM clientes c $whereClause";
$resultCount = mysqli_query($bd, $sqlCount);
$totalClients = $resultCount ? mysqli_fetch_assoc($resultCount)['total'] : 0;
$totalPaginas = ceil($totalClients / $porPagina);

// Estadísticas
$stats = [
    'total' => 0,
    'professionals' => 0,
    'particulars' => 0,
    'ventas_mes' => 0
];

$result = mysqli_query($bd, "SELECT COUNT(*) as total FROM clientes");
if ($result) { $row = mysqli_fetch_assoc($result); $stats['total'] = $row['total']; }

$result = mysqli_query($bd, "SELECT COUNT(*) as total FROM clientes WHERE profesional = 1");
if ($result) { $row = mysqli_fetch_assoc($result); $stats['professionals'] = $row['total']; }

$stats['particulars'] = $stats['total'] - $stats['professionals'];

$firstDayOfMonth = date('Y-m-01');
$result = mysqli_query($bd, "SELECT COALESCE(SUM(lv.cantidad * lv.precio_unidad), 0) as total FROM linea_venta lv INNER JOIN ventas v ON lv.idVenta = v.id WHERE v.fecha >= '$firstDayOfMonth' AND v.idCliente > 0");
if ($result) { $row = mysqli_fetch_assoc($result); $stats['ventas_mes'] = $row['total']; }

// Obtener clientes
$clientes = [];
$sql = "SELECT c.id, c.nombre, c.nombre_fiscal, c.telefono, c.email, c.nif, c.poblacion, c.profesional,
               fp.nombre as forma_pago
        FROM clientes c
        LEFT JOIN formas_pago fp ON c.idFormaPago = fp.id
        $whereClause
        ORDER BY c.nombre ASC
        LIMIT $offset, $porPagina";

$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $clientes[] = $row;
    }
}

// Mensajes flash
$mensaje = '';
$tipoMensaje = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':
            $mensaje = 'Client creat correctament.';
            $tipoMensaje = 'success';
            break;
        case 'updated':
            $mensaje = 'Client actualitzat correctament.';
            $tipoMensaje = 'success';
            break;
        case 'deleted':
            $mensaje = 'Client eliminat correctament.';
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
            <div class="stat-label">Total Clients</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['professionals']); ?></div>
            <div class="stat-label">Professionals</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['particulars']); ?></div>
            <div class="stat-label">Particulars</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['ventas_mes'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Vendes del Mes</div>
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
                    <input type="text" name="buscar" class="form-input" placeholder="Nom, NIF, email, telèfon, població..."
                           value="<?php echo e($buscar); ?>">
                </div>
                <div style="min-width: 150px;">
                    <label class="form-label">Tipus</label>
                    <select name="tipus" class="form-input">
                        <option value="tots" <?php echo $tipusClient === 'tots' ? 'selected' : ''; ?>>Tots</option>
                        <option value="professional" <?php echo $tipusClient === 'professional' ? 'selected' : ''; ?>>Professional</option>
                        <option value="particular" <?php echo $tipusClient === 'particular' ? 'selected' : ''; ?>>Particular</option>
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
                    <a href="<?php echo getAdminUrl('modules/clients/'); ?>" class="btn btn-outline">Netejar</a>
                </div>
                <div style="margin-left: auto;">
                    <a href="<?php echo getAdminUrl('modules/clients/crear.php'); ?>" class="btn btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nou Client
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
            Clients
            <span class="badge badge-info" style="margin-left: 0.5rem;"><?php echo number_format($totalClients); ?></span>
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($clientes)): ?>
        <div class="empty-state" style="padding: 3rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; color: var(--gray-400);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <h3 class="empty-state-title">No s'han trobat clients</h3>
            <p class="empty-state-text">Prova amb altres filtres o crea un nou client.</p>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Nom</th>
                        <th>NIF</th>
                        <th>Telèfon</th>
                        <th>Email</th>
                        <th>Població</th>
                        <th style="text-align: center;">Tipus</th>
                        <th style="width: 100px;">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $client): ?>
                    <tr>
                        <td><strong>#<?php echo $client['id']; ?></strong></td>
                        <td>
                            <a href="<?php echo getAdminUrl('modules/clients/modificar.php?id=' . $client['id']); ?>">
                                <strong><?php echo e($client['nombre']); ?></strong>
                            </a>
                            <?php if ($client['nombre_fiscal'] && $client['nombre_fiscal'] !== $client['nombre']): ?>
                            <br><small style="color: var(--gray-500);"><?php echo e($client['nombre_fiscal']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($client['nif'] ?: '-'); ?></td>
                        <td><?php echo e($client['telefono'] ?: '-'); ?></td>
                        <td>
                            <?php if ($client['email']): ?>
                            <a href="mailto:<?php echo e($client['email']); ?>"><?php echo e($client['email']); ?></a>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($client['poblacion'] ?: '-'); ?></td>
                        <td style="text-align: center;">
                            <span class="badge <?php echo $client['profesional'] ? 'badge-success' : 'badge-info'; ?>">
                                <?php echo $client['profesional'] ? 'Professional' : 'Particular'; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="<?php echo getAdminUrl('modules/clients/modificar.php?id=' . $client['id']); ?>"
                                   class="btn btn-sm btn-secondary" title="Editar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                        onclick="confirmarEliminar(<?php echo $client['id']; ?>, '<?php echo e(addslashes($client['nombre'])); ?>')">
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
            Mostrant <?php echo $offset + 1; ?> - <?php echo min($offset + $porPagina, $totalClients); ?> de <?php echo $totalClients; ?>
        </div>
        <div class="pagination">
            <?php
            $queryParams = $_GET;
            unset($queryParams['pagina']);
            $baseUrl = getAdminUrl('modules/clients/') . '?' . http_build_query($queryParams);
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
        <div class="modal-header">
            <h3>Confirmar eliminació</h3>
        </div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar el client <strong id="deleteClientName"></strong>?</p>
            <p style="color: var(--gray-500); font-size: 0.875rem;">Aquesta acció no es pot desfer. S'eliminaran també els preus especials associats.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form id="deleteForm" method="POST" action="<?php echo getAdminUrl('modules/clients/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="id" id="deleteClientId" value="">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
}
.modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 400px;
    margin: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}
.modal-header h3 {
    margin: 0;
    font-size: 1.125rem;
}
.modal-body {
    padding: 1.5rem;
}
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
.pagination {
    display: flex;
    gap: 0.25rem;
}
.alert-success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
    padding: 1rem;
    border-radius: 8px;
}
.alert-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    padding: 1rem;
    border-radius: 8px;
}
.form-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--gray-600);
    margin-bottom: 0.25rem;
    text-transform: uppercase;
}
.form-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    transition: all 0.2s;
}
.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1);
}
.card-footer {
    padding: 1rem 1.5rem;
    background: var(--gray-50);
    border-top: 1px solid var(--gray-200);
}
</style>

<script>
function confirmarEliminar(id, nombre) {
    document.getElementById('deleteClientId').value = id;
    document.getElementById('deleteClientName').textContent = nombre;
    document.getElementById('deleteModal').style.display = 'flex';
}

function tancarModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        tancarModal();
    }
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
