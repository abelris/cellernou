<?php
/**
 * WineWorld Admin - Textos de categoria
 *
 * Introducció, text llarg i preguntes freqüents de cada llistat de la web
 * (seccions, tipus, D.O. i orígens de cervesa). Es pinten només a la pàgina 1.
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once __DIR__ . '/textos_helpers.php';

$pageTitle = 'WineWorld - Textos de categoria';

$grupFilter = $_GET['grup'] ?? '';
$estatFilter = $_GET['estat'] ?? 'tots';

$pagines = wwPaginesLlistat($bd);

// Textos existents: "ambito:id" => fila
$textos = [];
$r = mysqli_query($bd, "SELECT ambito, id_ref, publicado, updated_at, CHAR_LENGTH(intro) AS lintro,
                               CHAR_LENGTH(cuerpo) AS lcos, faqs FROM categoria_textos");
while ($r && $row = mysqli_fetch_assoc($r)) {
    $textos[$row['ambito'] . ':' . $row['id_ref']] = $row;
}

$grups = array_values(array_unique(array_column($pagines, 'grup')));
$ambText = 0;
foreach ($pagines as $p) {
    if (isset($textos[$p['ambito'] . ':' . $p['id_ref']])) $ambText++;
}

$pagines = array_filter($pagines, function ($p) use ($grupFilter, $estatFilter, $textos) {
    if ($grupFilter !== '' && $p['grup'] !== $grupFilter) return false;
    $t = $textos[$p['ambito'] . ':' . $p['id_ref']] ?? null;
    if ($estatFilter === 'amb' && !$t) return false;
    if ($estatFilter === 'sense' && $t) return false;
    return true;
});

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="content-header">
    <div>
        <h2>Textos de categoria</h2>
        <p><?php echo $ambText; ?> pàgines amb text · la primera frase de la introducció és la meta description (155 caràcters com a màxim)</p>
    </div>
    <div style="display:flex;gap:0.5rem;">
        <a href="<?php echo getAdminUrl('modules/wineworld/articulos.php'); ?>" class="btn btn-secondary">Articles</a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form method="GET" style="display:flex;gap:0.75rem;align-items:end;flex-wrap:wrap;">
            <div class="form-group" style="margin:0;">
                <label>Grup</label>
                <select name="grup" class="form-control">
                    <option value="">Tots</option>
                    <?php foreach ($grups as $g): ?>
                    <option value="<?php echo e($g); ?>" <?php echo $grupFilter === $g ? 'selected' : ''; ?>><?php echo e($g); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label>Estat</label>
                <select name="estat" class="form-control">
                    <option value="tots">Tots</option>
                    <option value="amb" <?php echo $estatFilter === 'amb' ? 'selected' : ''; ?>>Amb text</option>
                    <option value="sense" <?php echo $estatFilter === 'sense' ? 'selected' : ''; ?>>Sense text</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Pàgina</th>
                    <th>Grup</th>
                    <th>Productes</th>
                    <th>Text</th>
                    <th>Actualitzat</th>
                    <th style="width:120px">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagines as $p): ?>
                <?php $t = $textos[$p['ambito'] . ':' . $p['id_ref']] ?? null; ?>
                <tr>
                    <td>
                        <strong><?php echo e($p['nom']); ?></strong><br>
                        <small style="color:var(--text-muted)"><?php echo e($p['url']); ?></small>
                    </td>
                    <td><?php echo e($p['grup']); ?></td>
                    <td><?php echo $p['productes'] === null ? '-' : $p['productes']; ?></td>
                    <td>
                        <?php if (!$t): ?>
                        <span class="badge badge-warning">Sense text</span>
                        <?php else: ?>
                        <span class="badge badge-<?php echo $t['publicado'] ? 'success' : 'warning'; ?>"><?php echo $t['publicado'] ? 'Publicat' : 'Esborrany'; ?></span>
                        <small style="color:var(--text-muted)">
                            <?php echo (int) $t['lcos']; ?> car. · <?php echo count(json_decode((string) $t['faqs'], true) ?: []); ?> FAQ
                        </small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $t ? date('d/m/Y H:i', strtotime($t['updated_at'])) : '-'; ?></td>
                    <td>
                        <div style="display:flex;gap:0.25rem;">
                            <a href="<?php echo getAdminUrl('modules/wineworld/texto.php?ambito=' . $p['ambito'] . '&id=' . $p['id_ref']); ?>" class="btn btn-sm btn-secondary"><?php echo $t ? 'Editar' : 'Escriure'; ?></a>
                            <a href="<?php echo e($p['url']); ?>" class="btn btn-sm btn-secondary" target="_blank" title="Veure al web">↗</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
