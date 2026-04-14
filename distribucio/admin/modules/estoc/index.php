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

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
