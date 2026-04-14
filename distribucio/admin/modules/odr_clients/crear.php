<?php
/**
 * OnDigitalRis - Mòdul Clients - Crear
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';

$pageTitle = 'ODR - Nou Client';

$errors = [];
$client = [
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

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        // Recoger datos
        $client['nombre'] = trim($_POST['nombre'] ?? '');
        $client['nombre_fiscal'] = trim($_POST['nombre_fiscal'] ?? '');
        $client['nif'] = trim($_POST['nif'] ?? '');
        $client['telefono'] = trim($_POST['telefono'] ?? '');
        $client['email'] = trim($_POST['email'] ?? '');
        $client['direccion'] = trim($_POST['direccion'] ?? '');
        $client['cp'] = trim($_POST['cp'] ?? '');
        $client['poblacion'] = trim($_POST['poblacion'] ?? '');
        $client['provincia'] = trim($_POST['provincia'] ?? '');
        $client['notas'] = trim($_POST['notas'] ?? '');

        // Validaciones
        if (empty($client['nombre'])) {
            $errors[] = 'El nom del client és obligatori.';
        }

        if (!empty($client['email']) && !filter_var($client['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El format de l\'email no és vàlid.';
        }

        // Insertar si no hay errores
        if (empty($errors)) {
            $sql = "INSERT INTO odr_clientes (nombre, nombre_fiscal, nif, telefono, email, direccion, cp, poblacion, provincia, notas, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'ssssssssss',
                $client['nombre'],
                $client['nombre_fiscal'],
                $client['nif'],
                $client['telefono'],
                $client['email'],
                $client['direccion'],
                $client['cp'],
                $client['poblacion'],
                $client['provincia'],
                $client['notas']
            );

            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($bd);
                logActivity('odr_client_crear', "Client ODR creat: {$client['nombre']} (ID: $newId)");
                header('Location: ' . getAdminUrl('modules/odr_clients/?msg=created'));
                exit;
            } else {
                $errors[] = 'Error al crear el client: ' . mysqli_error($bd);
            }
        }
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/odr_clients/'); ?>" class="btn btn-secondary">
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
            <h3 class="card-title">Dades del Client</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nom *</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required
                           value="<?php echo e($client['nombre']); ?>" placeholder="Nom comercial">
                </div>

                <div class="form-group">
                    <label class="form-label" for="nombre_fiscal">Nom Fiscal</label>
                    <input type="text" id="nombre_fiscal" name="nombre_fiscal" class="form-input"
                           value="<?php echo e($client['nombre_fiscal']); ?>" placeholder="Raó social">
                </div>

                <div class="form-group">
                    <label class="form-label" for="nif">NIF / CIF</label>
                    <input type="text" id="nif" name="nif" class="form-input"
                           value="<?php echo e($client['nif']); ?>" placeholder="12345678A">
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefono">Telèfon</label>
                    <input type="text" id="telefono" name="telefono" class="form-input"
                           value="<?php echo e($client['telefono']); ?>" placeholder="600 000 000">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input"
                           value="<?php echo e($client['email']); ?>" placeholder="client@exemple.com">
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Adreça</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="direccion">Adreça</label>
                    <input type="text" id="direccion" name="direccion" class="form-input"
                           value="<?php echo e($client['direccion']); ?>" placeholder="Carrer, número, pis...">
                </div>

                <div class="form-group">
                    <label class="form-label" for="cp">Codi Postal</label>
                    <input type="text" id="cp" name="cp" class="form-input"
                           value="<?php echo e($client['cp']); ?>" placeholder="08000">
                </div>

                <div class="form-group">
                    <label class="form-label" for="poblacion">Població</label>
                    <input type="text" id="poblacion" name="poblacion" class="form-input"
                           value="<?php echo e($client['poblacion']); ?>" placeholder="Barcelona">
                </div>

                <div class="form-group">
                    <label class="form-label" for="provincia">Província</label>
                    <input type="text" id="provincia" name="provincia" class="form-input"
                           value="<?php echo e($client['provincia']); ?>" placeholder="Barcelona">
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
                    <label class="form-label" for="notas">Notes</label>
                    <textarea id="notas" name="notas" class="form-input" rows="4"
                              placeholder="Observacions, notes internes..."><?php echo e($client['notas']); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?php echo getAdminUrl('modules/odr_clients/'); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Crear Client
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
