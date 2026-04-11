<?php
/**
 * BI Dashboard - AJAX Data Endpoint
 * Returns JSON with KPIs, monthly data, forecast
 *
 * Parameters:
 *   brand      = all | cellernou | ondigitalris
 *   year       = YYYY (default current year)
 *   month_from = 1-12 (default 1)
 *   month_to   = 1-12 (default 12)
 */
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

header('Content-Type: application/json');
mysqli_query($bd, "SET SESSION sql_mode = ''");

$brand     = $_GET['brand'] ?? 'all';
$year      = intval($_GET['year'] ?? date('Y'));
$prevYear  = $year - 1;
$monthFrom = max(1, min(12, intval($_GET['month_from'] ?? 1)));
$monthTo   = max($monthFrom, min(12, intval($_GET['month_to'] ?? 12)));

$monthLabels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

function emptyMonths() {
    return array_fill(0, 12, 0);
}

function queryMonthly($bd, $sql) {
    $data = emptyMonths();
    $r = mysqli_query($bd, $sql);
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $m = intval($row['mn']) - 1;
            $data[$m] = round(floatval($row['val']), 2);
        }
    }
    return $data;
}

// Sum only selected month range (0-indexed)
function sumRange($arr, $from, $to) {
    $sum = 0;
    for ($i = $from; $i <= $to; $i++) {
        $sum += $arr[$i];
    }
    return round($sum, 2);
}

$mFrom0 = $monthFrom - 1; // 0-indexed
$mTo0   = $monthTo - 1;

// ============================================================
// CELLERNOU: revenue & COGS from linea_tiquet + linea_factura
// ============================================================
$cn = [
    'revenue'  => ['current' => emptyMonths(), 'previous' => emptyMonths()],
    'cogs'     => ['current' => emptyMonths(), 'previous' => emptyMonths()],
    'tiquets'  => ['current' => emptyMonths(), 'previous' => emptyMonths()],
    'facturas' => ['current' => emptyMonths(), 'previous' => emptyMonths()]
];

