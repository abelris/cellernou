<?php
/**
 * El Celler Nou - Gestoria / Comptabilitat - Resum Fiscal
 * Dashboard amb totals de facturació i desglossament IVA per trimestres
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Resum Fiscal';

$year = isset($_GET['any']) ? (int)$_GET['any'] : (int)date('Y');

// Filtre de marca (línia de negoci d'ALMABRIS GROUP 2024 S.L.)
$marca = $_GET['marca'] ?? 'all';
if (!in_array($marca, ['all', 'cellernou', 'odr'], true)) $marca = 'all';
$incCeller = in_array($marca, ['all', 'cellernou'], true);
$incOdr = in_array($marca, ['all', 'odr'], true);
$marcaLabels = ['all' => 'Global (totes les marques)', 'cellernou' => 'CellerNou', 'odr' => 'OnDigitalRis'];

// Años disponibles (inclou totes les marques)
$yearsAvailable = [];
$rsY = mysqli_query($bd, "SELECT DISTINCT YEAR(fecha) as y FROM tiquets
    UNION SELECT DISTINCT YEAR(fecha) FROM facturas
    UNION SELECT DISTINCT YEAR(fecha) FROM facturas_compra
    UNION SELECT DISTINCT YEAR(fecha) FROM odr_facturas
    UNION SELECT DISTINCT YEAR(fecha) FROM odr_facturas_compra
    ORDER BY y DESC");
if ($rsY) { while ($r = mysqli_fetch_assoc($rsY)) { if ($r['y'] > 2000) $yearsAvailable[] = $r['y']; } }
if (empty($yearsAvailable)) $yearsAvailable[] = (int)date('Y');

// ============================================================
// TIQUETS - IVA desglossat per trimestre (només CellerNou — ODR no té tiquets)
// ============================================================
$tiquetsData = [1 => [], 2 => [], 3 => [], 4 => []];
$tiquetsAnual = [];

if ($incCeller) {
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
}

// ============================================================
// FACTURES - IVA desglossat per trimestre (CellerNou + OnDigitalRis segons filtre)
// ============================================================
$facturasData = [1 => [], 2 => [], 3 => [], 4 => []];
$facturasAnual = [];

$facturasSources = [];
if ($incCeller) {
    // En linea_factura: precio_unidad = base (sense IVA)
    $facturasSources[] = "SELECT
        QUARTER(f.fecha) as trimestre,
        lf.iva as tipo_iva,
        SUM(lf.cantidad * lf.precio_unidad) as base_imponible,
        SUM(lf.cantidad * lf.precio_unidad * lf.iva / 100) as cuota_iva,
        SUM(lf.cantidad * lf.precio_unidad * (1 + lf.iva / 100)) as total_con_iva
        FROM linea_factura lf
        JOIN facturas f ON lf.idFactura = f.id
        WHERE YEAR(f.fecha) = $year
        GROUP BY trimestre, lf.iva";
}
if ($incOdr) {
    // odr_linea_factura: precio_unidad = base (sense IVA), mateixa estructura
    $facturasSources[] = "SELECT
        QUARTER(f.fecha) as trimestre,
        lf.iva as tipo_iva,
        SUM(lf.cantidad * lf.precio_unidad) as base_imponible,
        SUM(lf.cantidad * lf.precio_unidad * lf.iva / 100) as cuota_iva,
        SUM(lf.cantidad * lf.precio_unidad * (1 + lf.iva / 100)) as total_con_iva
        FROM odr_linea_factura lf
        JOIN odr_facturas f ON lf.idFactura = f.id
        WHERE YEAR(f.fecha) = $year
        GROUP BY trimestre, lf.iva";
}

foreach ($facturasSources as $sqlFacturas) {
    $rsFacturas = mysqli_query($bd, $sqlFacturas);
    if ($rsFacturas) {
        while ($row = mysqli_fetch_assoc($rsFacturas)) {
            $q = (int)$row['trimestre'];
            $iva = (int)$row['tipo_iva'];
            if (!isset($facturasData[$q][$iva])) $facturasData[$q][$iva] = ['base' => 0, 'cuota' => 0, 'total' => 0];
            $facturasData[$q][$iva]['base'] += (float)$row['base_imponible'];
            $facturasData[$q][$iva]['cuota'] += (float)$row['cuota_iva'];
            $facturasData[$q][$iva]['total'] += (float)$row['total_con_iva'];

            if (!isset($facturasAnual[$iva])) $facturasAnual[$iva] = ['base' => 0, 'cuota' => 0, 'total' => 0];
            $facturasAnual[$iva]['base'] += (float)$row['base_imponible'];
            $facturasAnual[$iva]['cuota'] += (float)$row['cuota_iva'];
            $facturasAnual[$iva]['total'] += (float)$row['total_con_iva'];
        }
    }
}

// ============================================================
// RECÀRREC D'EQUIVALÈNCIA en factures (només CellerNou — ODR no aplica RE)
// ============================================================
$recarrecData = [1 => [], 2 => [], 3 => [], 4 => []];
$recarrecAnual = [];

if ($incCeller) {
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
if ($rsRec) {
    while ($row = mysqli_fetch_assoc($rsRec)) {
        $q = (int)$row['trimestre'];
        $iva = (int)$row['tipo_iva'];
        $recarrecData[$q][$iva] = (float)$row['recarrec'];
        if (!isset($recarrecAnual[$iva])) $recarrecAnual[$iva] = 0;
        $recarrecAnual[$iva] += (float)$row['recarrec'];
    }
}
} // end if ($incCeller) recarrec

// ============================================================
// FACTURES REBUDES (compres) — IVA SUPORTAT
// Mateixa estructura per CellerNou i ODR: precio_unidad = base, iva = %
// ============================================================
$comprasData = [1 => [], 2 => [], 3 => [], 4 => []];
$comprasAnual = [];

$comprasSources = [];
if ($incCeller) {
    $comprasSources[] = "SELECT
        QUARTER(f.fecha) as trimestre,
        lf.iva as tipo_iva,
        SUM(lf.cantidad * lf.precio_unidad) as base_imponible,
        SUM(lf.cantidad * lf.precio_unidad * lf.iva / 100) as cuota_iva,
        SUM(lf.cantidad * lf.precio_unidad * (1 + lf.iva / 100)) as total_con_iva
        FROM linea_factura_compra lf
        JOIN facturas_compra f ON lf.idFactura = f.id
        WHERE YEAR(f.fecha) = $year
        GROUP BY trimestre, lf.iva";
}
if ($incOdr) {
    $comprasSources[] = "SELECT
        QUARTER(f.fecha) as trimestre,
        lf.iva as tipo_iva,
        SUM(lf.cantidad * lf.precio_unidad) as base_imponible,
        SUM(lf.cantidad * lf.precio_unidad * lf.iva / 100) as cuota_iva,
        SUM(lf.cantidad * lf.precio_unidad * (1 + lf.iva / 100)) as total_con_iva
        FROM odr_linea_factura_compra lf
        JOIN odr_facturas_compra f ON lf.idFactura = f.id
        WHERE YEAR(f.fecha) = $year
        GROUP BY trimestre, lf.iva";
}

foreach ($comprasSources as $sqlCompras) {
    $rsCompras = mysqli_query($bd, $sqlCompras);
    if ($rsCompras) {
        while ($row = mysqli_fetch_assoc($rsCompras)) {
            $q = (int)$row['trimestre'];
            $iva = (int)$row['tipo_iva'];
            if (!isset($comprasData[$q][$iva])) $comprasData[$q][$iva] = ['base' => 0, 'cuota' => 0, 'total' => 0];
            $comprasData[$q][$iva]['base'] += (float)$row['base_imponible'];
            $comprasData[$q][$iva]['cuota'] += (float)$row['cuota_iva'];
            $comprasData[$q][$iva]['total'] += (float)$row['total_con_iva'];

            if (!isset($comprasAnual[$iva])) $comprasAnual[$iva] = ['base' => 0, 'cuota' => 0, 'total' => 0];
            $comprasAnual[$iva]['base'] += (float)$row['base_imponible'];
            $comprasAnual[$iva]['cuota'] += (float)$row['cuota_iva'];
            $comprasAnual[$iva]['total'] += (float)$row['total_con_iva'];
        }
    }
}

// Totales generales
$totalTiquets = array_sum(array_column($tiquetsAnual, 'total'));
$totalFacturas = array_sum(array_column($facturasAnual, 'total'));
$totalRecarrec = array_sum($recarrecAnual);
$totalCompras = array_sum(array_column($comprasAnual, 'total'));
$totalIngressos = $totalTiquets + $totalFacturas + $totalRecarrec;
$totalGeneral = $totalIngressos; // (compatibilitat amb codi existent; representa ingressos)

$totalBaseTiquets = array_sum(array_column($tiquetsAnual, 'base'));
$totalBaseFacturas = array_sum(array_column($facturasAnual, 'base'));
$totalBaseCompras = array_sum(array_column($comprasAnual, 'base'));
$totalIvaTiquets = array_sum(array_column($tiquetsAnual, 'cuota'));
$totalIvaFacturas = array_sum(array_column($facturasAnual, 'cuota'));
$totalIvaRepercutit = $totalIvaTiquets + $totalIvaFacturas + $totalRecarrec;
$totalIvaSuportat = array_sum(array_column($comprasAnual, 'cuota'));
$quotaIva = $totalIvaRepercutit - $totalIvaSuportat;

// Contadores
$numTiquets = 0;
if ($incCeller) {
    $rsCntT = mysqli_query($bd, "SELECT COUNT(*) as c FROM tiquets WHERE YEAR(fecha) = $year");
    $numTiquets = $rsCntT ? (int)mysqli_fetch_assoc($rsCntT)['c'] : 0;
}
$numFacturas = 0;
if ($incCeller) {
    $rsCntF = mysqli_query($bd, "SELECT COUNT(*) as c FROM facturas WHERE YEAR(fecha) = $year");
    $numFacturas += $rsCntF ? (int)mysqli_fetch_assoc($rsCntF)['c'] : 0;
}
if ($incOdr) {
    $rsCntFO = mysqli_query($bd, "SELECT COUNT(*) as c FROM odr_facturas WHERE YEAR(fecha) = $year");
    $numFacturas += $rsCntFO ? (int)mysqli_fetch_assoc($rsCntFO)['c'] : 0;
}

// Factures rebudes (compres)
$numCompras = 0;
if ($incCeller) {
    $rsCntC = mysqli_query($bd, "SELECT COUNT(*) as c FROM facturas_compra WHERE YEAR(fecha) = $year");
    $numCompras += $rsCntC ? (int)mysqli_fetch_assoc($rsCntC)['c'] : 0;
}
if ($incOdr) {
    $rsCntCO = mysqli_query($bd, "SELECT COUNT(*) as c FROM odr_facturas_compra WHERE YEAR(fecha) = $year");
    $numCompras += $rsCntCO ? (int)mysqli_fetch_assoc($rsCntCO)['c'] : 0;
}

// Todos los tipos de IVA presentes (ingressos + compres)
$allIvaRates = array_unique(array_merge(
    array_keys($tiquetsAnual),
    array_keys($facturasAnual),
    array_keys($comprasAnual)
));
sort($allIvaRates);

$trimLabels = [1 => '1T (Gen-Mar)', 2 => '2T (Abr-Jun)', 3 => '3T (Jul-Set)', 4 => '4T (Oct-Des)'];

// Helper
function fmtEur($v) { return number_format($v, 2, ',', '.'); }

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<!-- Selector d'any i marca -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <label style="font-weight: 600; color: var(--gray-600);">Any fiscal:</label>
            <select id="selYear" class="form-input" style="width: auto; padding: 0.4rem 0.75rem;">
                <?php foreach ($yearsAvailable as $y): ?>
                <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <label style="font-weight: 600; color: var(--gray-600);">Marca:</label>
            <select id="selMarca" class="form-input" style="width: auto; padding: 0.4rem 0.75rem;">
                <?php foreach ($marcaLabels as $mKey => $mLabel): ?>
                <option value="<?php echo $mKey; ?>" <?php echo $mKey === $marca ? 'selected' : ''; ?>><?php echo $mLabel; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <span style="font-size: 0.75rem; color: var(--gray-500); background: var(--gray-50); padding: 0.25rem 0.6rem; border-radius: 4px;">
            Societat fiscal: <strong>ALMABRIS GROUP 2024 S.L.</strong>
        </span>
    </div>
    <div style="font-size: 0.8rem; color: var(--gray-400);">
        Dades actualitzades en temps real
    </div>
</div>
<script>
(function(){
    function nav(){
        var y = document.getElementById('selYear').value;
        var m = document.getElementById('selMarca').value;
        window.location = '?any=' + encodeURIComponent(y) + '&marca=' + encodeURIComponent(m);
    }
    document.getElementById('selYear').addEventListener('change', nav);
    document.getElementById('selMarca').addEventListener('change', nav);
})();
</script>

<!-- Cards resum — EMESES (ingressos) -->
<h4 style="margin: 0 0 0.75rem; font-size: 0.8rem; text-transform: uppercase; color: var(--gray-500); letter-spacing: 0.05em;">Factures emeses (ingressos)</h4>
<div class="stats-grid" style="margin-bottom: 1.5rem;">
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
            <div class="stat-value"><?php echo fmtEur($totalIngressos); ?> &euro;</div>
            <div class="stat-label">Total Ingressos <?php echo $year; ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo fmtEur($totalIvaRepercutit); ?> &euro;</div>
            <div class="stat-label">IVA Repercutit</div>
        </div>
    </div>
</div>

<!-- Cards resum — REBUDES (despeses) -->
<h4 style="margin: 0 0 0.75rem; font-size: 0.8rem; text-transform: uppercase; color: var(--gray-500); letter-spacing: 0.05em;">Factures rebudes (despeses)</h4>
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #1d4ed8;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo fmtEur($totalBaseCompras); ?> &euro;</div>
            <div class="stat-label">Base compres</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #ede9fe; color: #7c3aed;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo fmtEur($totalIvaSuportat); ?> &euro;</div>
            <div class="stat-label">IVA Suportat</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fce7f3; color: #be185d;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo fmtEur($totalCompras); ?> &euro;</div>
            <div class="stat-label">Total Despeses (<?php echo number_format($numCompras); ?>)</div>
        </div>
    </div>
    <div class="stat-card" style="border: 2px solid <?php echo $quotaIva >= 0 ? '#dc2626' : '#059669'; ?>;">
        <div class="stat-icon" style="background: <?php echo $quotaIva >= 0 ? '#fee2e2' : '#d1fae5'; ?>; color: <?php echo $quotaIva >= 0 ? '#dc2626' : '#059669'; ?>;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value" style="color: <?php echo $quotaIva >= 0 ? '#dc2626' : '#059669'; ?>;">
                <?php echo ($quotaIva >= 0 ? '' : '+') . fmtEur($quotaIva); ?> &euro;
            </div>
            <div class="stat-label">Quota IVA <?php echo $quotaIva >= 0 ? '(a liquidar)' : '(a compensar)'; ?></div>
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
                        <th rowspan="2">Concepte</th>
                        <?php foreach ($allIvaRates as $rate): ?>
                        <th colspan="4" style="text-align: center; background: var(--gray-50);">IVA <?php echo $rate; ?>%</th>
                        <?php endforeach; ?>
                        <th rowspan="2" style="text-align: right;">Total Base</th>
                        <th rowspan="2" style="text-align: right;">Total IVA</th>
                        <th rowspan="2" style="text-align: right;">TOTAL</th>
                    </tr>
                    <tr>
                        <?php foreach ($allIvaRates as $rate): ?>
                        <th style="text-align: right; font-size: 0.7rem; color: var(--gray-500);">Base E.</th>
                        <th style="text-align: right; font-size: 0.7rem; color: var(--gray-500);">Quota E.</th>
                        <th style="text-align: right; font-size: 0.7rem; color: #1d4ed8; background: #eff6ff;">Base C.</th>
                        <th style="text-align: right; font-size: 0.7rem; color: #1d4ed8; background: #eff6ff;">Quota C.</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Tiquets</strong></td>
                        <?php $sumBase = 0; $sumIva = 0; foreach ($allIvaRates as $rate): $d = $tiquetsAnual[$rate] ?? ['base'=>0,'cuota'=>0,'total'=>0]; $sumBase += $d['base']; $sumIva += $d['cuota']; ?>
                        <td style="text-align: right;"><?php echo fmtEur($d['base']); ?></td>
                        <td style="text-align: right;"><?php echo fmtEur($d['cuota']); ?></td>
                        <td style="text-align: right; color: var(--gray-300); background: #fafbff;">&mdash;</td>
                        <td style="text-align: right; color: var(--gray-300); background: #fafbff;">&mdash;</td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($sumBase); ?></td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($sumIva); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($totalTiquets); ?> &euro;</td>
                    </tr>
                    <tr>
                        <td><strong>Factures emeses</strong></td>
                        <?php foreach ($allIvaRates as $rate): $d = $facturasAnual[$rate] ?? ['base'=>0,'cuota'=>0,'total'=>0]; $rec = $recarrecAnual[$rate] ?? 0; ?>
                        <td style="text-align: right;"><?php echo fmtEur($d['base']); ?></td>
                        <td style="text-align: right;"><?php echo fmtEur($d['cuota']); ?><?php if ($rec > 0): ?><br><span style="font-size:0.7rem;color:#d97706;">+RE <?php echo fmtEur($rec); ?></span><?php endif; ?></td>
                        <td style="text-align: right; color: var(--gray-300); background: #fafbff;">&mdash;</td>
                        <td style="text-align: right; color: var(--gray-300); background: #fafbff;">&mdash;</td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($totalBaseFacturas); ?></td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($totalIvaFacturas); ?><?php if ($totalRecarrec > 0): ?><br><span style="font-size:0.7rem;color:#d97706;">+RE <?php echo fmtEur($totalRecarrec); ?></span><?php endif; ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($totalFacturas + $totalRecarrec); ?> &euro;</td>
                    </tr>
                    <tr>
                        <td><strong style="color: #1d4ed8;">Factures rebudes</strong></td>
                        <?php foreach ($allIvaRates as $rate): $dc = $comprasAnual[$rate] ?? ['base'=>0,'cuota'=>0,'total'=>0]; ?>
                        <td style="text-align: right; color: var(--gray-300);">&mdash;</td>
                        <td style="text-align: right; color: var(--gray-300);">&mdash;</td>
                        <td style="text-align: right; color: #1d4ed8; background: #eff6ff;"><?php echo fmtEur($dc['base']); ?></td>
                        <td style="text-align: right; color: #1d4ed8; background: #eff6ff;"><?php echo fmtEur($dc['cuota']); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600; color: #1d4ed8;"><?php echo fmtEur($totalBaseCompras); ?></td>
                        <td style="text-align: right; font-weight: 600; color: #1d4ed8;"><?php echo fmtEur($totalIvaSuportat); ?></td>
                        <td style="text-align: right; font-weight: 700; color: #1d4ed8;"><?php echo fmtEur($totalCompras); ?> &euro;</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: var(--gray-50);">
                        <td><strong>TOTAL</strong></td>
                        <?php foreach ($allIvaRates as $rate):
                            $bte = ($tiquetsAnual[$rate]['base'] ?? 0) + ($facturasAnual[$rate]['base'] ?? 0);
                            $ite = ($tiquetsAnual[$rate]['cuota'] ?? 0) + ($facturasAnual[$rate]['cuota'] ?? 0);
                            $btr = $comprasAnual[$rate]['base'] ?? 0;
                            $itr = $comprasAnual[$rate]['cuota'] ?? 0;
                        ?>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($bte); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($ite); ?></td>
                        <td style="text-align: right; font-weight: 700; color: #1d4ed8; background: #eff6ff;"><?php echo fmtEur($btr); ?></td>
                        <td style="text-align: right; font-weight: 700; color: #1d4ed8; background: #eff6ff;"><?php echo fmtEur($itr); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 700;">E: <?php echo fmtEur($totalBaseTiquets + $totalBaseFacturas); ?><br><span style="color:#1d4ed8;">C: <?php echo fmtEur($totalBaseCompras); ?></span></td>
                        <td style="text-align: right; font-weight: 700;">E: <?php echo fmtEur($totalIvaRepercutit); ?><br><span style="color:#1d4ed8;">C: <?php echo fmtEur($totalIvaSuportat); ?></span></td>
                        <td style="text-align: right; font-weight: 700; font-size: 1.1rem; color: var(--primary);"><?php echo fmtEur($totalIngressos); ?></td>
                    </tr>
                    <tr style="background: #eff6ff;">
                        <td><strong>QUOTA IVA</strong></td>
                        <td colspan="<?php echo count($allIvaRates) * 4 + 2; ?>" style="text-align: right; font-size: 0.85rem; color: var(--gray-600);">
                            IVA Repercutit <strong><?php echo fmtEur($totalIvaRepercutit); ?> &euro;</strong>
                            &minus; IVA Suportat <strong><?php echo fmtEur($totalIvaSuportat); ?> &euro;</strong> =
                        </td>
                        <td style="text-align: right; font-weight: 700; font-size: 1.1rem; color: <?php echo $quotaIva >= 0 ? '#dc2626' : '#059669'; ?>;"><?php echo fmtEur($quotaIva); ?> &euro;</td>
                        <td style="text-align: right; font-size: 0.75rem; color: var(--gray-500);"><?php echo $quotaIva >= 0 ? 'a liquidar' : 'a compensar'; ?></td>
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
    $cData = $comprasData[$q];
    $qIvaRates = array_unique(array_merge(array_keys($tData), array_keys($fData), array_keys($cData)));
    sort($qIvaRates);

    $qTotalTiquets = array_sum(array_column($tData, 'total'));
    $qTotalFacturas = array_sum(array_column($fData, 'total'));
    $qTotalRecarrec = array_sum($rData);
    $qTotalCompras = array_sum(array_column($cData, 'total'));
    $qTotalIngressos = $qTotalTiquets + $qTotalFacturas + $qTotalRecarrec;

    $qBaseFacturas = array_sum(array_column($fData, 'base'));
    $qBaseCompras = array_sum(array_column($cData, 'base'));
    $qIvaTiquets = array_sum(array_column($tData, 'cuota'));
    $qIvaFacturas = array_sum(array_column($fData, 'cuota'));
    $qIvaCompras = array_sum(array_column($cData, 'cuota'));
    $qIvaRepercutit = $qIvaTiquets + $qIvaFacturas + $qTotalRecarrec;
    $qQuotaIva = $qIvaRepercutit - $qIvaCompras;
?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title"><?php echo $trimLabels[$q]; ?></h3>
        <div style="display: flex; gap: 1rem; font-size: 0.85rem; flex-wrap: wrap;">
            <span>Ingressos: <strong><?php echo fmtEur($qTotalIngressos); ?> &euro;</strong></span>
            <span style="color: #1d4ed8;">Despeses: <strong><?php echo fmtEur($qTotalCompras); ?> &euro;</strong></span>
            <span style="color: <?php echo $qQuotaIva >= 0 ? '#dc2626' : '#059669'; ?>; font-weight: 700;">
                Quota IVA: <?php echo fmtEur($qQuotaIva); ?> &euro;
            </span>
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
                        <th rowspan="2" style="width: 120px;">Concepte</th>
                        <?php foreach ($qIvaRates as $rate): ?>
                        <th colspan="4" style="text-align: center; background: var(--gray-50);">IVA <?php echo $rate; ?>%</th>
                        <?php endforeach; ?>
                        <th rowspan="2" style="text-align: right;">Total Base</th>
                        <th rowspan="2" style="text-align: right;">Total IVA</th>
                        <th rowspan="2" style="text-align: right;">TOTAL</th>
                    </tr>
                    <tr>
                        <?php foreach ($qIvaRates as $rate): ?>
                        <th style="text-align: right; font-size: 0.7rem; color: var(--gray-500);">Base E.</th>
                        <th style="text-align: right; font-size: 0.7rem; color: var(--gray-500);">Quota E.</th>
                        <th style="text-align: right; font-size: 0.7rem; color: #1d4ed8; background: #eff6ff;">Base C.</th>
                        <th style="text-align: right; font-size: 0.7rem; color: #1d4ed8; background: #eff6ff;">Quota C.</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tiquets</td>
                        <?php $sb=0;$si=0; foreach ($qIvaRates as $rate): $d=$tData[$rate]??['base'=>0,'cuota'=>0]; $sb+=$d['base']; $si+=$d['cuota']; ?>
                        <td style="text-align: right;"><?php echo fmtEur($d['base']); ?></td>
                        <td style="text-align: right;"><?php echo fmtEur($d['cuota']); ?></td>
                        <td style="text-align: right; color: var(--gray-300); background: #fafbff;">&mdash;</td>
                        <td style="text-align: right; color: var(--gray-300); background: #fafbff;">&mdash;</td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($sb); ?></td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($si); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($qTotalTiquets); ?></td>
                    </tr>
                    <tr>
                        <td>Factures emeses</td>
                        <?php foreach ($qIvaRates as $rate): $d=$fData[$rate]??['base'=>0,'cuota'=>0]; $rec=$rData[$rate]??0; ?>
                        <td style="text-align: right;"><?php echo fmtEur($d['base']); ?></td>
                        <td style="text-align: right;"><?php echo fmtEur($d['cuota']); ?><?php if($rec>0):?><br><span style="font-size:0.7rem;color:#d97706;">+RE <?php echo fmtEur($rec);?></span><?php endif;?></td>
                        <td style="text-align: right; color: var(--gray-300); background: #fafbff;">&mdash;</td>
                        <td style="text-align: right; color: var(--gray-300); background: #fafbff;">&mdash;</td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($qBaseFacturas); ?></td>
                        <td style="text-align: right; font-weight: 600;"><?php echo fmtEur($qIvaFacturas); ?><?php if($qTotalRecarrec>0):?><br><span style="font-size:0.7rem;color:#d97706;">+RE <?php echo fmtEur($qTotalRecarrec);?></span><?php endif;?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($qTotalFacturas + $qTotalRecarrec); ?></td>
                    </tr>
                    <tr>
                        <td style="color: #1d4ed8;">Factures rebudes</td>
                        <?php foreach ($qIvaRates as $rate): $dc=$cData[$rate]??['base'=>0,'cuota'=>0]; ?>
                        <td style="text-align: right; color: var(--gray-300);">&mdash;</td>
                        <td style="text-align: right; color: var(--gray-300);">&mdash;</td>
                        <td style="text-align: right; color: #1d4ed8; background: #eff6ff;"><?php echo fmtEur($dc['base']); ?></td>
                        <td style="text-align: right; color: #1d4ed8; background: #eff6ff;"><?php echo fmtEur($dc['cuota']); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 600; color: #1d4ed8;"><?php echo fmtEur($qBaseCompras); ?></td>
                        <td style="text-align: right; font-weight: 600; color: #1d4ed8;"><?php echo fmtEur($qIvaCompras); ?></td>
                        <td style="text-align: right; font-weight: 700; color: #1d4ed8;"><?php echo fmtEur($qTotalCompras); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: var(--gray-50);">
                        <td><strong>TOTAL <?php echo $q;?>T</strong></td>
                        <?php foreach ($qIvaRates as $rate):
                            $bte=($tData[$rate]['base']??0)+($fData[$rate]['base']??0);
                            $ite=($tData[$rate]['cuota']??0)+($fData[$rate]['cuota']??0);
                            $btr=$cData[$rate]['base']??0;
                            $itr=$cData[$rate]['cuota']??0;
                        ?>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($bte); ?></td>
                        <td style="text-align: right; font-weight: 700;"><?php echo fmtEur($ite); ?></td>
                        <td style="text-align: right; font-weight: 700; color: #1d4ed8; background: #eff6ff;"><?php echo fmtEur($btr); ?></td>
                        <td style="text-align: right; font-weight: 700; color: #1d4ed8; background: #eff6ff;"><?php echo fmtEur($itr); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: right; font-weight: 700;">E: <?php echo fmtEur($qBaseFacturas + array_sum(array_column($tData, 'base'))); ?><br><span style="color:#1d4ed8;">C: <?php echo fmtEur($qBaseCompras); ?></span></td>
                        <td style="text-align: right; font-weight: 700;">E: <?php echo fmtEur($qIvaRepercutit); ?><br><span style="color:#1d4ed8;">C: <?php echo fmtEur($qIvaCompras); ?></span></td>
                        <td style="text-align: right; font-weight: 700; color: var(--primary);"><?php echo fmtEur($qTotalIngressos); ?> &euro;</td>
                    </tr>
                    <tr style="background: #eff6ff;">
                        <td><strong>Quota <?php echo $q;?>T</strong></td>
                        <td colspan="<?php echo count($qIvaRates) * 4 + 2; ?>" style="text-align: right; font-size: 0.8rem; color: var(--gray-600);">
                            Repercutit <strong><?php echo fmtEur($qIvaRepercutit); ?></strong> &minus; Suportat <strong><?php echo fmtEur($qIvaCompras); ?></strong> =
                        </td>
                        <td style="text-align: right; font-weight: 700; color: <?php echo $qQuotaIva >= 0 ? '#dc2626' : '#059669'; ?>;"><?php echo fmtEur($qQuotaIva); ?> &euro;</td>
                        <td style="text-align: right; font-size: 0.75rem; color: var(--gray-500);"><?php echo $qQuotaIva >= 0 ? 'a liquidar' : 'a compensar'; ?></td>
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
    <?php if ($incCeller): ?>
    <a href="<?php echo getAdminUrl('modules/gestoria/tiquets.php?any=' . $year); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
        Llistat Tiquets (CellerNou)
    </a>
    <a href="<?php echo getAdminUrl('modules/gestoria/facturas.php?any=' . $year); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Llistat Factures (CellerNou)
    </a>
    <?php endif; ?>
    <?php if ($incOdr): ?>
    <a href="<?php echo getAdminUrl('modules/odr_facturas/'); ?>" class="btn btn-secondary" style="background: #0066FF;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Llistat Factures (ODR)
    </a>
    <?php endif; ?>
</div>

<style>
.form-input { padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114,47,55,0.1); }
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
