<?php
/**
 * El Celler Nou - Mòdul Clients - Crear
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Nou Client';

$errors = [];
$client = [
    'profesional' => 1,
    'nombre' => '',
    'nombre_fiscal' => '',
    'telefono' => '',
    'email' => '',
    'nif' => '',
    'direccion' => '',
    'cp' => '',
    'poblacion' => '',
    'provincia' => '',
    'idFormaPago' => 0,
    'idCondiciones' => 0,
    'diaPago' => 0,
    'recarrec_equiv' => 0
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        // Recoger datos
        $client['profesional'] = isset($_POST['profesional']) ? 1 : 0;
        $client['nombre'] = trim($_POST['nombre'] ?? '');
        $client['nombre_fiscal'] = trim($_POST['nombre_fiscal'] ?? '');
        $client['telefono'] = trim($_POST['telefono'] ?? '');
        $client['email'] = trim($_POST['email'] ?? '');
        $client['nif'] = trim($_POST['nif'] ?? '');
        $client['direccion'] = trim($_POST['direccion'] ?? '');
        $client['cp'] = trim($_POST['cp'] ?? '');
        $client['poblacion'] = trim($_POST['poblacion'] ?? '');
        $client['provincia'] = trim($_POST['provincia'] ?? '');
        $client['idFormaPago'] = (int)($_POST['idFormaPago'] ?? 0);
        $client['idCondiciones'] = (int)($_POST['idCondiciones'] ?? 0);
        $client['diaPago'] = (int)($_POST['diaPago'] ?? 0);
        $client['recarrec_equiv'] = isset($_POST['recarrec_equiv']) ? 1 : 0;

        // Validaciones
        if (empty($client['nombre'])) {
            $errors[] = 'El nom del client és obligatori.';
        }

        if (!empty($client['email']) && !filter_var($client['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El format de l\'email no és vàlid.';
        }

        // Insertar si no hay errores
        if (empty($errors)) {
            $sql = "INSERT INTO clientes (nombre, nombre_fiscal, telefono, email, nif, direccion, cp, poblacion, provincia, idUsuario, idFormaPago, idCondiciones, diaPago, profesional, recarrec_equiv)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $userId = $_SESSION['admin_user_id'];
            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'sssssssssiiiiii',
                $client['nombre'],
                $client['nombre_fiscal'],
                $client['telefono'],
                $client['email'],
                $client['nif'],
                $client['direccion'],
                $client['cp'],
                $client['poblacion'],
                $client['provincia'],
                $userId,
                $client['idFormaPago'],
                $client['idCondiciones'],
                $client['diaPago'],
                $client['profesional'],
                $client['recarrec_equiv']
            );

            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($bd);
                logActivity('client_crear', "Client creat: {$client['nombre']} (ID: $newId)");
                header('Location: ' . getAdminUrl('modules/clients/?msg=created'));
                exit;
            } else {
                $errors[] = 'Error al crear el client: ' . mysqli_error($bd);
            }
        }
    }
}

// Obtener formas de pago
$formasPago = [];
$result = @mysqli_query($bd, "SELECT id, nombre FROM formas_pago ORDER BY id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $formasPago[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/clients/'); ?>" class="btn btn-secondary">
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
                <div class="form-group" style="grid-column: span 3;">
                    <label class="checkbox-label">
                        <input type="checkbox" name="profesional" <?php echo $client['profesional'] ? 'checked' : ''; ?>>
                        <span>Client professional</span>
                    </label>
                </div>

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
            <h3 class="card-title">Condicions de Pagament</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="idFormaPago">Forma de pagament</label>
                    <select id="idFormaPago" name="idFormaPago" class="form-input">
                        <option value="0">-- Seleccionar --</option>
                        <?php foreach ($formasPago as $fp): ?>
                        <option value="<?php echo $fp['id']; ?>" <?php echo $client['idFormaPago'] == $fp['id'] ? 'selected' : ''; ?>>
                            <?php echo e($fp['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="idCondiciones">Condicions</label>
                    <select id="idCondiciones" name="idCondiciones" class="form-input">
                        <option value="0" <?php echo $client['idCondiciones'] == 0 ? 'selected' : ''; ?>>Pronto pago</option>
                        <option value="15" <?php echo $client['idCondiciones'] == 15 ? 'selected' : ''; ?>>15 Dies</option>
                        <option value="30" <?php echo $client['idCondiciones'] == 30 ? 'selected' : ''; ?>>30 Dies</option>
                        <option value="60" <?php echo $client['idCondiciones'] == 60 ? 'selected' : ''; ?>>60 Dies</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="diaPago">Dia de pagament</label>
                    <select id="diaPago" name="diaPago" class="form-input">
                        <option value="0">Data Factura</option>
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $client['diaPago'] == $i ? 'selected' : ''; ?>>
                            Dia <?php echo $i; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="recarrec_equiv" <?php echo $client['recarrec_equiv'] ? 'checked' : ''; ?>>
                        <span>Recàrrec d'equivalència</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?php echo getAdminUrl('modules/clients/'); ?>" class="btn btn-secondary">Cancel·lar</a>
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
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.625rem 0;
}
.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
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
