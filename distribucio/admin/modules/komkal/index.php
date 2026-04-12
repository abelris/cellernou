<?php
/**
 * El Celler Nou - Mòdul Komkal - Dashboard
 */
$pageTitle = 'Komkal - Importació de Preus';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/config.php';
requireAuth();

// Stats: komkal_vinos
$statsVinos = ['total' => 0, 'matched' => 0, 'disponible' => 0];
$r = mysqli_query($bd, "SELECT COUNT(*) as total,
    SUM(CASE WHEN id_producto_match IS NOT NULL THEN 1 ELSE 0 END) as matched,
    SUM(CASE WHEN disponible = 1 THEN 1 ELSE 0 END) as disponible
    FROM komkal_vinos");
if ($r && $row = mysqli_fetch_assoc($r)) {
    $statsVinos = $row;
}

// Stats: komkal_licores
$statsLicores = ['total' => 0, 'matched' => 0, 'disponible' => 0];
$r = mysqli_query($bd, "SELECT COUNT(*) as total,
    SUM(CASE WHEN id_producto_match IS NOT NULL THEN 1 ELSE 0 END) as matched,
    SUM(CASE WHEN disponible = 1 THEN 1 ELSE 0 END) as disponible
    FROM komkal_licores");
if ($r && $row = mysqli_fetch_assoc($r)) {
    $statsLicores = $row;
}

// Price diff counts
$diffVinos = 0;
$r = mysqli_query($bd, "SELECT COUNT(*) as cnt FROM komkal_vinos kv
    JOIN productos p ON kv.id_producto_match = p.id
    WHERE kv.id_producto_match IS NOT NULL
    AND kv.precio_sin_iva IS NOT NULL
    AND ABS(kv.precio_sin_iva - p.precio_medio) > 0.01");
if ($r && $row = mysqli_fetch_assoc($r)) {
    $diffVinos = (int)$row['cnt'];
}

$diffLicores = 0;
$r = mysqli_query($bd, "SELECT COUNT(*) as cnt FROM komkal_licores kl
    JOIN productos p ON kl.id_producto_match = p.id
    WHERE kl.id_producto_match IS NOT NULL
    AND kl.precio_sin_iva IS NOT NULL
    AND ABS(kl.precio_sin_iva - p.precio_medio) > 0.01");
if ($r && $row = mysqli_fetch_assoc($r)) {
    $diffLicores = (int)$row['cnt'];
}

// Recent import batches
$batches = [];
$r = mysqli_query($bd, "SELECT lote, MIN(fecha_importacion) as fecha, COUNT(*) as total, 'vinos' as taula
    FROM komkal_vinos GROUP BY lote
    UNION ALL
    SELECT lote, MIN(fecha_importacion) as fecha, COUNT(*) as total, 'licores' as taula
    FROM komkal_licores GROUP BY lote
    ORDER BY fecha DESC LIMIT 20");
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) {
        $batches[] = $row;
    }
}

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<!-- Stats cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format((int)$statsVinos['total']); ?></div>
        <div class="stat-label">Vins Komkal</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format((int)$statsVinos['matched']); ?></div>
        <div class="stat-label">Vins Vinculats</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format((int)$statsLicores['total']); ?></div>
        <div class="stat-label">Licors Komkal</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo number_format((int)$statsLicores['matched']); ?></div>
        <div class="stat-label">Licors Vinculats</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: var(--warning, #f59e0b);"><?php echo number_format($diffVinos + $diffLicores); ?></div>
        <div class="stat-label">Preus Diferents</div>
    </div>
</div>

<!-- Quick actions -->
<div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <a href="<?php echo getAdminUrl('modules/komkal/importar.php'); ?>" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>
        Importar XLSX
    </a>
    <a href="<?php echo getAdminUrl('modules/komkal/matching.php?taula=vinos'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        Matching Vins
    </a>
    <a href="<?php echo getAdminUrl('modules/komkal/matching.php?taula=licores'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        Matching Licors
    </a>
</div>

<!-- Summary per table -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Vinos summary -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Vins</h3>
        </div>
        <div class="card-body">
            <table style="width:100%; font-size:0.875rem;">
                <tr><td>Total importats</td><td style="text-align:right; font-weight:600;"><?php echo number_format((int)$statsVinos['total']); ?></td></tr>
                <tr><td>Vinculats</td><td style="text-align:right; font-weight:600; color:#16a34a;"><?php echo number_format((int)$statsVinos['matched']); ?></td></tr>
                <tr><td>Pendents</td><td style="text-align:right; font-weight:600; color:#dc2626;"><?php echo number_format((int)$statsVinos['total'] - (int)$statsVinos['matched']); ?></td></tr>
                <tr><td>Disponibles</td><td style="text-align:right; font-weight:600;"><?php echo number_format((int)$statsVinos['disponible']); ?></td></tr>
                <tr><td>Preu diferent</td><td style="text-align:right; font-weight:600; color:#f59e0b;"><?php echo number_format($diffVinos); ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Licores summary -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Licors</h3>
        </div>
        <div class="card-body">
            <table style="width:100%; font-size:0.875rem;">
                <tr><td>Total importats</td><td style="text-align:right; font-weight:600;"><?php echo number_format((int)$statsLicores['total']); ?></td></tr>
                <tr><td>Vinculats</td><td style="text-align:right; font-weight:600; color:#16a34a;"><?php echo number_format((int)$statsLicores['matched']); ?></td></tr>
                <tr><td>Pendents</td><td style="text-align:right; font-weight:600; color:#dc2626;"><?php echo number_format((int)$statsLicores['total'] - (int)$statsLicores['matched']); ?></td></tr>
                <tr><td>Disponibles</td><td style="text-align:right; font-weight:600;"><?php echo number_format((int)$statsLicores['disponible']); ?></td></tr>
                <tr><td>Preu diferent</td><td style="text-align:right; font-weight:600; color:#f59e0b;"><?php echo number_format($diffLicores); ?></td></tr>
            </table>
        </div>
    </div>
</div>

<!-- Recent batches -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lots Recents</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($batches)): ?>
        <div style="padding:2rem; text-align:center; color:var(--gray-500);">
            No hi ha importacions. <a href="<?php echo getAdminUrl('modules/komkal/importar.php'); ?>">Importa el primer fitxer XLSX</a>.
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Lot</th>
                        <th>Taula</th>
                        <th style="text-align:right;">Registres</th>
                        <th>Data Importació</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($batches as $b): ?>
                    <tr>
                        <td><strong><?php echo e($b['lote']); ?></strong></td>
                        <td>
                            <?php if ($b['taula'] === 'vinos'): ?>
                            <span class="badge badge-info">Vins</span>
                            <?php else: ?>
                            <span class="badge badge-warning">Licors</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;"><?php echo number_format((int)$b['total']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($b['fecha'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
