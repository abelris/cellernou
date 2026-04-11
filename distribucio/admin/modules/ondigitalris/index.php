<?php
/**
 * OnDigitalRis - Dashboard
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

$pageTitle = 'OnDigitalRis - Dashboard';

// Función helper para consultas seguras
function odrSafeQuery($bd, $sql) {
    $result = @mysqli_query($bd, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    return null;
}

// Obtener estadísticas
$stats = [
    'clientes' => 0,
    'proveedores' => 0,
    'facturas_venda_mes' => 0,
    'facturas_compra_mes' => 0,
    'facturacio_venda_mes' => 0,
    'facturacio_compra_mes' => 0
];

$firstDayOfMonth = date('Y-m-01');

// Total clients
$row = odrSafeQuery($bd, "SELECT COUNT(*) as total FROM odr_clientes");
if ($row) $stats['clientes'] = $row['total'];

// Total proveedores
$row = odrSafeQuery($bd, "SELECT COUNT(*) as total FROM odr_proveedores");
if ($row) $stats['proveedores'] = $row['total'];

// Factures venda del mes
$row = odrSafeQuery($bd, "SELECT COUNT(*) as total FROM odr_facturas WHERE fecha >= '$firstDayOfMonth'");
if ($row) $stats['facturas_venda_mes'] = $row['total'];

// Factures compra del mes
$row = odrSafeQuery($bd, "SELECT COUNT(*) as total FROM odr_facturas_compra WHERE fecha >= '$firstDayOfMonth'");
if ($row) $stats['facturas_compra_mes'] = $row['total'];

// Facturació venda mes
$row = odrSafeQuery($bd, "SELECT SUM(lf.cantidad * lf.precio_unidad) as total
    FROM odr_linea_factura lf
    INNER JOIN odr_facturas f ON lf.idFactura = f.id
    WHERE f.fecha >= '$firstDayOfMonth'");
if ($row) $stats['facturacio_venda_mes'] = $row['total'] ?? 0;

// Facturació compra mes
$row = odrSafeQuery($bd, "SELECT SUM(lf.cantidad * lf.precio_unidad) as total
    FROM odr_linea_factura_compra lf
    INNER JOIN odr_facturas_compra f ON lf.idFactura = f.id
    WHERE f.fecha >= '$firstDayOfMonth'");
if ($row) $stats['facturacio_compra_mes'] = $row['total'] ?? 0;

// Últimas 5 facturas de venta
$ultimasFacturas = [];
$result = @mysqli_query($bd, "SELECT f.id, f.numero, f.fecha, f.estado, c.nombre as cliente,
       (SELECT SUM(lf.cantidad * lf.precio_unidad) FROM odr_linea_factura lf WHERE lf.idFactura = f.id) as total
FROM odr_facturas f
LEFT JOIN odr_clientes c ON f.idCliente = c.id
ORDER BY f.fecha DESC, f.id DESC LIMIT 5");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ultimasFacturas[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['clientes']); ?></div>
            <div class="stat-label">Total Clients</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['proveedores']); ?></div>
            <div class="stat-label">Total Proveidors</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['facturas_venda_mes']); ?></div>
            <div class="stat-label">Factures Venda del Mes</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['facturas_compra_mes']); ?></div>
            <div class="stat-label">Factures Compra del Mes</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['facturacio_venda_mes'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Facturaci&oacute; Venda Mes</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($stats['facturacio_compra_mes'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Facturaci&oacute; Compra Mes</div>
        </div>
    </div>
</div>

<!-- Modules Grid -->
<h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: var(--gray-900);">M&ograve;duls</h2>
<div class="modules-grid">
    <a href="<?php echo getAdminUrl('modules/odr_clients/'); ?>" class="module-card">
        <div class="module-icon users">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <h3 class="module-title">Clients</h3>
        <p class="module-description">Gesti&oacute; de clients d'OnDigitalRis.</p>
        <div class="module-meta">
            <span class="module-count"><?php echo number_format($stats['clientes']); ?> clients</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/odr_proveidors/'); ?>" class="module-card">
        <div class="module-icon truck">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"/>
            </svg>
        </div>
        <h3 class="module-title">Proveidors</h3>
        <p class="module-description">Gesti&oacute; de prove&iuml;dors d'OnDigitalRis.</p>
        <div class="module-meta">
            <span class="module-count"><?php echo number_format($stats['proveedores']); ?> proveidors</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/odr_facturas/'); ?>" class="module-card">
        <div class="module-icon database">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="module-title">Factures Venda</h3>
        <p class="module-description">Facturaci&oacute; de venda a clients.</p>
        <div class="module-meta">
            <span class="module-count"><?php echo number_format($stats['facturas_venda_mes']); ?> aquest mes</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>

    <a href="<?php echo getAdminUrl('modules/odr_factures_compra/'); ?>" class="module-card">
        <div class="module-icon database">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="module-title">Factures Compra</h3>
        <p class="module-description">Factures de compra de prove&iuml;dors.</p>
        <div class="module-meta">
            <span class="module-count"><?php echo number_format($stats['facturas_compra_mes']); ?> aquest mes</span>
            <span class="module-arrow">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </a>
</div>

<!-- Recent Sales Invoices -->
<?php if (!empty($ultimasFacturas)): ?>
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">&Uacute;ltimes Factures de Venda</h3>
        <a href="<?php echo getAdminUrl('modules/odr_facturas/'); ?>" class="btn btn-sm btn-secondary">Veure totes</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>N&uacute;mero</th>
                        <th>Data</th>
                        <th>Client</th>
                        <th>Import</th>
                        <th>Estat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimasFacturas as $factura): ?>
                    <tr>
                        <td><strong><?php echo e($factura['numero'] ?? '#' . $factura['id']); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($factura['fecha'])); ?></td>
                        <td><?php echo e($factura['cliente'] ?? 'Client general'); ?></td>
                        <td><strong><?php echo number_format($factura['total'] ?? 0, 2, ',', '.'); ?> &euro;</strong></td>
                        <td>
                            <?php
                            $estado = $factura['estado'] ?? 'pendent';
                            $badgeClass = ($estado === 'pagada') ? 'badge-success' : 'badge-warning';
                            $estadoLabel = ($estado === 'pagada') ? 'Pagada' : 'Pendent';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $estadoLabel; ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.modules-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.module-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 1.5rem 1rem;
    background: #fff;
    border: 1px solid var(--gray-200, #e5e7eb);
    border-radius: 0.75rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.module-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: var(--primary, #6366f1);
}
.badge {
    display: inline-block;
    padding: 0.2em 0.6em;
    font-size: 0.8em;
    font-weight: 600;
    border-radius: 0.375rem;
}
.badge-success {
    background-color: #d1fae5;
    color: #065f46;
}
.badge-warning {
    background-color: #fef3c7;
    color: #92400e;
}
@media (max-width: 768px) {
    .modules-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
