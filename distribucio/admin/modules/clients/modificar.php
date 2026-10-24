<?php
/**
 * El Celler Nou - Mòdul Clients - Modificar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Editar Client';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/clients/?msg=error'));
    exit;
}

// Obtener cliente
$sql = "SELECT c.*, fp.nombre as forma_pago_nombre
        FROM clientes c
        LEFT JOIN formas_pago fp ON c.idFormaPago = fp.id
        WHERE c.id = $id";
$result = mysqli_query($bd, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/clients/?msg=error'));
    exit;
}

$client = mysqli_fetch_assoc($result);
$errors = [];

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

        // Actualizar si no hay errores
        if (empty($errors)) {
            $sql = "UPDATE clientes SET
                    nombre = ?,
                    nombre_fiscal = ?,
                    telefono = ?,
                    email = ?,
                    nif = ?,
                    direccion = ?,
                    cp = ?,
                    poblacion = ?,
                    provincia = ?,
                    idFormaPago = ?,
                    idCondiciones = ?,
                    diaPago = ?,
                    profesional = ?,
                    recarrec_equiv = ?
                    WHERE id = ?";

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
                $client['idFormaPago'],
                $client['idCondiciones'],
                $client['diaPago'],
                $client['profesional'],
                $client['recarrec_equiv'],
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                logActivity('client_modificar', "Client modificat: {$client['nombre']} (ID: $id)");
                header('Location: ' . getAdminUrl('modules/clients/?msg=updated'));
                exit;
            } else {
                $errors[] = 'Error al actualitzar el client: ' . mysqli_error($bd);
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

// Obtener estadísticas del cliente
$clientStats = ['total_ventas' => 0, 'num_tiquets' => 0, 'num_facturas' => 0];

$row = @mysqli_fetch_assoc(@mysqli_query($bd, "SELECT COUNT(*) as total FROM tiquets WHERE idCliente = $id"));
if ($row) $clientStats['num_tiquets'] = $row['total'];

$row = @mysqli_fetch_assoc(@mysqli_query($bd, "SELECT COUNT(*) as total FROM facturas WHERE idCliente = $id"));
if ($row) $clientStats['num_facturas'] = $row['total'];

$row = @mysqli_fetch_assoc(@mysqli_query($bd, "SELECT COALESCE(SUM(lt.cantidad * lt.precio_unidad), 0) as total FROM linea_tiquet lt INNER JOIN tiquets t ON lt.idVenta = t.id WHERE t.idCliente = $id"));
if ($row) $clientStats['total_ventas'] = $row['total'];

$pageTitle = 'Editar: ' . $client['nombre'];

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo getAdminUrl('modules/clients/'); ?>" class="btn btn-secondary">
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

<!-- Estadísticas del client -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($clientStats['total_ventas'], 2, ',', '.'); ?> &euro;</div>
            <div class="stat-label">Total Vendes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($clientStats['num_tiquets']); ?></div>
            <div class="stat-label">Tiquets</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo number_format($clientStats['num_facturas']); ?></div>
            <div class="stat-label">Factures</div>
        </div>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Dades del Client</h3>
            <span class="badge <?php echo $client['profesional'] ? 'badge-success' : 'badge-info'; ?>">
                <?php echo $client['profesional'] ? 'Professional' : 'Particular'; ?>
            </span>
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
        <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
        </button>
        <div style="margin-left: auto; display: flex; gap: 1rem;">
            <a href="<?php echo getAdminUrl('modules/clients/'); ?>" class="btn btn-secondary">Cancel·lar</a>
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
        <div class="modal-header">
            <h3>Confirmar eliminació</h3>
        </div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar el client <strong><?php echo e($client['nombre']); ?></strong>?</p>
            <p style="color: var(--gray-500); font-size: 0.875rem;">Aquesta acció no es pot desfer. S'eliminaran també els preus especials associats.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form method="POST" action="<?php echo getAdminUrl('modules/clients/eliminar.php'); ?>" style="display: inline;">
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
