<?php
/**
 * El Celler Nou - Mòdul Factures de Compra - Modificar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Editar Factura de Compra';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/factures_compra/?msg=error'));
    exit;
}

// Obtener factura
$result = mysqli_query($bd, "SELECT f.*, p.nombre as proveedor_nombre
                             FROM facturas_compra f
                             LEFT JOIN proveedores p ON f.idProveedor = p.id
                             WHERE f.id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/factures_compra/?msg=error'));
    exit;
}

$factura = mysqli_fetch_assoc($result);
$errors = [];

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
        $factura['idEstado'] = (int)($_POST['idEstado'] ?? 0);
        $factura['comentarios'] = trim($_POST['comentarios'] ?? '');
        $factura['total_base'] = (float)str_replace(',', '.', $_POST['total_base'] ?? 0);
        $factura['total_iva'] = (float)str_replace(',', '.', $_POST['total_iva'] ?? 0);
        $factura['total'] = (float)str_replace(',', '.', $_POST['total'] ?? 0);

        if (empty($factura['numFactura'])) {
            $errors[] = 'El número de factura és obligatori.';
        }
        if ($factura['idProveedor'] <= 0) {
            $errors[] = 'Has de seleccionar un proveïdor.';
        }

        if (empty($errors)) {
            $fechaV = !empty($factura['fecha_vencimiento']) ? $factura['fecha_vencimiento'] : null;

            $sql = "UPDATE facturas_compra SET
                    numFactura = ?, idProveedor = ?, fecha = ?, fecha_vencimiento = ?,
                    idEstado = ?, comentarios = ?, total_base = ?, total_iva = ?, total = ?
                    WHERE id = ?";
            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'sississddi',
                $factura['numFactura'],
                $factura['idProveedor'],
                $factura['fecha'],
                $fechaV,
                $factura['idEstado'],
                $factura['comentarios'],
                $factura['total_base'],
                $factura['total_iva'],
                $factura['total'],
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                logActivity('factura_compra_modificar', "Factura de compra modificada: {$factura['numFactura']} (ID: $id)");
                header('Location: ' . getAdminUrl('modules/factures_compra/?msg=updated'));
                exit;
            } else {
                $errors[] = 'Error al actualitzar la factura: ' . mysqli_error($bd);
            }
        }
    }
}

$pageTitle = 'Editar Factura: ' . ($factura['numFactura'] ?: '#' . $id);

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo getAdminUrl('modules/factures_compra/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar al llistat
    </a>
    <span class="badge badge-info">ID: <?php echo $id; ?></span>
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
                        <option value="<?php echo $ef['id']; ?>" <?php echo $ef['id'] == $factura['idEstado'] ? 'selected' : ''; ?>>
                            <?php echo e($ef['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="fecha">Data d'Emissió</label>
                    <input type="date" id="fecha" name="fecha" class="form-input"
                           value="<?php echo $factura['fecha'] ? date('Y-m-d', strtotime($factura['fecha'])) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="fecha_vencimiento">Data de Venciment</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" class="form-input"
                           value="<?php echo $factura['fecha_vencimiento'] ? date('Y-m-d', strtotime($factura['fecha_vencimiento'])) : ''; ?>">
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
                           value="<?php echo number_format($factura['total_base'] ?? 0, 2, ',', ''); ?>"
                           placeholder="0,00" onkeyup="calcularTotal()" onchange="calcularTotal()">
                </div>

                <div class="form-group">
                    <label class="form-label" for="total_iva">IVA (&euro;)</label>
                    <input type="text" id="total_iva" name="total_iva" class="form-input"
                           value="<?php echo number_format($factura['total_iva'] ?? 0, 2, ',', ''); ?>"
                           placeholder="0,00" onkeyup="calcularTotal()" onchange="calcularTotal()">
                </div>

                <div class="form-group">
                    <label class="form-label" for="total">Total (&euro;)</label>
                    <input type="text" id="total" name="total" class="form-input"
                           value="<?php echo number_format($factura['total'] ?? 0, 2, ',', ''); ?>"
                           placeholder="0,00" style="font-weight: 700; font-size: 1.125rem;">
                </div>
            </div>
        </div>
    </div>

    <?php if ($factura['idCompra'] > 0): ?>
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Informació del Sistema</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Compra Associada</strong>
                    <p style="margin: 0.25rem 0 0;">
                        <a href="<?php echo getAdminUrl('modules/compres/veure.php?id=' . $factura['idCompra']); ?>">
                            Compra #<?php echo $factura['idCompra']; ?>
                        </a>
                    </p>
                </div>
                <div>
                    <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">ID Factura</strong>
                    <p style="margin: 0.25rem 0 0;"><?php echo $id; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-actions">
        <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
        </button>
        <div style="margin-left: auto; display: flex; gap: 1rem;">
            <a href="<?php echo getAdminUrl('modules/factures_compra/'); ?>" class="btn btn-secondary">Cancel·lar</a>
            <button type="submit" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar Canvis
            </button>
        </div>
    </div>
</form>

<!-- Modal de confirmación de eliminación -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModal()"></div>
    <div class="modal-content">
        <div class="modal-header"><h3>Confirmar eliminació</h3></div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar la factura <strong><?php echo e($factura['numFactura'] ?: '#' . $id); ?></strong>?</p>
            <p style="color: var(--gray-500); font-size: 0.875rem;">Aquesta acció no es pot desfer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form method="POST" action="<?php echo getAdminUrl('modules/factures_compra/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<style>
.form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.form-group { margin-bottom: 0; }
.form-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.625rem 0.875rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; transition: all 0.2s; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
textarea.form-input { resize: vertical; }
.form-actions { display: flex; align-items: center; gap: 1rem; padding-top: 1rem; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; }
.modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-backdrop { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); }
.modal-content { position: relative; background: white; border-radius: 12px; width: 100%; max-width: 400px; margin: 1rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--gray-200); }
.modal-header h3 { margin: 0; font-size: 1.125rem; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 0.75rem; }
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

function confirmarEliminar() { document.getElementById('deleteModal').style.display = 'flex'; }
function tancarModal() { document.getElementById('deleteModal').style.display = 'none'; }
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') tancarModal(); });
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
