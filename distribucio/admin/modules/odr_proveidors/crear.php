<?php
/**
 * OnDigitalRis - Modul Proveidors - Crear
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

$pageTitle = 'ODR - Nou Proveidor';

$errors = [];
$prov = [
    'nombre' => '',
    'nombre_fiscal' => '',
    'nif' => '',
    'telefono' => '',
    'email' => '',
    'direccion' => '',
    'cp' => '',
    'poblacion' => '',
    'provincia' => '',
    'notas' => ''
];

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

        // Insertar si no hi ha errors
        if (empty($errors)) {
            $sql = "INSERT INTO odr_proveedores (nombre, nombre_fiscal, nif, telefono, email, direccion, cp, poblacion, provincia, notas, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'ssssssssss',
                $prov['nombre'],
                $prov['nombre_fiscal'],
                $prov['nif'],
                $prov['telefono'],
                $prov['email'],
                $prov['direccion'],
                $prov['cp'],
                $prov['poblacion'],
                $prov['provincia'],
                $prov['notas']
            );

            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($bd);
                logActivity('odr_proveidor_crear', "Proveidor creat: {$prov['nombre']} (ID: $newId)");
                header('Location: ' . getAdminUrl('modules/odr_proveidors/?msg=created'));
                exit;
            } else {
                $errors[] = 'Error al crear el proveidor: ' . mysqli_error($bd);
            }
        }
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/odr_proveidors/'); ?>" class="btn btn-secondary">
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
        <a href="<?php echo getAdminUrl('modules/odr_proveidors/'); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Crear Proveidor
        </button>
    </div>
</form>

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
    justify-content: flex-end;
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
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
