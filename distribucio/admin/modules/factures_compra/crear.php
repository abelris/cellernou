<?php
/**
 * El Celler Nou - Mòdul Factures de Compra - Crear
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Nova Factura de Compra';

$errors = [];
$factura = [
    'numFactura' => '',
    'idProveedor' => '',
    'fecha' => date('Y-m-d'),
    'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
    'comentarios' => '',
    'total_base' => 0,
    'total_iva' => 0,
    'total' => 0
];

// Obtener proveedores
$proveedores = [];
$result = mysqli_query($bd, "SELECT id, nombre FROM proveedores ORDER BY nombre");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $proveedores[] = $row;
    }
}

// Obtener estados de factura
$estadosFactura = [];
$result = mysqli_query($bd, "SELECT * FROM estados_facturas ORDER BY id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $estadosFactura[] = $row;
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        $factura['numFactura'] = trim($_POST['numFactura'] ?? '');
        $factura['idProveedor'] = (int)($_POST['idProveedor'] ?? 0);
        $factura['fecha'] = trim($_POST['fecha'] ?? date('Y-m-d'));
        $factura['fecha_vencimiento'] = trim($_POST['fecha_vencimiento'] ?? '');
        $factura['comentarios'] = trim($_POST['comentarios'] ?? '');
        $factura['total_base'] = (float)str_replace(',', '.', $_POST['total_base'] ?? 0);
        $factura['total_iva'] = (float)str_replace(',', '.', $_POST['total_iva'] ?? 0);
        $factura['total'] = (float)str_replace(',', '.', $_POST['total'] ?? 0);
        $idEstado = (int)($_POST['idEstado'] ?? 0);

        if (empty($factura['numFactura'])) {
            $errors[] = 'El número de factura és obligatori.';
        }
        if ($factura['idProveedor'] <= 0) {
            $errors[] = 'Has de seleccionar un proveïdor.';
        }
        if ($factura['total'] <= 0) {
            $errors[] = 'El total ha de ser superior a 0.';
        }

        if (empty($errors)) {
            $fechaV = !empty($factura['fecha_vencimiento']) ? $factura['fecha_vencimiento'] : null;

            $sql = "INSERT INTO facturas_compra (numFactura, idProveedor, fecha, fecha_vencimiento, idEstado, comentarios, total_base, total_iva, total)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'sississdd',
                $factura['numFactura'],
                $factura['idProveedor'],
                $factura['fecha'],
                $fechaV,
                $idEstado,
                $factura['comentarios'],
                $factura['total_base'],
                $factura['total_iva'],
                $factura['total']
            );

            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($bd);
                logActivity('factura_compra_crear', "Factura de compra creada: {$factura['numFactura']} (ID: $newId)");
                header('Location: ' . getAdminUrl('modules/factures_compra/?msg=created'));
                exit;
            } else {
                $errors[] = 'Error al crear la factura: ' . mysqli_error($bd);
            }
        }
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/factures_compra/'); ?>" class="btn btn-secondary">
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

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Dades de la Factura</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="numFactura">Núm. Factura *</label>
                    <input type="text" id="numFactura" name="numFactura" class="form-input" required
                           value="<?php echo e($factura['numFactura']); ?>" placeholder="Ex: F-2024-001">
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
                    <label class="form-label" for="idEstado">Estat</label>
                    <select id="idEstado" name="idEstado" class="form-input">
                        <?php foreach ($estadosFactura as $ef): ?>
                        <option value="<?php echo $ef['id']; ?>"><?php echo e($ef['nombre']); ?></option>
                        <?php endforeach; ?>
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
                    <label class="form-label" for="comentarios">Comentaris</label>
                    <textarea id="comentarios" name="comentarios" class="form-input" rows="2"
                              placeholder="Comentaris opcionals..."><?php echo e($factura['comentarios']); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Imports</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="total_base">Base Imposable (&euro;)</label>
                    <input type="text" id="total_base" name="total_base" class="form-input"
                           value="<?php echo number_format($factura['total_base'], 2, ',', ''); ?>"
                           placeholder="0,00" onkeyup="calcularTotal()" onchange="calcularTotal()">
                </div>

                <div class="form-group">
                    <label class="form-label" for="total_iva">IVA (&euro;)</label>
                    <input type="text" id="total_iva" name="total_iva" class="form-input"
                           value="<?php echo number_format($factura['total_iva'], 2, ',', ''); ?>"
                           placeholder="0,00" onkeyup="calcularTotal()" onchange="calcularTotal()">
                </div>

                <div class="form-group">
                    <label class="form-label" for="total">Total (&euro;) *</label>
                    <input type="text" id="total" name="total" class="form-input" required
                           value="<?php echo number_format($factura['total'], 2, ',', ''); ?>"
                           placeholder="0,00" style="font-weight: 700; font-size: 1.125rem;">
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?php echo getAdminUrl('modules/factures_compra/'); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Crear Factura
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
@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-group[style*="grid-column"] { grid-column: span 1 !important; }
}
</style>

<script>
function calcularTotal() {
    const base = parseFloat(document.getElementById('total_base').value.replace(',', '.')) || 0;
    const iva = parseFloat(document.getElementById('total_iva').value.replace(',', '.')) || 0;
    document.getElementById('total').value = (base + iva).toFixed(2).replace('.', ',');
}
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
