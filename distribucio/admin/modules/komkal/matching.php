<?php
/**
 * El Celler Nou - Mòdul Komkal - Matching Interface
 */
$pageTitle = 'Komkal - Matching';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/config.php';
requireAuth();

$csrfToken = generateCsrfToken();

// Current table
$taula = (isset($_GET['taula']) && $_GET['taula'] === 'licores') ? 'licores' : 'vinos';
$taulaDb = 'komkal_' . $taula;

// Filters
$filtre = $_GET['filtre'] ?? 'tots';
$buscar = trim($_GET['buscar'] ?? '');
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = 50;
$offset = ($pagina - 1) * $porPagina;

// Stats for this table
$stats = ['total' => 0, 'vinculats' => 0, 'pendents' => 0, 'preu_diff' => 0, 'disponibles' => 0];

$r = mysqli_query($bd, "SELECT COUNT(*) as total FROM $taulaDb");
if ($r) $stats['total'] = (int)mysqli_fetch_assoc($r)['total'];

$r = mysqli_query($bd, "SELECT COUNT(*) as cnt FROM $taulaDb WHERE id_producto_match IS NOT NULL");
if ($r) $stats['vinculats'] = (int)mysqli_fetch_assoc($r)['cnt'];

$stats['pendents'] = $stats['total'] - $stats['vinculats'];

$r = mysqli_query($bd, "SELECT COUNT(*) as cnt FROM $taulaDb WHERE disponible = 1");
if ($r) $stats['disponibles'] = (int)mysqli_fetch_assoc($r)['cnt'];

$r = mysqli_query($bd, "SELECT COUNT(*) as cnt FROM $taulaDb k
    JOIN productos p ON k.id_producto_match = p.id
    WHERE k.id_producto_match IS NOT NULL
    AND k.precio_sin_iva IS NOT NULL
    AND ABS(k.precio_sin_iva - p.precio_medio) > 0.01");
if ($r) $stats['preu_diff'] = (int)mysqli_fetch_assoc($r)['cnt'];

// Build query
$where = [];
$buscarSafe = mysqli_real_escape_string($bd, $buscar);

if ($buscar !== '') {
    $where[] = "(k.nombre LIKE '%$buscarSafe%' OR k.codigo_komkal LIKE '%$buscarSafe%')";
}

switch ($filtre) {
    case 'vinculats':
        $where[] = "k.id_producto_match IS NOT NULL";
        break;
    case 'pendents':
        $where[] = "k.id_producto_match IS NULL";
        break;
    case 'disponibles':
        $where[] = "k.disponible = 1";
        break;
    case 'preu_diff':
        $where[] = "k.id_producto_match IS NOT NULL AND k.precio_sin_iva IS NOT NULL AND ABS(k.precio_sin_iva - p.precio_medio) > 0.01";
        break;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Need JOIN for preu_diff filter and for product data
$sql = "SELECT k.*, p.id as prod_id, p.nombre as prod_nombre, p.precio_medio as prod_precio, p.codigo as prod_codigo,
               (SELECT COUNT(*) FROM stock_producto sp WHERE sp.vendido = 0 AND sp.idProducto = p.id) as stock_actual
        FROM $taulaDb k
        LEFT JOIN productos p ON k.id_producto_match = p.id
        $whereClause
        ORDER BY k.nombre ASC
        LIMIT $offset, $porPagina";

$entries = [];
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $entries[] = $row;
    }
}

// Count total for pagination
$sqlCount = "SELECT COUNT(*) as total FROM $taulaDb k LEFT JOIN productos p ON k.id_producto_match = p.id $whereClause";
$resultCount = mysqli_query($bd, $sqlCount);
$totalEntries = $resultCount ? (int)mysqli_fetch_assoc($resultCount)['total'] : 0;
$totalPaginas = ceil($totalEntries / $porPagina);

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div style="margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
    <a href="<?php echo getAdminUrl('modules/komkal/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar
    </a>
    <div style="margin-left:auto; display:flex; gap:0.5rem;">
        <button type="button" class="btn btn-primary btn-sm" onclick="executarAutoMatch()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Executar Auto-Match
        </button>
        <button type="button" class="btn btn-warning btn-sm" onclick="actualitzarTotsPreus()" title="Actualitzar preus dels productes vinculats amb stock 0 i preu diferent">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Actualitzar preus
        </button>
        <button type="button" class="btn btn-sm" style="background:#059669;color:#fff;" onclick="crearTotsPendents()" title="Crear tots els productes Komkal no vinculats com a productes nous (vins +25%, licors +20%)">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Crear tots els pendents
        </button>
    </div>
