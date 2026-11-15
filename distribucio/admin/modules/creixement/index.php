<?php
/**
 * Plan de crecimiento de la tienda: tareas con casillas e indicadores.
 *
 * - Tareas: plan.php (fuente única). Estado en `crecimiento_estado`.
 * - Indicadores del TPV (tiquets y linea_tiquet), calculados al abrir:
 *   tiquet medio, % de tiquets de una sola línea y % de tiquets con granel
 *   que llevan algo más. Base = los 12 meses antes del inicio del plan.
 * - Contactos de la web: `clics_contacto`. Reseñas de Google: a mano
 *   (`crecimiento_kpi`).
 * Migración: sql/migration_crecimiento.sql
 */
$pageTitle = 'Plan de crecimiento';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$plan = require __DIR__ . '/plan.php';
$codigos = array_column($plan['tareas'], 'codigo');

// Granel: sector 10 y los dos productos de granel fuera de ese sector.
const CREC_GRANEL = '(COALESCE(p.idSector, 0) = 10 OR lt.idProducto IN (127, 6584))';

function crecFilas($bd, string $sql): array {
    $r = mysqli_query($bd, $sql);
    return $r ? mysqli_fetch_all($r, MYSQLI_ASSOC) : [];
}

// ---------- Guardar (AJAX y formulario de reseñas)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esAjax = ($_POST['accio'] ?? '') === 'tarea';
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        if ($esAjax) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'La sesión ha caducado. Recarga la página.']);
            exit;
        }
        $error = 'La sesión ha caducado. Vuelve a intentarlo.';
    } elseif ($esAjax) {
        header('Content-Type: application/json');
        $codigo = $_POST['codigo'] ?? '';
        if (!in_array($codigo, $codigos, true)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Tarea desconocida']);
            exit;
        }
        $hecho = !empty($_POST['hecho']) ? 1 : 0;
        $nota = trim((string) ($_POST['nota'] ?? ''));
        $usuario = $_SESSION['admin_user_name'] ?? '';
        $st = mysqli_prepare($bd, "INSERT INTO crecimiento_estado (codigo, hecho, hecho_en, hecho_por, nota)
            VALUES (?, ?, IF(? = 1, NOW(), NULL), IF(? = 1, ?, NULL), ?)
            ON DUPLICATE KEY UPDATE
                hecho_en  = IF(VALUES(hecho) = 1, IF(hecho = 1, hecho_en, NOW()), NULL),
                hecho_por = IF(VALUES(hecho) = 1, IF(hecho = 1, hecho_por, VALUES(hecho_por)), NULL),
                hecho = VALUES(hecho), nota = VALUES(nota)");
        mysqli_stmt_bind_param($st, 'siiiss', $codigo, $hecho, $hecho, $hecho, $usuario, $nota);
        $ok = mysqli_stmt_execute($st);
        if ($ok) {
            logActivity('creixement', $codigo . ($hecho ? ' hecha' : ' pendiente'));
        }
        $fila = crecFilas($bd, "SELECT hecho, DATE_FORMAT(hecho_en, '%d/%m/%Y') AS hecho_en, hecho_por FROM crecimiento_estado WHERE codigo = '" . mysqli_real_escape_string($bd, $codigo) . "'")[0] ?? [];
        echo json_encode(['ok' => $ok, 'estado' => $fila]);
        exit;
    } elseif (($_POST['accio'] ?? '') === 'kpi') {
        $mes = preg_match('/^\d{4}-\d{2}$/', $_POST['mes'] ?? '') ? $_POST['mes'] : date('Y-m');
        foreach (['resenas', 'nota_google'] as $ind) {
            $v = str_replace(',', '.', trim($_POST[$ind] ?? ''));
            if ($v === '' || !is_numeric($v)) {
                continue;
            }
            $st = mysqli_prepare($bd, "INSERT INTO crecimiento_kpi (mes, indicador, valor) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
            $num = (float) $v;
            mysqli_stmt_bind_param($st, 'ssd', $mes, $ind, $num);
            mysqli_stmt_execute($st);
        }
        header('Location: ?guardado=1#indicadores');
        exit;
    }
}

// ---------- Estado de las tareas
$estado = [];
foreach (crecFilas($bd, "SELECT codigo, hecho, DATE_FORMAT(hecho_en, '%d/%m/%Y') AS hecho_en, hecho_por, nota FROM crecimiento_estado") as $f) {
    $estado[$f['codigo']] = $f;
}
$total = count($plan['tareas']);
$hechas = 0;
foreach ($codigos as $c) {
    $hechas += !empty($estado[$c]['hecho']) ? 1 : 0;
}

// ---------- Indicadores del TPV
$inicio = $plan['inicio'];
$baseDesde = date('Y-m-d', strtotime($inicio . ' -12 months'));
$desdeMeses = date('Y-m-01', strtotime('first day of this month -12 months'));

$porTiquet = "SELECT lt.idVenta, MIN(t.fecha) AS fecha, SUM(lt.cantidad * lt.precio_unidad) AS tot, COUNT(*) AS lineas,
        MAX(" . CREC_GRANEL . ") AS granel, MAX(NOT " . CREC_GRANEL . ") AS otros
    FROM linea_tiquet lt
    JOIN tiquets t ON t.id = lt.idVenta
    LEFT JOIN productos p ON p.id = lt.idProducto
    WHERE lt.cantidad > 0 AND %s
    GROUP BY lt.idVenta";
$agregado = "COUNT(*) AS tiquets, SUM(tot) AS ventas, AVG(tot) AS medio,
    100 * AVG(lineas = 1) AS una_linea,
    100 * SUM(granel AND otros) / NULLIF(SUM(granel), 0) AS granel_mas";

$base = crecFilas($bd, "SELECT $agregado FROM (" . sprintf($porTiquet, "t.fecha >= '$baseDesde' AND t.fecha < '$inicio'") . ") x")[0] ?? [];
$meses = crecFilas($bd, "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, $agregado FROM (" . sprintf($porTiquet, "t.fecha >= '$desdeMeses'") . ") x GROUP BY mes ORDER BY mes DESC");
$mesesIdx = array_column($meses, null, 'mes');

$clics = array_column(crecFilas($bd, "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(clics) AS clics FROM clics_contacto WHERE fecha >= '$desdeMeses' GROUP BY mes"), 'clics', 'mes');
$kpiManual = [];
foreach (crecFilas($bd, "SELECT mes, indicador, valor FROM crecimiento_kpi") as $f) {
    $kpiManual[$f['mes']][$f['indicador']] = (float) $f['valor'];
}
krsort($kpiManual);
$ultResenas = null;
foreach ($kpiManual as $m => $v) {
    if (isset($v['resenas'])) { $ultResenas = ['mes' => $m] + $v; break; }
}

// Contactos web por semana: últimos 28 días.
$clics28 = (int) (crecFilas($bd, "SELECT SUM(clics) AS n FROM clics_contacto WHERE fecha >= CURDATE() - INTERVAL 28 DAY")[0]['n'] ?? 0);
$clicsSemana = $clics28 / 4;

// Mes de referencia: el último mes cerrado (el actual va a medias).
$mesRef = date('Y-m', strtotime('first day of last month'));
$mesActual = date('Y-m');
$ref = $mesesIdx[$mesRef] ?? [];
$act = $mesesIdx[$mesActual] ?? [];

// Diciembre: el mismo mes del año anterior + 15 %.
$dicAnterior = crecFilas($bd, "SELECT SUM(tot) AS ventas FROM (" . sprintf($porTiquet, "t.fecha >= '2025-12-01' AND t.fecha < '2026-01-01'") . ") x")[0]['ventas'] ?? 0;
$dicActual = $mesesIdx['2026-12']['ventas'] ?? null;

/** Indicadores: [clave, título, valor base, objetivo 3 meses, objetivo 12 meses, formato, sube = mejor] */
$bMedio = (float) ($base['medio'] ?? 0);
$bUna = (float) ($base['una_linea'] ?? 0);
$bGranel = (float) ($base['granel_mas'] ?? 0);
$indicadores = [
    ['medio', 'Tiquet medio', $bMedio, $bMedio + 1.3, $bMedio + 2.8, 'eur', true],
    ['una_linea', 'Tiquets de una sola línea', $bUna, $bUna - 6, $bUna - 12, 'pct', false],
    ['granel_mas', 'Granel que se lleva algo más', $bGranel, $bGranel + 8, $bGranel + 16, 'pct', true],
];

function crecFmt($v, string $f): string {
    if ($v === null || $v === '') return '—';
    $v = (float) $v;
    if ($f === 'eur') return number_format($v, 2, ',', '.') . ' €';
    if ($f === 'pct') return number_format($v, 1, ',', '.') . ' %';
    if ($f === 'ent') return number_format($v, 0, ',', '.');
    return number_format($v, 1, ',', '.');
}

/** ok = cumple el objetivo a 3 meses; mejora = va mejor que la base; si no, mal. */
function crecClase($v, float $base, float $obj3, bool $sube): string {
    if ($v === null || $v === '') return 'neutral';
    $v = (float) $v;
    if ($sube ? $v >= $obj3 : $v <= $obj3) return 'ok';
    if ($sube ? $v > $base : $v < $base) return 'mejora';
    return 'mal';
}

$nombresMes = ['01' => 'ene', '02' => 'feb', '03' => 'mar', '04' => 'abr', '05' => 'may', '06' => 'jun', '07' => 'jul', '08' => 'ago', '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'dic'];
$mesTxt = function (string $m) use ($nombresMes): string {
    return $nombresMes[substr($m, 5, 2)] . ' ' . substr($m, 0, 4);
};

$quienes = [];
foreach (array_column($plan['tareas'], 'quien') as $q) {
    foreach (explode(' y ', $q) as $persona) {
        $quienes[$persona] = true;
    }
}
$quienes = array_keys($quienes);
sort($quienes);

$extraCss = '<link rel="stylesheet" href="' . getAdminUrl('modules/bi/bi.css') . '">'
    . '<link rel="stylesheet" href="' . getAdminUrl('modules/creixement/creixement.css') . '?v=1">';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="crec">

<?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
<?php if (!empty($_GET['guardado'])): ?><div class="alert alert-success">Indicadores guardados.</div><?php endif; ?>

<section class="crec-cabecera">
    <div>
        <h2>Subir el tiquet medio y traer clientes desde la web</h2>
        <p>Plan aprobado el 15 de noviembre de 2026. Objetivo a 12 meses: +25 % de ventas en tienda, la mitad por tiquet medio y el resto por clientes nuevos que llegan por la web.</p>
    </div>
    <div class="crec-progreso" aria-label="Progreso del plan">
        <div class="crec-progreso-num"><span id="crec-hechas"><?php echo $hechas; ?></span> / <?php echo $total; ?> tareas</div>
        <div class="crec-barra"><span id="crec-barra" style="width: <?php echo $total ? round(100 * $hechas / $total) : 0; ?>%"></span></div>
    </div>
</section>

<section id="indicadores">
    <h2 class="crec-h2">Indicadores</h2>
    <p class="crec-nota">Del TPV, calculados al abrir la página. Mes de referencia: <strong><?php echo $mesTxt($mesRef); ?></strong> (el último cerrado); entre paréntesis, lo que va de <?php echo $mesTxt($mesActual); ?>. Base: los 12 meses antes del plan.</p>

    <div class="kpi-grid">
        <?php foreach ($indicadores as [$k, $titulo, $b, $o3, $o12, $f, $sube]):
            $v = $ref[$k] ?? null; $clase = crecClase($v, $b, $o3, $sube); ?>
        <div class="kpi-card crec-kpi crec-<?php echo $clase; ?>">
            <div class="kpi-label"><?php echo e($titulo); ?></div>
            <div class="kpi-value"><?php echo crecFmt($v, $f); ?> <small>(<?php echo crecFmt($act[$k] ?? null, $f); ?>)</small></div>
            <div class="kpi-prev">Base <?php echo crecFmt($b, $f); ?> · Objetivo <?php echo crecFmt($o3, $f); ?> en 3 meses, <?php echo crecFmt($o12, $f); ?> en 12</div>
        </div>
        <?php endforeach; ?>

        <?php $o3w = 25; $cw = $clicsSemana >= $o3w ? 'ok' : ($clicsSemana > 0 ? 'mejora' : 'neutral'); ?>
        <div class="kpi-card crec-kpi crec-<?php echo $cw; ?>">
            <div class="kpi-label">Contactos web por semana</div>
            <div class="kpi-value"><?php echo crecFmt($clicsSemana, 'num'); ?></div>
            <div class="kpi-prev">WhatsApp y llamadas, media de los últimos 28 días · Objetivo 25 en 3 meses, 50 en 12 · <a href="<?php echo getAdminUrl('modules/bi/clics.php'); ?>">detalle</a></div>
        </div>

        <?php $cr = $ultResenas ? (($ultResenas['resenas'] >= 220) ? 'ok' : 'mejora') : 'neutral'; ?>
        <div class="kpi-card crec-kpi crec-<?php echo $cr; ?>">
            <div class="kpi-label">Reseñas en Google</div>
            <div class="kpi-value"><?php echo $ultResenas ? crecFmt($ultResenas['resenas'], 'ent') : '—'; ?>
                <?php if (isset($ultResenas['nota_google'])): ?><small>(<?php echo crecFmt($ultResenas['nota_google'], 'num'); ?> ★)</small><?php endif; ?></div>
            <div class="kpi-prev"><?php echo $ultResenas ? 'Apuntadas en ' . $mesTxt($ultResenas['mes']) . ' · ' : 'Apúntalas abajo cada mes · '; ?>Partimos de 158 (4,4) · Objetivo 220 en 3 meses, 350 (4,6) en 12</div>
        </div>

        <?php $o = $dicAnterior * 1.15; $cd = $dicActual === null ? 'neutral' : ($dicActual >= $o ? 'ok' : ($dicActual > $dicAnterior ? 'mejora' : 'mal')); ?>
        <div class="kpi-card crec-kpi crec-<?php echo $cd; ?>">
            <div class="kpi-label">Diciembre 2026</div>
            <div class="kpi-value"><?php echo crecFmt($dicActual, 'eur'); ?></div>
            <div class="kpi-prev">Diciembre 2025: <?php echo crecFmt($dicAnterior, 'eur'); ?> · Objetivo +15 %: <?php echo crecFmt($o, 'eur'); ?></div>
        </div>
    </div>

    <div class="crec-leyenda">
        <span class="crec-punto ok"></span> Cumple el objetivo a 3 meses
        <span class="crec-punto mejora"></span> Mejor que la base
        <span class="crec-punto mal"></span> Peor que la base
    </div>

    <div class="chart-card crec-tabla-card">
        <h3>Mes a mes</h3>
        <div class="crec-scroll">
        <table class="top-products-table crec-tabla">
            <thead><tr><th>Mes</th><th>Tiquets</th><th>Ventas</th><th>Tiquet medio</th><th>Una línea</th><th>Granel + algo</th><th>Contactos web</th><th>Reseñas</th></tr></thead>
            <tbody>
            <tr class="crec-base">
                <td>Base (12 meses)</td>
                <td class="num"><?php echo crecFmt($base['tiquets'] ?? null, 'ent'); ?></td>
                <td class="num"><?php echo crecFmt($base['ventas'] ?? null, 'eur'); ?></td>
                <td class="num"><?php echo crecFmt($bMedio, 'eur'); ?></td>
                <td class="num"><?php echo crecFmt($bUna, 'pct'); ?></td>
                <td class="num"><?php echo crecFmt($bGranel, 'pct'); ?></td>
                <td class="num">—</td><td class="num">158</td>
            </tr>
            <?php foreach ($meses as $m): ?>
            <tr<?php echo $m['mes'] === $mesActual ? ' class="crec-curso"' : ''; ?>>
                <td><?php echo $mesTxt($m['mes']); ?><?php echo $m['mes'] === $mesActual ? ' <small>(en curso)</small>' : ''; ?></td>
                <td class="num"><?php echo crecFmt($m['tiquets'], 'ent'); ?></td>
                <td class="num"><?php echo crecFmt($m['ventas'], 'eur'); ?></td>
                <td class="num crec-<?php echo crecClase($m['medio'], $bMedio, $bMedio + 1.3, true); ?>"><?php echo crecFmt($m['medio'], 'eur'); ?></td>
                <td class="num crec-<?php echo crecClase($m['una_linea'], $bUna, $bUna - 6, false); ?>"><?php echo crecFmt($m['una_linea'], 'pct'); ?></td>
                <td class="num crec-<?php echo crecClase($m['granel_mas'], $bGranel, $bGranel + 8, true); ?>"><?php echo crecFmt($m['granel_mas'], 'pct'); ?></td>
                <td class="num"><?php echo isset($clics[$m['mes']]) ? (int) $clics[$m['mes']] : '—'; ?></td>
                <td class="num"><?php echo isset($kpiManual[$m['mes']]['resenas']) ? crecFmt($kpiManual[$m['mes']]['resenas'], 'ent') : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <form method="POST" class="crec-form-kpi">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="hidden" name="accio" value="kpi">
        <strong>Reseñas de Google</strong>
        <label>Mes <input type="month" name="mes" value="<?php echo $mesActual; ?>" required></label>
        <label>Número <input type="number" name="resenas" min="0" step="1" inputmode="numeric"></label>
        <label>Nota <input type="number" name="nota_google" min="1" max="5" step="0.1" inputmode="decimal"></label>
        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
    </form>
</section>

<section id="tareas">
    <div class="crec-tareas-cab">
        <h2 class="crec-h2">Tareas</h2>
        <div class="crec-filtros" role="group" aria-label="Filtros">
            <select id="f-estado" aria-label="Estado">
                <option value="">Todas</option>
                <option value="pendiente">Pendientes</option>
                <option value="hecha">Hechas</option>
            </select>
            <select id="f-bloque" aria-label="Bloque">
                <option value="">Tienda y web</option>
                <?php foreach ($plan['bloques'] as $k => $b): ?><option value="<?php echo e($k); ?>"><?php echo e($b); ?></option><?php endforeach; ?>
            </select>
            <select id="f-quien" aria-label="Quién">
                <option value="">Todo el equipo</option>
                <?php foreach ($quienes as $q): ?><option value="<?php echo e($q); ?>"><?php echo e($q); ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php foreach ($plan['fases'] as $idFase => $fase):
        $tareasFase = array_filter($plan['tareas'], function ($t) use ($idFase) { return $t['fase'] === $idFase; });
        $hechasFase = count(array_filter($tareasFase, function ($t) use ($estado) { return !empty($estado[$t['codigo']]['hecho']); }));
    ?>
    <div class="crec-fase" data-fase="<?php echo $idFase; ?>">
        <h3><?php echo e($fase['nombre']); ?> <span class="crec-fase-cuando"><?php echo e($fase['cuando']); ?></span>
            <span class="crec-fase-cuenta"><span class="n"><?php echo $hechasFase; ?></span>/<?php echo count($tareasFase); ?></span></h3>
        <ul class="crec-lista">
        <?php foreach ($tareasFase as $t):
            $s = $estado[$t['codigo']] ?? []; $h = !empty($s['hecho']); ?>
            <li class="crec-tarea<?php echo $h ? ' hecha' : ''; ?>" data-codigo="<?php echo e($t['codigo']); ?>" data-bloque="<?php echo e($t['bloque']); ?>" data-quien="<?php echo e($t['quien']); ?>">
                <label class="crec-check">
                    <input type="checkbox" <?php echo $h ? 'checked' : ''; ?> aria-label="Marcar como hecha: <?php echo e($t['titulo']); ?>">
                    <span class="crec-titulo"><?php echo e($t['titulo']); ?></span>
                </label>
                <div class="crec-meta">
                    <span class="crec-tag crec-tag-<?php echo e($t['bloque']); ?>"><?php echo $t['bloque'] === 'tienda' ? 'Tienda' : 'Web'; ?></span>
                    <span class="crec-quien"><?php echo e($t['quien']); ?></span>
                    <span class="crec-hecha-info"><?php echo $h ? 'Hecha el ' . e($s['hecho_en']) . ($s['hecho_por'] ? ' por ' . e($s['hecho_por']) : '') : ''; ?></span>
                </div>
                <details>
                    <summary>Detalle y notas</summary>
                    <p><?php echo e($t['detalle']); ?></p>
                    <textarea rows="2" placeholder="Notas: qué se ha hecho, qué falta, cómo ha ido…" aria-label="Notas de <?php echo e($t['codigo']); ?>"><?php echo e($s['nota'] ?? ''); ?></textarea>
                    <button type="button" class="btn btn-secondary btn-sm crec-guardar-nota">Guardar nota</button>
                    <span class="crec-guardado" aria-live="polite"></span>
                </details>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>
</section>

</div>

<script>
(function () {
    var csrf = <?php echo json_encode(generateCsrfToken()); ?>;
    var total = <?php echo $total; ?>;

    function contar() {
        var hechas = document.querySelectorAll('.crec-tarea.hecha').length;
        document.getElementById('crec-hechas').textContent = hechas;
        document.getElementById('crec-barra').style.width = Math.round(100 * hechas / total) + '%';
        document.querySelectorAll('.crec-fase').forEach(function (f) {
            f.querySelector('.crec-fase-cuenta .n').textContent = f.querySelectorAll('.crec-tarea.hecha').length;
        });
    }

    function guardar(li, aviso) {
        var datos = new FormData();
        datos.append('csrf_token', csrf);
        datos.append('accio', 'tarea');
        datos.append('codigo', li.dataset.codigo);
        datos.append('hecho', li.querySelector('input[type=checkbox]').checked ? '1' : '');
        datos.append('nota', li.querySelector('textarea').value);
        var check = li.querySelector('input[type=checkbox]');
        check.disabled = true;
        return fetch(location.pathname, { method: 'POST', body: datos, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j.ok) throw new Error(j.error || 'No se ha podido guardar');
                var s = j.estado || {};
                li.classList.toggle('hecha', s.hecho == 1);
                li.querySelector('.crec-hecha-info').textContent = s.hecho == 1
                    ? 'Hecha el ' + s.hecho_en + (s.hecho_por ? ' por ' + s.hecho_por : '') : '';
                if (aviso) aviso.textContent = 'Guardado';
                contar();
            })
            .catch(function (e) {
                if (aviso) aviso.textContent = e.message;
                else alertaError(li, e.message);
                check.checked = li.classList.contains('hecha');
            })
            .finally(function () { check.disabled = false; });
    }

    function alertaError(li, msg) {
        var info = li.querySelector('.crec-hecha-info');
        info.textContent = msg;
        info.classList.add('error');
    }

    document.querySelectorAll('.crec-tarea').forEach(function (li) {
        li.querySelector('input[type=checkbox]').addEventListener('change', function () { guardar(li, null); });
        li.querySelector('.crec-guardar-nota').addEventListener('click', function () {
            guardar(li, li.querySelector('.crec-guardado'));
        });
    });

    function filtrar() {
        var e = document.getElementById('f-estado').value;
        var b = document.getElementById('f-bloque').value;
        var q = document.getElementById('f-quien').value;
        document.querySelectorAll('.crec-tarea').forEach(function (li) {
            var ver = (!e || (e === 'hecha') === li.classList.contains('hecha'))
                && (!b || li.dataset.bloque === b)
                && (!q || li.dataset.quien.indexOf(q) !== -1);
            li.hidden = !ver;
        });
        document.querySelectorAll('.crec-fase').forEach(function (f) {
            f.hidden = !f.querySelector('.crec-tarea:not([hidden])');
        });
    }
    ['f-estado', 'f-bloque', 'f-quien'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', filtrar);
    });
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
