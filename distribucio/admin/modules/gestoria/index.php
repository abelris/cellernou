<?php
/**
 * El Celler Nou - Gestoria / Comptabilitat - Resum Fiscal
 * Dashboard amb totals de facturació i desglossament IVA per trimestres
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Resum Fiscal';

$year = isset($_GET['any']) ? (int)$_GET['any'] : (int)date('Y');

// Años disponibles
$yearsAvailable = [];
$rsY = mysqli_query($bd, "SELECT DISTINCT YEAR(fecha) as y FROM tiquets UNION SELECT DISTINCT YEAR(fecha) FROM facturas ORDER BY y DESC");
if ($rsY) { while ($r = mysqli_fetch_assoc($rsY)) { if ($r['y'] > 2000) $yearsAvailable[] = $r['y']; } }
if (empty($yearsAvailable)) $yearsAvailable[] = (int)date('Y');

// ============================================================
// TIQUETS - IVA desglossat per trimestre
// ============================================================
// En linea_tiquet: precio_unidad = PVP (IVA inclòs), precio_unidad_sin_imp = base
$sqlTiquets = "SELECT
    QUARTER(t.fecha) as trimestre,
    lt.iva as tipo_iva,
    SUM(lt.cantidad * lt.precio_unidad_sin_imp) as base_imponible,
    SUM(lt.cantidad * lt.precio_unidad_sin_imp * lt.iva / 100) as cuota_iva,
    SUM(lt.cantidad * lt.precio_unidad) as total_con_iva
    FROM linea_tiquet lt
    JOIN tiquets t ON lt.idVenta = t.id
    WHERE YEAR(t.fecha) = $year
    GROUP BY trimestre, lt.iva
    ORDER BY trimestre, lt.iva";
$rsTiquets = mysqli_query($bd, $sqlTiquets);

$tiquetsData = [1 => [], 2 => [], 3 => [], 4 => []];
$tiquetsAnual = [];

if ($rsTiquets) {
    while ($row = mysqli_fetch_assoc($rsTiquets)) {
        $q = (int)$row['trimestre'];
        $iva = (int)$row['tipo_iva'];
        $tiquetsData[$q][$iva] = [
            'base' => (float)$row['base_imponible'],
            'cuota' => (float)$row['cuota_iva'],
            'total' => (float)$row['total_con_iva']
        ];
        // Acumular anual
        if (!isset($tiquetsAnual[$iva])) $tiquetsAnual[$iva] = ['base' => 0, 'cuota' => 0, 'total' => 0];
        $tiquetsAnual[$iva]['base'] += (float)$row['base_imponible'];
        $tiquetsAnual[$iva]['cuota'] += (float)$row['cuota_iva'];
        $tiquetsAnual[$iva]['total'] += (float)$row['total_con_iva'];
    }
}

// ============================================================
// FACTURAS - IVA desglossat per trimestre
// ============================================================
// En linea_factura: precio_unidad = base (sense IVA)
$sqlFacturas = "SELECT
    QUARTER(f.fecha) as trimestre,
    lf.iva as tipo_iva,
    SUM(lf.cantidad * lf.precio_unidad) as base_imponible,
    SUM(lf.cantidad * lf.precio_unidad * lf.iva / 100) as cuota_iva,
    SUM(lf.cantidad * lf.precio_unidad * (1 + lf.iva / 100)) as total_con_iva
    FROM linea_factura lf
    JOIN facturas f ON lf.idFactura = f.id
    WHERE YEAR(f.fecha) = $year
    GROUP BY trimestre, lf.iva
    ORDER BY trimestre, lf.iva";
$rsFacturas = mysqli_query($bd, $sqlFacturas);

$facturasData = [1 => [], 2 => [], 3 => [], 4 => []];
$facturasAnual = [];

if ($rsFacturas) {
    while ($row = mysqli_fetch_assoc($rsFacturas)) {
        $q = (int)$row['trimestre'];
        $iva = (int)$row['tipo_iva'];
        $facturasData[$q][$iva] = [
            'base' => (float)$row['base_imponible'],
            'cuota' => (float)$row['cuota_iva'],
            'total' => (float)$row['total_con_iva']
        ];
        if (!isset($facturasAnual[$iva])) $facturasAnual[$iva] = ['base' => 0, 'cuota' => 0, 'total' => 0];
        $facturasAnual[$iva]['base'] += (float)$row['base_imponible'];
        $facturasAnual[$iva]['cuota'] += (float)$row['cuota_iva'];
        $facturasAnual[$iva]['total'] += (float)$row['total_con_iva'];
    }
}

// ============================================================
// RECÀRREC D'EQUIVALÈNCIA en factures
// ============================================================
$sqlRecarrec = "SELECT
    QUARTER(f.fecha) as trimestre,
    lf.iva as tipo_iva,
    SUM(lf.cantidad * lf.precio_unidad *
        CASE WHEN lf.iva = 4 THEN 0.005
             WHEN lf.iva = 10 THEN 0.014
             WHEN lf.iva = 21 THEN 0.052
             ELSE 0 END
    ) as recarrec
    FROM linea_factura lf
    JOIN facturas f ON lf.idFactura = f.id
    JOIN clientes c ON f.idCliente = c.id
    WHERE YEAR(f.fecha) = $year AND c.recarrec_equiv = 1
    GROUP BY trimestre, lf.iva
    ORDER BY trimestre, lf.iva";
$rsRec = mysqli_query($bd, $sqlRecarrec);
$recarrecData = [1 => [], 2 => [], 3 => [], 4 => []];
$recarrecAnual = [];
if ($rsRec) {
    while ($row = mysqli_fetch_assoc($rsRec)) {
        $q = (int)$row['trimestre'];
        $iva = (int)$row['tipo_iva'];
        $recarrecData[$q][$iva] = (float)$row['recarrec'];
        if (!isset($recarrecAnual[$iva])) $recarrecAnual[$iva] = 0;
        $recarrecAnual[$iva] += (float)$row['recarrec'];
    }
}

// Totales generales
$totalTiquets = array_sum(array_column($tiquetsAnual, 'total'));
$totalFacturas = array_sum(array_column($facturasAnual, 'total'));
$totalRecarrec = array_sum($recarrecAnual);
$totalGeneral = $totalTiquets + $totalFacturas + $totalRecarrec;

$totalBaseTiquets = array_sum(array_column($tiquetsAnual, 'base'));
$totalBaseFacturas = array_sum(array_column($facturasAnual, 'base'));
$totalIvaTiquets = array_sum(array_column($tiquetsAnual, 'cuota'));
$totalIvaFacturas = array_sum(array_column($facturasAnual, 'cuota'));

// Contadores
$rsCntT = mysqli_query($bd, "SELECT COUNT(*) as c FROM tiquets WHERE YEAR(fecha) = $year");
$numTiquets = $rsCntT ? mysqli_fetch_assoc($rsCntT)['c'] : 0;
$rsCntF = mysqli_query($bd, "SELECT COUNT(*) as c FROM facturas WHERE YEAR(fecha) = $year");
$numFacturas = $rsCntF ? mysqli_fetch_assoc($rsCntF)['c'] : 0;

// Todos los tipos de IVA presentes
$allIvaRates = array_unique(array_merge(array_keys($tiquetsAnual), array_keys($facturasAnual)));
sort($allIvaRates);

$trimLabels = [1 => '1T (Gen-Mar)', 2 => '2T (Abr-Jun)', 3 => '3T (Jul-Set)', 4 => '4T (Oct-Des)'];

// Helper
function fmtEur($v) { return number_format($v, 2, ',', '.'); }

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<!-- Selector d'any -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <label style="font-weight: 600; color: var(--gray-600);">Any fiscal:</label>
        <select onchange="window.location='?any='+this.value" class="form-input" style="width: auto; padding: 0.4rem 0.75rem;">
            <?php foreach ($yearsAvailable as $y): ?>
            <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="font-size: 0.8rem; color: var(--gray-400);">
        Dades actualitzades en temps real
    </div>
</div>

<!-- Cards resum -->
<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo fmtEur($totalTiquets); ?> &euro;</div>
            <div class="stat-label">Tiquets (<?php echo number_format($numTiquets); ?>)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo fmtEur($totalFacturas + $totalRecarrec); ?> &euro;</div>
            <div class="stat-label">Factures (<?php echo number_format($numFacturas); ?>)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v1"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo fmtEur($totalGeneral); ?> &euro;</div>
            <div class="stat-label">Total Facturació <?php echo $year; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo fmtEur($totalIvaTiquets + $totalIvaFacturas); ?> &euro;</div>
            <div class="stat-label">IVA Repercutit Total</div>
        </div>
    </div>
</div>

<!-- DESGLOSSAMENT ANUAL -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header"><h3 class="card-title">Desglossament IVA Anual — <?php echo $year; ?></h3></div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Concepte</th>
                        <?php foreach ($allIvaRates as $rate): ?>
                        <th colspan="2" style="text-align: center; background: var(--gray-50);">IVA <?php echo $rate; ?>%</th>
                        <?php endforeach; ?>
                        <th style="text-align: right;">Total Base</th>
                        <th style="text-align: right;">Total IVA</th>
                        <th style="text-align: right;">TOTAL</th>
                    </tr>
                    <tr>
                        <th></th>
                        <?php foreach ($allIvaRates as $rate): ?>
                        <th style="text-align: right; font-size: 0.7rem; color: var(--gray-500);">Base</th>
                        <th style="text-align: right; font-size: 0.7rem; color: var(--gray-500);">Quota</th>
                        <?php endforeach; ?>
                        <th></th><th></th><th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Tiquets</strong></td>
                        <?php $sumBase = 0; $sumIva = 0; foreach ($allIvaRates as $rate): $d = $tiquetsAnual[$rate] ?? ['base'=>0,'cuota'=>0,'total'=>0]; $sumBase += $d['base']; $sumIva += $d['cuota']; ?>
                        <td style="text-align: right;"><?php echo fmtEur($d['base']); ?></td>
                        <td style="text-align: right;"><?php echo fmtEur($d['cuota']); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($sumBase); ?></td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($sumIva); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($totalTiquets); ?> &euro;</td>
                    </tr>
                    <tr>
                        <td><strong>Factures</strong></td>
                        <?php $sumBase = 0; $sumIva = 0; foreach ($allIvaRates as $rate): $d = $facturasAnual[$rate] ?? ['base'=>0,'cuota'=>0,'total'=>0]; $rec = $recarrecAnual[$rate] ?? 0; $sumBase += $d['base']; $sumIva += $d['cuota']; ?>
                        <td style="text-align: right;"><?php echo fmtEur($d['base']); ?></td>
                        <td style="text-align: right;"><?php echo fmtEur($d['cuota']); ?><?php if ($rec > 0): ?><br><span style="font-size:0.7rem;color:#d97706;">+RE <?php echo fmtEur($rec); ?></span><?php endif; ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($totalBaseFacturas); ?></td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($totalIvaFacturas); ?><?php if ($totalRecarrec > 0): ?><br><span style="font-size:0.7rem;color:#d97706;">+RE <?php echo fmtEur($totalRecarrec); ?></span><?php endif; ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($totalFacturas + $totalRecarrec); ?> &euro;</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: var(--gray-50);">
                        <td><strong>TOTAL</strong></td>
                        <?php $sumBase = 0; $sumIva = 0; foreach ($allIvaRates as $rate):
                            $bt = ($tiquetsAnual[$rate]['base'] ?? 0) + ($facturasAnual[$rate]['base'] ?? 0);
                            $it = ($tiquetsAnual[$rate]['cuota'] ?? 0) + ($facturasAnual[$rate]['cuota'] ?? 0);
                            $sumBase += $bt; $sumIva += $it;
                        ?>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($bt); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($it); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($sumBase); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($sumIva); ?></td>
                        <td style="text-align: right; font-weight: 700; font-size: 1.1rem; color: var(--primary);"><?php echo fmtEur($totalGeneral); ?> &euro;</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- DESGLOSSAMENT PER TRIMESTRES -->
<?php foreach ([1,2,3,4] as $q):
    $tData = $tiquetsData[$q];
    $fData = $facturasData[$q];
    $rData = $recarrecData[$q];
    $qIvaRates = array_unique(array_merge(array_keys($tData), array_keys($fData)));
    sort($qIvaRates);

    $qTotalTiquets = array_sum(array_column($tData, 'total'));
    $qTotalFacturas = array_sum(array_column($fData, 'total'));
    $qTotalRecarrec = array_sum($rData);
    $qTotal = $qTotalTiquets + $qTotalFacturas + $qTotalRecarrec;

    $qBaseTiquets = array_sum(array_column($tData, 'base'));
    $qBaseFacturas = array_sum(array_column($fData, 'base'));
    $qIvaTiquets = array_sum(array_column($tData, 'cuota'));
    $qIvaFacturas = array_sum(array_column($fData, 'cuota'));
?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title"><?php echo $trimLabels[$q]; ?></h3>
        <div style="display: flex; gap: 1rem; font-size: 0.85rem;">
            <span>Tiquets: <strong><?php echo fmtEur($qTotalTiquets); ?> &euro;</strong></span>
            <span>Factures: <strong><?php echo fmtEur($qTotalFacturas + $qTotalRecarrec); ?> &euro;</strong></span>
            <span style="color: var(--primary); font-weight: 700;">Total: <?php echo fmtEur($qTotal); ?> &euro;</span>
        </div>
    </div>
    <?php if (empty($qIvaRates)): ?>
    <div class="card-body" style="text-align: center; color: var(--gray-400); padding: 1.5rem;">
        Sense dades per a aquest trimestre
    </div>
    <?php else: ?>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table" style="font-size: 0.875rem;">
                <thead>
                    <tr>
                        <th style="width: 120px;">Concepte</th>
                        <?php foreach ($qIvaRates as $rate): ?>
                        <th style="text-align: right;">Base <?php echo $rate; ?>%</th>
                        <th style="text-align: right;">Quota <?php echo $rate; ?>%</th>
                        <?php endforeach; ?>
                        <th style="text-align: right;">Total Base</th>
                        <th style="text-align: right;">Total IVA</th>
                        <th style="text-align: right;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tiquets</td>
                        <?php $sb=0;$si=0; foreach ($qIvaRates as $rate): $d=$tData[$rate]??['base'=>0,'cuota'=>0]; $sb+=$d['base']; $si+=$d['cuota']; ?>
                        <td style="text-align: right;"><?php echo fmtEur($d['base']); ?></td>
                        <td style="text-align: right;"><?php echo fmtEur($d['cuota']); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($sb); ?></td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($si); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($qTotalTiquets); ?></td>
                    </tr>
                    <tr>
                        <td>Factures</td>
                        <?php $sb=0;$si=0; foreach ($qIvaRates as $rate): $d=$fData[$rate]??['base'=>0,'cuota'=>0]; $rec=$rData[$rate]??0; $sb+=$d['base']; $si+=$d['cuota']; ?>
                        <td style="text-align: right;"><?php echo fmtEur($d['base']); ?></td>
                        <td style="text-align: right;"><?php echo fmtEur($d['cuota']); ?><?php if($rec>0):?><br><span style="font-size:0.7rem;color:#d97706;">+RE <?php echo fmtEur($rec);?></span><?php endif;?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($qBaseFacturas); ?></td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($qIvaFacturas); ?><?php if($qTotalRecarrec>0):?><br><span style="font-size:0.7rem;color:#d97706;">+RE <?php echo fmtEur($qTotalRecarrec);?></span><?php endif;?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($qTotalFacturas + $qTotalRecarrec); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: var(--gray-50);">
                        <td><strong>TOTAL <?php echo $q;?>T</strong></td>
                        <?php $sb=0;$si=0; foreach ($qIvaRates as $rate):
                            $bt=($tData[$rate]['base']??0)+($fData[$rate]['base']??0);
                            $it=($tData[$rate]['cuota']??0)+($fData[$rate]['cuota']??0);
                            $sb+=$bt;$si+=$it;
                        ?>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($bt); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($it); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($sb); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($si); ?></td>
                        <td style="text-align: right; font-weight: 700; color: var(--primary);"><?php echo fmtEur($qTotal); ?> &euro;</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<!-- Enllaços ràpids -->
<div style="display: flex; gap: 1rem; margin-top: 1rem;">
    <a href="<?php echo getAdminUrl('modules/gestoria/tiquets.php?any=' . $year); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
        Llistat Tiquets
    </a>
    <a href="<?php echo getAdminUrl('modules/gestoria/facturas.php?any=' . $year); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Llistat Factures
    </a>
</div>

<style>
.form-input { padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114,47,55,0.1); }
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
