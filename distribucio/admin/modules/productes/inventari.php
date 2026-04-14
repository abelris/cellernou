<?php
/**
 * El Celler Nou - Inventari - Grid Editable
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Inventari';

// Procesar actualización AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Token invàlid']);
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';

    $allowedFields = ['codigo', 'unidades', 'idTipo', 'idSector', 'idProcedencia', 'activo'];
    if ($id <= 0 || !in_array($field, $allowedFields)) {
        echo json_encode(['success' => false, 'error' => 'Dades invàlides']);
        exit;
    }

    if ($field === 'activo') {
        $valueInt = (int)$value ? 1 : 0;
        $sql = "UPDATE productos SET activo = $valueInt WHERE id = $id";
        if (mysqli_query($bd, $sql)) {
            logActivity('inventari_update', "Producte ID: $id " . ($valueInt ? 'activat' : 'desactivat'));
            echo json_encode(['success' => true, 'activo' => $valueInt]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al guardar']);
        }
        exit;
    } elseif ($field === 'codigo') {
        $valueSafe = mysqli_real_escape_string($bd, trim($value));
        // Verificar código único
        if (!empty($valueSafe)) {
            $check = mysqli_query($bd, "SELECT id FROM productos WHERE codigo = '$valueSafe' AND id != $id");
            if (mysqli_num_rows($check) > 0) {
                echo json_encode(['success' => false, 'error' => 'Aquest codi ja existeix']);
                exit;
            }
        }
        $sql = "UPDATE productos SET codigo = '$valueSafe' WHERE id = $id";
    } elseif ($field === 'unidades') {
        $valueInt = max(0, (int)$value);

        // Obtener stock actual desde stock_producto (como hace inventario.php)
        $rsStock = mysqli_query($bd, "SELECT COUNT(*) as existencias FROM stock_producto WHERE vendido=0 AND idProducto=$id");
        $stockActual = 0;
        if ($dataStock = mysqli_fetch_assoc($rsStock)) {
            $stockActual = (int)$dataStock['existencias'];
        }

        $diff = $valueInt - $stockActual;

        if ($diff > 0) {
            // Añadir unidades: insertar filas en stock_producto (como hace inventario.php)
            $rsPrecio = mysqli_query($bd, "SELECT precio_medio FROM productos WHERE id=$id");
            $precioMedio = 0;
            if ($dataPrecio = mysqli_fetch_assoc($rsPrecio)) {
                $precioMedio = $dataPrecio['precio_medio'];
            }
            $values = array_fill(0, $diff, "(1, 0, $id, NOW(), $precioMedio)");
            mysqli_query($bd, "INSERT INTO stock_producto (idTienda, idCompra, idProducto, fecha_entrada, precio_compra) VALUES " . implode(',', $values));
        } elseif ($diff < 0) {
            // Reducir unidades: eliminar filas de stock_producto no vendidas (las más recientes)
            mysqli_query($bd, "DELETE FROM stock_producto WHERE vendido=0 AND idProducto=$id ORDER BY fecha_entrada DESC LIMIT " . abs($diff));
        }

        // No hay SQL pendiente, ya se ejecutó todo
        if (true) {
            logActivity('inventari_update', "Actualitzat $field del producte ID: $id (de $stockActual a $valueInt)");
            echo json_encode(['success' => true]);
        }
        exit;
    } elseif (in_array($field, ['idTipo', 'idSector', 'idProcedencia'])) {
        $valueInt = (int)$value;
        $sql = "UPDATE productos SET $field = $valueInt WHERE id = $id";
    }

    if (mysqli_query($bd, $sql)) {
        logActivity('inventari_update', "Actualitzat $field del producte ID: $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar']);
    }
    exit;
}

// Parámetros de búsqueda y paginación
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$sector = isset($_GET['sector']) ? (int)$_GET['sector'] : 0;
$precioMin = isset($_GET['precio_min']) && $_GET['precio_min'] !== '' ? (float)$_GET['precio_min'] : null;
$precioMax = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? (float)$_GET['precio_max'] : null;
$soloSinCodigo = isset($_GET['sin_codigo']) && $_GET['sin_codigo'] === '1';
$soloBajoStock = isset($_GET['bajo_stock']) && $_GET['bajo_stock'] === '1';
$mostrarInactivos = isset($_GET['inactivos']) && $_GET['inactivos'] === '1';
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$porPagina = 50;
$offset = ($pagina - 1) * $porPagina;

// Construir consulta
$where = [];
if (!$mostrarInactivos) {
    $where[] = "p.activo = 1";
}

if ($buscar !== '') {
    $buscarSafe = mysqli_real_escape_string($bd, $buscar);
    $where[] = "(p.nombre LIKE '%$buscarSafe%' OR p.codigo LIKE '%$buscarSafe%')";
}

if ($sector > 0) {
    $where[] = "p.idSector = $sector";
}

if ($precioMin !== null) {
    $where[] = "p.precio_medio >= $precioMin";
}

if ($precioMax !== null) {
    $where[] = "p.precio_medio <= $precioMax";
}

if ($soloSinCodigo) {
    $where[] = "(p.codigo IS NULL OR p.codigo = '')";
}

if ($soloBajoStock) {
    $where[] = "COALESCE(sp_count.unidades, 0) < 5";
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Precomputed stock JOIN (single GROUP BY scan, avoids N+1 correlated subqueries)
$stockJoin = "LEFT JOIN (SELECT idProducto, COUNT(*) as unidades FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) sp_count ON sp_count.idProducto = p.id";

// Contar total
$sqlCount = "SELECT COUNT(*) as total FROM productos p $stockJoin $whereClause";
$resultCount = mysqli_query($bd, $sqlCount);
$totalProductos = mysqli_fetch_assoc($resultCount)['total'];
$totalPaginas = ceil($totalProductos / $porPagina);

// Obtener productos
$productos = [];
$sql = "SELECT p.id, p.codigo, p.nombre, p.precio_medio,
               p.idTipo, p.idSector, p.idProcedencia, p.activo,
               COALESCE(ppt.pvp, 0) as precio_venta,
               s.nombre_es as sector_nombre,
               tp.nombre_es as tipo_nombre,
               pr.nombre_es as procedencia_nombre,
               COALESCE(sp_count.unidades, 0) as unidades
        FROM productos p
        LEFT JOIN sectores s ON p.idSector = s.id
        LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
        LEFT JOIN procedencia pr ON p.idProcedencia = pr.id
        LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
        $stockJoin
        $whereClause
        ORDER BY p.nombre ASC
        LIMIT $offset, $porPagina";

$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
}

// Obtener sectores para filtro y edición
$sectores = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM sectores ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sectores[] = $row;
    }
}

// Obtener tipos de producto para edición
$tipos = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM tipo_producto WHERE nombre_es != '' ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tipos[] = $row;
    }
}

// Obtener procedencias para edición
$procedencias = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM procedencia WHERE nombre_es != '' ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $procedencias[] = $row;
    }
}

$csrfToken = generateCsrfToken();

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-actions" style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo getAdminUrl('modules/estoc/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar a Estoc
    </a>
    <div id="saveStatus" class="save-status"></div>
</div>

<!-- Filtros -->
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" action="">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Cercar</label>
                    <input type="text" name="buscar" class="form-input" placeholder="Nom o codi..."
                           value="<?php echo e($buscar); ?>">
                </div>
                <div style="min-width: 150px;">
                    <label class="form-label">Sector</label>
                    <select name="sector" class="form-input">
                        <option value="0">Tots</option>
                        <?php foreach ($sectores as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo $sector == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo e($s['nombre_es']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width: 120px;">
                    <label class="form-label">Preu compra des de</label>
                    <input type="number" name="precio_min" class="form-input" step="0.01" min="0"
                           placeholder="0,00" value="<?php echo $precioMin !== null ? e($precioMin) : ''; ?>">
                </div>
                <div style="min-width: 120px;">
                    <label class="form-label">Preu compra fins a</label>
                    <input type="number" name="precio_max" class="form-input" step="0.01" min="0"
                           placeholder="999,00" value="<?php echo $precioMax !== null ? e($precioMax) : ''; ?>">
                </div>
                <div>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="sin_codigo" value="1" <?php echo $soloSinCodigo ? 'checked' : ''; ?>>
                        Sense codi
                    </label>
                </div>
                <div>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="bajo_stock" value="1" <?php echo $soloBajoStock ? 'checked' : ''; ?>>
                        Baix estoc
                    </label>
                </div>
                <div>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="inactivos" value="1" <?php echo $mostrarInactivos ? 'checked' : ''; ?>>
                        Mostrar inactius
                    </label>
                </div>
                <div>
                    <button type="submit" class="btn btn-secondary">Filtrar</button>
                </div>
                <div>
                    <a href="<?php echo getAdminUrl('modules/productes/inventari.php'); ?>" class="btn btn-outline">Netejar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Grid de inventario -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            Inventari
            <span class="badge badge-info" style="margin-left: 0.5rem;"><?php echo number_format($totalProductos); ?> productes</span>
        </h3>
        <div style="font-size: 0.875rem; color: var(--gray-500);">
            <kbd>Tab</kbd> o <kbd>Enter</kbd> per moure's | <kbd>Ctrl+&uarr;</kbd> <kbd>Ctrl+&darr;</kbd> en selects
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($productos)): ?>
        <div class="empty-state" style="padding: 3rem;">
            <h3 class="empty-state-title">No s'han trobat productes</h3>
            <p class="empty-state-text">Prova amb altres filtres.</p>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table inventory-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">Actiu</th>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 140px;">Codi</th>
                        <th>Producte</th>
                        <th style="width: 130px;">Sector</th>
                        <th style="width: 150px;">Tipus</th>
                        <th style="width: 170px;">Procedència</th>
                        <th style="width: 80px; text-align: center;">Unitats</th>
                        <th style="width: 90px; text-align: right;">P. Venda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                    <tr data-id="<?php echo $producto['id']; ?>"<?php echo !$producto['activo'] ? ' class="row-inactiu"' : ''; ?>>
                        <td style="text-align: center;">
                            <label class="toggle-switch">
                                <input type="checkbox" class="toggle-activo" data-id="<?php echo $producto['id']; ?>" <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td class="cell-id"><?php echo $producto['id']; ?></td>
                        <td>
                            <input type="text"
                                   class="grid-input input-codigo"
                                   data-field="codigo"
                                   data-id="<?php echo $producto['id']; ?>"
                                   value="<?php echo e($producto['codigo']); ?>"
                                   placeholder="Sense codi">
                        </td>
                        <td class="cell-nombre">
                            <a href="<?php echo getAdminUrl('modules/productes/modificar.php?id=' . $producto['id']); ?>">
                                <?php echo e($producto['nombre']); ?>
                            </a>
                        </td>
                        <td>
                            <select class="grid-select" data-field="idSector" data-id="<?php echo $producto['id']; ?>">
                                <option value="0">-- Cap --</option>
                                <?php foreach ($sectores as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo $producto['idSector'] == $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($s['nombre_es']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select class="grid-select" data-field="idTipo" data-id="<?php echo $producto['id']; ?>">
                                <option value="0">-- Cap --</option>
                                <?php foreach ($tipos as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $producto['idTipo'] == $t['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($t['nombre_es']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select class="grid-select" data-field="idProcedencia" data-id="<?php echo $producto['id']; ?>">
                                <option value="0">-- Cap --</option>
                                <?php foreach ($procedencias as $pr): ?>
                                <option value="<?php echo $pr['id']; ?>" <?php echo $producto['idProcedencia'] == $pr['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($pr['nombre_es']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td style="text-align: center;">
                            <input type="number"
                                   class="grid-input input-unidades <?php echo $producto['unidades'] < 5 ? 'low-stock' : ''; ?>"
                                   data-field="unidades"
                                   data-id="<?php echo $producto['id']; ?>"
                                   value="<?php echo $producto['unidades']; ?>"
                                   min="0">
                        </td>
                        <td style="text-align: right;"><strong><?php echo number_format($producto['precio_venta'], 2, ',', '.'); ?> &euro;</strong></td>
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
            Pàgina <?php echo $pagina; ?> de <?php echo $totalPaginas; ?>
        </div>
        <div class="pagination">
            <?php
            $queryParams = $_GET;
            unset($queryParams['pagina']);
            $baseUrl = getAdminUrl('modules/productes/inventari.php') . '?' . http_build_query($queryParams);
            ?>

            <?php if ($pagina > 1): ?>
            <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $pagina - 1; ?>" class="btn btn-sm btn-outline">&laquo;</a>
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
            <a href="<?php echo $baseUrl; ?>&pagina=<?php echo $pagina + 1; ?>" class="btn btn-sm btn-outline">&raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
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
}
.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1);
}
.checkbox-inline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    padding: 0.5rem 0;
}
.checkbox-inline input {
    width: 16px;
    height: 16px;
}
.card-footer {
    padding: 1rem 1.5rem;
    background: var(--gray-50);
    border-top: 1px solid var(--gray-200);
}
.pagination {
    display: flex;
    gap: 0.25rem;
}

/* Grid editable styles */
.inventory-table {
    font-size: 0.875rem;
}
.inventory-table td {
    padding: 0.5rem 0.75rem;
    vertical-align: middle;
}
.cell-id {
    color: var(--gray-400);
    font-size: 0.75rem;
}
.cell-nombre a {
    color: var(--gray-900);
    text-decoration: none;
}
.cell-nombre a:hover {
    color: var(--primary);
}
.cell-sector {
    color: var(--gray-500);
    font-size: 0.8125rem;
}

