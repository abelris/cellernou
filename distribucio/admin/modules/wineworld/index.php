<?php
/**
 * WineWorld Admin - Llistat de categories i articles
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'WineWorld';

// Processar accions
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $mensaje = ['type' => 'error', 'text' => 'Error de seguretat.'];
    } else {
        $action = $_POST['action'] ?? '';

        // Crear/editar categoria
        if ($action === 'save_categoria') {
            $id = intval($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $color = trim($_POST['color'] ?? '#722F37');
            $orden = intval($_POST['orden'] ?? 0);
            $activo = isset($_POST['activo']) ? 1 : 0;

            if (empty($nombre)) {
                $mensaje = ['type' => 'error', 'text' => 'El nom és obligatori.'];
            } else {
                if (empty($slug)) {
                    $slug = createSlug($nombre);
                }

                $nombreSafe = mysqli_real_escape_string($bd, $nombre);
                $slugSafe = mysqli_real_escape_string($bd, $slug);
                $descSafe = mysqli_real_escape_string($bd, $descripcion);
                $colorSafe = mysqli_real_escape_string($bd, $color);

                // Handle image upload
                $imagenField = '';
                if (!empty($_FILES['imagen']['name'])) {
                    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $filename = 'cat-' . $slug . '.' . $ext;
                        $uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/images/wineworld/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $filename)) {
                            $imagenField = ", imagen = '$filename'";
                        }
                    }
                }

                if ($id > 0) {
                    $sql = "UPDATE ww_categorias SET nombre = '$nombreSafe', slug = '$slugSafe', descripcion = '$descSafe', color = '$colorSafe', orden = $orden, activo = $activo $imagenField WHERE id = $id";
                } else {
                    $imgVal = '';
                    if ($imagenField) {
                        $imgVal = str_replace(", imagen = '", "", $imagenField);
                        $imgVal = rtrim($imgVal, "'");
                    }
                    $sql = "INSERT INTO ww_categorias (nombre, slug, descripcion, color, orden, activo" . ($imgVal ? ", imagen" : "") . ") VALUES ('$nombreSafe', '$slugSafe', '$descSafe', '$colorSafe', $orden, $activo" . ($imgVal ? ", '$imgVal'" : "") . ")";
                }

                if (mysqli_query($bd, $sql)) {
                    logActivity('wineworld', ($id > 0 ? 'Categoria actualitzada' : 'Categoria creada') . ": $nombre");
                    $mensaje = ['type' => 'success', 'text' => 'Categoria ' . ($id > 0 ? 'actualitzada' : 'creada') . ' correctament.'];
                } else {
                    $mensaje = ['type' => 'error', 'text' => 'Error: ' . mysqli_error($bd)];
                }
            }
        }

        // Eliminar categoria
        if ($action === 'delete_categoria') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                // Check if has articles
                $check = mysqli_query($bd, "SELECT COUNT(*) as c FROM ww_articulos WHERE id_categoria = $id");
                $row = mysqli_fetch_assoc($check);
                if ($row['c'] > 0) {
                    $mensaje = ['type' => 'error', 'text' => "No es pot eliminar: la categoria té {$row['c']} article(s). Elimina'ls primer."];
                } else {
                    if (mysqli_query($bd, "DELETE FROM ww_categorias WHERE id = $id")) {
                        logActivity('wineworld', "Categoria eliminada ID: $id");
                        $mensaje = ['type' => 'success', 'text' => 'Categoria eliminada.'];
                    }
                }
            }
        }
    }
}

// Obtenir dades
$categorias = [];
$result = mysqli_query($bd, "SELECT c.*, (SELECT COUNT(*) FROM ww_articulos WHERE id_categoria = c.id) as num_articulos FROM ww_categorias c ORDER BY c.orden, c.nombre");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categorias[] = $row;
    }
}

// Total articles
$totalArticles = 0;
$resTotal = mysqli_query($bd, "SELECT COUNT(*) as t FROM ww_articulos");
if ($resTotal && $r = mysqli_fetch_assoc($resTotal)) {
    $totalArticles = $r['t'];
}

$editCat = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $res = mysqli_query($bd, "SELECT * FROM ww_categorias WHERE id = $editId");
    if ($res) $editCat = mysqli_fetch_assoc($res);
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="content-header">
    <div>
        <h2>WineWorld</h2>
        <p>Gestió de categories i articles del blog</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?php echo getAdminUrl('modules/wineworld/articulo.php'); ?>" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nou article
        </a>
    </div>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $mensaje['type']; ?>">
    <?php echo e($mensaje['text']); ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-value"><?php echo count($categorias); ?></div>
        <div class="stat-label">Categories</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php echo $totalArticles; ?></div>
        <div class="stat-label">Articles totals</div>
    </div>
</div>

<!-- Categories Section -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3>Categories</h3>
    </div>
    <div class="card-body">
        <!-- Category Form -->
        <form method="POST" enctype="multipart/form-data" style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-secondary); border-radius: 8px;">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="save_categoria">
            <input type="hidden" name="id" value="<?php echo $editCat ? $editCat['id'] : 0; ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr 80px 80px; gap: 0.75rem; align-items: end;">
                <div class="form-group" style="margin:0">
                    <label>Nom</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo $editCat ? e($editCat['nombre']) : ''; ?>" required placeholder="Nom de la categoria">
                </div>
                <div class="form-group" style="margin:0">
                    <label>Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?php echo $editCat ? e($editCat['slug']) : ''; ?>" placeholder="Auto-generat si buit">
                </div>
                <div class="form-group" style="margin:0">
                    <label>Color</label>
                    <input type="color" name="color" value="<?php echo $editCat ? e($editCat['color']) : '#722F37'; ?>" style="height:38px;width:100%;cursor:pointer;border:1px solid var(--border-color);border-radius:6px;">
                </div>
                <div class="form-group" style="margin:0">
                    <label>Ordre</label>
                    <input type="number" name="orden" class="form-control" value="<?php echo $editCat ? $editCat['orden'] : 0; ?>" min="0">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.75rem;">
                <div class="form-group" style="margin:0">
                    <label>Descripció</label>
                    <textarea name="descripcion" class="form-control" rows="2" placeholder="Descripció breu de la categoria"><?php echo $editCat ? e($editCat['descripcion']) : ''; ?></textarea>
                </div>
                <div style="display:flex;gap:1rem;align-items:end;">
                    <div class="form-group" style="margin:0;flex:1">
                        <label>Imatge</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer">
                            <input type="checkbox" name="activo" <?php echo (!$editCat || $editCat['activo']) ? 'checked' : ''; ?>>
                            Activa
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $editCat ? 'Actualitzar' : 'Crear'; ?></button>
                    <?php if ($editCat): ?>
                    <a href="<?php echo getAdminUrl('modules/wineworld/'); ?>" class="btn btn-secondary">Cancel·lar</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <!-- Categories Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px">Color</th>
                    <th>Nom</th>
                    <th>Slug</th>
                    <th>Articles</th>
                    <th>Ordre</th>
                    <th>Estat</th>
                    <th style="width:120px">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $cat): ?>
                <tr>
                    <td><span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:<?php echo e($cat['color']); ?>;"></span></td>
                    <td><strong><?php echo e($cat['nombre']); ?></strong></td>
                    <td><code><?php echo e($cat['slug']); ?></code></td>
                    <td>
                        <?php if ($cat['num_articulos'] > 0): ?>
                        <a href="<?php echo getAdminUrl('modules/wineworld/articulos.php?categoria=' . $cat['id']); ?>"><?php echo $cat['num_articulos']; ?> article(s)</a>
                        <?php else: ?>
                        <span style="color:var(--text-muted)">0</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $cat['orden']; ?></td>
                    <td>
                        <span class="badge badge-<?php echo $cat['activo'] ? 'success' : 'secondary'; ?>">
                            <?php echo $cat['activo'] ? 'Activa' : 'Inactiva'; ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:0.25rem;">
                            <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar aquesta categoria?')">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <input type="hidden" name="action" value="delete_categoria">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categorias)): ?>
                <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">No hi ha categories. Crea la primera amb el formulari de dalt.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Articles -->
<?php
$recentArticles = [];
$resRecent = mysqli_query($bd, "SELECT a.*, c.nombre as categoria_nombre FROM ww_articulos a LEFT JOIN ww_categorias c ON a.id_categoria = c.id ORDER BY a.updated_at DESC LIMIT 10");
if ($resRecent) {
    while ($row = mysqli_fetch_assoc($resRecent)) {
        $recentArticles[] = $row;
    }
}
?>

<div class="card">
    <div class="card-header">
        <h3>Articles recents</h3>
        <a href="<?php echo getAdminUrl('modules/wineworld/articulos.php'); ?>" class="btn btn-sm btn-secondary">Veure tots</a>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Títol</th>
                    <th>Categoria</th>
                    <th>Estat</th>
                    <th>Data publicació</th>
                    <th style="width:80px">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentArticles as $art): ?>
                <tr>
                    <td><strong><?php echo e($art['titulo']); ?></strong></td>
                    <td><?php echo e($art['categoria_nombre']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $art['publicado'] ? 'success' : 'warning'; ?>">
                            <?php echo $art['publicado'] ? 'Publicat' : 'Esborrany'; ?>
                        </span>
                    </td>
                    <td><?php echo $art['fecha_publicacion'] ? date('d/m/Y', strtotime($art['fecha_publicacion'])) : '-'; ?></td>
                    <td>
                        <a href="<?php echo getAdminUrl('modules/wineworld/articulo.php?id=' . $art['id']); ?>" class="btn btn-sm btn-secondary">Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentArticles)): ?>
                <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-muted)">No hi ha articles encara.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
