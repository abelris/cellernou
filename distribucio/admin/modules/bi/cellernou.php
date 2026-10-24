<?php
/**
 * BI Dashboard - El Celler Nou
 */
$pageTitle = 'BI El Celler Nou';
require_once __DIR__ . '/../../includes/auth.php';
$extraCss = '<link rel="stylesheet" href="' . getAdminUrl('modules/bi/bi.css') . '">';
require_once __DIR__ . '/../../includes/header.php';

$currentYear = intval(date('Y'));
$selectedYear = intval($_GET['year'] ?? $currentYear);
$years = range($currentYear, 2020);
$dataUrl = getAdminUrl('modules/bi/data.php');
$selectedMonthFrom = intval($_GET['mf'] ?? 1);
$selectedMonthTo   = intval($_GET['mt'] ?? 12);
$months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
?>

<!-- Toolbar -->
<div class="bi-toolbar">
    <label for="yearSelect">Any:</label>
    <select id="yearSelect" onchange="reloadDashboard()">
        <?php foreach ($years as $y): ?>
        <option value="<?php echo $y; ?>" <?php echo $y == $selectedYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
        <?php endforeach; ?>
    </select>
    <label for="monthFrom">Des de:</label>
    <select id="monthFrom" onchange="reloadDashboard()">
        <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?php echo $m; ?>" <?php echo $m == $selectedMonthFrom ? 'selected' : ''; ?>><?php echo $months[$m-1]; ?></option>
        <?php endfor; ?>
    </select>
    <label for="monthTo">Fins a:</label>
    <select id="monthTo" onchange="reloadDashboard()">
        <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?php echo $m; ?>" <?php echo $m == $selectedMonthTo ? 'selected' : ''; ?>><?php echo $months[$m-1]; ?></option>
        <?php endfor; ?>
    </select>
    <div class="bi-brand-tabs">
        <a href="<?php echo getAdminUrl('modules/bi/'); ?>" class="bi-brand-tab tab-all">Global</a>
        <a href="<?php echo getAdminUrl('modules/bi/cellernou.php'); ?>" class="bi-brand-tab active tab-cellernou">El Celler Nou</a>
        <a href="<?php echo getAdminUrl('modules/bi/ondigitalris.php'); ?>" class="bi-brand-tab tab-ondigitalris">ondigitalris</a>
    </div>
    <a href="<?php echo getAdminUrl('modules/bi/costos.php'); ?>" class="btn btn-secondary btn-sm">Correcció de costos</a>
</div>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi-card kpi-revenue">
        <div class="kpi-label">Facturació (sense IVA)</div>
        <div class="kpi-value" id="kpi-revenue">--</div>
        <div class="kpi-change neutral" id="kpi-revenue-yoy">--</div>
        <div class="kpi-prev" id="kpi-revenue-prev"></div>
    </div>
    <div class="kpi-card kpi-costs">
        <div class="kpi-label">Cost Producte Venut</div>
        <div class="kpi-value" id="kpi-costs">--</div>
        <div class="kpi-change neutral" id="kpi-costs-yoy">--</div>
        <div class="kpi-prev" id="kpi-costs-prev"></div>
    </div>
    <div class="kpi-card kpi-margin">
        <div class="kpi-label">Marge Brut</div>
        <div class="kpi-value" id="kpi-margin">--</div>
        <div class="kpi-change neutral" id="kpi-margin-yoy">--</div>
        <div class="kpi-prev" id="kpi-margin-prev"></div>
    </div>
    <div class="kpi-card kpi-growth">
        <div class="kpi-label">Marge %</div>
        <div class="kpi-value" id="kpi-marginpct">--</div>
        <div class="kpi-change neutral">Previsió anual</div>
        <div class="kpi-prev" id="kpi-forecast-val"></div>
    </div>
</div>