if ($brand === 'all' || $brand === 'cellernou') {
    foreach (['current' => $year, 'previous' => $prevYear] as $period => $yr) {

        $tiqRev = queryMonthly($bd, "
            SELECT MONTH(t.fecha) as mn, SUM(lt.cantidad * lt.precio_unidad) as val
            FROM linea_tiquet lt
            INNER JOIN tiquets t ON lt.idVenta = t.id
            WHERE YEAR(t.fecha) = $yr AND t.fecha > '2000-01-01'
            GROUP BY MONTH(t.fecha)
        ");
        $tiqCogs = queryMonthly($bd, "
            SELECT MONTH(t.fecha) as mn, SUM(lt.cantidad * lt.precio_compra) as val
            FROM linea_tiquet lt
            INNER JOIN tiquets t ON lt.idVenta = t.id
            WHERE YEAR(t.fecha) = $yr AND t.fecha > '2000-01-01'
            GROUP BY MONTH(t.fecha)
        ");

        $facRev = queryMonthly($bd, "
            SELECT MONTH(f.fecha) as mn, SUM(lf.cantidad * lf.precio_unidad) as val
            FROM linea_factura lf
            INNER JOIN facturas f ON lf.idFactura = f.id
            WHERE YEAR(f.fecha) = $yr AND f.fecha > '2000-01-01'
            GROUP BY MONTH(f.fecha)
        ");
        $facCogs = queryMonthly($bd, "
            SELECT MONTH(f.fecha) as mn, SUM(lf.cantidad * lf.precio_compra) as val
            FROM linea_factura lf
            INNER JOIN facturas f ON lf.idFactura = f.id
            WHERE YEAR(f.fecha) = $yr AND f.fecha > '2000-01-01'
            GROUP BY MONTH(f.fecha)
        ");

        for ($i = 0; $i < 12; $i++) {
            $cn['revenue'][$period][$i]  = round($tiqRev[$i] + $facRev[$i], 2);
            $cn['cogs'][$period][$i]     = round($tiqCogs[$i] + $facCogs[$i], 2);
            $cn['tiquets'][$period][$i]  = $tiqRev[$i];
            $cn['facturas'][$period][$i] = $facRev[$i];
        }
    }
}

// ============================================================
// ONDIGITALRIS: revenue & costs from line items
// ============================================================
$odr = [
    'revenue' => ['current' => emptyMonths(), 'previous' => emptyMonths()],
    'costs'   => ['current' => emptyMonths(), 'previous' => emptyMonths()]
];

if ($brand === 'all' || $brand === 'ondigitalris') {
    foreach (['current' => $year, 'previous' => $prevYear] as $period => $yr) {
        $odr['revenue'][$period] = queryMonthly($bd, "
            SELECT MONTH(f.fecha) as mn, SUM(l.cantidad * l.precio_unidad) as val
            FROM odr_linea_factura l
            INNER JOIN odr_facturas f ON l.idFactura = f.id
            WHERE YEAR(f.fecha) = $yr AND f.fecha > '2000-01-01'
            GROUP BY MONTH(f.fecha)
        ");
        $odr['costs'][$period] = queryMonthly($bd, "
            SELECT MONTH(f.fecha) as mn, SUM(l.cantidad * l.precio_unidad) as val
            FROM odr_linea_factura_compra l
            INNER JOIN odr_facturas_compra f ON l.idFactura = f.id
            WHERE YEAR(f.fecha) = $yr AND f.fecha > '2000-01-01'
            GROUP BY MONTH(f.fecha)
        ");
    }
}

// ============================================================
// COMBINE according to brand filter
// ============================================================
$revenue = ['current' => emptyMonths(), 'previous' => emptyMonths()];
$costs   = ['current' => emptyMonths(), 'previous' => emptyMonths()];
$margin  = ['current' => emptyMonths(), 'previous' => emptyMonths()];

for ($i = 0; $i < 12; $i++) {
    foreach (['current', 'previous'] as $p) {
        if ($brand === 'all') {
            $revenue[$p][$i] = $cn['revenue'][$p][$i] + $odr['revenue'][$p][$i];
            $costs[$p][$i]   = $cn['cogs'][$p][$i]    + $odr['costs'][$p][$i];
        } elseif ($brand === 'cellernou') {
            $revenue[$p][$i] = $cn['revenue'][$p][$i];
            $costs[$p][$i]   = $cn['cogs'][$p][$i];
        } else {
            $revenue[$p][$i] = $odr['revenue'][$p][$i];
            $costs[$p][$i]   = $odr['costs'][$p][$i];
        }
        $margin[$p][$i] = round($revenue[$p][$i] - $costs[$p][$i], 2);
    }
}

// ============================================================
// KPIs (filtered by month range)
// ============================================================
$totalRevCur    = sumRange($revenue['current'], $mFrom0, $mTo0);
$totalRevPrev   = sumRange($revenue['previous'], $mFrom0, $mTo0);
$totalCostsCur  = sumRange($costs['current'], $mFrom0, $mTo0);
$totalCostsPrev = sumRange($costs['previous'], $mFrom0, $mTo0);
$totalMarginCur = $totalRevCur - $totalCostsCur;
$totalMarginPrev= $totalRevPrev - $totalCostsPrev;

$yoyRev    = $totalRevPrev   > 0 ? round(($totalRevCur - $totalRevPrev) / $totalRevPrev * 100, 1) : null;
$yoyCosts  = $totalCostsPrev > 0 ? round(($totalCostsCur - $totalCostsPrev) / $totalCostsPrev * 100, 1) : null;
$yoyMargin = $totalMarginPrev!= 0 ? round(($totalMarginCur - $totalMarginPrev) / abs($totalMarginPrev) * 100, 1) : null;
$marginPct = $totalRevCur    > 0 ? round($totalMarginCur / $totalRevCur * 100, 1) : 0;

// ============================================================
// FORECAST: linear projection from months with data
// ============================================================
$currentMonth = ($year == date('Y')) ? intval(date('n')) : 12;
$monthsWithData = 0;
$runningTotal = 0;
for ($i = 0; $i < $currentMonth; $i++) {
    if ($revenue['current'][$i] > 0) {
        $monthsWithData++;
        $runningTotal += $revenue['current'][$i];
    }
}
$avgMonthly  = $monthsWithData > 0 ? $runningTotal / $monthsWithData : 0;
$forecastEOY = $runningTotal + $avgMonthly * (12 - $currentMonth);

$forecastLine = emptyMonths();
for ($i = 0; $i < 12; $i++) {
    $forecastLine[$i] = ($i < $currentMonth) ? $revenue['current'][$i] : round($avgMonthly, 2);
}

// ============================================================
// BUILD RESPONSE
// ============================================================
$response = [
    'year'       => $year,
    'prev_year'  => $prevYear,
    'labels'     => $monthLabels,
    'month_from' => $monthFrom,
    'month_to'   => $monthTo,
    'kpis' => [
        'revenue'      => ['value' => round($totalRevCur, 2),    'prev' => round($totalRevPrev, 2),    'yoy' => $yoyRev],
        'costs'        => ['value' => round($totalCostsCur, 2),  'prev' => round($totalCostsPrev, 2),  'yoy' => $yoyCosts],
        'margin'       => ['value' => round($totalMarginCur, 2), 'prev' => round($totalMarginPrev, 2), 'yoy' => $yoyMargin],
        'margin_pct'   => ['value' => $marginPct],
        'forecast_eoy' => ['value' => round($forecastEOY, 2)],
        'avg_monthly'  => ['value' => round($avgMonthly, 2)]
    ],
    'monthly' => [
        'revenue' => $revenue,
        'costs'   => $costs,
        'margin'  => $margin
    ],
    'forecast' => [
        'line'          => $forecastLine,
        'current_month' => $currentMonth
    ]
];

// Brand breakdown for combined dashboard
if ($brand === 'all') {
    $response['by_brand'] = [
        'cellernou' => [
            'revenue' => $cn['revenue']['current'],
            'total'   => sumRange($cn['revenue']['current'], $mFrom0, $mTo0)
        ],
        'ondigitalris' => [
            'revenue' => $odr['revenue']['current'],
            'total'   => sumRange($odr['revenue']['current'], $mFrom0, $mTo0)
        ]
    ];
}

// Source breakdown for CellerNou
if ($brand === 'cellernou') {
    $response['breakdown'] = [
        'tiquets'  => $cn['tiquets']['current'],
        'facturas' => $cn['facturas']['current'],
        'tiquets_total'  => sumRange($cn['tiquets']['current'], $mFrom0, $mTo0),
        'facturas_total' => sumRange($cn['facturas']['current'], $mFrom0, $mTo0)
    ];
}

// Top 10 products for CellerNou (filtered by month range)
if ($brand === 'cellernou') {
    $top = [];
    $r = mysqli_query($bd, "
        SELECT p.nombre,
               SUM(lt.cantidad) as qty,
               SUM(lt.cantidad * lt.precio_unidad) as revenue,
               SUM(lt.cantidad * lt.precio_compra) as cogs
        FROM linea_tiquet lt
        INNER JOIN tiquets t ON lt.idVenta = t.id
        INNER JOIN productos p ON lt.idProducto = p.id
        WHERE YEAR(t.fecha) = $year AND t.fecha > '2000-01-01'
          AND MONTH(t.fecha) BETWEEN $monthFrom AND $monthTo
        GROUP BY lt.idProducto
        ORDER BY revenue DESC
        LIMIT 10
    ");
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $top[] = [
                'nombre'  => $row['nombre'],
                'qty'     => intval($row['qty']),
                'revenue' => round(floatval($row['revenue']), 2),
                'margin'  => round(floatval($row['revenue']) - floatval($row['cogs']), 2)
            ];
        }
    }
    $response['top_products'] = $top;
}

echo json_encode($response);
