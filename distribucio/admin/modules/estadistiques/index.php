<?php
/**
 * El Celler Nou - Mòdul Estadístiques
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Estadístiques';

// Función helper para consultas seguras
function safeQuery($bd, $sql) {
    $result = @mysqli_query($bd, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    return null;
}

// Obtener estadísticas del mes actual
$stats = [
    'facturacion_mes' => 0,
    'facturacion_anterior' => 0,
    'cobros_mes' => 0,
    'margen_promedio' => 0
];

$firstDayOfMonth = date('Y-m-01');
$firstDayOfLastMonth = date('Y-m-01', strtotime('-1 month'));
$lastDayOfLastMonth = date('Y-m-t', strtotime('-1 month'));

// Facturación del mes actual (desde linea_tiquet)
$row = safeQuery($bd, "SELECT SUM(cantidad * precio_unidad) as total FROM linea_tiquet WHERE fecha >= '$firstDayOfMonth'");
if ($row) $stats['facturacion_mes'] = $row['total'] ?? 0;

// Facturación del mes anterior
$row = safeQuery($bd, "SELECT SUM(cantidad * precio_unidad) as total FROM linea_tiquet WHERE fecha >= '$firstDayOfLastMonth' AND fecha <= '$lastDayOfLastMonth'");
if ($row) $stats['facturacion_anterior'] = $row['total'] ?? 0;

// Cobros del mes (desde pagos si existe)
$row = safeQuery($bd, "SELECT SUM(importe) as total FROM pagos WHERE fecha >= '$firstDayOfMonth'");
if ($row) $stats['cobros_mes'] = $row['total'] ?? 0;

// Calcular variación
$variacion = 0;
if ($stats['facturacion_anterior'] > 0) {
    $variacion = (($stats['facturacion_mes'] - $stats['facturacion_anterior']) / $stats['facturacion_anterior']) * 100;
}

// Ventas por día del mes actual
$ventasPorDia = [];
$result = @mysqli_query($bd, "SELECT DATE(fecha) as dia, SUM(cantidad * precio_unidad) as total
                             FROM linea_tiquet
                             WHERE fecha >= '$firstDayOfMonth'
                             GROUP BY DATE(fecha)
                             ORDER BY dia");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ventasPorDia[] = $row;
    }
}

// Top productos
$topProductos = [];
$result = @mysqli_query($bd, "SELECT p.nombre, SUM(lt.cantidad) as unidades, SUM(lt.cantidad * lt.precio_unidad) as total
                             FROM linea_tiquet lt
                             INNER JOIN productos p ON lt.idProducto = p.id
                             WHERE lt.fecha >= '$firstDayOfMonth'
                             GROUP BY p.id
                             ORDER BY total DESC
                             LIMIT 10");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $topProductos[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['facturacion_mes'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Facturació del Mes</div>
            <?php if ($variacion != 0): ?>
            <div class="stat-change <?php echo $variacion > 0 ? 'up' : 'down'; ?>">
                <?php echo $variacion > 0 ? '+' : ''; ?><?php echo number_format($variacion, 1); ?>% vs mes anterior
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['facturacion_anterior'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Mes Anterior</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['cobros_mes'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Cobraments del Mes</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo count($ventasPorDia); ?></div>
            <div class="stat-label">Dies amb Vendes</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Informes</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo getBaseUrl(); ?>analitica/facturacio/" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Facturació
            </a>
            <a href="<?php echo getBaseUrl(); ?>analitica/cobros/" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Cobraments
            </a>
            <a href="<?php echo getBaseUrl(); ?>analitica/granel/" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Granel
            </a>
            <a href="<?php echo getBaseUrl(); ?>analitica/marges/" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Marges
            </a>
            <a href="<?php echo getBaseUrl(); ?>productes/topVentas.php" class="btn btn-outline">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                Top Vendes
            </a>
        </div>
    </div>
</div>

<!-- Top Products -->
<?php if (!empty($topProductos)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Top 10 Productes del Mes</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producte</th>
                        <th>Unitats</th>
                        <th>Import</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $pos = 1; foreach ($topProductos as $producto): ?>
                    <tr>
                        <td>
                            <span class="badge <?php echo $pos <= 3 ? 'badge-success' : 'badge-info'; ?>">
                                <?php echo $pos; ?>
                            </span>
                        </td>
                        <td><?php echo e($producto['nombre']); ?></td>
                        <td><?php echo number_format($producto['unidades']); ?></td>
                        <td><strong><?php echo number_format($producto['total'], 2, ',', '.'); ?> &euro;</strong></td>
                    </tr>
                    <?php $pos++; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