<!-- Charts -->
<div class="chart-grid">
    <div class="chart-card">
        <h3>Evolució de Facturació</h3>
        <canvas id="revenueChart"></canvas>
    </div>

    <div class="chart-row">
        <div class="chart-card">
            <h3>Tiquets vs Factures</h3>
            <canvas id="breakdownChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Evolució del Marge %</h3>
            <canvas id="marginChart"></canvas>
        </div>
    </div>

    <div class="chart-row">
        <div class="chart-card">
            <h3>Top 10 Productes</h3>
            <div id="topProductsContainer"></div>
        </div>
        <div class="chart-card">
            <h3>Previsió Anual</h3>
            <canvas id="forecastChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
var dataUrl = '<?php echo $dataUrl; ?>';
var charts = {};

function formatEur(n) { return new Intl.NumberFormat('es-ES', {style:'currency', currency:'EUR', maximumFractionDigits:0}).format(n); }
function formatPct(n) { if (n === null) return 'N/A'; return (n >= 0 ? '+' : '') + n.toFixed(1) + '%'; }
function setKPI(id, value, yoy, prev) {
    document.getElementById(id).textContent = formatEur(value);
    var yoyEl = document.getElementById(id + '-yoy');
    if (yoyEl) { yoyEl.textContent = yoy !== null ? formatPct(yoy) + ' vs any ant.' : 'Sense dades any ant.'; yoyEl.className = 'kpi-change ' + (yoy === null ? 'neutral' : (yoy >= 0 ? 'positive' : 'negative')); }
    var prevEl = document.getElementById(id + '-prev');
    if (prevEl && prev !== undefined) prevEl.textContent = 'Ant: ' + formatEur(prev);
}
function destroyCharts() { Object.keys(charts).forEach(function(k) { if (charts[k]) charts[k].destroy(); }); charts = {}; }

function getFilters() {
    var year = document.getElementById('yearSelect').value;
    var mf = document.getElementById('monthFrom').value;
    var mt = document.getElementById('monthTo').value;
    if (parseInt(mt) < parseInt(mf)) { mt = mf; document.getElementById('monthTo').value = mf; }
    return { year: year, mf: mf, mt: mt };
}

function reloadDashboard() {
    var f = getFilters();
    document.querySelectorAll('.bi-brand-tab').forEach(function(a) { var base = a.href.split('?')[0]; a.href = base + '?year=' + f.year + '&mf=' + f.mf + '&mt=' + f.mt; });
    loadDashboard(f.year, f.mf, f.mt);
}

function loadDashboard(year, mf, mt) {
    fetch(dataUrl + '?brand=cellernou&year=' + year + '&month_from=' + mf + '&month_to=' + mt)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            setKPI('kpi-revenue', d.kpis.revenue.value, d.kpis.revenue.yoy, d.kpis.revenue.prev);
            setKPI('kpi-costs', d.kpis.costs.value, d.kpis.costs.yoy, d.kpis.costs.prev);
            setKPI('kpi-margin', d.kpis.margin.value, d.kpis.margin.yoy, d.kpis.margin.prev);
            document.getElementById('kpi-marginpct').textContent = d.kpis.margin_pct.value + '%';
            document.getElementById('kpi-forecast-val').textContent = 'Previsió: ' + formatEur(d.kpis.forecast_eoy.value);
            destroyCharts();
            buildRevenueChart(d);
            buildBreakdownChart(d);
            buildMarginChart(d);
            buildForecastChart(d);
            buildTopProducts(d);
        })
        .catch(function(e) { console.error('BI data error:', e); });
}

function buildRevenueChart(d) {
    charts.revenue = new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: { labels: d.labels, datasets: [
            { label: d.year + ' Facturació', data: d.monthly.revenue.current, borderColor: '#722F37', backgroundColor: 'rgba(114,47,55,0.08)', fill: true, tension: 0.3, borderWidth: 2.5 },
            { label: d.prev_year + ' Facturació', data: d.monthly.revenue.previous, borderColor: '#9ca3af', borderDash: [6,3], fill: false, tension: 0.3, borderWidth: 1.5 }
        ] },
        options: { responsive: true, plugins: { tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': ' + formatEur(ctx.raw); } } } }, scales: { y: { ticks: { callback: function(v) { return formatEur(v); } } } } }
    });
}