</div>

<!-- Tabs -->
<div style="display:flex; gap:0; margin-bottom:1rem;">
    <a href="<?php echo getAdminUrl('modules/komkal/matching.php?taula=vinos'); ?>"
       class="btn <?php echo $taula === 'vinos' ? 'btn-primary' : 'btn-secondary'; ?>"
       style="border-radius:6px 0 0 6px;">
        Vins
    </a>
    <a href="<?php echo getAdminUrl('modules/komkal/matching.php?taula=licores'); ?>"
       class="btn <?php echo $taula === 'licores' ? 'btn-primary' : 'btn-secondary'; ?>"
       style="border-radius:0 6px 6px 0;">
        Licors
    </a>
</div>

<!-- Stats bar -->
<div style="display:flex; gap:1rem; margin-bottom:1rem; flex-wrap:wrap;">
    <div class="stat-card" style="flex:1; min-width:120px;">
        <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
        <div class="stat-label">Total</div>
    </div>
    <div class="stat-card" style="flex:1; min-width:120px;">
        <div class="stat-value" style="color:#16a34a;"><?php echo number_format($stats['vinculats']); ?></div>
        <div class="stat-label">Vinculats</div>
    </div>
    <div class="stat-card" style="flex:1; min-width:120px;">
        <div class="stat-value" style="color:#dc2626;"><?php echo number_format($stats['pendents']); ?></div>
        <div class="stat-label">Pendents</div>
    </div>
    <div class="stat-card" style="flex:1; min-width:120px;">
        <div class="stat-value" style="color:#f59e0b;"><?php echo number_format($stats['preu_diff']); ?></div>
        <div class="stat-label">Preu Diferent</div>
    </div>
    <div class="stat-card" style="flex:1; min-width:120px;">
        <div class="stat-value"><?php echo number_format($stats['disponibles']); ?></div>
        <div class="stat-label">Disponibles</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body" style="padding:0.75rem 1rem;">
        <form method="GET" action="" style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <input type="hidden" name="taula" value="<?php echo e($taula); ?>">
            <div style="display:flex; gap:0.25rem;">
                <?php
                $filtres = [
                    'tots' => 'Tots',
                    'vinculats' => 'Vinculats',
                    'pendents' => 'Pendents',
                    'disponibles' => 'Disponibles',
                    'preu_diff' => 'Preu Diferent'
                ];
                foreach ($filtres as $key => $label):
                ?>
                <button type="submit" name="filtre" value="<?php echo $key; ?>"
                        class="btn btn-sm <?php echo $filtre === $key ? 'btn-primary' : 'btn-outline'; ?>">
                    <?php echo $label; ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div style="flex:1; min-width:200px;">
                <input type="text" name="buscar" class="form-input" style="padding:0.4rem 0.75rem; font-size:0.875rem;"
                       placeholder="Cercar per nom o codi..." value="<?php echo e($buscar); ?>">
            </div>
            <button type="submit" class="btn btn-sm btn-secondary">Cercar</button>
            <?php if ($buscar !== '' || $filtre !== 'tots'): ?>
            <a href="<?php echo getAdminUrl('modules/komkal/matching.php?taula=' . $taula); ?>" class="btn btn-sm btn-outline">Netejar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Results table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?php echo $taula === 'vinos' ? 'Vins' : 'Licors'; ?> Komkal
            <span class="badge badge-info" style="margin-left:0.5rem;"><?php echo number_format($totalEntries); ?></span>
        </h3>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($entries)): ?>
        <div style="padding:3rem; text-align:center; color:var(--gray-500);">
            No s'han trobat registres.
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table" id="matchingTable">
                <thead>
                    <tr>
                        <th style="width:80px;">Codi</th>
                        <th>Nom Komkal</th>
                        <th style="text-align:right; width:90px;">Preu s/IVA</th>
                        <th style="text-align:center; width:70px;">Disp.</th>
                        <th>Producte Cellernou</th>
                        <th style="text-align:right; width:90px;">Preu Actual</th>
                        <th style="text-align:center; width:60px;">Stock</th>
                        <th style="text-align:right; width:90px;">Diferència</th>
                        <th style="width:180px;">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                    <tr id="row-<?php echo $entry['id']; ?>" class="<?php echo $entry['id_producto_match'] ? 'row-matched' : 'row-unmatched'; ?>">
                        <td><code><?php echo e($entry['codigo_komkal'] ?: '-'); ?></code></td>
                        <td>
                            <strong><?php echo e($entry['nombre']); ?></strong>
                            <?php if ($entry['match_tipo']): ?>
                            <br><small style="color:var(--gray-400);"><?php echo e($entry['match_tipo']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <?php echo $entry['precio_sin_iva'] !== null ? number_format((float)$entry['precio_sin_iva'], 2, ',', '.') . ' &euro;' : '<span style="color:var(--gray-400);">-</span>'; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($entry['disponible']): ?>
                            <span class="badge badge-success">Si</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td id="prod-cell-<?php echo $entry['id']; ?>">
                            <?php if ($entry['id_producto_match']): ?>
                            <a href="<?php echo getAdminUrl('modules/productes/modificar.php?id=' . $entry['prod_id']); ?>" target="_blank">
                                <?php echo e($entry['prod_nombre']); ?>
                            </a>
                            <br><small style="color:var(--gray-400);">ID: <?php echo $entry['prod_id']; ?><?php echo $entry['prod_codigo'] ? ' | ' . e($entry['prod_codigo']) : ''; ?></small>
                            <?php else: ?>
                            <span style="color:var(--gray-400); font-style:italic;">No vinculat</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;" id="price-cell-<?php echo $entry['id']; ?>">
                            <?php if ($entry['id_producto_match'] && $entry['prod_precio'] !== null): ?>
                            <?php echo number_format((float)$entry['prod_precio'], 2, ',', '.'); ?> &euro;
                            <?php else: ?>
                            <span style="color:var(--gray-400);">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;" id="stock-cell-<?php echo $entry['id']; ?>">
                            <?php if ($entry['id_producto_match']):
                                $stockActual = (int)($entry['stock_actual'] ?? 0);
                            ?>
                            <span style="font-weight:600; color:<?php echo $stockActual > 0 ? '#16a34a' : 'var(--gray-400)'; ?>;">
                                <?php echo $stockActual; ?>
                            </span>
                            <?php else: ?>
                            <span style="color:var(--gray-400);">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;" id="diff-cell-<?php echo $entry['id']; ?>">
                            <?php if ($entry['id_producto_match'] && $entry['precio_sin_iva'] !== null && $entry['prod_precio'] !== null):
                                $diff = (float)$entry['precio_sin_iva'] - (float)$entry['prod_precio'];
                                $diffColor = abs($diff) < 0.01 ? 'var(--gray-400)' : ($diff < 0 ? '#16a34a' : '#dc2626');
                            ?>
                            <span style="color:<?php echo $diffColor; ?>; font-weight:600;">
                                <?php echo ($diff >= 0 ? '+' : '') . number_format($diff, 2, ',', '.'); ?> &euro;
                            </span>
                            <?php else: ?>
                            <span style="color:var(--gray-400);">-</span>
                            <?php endif; ?>
                        </td>
                        <td id="action-cell-<?php echo $entry['id']; ?>">
                            <?php if ($entry['id_producto_match']): ?>
                            <div style="display:flex; gap:0.25rem; flex-wrap:wrap;">
                                <?php
                                $hasPriceDiff = $entry['precio_sin_iva'] !== null && $entry['prod_precio'] !== null && abs((float)$entry['precio_sin_iva'] - (float)$entry['prod_precio']) > 0.01;
                                $stockActual = (int)($entry['stock_actual'] ?? 0);
                                if ($hasPriceDiff && $stockActual === 0): ?>
                                <button type="button" class="btn btn-sm btn-success" onclick="actualitzarPreu(<?php echo $entry['id']; ?>, '<?php echo $taula; ?>')" title="Actualitzar preu (stock 0)">
                                    Actualitzar Preu
                                </button>
                                <?php elseif ($hasPriceDiff && $stockActual > 0): ?>
                                <button type="button" class="btn btn-sm btn-warning" onclick="actualitzarPreuAmbStock(<?php echo $entry['id']; ?>, '<?php echo $taula; ?>', <?php echo $stockActual; ?>)" title="Actualitzar preu (amb stock)">
                                    Actualitzar Preu
                                </button>
                                <?php elseif ($entry['id_producto_match'] && $entry['precio_sin_iva'] !== null && $entry['prod_precio'] !== null): ?>
                                <span style="color:var(--gray-400); font-size:0.8rem; padding:0.25rem 0.5rem;">Preu OK</span>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-danger" onclick="desvincular(<?php echo $entry['id']; ?>, '<?php echo $taula; ?>')" title="Desvincular">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <?php else: ?>
                            <div style="display:flex; gap:0.25rem; flex-wrap:wrap;">
                                <button type="button" class="btn btn-sm btn-primary" onclick="obrirModalVincular(<?php echo $entry['id']; ?>, '<?php echo e(addslashes($entry['nombre'])); ?>', '<?php echo $taula; ?>')" title="Vincular">
                                    Vincular
                                </button>
                                <a href="<?php echo getAdminUrl('modules/komkal/crear_producto.php?komkal_id=' . $entry['id'] . '&taula=' . $taula); ?>"
                                   class="btn btn-sm btn-secondary" title="Crear producte">
                                    Crear
                                </a>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPaginas > 1): ?>
    <div class="card-footer" style="display:flex; justify-content:space-between; align-items:center; padding:1rem 1.5rem; background:var(--gray-50); border-top:1px solid var(--gray-200);">
        <div style="color:var(--gray-500); font-size:0.875rem;">
            Mostrant <?php echo $offset + 1; ?> - <?php echo min($offset + $porPagina, $totalEntries); ?> de <?php echo number_format($totalEntries); ?>
        </div>
        <div style="display:flex; gap:0.25rem;">
            <?php
            $queryParams = $_GET;
            unset($queryParams['pagina']);
            $baseUrl = getAdminUrl('modules/komkal/matching.php') . '?' . http_build_query($queryParams);
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
            <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $pagina + 1; ?>" class="btn btn-sm btn-outline">Seg&uuml;ent &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Vincular -->
