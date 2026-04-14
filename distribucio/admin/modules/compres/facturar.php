<?php
/**
 * El Celler Nou - Mòdul Compres - Convertir a Factura
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Convertir Compra a Factura';

$id = (int)($_GET['id'] ?? $_POST['idCompra'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/compres/'));
    exit;
}

// Obtener compra
$result = mysqli_query($bd, "SELECT c.*, p.nombre as proveedor_nombre
                             FROM compras c
                             LEFT JOIN proveedores p ON c.idProveedor = p.id
                             WHERE c.id = $id AND c.idEstado != 3");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/compres/?msg=error'));
    exit;
}
$compra = mysqli_fetch_assoc($result);

$errors = [];

// Obtener formas de pago
$formasPago = [];
$result = mysqli_query($bd, "SELECT * FROM formas_pago ORDER BY id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $formasPago[] = $row;
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        $numFactura = trim($_POST['numFactura'] ?? '');
        $fechaEmision = trim($_POST['fecha_emision'] ?? '');
        $fechaVencimiento = trim($_POST['fecha_vencimiento'] ?? '');
        $idFormaPago = (int)($_POST['idFormaPago'] ?? 0);
        $comentarios = trim($_POST['comentarios'] ?? '');

        if (empty($numFactura)) {
            $errors[] = 'El número de factura és obligatori.';
        }

        if (empty($errors)) {
            // Convertir fechas
            $fechaE = !empty($fechaEmision) ? date('Y-m-d', strtotime($fechaEmision)) : date('Y-m-d');
            $fechaV = !empty($fechaVencimiento) ? date('Y-m-d', strtotime($fechaVencimiento)) : null;

            // Insertar factura copiando datos de la compra
            $sql = "INSERT INTO facturas_compra (numFactura, idProveedor, descuento, precio_desc, coste, precio_coste,
                    idEstado, idCompra, fecha, fecha_vencimiento, total_base, total_iva, total, comentarios)
                    VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'sisdsdississs',
                $numFactura,
                $compra['idProveedor'],
                $compra['descuento'],
                $compra['precio_desc'],
                $compra['coste'],
                $compra['precio_coste'],
                $id,
                $fechaE,
                $fechaV,
                $compra['total_base'],
                $compra['total_iva'],
                $compra['total'],
                $comentarios
            );

            if (mysqli_stmt_execute($stmt)) {
                $idFactura = mysqli_insert_id($bd);

                // Copiar líneas de compra a líneas de factura
                $sqlLineas = "INSERT INTO linea_factura_compra (idFactura, idProducto, idProveedor, cantidad, precio_unidad, descuento, precio_unidad_sin_desc, iva)
                              SELECT $idFactura, idProducto, idProveedor, cantidad, precio_unidad, descuento, precio_unidad_sin_desc, iva
                              FROM linea_compra WHERE idCompra = $id";
                mysqli_query($bd, $sqlLineas);

                // Actualizar estado de la compra a facturada
                mysqli_query($bd, "UPDATE compras SET idEstado = 3, idFormaPago = $idFormaPago WHERE id = $id");

                logActivity('compra_facturar', "Compra #$id convertida a factura #$idFactura ($numFactura)");
                header('Location: ' . getAdminUrl('modules/compres/?msg=invoiced'));
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
    <a href="<?php echo getAdminUrl('modules/compres/veure.php?id=' . $id); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar a la compra
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

<!-- Resumen de la compra -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Compra #<?php echo $id; ?></h3>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Proveïdor</span>
                <span class="info-value"><?php echo e($compra['proveedor_nombre']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Referència</span>
                <span class="info-value"><?php echo e($compra['referencia'] ?: '-'); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Total</span>
                <span class="info-value" style="color: var(--primary); font-weight: 700;"><?php echo number_format($compra['total'] ?? 0, 2, ',', '.'); ?> &euro;</span>
            </div>
        </div>
    </div>
</div>

<!-- Formulario de facturación -->
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="idCompra" value="<?php echo $id; ?>">

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Dades de la Factura</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="numFactura">Núm. Factura Proveïdor *</label>
                    <input type="text" id="numFactura" name="numFactura" class="form-input" required
                           value="<?php echo e($_POST['numFactura'] ?? ''); ?>" placeholder="Ex: F-2024-001">
                </div>

                <div class="form-group">
                    <label class="form-label" for="fecha_emision">Data d'Emissió</label>
                    <input type="date" id="fecha_emision" name="fecha_emision" class="form-input"
                           value="<?php echo e($_POST['fecha_emision'] ?? date('Y-m-d')); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="fecha_vencimiento">Data de Venciment</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" class="form-input"
                           value="<?php echo e($_POST['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+30 days'))); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="idFormaPago">Forma de Pagament</label>
                    <select id="idFormaPago" name="idFormaPago" class="form-input">
                        <?php foreach ($formasPago as $fp): ?>
                        <option value="<?php echo $fp['id']; ?>"><?php echo e($fp['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label" for="comentarios">Comentaris</label>
                    <textarea id="comentarios" name="comentarios" class="form-input" rows="2"
                              placeholder="Comentaris opcionals..."><?php echo e($_POST['comentarios'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?php echo getAdminUrl('modules/compres/veure.php?id=' . $id); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Convertir a Factura
        </button>
    </div>
</form>

<style>
.info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.info-label { display: block; font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem; }
.info-value { font-size: 0.95rem; font-weight: 500; }
.form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.form-group { margin-bottom: 0; }
.form-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.625rem 0.875rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; transition: all 0.2s; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
textarea.form-input { resize: vertical; }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; }
@media (max-width: 768px) {
    .form-grid, .info-grid { grid-template-columns: 1fr; }
    .form-group[style*="grid-column"] { grid-column: span 1 !important; }
}
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
