<?php
/**
 * El Celler Nou - Mòdul Estoc
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Estoc';

// Función helper para consultas seguras
function safeQuery($bd, $sql) {
    $result = @mysqli_query($bd, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    return null;
}

// Precomputed stock subquery (single GROUP BY scan, avoids N+1 correlated subqueries)
$stockSub = "(SELECT idProducto, COUNT(*) as cnt FROM stock_producto WHERE vendido = 0 GROUP BY idProducto)";

// All stats in a single query (was 5 separate queries with correlated subqueries)
$statsResult = @mysqli_query($bd, "
    SELECT
        SUM(CASE WHEN p.activo = 1 THEN 1 ELSE 0 END) as total_activos,
        SUM(CASE WHEN p.activo = 0 THEN 1 ELSE 0 END) as total_inactivos,
        SUM(CASE WHEN p.activo = 1 AND COALESCE(sp.cnt, 0) > 0 THEN 1 ELSE 0 END) as productos_con_stock,
        SUM(CASE WHEN p.activo = 1 AND COALESCE(sp.cnt, 0) = 0 THEN 1 ELSE 0 END) as productos_sin_stock,
        SUM(CASE WHEN p.activo = 1 AND COALESCE(sp.cnt, 0) BETWEEN 1 AND 4 THEN 1 ELSE 0 END) as productos_bajo_stock
    FROM productos p
    LEFT JOIN $stockSub sp ON sp.idProducto = p.id");
$statsRow = $statsResult ? mysqli_fetch_assoc($statsResult) : [];

// Inventory cost value (sum of purchase prices from stock_producto)
$valRow = safeQuery($bd, "SELECT SUM(sp.precio_compra) as total FROM stock_producto sp INNER JOIN productos p ON sp.idProducto = p.id WHERE sp.vendido = 0 AND p.activo = 1");

// Inventory sale value (sum of PVP * units in stock for each product)
$valVentaRow = safeQuery($bd, "SELECT SUM(COALESCE(ppt.pvp, 0) * sp.cnt) as total
    FROM $stockSub sp
    INNER JOIN productos p ON sp.idProducto = p.id
    LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
    WHERE p.activo = 1");

$stats = [
    'total_activos' => $statsRow['total_activos'] ?? 0,
    'total_inactivos' => $statsRow['total_inactivos'] ?? 0,
    'productos_con_stock' => $statsRow['productos_con_stock'] ?? 0,
    'productos_sin_stock' => $statsRow['productos_sin_stock'] ?? 0,
    'productos_bajo_stock' => $statsRow['productos_bajo_stock'] ?? 0,
    'valor_coste' => $valRow['total'] ?? 0,
    'valor_venta' => $valVentaRow['total'] ?? 0
];

// Products with low stock (using JOIN instead of correlated subquery)
$productosBajoStock = [];
$result = @mysqli_query($bd, "SELECT p.id, p.codigo, p.nombre, p.precio_medio,
                                    s.nombre_es as sector, tp.nombre_es as tipo,
                                    COALESCE(sp.cnt, 0) as unidades
                             FROM productos p
                             LEFT JOIN sectores s ON p.idSector = s.id
                             LEFT JOIN tipo_producto tp ON p.idTipo = tp.id
                             LEFT JOIN $stockSub sp ON sp.idProducto = p.id
                             WHERE p.activo = 1
                             HAVING unidades > 0 AND unidades < 5
                             ORDER BY unidades ASC
                             LIMIT 20");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $productosBajoStock[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total_activos']); ?></div>
            <div class="stat-label">Productes Actius</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total_inactivos']); ?></div>
            <div class="stat-label">Productes Inactius</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['productos_con_stock']); ?></div>
            <div class="stat-label">Actius amb Estoc</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['productos_sin_stock']); ?></div>
            <div class="stat-label">Actius sense Estoc</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['productos_bajo_stock']); ?></div>
            <div class="stat-label">Baix Estoc (&lt;5)</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['valor_coste'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Valor Cost</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['valor_venta'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Valor Venda</div>
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
            <a href="<?php echo getAdminUrl('modules/productes/'); ?>" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Llistat Productes
            </a>
            <a href="<?php echo getAdminUrl('modules/productes/inventari.php'); ?>" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Inventari
            </a>
            <a href="<?php echo getBaseUrl(); ?>productes/stock/alta.php" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Moviment d'Estoc
            </a>
            <a href="<?php echo getBaseUrl(); ?>productes/sin_precio_compra.php" class="btn btn-outline">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Sense Preu Compra
            </a>
        </div>
    </div>
</div>

<!-- Low Stock Products -->
<?php if (!empty($productosBajoStock)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Productes amb Baix Estoc</h3>
        <span class="badge badge-warning"><?php echo count($productosBajoStock); ?> productes</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Codi</th>
                        <th>Producte</th>
                        <th>Sector</th>
                        <th>Tipus</th>
                        <th>Estoc</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productosBajoStock as $producto): ?>
                    <tr>
                        <td><code><?php echo e($producto['codigo']); ?></code></td>
                        <td>
                            <a href="<?php echo getAdminUrl('modules/productes/modificar.php?id=' . $producto['id']); ?>">
                                <?php echo e($producto['nombre']); ?>
                            </a>
                        </td>
                        <td><?php echo e($producto['sector'] ?? '-'); ?></td>
                        <td><?php echo e($producto['tipo'] ?? '-'); ?></td>
                        <td>
                            <span class="badge <?php echo $producto['unidades'] <= 2 ? 'badge-danger' : 'badge-warning'; ?>">
                                <?php echo $producto['unidades']; ?>
                            </span>
                        </td>
                        <td><?php echo number_format($producto['unidades'] * $producto['precio_medio'], 2, ',', '.'); ?> &euro;</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="empty-state-title">Tot correcte!</h3>
            <p class="empty-state-text">No hi ha productes amb estoc baix.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Productes Duplicats per Nom -->
<?php
$duplicats = [];
$rDup = @mysqli_query($bd, "
    SELECT p.nombre, GROUP_CONCAT(p.id ORDER BY p.id) as ids,
           GROUP_CONCAT(p.activo ORDER BY p.id) as actius,
           GROUP_CONCAT(COALESCE(sp.cnt, 0) ORDER BY p.id) as stocks,
           GROUP_CONCAT(COALESCE(s.nombre_es, '-') ORDER BY p.id SEPARATOR ' | ') as sectors,
           COUNT(*) as total
    FROM productos p
    LEFT JOIN (SELECT idProducto, COUNT(*) as cnt FROM stock_producto WHERE vendido = 0 GROUP BY idProducto) sp ON sp.idProducto = p.id
    LEFT JOIN sectores s ON p.idSector = s.id
    WHERE p.activo = 1
    GROUP BY UPPER(TRIM(p.nombre))
    HAVING total > 1
    ORDER BY total DESC, p.nombre
");
if ($rDup) {
    while ($row = mysqli_fetch_assoc($rDup)) {
        $duplicats[] = $row;
    }
}
?>

<div class="card" style="margin-top:1.5rem;">
    <div class="card-header">
        <h3 class="card-title">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;vertical-align:middle;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Productes Duplicats per Nom (<?php echo count($duplicats); ?>)
        </h3>
    </div>
    <?php if (!empty($duplicats)): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>IDs</th>
                    <th>Stock</th>
                    <th>Sectors</th>
                    <th>Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($duplicats as $dup):
                    $ids = explode(',', $dup['ids']);
                    $actius = explode(',', $dup['actius']);
                    $stocks = explode(',', $dup['stocks']);
                ?>
                <tr>
                    <td><strong><?php echo e($dup['nombre']); ?></strong> <span style="color:var(--gray-400);">(<?php echo $dup['total']; ?>x)</span></td>
                    <td>
                        <?php foreach ($ids as $i => $pid): ?>
                        <a href="<?php echo getAdminUrl('modules/productes/modificar.php?id=' . $pid); ?>" target="_blank" style="display:inline-block;margin:2px;padding:2px 8px;background:var(--gray-100);border-radius:4px;font-size:0.8rem;">
                            #<?php echo $pid; ?>
                        </a>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <?php foreach ($stocks as $i => $stk): ?>
                        <span style="display:inline-block;margin:2px;padding:2px 8px;border-radius:4px;font-size:0.8rem;background:<?php echo $stk > 0 ? '#dcfce7' : '#f3f4f6'; ?>;color:<?php echo $stk > 0 ? '#166534' : '#6b7280'; ?>;">
                            <?php echo $stk; ?>
                        </span>
                        <?php endforeach; ?>
                    </td>
                    <td style="font-size:0.8rem;"><?php echo e($dup['sectors']); ?></td>
                    <td>
                        <?php
                        // Suggest deactivating IDs with 0 stock (keep the one with stock or lowest ID)
                        $keepId = null;
                        foreach ($ids as $i => $pid) {
                            if ((int)$stocks[$i] > 0) { $keepId = $pid; break; }
                        }
                        if (!$keepId) $keepId = $ids[0]; // keep first if none has stock
                        $deactivateIds = array_filter($ids, function($pid) use ($keepId) { return $pid != $keepId; });
                        ?>
                        <?php foreach ($deactivateIds as $delId): ?>
                        <button class="btn btn-danger btn-sm" onclick="desactivarDuplicat(<?php echo $delId; ?>, this)"
                                title="Desactivar #<?php echo $delId; ?>" style="margin:2px;padding:2px 8px;font-size:0.75rem;">
                            Desactivar #<?php echo $delId; ?>
                        </button>
                        <?php endforeach; ?>
                        <span style="font-size:0.7rem;color:var(--gray-400);">manté #<?php echo $keepId; ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="card-body">
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="empty-state-title">Cap duplicat!</h3>
            <p class="empty-state-text">No s'han trobat productes actius amb el mateix nom.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function desactivarDuplicat(id, btn) {
    if (!confirm('Desactivar producte #' + id + '? El producte deixarà de ser visible a la web.')) return;
    btn.disabled = true;
    btn.textContent = '...';

    var fd = new FormData();
    fd.append('id', id);
    fd.append('csrf_token', '<?php echo generateCsrfToken(); ?>');

    fetch('<?php echo getAdminUrl("modules/estoc/"); ?>ajax_desactivar.php', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            btn.closest('tr').style.opacity = '0.3';
            btn.textContent = 'Desactivat';
            btn.style.background = '#9ca3af';
        } else {
            alert('Error: ' + (data.error || 'Desconegut'));
            btn.disabled = false;
            btn.textContent = 'Desactivar #' + id;
        }
    })
    .catch(function() {
        alert('Error de connexió');
        btn.disabled = false;
        btn.textContent = 'Desactivar #' + id;
    });
}
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