function buildBreakdownChart(d) {
    charts.breakdown = new Chart(document.getElementById('breakdownChart'), {
        type: 'bar',
        data: { labels: d.labels, datasets: [
            { label: 'Tiquets', data: d.breakdown ? d.breakdown.tiquets : [], backgroundColor: 'rgba(114,47,55,0.7)' },
            { label: 'Factures', data: d.breakdown ? d.breakdown.facturas : [], backgroundColor: 'rgba(201,169,98,0.7)' }
        ] },
        options: { responsive: true, plugins: { tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': ' + formatEur(ctx.raw); } } } },
            scales: { x: { stacked: true }, y: { stacked: true, ticks: { callback: function(v) { return formatEur(v); } } } } }
    });
}

function buildMarginChart(d) {
    var marginPct = d.monthly.revenue.current.map(function(r,i) { return r > 0 ? Math.round((d.monthly.margin.current[i] / r) * 1000) / 10 : 0; });
    var prevMarginPct = d.monthly.revenue.previous.map(function(r,i) { return r > 0 ? Math.round((d.monthly.margin.previous[i] / r) * 1000) / 10 : 0; });
    charts.margin = new Chart(document.getElementById('marginChart'), {
        type: 'line',
        data: { labels: d.labels, datasets: [
            { label: d.year + ' Marge %', data: marginPct, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', fill: true, tension: 0.3, borderWidth: 2 },
            { label: d.prev_year + ' Marge %', data: prevMarginPct, borderColor: '#9ca3af', borderDash: [6,3], fill: false, tension: 0.3, borderWidth: 1.5 }
        ] },
        options: { responsive: true, scales: { y: { ticks: { callback: function(v) { return v + '%'; } }, min: 0 } } }
    });
}

function buildForecastChart(d) {
    var actualData = d.forecast.line.map(function(v,i) { return i < d.forecast.current_month ? v : null; });
    var forecastData = d.forecast.line.map(function(v,i) { if (i === d.forecast.current_month - 1) return d.forecast.line[i]; return i >= d.forecast.current_month ? v : null; });
    charts.forecast = new Chart(document.getElementById('forecastChart'), {
        type: 'line',
        data: { labels: d.labels, datasets: [
            { label: 'Real', data: actualData, borderColor: '#722F37', backgroundColor: 'rgba(114,47,55,0.08)', fill: true, tension: 0.3, borderWidth: 2.5 },
            { label: 'Previsió', data: forecastData, borderColor: '#722F37', borderDash: [8,4], backgroundColor: 'rgba(114,47,55,0.03)', fill: true, tension: 0.3, borderWidth: 2 }
        ] },
        options: { responsive: true, plugins: { tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': ' + formatEur(ctx.raw); } } } }, scales: { y: { ticks: { callback: function(v) { return formatEur(v); } } } } }
    });
}

function buildTopProducts(d) {
    var c = document.getElementById('topProductsContainer');
    if (!d.top_products || d.top_products.length === 0) { c.innerHTML = '<p style="color:var(--gray-400);text-align:center;padding:2rem">Sense dades</p>'; return; }
    var h = '<table class="top-products-table"><thead><tr><th class="rank">#</th><th>Producte</th><th style="text-align:right">Uds</th><th style="text-align:right">Facturació</th><th style="text-align:right">Marge</th></tr></thead><tbody>';
    d.top_products.forEach(function(p,i) { h += '<tr><td class="rank">'+(i+1)+'</td><td>'+p.nombre+'</td><td class="num">'+p.qty+'</td><td class="num">'+formatEur(p.revenue)+'</td><td class="num">'+formatEur(p.margin)+'</td></tr>'; });
    h += '</tbody></table>';
    c.innerHTML = h;
}

document.addEventListener('DOMContentLoaded', function() {
    loadDashboard(<?php echo $selectedYear; ?>, <?php echo $selectedMonthFrom; ?>, <?php echo $selectedMonthTo; ?>);
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
