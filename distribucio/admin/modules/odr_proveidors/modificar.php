<?php
/**
 * OnDigitalRis - Modul Proveidors - Modificar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

$pageTitle = 'ODR - Editar Proveidor';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/odr_proveidors/?msg=error'));
    exit;
}

// Obtenir proveidor
$sql = "SELECT * FROM odr_proveedores WHERE id = $id";
$result = mysqli_query($bd, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/odr_proveidors/?msg=error'));
    exit;
}

$prov = mysqli_fetch_assoc($result);
$errors = [];

// Processar formulari
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        // Recollir dades
        $prov['nombre'] = trim($_POST['nombre'] ?? '');
        $prov['nombre_fiscal'] = trim($_POST['nombre_fiscal'] ?? '');
        $prov['nif'] = trim($_POST['nif'] ?? '');
        $prov['telefono'] = trim($_POST['telefono'] ?? '');
        $prov['email'] = trim($_POST['email'] ?? '');
        $prov['direccion'] = trim($_POST['direccion'] ?? '');
        $prov['cp'] = trim($_POST['cp'] ?? '');
        $prov['poblacion'] = trim($_POST['poblacion'] ?? '');
        $prov['provincia'] = trim($_POST['provincia'] ?? '');
        $prov['notas'] = trim($_POST['notas'] ?? '');

        // Validacions
        if (empty($prov['nombre'])) {
            $errors[] = 'El nom del proveidor es obligatori.';
        }

        if (!empty($prov['email']) && !filter_var($prov['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El format de l'email no es valid.";
        }

        // Actualitzar si no hi ha errors
        if (empty($errors)) {
            $sql = "UPDATE odr_proveedores SET
                    nombre = ?,
                    nombre_fiscal = ?,
                    nif = ?,
                    telefono = ?,
                    email = ?,
                    direccion = ?,
                    cp = ?,
                    poblacion = ?,
                    provincia = ?,
                    notas = ?,
                    updated_at = NOW()
                    WHERE id = ?";

            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'ssssssssssi',
                $prov['nombre'],
                $prov['nombre_fiscal'],
                $prov['nif'],
                $prov['telefono'],
                $prov['email'],
                $prov['direccion'],
                $prov['cp'],
                $prov['poblacion'],
                $prov['provincia'],
                $prov['notas'],
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                logActivity('odr_proveidor_modificar', "Proveidor modificat: {$prov['nombre']} (ID: $id)");
                header('Location: ' . getAdminUrl('modules/odr_proveidors/?msg=updated'));
                exit;
            } else {
                $errors[] = 'Error al actualitzar el proveidor: ' . mysqli_error($bd);
            }
        }
    }
}

// Estadistiques del proveidor
$provStats = ['num_factures' => 0, 'total_import' => 0];

$row = @mysqli_fetch_assoc(@mysqli_query($bd, "SELECT COUNT(*) as total FROM odr_facturas_compra WHERE id_proveedor = $id"));
if ($row) $provStats['num_factures'] = $row['total'];

$row = @mysqli_fetch_assoc(@mysqli_query($bd, "SELECT COALESCE(SUM(l.cantidad * l.precio_unitario), 0) as total
    FROM odr_linea_factura_compra l
    INNER JOIN odr_facturas_compra f ON l.id_factura = f.id
    WHERE f.id_proveedor = $id"));
if ($row) $provStats['total_import'] = $row['total'];

$pageTitle = 'ODR - Editar: ' . $prov['nombre'];

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo getAdminUrl('modules/odr_proveidors/'); ?>" class="btn btn-secondary">
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

<!-- Estadistiques del proveidor -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($provStats['num_factures']); ?></div>
            <div class="stat-label">Factures de Compra</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($provStats['total_import'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Import Total Compres</div>
        </div>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Dades del Proveidor</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nom *</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required
                           value="<?php echo e($prov['nombre']); ?>" placeholder="Nom comercial">
                </div>

                <div class="form-group">
                    <label class="form-label" for="nombre_fiscal">Nom Fiscal</label>
                    <input type="text" id="nombre_fiscal" name="nombre_fiscal" class="form-input"
                           value="<?php echo e($prov['nombre_fiscal']); ?>" placeholder="Rao social">
                </div>

                <div class="form-group">
                    <label class="form-label" for="nif">NIF / CIF</label>
                    <input type="text" id="nif" name="nif" class="form-input"
                           value="<?php echo e($prov['nif']); ?>" placeholder="B12345678">
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefono">Telefon</label>
                    <input type="text" id="telefono" name="telefono" class="form-input"
                           value="<?php echo e($prov['telefono']); ?>" placeholder="600 000 000">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           value="<?php echo e($prov['email']); ?>" placeholder="proveidor@exemple.com">
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Adreca</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="direccion">Adreca</label>
                    <input type="text" id="direccion" name="direccion" class="form-input"
                           value="<?php echo e($prov['direccion']); ?>" placeholder="Carrer, numero, pis...">
                </div>

                <div class="form-group">
                    <label class="form-label" for="cp">Codi Postal</label>
                    <input type="text" id="cp" name="cp" class="form-input"
                           value="<?php echo e($prov['cp']); ?>" placeholder="08000">
                </div>

                <div class="form-group">
                    <label class="form-label" for="poblacion">Poblacio</label>
                    <input type="text" id="poblacion" name="poblacion" class="form-input"
                           value="<?php echo e($prov['poblacion']); ?>" placeholder="Barcelona">
                </div>

                <div class="form-group">
                    <label class="form-label" for="provincia">Provincia</label>
                    <input type="text" id="provincia" name="provincia" class="form-input"
                           value="<?php echo e($prov['provincia']); ?>" placeholder="Barcelona">
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Notes</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="notas">Notes internes</label>
                    <textarea id="notas" name="notas" class="form-input" rows="4"
                              placeholder="Observacions, condicions especials..."><?php echo e($prov['notas']); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
        </button>
        <div style="margin-left: auto; display: flex; gap: 1rem;">
            <a href="<?php echo getAdminUrl('modules/odr_proveidors/'); ?>" class="btn btn-secondary">Cancel·lar</a>
            <button type="submit" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar Canvis
            </button>
        </div>
    </div>
</form>

<!-- Modal de confirmacio d'eliminacio -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar eliminacio</h3>
        </div>
        <div class="modal-body">
            <p>Estas segur que vols eliminar el proveidor <strong><?php echo e($prov['nombre']); ?></strong>?</p>
            <p style="color: var(--gray-500); font-size: 0.875rem;">Aquesta accio no es pot desfer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form method="POST" action="<?php echo getAdminUrl('modules/odr_proveidors/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    .form-group[style*="grid-column"] {
        grid-column: span 1 !important;
    }
}
.form-group {
    margin-bottom: 0;
}
.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}
.form-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    transition: all 0.2s;
}
.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1);
}
textarea.form-input {
    resize: vertical;
    min-height: 100px;
}
.form-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-top: 1rem;
}
.alert-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    padding: 1rem;
    border-radius: 8px;
}
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
}
.modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 400px;
    margin: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}
.modal-header h3 {
    margin: 0;
    font-size: 1.125rem;
}
.modal-body {
    padding: 1.5rem;
}
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
</style>

<script>
function confirmarEliminar() {
    document.getElementById('deleteModal').style.display = 'flex';
}

function tancarModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        tancarModal();
    }
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
