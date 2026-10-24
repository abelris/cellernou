<?php
/**
 * El Celler Nou - BI - Correcció de costos de les vendes
 *
 * Arregla el cost (precio_compra) de les línies de tiquet i de factura que el
 * tenen malament, perquè el marge del BI sigui real:
 *
 *  1. Línies amb cost 0 (tiquets i factures, tots els anys): se'ls posa el
 *     cost mitjà de compra del producte a la data de la venda.
 *  2. Tiquets des del 05/03/2026 (admin nou): gravaven productos.precio_medio,
 *     que no s'actualitzava amb les compres. Es recalculen amb el cost mitjà de
 *     compra a la data, si el producte té compres.
 *
 * Cost mitjà a una data = mitjana ponderada de les compres dels 12 mesos
 * anteriors (sense els preus absurds); si no n'hi ha, la darrera compra
 * anterior; si no, la primera posterior; si no, el precio_medio que tingui el
 * producte (posat a mà). És la regla de includes/costos.php.
 *
 * Sense POST només simula. Cada canvi aplicat queda a `costos_correccio_log`
 * amb el cost anterior, de manera que es pot desfer.
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/costos.php';
requireAuth();

@set_time_limit(300);
mysqli_query($bd, "SET SESSION sql_mode = ''");

define('INICI_ADMIN_NOU', '2026-03-05');
$pageTitle = 'Correcció de costos';
$missatge = '';
$errors = [];

// ------------------------------------------------------------------
// Costos posats a mà per als productes sense cap compra
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accio'] ?? '') === 'costos_manuals') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        $n = 0;
        foreach (($_POST['cost'] ?? []) as $idProd => $valor) {
            $valor = (float)str_replace(',', '.', trim($valor));
            if ($valor > 0) {
                mysqli_query($bd, "UPDATE productos SET precio_medio = " . round($valor, 4) . " WHERE id = " . (int)$idProd);
                $n++;
            }
        }
        if ($n) logActivity('costos_manuals', "Cost posat a mà a $n productes sense compres");
        $missatge = $n ? "Cost desat a $n productes. Revisa la simulació i aplica la correcció." : 'No has posat cap cost.';
    }
}

// ------------------------------------------------------------------
// Compres de tots els productes, en memòria (una sola consulta)
// ------------------------------------------------------------------
$compres = [];
$r = mysqli_query($bd, "SELECT idProducto, UNIX_TIMESTAMP(fecha) AS ts, cantidad, precio_unidad
                        FROM linea_compra
                        WHERE precio_unidad > 0 AND cantidad > 0 AND fecha > '2000-01-01'
                        ORDER BY idProducto, fecha, id");
while ($r && ($row = mysqli_fetch_assoc($r))) {
    $compres[(int)$row['idProducto']][] = [(int)$row['ts'], (float)$row['cantidad'], (float)$row['precio_unidad']];
}
// Fora les compres amb preus absurds (errors d'introducció).
$compres = array_map('filtrarCompresAnomales', $compres);

$preuMig = [];
$nomProducte = [];
$r = mysqli_query($bd, "SELECT id, nombre, precio_medio FROM productos");
while ($r && ($row = mysqli_fetch_assoc($r))) {
    $preuMig[(int)$row['id']] = (float)$row['precio_medio'];
    $nomProducte[(int)$row['id']] = $row['nombre'];
}

/**
 * Cost mitjà de compra d'un producte a una data (timestamp), amb la regla de
 * includes/costos.php. $ambManual: si no té compres, tornar el precio_medio
 * posat a mà.
 */
function costALaData(int $idProd, int $ts, bool $ambManual = true): float {
    global $compres, $preuMig;
    $cost = isset($compres[$idProd]) ? costMitjaDeCompres($compres[$idProd], $ts) : 0;
    if ($cost > 0) return $cost;
    return ($ambManual && ($preuMig[$idProd] ?? 0) > 0) ? $preuMig[$idProd] : 0;
}

