<?php
/**
 * El Celler Nou - Mòdul Control
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Control';

// Función helper para consultas seguras
function safeQuery($bd, $sql) {
    $result = @mysqli_query($bd, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    return null;
}

// Obtener estadísticas del sistema
$stats = [
    'usuarios' => 0,
    'logs_hoy' => 0,
    'productos_inactivos' => 0,
    'ultimo_backup' => 'Desconocido'
];

// Usuarios
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM usuarios");
if ($row) $stats['usuarios'] = $row['total'];

// Logs de hoy
$today = date('Y-m-d');
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM admin_logs WHERE DATE(created_at) = '$today'");
if ($row) $stats['logs_hoy'] = $row['total'];

// Productos inactivos
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM productos WHERE activo = 0");
if ($row) $stats['productos_inactivos'] = $row['total'];

// Últimos usuarios
$usuarios = [];
$result = @mysqli_query($bd, "SELECT id, nombre, usuario FROM usuarios ORDER BY id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $usuarios[] = $row;
    }
}

// Últimos logs
$ultimosLogs = [];
$result = @mysqli_query($bd, "SELECT al.*, u.nombre as usuario_nombre
                             FROM admin_logs al
                             LEFT JOIN usuarios u ON al.user_id = u.id
                             ORDER BY al.created_at DESC LIMIT 20");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ultimosLogs[] = $row;
    }
}

// Información del sistema
$phpVersion = phpversion();
$mysqlVersion = mysqli_get_server_info($bd);
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido';

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['usuarios']); ?></div>
            <div class="stat-label">Usuaris</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['logs_hoy']); ?></div>
            <div class="stat-label">Logs Avui</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['productos_inactivos']); ?></div>
            <div class="stat-label">Productes Inactius</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value">OK</div>
            <div class="stat-label">Estat Sistema</div>
        </div>
    </div>
</div>

<!-- System Info -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Informació del Sistema</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">PHP</strong>
                <p style="margin: 0.25rem 0 0; font-size: 1rem;"><?php echo e($phpVersion); ?></p>
            </div>
            <div>
                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">MySQL</strong>
                <p style="margin: 0.25rem 0 0; font-size: 1rem;"><?php echo e($mysqlVersion); ?></p>
            </div>
            <div>
                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Servidor</strong>
                <p style="margin: 0.25rem 0 0; font-size: 1rem;"><?php echo e($serverSoftware); ?></p>
            </div>
            <div>
                <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Hora Servidor</strong>
                <p style="margin: 0.25rem 0 0; font-size: 1rem;"><?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Accions</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo getBaseUrl(); ?>administracio/pagos/" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Gestió Pagaments
            </a>
            <a href="<?php echo getBaseUrl(); ?>backup.php" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Crear Backup
            </a>
            <a href="<?php echo getBaseUrl(); ?>productes/actualizaStats.php" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Actualitzar Stats
            </a>
            <a href="<?php echo getBaseUrl(); ?>productes/merce-llull.php" class="btn btn-outline">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Actualitzar Preus
            </a>
        </div>
    </div>
</div>

<!-- Users -->
<?php if (!empty($usuarios)): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Usuaris del Sistema</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Usuari</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><strong>#<?php echo $usuario['id']; ?></strong></td>
                        <td><?php echo e($usuario['nombre']); ?></td>
                        <td><code><?php echo e($usuario['usuario']); ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Activity Logs -->
<?php if (!empty($ultimosLogs)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Registre d'Activitat</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuari</th>
                        <th>Acció</th>
                        <th>Detalls</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosLogs as $log): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                        <td><?php echo e($log['usuario_nombre'] ?? 'Sistema'); ?></td>
                        <td>
                            <?php
                            $actionClass = 'badge-info';
                            if ($log['action'] == 'login') $actionClass = 'badge-success';
                            elseif ($log['action'] == 'logout') $actionClass = 'badge-warning';
                            elseif (strpos($log['action'], 'delete') !== false) $actionClass = 'badge-danger';
                            ?>
                            <span class="badge <?php echo $actionClass; ?>"><?php echo e($log['action']); ?></span>
                        </td>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?php echo e($log['details'] ?? '-'); ?>
                        </td>
                        <td><code><?php echo e($log['ip_address']); ?></code></td>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="empty-state-title">Sense registres</h3>
            <p class="empty-state-text">No hi ha activitat registrada encara.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
