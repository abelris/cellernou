<?php
/**
 * El Celler Nou - Gestoria - Detall Tiquet (AJAX)
 * Returns ticket detail HTML fragment with IVA breakdown for gestoria modal
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo '<p style="text-align:center; color:var(--gray-400); padding:2rem;">Tiquet no trobat.</p>';
    exit;
}

// Obtenir tiquet
$result = mysqli_query($bd, "SELECT t.*, c.nombre as cliente_nombre
                             FROM tiquets t
                             LEFT JOIN clientes c ON t.idCliente = c.id
                             WHERE t.id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo '<p style="text-align:center; color:var(--gray-400); padding:2rem;">Tiquet no trobat.</p>';
    exit;
}

$tiquet = mysqli_fetch_assoc($result);

// Obtenir línies
$lineas = [];
$result = mysqli_query($bd, "SELECT lt.*, p.nombre as producto_nombre, p.codigo as producto_codigo
                             FROM linea_tiquet lt
                             LEFT JOIN productos p ON lt.idProducto = p.id
                             WHERE lt.idVenta = $id ORDER BY lt.id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $lineas[] = $row;
    }
}

// Calcular desglossament IVA
$ivaBreakdown = [];
$grandTotal = 0;
foreach ($lineas as $l) {
    $iva = (int)$l['iva'];
    $base = $l['cantidad'] * $l['precio_unidad_sin_imp'];
    $quota = $base * $iva / 100;
    $totalLinea = $l['cantidad'] * $l['precio_unidad'];
    $grandTotal += $totalLinea;

    if (!isset($ivaBreakdown[$iva])) {
        $ivaBreakdown[$iva] = ['base' => 0, 'quota' => 0, 'total' => 0];
    }
    $ivaBreakdown[$iva]['base'] += $base;
    $ivaBreakdown[$iva]['quota'] += $quota;
    $ivaBreakdown[$iva]['total'] += $totalLinea;
}
ksort($ivaBreakdown);

$sumBase = array_sum(array_column($ivaBreakdown, 'base'));
$sumQuota = array_sum(array_column($ivaBreakdown, 'quota'));

$tipoPagoLabels = ['E' => 'Efectiu', 'T' => 'Targeta', 'C' => 'Compte', 'V' => 'Varis'];
$tp = $tiquet['tipo_pago'] ?? '';
$tipoPagoNom = $tipoPagoLabels[$tp] ?? $tp;
$tpClass = $tp === 'E' ? 'badge-success' : ($tp === 'T' ? 'badge-info' : 'badge-default');

function fmt($v) { return number_format($v, 2, ',', '.'); }
?>
<div style="font-family: inherit; font-size: 0.875rem; color: var(--gray-600);">

    <!-- Capçalera -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 2px solid var(--gray-100);">
        <div>
            <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem; color: var(--gray-600);">
                Tiquet <strong>#<?php echo $id; ?></strong>
            </h3>
            <div style="font-size: 0.85rem; color: var(--gray-500);">
                <?php echo date('d/m/Y H:i', strtotime($tiquet['fecha'])); ?>
            </div>
        </div>
        <div style="text-align: right;">
            <span class="<?php echo $tpClass; ?>" style="display:inline-block; padding:0.25rem 0.625rem; font-size:0.75rem; font-weight:600; border-radius:9999px;
                <?php if ($tp === 'E'): ?>background:#d1fae5; color:#065f46;
                <?php elseif ($tp === 'T'): ?>background:#dbeafe; color:#1e40af;
                <?php else: ?>background:var(--gray-100); color:var(--gray-600);
                <?php endif; ?>
            "><?php echo e($tipoPagoNom); ?></span>
            <div style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--gray-500);">
                <?php echo $tiquet['cliente_nombre'] ? e($tiquet['cliente_nombre']) : '<span style="color:var(--gray-400);">Client general</span>'; ?>
            </div>
        </div>
    </div>

    <!-- Taula de línies -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.25rem; font-size: 0.85rem;">
        <thead>
            <tr style="background: var(--gray-50); border-bottom: 2px solid var(--gray-200);">
                <th style="text-align: left; padding: 0.5rem 0.75rem; font-weight: 600; color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Producte</th>
                <th style="text-align: center; padding: 0.5rem 0.75rem; font-weight: 600; color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Qtat</th>
                <th style="text-align: right; padding: 0.5rem 0.75rem; font-weight: 600; color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Preu</th>
                <th style="text-align: center; padding: 0.5rem 0.75rem; font-weight: 600; color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">IVA</th>
                <th style="text-align: right; padding: 0.5rem 0.75rem; font-weight: 600; color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($lineas)): ?>
            <tr><td colspan="5" style="text-align: center; padding: 1.5rem; color: var(--gray-400);">Sense línies</td></tr>
            <?php else: ?>
            <?php foreach ($lineas as $l): ?>
            <?php $lineaTotal = $l['cantidad'] * $l['precio_unidad']; ?>
            <tr style="border-bottom: 1px solid var(--gray-100);">
                <td style="padding: 0.5rem 0.75rem;">
                    <?php echo e($l['producto_nombre'] ?? 'Producte #' . $l['idProducto']); ?>
                </td>
                <td style="text-align: center; padding: 0.5rem 0.75rem;"><?php echo fmt($l['cantidad']); ?></td>
                <td style="text-align: right; padding: 0.5rem 0.75rem;"><?php echo fmt($l['precio_unidad']); ?> &euro;</td>
                <td style="text-align: center; padding: 0.5rem 0.75rem;"><?php echo (int)$l['iva']; ?>%</td>
                <td style="text-align: right; padding: 0.5rem 0.75rem; font-weight: 600;"><?php echo fmt($lineaTotal); ?> &euro;</td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Desglossament IVA -->
    <?php if (!empty($ivaBreakdown)): ?>
    <div style="background: var(--gray-50); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
        <h4 style="margin: 0 0 0.75rem 0; font-size: 0.8rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase;">Desglossament IVA</h4>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--gray-200);">
                    <th style="text-align: left; padding: 0.375rem 0.5rem; font-weight: 600; color: var(--gray-500); font-size: 0.75rem;">Tipus</th>
                    <th style="text-align: right; padding: 0.375rem 0.5rem; font-weight: 600; color: var(--gray-500); font-size: 0.75rem;">Base</th>
                    <th style="text-align: right; padding: 0.375rem 0.5rem; font-weight: 600; color: var(--gray-500); font-size: 0.75rem;">Quota IVA</th>
                    <th style="text-align: right; padding: 0.375rem 0.5rem; font-weight: 600; color: var(--gray-500); font-size: 0.75rem;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ivaBreakdown as $rate => $data): ?>
                <tr style="border-bottom: 1px solid var(--gray-200);">
                    <td style="padding: 0.375rem 0.5rem; font-weight: 600;">IVA <?php echo $rate; ?>%</td>
                    <td style="text-align: right; padding: 0.375rem 0.5rem;"><?php echo fmt($data['base']); ?> &euro;</td>
                    <td style="text-align: right; padding: 0.375rem 0.5rem;"><?php echo fmt($data['quota']); ?> &euro;</td>
                    <td style="text-align: right; padding: 0.375rem 0.5rem; font-weight: 600;"><?php echo fmt($data['total']); ?> &euro;</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid var(--gray-300);">
                    <td style="padding: 0.5rem 0.5rem; font-weight: 700;">TOTALS</td>
                    <td style="text-align: right; padding: 0.5rem 0.5rem; font-weight: 700;"><?php echo fmt($sumBase); ?> &euro;</td>
                    <td style="text-align: right; padding: 0.5rem 0.5rem; font-weight: 700;"><?php echo fmt($sumQuota); ?> &euro;</td>
                    <td style="text-align: right; padding: 0.5rem 0.5rem; font-weight: 700; color: var(--primary);"><?php echo fmt($grandTotal); ?> &euro;</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <!-- Total general -->
    <div style="text-align: right; padding: 0.75rem 0; border-top: 2px solid var(--gray-200);">
        <span style="font-size: 0.85rem; color: var(--gray-500); margin-right: 0.75rem;">TOTAL TIQUET:</span>
        <span style="font-size: 1.25rem; font-weight: 700; color: var(--primary);"><?php echo fmt($grandTotal); ?> &euro;</span>
    </div>

</div>
