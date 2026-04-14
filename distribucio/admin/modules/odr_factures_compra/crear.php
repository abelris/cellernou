<?php
/**
 * OnDigitalRis - Mòdul Factures de Compra - Crear / Editar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

$uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/uploads/ondigitalris/';

// Mode edició?
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $editId > 0;

$pageTitle = $isEdit ? 'ODR - Editar Factura Compra' : 'ODR - Nova Factura Compra';

$errors = [];
$factura = [
    'numero_factura' => '',
    'idProveedor' => '',
    'fecha' => date('Y-m-d'),
    'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
    'forma_pago' => '',
    'notas' => '',
    'archivo' => ''
];
$lineas = [];

// Si mode edició, carregar dades existents
if ($isEdit) {
    $stmt = mysqli_prepare($bd, "SELECT * FROM odr_facturas_compra WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $facturaDb = mysqli_fetch_assoc($result);

    if (!$facturaDb) {
        header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=error'));
        exit;
    }

    $factura = array_merge($factura, $facturaDb);

    // Carregar línies
    $stmtL = mysqli_prepare($bd, "SELECT * FROM odr_linea_factura_compra WHERE idFactura = ? ORDER BY id");
    mysqli_stmt_bind_param($stmtL, 'i', $editId);
    mysqli_stmt_execute($stmtL);
    $resultL = mysqli_stmt_get_result($stmtL);
    while ($row = mysqli_fetch_assoc($resultL)) {
        $lineas[] = $row;
    }
}

// Obtenir proveïdors
$proveedores = [];
$result = mysqli_query($bd, "SELECT id, nombre FROM odr_proveedores ORDER BY nombre");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $proveedores[] = $row;
    }
}

// Processar formulari
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        $factura['numero_factura'] = trim($_POST['numero_factura'] ?? '');
        $factura['idProveedor'] = (int)($_POST['idProveedor'] ?? 0);
        $factura['fecha'] = trim($_POST['fecha'] ?? date('Y-m-d'));
        $factura['fecha_vencimiento'] = trim($_POST['fecha_vencimiento'] ?? '');
        $factura['forma_pago'] = trim($_POST['forma_pago'] ?? '');
        $factura['notas'] = trim($_POST['notas'] ?? '');

        // Línies
        $conceptos = $_POST['concepto'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $precios = $_POST['precio_unidad'] ?? [];
        $ivas = $_POST['iva'] ?? [];

        $lineas = [];
        for ($i = 0; $i < count($conceptos); $i++) {
            $concepto = trim($conceptos[$i] ?? '');
            $cantidad = (float)str_replace(',', '.', $cantidades[$i] ?? 0);
            $precio = (float)str_replace(',', '.', $precios[$i] ?? 0);
            $iva = (float)($ivas[$i] ?? 21);
            if ($concepto !== '' || $cantidad > 0 || $precio > 0) {
                $lineas[] = [
                    'concepto' => $concepto,
                    'cantidad' => $cantidad,
                    'precio_unidad' => $precio,
                    'iva' => $iva
                ];
            }
        }

        // Validacions
        if (empty($factura['numero_factura'])) {
            $errors[] = 'El número de factura és obligatori.';
        }
        if ($factura['idProveedor'] <= 0) {
            $errors[] = 'Has de seleccionar un proveïdor.';
        }
        if (empty($lineas)) {
            $errors[] = 'Has d\'afegir almenys una línia.';
        }

        // Gestió arxiu adjunt
        $archivoName = $isEdit ? $factura['archivo'] : '';
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['archivo']['tmp_name'];
            $originalName = $_FILES['archivo']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $errors[] = 'Format d\'arxiu no permès. Formats acceptats: ' . implode(', ', $allowed);
            } else {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $archivoName = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
                if (!move_uploaded_file($tmpName, $uploadDir . $archivoName)) {
                    $errors[] = 'Error al pujar l\'arxiu.';
                    $archivoName = $isEdit ? $factura['archivo'] : '';
                }
            }
        }

        if (empty($errors)) {
            $fechaV = !empty($factura['fecha_vencimiento']) ? $factura['fecha_vencimiento'] : null;

            if ($isEdit) {
                // UPDATE factura
                $sql = "UPDATE odr_facturas_compra SET numero_factura = ?, idProveedor = ?, fecha = ?, fecha_vencimiento = ?, forma_pago = ?, notas = ?, archivo = ?, updated_at = NOW() WHERE id = ?";
                $stmt = mysqli_prepare($bd, $sql);
                mysqli_stmt_bind_param($stmt, 'sisssssi',
                    $factura['numero_factura'],
                    $factura['idProveedor'],
                    $factura['fecha'],
                    $fechaV,
                    $factura['forma_pago'],
                    $factura['notas'],
                    $archivoName,
                    $editId
                );
                mysqli_stmt_execute($stmt);

                // Eliminar línies antigues i reinserir
                mysqli_query($bd, "DELETE FROM odr_linea_factura_compra WHERE idFactura = $editId");

                $stmtLine = mysqli_prepare($bd, "INSERT INTO odr_linea_factura_compra (idFactura, concepto, cantidad, precio_unidad, iva) VALUES (?, ?, ?, ?, ?)");
                foreach ($lineas as $linea) {
                    mysqli_stmt_bind_param($stmtLine, 'isddd',
                        $editId,
                        $linea['concepto'],
                        $linea['cantidad'],
                        $linea['precio_unidad'],
                        $linea['iva']
                    );
                    mysqli_stmt_execute($stmtLine);
                }

                logActivity('odr_factura_compra_editar', "ODR Factura compra editada: {$factura['numero_factura']} (ID: $editId)");
                header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=updated'));
                exit;
            } else {
                // INSERT factura
                $sql = "INSERT INTO odr_facturas_compra (numero_factura, idProveedor, fecha, fecha_vencimiento, estado, forma_pago, notas, archivo, created_at, updated_at)
                        VALUES (?, ?, ?, ?, 'pendent', ?, ?, ?, NOW(), NOW())";
                $stmt = mysqli_prepare($bd, $sql);
                mysqli_stmt_bind_param($stmt, 'sisssss',
                    $factura['numero_factura'],
                    $factura['idProveedor'],
                    $factura['fecha'],
                    $fechaV,
                    $factura['forma_pago'],
                    $factura['notas'],
                    $archivoName
                );

                if (mysqli_stmt_execute($stmt)) {
                    $newId = mysqli_insert_id($bd);

                    // Inserir línies
                    $stmtLine = mysqli_prepare($bd, "INSERT INTO odr_linea_factura_compra (idFactura, concepto, cantidad, precio_unidad, iva) VALUES (?, ?, ?, ?, ?)");
                    foreach ($lineas as $linea) {
                        mysqli_stmt_bind_param($stmtLine, 'isddd',
                            $newId,
                            $linea['concepto'],
                            $linea['cantidad'],
                            $linea['precio_unidad'],
                            $linea['iva']
                        );
                        mysqli_stmt_execute($stmtLine);
                    }

                    logActivity('odr_factura_compra_crear', "ODR Factura compra creada: {$factura['numero_factura']} (ID: $newId)");
                    header('Location: ' . getAdminUrl('modules/odr_factures_compra/?msg=created'));
                    exit;
                } else {
                    $errors[] = 'Error al crear la factura: ' . mysqli_error($bd);
                }
            }
        }
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/odr_factures_compra/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar al llistat
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom: 1rem;">
    <ul style="margin: 0; padding-left: 1.25rem;">
        <?php foreach ($errors as $error): ?>
        <li><?php echo e($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <!-- Card: Dades de la Factura -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Dades de la Factura</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="numero_factura">Núm. Factura *</label>
                    <input type="text" id="numero_factura" name="numero_factura" class="form-input" required
                           value="<?php echo e($factura['numero_factura']); ?>" placeholder="Ex: FC-2026-001">
                </div>

                <div class="form-group">
                    <label class="form-label" for="idProveedor">Proveïdor *</label>
                    <select id="idProveedor" name="idProveedor" class="form-input" required>
                        <option value="">Selecciona proveïdor...</option>
                        <?php foreach ($proveedores as $prov): ?>
                        <option value="<?php echo $prov['id']; ?>" <?php echo $factura['idProveedor'] == $prov['id'] ? 'selected' : ''; ?>>
                            <?php echo e($prov['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="forma_pago">Forma de Pagament</label>
                    <select id="forma_pago" name="forma_pago" class="form-input">
                        <option value="" <?php echo $factura['forma_pago'] === '' ? 'selected' : ''; ?>>-- Selecciona --</option>
                        <option value="Efectiu" <?php echo $factura['forma_pago'] === 'Efectiu' ? 'selected' : ''; ?>>Efectiu</option>
                        <option value="Transferència" <?php echo $factura['forma_pago'] === 'Transferència' ? 'selected' : ''; ?>>Transferència</option>
                        <option value="Targeta" <?php echo $factura['forma_pago'] === 'Targeta' ? 'selected' : ''; ?>>Targeta</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="fecha">Data d'Emissió</label>
                    <input type="date" id="fecha" name="fecha" class="form-input"
                           value="<?php echo e($factura['fecha']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="fecha_vencimiento">Data de Venciment</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" class="form-input"
                           value="<?php echo e($factura['fecha_vencimiento']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                </div>

                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="notas">Notes</label>
                    <textarea id="notas" name="notas" class="form-input" rows="2"
                              placeholder="Notes opcionals..."><?php echo e($factura['notas']); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Adjunt -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Adjunt</h3>
        </div>
        <div class="card-body">
            <?php if ($isEdit && !empty($factura['archivo'])): ?>
            <div style="margin-bottom: 1rem; padding: 0.75rem; background: var(--gray-50); border-radius: 8px; display: flex; align-items: center; gap: 0.75rem;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: var(--gray-500);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                <span style="font-size: 0.875rem;"><?php echo e($factura['archivo']); ?></span>
                <a href="<?php echo getBaseUrl('../uploads/ondigitalris/' . $factura['archivo']); ?>" target="_blank" class="btn btn-sm btn-secondary" style="margin-left: auto;">Veure</a>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label class="form-label" for="archivo"><?php echo $isEdit && !empty($factura['archivo']) ? 'Substituir arxiu' : 'Arxiu (PDF, imatge)'; ?></label>
                <input type="file" id="archivo" name="archivo" class="form-input"
                       accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
            </div>
        </div>
    </div>

    <!-- Card: Línies -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Línies</h3>
            <button type="button" class="btn btn-sm btn-primary" onclick="addLine()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Afegir línia
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table" id="linesTable">
                    <thead>
                        <tr>
                            <th style="min-width: 250px;">Concepte</th>
                            <th style="width: 100px; text-align: right;">Quantitat</th>
                            <th style="width: 120px; text-align: right;">Preu Unitat</th>
                            <th style="width: 100px; text-align: center;">IVA %</th>
                            <th style="width: 120px; text-align: right;">Subtotal</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        <!-- Les línies es carreguen via JS -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: 600;">Base imposable:</td>
                            <td style="text-align: right; font-weight: 600;" id="totalBase">0,00 &euro;</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: 600;">Total IVA:</td>
                            <td style="text-align: right; font-weight: 600;" id="totalIva">0,00 &euro;</td>
                            <td></td>
                        </tr>
                        <tr style="font-size: 1.125rem;">
                            <td colspan="4" style="text-align: right; font-weight: 700;">TOTAL:</td>
                            <td style="text-align: right; font-weight: 700;" id="totalGeneral">0,00 &euro;</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?php echo getAdminUrl('modules/odr_factures_compra/'); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <?php echo $isEdit ? 'Actualitzar Factura' : 'Crear Factura'; ?>
        </button>
    </div>
</form>

<style>
.form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.form-group { margin-bottom: 0; }
.form-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.625rem 0.875rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; transition: all 0.2s; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
textarea.form-input { resize: vertical; }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; }
.line-input { width: 100%; padding: 0.375rem 0.5rem; font-size: 0.8125rem; border: 1px solid var(--gray-300); border-radius: 4px; box-sizing: border-box; }
.line-input:focus { outline: none; border-color: var(--primary); }
.line-input-right { text-align: right; }
.btn-remove-line { background: none; border: none; color: var(--gray-400); cursor: pointer; padding: 0.25rem; border-radius: 4px; }
.btn-remove-line:hover { color: #dc2626; background: #fef2f2; }
@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-group[style*="grid-column"] { grid-column: span 1 !important; }
}
</style>

<script>
// Línies existents (mode edició)
const existingLines = <?php echo json_encode($lineas); ?>;
let lineCount = 0;

function addLine(data) {
    data = data || { concepto: '', cantidad: 1, precio_unidad: '', iva: 21 };
    const idx = lineCount++;
    const tbody = document.getElementById('linesBody');
    const tr = document.createElement('tr');
    tr.id = 'line-' + idx;
    tr.innerHTML = `
        <td>
            <input type="text" name="concepto[]" class="line-input" placeholder="Descripció..."
                   value="${escHtml(data.concepto || '')}">
        </td>
        <td>
            <input type="text" name="cantidad[]" class="line-input line-input-right"
                   value="${formatNum(data.cantidad || 1)}" onchange="calculateTotals()" onkeyup="calculateTotals()">
        </td>
        <td>
            <input type="text" name="precio_unidad[]" class="line-input line-input-right" placeholder="0,00"
                   value="${formatNum(data.precio_unidad || '')}" onchange="calculateTotals()" onkeyup="calculateTotals()">
        </td>
        <td>
            <select name="iva[]" class="line-input" onchange="calculateTotals()" style="text-align: center;">
                <option value="0" ${data.iva == 0 ? 'selected' : ''}>0%</option>
                <option value="4" ${data.iva == 4 ? 'selected' : ''}>4%</option>
                <option value="10" ${data.iva == 10 ? 'selected' : ''}>10%</option>
                <option value="21" ${data.iva == 21 ? 'selected' : ''}>21%</option>
            </select>
        </td>
        <td style="text-align: right; font-weight: 500; font-size: 0.875rem;" class="line-subtotal">0,00 &euro;</td>
        <td style="text-align: center;">
            <button type="button" class="btn-remove-line" onclick="removeLine(${idx})" title="Eliminar línia">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    calculateTotals();
}

function removeLine(idx) {
    const row = document.getElementById('line-' + idx);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

function calculateTotals() {
    const rows = document.querySelectorAll('#linesBody tr');
    let totalBase = 0;
    let totalIva = 0;

    rows.forEach(row => {
        const cantidad = parseFloat(row.querySelector('input[name="cantidad[]"]').value.replace(',', '.')) || 0;
        const precio = parseFloat(row.querySelector('input[name="precio_unidad[]"]').value.replace(',', '.')) || 0;
        const iva = parseFloat(row.querySelector('select[name="iva[]"]').value) || 0;

        const base = cantidad * precio;
        const ivaAmount = base * (iva / 100);
        const subtotal = base + ivaAmount;

        totalBase += base;
        totalIva += ivaAmount;

        row.querySelector('.line-subtotal').textContent = formatEuro(subtotal);
    });

    document.getElementById('totalBase').innerHTML = formatEuro(totalBase);
    document.getElementById('totalIva').innerHTML = formatEuro(totalIva);
    document.getElementById('totalGeneral').innerHTML = formatEuro(totalBase + totalIva);
}

function formatEuro(num) {
    return num.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' \u20AC';
}

function formatNum(val) {
    if (val === '' || val === null || val === undefined) return '';
    const num = parseFloat(String(val).replace(',', '.'));
    if (isNaN(num)) return '';
    return num.toFixed(2).replace('.', ',');
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Inicialitzar
document.addEventListener('DOMContentLoaded', function() {
    if (existingLines.length > 0) {
        existingLines.forEach(function(line) {
            addLine(line);
        });
    } else {
        addLine();
    }
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