// ------------------------------------------------------------------
// Línies a revisar
// ------------------------------------------------------------------
$canvis = [];          // [taula, id, any, cost_anterior, cost_nou, quantitat, causa]
$senseCost = [];       // idProducto => [línies, unitats, vendes]

$consultes = [
    'linea_tiquet' => "SELECT lt.id, lt.idProducto, lt.cantidad, lt.precio_compra, UNIX_TIMESTAMP(t.fecha) AS ts,
                              YEAR(t.fecha) AS any_venda,
                              lt.cantidad * COALESCE(NULLIF(lt.precio_unidad_sin_imp, 0), lt.precio_unidad / (1 + lt.iva / 100)) AS base
                       FROM linea_tiquet lt
                       INNER JOIN tiquets t ON lt.idVenta = t.id
                       WHERE t.fecha > '2000-01-01'
                         AND (lt.precio_compra <= 0 OR t.fecha >= '" . INICI_ADMIN_NOU . "')",
    'linea_factura' => "SELECT lf.id, lf.idProducto, lf.cantidad, lf.precio_compra, UNIX_TIMESTAMP(f.fecha) AS ts,
                               YEAR(f.fecha) AS any_venda, lf.cantidad * lf.precio_unidad AS base
                        FROM linea_factura lf
                        INNER JOIN facturas f ON lf.idFactura = f.id
                        WHERE f.fecha > '2000-01-01' AND lf.precio_compra <= 0",
];

foreach ($consultes as $taula => $sql) {
    $r = mysqli_query($bd, $sql);
    while ($r && ($row = mysqli_fetch_assoc($r))) {
        $idProd = (int)$row['idProducto'];
        if (!isset($nomProducte[$idProd])) continue;   // producte esborrat

        $anterior = (float)$row['precio_compra'];
        $esZero = $anterior <= 0;
        // Les línies amb cost ja posat només es recalculen si hi ha compres:
        // el precio_medio manual és justament el que ja tenen.
        $nou = costALaData($idProd, (int)$row['ts'], $esZero);

        if ($nou <= 0) {
            if ($esZero) {
                $s = &$senseCost[$idProd];
                $s = $s ?? [0, 0, 0];
                $s[0]++; $s[1] += (float)$row['cantidad']; $s[2] += (float)$row['base'];
                unset($s);
            }
            continue;
        }
        $nou = round($nou, 3);
        if (abs($nou - $anterior) < 0.001) continue;

        $canvis[] = [$taula, (int)$row['id'], (int)$row['any_venda'], $anterior, $nou, (float)$row['cantidad'],
                     $esZero ? 'cost 0' : 'precio_medio desfasat'];
    }
}

// ------------------------------------------------------------------
// Aplicar
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accio'] ?? '') === 'aplicar' && empty($errors)) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        mysqli_query($bd, "CREATE TABLE IF NOT EXISTS costos_correccio_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            taula VARCHAR(20) NOT NULL,
            idLinia INT NOT NULL,
            cost_anterior DECIMAL(12,4) NOT NULL,
            cost_nou DECIMAL(12,4) NOT NULL,
            causa VARCHAR(40) NOT NULL,
            aplicat DATETIME NOT NULL,
            idUsuari INT NULL,
            KEY (taula, idLinia)
        )");

        mysqli_begin_transaction($bd);
        $ok = true;
        $usuari = (int)($_SESSION['admin_user_id'] ?? 0);
        $stmtUpd = [
            'linea_tiquet'  => mysqli_prepare($bd, "UPDATE linea_tiquet SET precio_compra = ? WHERE id = ?"),
            'linea_factura' => mysqli_prepare($bd, "UPDATE linea_factura SET precio_compra = ? WHERE id = ?"),
        ];
        $stmtLog = mysqli_prepare($bd, "INSERT INTO costos_correccio_log (taula, idLinia, cost_anterior, cost_nou, causa, aplicat, idUsuari)
                                        VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        foreach ($canvis as [$taula, $id, , $anterior, $nou, , $causa]) {
            mysqli_stmt_bind_param($stmtUpd[$taula], 'di', $nou, $id);
            mysqli_stmt_bind_param($stmtLog, 'siddsi', $taula, $id, $anterior, $nou, $causa, $usuari);
            if (!mysqli_stmt_execute($stmtUpd[$taula]) || !mysqli_stmt_execute($stmtLog)) { $ok = false; break; }
        }

        // precio_medio al dia per a tots els productes amb compres.
        if ($ok) {
            foreach (array_keys($compres) as $idProd) {
                if (!isset($nomProducte[$idProd])) continue;
                $cost = costALaData($idProd, time(), false);
                if ($cost > 0 && abs($cost - ($preuMig[$idProd] ?? 0)) >= 0.0001) {
                    if (!mysqli_query($bd, "UPDATE productos SET precio_medio = " . round($cost, 4) . " WHERE id = $idProd")) { $ok = false; break; }
                }
            }
        }

        if ($ok) {
            mysqli_commit($bd);
            logActivity('costos_correccio', count($canvis) . ' línies de venda amb el cost corregit');
            header('Location: ' . getAdminUrl('modules/bi/costos.php?fet=' . count($canvis)));
            exit;
        }
        mysqli_rollback($bd);
        $errors[] = 'No s\'ha pogut aplicar: ' . mysqli_error($bd) . '. No s\'ha canviat res.';
    }
}

