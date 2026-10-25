<?php
/**
 * WineWorld Admin - Llistat d'articles
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'WineWorld - Articles';

// Filtres
$categoriaFilter = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$estadoFilter = $_GET['estado'] ?? 'todos';
$buscar = trim($_GET['buscar'] ?? '');

// Processar eliminació
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && mysqli_query($bd, "DELETE FROM ww_articulos WHERE id = $id")) {
            logActivity('wineworld', "Article eliminat ID: $id");
            $mensaje = ['type' => 'success', 'text' => 'Article eliminat.'];
        }
    }
}

// Construir consulta
$where = "1=1";
if ($categoriaFilter > 0) {
    $where .= " AND a.id_categoria = $categoriaFilter";
}
if ($estadoFilter === 'publicado') {
    $where .= " AND a.publicado = 1";
} elseif ($estadoFilter === 'borrador') {
    $where .= " AND a.publicado = 0";
}
if (!empty($buscar)) {
    $buscarSafe = mysqli_real_escape_string($bd, $buscar);
    $where .= " AND (a.titulo LIKE '%$buscarSafe%' OR a.extracto LIKE '%$buscarSafe%')";
}

$articulos = [];
$sql = "SELECT a.*, c.nombre as categoria_nombre, c.color as categoria_color
        FROM ww_articulos a
        LEFT JOIN ww_categorias c ON a.id_categoria = c.id
        WHERE $where
        ORDER BY a.updated_at DESC";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $articulos[] = $row;
    }
}

// Categorias per filtre
$categorias = [];
$resCat = mysqli_query($bd, "SELECT * FROM ww_categorias ORDER BY orden, nombre");
if ($resCat) {
    while ($row = mysqli_fetch_assoc($resCat)) {
        $categorias[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="content-header">
    <div>
        <h2>Articles WineWorld</h2>
        <p><?php echo count($articulos); ?> article(s)</p>
    </div>
    <div style="display:flex;gap:0.5rem;">
        <a href="<?php echo getAdminUrl('modules/wineworld/'); ?>" class="btn btn-secondary">← Categories</a>
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

<!-- Filters -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" style="display:flex;gap:0.75rem;align-items:end;flex-wrap:wrap;">
            <div class="form-group" style="margin:0;flex:1;min-width:200px;">
                <label>Cercar</label>
                <input type="text" name="buscar" class="form-control" value="<?php echo e($buscar); ?>" placeholder="Cercar per títol...">
            </div>
            <div class="form-group" style="margin:0;">
                <label>Categoria</label>
                <select name="categoria" class="form-control">
                    <option value="0">Totes</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoriaFilter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo e($cat['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label>Estat</label>
                <select name="estado" class="form-control">
                    <option value="todos">Tots</option>
                    <option value="publicado" <?php echo $estadoFilter === 'publicado' ? 'selected' : ''; ?>>Publicats</option>
                    <option value="borrador" <?php echo $estadoFilter === 'borrador' ? 'selected' : ''; ?>>Esborranys</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            <?php if ($categoriaFilter || $estadoFilter !== 'todos' || $buscar): ?>
            <a href="<?php echo getAdminUrl('modules/wineworld/articulos.php'); ?>" class="btn btn-sm btn-secondary">Netejar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Articles Table -->
<div class="card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:60px">Img</th>
                    <th>Títol</th>
                    <th>Categoria</th>
                    <th>Estat</th>
                    <th>Data pub.</th>
                    <th>Actualitzat</th>
                    <th style="width:100px">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articulos as $art): ?>
                <tr>
                    <td>
                        <?php if (!empty($art['imagen'])): ?>
                        <img src="/images/wineworld/<?php echo e($art['imagen']); ?>" alt="" style="width:50px;height:35px;object-fit:cover;border-radius:4px;">
                        <?php else: ?>
                        <span style="display:inline-block;width:50px;height:35px;background:<?php echo e($art['categoria_color'] ?: '#722F37'); ?>;border-radius:4px;opacity:0.3;"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo e($art['titulo']); ?></strong>
                        <?php if (!empty($art['extracto'])): ?>
                        <br><small style="color:var(--text-muted)"><?php echo e(mb_substr($art['extracto'], 0, 80)); ?>...</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:0.35rem;">
                            <span style="width:10px;height:10px;border-radius:50%;background:<?php echo e($art['categoria_color'] ?: '#722F37'); ?>;"></span>
                            <?php echo e($art['categoria_nombre']); ?>
                        </span>
                    </td>
                    <td>
                        <?php $artProgramat = $art['publicado'] && !empty($art['fecha_publicacion']) && strtotime($art['fecha_publicacion']) > time(); ?>
                        <span class="badge badge-<?php echo $art['publicado'] && !$artProgramat ? 'success' : 'warning'; ?>">
                            <?php echo $artProgramat ? 'Programat' : ($art['publicado'] ? 'Publicat' : 'Esborrany'); ?>
                        </span>
                    </td>
                    <td><?php echo $art['fecha_publicacion'] ? date('d/m/Y', strtotime($art['fecha_publicacion'])) : '-'; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($art['updated_at'])); ?></td>
                    <td>
                        <div style="display:flex;gap:0.25rem;">
                            <a href="<?php echo getAdminUrl('modules/wineworld/articulo.php?id=' . $art['id']); ?>" class="btn btn-sm btn-secondary" title="Editar">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Eliminar aquest article?')">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $art['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($articulos)): ?>
                <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">No hi ha articles <?php echo $categoriaFilter ? 'en aquesta categoria' : ''; ?>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