<div id="vincularModal" class="komkal-modal" style="display:none;">
    <div class="komkal-modal-backdrop" onclick="tancarModal()"></div>
    <div class="komkal-modal-content">
        <div class="komkal-modal-header">
            <h3>Vincular Producte</h3>
            <button type="button" onclick="tancarModal()" style="background:none; border:none; cursor:pointer; font-size:1.25rem; color:var(--gray-400);">&times;</button>
        </div>
        <div class="komkal-modal-body">
            <p style="font-size:0.875rem; margin-bottom:1rem;">
                Producte Komkal: <strong id="modalKomkalName"></strong>
            </p>
            <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Cercar producte Cellernou</label>
                <input type="text" id="searchProductInput" class="form-input" placeholder="Escriu nom o codi..."
                       oninput="cercarProducte(this.value)" autocomplete="off">
            </div>
            <div id="searchResults" style="max-height:300px; overflow-y:auto;">
                <p style="color:var(--gray-400); font-size:0.875rem; text-align:center;">Escriu almenys 2 caràcters per cercar.</p>
            </div>
        </div>
    </div>
</div>

<style>
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
.form-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--gray-600);
    margin-bottom: 0.25rem;
    text-transform: uppercase;
}
.row-matched { background: #f0fdf4; }
.row-unmatched { }
.row-matched:hover, .row-unmatched:hover { background: var(--gray-50); }
.btn-success {
    background: #16a34a;
    color: white;
    border: 1px solid #16a34a;
}
.btn-success:hover {
    background: #15803d;
    border-color: #15803d;
}

/* Modal styles */
.komkal-modal {
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
.komkal-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
}
.komkal-modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 600px;
    margin: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    max-height: 80vh;
    display: flex;
    flex-direction: column;
}
.komkal-modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.komkal-modal-header h3 {
    margin: 0;
    font-size: 1.125rem;
}
.komkal-modal-body {
    padding: 1.5rem;
    overflow-y: auto;
}
.search-result-item {
    padding: 0.75rem;
    border: 1px solid var(--gray-200);
    border-radius: 6px;
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background 0.15s;
}
.search-result-item:hover {
    background: var(--gray-50);
    border-color: var(--primary);
}
.alert {
    padding: 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}
