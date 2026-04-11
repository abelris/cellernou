<?php
/**
 * El Celler Nou - Modificar Tipus de Producte
 */
require_once dirname(dirname(dirname(__DIR__))) . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/database/tipus/'));
    exit;
}

$result = mysqli_query($bd, "SELECT * FROM tipo_producto WHERE id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/database/tipus/'));
    exit;
}

$tipo = mysqli_fetch_assoc($result);
$pageTitle = 'Editar: ' . $tipo['nombre_es'];
$errors = [];

// Obtener sectores
$sectores = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM sectores ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sectores[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat.';
    } else {
        $tipo['nombre_es'] = trim($_POST['nombre_es'] ?? '');
        $tipo['nombre_cat'] = trim($_POST['nombre_cat'] ?? '');
        $tipo['descripcion'] = trim($_POST['descripcion'] ?? '');
        $tipo['activo'] = isset($_POST['activo']) ? 1 : 0;
        $tipo['idSector'] = (int)($_POST['idSector'] ?? 0);

        if (empty($tipo['nombre_es'])) {
            $errors[] = 'El nom (ES) és obligatori.';
        }

        if (empty($errors)) {
            $sql = "UPDATE tipo_producto SET nombre = ?, nombre_es = ?, nombre_cat = ?, descripcion = ?, activo = ?, idSector = ? WHERE id = ?";
            $stmt = mysqli_prepare($bd, $sql);
            $nombre = $tipo['nombre_es'];
            mysqli_stmt_bind_param($stmt, 'ssssiii', $nombre, $tipo['nombre_es'], $tipo['nombre_cat'], $tipo['descripcion'], $tipo['activo'], $tipo['idSector'], $id);

            if (mysqli_stmt_execute($stmt)) {
                logActivity('tipo_update', "Tipus actualitzat: {$tipo['nombre_es']} (ID: $id)");
                header('Location: ' . getAdminUrl('modules/database/tipus/?msg=updated'));
                exit;
            } else {
                $errors[] = 'Error al actualitzar.';
            }
        }
    }
}

include dirname(dirname(dirname(__DIR__))) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/database/tipus/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom: 1rem;">
    <?php foreach ($errors as $e): ?><p style="margin: 0;"><?php echo e($e); ?></p><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <div class="card">
        <div class="card-header"><h3 class="card-title">Dades del Tipus</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre_es">Nom (Castellà) *</label>
                    <input type="text" id="nombre_es" name="nombre_es" class="form-input" required
                           value="<?php echo e($tipo['nombre_es']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="nombre_cat">Nom (Català)</label>
                    <input type="text" id="nombre_cat" name="nombre_cat" class="form-input"
                           value="<?php echo e($tipo['nombre_cat'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="idSector">Sector</label>
                    <select id="idSector" name="idSector" class="form-input">
                        <option value="0">Sense sector</option>
                        <?php foreach ($sectores as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo $tipo['idSector'] == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo e($s['nombre_es']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="activo" <?php echo $tipo['activo'] ? 'checked' : ''; ?>>
                        <span>Actiu</span>
                    </label>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label" for="descripcion">Descripció</label>
                    <textarea id="descripcion" name="descripcion" class="form-input" rows="3"><?php echo e($tipo['descripcion']); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions" style="margin-top: 1rem; display: flex; justify-content: flex-end; gap: 1rem;">
        <a href="<?php echo getAdminUrl('modules/database/tipus/'); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">Guardar Canvis</button>
    </div>
</form>

<style>
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.form-group { margin-bottom: 0; }
.form-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.625rem 0.875rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
.checkbox-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.625rem 0; }
.checkbox-label input { width: 18px; height: 18px; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; }
@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .form-group[style*="grid-column"] { grid-column: span 1 !important; } }
</style>

<?php include dirname(dirname(dirname(__DIR__))) . '/includes/footer.php'; ?>
