<?php
/**
 * El Celler Nou - Admin Dashboard
 */
require_once __DIR__ . '/includes/auth.php';

// Redirigir usuaris gestoria al seu dashboard
if (getUserRole() === 'gestoria') {
    header('Location: ' . getAdminUrl('modules/gestoria/'));
    exit;
}

$pageTitle = 'Dashboard';

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
    'productos' => 0,
    'clientes' => 0,
    'proveedores' => 0,
    'tiquets_hoy' => 0,
    'ventas_mes' => 0,
    'productos_bajo_stock' => 0
];

// Total productos activos
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM productos WHERE activo = 1");
if ($row) $stats['productos'] = $row['total'];

// Total clientes
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM clientes");
if ($row) $stats['clientes'] = $row['total'];

// Total proveedores
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM proveedores");
if ($row) $stats['proveedores'] = $row['total'];

// Tiquets de hoy
$today = date('Y-m-d');
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM tiquets WHERE DATE(fecha) = '$today'");
if ($row) $stats['tiquets_hoy'] = $row['total'];

// Ventas del mes (suma de linea_tiquet)
$firstDayOfMonth = date('Y-m-01');
$row = safeQuery($bd, "SELECT SUM(cantidad * precio_unidad) as total FROM linea_tiquet WHERE fecha >= '$firstDayOfMonth'");
if ($row) $stats['ventas_mes'] = $row['total'] ?? 0;

// Productos con bajo stock (unidades < 5)
$row = safeQuery($bd, "SELECT COUNT(*) as total FROM productos WHERE activo = 1 AND unidades < 5 AND unidades >= 0");
if ($row) $stats['productos_bajo_stock'] = $row['total'];

// Últimos tiquets
$ultimosTiquets = [];
$result = @mysqli_query($bd, "SELECT t.id, t.fecha, t.precio_desc, c.nombre as cliente
                             FROM tiquets t
                             LEFT JOIN clientes c ON t.idCliente = c.id
                             ORDER BY t.fecha DESC, t.id DESC LIMIT 5");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ultimosTiquets[] = $row;
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['productos']); ?></div>
            <div class="stat-label">Productes Actius</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['clientes']); ?></div>
            <div class="stat-label">Clients</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['proveedores']); ?></div>
            <div class="stat-label">Proveidors</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['tiquets_hoy']); ?></div>
            <div class="stat-label">Tiquets Avui</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['ventas_mes'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Vendes del Mes</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon <?php echo $stats['productos_bajo_stock'] > 0 ? 'warning' : 'success'; ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['productos_bajo_stock']); ?></div>
            <div class="stat-label">Productes Baix Estoc</div>
        </div>
    </div>
</div>

<!-- Modules Grid -->
<h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: var(--gray-900);">Mòduls</h2>
<div class="modules-grid">
    <a href="<?php echo getAdminUrl('modules/estoc/'); ?>" class="module-card">
        <div class="module-icon wine">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <h3 class="module-title">Estoc</h3>
        <p class="module-description">Gestió d'inventari, control d'estoc i moviments de productes.</p>
        <div class="module-meta">
            <span class="module-count"><?php echo number_format($stats['productos']); ?> productes</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/clients/'); ?>" class="module-card">
        <div class="module-icon users">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <h3 class="module-title">Clients</h3>
        <p class="module-description">Gestió de clients, vendes i facturació professional.</p>
        <div class="module-meta">
            <span class="module-count"><?php echo number_format($stats['clientes']); ?> clients</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/proveidors/'); ?>" class="module-card">
        <div class="module-icon truck">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
            </svg>
        </div>
        <h3 class="module-title">Proveidors</h3>
        <p class="module-description">Gestió de proveïdors, comandes i factures de compra.</p>
        <div class="module-meta">
            <span class="module-count"><?php echo number_format($stats['proveedores']); ?> proveidors</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/tiquets/'); ?>" class="module-card">
        <div class="module-icon ticket">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
        </div>
        <h3 class="module-title">Tiquets</h3>
        <p class="module-description">Gestió de vendes al detall, tiquets de caixa i TPV.</p>
        <div class="module-meta">
            <span class="module-count"><?php echo number_format($stats['tiquets_hoy']); ?> avui</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/database/'); ?>" class="module-card">
        <div class="module-icon database">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
            </svg>
        </div>
        <h3 class="module-title">Base de Dades</h3>
        <p class="module-description">Gestió de sectors, tipus de producte i procedències.</p>
        <div class="module-meta">
            <span class="module-count">Configuració</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/estadistiques/'); ?>" class="module-card">
        <div class="module-icon chart">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <h3 class="module-title">Estadístiques</h3>
        <p class="module-description">Informes de vendes, facturació, marges i anàlisi.</p>
        <div class="module-meta">
            <span class="module-count">Informes</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/ocr/'); ?>" class="module-card">
        <div class="module-icon database">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="module-title">OCR Factures</h3>
        <p class="module-description">Reconeixement automàtic de factures de proveïdors amb OCR.</p>
        <div class="module-meta">
            <span class="module-count">Comptabilitat</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/control/'); ?>" class="module-card">
        <div class="module-icon settings">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h3 class="module-title">Control</h3>
        <p class="module-description">Configuració del sistema, usuaris i registre d'activitat.</p>
        <div class="module-meta">
            <span class="module-count">Sistema</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>
</div>

<!-- Recent Tickets -->
<?php if (!empty($ultimosTiquets)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Últims Tiquets</h3>
        <a href="<?php echo getAdminUrl('modules/tiquets/'); ?>" class="btn btn-sm btn-secondary">Veure tots</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Client</th>
                        <th>Import</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosTiquets as $tiquet): ?>
                    <tr>
                        <td><strong>#<?php echo $tiquet['id']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($tiquet['fecha'])); ?></td>
                        <td><?php echo e($tiquet['cliente'] ?? 'Client general'); ?></td>
                        <td><strong><?php echo number_format($tiquet['precio_desc'] ?? 0, 2, ',', '.'); ?> &euro;</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