.alert-success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}
.alert-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
</style>

<script>
const CSRF_TOKEN = '<?php echo $csrfToken; ?>';
const ADMIN_URL = '<?php echo getAdminUrl(''); ?>';
const CURRENT_TABLE = '<?php echo $taula; ?>';

let modalKomkalId = null;
let modalTable = null;
let searchTimeout = null;

function obrirModalVincular(komkalId, komkalName, table) {
    modalKomkalId = komkalId;
    modalTable = table;
    document.getElementById('modalKomkalName').textContent = komkalName;
    document.getElementById('searchProductInput').value = '';
    document.getElementById('searchResults').innerHTML = '<p style="color:var(--gray-400); font-size:0.875rem; text-align:center;">Escriu almenys 2 car\u00e0cters per cercar.</p>';
    document.getElementById('vincularModal').style.display = 'flex';
    setTimeout(function() { document.getElementById('searchProductInput').focus(); }, 100);
}

function tancarModal() {
    document.getElementById('vincularModal').style.display = 'none';
    modalKomkalId = null;
    modalTable = null;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tancarModal();
});

function cercarProducte(query) {
    if (searchTimeout) clearTimeout(searchTimeout);
    if (query.length < 2) {
        document.getElementById('searchResults').innerHTML = '<p style="color:var(--gray-400); font-size:0.875rem; text-align:center;">Escriu almenys 2 car\u00e0cters per cercar.</p>';
        return;
    }
    searchTimeout = setTimeout(function() {
        var fd = new FormData();
        fd.append('action', 'search');
        fd.append('csrf_token', CSRF_TOKEN);
        fd.append('query', query);

        fetch(ADMIN_URL + 'modules/komkal/ajax_match.php', {
            method: 'POST',
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.results.length > 0) {
                var html = '';
                data.results.forEach(function(p) {
                    html += '<div class="search-result-item" onclick="vincular(' + modalKomkalId + ', ' + p.id + ', \'' + modalTable + '\')">';
                    html += '<div><strong>' + escapeHtml(p.nombre) + '</strong>';
                    if (p.codigo) html += '<br><small style="color:var(--gray-400);">Codi: ' + escapeHtml(p.codigo) + '</small>';
                    html += '</div>';
                    html += '<div style="text-align:right;">';
                    html += '<div style="font-weight:600;">' + parseFloat(p.precio_medio).toFixed(2).replace('.', ',') + ' \u20ac</div>';
                    if (p.sector) html += '<small style="color:var(--gray-400);">' + escapeHtml(p.sector) + '</small>';
                    html += '</div></div>';
                });
                document.getElementById('searchResults').innerHTML = html;
            } else {
                document.getElementById('searchResults').innerHTML = '<p style="color:var(--gray-400); font-size:0.875rem; text-align:center;">No s\'han trobat resultats.</p>';
            }
        })
        .catch(function() {
            document.getElementById('searchResults').innerHTML = '<p style="color:#dc2626; font-size:0.875rem; text-align:center;">Error de connexi\u00f3.</p>';
        });
    }, 300);
}

