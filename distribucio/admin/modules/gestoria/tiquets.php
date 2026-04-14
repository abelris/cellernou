<?php
/**
 * El Celler Nou - Gestoria - Llistat Tiquets amb desglossament IVA
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Gestoria - Tiquets';

// Filtros
$year = (int)($_GET['any'] ?? date('Y'));
$filterDesde = $_GET['desde'] ?? '';
$filterHasta = $_GET['hasta'] ?? '';
$filterTrimestre = $_GET['trim'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Construir WHERE
$where = ['1=1'];

if ($filterTrimestre !== '') {
    $q = (int)$filterTrimestre;
    $qStart = $year . '-' . str_pad(($q - 1) * 3 + 1, 2, '0', STR_PAD_LEFT) . '-01';
    $qEnd = $year . '-' . str_pad($q * 3, 2, '0', STR_PAD_LEFT) . '-' . ($q == 1 || $q == 4 ? '31' : '30');
    $where[] = "t.fecha >= '$qStart' AND t.fecha <= '$qEnd 23:59:59'";
} else {
    if ($filterDesde !== '') {
        $where[] = "t.fecha >= '" . mysqli_real_escape_string($bd, $filterDesde) . "'";
    } else {
        $where[] = "YEAR(t.fecha) = $year";
    }
    if ($filterHasta !== '') {
        $where[] = "t.fecha <= '" . mysqli_real_escape_string($bd, $filterHasta) . " 23:59:59'";
    }
}

$whereClause = implode(' AND ', $where);

// Contar total
$rsCnt = mysqli_query($bd, "SELECT COUNT(*) as c FROM tiquets t WHERE $whereClause");
$totalRows = $rsCnt ? mysqli_fetch_assoc($rsCnt)['c'] : 0;
$totalPages = ceil($totalRows / $perPage);

// Obtener tiquets
$tiquets = [];
$sql = "SELECT t.*, c.nombre as cliente_nombre,
        (SELECT SUM(lt.cantidad * lt.precio_unidad) FROM linea_tiquet lt WHERE lt.idVenta = t.id) as total_tiquet
        FROM tiquets t
        LEFT JOIN clientes c ON t.idCliente = c.id
        WHERE $whereClause
        ORDER BY t.fecha DESC, t.id DESC
        LIMIT $perPage OFFSET $offset";
$result = mysqli_query($bd, $sql);
if ($result) { while ($row = mysqli_fetch_assoc($result)) { $tiquets[] = $row; } }

// IVA per tiquet (batch query)
$ivaPerTiquet = [];
if (!empty($tiquets)) {
    $tIds = array_map(function($t) { return (int)$t['id']; }, $tiquets);
    $tIdsStr = implode(',', $tIds);
    $sqlPerT = "SELECT lt.idVenta, lt.iva,
        SUM(lt.cantidad * lt.precio_unidad_sin_imp) as base,
        SUM(lt.cantidad * lt.precio_unidad_sin_imp * lt.iva / 100) as cuota
        FROM linea_tiquet lt
        WHERE lt.idVenta IN ($tIdsStr)
        GROUP BY lt.idVenta, lt.iva
        ORDER BY lt.iva";
    $rsPerT = mysqli_query($bd, $sqlPerT);
    if ($rsPerT) {
        while ($row = mysqli_fetch_assoc($rsPerT)) {
            $ivaPerTiquet[(int)$row['idVenta']][] = $row;
        }
    }
}

// IVA desglossat del període filtrat
$sqlIva = "SELECT
    lt.iva as tipo_iva,
    SUM(lt.cantidad * lt.precio_unidad_sin_imp) as base_imponible,
    SUM(lt.cantidad * lt.precio_unidad_sin_imp * lt.iva / 100) as cuota_iva,
    SUM(lt.cantidad * lt.precio_unidad) as total_con_iva,
    COUNT(DISTINCT t.id) as num_tiquets
    FROM linea_tiquet lt
    JOIN tiquets t ON lt.idVenta = t.id
    WHERE $whereClause
    GROUP BY lt.iva
    ORDER BY lt.iva";
$rsIva = mysqli_query($bd, $sqlIva);
$ivaData = [];
$totBase = 0; $totIva = 0; $totTotal = 0;
if ($rsIva) {
    while ($row = mysqli_fetch_assoc($rsIva)) {
        $ivaData[] = $row;
        $totBase += $row['base_imponible'];
        $totIva += $row['cuota_iva'];
        $totTotal += $row['total_con_iva'];
    }
}

$tipoPagoLabels = ['E' => 'Efectiu', 'T' => 'Targeta', 'C' => 'Compte', 'V' => 'Varis'];

// Años disponibles
$yearsAvailable = [];
$rsY = mysqli_query($bd, "SELECT DISTINCT YEAR(fecha) as y FROM tiquets WHERE YEAR(fecha) > 2000 ORDER BY y DESC");
if ($rsY) { while ($r = mysqli_fetch_assoc($rsY)) $yearsAvailable[] = $r['y']; }

function fmtEur($v) { return number_format($v, 2, ',', '.'); }

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div style="margin-bottom: 1rem;">
    <a href="<?php echo getAdminUrl('modules/gestoria/'); ?>" class="btn btn-secondary btn-sm">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Tornar al Resum Fiscal
    </a>
</div>

<!-- Filtres -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem;">
        <form method="GET" style="display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="min-width: 100px;">
                <label class="filter-label">Any</label>
                <select name="any" class="form-input">
                    <?php foreach ($yearsAvailable as $y): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="min-width: 120px;">
                <label class="filter-label">Trimestre</label>
                <select name="trim" class="form-input">
                    <option value="">Tot l'any</option>
                    <option value="1" <?php echo $filterTrimestre === '1' ? 'selected' : ''; ?>>1T (Gen-Mar)</option>
                    <option value="2" <?php echo $filterTrimestre === '2' ? 'selected' : ''; ?>>2T (Abr-Jun)</option>
                    <option value="3" <?php echo $filterTrimestre === '3' ? 'selected' : ''; ?>>3T (Jul-Set)</option>
                    <option value="4" <?php echo $filterTrimestre === '4' ? 'selected' : ''; ?>>4T (Oct-Des)</option>
                </select>
            </div>
            <div style="min-width: 140px;">
                <label class="filter-label">Des de</label>
                <input type="date" name="desde" class="form-input" value="<?php echo e($filterDesde); ?>">
            </div>
            <div style="min-width: 140px;">
                <label class="filter-label">Fins a</label>
                <input type="date" name="hasta" class="form-input" value="<?php echo e($filterHasta); ?>">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="<?php echo getAdminUrl('modules/gestoria/tiquets.php?any=' . $year); ?>" class="btn btn-secondary btn-sm">Netejar</a>
            </div>
        </form>
    </div>
</div>

<!-- Desglossament IVA del període -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3 class="card-title">Desglossament IVA — Tiquets filtrats (<?php echo number_format($totalRows); ?>)</h3></div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table" style="font-size: 0.875rem;">
                <thead>
                    <tr>
                        <th>Tipus IVA</th>
                        <th style="text-align: right;">Base Imposable</th>
                        <th style="text-align: right;">Quota IVA</th>
                        <th style="text-align: right;">Total (IVA inclòs)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ivaData)): ?>
                    <tr><td colspan="4" style="text-align: center; color: var(--gray-400);">Sense dades</td></tr>
                    <?php else: ?>
                    <?php foreach ($ivaData as $iva): ?>
                    <tr>
                        <td><strong>IVA <?php echo (int)$iva['tipo_iva']; ?>%</strong></td>
                        <td style="text-align: right;"><?php echo fmtEur($iva['base_imponible']); ?> &euro;</td>
                        <td style="text-align: right;"><?php echo fmtEur($iva['cuota_iva']); ?> &euro;</td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($iva['total_con_iva']); ?> &euro;</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background: var(--gray-50);">
                        <td><strong>TOTALS</strong></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($totBase); ?> &euro;</td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($totIva); ?> &euro;</td>
                        <td style="text-align: right; font-weight: 700; font-size: 1.05rem; color: var(--primary);"><?php echo fmtEur($totTotal); ?> &euro;</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Llistat de tiquets -->
<div class="card">
    <div class="card-header"><h3 class="card-title">Llistat de Tiquets</h3></div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 130px;">Data</th>
                        <th>Client</th>
                        <th style="width: 85px;">Pagament</th>
                        <th style="min-width: 160px;">Desgl. IVA</th>
                        <th style="width: 100px; text-align: right;">Total</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tiquets)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-400);">No s'han trobat tiquets.</td></tr>
                    <?php else: ?>
                    <?php foreach ($tiquets as $t): ?>
                    <tr>
                        <td><strong><?php echo $t['id']; ?></strong></td>
                        <td style="font-size: 0.85rem;"><?php echo date('d/m/Y H:i', strtotime($t['fecha'])); ?></td>
                        <td><?php echo $t['cliente_nombre'] ? e($t['cliente_nombre']) : '<span style="color:var(--gray-400)">Client general</span>'; ?></td>
                        <td>
                            <?php
                            $tp = $t['tipo_pago'] ?? '';
                            $tpClass = $tp === 'E' ? 'badge-success' : ($tp === 'T' ? 'badge-info' : 'badge-default');
                            ?>
                            <span class="badge <?php echo $tpClass; ?>"><?php echo $tipoPagoLabels[$tp] ?? '-'; ?></span>
                        </td>
                        <td>
                            <?php if (isset($ivaPerTiquet[$t['id']])): ?>
                            <div class="iva-breakdown-cell">
                                <?php foreach ($ivaPerTiquet[$t['id']] as $ivr): ?>
                                <div class="iva-line">
                                    <span class="iva-rate"><?php echo (int)$ivr['iva']; ?>%</span>
                                    <span class="iva-detail">B: <?php echo fmtEur($ivr['base']); ?> | IVA: <?php echo fmtEur($ivr['cuota']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <span style="color: var(--gray-400);">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($t['total_tiquet'] ?? 0); ?> &euro;</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="viewTiquet(<?php echo $t['id']; ?>)" title="Veure detall">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; background: var(--gray-50); border-top: 1px solid var(--gray-200);">
        <span style="font-size: 0.85rem; color: var(--gray-500);">Pàg. <?php echo $page; ?>/<?php echo $totalPages; ?> (<?php echo number_format($totalRows); ?> tiquets)</span>
        <div style="display: flex; gap: 0.25rem;">
            <?php
            $qp = $_GET; unset($qp['page']);
            $bu = getAdminUrl('modules/gestoria/tiquets.php') . '?' . http_build_query($qp);
            ?>
            <?php if ($page > 1): ?>
            <a href="<?php echo $bu . '&page=' . ($page-1); ?>" class="btn btn-sm btn-secondary">&laquo;</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
            <a href="<?php echo $bu . '&page=' . ($page+1); ?>" class="btn btn-sm btn-secondary">&raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Detall -->
<div id="detailModal" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title">Detall del Tiquet</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="detailContent">
            <p style="text-align: center; color: var(--gray-400);">Carregant...</p>
        </div>
    </div>
</div>

<script>
function viewTiquet(id) {
    document.getElementById('detailContent').innerHTML = '<p style="text-align:center;color:var(--gray-400);">Carregant...</p>';
    document.getElementById('detailModal').style.display = 'flex';
    fetch('<?php echo getAdminUrl('modules/gestoria/detall_tiquet_ajax.php'); ?>?id=' + id)
        .then(r => r.text())
        .then(html => document.getElementById('detailContent').innerHTML = html);
}
function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<style>
.filter-label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114,47,55,0.1); }
.badge { display: inline-block; padding: 0.25rem 0.625rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; }
.badge-success { background: #d1fae5; color: #065f46; }
.badge-info { background: #dbeafe; color: #1e40af; }
.badge-default { background: var(--gray-100); color: var(--gray-600); }

.iva-breakdown-cell { display: flex; flex-direction: column; gap: 2px; }
.iva-line { display: flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; line-height: 1.3; }
.iva-rate { font-weight: 700; color: var(--gray-700); min-width: 28px; }
.iva-detail { color: var(--gray-500); }

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-content { background: white; border-radius: 12px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-height: 90vh; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-200); }
.modal-title { font-size: 1.125rem; font-weight: 600; margin: 0; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--gray-400); padding: 0; line-height: 1; }
.modal-close:hover { color: var(--gray-600); }
.modal-body { padding: 1.5rem; overflow-y: auto; }
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
