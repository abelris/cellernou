<?php
/**
 * El Celler Nou - Gestoria - Llistat Factures amb desglossament IVA
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Gestoria - Factures';

// Filtros
$year = (int)($_GET['any'] ?? date('Y'));
$filterDesde = $_GET['desde'] ?? '';
$filterHasta = $_GET['hasta'] ?? '';
$filterTrimestre = $_GET['trim'] ?? '';
$filterEstado = $_GET['estat'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Construir WHERE
$where = ['1=1'];

if ($filterTrimestre !== '') {
    $q = (int)$filterTrimestre;
    $qStart = $year . '-' . str_pad(($q - 1) * 3 + 1, 2, '0', STR_PAD_LEFT) . '-01';
    $qEnd = $year . '-' . str_pad($q * 3, 2, '0', STR_PAD_LEFT) . '-' . ($q == 1 || $q == 4 ? '31' : '30');
    $where[] = "f.fecha >= '$qStart' AND f.fecha <= '$qEnd 23:59:59'";
} else {
    if ($filterDesde !== '') {
        $where[] = "f.fecha >= '" . mysqli_real_escape_string($bd, $filterDesde) . "'";
    } else {
        $where[] = "YEAR(f.fecha) = $year";
    }
    if ($filterHasta !== '') {
        $where[] = "f.fecha <= '" . mysqli_real_escape_string($bd, $filterHasta) . " 23:59:59'";
    }
}

if ($filterEstado === 'pagada') {
    $where[] = "f.fecha_pago IS NOT NULL AND f.fecha_pago != '0000-00-00'";
} elseif ($filterEstado === 'pendent') {
    $where[] = "(f.fecha_pago IS NULL OR f.fecha_pago = '0000-00-00')";
}

$whereClause = implode(' AND ', $where);

$recarrecRates = [4 => 0.005, 10 => 0.014, 21 => 0.052];

// Comprovar si existeix la taula pagos_factura
$hasPagosFactura = false;
$chkTable = mysqli_query($bd, "SHOW TABLES LIKE 'pagos_factura'");
if ($chkTable && mysqli_num_rows($chkTable) > 0) {
    $hasPagosFactura = true;
}

// Contar total
$rsCnt = mysqli_query($bd, "SELECT COUNT(*) as c FROM facturas f WHERE $whereClause");
$totalRows = $rsCnt ? mysqli_fetch_assoc($rsCnt)['c'] : 0;
$totalPages = ceil($totalRows / $perPage);

// Obtener facturas
$facturas = [];
$subPagos = $hasPagosFactura
    ? "(SELECT COALESCE(SUM(pf.importe), 0) FROM pagos_factura pf WHERE pf.idFactura = f.id)"
    : "0";
$sql = "SELECT f.*, c.nombre as cliente_nombre, c.recarrec_equiv,
        (SELECT SUM(lf.cantidad * lf.precio_unidad) FROM linea_factura lf WHERE lf.idFactura = f.id) as base_total,
        (SELECT SUM(lf.cantidad * lf.precio_unidad * lf.iva / 100) FROM linea_factura lf WHERE lf.idFactura = f.id) as iva_total,
        $subPagos as total_pagado
        FROM facturas f
        LEFT JOIN clientes c ON f.idCliente = c.id
        WHERE $whereClause
        ORDER BY f.fecha DESC, f.id DESC
        LIMIT $perPage OFFSET $offset";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Calcular recàrrec d'equivalència si aplica
        $row['recarrec_total'] = 0;
        if ($row['recarrec_equiv']) {
            $rsRF = mysqli_query($bd, "SELECT lf.iva, SUM(lf.cantidad * lf.precio_unidad) as base_r
                FROM linea_factura lf WHERE lf.idFactura = " . (int)$row['id'] . " GROUP BY lf.iva");
            if ($rsRF) {
                while ($rr = mysqli_fetch_assoc($rsRF)) {
                    $rRate = (int)$rr['iva'];
                    if (isset($recarrecRates[$rRate])) {
                        $row['recarrec_total'] += $rr['base_r'] * $recarrecRates[$rRate];
                    }
                }
            }
        }
        $row['total_factura'] = ($row['base_total'] ?? 0) + ($row['iva_total'] ?? 0) + $row['recarrec_total'];
        $facturas[] = $row;
    }
}

// IVA per factura (batch query)
$ivaPerFactura = [];
if (!empty($facturas)) {
    $fIds = array_map(function($f) { return (int)$f['id']; }, $facturas);
    $fIdsStr = implode(',', $fIds);
    $sqlPerF = "SELECT lf.idFactura, lf.iva,
        SUM(lf.cantidad * lf.precio_unidad) as base,
        SUM(lf.cantidad * lf.precio_unidad * lf.iva / 100) as cuota
        FROM linea_factura lf
        WHERE lf.idFactura IN ($fIdsStr)
        GROUP BY lf.idFactura, lf.iva
        ORDER BY lf.iva";
    $rsPerF = mysqli_query($bd, $sqlPerF);
    if ($rsPerF) {
        while ($row = mysqli_fetch_assoc($rsPerF)) {
            $ivaPerFactura[(int)$row['idFactura']][] = $row;
        }
    }
}

// IVA desglossat del període filtrat (factures: precio_unidad = base sense IVA)
$sqlIva = "SELECT
    lf.iva as tipo_iva,
    SUM(lf.cantidad * lf.precio_unidad) as base_imponible,
    SUM(lf.cantidad * lf.precio_unidad * lf.iva / 100) as cuota_iva,
    SUM(lf.cantidad * lf.precio_unidad * (1 + lf.iva / 100)) as total_con_iva,
    COUNT(DISTINCT f.id) as num_factures
    FROM linea_factura lf
    JOIN facturas f ON lf.idFactura = f.id
    WHERE $whereClause
    GROUP BY lf.iva
    ORDER BY lf.iva";
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

// Recàrrec d'equivalència desglossat
$sqlRe = "SELECT
    lf.iva as tipo_iva,
    SUM(lf.cantidad * lf.precio_unidad) as base_re
    FROM linea_factura lf
    JOIN facturas f ON lf.idFactura = f.id
    JOIN clientes c ON f.idCliente = c.id
    WHERE $whereClause AND c.recarrec_equiv = 1
    GROUP BY lf.iva
    ORDER BY lf.iva";
$rsRe = mysqli_query($bd, $sqlRe);
$recarrecData = [];
$totRecarrec = 0;
if ($rsRe) {
    while ($row = mysqli_fetch_assoc($rsRe)) {
        $rate = (int)$row['tipo_iva'];
        if (isset($recarrecRates[$rate])) {
            $reAmount = $row['base_re'] * $recarrecRates[$rate];
            $recarrecData[$rate] = $reAmount;
            $totRecarrec += $reAmount;
        }
    }
}

$formaPagoLabels = [1 => 'Efectiu', 2 => 'Transferència', 3 => 'Xec', 4 => 'Domiciliació'];
$recarrecPct = [4 => '0,5', 10 => '1,4', 21 => '5,2'];

// Años disponibles
$yearsAvailable = [];
$rsY = mysqli_query($bd, "SELECT DISTINCT YEAR(fecha) as y FROM facturas WHERE YEAR(fecha) > 2000 ORDER BY y DESC");
if ($rsY) { while ($r = mysqli_fetch_assoc($rsY)) $yearsAvailable[] = $r['y']; }

if (!function_exists('fmtEur')) {
    function fmtEur($v) { return number_format($v, 2, ',', '.'); }
}

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
            <div style="min-width: 120px;">
                <label class="filter-label">Estat</label>
                <select name="estat" class="form-input">
                    <option value="">Totes</option>
                    <option value="pendent" <?php echo $filterEstado === 'pendent' ? 'selected' : ''; ?>>Pendent</option>
                    <option value="pagada" <?php echo $filterEstado === 'pagada' ? 'selected' : ''; ?>>Pagada</option>
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
                <a href="<?php echo getAdminUrl('modules/gestoria/facturas.php?any=' . $year); ?>" class="btn btn-secondary btn-sm">Netejar</a>
            </div>
        </form>
    </div>
</div>

<!-- Desglossament IVA del període -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3 class="card-title">Desglossament IVA — Factures filtrades (<?php echo number_format($totalRows); ?>)</h3></div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table" style="font-size: 0.875rem;">
                <thead>
                    <tr>
                        <th>Tipus IVA</th>
                        <th style="text-align: right;">Base Imposable</th>
                        <th style="text-align: right;">Quota IVA</th>
                        <?php if ($totRecarrec > 0): ?>
                        <th style="text-align: right;">Recàrrec Equiv.</th>
                        <?php endif; ?>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ivaData)): ?>
                    <tr><td colspan="<?php echo $totRecarrec > 0 ? 5 : 4; ?>" style="text-align: center; color: var(--gray-400);">Sense dades</td></tr>
                    <?php else: ?>
                    <?php foreach ($ivaData as $iva): ?>
                    <?php $rate = (int)$iva['tipo_iva']; ?>
                    <tr>
                        <td><strong>IVA <?php echo $rate; ?>%</strong></td>
                        <td style="text-align: right;"><?php echo fmtEur($iva['base_imponible']); ?> &euro;</td>
                        <td style="text-align: right;"><?php echo fmtEur($iva['cuota_iva']); ?> &euro;</td>
                        <?php if ($totRecarrec > 0): ?>
                        <td style="text-align: right;">
                            <?php if (isset($recarrecData[$rate])): ?>
                                <?php echo fmtEur($recarrecData[$rate]); ?> &euro;
                                <span style="font-size: 0.75rem; color: var(--gray-500);">(<?php echo $recarrecPct[$rate] ?? ''; ?>%)</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td style="text-align: right; font-weight: 600;">
                            <?php
                            $lineTotal = $iva['total_con_iva'] + ($recarrecData[$rate] ?? 0);
                            echo fmtEur($lineTotal);
                            ?> &euro;
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background: var(--gray-50);">
                        <td><strong>TOTALS</strong></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($totBase); ?> &euro;</td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($totIva); ?> &euro;</td>
                        <?php if ($totRecarrec > 0): ?>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($totRecarrec); ?> &euro;</td>
                        <?php endif; ?>
                        <td style="text-align: right; font-weight: 700; font-size: 1.05rem; color: var(--primary);"><?php echo fmtEur($totTotal + $totRecarrec); ?> &euro;</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Llistat de factures -->
<div class="card">
    <div class="card-header"><h3 class="card-title">Llistat de Factures</h3></div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 110px;">Nº Factura</th>
                        <th style="width: 90px;">Data</th>
                        <th>Client</th>
                        <th style="min-width: 160px;">Desgl. IVA</th>
                        <th style="width: 90px; text-align: right;">Base</th>
                        <th style="width: 90px; text-align: right;">Total</th>
                        <th style="width: 80px;">Estat</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($facturas)): ?>
                    <tr><td colspan="8" style="text-align: center; padding: 2rem; color: var(--gray-400);">No s'han trobat factures.</td></tr>
                    <?php else: ?>
                    <?php foreach ($facturas as $f): ?>
                    <?php
                        $numFactura = 'FA' . str_pad((date('Y', strtotime($f['fecha'])) * 100) . $f['id'], 10, '0', STR_PAD_LEFT);
                        $isPagada = !empty($f['fecha_pago']) && $f['fecha_pago'] !== '0000-00-00';
                        $baseF = $f['base_total'] ?? 0;
                        $totalF = $f['total_factura'];
                    ?>
                    <tr>
                        <td><strong style="font-family: monospace; font-size: 0.78rem;"><?php echo $numFactura; ?></strong></td>
                        <td style="font-size: 0.85rem;"><?php echo date('d/m/Y', strtotime($f['fecha'])); ?></td>
                        <td><?php echo $f['cliente_nombre'] ? e($f['cliente_nombre']) : '<span style="color:var(--gray-400)">-</span>'; ?></td>
                        <td>
                            <?php if (isset($ivaPerFactura[$f['id']])): ?>
                            <div class="iva-breakdown-cell">
                                <?php foreach ($ivaPerFactura[$f['id']] as $ivr): ?>
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
                        <td style="text-align: right; font-size: 0.85rem;"><?php echo fmtEur($baseF); ?> &euro;</td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($totalF); ?> &euro;</td>
                        <td>
                            <?php if ($isPagada): ?>
                                <span class="badge badge-success">Pagada</span>
                            <?php elseif ($f['total_pagado'] > 0): ?>
                                <span class="badge badge-partial">Parcial</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pendent</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="viewFactura(<?php echo $f['id']; ?>)" title="Veure detall">
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
        <span style="font-size: 0.85rem; color: var(--gray-500);">Pàg. <?php echo $page; ?>/<?php echo $totalPages; ?> (<?php echo number_format($totalRows); ?> factures)</span>
        <div style="display: flex; gap: 0.25rem;">
            <?php
            $qp = $_GET; unset($qp['page']);
            $bu = getAdminUrl('modules/gestoria/facturas.php') . '?' . http_build_query($qp);
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
    <div class="modal-content" style="max-width: 750px;">
        <div class="modal-header">
            <h3 class="modal-title">Detall de la Factura</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="detailContent">
            <p style="text-align: center; color: var(--gray-400);">Carregant...</p>
        </div>
    </div>
</div>

<script>
function viewFactura(id) {
    document.getElementById('detailContent').innerHTML = '<p style="text-align:center;color:var(--gray-400);">Carregant...</p>';
    document.getElementById('detailModal').style.display = 'flex';
    fetch('<?php echo getAdminUrl('modules/gestoria/detall_factura_ajax.php'); ?>?id=' + id)
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
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-partial { background: #dbeafe; color: #1e40af; }
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