.grid-input {
    width: 100%;
    padding: 0.375rem 0.5rem;
    font-size: 0.875rem;
    border: 1px solid transparent;
    border-radius: 4px;
    background: transparent;
    transition: all 0.15s;
}
.grid-input:hover {
    background: var(--gray-50);
    border-color: var(--gray-200);
}
.grid-input:focus {
    outline: none;
    background: white;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1);
}
.input-codigo {
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.8125rem;
}
.input-unidades {
    text-align: center;
    font-weight: 600;
    max-width: 80px;
    margin: 0 auto;
}
.input-unidades.low-stock {
    color: var(--danger);
    background: #fef2f2;
}
.input-unidades.low-stock:focus {
    background: white;
}

.grid-select {
    width: 100%;
    padding: 0.375rem 0.5rem;
    font-size: 0.8125rem;
    border: 1px solid transparent;
    border-radius: 4px;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
    padding-right: 1.5rem;
}
.grid-select:hover {
    background-color: var(--gray-50);
    border-color: var(--gray-200);
}
.grid-select:focus {
    outline: none;
    background-color: white;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1);
}
.grid-select.saving {
    background-color: #fef3c7 !important;
    border-color: #f59e0b !important;
}
.grid-select.saved {
    background-color: #d1fae5 !important;
    border-color: #10b981 !important;
}
.grid-select.error {
    background-color: #fee2e2 !important;
    border-color: #ef4444 !important;
}

