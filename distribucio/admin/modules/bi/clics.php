<?php
/**
 * BI - Clics en WhatsApp i Trucar de la web pública
 *
 * Mesura si la web porta comandes, no només visites. Dades de `clics_contacto`
 * (controllers/ClicController.php), per dia, tipus, pàgina i poble.
 */
$pageTitle = 'Clics WhatsApp i Trucar';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();
$extraCss = '<link rel="stylesheet" href="' . getAdminUrl('modules/bi/bi.css') . '">';
require_once __DIR__ . '/../../includes/header.php';

$dies = (int) ($_GET['dies'] ?? 30);
if (!in_array($dies, [7, 30, 90, 365], true)) {
    $dies = 30;
}
$desde = "fecha >= CURDATE() - INTERVAL $dies DAY";

function clicsFiles($bd, string $sql): array {
    $r = mysqli_query($bd, $sql);
    return $r ? mysqli_fetch_all($r, MYSQLI_ASSOC) : [];
}

$suma = "SUM(IF(tipo = 'whatsapp', clics, 0)) AS whatsapp, SUM(IF(tipo = 'telefono', clics, 0)) AS telefono, SUM(clics) AS total";
$totals = clicsFiles($bd, "SELECT $suma FROM clics_contacto WHERE $desde")[0] ?? [];
$pagines = clicsFiles($bd, "SELECT pagina AS nom, $suma FROM clics_contacto WHERE $desde GROUP BY pagina ORDER BY total DESC LIMIT 30");
$pobles = clicsFiles($bd, "SELECT pueblo AS nom, $suma FROM clics_contacto WHERE $desde AND pueblo IS NOT NULL GROUP BY pueblo ORDER BY total DESC");

function clicsTaula(array $files, string $titol): void { ?>
    <div class="chart-card">
        <h3><?php echo e($titol); ?></h3>
        <?php if (!$files): ?>
            <p>Encara no hi ha clics en aquest període.</p>
        <?php else: ?>
        <table class="top-products-table">
            <thead><tr><th><?php echo e($titol === 'Per poble' ? 'Poble' : 'Pàgina'); ?></th><th>WhatsApp</th><th>Trucar</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($files as $f): ?>
                <tr>
                    <td><?php echo e($f['nom']); ?></td>
                    <td class="num"><?php echo (int) $f['whatsapp']; ?></td>
                    <td class="num"><?php echo (int) $f['telefono']; ?></td>
                    <td class="num"><?php echo (int) $f['total']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
<?php } ?>

<div class="bi-toolbar">
    <?php foreach ([7, 30, 90, 365] as $d): ?>
        <a href="?dies=<?php echo $d; ?>" class="btn btn-sm <?php echo $d === $dies ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $d; ?> dies</a>
    <?php endforeach; ?>
    <a href="<?php echo getAdminUrl('modules/bi/cellernou.php'); ?>" class="btn btn-secondary btn-sm">Tornar al BI</a>
</div>

<div class="kpi-grid">
    <div class="kpi-card"><div class="kpi-label">WhatsApp</div><div class="kpi-value"><?php echo (int) ($totals['whatsapp'] ?? 0); ?></div></div>
    <div class="kpi-card"><div class="kpi-label">Trucar</div><div class="kpi-value"><?php echo (int) ($totals['telefono'] ?? 0); ?></div></div>
    <div class="kpi-card"><div class="kpi-label">Total</div><div class="kpi-value"><?php echo (int) ($totals['total'] ?? 0); ?></div></div>
</div>

<div class="chart-row">
    <?php clicsTaula($pagines, 'Per pàgina'); ?>
    <?php clicsTaula($pobles, 'Per poble'); ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
