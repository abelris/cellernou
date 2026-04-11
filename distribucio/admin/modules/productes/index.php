<?php
/**
 * El Celler Nou - Mòdul Productes - Llistat
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Productes';

// Procesar acciones masivas
$mensajeBulk = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $mensajeBulk = ['type' => 'error', 'text' => 'Error de seguretat. Torna a intentar-ho.'];
    } else {
        $ids = $_POST['ids'] ?? [];
        $action = $_POST['bulk_action'];

        if (!empty($ids) && is_array($ids)) {
            $idsClean = array_map('intval', $ids);
            $idsList = implode(',', $idsClean);

            if ($action === 'deactivate') {
                $result = mysqli_query($bd, "UPDATE productos SET activo = 0 WHERE id IN ($idsList)");
                if ($result) {
                    $count = mysqli_affected_rows($bd);
                    logActivity('bulk_deactivate', "Desactivats $count productes: $idsList");
                    $mensajeBulk = ['type' => 'success', 'text' => "$count productes desactivats correctament."];
                }
            } elseif ($action === 'activate') {
                $result = mysqli_query($bd, "UPDATE productos SET activo = 1 WHERE id IN ($idsList)");
                if ($result) {
                    $count = mysqli_affected_rows($bd);
                    logActivity('bulk_activate', "Activats $count productes: $idsList");
                    $mensajeBulk = ['type' => 'success', 'text' => "$count productes activats correctament."];
                }
            } elseif ($action === 'delete') {
                // Primer eliminar registres relacionats
                mysqli_query($bd, "DELETE FROM precios_producto_tienda WHERE idProducto IN ($idsList)");
                mysqli_query($bd, "DELETE FROM linea_tiquet WHERE idProducto IN ($idsList)");

                // Ara eliminar els productes
                $result = mysqli_query($bd, "DELETE FROM productos WHERE id IN ($idsList)");
                if ($result) {
                    $count = mysqli_affected_rows($bd);
                    logActivity('bulk_delete', "Eliminats $count productes: $idsList");
                    $mensajeBulk = ['type' => 'success', 'text' => "$count productes eliminats correctament."];
                }
            }
        }
    }
}

// Parámetros de búsqueda y paginación
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$sector = isset($_GET['sector']) ? (int)$_GET['sector'] : 0;
$tipo = isset($_GET['tipo']) ? (int)$_GET['tipo'] : 0;
$procedencia = isset($_GET['procedencia']) ? (int)$_GET['procedencia'] : 0;
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'activos';
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$porPagina = 25;
$offset = ($pagina - 1) * $porPagina;

// Construir consulta
$where = [];

if ($estado === 'activos') {
    $where[] = "p.activo = 1";
} elseif ($estado === 'inactivos') {
    $where[] = "p.activo = 0";
}

if ($buscar !== '') {
    $buscarSafe = mysqli_real_escape_string($bd, $buscar);
    $where[] = "(p.nombre LIKE '%$buscarSafe%' OR p.codigo LIKE '%$buscarSafe%' OR p.descripcion LIKE '%$buscarSafe%')";
}

if ($sector > 0) {
    $where[] = "p.idSector = $sector";
}

if ($tipo > 0) {
    $where[] = "p.idTipo = $tipo";
}

if ($procedencia > 0) {
    $where[] = "p.idProcedencia = $procedencia";
}

$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Contar total
$sqlCount = "SELECT COUNT(*) as total FROM productos p $whereClause";
$resultCount = mysqli_query($bd, $sqlCount);
$totalProductos = mysqli_fetch_assoc($resultCount)['total'];
$totalPaginas = ceil($totalProductos / $porPagina);

// Obtener productos
$productos = [];
$sql = "SELECT p.id, p.codigo, p.nombre, p.unidades, p.precio_medio, p.precio_venta_dis, p.activo,
               s.nombre_es as sector, tp.nombre_es as tipo, pr.nombre_es as procedencia,
               COALESCE(ppt.pvp, 0) as precio_venta
        FROM productos p
        LEFT JOIN sectores s ON p.idSector = s.id
        LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
        LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
        LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
        $whereClause
        ORDER BY p.nombre ASC
        LIMIT $offset, $porPagina";

$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
}

// Obtener sectores para filtro
$sectores = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM sectores ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sectores[] = $row;
    }
}

// Obtener tipos para filtro
$tipos = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM tipo_producto ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tipos[] = $row;
    }
}

// Obtener procedencias para filtro
$procedencias = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM procedencia ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $procedencias[] = $row;
    }
}

// Mensajes flash
$mensaje = '';
$tipoMensaje = '';
if ($mensajeBulk) {
    $mensaje = $mensajeBulk['text'];
    $tipoMensaje = $mensajeBulk['type'];
} elseif (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':
            $mensaje = 'Producte creat correctament.';
            $tipoMensaje = 'success';
            break;
        case 'updated':
            $mensaje = 'Producte actualitzat correctament.';
            $tipoMensaje = 'success';
            break;
        case 'deleted':
            $mensaje = 'Producte eliminat correctament.';
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

<!-- Barra de acciones masivas (oculta inicialmente) -->
<div id="bulkActionBar" class="bulk-action-bar" style="display: none;">
    <div class="bulk-action-content">
        <span class="bulk-count"><strong id="selectedCount">0</strong> productes seleccionats</span>
        <div class="bulk-actions">
            <?php if ($estado !== 'inactivos'): ?>
            <button type="button" class="btn btn-warning btn-sm" onclick="executarAccioMassiva('deactivate')">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Desactivar
            </button>
            <?php endif; ?>
            <?php if ($estado !== 'activos'): ?>
            <button type="button" class="btn btn-success btn-sm" onclick="executarAccioMassiva('activate')">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Activar
            </button>
            <?php endif; ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="executarAccioMassiva('delete')">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Eliminar
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="desseleccionarTots()">
                Cancel·lar
            </button>
        </div>
    </div>
</div>

<!-- Filtros y acciones -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" action="" class="filters-form">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Cercar</label>
                    <input type="text" name="buscar" class="form-input" placeholder="Nom, codi o descripció..."
                           value="<?php echo e($buscar); ?>">
                </div>
                <div style="min-width: 150px;">
                    <label class="form-label">Sector</label>
                    <select name="sector" class="form-input">
                        <option value="0">Tots els sectors</option>
                        <?php foreach ($sectores as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo $sector == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo e($s['nombre_es']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width: 150px;">
                    <label class="form-label">Tipus</label>
                    <select name="tipo" class="form-input">
                        <option value="0">Tots els tipus</option>
                        <?php foreach ($tipos as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo $tipo == $t['id'] ? 'selected' : ''; ?>>
                            <?php echo e($t['nombre_es']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width: 150px;">
                    <label class="form-label">Procedència</label>
                    <select name="procedencia" class="form-input">
                        <option value="0">Totes les procedències</option>
                        <?php foreach ($procedencias as $pr): ?>
                        <option value="<?php echo $pr['id']; ?>" <?php echo $procedencia == $pr['id'] ? 'selected' : ''; ?>>
                            <?php echo e($pr['nombre_es']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width: 120px;">
                    <label class="form-label">Estat</label>
                    <select name="estado" class="form-input">
                        <option value="activos" <?php echo $estado === 'activos' ? 'selected' : ''; ?>>Actius</option>
                        <option value="inactivos" <?php echo $estado === 'inactivos' ? 'selected' : ''; ?>>Inactius</option>
                        <option value="todos" <?php echo $estado === 'todos' ? 'selected' : ''; ?>>Tots</option>
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
                    <a href="<?php echo getAdminUrl('modules/productes/'); ?>" class="btn btn-outline">Netejar</a>
                </div>
                <div style="margin-left: auto;">
                    <a href="<?php echo getAdminUrl('modules/productes/crear.php'); ?>" class="btn btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nou Producte
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Formulario para acciones masivas -->
<form id="bulkForm" method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="bulk_action" id="bulkAction" value="">

    <!-- Resultados -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Productes
                <span class="badge badge-info" style="margin-left: 0.5rem;"><?php echo number_format($totalProductos); ?></span>
            </h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($productos)): ?>
            <div class="empty-state" style="padding: 3rem;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; color: var(--gray-400);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="empty-state-title">No s'han trobat productes</h3>
                <p class="empty-state-text">Prova amb altres filtres o crea un nou producte.</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAll" class="checkbox-select-all" title="Seleccionar tots">
                            </th>
                            <th style="width: 80px;">Codi</th>
                            <th>Producte</th>
                            <th>Sector</th>
                            <th>Tipus</th>
                            <th style="text-align: center;">Unitats</th>
                            <th style="text-align: right;">Preu Compra</th>
                            <th style="text-align: right;">PVP</th>
                            <th style="text-align: right;">PVD</th>
                            <th style="text-align: center;">Estat</th>
                            <th style="width: 100px;">Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr class="product-row" data-id="<?php echo $producto['id']; ?>">
                            <td>
                                <input type="checkbox" name="ids[]" value="<?php echo $producto['id']; ?>" class="checkbox-product">
                            </td>
                            <td><code><?php echo e($producto['codigo'] ?: '-'); ?></code></td>
                            <td>
                                <a href="<?php echo getAdminUrl('modules/productes/modificar.php?id=' . $producto['id']); ?>">
                                    <strong><?php echo e($producto['nombre']); ?></strong>
                                </a>
                                <?php if ($producto['procedencia']): ?>
                                <br><small style="color: var(--gray-500);"><?php echo e($producto['procedencia']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($producto['sector'] ?: '-'); ?></td>
                            <td><?php echo e($producto['tipo'] ?: '-'); ?></td>
                            <td style="text-align: center;">
                                <?php if ($producto['unidades'] <= 0): ?>
                                <span class="badge badge-danger">0</span>
                                <?php elseif ($producto['unidades'] < 5): ?>
                                <span class="badge badge-warning"><?php echo $producto['unidades']; ?></span>
                                <?php else: ?>
                                <span class="badge badge-success"><?php echo $producto['unidades']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;"><?php echo number_format($producto['precio_medio'], 2, ',', '.'); ?> &euro;</td>
                            <td style="text-align: right;"><strong><?php echo number_format($producto['precio_venta'], 2, ',', '.'); ?> &euro;</strong></td>
                            <td style="text-align: right;"><?php echo $producto['precio_venta_dis'] > 0 ? number_format($producto['precio_venta_dis'], 2, ',', '.') . ' &euro;' : '<span style="color:var(--gray-400);">-</span>'; ?></td>
                            <td style="text-align: center;">
                                <?php if ($producto['activo']): ?>
                                <span class="badge badge-success">Actiu</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">Inactiu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="<?php echo getAdminUrl('modules/productes/modificar.php?id=' . $producto['id']); ?>"
                                       class="btn btn-sm btn-secondary" title="Editar">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                            onclick="confirmarEliminar(<?php echo $producto['id']; ?>, '<?php echo e(addslashes($producto['nombre'])); ?>')">
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
                Mostrant <?php echo $offset + 1; ?> - <?php echo min($offset + $porPagina, $totalProductos); ?> de <?php echo $totalProductos; ?>
            </div>
            <div class="pagination">
                <?php
                $queryParams = $_GET;
                unset($queryParams['pagina']);
                $baseUrl = getAdminUrl('modules/productes/') . '?' . http_build_query($queryParams);
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
</form>

<!-- Modal de confirmación de eliminación -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar eliminació</h3>
        </div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar el producte <strong id="deleteProductName"></strong>?</p>
            <p style="color: var(--gray-500); font-size: 0.875rem;">Aquesta acció no es pot desfer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form id="deleteForm" method="POST" action="<?php echo getAdminUrl('modules/productes/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="id" id="deleteProductId" value="">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación de acción masiva -->
<div id="bulkModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarBulkModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="bulkModalTitle">Confirmar acció</h3>
        </div>
        <div class="modal-body">
            <p id="bulkModalText"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarBulkModal()">Cancel·lar</button>
            <button type="button" class="btn btn-primary" id="bulkConfirmBtn" onclick="confirmarAccioMassiva()">Confirmar</button>
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
.alert {
    padding: 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
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

/* Bulk action bar */
.bulk-action-bar {
    position: sticky;
    top: 0;
    z-index: 100;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark, #5a252c) 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    margin-bottom: 1rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.2s ease;
}
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.bulk-action-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.bulk-count {
    font-size: 0.9375rem;
}
.bulk-actions {
    display: flex;
    gap: 0.5rem;
}

