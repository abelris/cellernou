<?php
/**
 * El Celler Nou - Detall Tiquet (AJAX)
 * Returns ticket detail HTML fragment for modal preview
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo '<p>Tiquet no trobat.</p>';
    exit;
}

// Obtener tiquet
$result = mysqli_query($bd, "SELECT t.*, c.nombre as cliente_nombre, c.nif as cliente_nif
                             FROM tiquets t
                             LEFT JOIN clientes c ON t.idCliente = c.id
                             WHERE t.id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo '<p>Tiquet no trobat.</p>';
    exit;
}

$tiquet = mysqli_fetch_assoc($result);

// Obtener líneas
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

// precio_unidad_sin_desc = preu de tarifa; precio_unidad = preu amb el descompte ja aplicat.
$totalBrut = 0;
$totalDescompte = 0;
$subtotal = 0;
foreach ($lineas as $l) {
    $tarifa = $l['precio_unidad_sin_desc'] > 0 ? $l['precio_unidad_sin_desc'] : $l['precio_unidad'];
    $brutLinea = $l['cantidad'] * $tarifa;
    $netLinea = $l['cantidad'] * $l['precio_unidad'];

    $totalBrut += $brutLinea;
    $totalDescompte += $brutLinea - $netLinea;
    $subtotal += $netLinea;
}

$tipoPago = ['E' => 'Efectiu', 'T' => 'Targeta', 'C' => 'Compte'];
$tipoPagoNom = $tipoPago[$tiquet['tipo_pago']] ?? $tiquet['tipo_pago'];
?>
<div class="tiquet-preview" id="tiquetPrintArea">
    <div class="tiquet-preview-header">
        <div class="tiquet-preview-empresa">
            <strong>EL CELLER NOU</strong><br>
            <small>C/ Ramon Llull, 15 · 08330 Premià de Mar<br>Tel: 93 752 02 71 · CIF: B66318684</small>
        </div>
        <div class="tiquet-preview-meta">
            <div class="tiquet-preview-id">Tiquet #<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></div>
            <div><?php echo date('d/m/Y H:i', strtotime($tiquet['fecha'])); ?></div>
        </div>
    </div>

    <div class="tiquet-preview-info">
        <span><strong>Client:</strong> <?php echo e($tiquet['cliente_nombre'] ?? 'Client general'); ?></span>
        <span><strong>Pagament:</strong> <?php echo $tipoPagoNom; ?></span>
        <?php if (!empty($tiquet['comentarios'])): ?>
        <span><strong>Comentaris:</strong> <?php echo e($tiquet['comentarios']); ?></span>
        <?php endif; ?>
    </div>

    <table class="tiquet-preview-table">
        <thead>
            <tr>
                <th>Codi</th>
                <th>Producte</th>
                <th class="text-center">Qtat</th>
                <th class="text-right">Preu</th>
                <th class="text-center">Dto</th>
                <th class="text-right">Preu net</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lineas as $l): ?>
            <?php
            $tarifa = $l['precio_unidad_sin_desc'] > 0 ? $l['precio_unidad_sin_desc'] : $l['precio_unidad'];
            $lineaTotal = $l['cantidad'] * $l['precio_unidad'];
            ?>
            <tr>
                <td><code><?php echo e($l['producto_codigo'] ?? '-'); ?></code></td>
                <td><?php echo e($l['producto_nombre'] ?? 'Producte #' . $l['idProducto']); ?></td>
                <td class="text-center"><?php echo number_format($l['cantidad'], 2, ',', '.'); ?></td>
                <td class="text-right"><?php echo number_format($tarifa, 2, ',', '.'); ?> €</td>
                <td class="text-center">
                    <?php echo $l['descuento'] > 0 ? number_format($l['descuento'], 0) . '%' : '-'; ?>
                </td>
                <td class="text-right"><?php echo number_format($l['precio_unidad'], 2, ',', '.'); ?> €</td>
                <td class="text-right"><strong><?php echo number_format($lineaTotal, 2, ',', '.'); ?> €</strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <?php if ($totalDescompte > 0.001): ?>
            <tr>
                <td colspan="6" class="text-right">Subtotal:</td>
                <td class="text-right"><?php echo number_format($totalBrut, 2, ',', '.'); ?> €</td>
            </tr>
            <tr>
                <td colspan="6" class="text-right">Descompte:</td>
                <td class="text-right">-<?php echo number_format($totalDescompte, 2, ',', '.'); ?> €</td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                <td class="text-right tiquet-preview-total"><?php echo number_format($subtotal, 2, ',', '.'); ?> €</td>
            </tr>
        </tfoot>
    </table>

    <div class="tiquet-preview-footer">
        Gràcies per la seva compra! · IVA inclòs
    </div>
</div>
