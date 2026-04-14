<?php
/**
 * El Celler Nou - Mòdul Base de Dades
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Base de Dades';

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
    'sectores' => 0,
    'tipos' => 0,
    'procedencias' => 0,
    'total_productos' => 0
];

// Sectores
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM sectores");
if ($row) $stats['sectores'] = $row['total'];

// Tipos de producto
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM tipo_producto");
if ($row) $stats['tipos'] = $row['total'];

// Procedencias
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM procedencia");
if ($row) $stats['procedencias'] = $row['total'];

// Total productos
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM productos WHERE activo = 1");
if ($row) $stats['total_productos'] = $row['total'];

// Sectores con productos
$sectoresConProductos = [];
$result = @mysqli_query($bd, "SELECT s.id, s.nombre_es, COUNT(p.id) as total_productos
                             FROM sectores s
                             LEFT JOIN productos p ON p.idSector = s.id AND p.activo = 1
                             GROUP BY s.id
                             ORDER BY total_productos DESC
                             LIMIT 10");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sectoresConProductos[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['sectores']); ?></div>
            <div class="stat-label">Sectors</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['tipos']); ?></div>
            <div class="stat-label">Tipus de Producte</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['procedencias']); ?></div>
            <div class="stat-label">Procedències / D.O.</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['total_productos']); ?></div>
            <div class="stat-label">Productes Actius</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Gestió de Taules</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo getAdminUrl('modules/database/sectors/'); ?>" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Sectors
            </a>
            <a href="<?php echo getAdminUrl('modules/database/tipus/'); ?>" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Tipus de Producte
            </a>
            <a href="<?php echo getAdminUrl('modules/database/procedencies/'); ?>" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Procedències
            </a>
        </div>
    </div>
</div>

<!-- Sectors Overview -->
<?php if (!empty($sectoresConProductos)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Productes per Sector</h3>
        <a href="<?php echo getAdminUrl('modules/database/sectors/'); ?>" class="btn btn-sm btn-secondary">Gestionar Sectors</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sector</th>
                        <th>Productes</th>
                        <th>Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sectoresConProductos as $sector): ?>
                    <tr>
                        <td><strong>#<?php echo $sector['id']; ?></strong></td>
                        <td><?php echo e($sector['nombre_es']); ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo number_format($sector['total_productos']); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo getAdminUrl('modules/database/sectors/modificar.php?id=' . $sector['id']); ?>" class="btn btn-sm btn-secondary">
                                Editar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