/* Checkboxes */
.checkbox-select-all,
.checkbox-product {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
.product-row.selected {
    background-color: #fef3c7 !important;
}
.product-row:hover {
    background-color: var(--gray-50);
}
</style>

<script>
let pendingAction = null;

// Actualizar contador y barra de acciones
function updateBulkBar() {
    const checkboxes = document.querySelectorAll('.checkbox-product:checked');
    const count = checkboxes.length;
    const bar = document.getElementById('bulkActionBar');
    const countEl = document.getElementById('selectedCount');

    countEl.textContent = count;

    if (count > 0) {
        bar.style.display = 'block';
    } else {
        bar.style.display = 'none';
    }

    // Actualizar estado visual de filas
    document.querySelectorAll('.product-row').forEach(row => {
        const checkbox = row.querySelector('.checkbox-product');
        if (checkbox && checkbox.checked) {
            row.classList.add('selected');
        } else {
            row.classList.remove('selected');
        }
    });

    // Actualizar checkbox "seleccionar todos"
    const selectAll = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.checkbox-product');
    selectAll.checked = allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length;
    selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
}

// Seleccionar/deseleccionar todos
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.checkbox-product');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});

// Escuchar cambios en checkboxes individuales
document.querySelectorAll('.checkbox-product').forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

// Deseleccionar todos
function desseleccionarTots() {
    document.querySelectorAll('.checkbox-product').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkBar();
}

// Ejecutar acción masiva
function executarAccioMassiva(action) {
    const count = document.querySelectorAll('.checkbox-product:checked').length;
    pendingAction = action;

    let title, text, btnClass;
    if (action === 'deactivate') {
        title = 'Desactivar productes';
        text = `Estàs segur que vols desactivar ${count} productes? Els productes desactivats no apareixeran a la botiga.`;
        btnClass = 'btn-warning';
    } else if (action === 'activate') {
        title = 'Activar productes';
        text = `Estàs segur que vols activar ${count} productes?`;
        btnClass = 'btn-success';
    } else if (action === 'delete') {
        title = 'Eliminar productes';
        text = `Estàs segur que vols ELIMINAR ${count} productes? Aquesta acció no es pot desfer i s'eliminaran també els preus i línies de tiquet associades.`;
        btnClass = 'btn-danger';
    }

    document.getElementById('bulkModalTitle').textContent = title;
    document.getElementById('bulkModalText').textContent = text;
    document.getElementById('bulkConfirmBtn').className = 'btn ' + btnClass;
    document.getElementById('bulkModal').style.display = 'flex';
}

// Confirmar acción masiva
function confirmarAccioMassiva() {
    document.getElementById('bulkAction').value = pendingAction;
    document.getElementById('bulkForm').submit();
}

function tancarBulkModal() {
    document.getElementById('bulkModal').style.display = 'none';
    pendingAction = null;
}

// Modal de eliminación individual
function confirmarEliminar(id, nombre) {
    document.getElementById('deleteProductId').value = id;
    document.getElementById('deleteProductName').textContent = nombre;
    document.getElementById('deleteModal').style.display = 'flex';
}

function tancarModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        tancarModal();
        tancarBulkModal();
    }
});

// Click en fila para seleccionar
document.querySelectorAll('.product-row').forEach(row => {
    row.addEventListener('click', function(e) {
        // No activar si se clickea en un enlace, botón o checkbox
        if (e.target.closest('a, button, input')) return;

        const checkbox = this.querySelector('.checkbox-product');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            updateBulkBar();
        }
    });
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