/* Estados de guardado */
.grid-input.saving {
    background: #fef3c7 !important;
    border-color: #f59e0b !important;
}
.grid-input.saved {
    background: #d1fae5 !important;
    border-color: #10b981 !important;
}
.grid-input.error {
    background: #fee2e2 !important;
    border-color: #ef4444 !important;
}

.save-status {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    opacity: 0;
    transition: opacity 0.3s;
}
.save-status.visible {
    opacity: 1;
}
.save-status.saving {
    background: #fef3c7;
    color: #92400e;
}
.save-status.saved {
    background: #d1fae5;
    color: #065f46;
}
.save-status.error {
    background: #fee2e2;
    color: #991b1b;
}

kbd {
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-radius: 3px;
    padding: 0.125rem 0.375rem;
    font-size: 0.75rem;
    font-family: inherit;
}

/* Toggle switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 36px;
    height: 20px;
    cursor: pointer;
    margin: 0;
}
.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--gray-300);
    border-radius: 20px;
    transition: background 0.2s;
}
.toggle-slider::before {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    left: 2px;
    bottom: 2px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s;
}
.toggle-switch input:checked + .toggle-slider {
    background: #10b981;
}
.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(16px);
}

/* Fila inactiva */
tr.row-inactiu {
    opacity: 0.5;
}
tr.row-inactiu .cell-nombre a {
    text-decoration: line-through;
}
</style>

