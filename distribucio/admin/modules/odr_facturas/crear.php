<?php
/**
 * OnDigitalRis - Factures Venda - Crear
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

$pageTitle = 'ODR - Nova Factura Venda';

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=error'));
        exit;
    }

    $numero = trim($_POST['numero'] ?? '');
    $idCliente = (int)($_POST['idCliente'] ?? 0);
    $fecha = trim($_POST['fecha'] ?? '');
    $fechaVencimiento = trim($_POST['fecha_vencimiento'] ?? '');
    $formaPago = trim($_POST['forma_pago'] ?? '');
    $notas = trim($_POST['notas'] ?? '');

    // Validar
    $errors = [];
    if ($fecha === '') {
        $errors[] = 'La data es obligatoria.';
    }

    // Recoger lineas
    $lineas = [];
    if (!empty($_POST['concepto']) && is_array($_POST['concepto'])) {
        for ($i = 0; $i < count($_POST['concepto']); $i++) {
            $concepto = trim($_POST['concepto'][$i] ?? '');
            $cantidad = floatval($_POST['cantidad'][$i] ?? 0);
            $precioUnidad = floatval($_POST['precio_unidad'][$i] ?? 0);
            $iva = floatval($_POST['iva'][$i] ?? 21);

            if ($concepto !== '' || $cantidad > 0 || $precioUnidad > 0) {
                $lineas[] = [
                    'concepto' => $concepto,
                    'cantidad' => $cantidad,
                    'precio_unidad' => $precioUnidad,
                    'iva' => $iva
                ];
            }
        }
    }

    if (empty($lineas)) {
        $errors[] = 'Cal afegir almenys una linia.';
    }

    if (empty($errors)) {
        // Insertar factura
        $stmt = mysqli_prepare($bd, "INSERT INTO odr_facturas (numero, idCliente, fecha, fecha_vencimiento, estado, forma_pago, notas, created_at, updated_at) VALUES (?, ?, ?, ?, 'pendent', ?, ?, NOW(), NOW())");
        $fechaVencDb = $fechaVencimiento ?: null;
        $notasDb = $notas ?: null;
        mysqli_stmt_bind_param($stmt, 'sissss', $numero, $idCliente, $fecha, $fechaVencDb, $formaPago, $notasDb);
        $ok = mysqli_stmt_execute($stmt);

        if ($ok) {
            $facturaId = mysqli_insert_id($bd);

            // Insertar lineas
            $stmtLine = mysqli_prepare($bd, "INSERT INTO odr_linea_factura (idFactura, concepto, cantidad, precio_unidad, iva) VALUES (?, ?, ?, ?, ?)");
            foreach ($lineas as $linea) {
                mysqli_stmt_bind_param($stmtLine, 'isddd',
                    $facturaId,
                    $linea['concepto'],
                    $linea['cantidad'],
                    $linea['precio_unidad'],
                    $linea['iva']
                );
                mysqli_stmt_execute($stmtLine);
            }
            mysqli_stmt_close($stmtLine);

            // Auto-generar numero correlativo INF2XXXX
            if ($numero === '') {
                $rsMax = mysqli_query($bd, "SELECT numero FROM odr_facturas WHERE numero LIKE 'INF2%' ORDER BY numero DESC LIMIT 1");
                $lastNum = 20000;
                if ($rsMax && $rowMax = mysqli_fetch_assoc($rsMax)) {
                    $lastNum = intval(substr($rowMax['numero'], 3));
                }
                $autoNumero = 'INF' . ($lastNum + 1);
                mysqli_query($bd, "UPDATE odr_facturas SET numero = '" . mysqli_real_escape_string($bd, $autoNumero) . "' WHERE id = $facturaId");
            }

            mysqli_stmt_close($stmt);
            logActivity('odr_factura_crear', "Factura venda creada: #$facturaId");
            header('Location: ' . getAdminUrl('modules/odr_facturas/?msg=created'));
            exit;
        } else {
            $errors[] = 'Error al guardar: ' . mysqli_error($bd);
            mysqli_stmt_close($stmt);
        }
    }
}

// Obtener clientes
$clientes = [];
$rsClients = mysqli_query($bd, "SELECT id, nombre, nif FROM odr_clientes ORDER BY nombre");
if ($rsClients) {
    while ($row = mysqli_fetch_assoc($rsClients)) {
        $clientes[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom: 1rem;">
    <ul style="margin: 0; padding-left: 1.25rem;">
        <?php foreach ($errors as $err): ?>
        <li><?php echo e($err); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div style="margin-bottom: 1rem;">
    <a href="<?php echo getAdminUrl('modules/odr_facturas/'); ?>" class="btn btn-secondary btn-sm">
        &larr; Tornar al llistat
    </a>
</div>

<form method="POST" id="facturaForm">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <!-- Dades de la Factura -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Dades de la Factura</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Numero de factura</label>
                    <input type="text" name="numero" class="form-input" placeholder="Auto (INF2XXXX)" value="<?php echo e($_POST['numero'] ?? ''); ?>">
                    <small style="color: var(--gray-500); font-size: 0.75rem;">Deixar buit per generar automaticament (serie INF2XXXX)</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Client <span style="color: #dc2626;">*</span></label>
                    <select name="idCliente" class="form-input" required>
                        <option value="0">-- Seleccionar client --</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo (isset($_POST['idCliente']) && $_POST['idCliente'] == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo e($c['nombre']); ?><?php echo $c['nif'] ? ' (' . e($c['nif']) . ')' : ''; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Data <span style="color: #dc2626;">*</span></label>
                    <input type="date" name="fecha" class="form-input" value="<?php echo e($_POST['fecha'] ?? date('Y-m-d')); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Data venciment</label>
                    <input type="date" name="fecha_vencimiento" class="form-input" value="<?php echo e($_POST['fecha_vencimiento'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Forma de pagament</label>
                    <select name="forma_pago" class="form-input">
                        <option value="">-- Seleccionar --</option>
                        <option value="Efectiu" <?php echo (($_POST['forma_pago'] ?? '') === 'Efectiu') ? 'selected' : ''; ?>>Efectiu</option>
                        <option value="Transferencia" <?php echo (($_POST['forma_pago'] ?? '') === 'Transferencia') ? 'selected' : ''; ?>>Transferencia</option>
                        <option value="Targeta" <?php echo (($_POST['forma_pago'] ?? '') === 'Targeta') ? 'selected' : ''; ?>>Targeta</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Notes</label>
                    <textarea name="notas" class="form-input" rows="2" placeholder="Notes opcionals..."><?php echo e($_POST['notas'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Linies de la Factura -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Linies de la Factura</h3>
            <button type="button" class="btn btn-primary btn-sm" onclick="addLine()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 0.25rem; vertical-align: -2px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Afegir linia
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table" id="liniesTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Concepte</th>
                            <th style="width: 100px; text-align: right;">Quantitat</th>
                            <th style="width: 120px; text-align: right;">Preu/Unitat</th>
                            <th style="width: 100px;">IVA</th>
                            <th style="width: 120px; text-align: right;">Subtotal</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="liniesBody">
                        <!-- Se rellena por JS -->
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid var(--gray-300);">
                            <td colspan="5" style="text-align: right; font-weight: 600; padding: 0.75rem;">Base imponible:</td>
                            <td style="text-align: right; font-weight: 700; padding: 0.75rem;" id="totalBase">0,00 &euro;</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: 600; padding: 0.5rem 0.75rem; color: var(--gray-500);">IVA:</td>
                            <td style="text-align: right; font-weight: 600; padding: 0.5rem 0.75rem; color: var(--gray-500);" id="totalIva">0,00 &euro;</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: 700; padding: 0.75rem; font-size: 1.1rem;">TOTAL:</td>
                            <td style="text-align: right; font-weight: 700; padding: 0.75rem; font-size: 1.1rem; color: var(--primary);" id="totalFinal">0,00 &euro;</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Accions -->
    <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
        <a href="<?php echo getAdminUrl('modules/odr_facturas/'); ?>" class="btn btn-secondary">Cancel&middot;lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px; margin-right: 0.25rem; vertical-align: -3px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Crear Factura
        </button>
    </div>
</form>

<script>
var lineCount = 0;

function addLine() {
    lineCount++;
    var tbody = document.getElementById('liniesBody');
    var tr = document.createElement('tr');
    tr.id = 'line-' + lineCount;
    tr.innerHTML =
        '<td style="text-align: center; color: var(--gray-400); font-size: 0.875rem;">' + lineCount + '</td>' +
        '<td><input type="text" name="concepto[]" class="form-input" placeholder="Descripcio del servei o producte..." style="min-width:200px;"></td>' +
        '<td><input type="number" name="cantidad[]" class="form-input line-qty" value="1" min="0" step="1" style="text-align:right;" onchange="calculateTotals()" oninput="calculateTotals()"></td>' +
        '<td><input type="number" name="precio_unidad[]" class="form-input line-price" value="0" min="0" step="0.01" style="text-align:right;" onchange="calculateTotals()" oninput="calculateTotals()"></td>' +
        '<td><select name="iva[]" class="form-input line-iva" onchange="calculateTotals()">' +
            '<option value="21">21%</option>' +
            '<option value="10">10%</option>' +
            '<option value="4">4%</option>' +
            '<option value="0">0%</option>' +
        '</select></td>' +
        '<td style="text-align: right; font-weight: 600; font-size: 0.9rem;" class="line-subtotal">0,00 &euro;</td>' +
        '<td style="text-align: center;"><button type="button" class="btn-remove" onclick="removeLine(this)" title="Eliminar linia">&times;</button></td>';
    tbody.appendChild(tr);
    calculateTotals();
}

function removeLine(btn) {
    var tr = btn.closest('tr');
    tr.remove();
    renumberLines();
    calculateTotals();
}

function renumberLines() {
    var rows = document.querySelectorAll('#liniesBody tr');
    rows.forEach(function(row, index) {
        row.querySelector('td:first-child').textContent = index + 1;
    });
    lineCount = rows.length;
}

function calculateTotals() {
    var rows = document.querySelectorAll('#liniesBody tr');
    var totalBase = 0;
    var totalIva = 0;

    rows.forEach(function(row) {
        var qty = parseFloat(row.querySelector('.line-qty').value) || 0;
        var price = parseFloat(row.querySelector('.line-price').value) || 0;
        var iva = parseFloat(row.querySelector('.line-iva').value) || 0;

        var subtotal = qty * price;
        var ivaAmount = subtotal * (iva / 100);

        totalBase += subtotal;
        totalIva += ivaAmount;

        row.querySelector('.line-subtotal').textContent = subtotal.toLocaleString('ca-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' \u20AC';
    });

    var totalFinal = totalBase + totalIva;

    document.getElementById('totalBase').innerHTML = totalBase.toLocaleString('ca-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' &euro;';
    document.getElementById('totalIva').innerHTML = totalIva.toLocaleString('ca-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' &euro;';
    document.getElementById('totalFinal').innerHTML = totalFinal.toLocaleString('ca-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' &euro;';
}

// Iniciar con una linea vacia
document.addEventListener('DOMContentLoaded', function() {
    addLine();
});
</script>

<style>
.filter-label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
.form-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; }
.form-group { margin-bottom: 1rem; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; }

.btn-remove {
    background: none;
    border: none;
    color: #dc2626;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    font-weight: 700;
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: background 0.15s;
}
.btn-remove:hover {
    background: #fef2f2;
}

#liniesTable .form-input {
    padding: 0.375rem 0.5rem;
    font-size: 0.8125rem;
}

#liniesTable tbody tr:hover {
    background: var(--gray-50, #f9fafb);
}
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