function vincular(komkalId, productoId, table) {
    var fd = new FormData();
    fd.append('action', 'match');
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('komkal_id', komkalId);
    fd.append('producto_id', productoId);
    fd.append('taula', table);

    fetch(ADMIN_URL + 'modules/komkal/ajax_match.php', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            tancarModal();
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown'));
        }
    })
    .catch(function() {
        alert('Error de connexi\u00f3.');
    });
}

function desvincular(komkalId, table) {
    if (!confirm('Segur que vols desvincular aquest producte?')) return;

    var fd = new FormData();
    fd.append('action', 'unmatch');
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('komkal_id', komkalId);
    fd.append('taula', table);

    fetch(ADMIN_URL + 'modules/komkal/ajax_match.php', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown'));
        }
    })
    .catch(function() {
        alert('Error de connexi\u00f3.');
    });
}

function actualitzarPreu(komkalId, table) {
    var fd = new FormData();
    fd.append('action', 'update_one');
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('komkal_id', komkalId);
    fd.append('taula', table);

    fetch(ADMIN_URL + 'modules/komkal/ajax_precio.php', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown'));
        }
    })
    .catch(function() {
        alert('Error de connexi\u00f3.');
    });
}

function actualitzarPreuAmbStock(komkalId, table, stock) {
    if (!confirm('Aquest producte t\u00e9 ' + stock + ' unitats en stock. Segur que vols canviar el preu?')) return;
    actualitzarPreu(komkalId, table);
}