if (isset($_GET['fet'])) {
    $missatge = 'Correcció aplicada: ' . (int)$_GET['fet'] . ' línies actualitzades i precio_medio posat al dia. '
        . 'El que queda a sota és el que encara es pot corregir (si no queda res, ja està tot).';
}

// ------------------------------------------------------------------
// Resum per any: marge actual i marge després de corregir
// ------------------------------------------------------------------
$anys = [];
$r = mysqli_query($bd, "
    SELECT any_venda, SUM(base) AS base, SUM(cost) AS cost FROM (
        SELECT YEAR(t.fecha) AS any_venda,
               lt.cantidad * COALESCE(NULLIF(lt.precio_unidad_sin_imp, 0), lt.precio_unidad / (1 + lt.iva / 100)) AS base,
               lt.cantidad * lt.precio_compra AS cost
        FROM linea_tiquet lt INNER JOIN tiquets t ON lt.idVenta = t.id
        WHERE t.fecha >= '2022-01-01'
        UNION ALL
        SELECT YEAR(f.fecha), lf.cantidad * lf.precio_unidad, lf.cantidad * lf.precio_compra
        FROM linea_factura lf INNER JOIN facturas f ON lf.idFactura = f.id
        WHERE f.fecha >= '2022-01-01'
    ) x GROUP BY any_venda ORDER BY any_venda DESC");
while ($r && ($row = mysqli_fetch_assoc($r))) {
    $anys[(int)$row['any_venda']] = ['base' => (float)$row['base'], 'cost' => (float)$row['cost'], 'delta' => 0, 'linies' => 0];
}
$perCausa = [];
foreach ($canvis as [$taula, , $any, $anterior, $nou, $cant, $causa]) {
    if (isset($anys[$any])) {
        $anys[$any]['delta'] += ($nou - $anterior) * $cant;
        $anys[$any]['linies']++;
    }
    $clau = ($taula === 'linea_tiquet' ? 'Tiquets' : 'Factures') . ' · ' . $causa;
    $perCausa[$clau] = ($perCausa[$clau] ?? 0) + 1;
}
uasort($senseCost, function ($a, $b) { return $b[2] <=> $a[2]; });

function pct($base, $cost) { return $base > 0 ? number_format(($base - $cost) / $base * 100, 1, ',', '.') . ' %' : '-'; }
function eur($v) { return number_format($v, 2, ',', '.') . ' €'; }

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/bi/cellernou.php'); ?>" class="btn btn-secondary">Tornar al BI</a>
</div>

<?php if ($missatge): ?>
<div class="alert" style="margin-bottom: 1rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.875rem 1rem; border-radius: 8px;"><?php echo e($missatge); ?></div>
<?php endif; ?>
<?php foreach ($errors as $err): ?>
<div class="alert" style="margin-bottom: 1rem; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 0.875rem 1rem; border-radius: 8px;"><?php echo e($err); ?></div>
<?php endforeach; ?>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3 class="card-title">Marge per any (sense IVA)</h3></div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead><tr>
                    <th>Any</th><th style="text-align:right">Vendes</th><th style="text-align:right">Cost actual</th>
                    <th style="text-align:right">Marge actual</th><th style="text-align:right">Línies a corregir</th>
                    <th style="text-align:right">Cost corregit</th><th style="text-align:right">Marge corregit</th>
                </tr></thead>
                <tbody>
                <?php foreach ($anys as $any => $a): ?>
                <tr>
                    <td><?php echo $any; ?></td>
                    <td style="text-align:right"><?php echo eur($a['base']); ?></td>
                    <td style="text-align:right"><?php echo eur($a['cost']); ?></td>
                    <td style="text-align:right"><?php echo pct($a['base'], $a['cost']); ?></td>
                    <td style="text-align:right"><?php echo number_format($a['linies'], 0, ',', '.'); ?></td>
                    <td style="text-align:right"><?php echo eur($a['cost'] + $a['delta']); ?></td>
                    <td style="text-align:right; font-weight:600"><?php echo pct($a['base'], $a['cost'] + $a['delta']); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3 class="card-title">Correcció pendent: <?php echo number_format(count($canvis), 0, ',', '.'); ?> línies</h3></div>
    <div class="card-body">
        <?php if ($canvis): ?>
        <ul style="margin: 0 0 1rem 1.25rem;">
            <?php foreach ($perCausa as $causa => $n): ?>
            <li><?php echo e($causa); ?>: <?php echo number_format($n, 0, ',', '.'); ?> línies</li>
            <?php endforeach; ?>
        </ul>
        <p style="margin-bottom: 1rem; color: var(--gray-600);">Cada canvi es desa a la taula <code>costos_correccio_log</code> amb el cost anterior. També es posa al dia el <code>precio_medio</code> de tots els productes amb compres.</p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="accio" value="aplicar">
            <button type="submit" class="btn btn-primary">Aplicar la correcció</button>
        </form>
        <?php else: ?>
        <p style="margin: 0;">No hi ha cap línia per corregir.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($senseCost): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3 class="card-title">Productes venuts sense cap cost conegut (<?php echo count($senseCost); ?>)</h3></div>
    <div class="card-body" style="padding: 0;">
        <p style="padding: 1rem 1.25rem 0; margin: 0; color: var(--gray-600);">No tenen cap compra ni preu de compra a la fitxa, així que no es pot calcular el cost. Posa'ls el cost unitari sense IVA (el que sàpigues; deixa en blanc el que no en tingui, com les catas o els lots), desa i torna a aplicar la correcció.</p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="accio" value="costos_manuals">
            <div class="table-container">
                <table class="table">
                    <thead><tr>
                        <th>Producte</th><th style="text-align:right">Línies</th><th style="text-align:right">Unitats</th>
                        <th style="text-align:right">Vendes s/IVA</th><th style="width:140px; text-align:right">Cost unitari s/IVA</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($senseCost as $idProd => [$linies, $unitats, $vendes]): ?>
                    <tr>
                        <td><?php echo e($nomProducte[$idProd]); ?> <span style="color: var(--gray-400);">#<?php echo $idProd; ?></span></td>
                        <td style="text-align:right"><?php echo number_format($linies, 0, ',', '.'); ?></td>
                        <td style="text-align:right"><?php echo number_format($unitats, 0, ',', '.'); ?></td>
                        <td style="text-align:right"><?php echo eur($vendes); ?></td>
                        <td><input type="text" name="cost[<?php echo $idProd; ?>]" class="form-input" style="text-align:right; padding: 0.375rem 0.5rem;" placeholder="0,00" inputmode="decimal"></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding: 1rem 1.25rem; text-align: right;">
                <button type="submit" class="btn btn-secondary">Desar costos</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
