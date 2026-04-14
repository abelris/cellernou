<?php
/**
 * El Celler Nou - Mòdul Proveidors
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Proveidors';

// Función helper para consultas seguras
function safeQuery($bd, $sql) {
    $result = @mysqli_query($bd, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    return null;
}

// Obtener estadísticas
$stats = [
    'total_proveedores' => 0,
    'compras_mes' => 0,
    'facturas_pendientes' => 0
];

// Total proveedores
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM proveedores");
if ($row) $stats['total_proveedores'] = $row['total'];

// Compras del mes
$firstDayOfMonth = date('Y-m-01');
$row = safeQuery($bd, "SELECT SUM(cantidad * precio_unidad) as total FROM linea_compra WHERE fecha >= '$firstDayOfMonth'");
if ($row) $stats['compras_mes'] = $row['total'] ?? 0;

// Facturas pendientes de pago
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM facturas_compra WHERE idEstado = 1");
if ($row) $stats['facturas_pendientes'] = $row['total'];

// Últimos proveedores
$ultimosProveedores = [];
$result = @mysqli_query($bd, "SELECT id, nombre, telefono, email
                             FROM proveedores
                             ORDER BY id DESC LIMIT 10");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ultimosProveedores[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total_proveedores']); ?></div>
            <div class="stat-label">Total Proveidors</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['compras_mes'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Compres del Mes</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['facturas_pendientes']); ?></div>
            <div class="stat-label">Factures Pendents</div>
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
            <a href="<?php echo getBaseUrl(); ?>proveidors/" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Llistat Proveidors
            </a>
            <a href="<?php echo getAdminUrl('modules/compres/'); ?>" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Compres
            </a>
            <a href="<?php echo getAdminUrl('modules/factures_compra/'); ?>" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Factures Compra
            </a>
        </div>
    </div>
</div>

<!-- Recent Proveedores -->
<?php if (!empty($ultimosProveedores)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Proveidors</h3>
        <a href="<?php echo getBaseUrl(); ?>proveidors/" class="btn btn-sm btn-secondary">Veure tots</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Telèfon</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosProveedores as $proveedor): ?>
                    <tr>
                        <td><strong>#<?php echo $proveedor['id']; ?></strong></td>
                        <td>
                            <a href="<?php echo getBaseUrl(); ?>proveidors/modificar.php?id=<?php echo $proveedor['id']; ?>">
                                <?php echo e($proveedor['nombre']); ?>
                            </a>
                        </td>
                        <td><?php echo e($proveedor['telefono'] ?: '-'); ?></td>
                        <td><?php echo e($proveedor['email'] ?: '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