function actualitzarTotsPreus() {
    if (!confirm('Actualitzar els preus de tots els productes vinculats amb stock 0 i preu diferent (' + CURRENT_TABLE + ')? Aquesta acci\u00f3 modificar\u00e0 la taula productos.')) return;

    var fd = new FormData();
    fd.append('action', 'update_all');
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('taula', CURRENT_TABLE);

    fetch(ADMIN_URL + 'modules/komkal/ajax_precio.php', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert('Actualitzats ' + data.count + ' preus (stock 0) correctament.');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown'));
        }
    })
    .catch(function() {
        alert('Error de connexi\u00f3.');
    });
}

function crearTotsPendents() {
    var margin = CURRENT_TABLE === 'vinos' ? '25%' : '20%';
    if (!confirm('Crear TOTS els productes Komkal no vinculats com a productes nous?\n\n' +
                 'Taula: ' + CURRENT_TABLE + '\n' +
                 'Marge comercial: ' + margin + '\n' +
                 'IVA: 21%\n\n' +
                 'Aquesta acció crearà productes nous a la taula productos amb PVP calculat automàticament.')) return;

    var fd = new FormData();
    fd.append('action', 'crear_tots');
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('taula', CURRENT_TABLE);

    fetch(ADMIN_URL + 'modules/komkal/ajax_match.php', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var msg = 'Creats ' + data.count + ' productes nous de ' + data.total + ' pendents.';
            if (data.errors && data.errors.length > 0) {
                msg += '\n\nErrors:\n' + data.errors.join('\n');
            }
            alert(msg);
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown'));
        }
    })
    .catch(function() {
        alert('Error de connexió.');
    });
}

function executarAutoMatch() {
    var fd = new FormData();
    fd.append('action', 'automatch');
    fd.append('csrf_token', CSRF_TOKEN);
    fd.append('taula', CURRENT_TABLE);

    document.querySelector('[onclick="executarAutoMatch()"]').disabled = true;
    document.querySelector('[onclick="executarAutoMatch()"]').textContent = 'Processant...';

    fetch(ADMIN_URL + 'modules/komkal/ajax_match.php', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert('Auto-match completat.\nPer codi: ' + data.codigo + '\nPer nom: ' + data.fuzzy);
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown'));
            location.reload();
        }
    })
    .catch(function() {
        alert('Error de connexi\u00f3.');
        location.reload();
    });
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
