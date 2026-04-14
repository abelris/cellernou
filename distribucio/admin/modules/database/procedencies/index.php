<?php
/**
 * El Celler Nou - Gestió Procedències / D.O.
 */
require_once dirname(dirname(dirname(__DIR__))) . '/includes/auth.php';

$pageTitle = 'Procedències / D.O.';

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $deleteId = (int)$_POST['delete_id'];

        $check = mysqli_query($bd, "SELECT COUNT(*) as total FROM productos WHERE idProcedencia = $deleteId");
        $count = mysqli_fetch_assoc($check)['total'];

        if ($count > 0) {
            $mensaje = ['type' => 'error', 'text' => "No es pot eliminar. Hi ha $count productes associats."];
        } else {
            $result = mysqli_query($bd, "DELETE FROM procedencia WHERE id = $deleteId");
            if ($result) {
                logActivity('procedencia_delete', "Procedència eliminada ID: $deleteId");
                $mensaje = ['type' => 'success', 'text' => 'Procedència eliminada correctament.'];
            }
        }
    }
}

// Obtener procedencias con conteo
$procedencias = [];
$sql = "SELECT pr.*, COUNT(p.id) as total_productos
        FROM procedencia pr
        LEFT JOIN productos p ON p.idProcedencia = pr.id AND p.activo = 1
        GROUP BY pr.id
        ORDER BY pr.nombre_es ASC";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $procedencias[] = $row;
    }
}

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created': $mensaje = ['type' => 'success', 'text' => 'Procedència creada correctament.']; break;
        case 'updated': $mensaje = ['type' => 'success', 'text' => 'Procedència actualitzada correctament.']; break;
    }
}

$csrfToken = generateCsrfToken();
include dirname(dirname(dirname(__DIR__))) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo getAdminUrl('modules/database/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar
    </a>
    <a href="<?php echo getAdminUrl('modules/database/procedencies/crear.php'); ?>" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nova Procedència
    </a>
</div>

<?php if (isset($mensaje)): ?>
<div class="alert alert-<?php echo $mensaje['type']; ?>" style="margin-bottom: 1rem;">
    <?php echo e($mensaje['text']); ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Procedències / D.O. <span class="badge badge-info"><?php echo count($procedencias); ?></span></h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($procedencias)): ?>
        <div class="empty-state" style="padding: 3rem;">
            <h3>No hi ha procedències</h3>
            <p>Crea la primera procedència per començar.</p>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Nom (ES)</th>
                        <th>Nom (CAT)</th>
                        <th style="text-align: center;">Productes</th>
                        <th style="width: 120px;">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($procedencias as $proc): ?>
                    <tr>
                        <td><strong>#<?php echo $proc['id']; ?></strong></td>
                        <td><?php echo e($proc['nombre_es']); ?></td>
                        <td><?php echo e($proc['nombre_ca'] ?? '-'); ?></td>
                        <td style="text-align: center;">
                            <span class="badge badge-info"><?php echo $proc['total_productos']; ?></span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="<?php echo getAdminUrl('modules/database/procedencies/modificar.php?id=' . $proc['id']); ?>"
                                   class="btn btn-sm btn-secondary" title="Editar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <?php if ($proc['total_productos'] == 0): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Estàs segur?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="delete_id" value="<?php echo $proc['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.alert { padding: 1rem; border-radius: 8px; font-size: 0.875rem; }
.alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
</style>

<?php include dirname(dirname(dirname(__DIR__))) . '/includes/footer.php'; ?>