<script>
const csrfToken = '<?php echo $csrfToken; ?>';
const saveUrl = '<?php echo getAdminUrl('modules/productes/inventari.php'); ?>';

let saveTimeout = null;
let pendingSaves = new Map();

// Mostrar estado
function showStatus(type, message) {
    const status = document.getElementById('saveStatus');
    status.textContent = message;
    status.className = 'save-status visible ' + type;

    if (type !== 'saving') {
        setTimeout(() => {
            status.classList.remove('visible');
        }, 2000);
    }
}

// Guardar campo (input o select)
async function saveField(element) {
    const id = element.dataset.id;
    const field = element.dataset.field;
    const value = element.value;
    const originalValue = element.dataset.originalValue;

    // No guardar si no ha cambiado
    if (value === originalValue) return;

    element.classList.remove('saved', 'error');
    element.classList.add('saving');
    showStatus('saving', 'Guardant...');

    try {
        const formData = new FormData();
        formData.append('ajax', '1');
        formData.append('csrf_token', csrfToken);
        formData.append('id', id);
        formData.append('field', field);
        formData.append('value', value);

        const response = await fetch(saveUrl, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        element.classList.remove('saving');

        if (result.success) {
            element.classList.add('saved');
            element.dataset.originalValue = value;
            showStatus('saved', 'Guardat!');

            // Actualizar clase low-stock si es unidades
            if (field === 'unidades') {
                if (parseInt(value) < 5) {
                    element.classList.add('low-stock');
                } else {
                    element.classList.remove('low-stock');
                }
            }

            setTimeout(() => {
                element.classList.remove('saved');
            }, 1500);
        } else {
            element.classList.add('error');
            showStatus('error', result.error || 'Error al guardar');
            // Revertir valor
            element.value = originalValue;
            setTimeout(() => {
                element.classList.remove('error');
            }, 2000);
        }
    } catch (error) {
        element.classList.remove('saving');
        element.classList.add('error');
        showStatus('error', 'Error de connexió');
        element.value = element.dataset.originalValue;
        setTimeout(() => {
            element.classList.remove('error');
        }, 2000);
    }
}

// Inicializar inputs
document.querySelectorAll('.grid-input').forEach(input => {
    // Guardar valor original
    input.dataset.originalValue = input.value;

    // Guardar al perder foco
    input.addEventListener('blur', function() {
        saveField(this);
    });

    // Guardar con Enter y mover al siguiente
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur();

            // Mover al siguiente input de la misma columna
            const currentRow = this.closest('tr');
            const nextRow = currentRow.nextElementSibling;
            if (nextRow) {
                const field = this.dataset.field;
                const nextInput = nextRow.querySelector(`input[data-field="${field}"]`);
                if (nextInput) {
                    nextInput.focus();
                    nextInput.select();
                }
            }
        }

        // Mover arriba/abajo con flechas
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            const currentRow = this.closest('tr');
            const targetRow = e.key === 'ArrowDown'
                ? currentRow.nextElementSibling
                : currentRow.previousElementSibling;

            if (targetRow) {
                const field = this.dataset.field;
                const targetInput = targetRow.querySelector(`input[data-field="${field}"]`);
                if (targetInput) {
                    this.blur();
                    targetInput.focus();
                    targetInput.select();
                }
            }
        }
    });

    // Seleccionar todo al enfocar
    input.addEventListener('focus', function() {
        this.select();
    });
});

// Inicializar toggles activo
document.querySelectorAll('.toggle-activo').forEach(toggle => {
    toggle.addEventListener('change', async function() {
        const id = this.dataset.id;
        const value = this.checked ? 1 : 0;
        const row = this.closest('tr');

        showStatus('saving', 'Guardant...');

        try {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('csrf_token', csrfToken);
            formData.append('id', id);
            formData.append('field', 'activo');
            formData.append('value', value);

            const response = await fetch(saveUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                row.classList.toggle('row-inactiu', !value);
                showStatus('saved', value ? 'Producte activat' : 'Producte desactivat');
            } else {
                this.checked = !this.checked;
                showStatus('error', result.error || 'Error al guardar');
            }
        } catch (error) {
            this.checked = !this.checked;
            showStatus('error', 'Error de connexió');
        }
    });
});

// Inicializar selects
document.querySelectorAll('.grid-select').forEach(select => {
    // Guardar valor original
    select.dataset.originalValue = select.value;

    // Guardar al cambiar
    select.addEventListener('change', function() {
        saveField(this);
    });

    // Navegación con teclado
    select.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            // Mover al siguiente select de la misma columna
            const currentRow = this.closest('tr');
            const nextRow = currentRow.nextElementSibling;
            if (nextRow) {
                const field = this.dataset.field;
                const nextSelect = nextRow.querySelector(`select[data-field="${field}"]`);
                if (nextSelect) {
                    nextSelect.focus();
                }
            }
        }

        // Mover arriba/abajo con Ctrl+flechas (sin Ctrl cambia el valor)
        if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && e.ctrlKey) {
            e.preventDefault();
            const currentRow = this.closest('tr');
            const targetRow = e.key === 'ArrowDown'
                ? currentRow.nextElementSibling
                : currentRow.previousElementSibling;

            if (targetRow) {
                const field = this.dataset.field;
                const targetSelect = targetRow.querySelector(`select[data-field="${field}"]`);
                if (targetSelect) {
                    targetSelect.focus();
                }
            }
        }
    });
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
