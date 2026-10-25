<?php
/**
 * WineWorld Admin - Crear/Editar article
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once __DIR__ . '/textos_helpers.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$articulo = null;
$pageTitle = $id ? 'Editar article' : 'Nou article';

if ($id > 0) {
    $result = mysqli_query($bd, "SELECT * FROM ww_articulos WHERE id = $id");
    if ($result) $articulo = mysqli_fetch_assoc($result);
    if (!$articulo) {
        header('Location: ' . getAdminUrl('modules/wineworld/articulos.php'));
        exit;
    }
}

// Processar formulari
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $mensaje = ['type' => 'error', 'text' => 'Error de seguretat.'];
    } else {
        $titulo = trim($_POST['titulo'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $id_categoria = intval($_POST['id_categoria'] ?? 0);
        $extracto = trim($_POST['extracto'] ?? '');
        $contenido = $_POST['contenido'] ?? '';
        $autor = trim($_POST['autor'] ?? '');
        $publicado = isset($_POST['publicado']) ? 1 : 0;
        $fecha_publicacion = trim($_POST['fecha_publicacion'] ?? '');
        $consejo = trim($_POST['consejo'] ?? '');
        $faqsJson = wwTextAFaqs($_POST['faqs'] ?? '');
        // Relacions amb categories: valors "ambito:id"; la principal ven («On comprar-ho»).
        $esRelacio = function ($v) { return is_string($v) && preg_match('/^(sector|tipo|procedencia):\d+$/', $v); };
        $relaciones = array_values(array_unique(array_filter((array) ($_POST['relaciones'] ?? []), $esRelacio)));
        $relPrincipal = $_POST['relacion_principal'] ?? '';
        if ($esRelacio($relPrincipal)) {
            $relaciones = array_values(array_diff($relaciones, [$relPrincipal]));
            array_unshift($relaciones, $relPrincipal);
        }

        if (empty($titulo) || empty($id_categoria)) {
            $mensaje = ['type' => 'error', 'text' => 'Títol i categoria són obligatoris.'];
        } else {
            if (empty($slug)) {
                $slug = createSlug($titulo);
            }
            if (empty($fecha_publicacion) && $publicado) {
                $fecha_publicacion = date('Y-m-d H:i:s');
            }

            $tituloSafe = mysqli_real_escape_string($bd, $titulo);
            $slugSafe = mysqli_real_escape_string($bd, $slug);
            $extractoSafe = mysqli_real_escape_string($bd, $extracto);
            $contenidoSafe = mysqli_real_escape_string($bd, $contenido);
            $autorSafe = mysqli_real_escape_string($bd, $autor);
            $fechaSafe = mysqli_real_escape_string($bd, $fecha_publicacion);
            $consejoSql = $consejo !== '' ? "'" . mysqli_real_escape_string($bd, $consejo) . "'" : 'NULL';
            $faqsSql = $faqsJson ? "'" . mysqli_real_escape_string($bd, $faqsJson) . "'" : 'NULL';

            // Handle image
            $imagenField = '';
            if (!empty($_FILES['imagen']['name'])) {
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $filename = 'art-' . $slug . '-' . time() . '.' . $ext;
                    $uploadDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/images/wineworld/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $filename)) {
                        $imagenField = ", imagen = '$filename'";
                    }
                }
            }

            if ($id > 0) {
                $sql = "UPDATE ww_articulos SET
                    titulo = '$tituloSafe',
                    slug = '$slugSafe',
                    id_categoria = $id_categoria,
                    extracto = '$extractoSafe',
                    contenido = '$contenidoSafe',
                    consejo = $consejoSql,
                    faqs = $faqsSql,
                    autor = '$autorSafe',
                    publicado = $publicado,
                    fecha_publicacion = " . ($fechaSafe ? "'$fechaSafe'" : "NULL") . "
                    $imagenField
                    WHERE id = $id";
            } else {
                $imgVal = '';
                if (!empty($imagenField)) {
                    preg_match("/imagen = '(.+?)'/", $imagenField, $m);
                    $imgVal = $m[1] ?? '';
                }
                $sql = "INSERT INTO ww_articulos (titulo, slug, id_categoria, extracto, contenido, consejo, faqs, autor, publicado, fecha_publicacion" . ($imgVal ? ", imagen" : "") . ")
                        VALUES ('$tituloSafe', '$slugSafe', $id_categoria, '$extractoSafe', '$contenidoSafe', $consejoSql, $faqsSql, '$autorSafe', $publicado, " . ($fechaSafe ? "'$fechaSafe'" : "NULL") . ($imgVal ? ", '$imgVal'" : "") . ")";
            }

            if (mysqli_query($bd, $sql)) {
                $newId = $id ?: mysqli_insert_id($bd);

                mysqli_query($bd, "DELETE FROM ww_articulo_relaciones WHERE id_articulo = $newId");
                foreach ($relaciones as $i => $rel) {
                    [$relAmbito, $relId] = explode(':', $rel);
                    mysqli_query($bd, "INSERT INTO ww_articulo_relaciones (id_articulo, ambito, id_ref, principal)
                                       VALUES ($newId, '$relAmbito', " . intval($relId) . ", " . ($i === 0 ? 1 : 0) . ")");
                }
                logActivity('wineworld', ($id ? 'Article actualitzat' : 'Article creat') . ": $titulo (ID: $newId)");

                // Reload article data
                $result = mysqli_query($bd, "SELECT * FROM ww_articulos WHERE id = $newId");
                if ($result) $articulo = mysqli_fetch_assoc($result);
                $id = $newId;

                $mensaje = ['type' => 'success', 'text' => 'Article ' . ($id ? 'actualitzat' : 'creat') . ' correctament.'];
            } else {
                $mensaje = ['type' => 'error', 'text' => 'Error: ' . mysqli_error($bd)];
            }
        }
    }
}

// Categorias
$categorias = [];
$resCat = mysqli_query($bd, "SELECT * FROM ww_categorias WHERE activo = 1 ORDER BY orden, nombre");
if ($resCat) {
    while ($row = mysqli_fetch_assoc($resCat)) {
        $categorias[] = $row;
    }
}

// Relacions actuals (la principal primer) i totes les pàgines de llistat.
$relacionesActuales = [];
if ($id > 0) {
    $resRel = mysqli_query($bd, "SELECT ambito, id_ref FROM ww_articulo_relaciones WHERE id_articulo = $id ORDER BY principal DESC, ambito, id_ref");
    while ($resRel && $row = mysqli_fetch_assoc($resRel)) $relacionesActuales[] = $row['ambito'] . ':' . $row['id_ref'];
}
$paginasLlistat = [];
foreach (wwPaginesLlistat($bd) as $p) $paginasLlistat[$p['grup']][] = $p;

$programat = $articulo && $articulo['publicado'] && !empty($articulo['fecha_publicacion']) && strtotime($articulo['fecha_publicacion']) > time();

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="content-header">
    <div>
        <h2><?php echo $id ? 'Editar article' : 'Nou article'; ?></h2>
    </div>
    <div style="display:flex;gap:0.5rem;">
        <a href="<?php echo getAdminUrl('modules/wineworld/articulos.php'); ?>" class="btn btn-secondary">← Articles</a>
        <?php if ($articulo && $articulo['publicado']):
            $catRes = mysqli_query($bd, "SELECT slug FROM ww_categorias WHERE id = " . intval($articulo['id_categoria']));
            $catRow = mysqli_fetch_assoc($catRes);
        ?>
        <a href="/wineworld/<?php echo e($catRow['slug']); ?>/<?php echo e($articulo['slug']); ?>" class="btn btn-secondary" target="_blank">
            Veure al web ↗
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $mensaje['type']; ?>">
    <?php echo e($mensaje['text']); ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;">
        <!-- Main Content -->
        <div>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body">
                    <div class="form-group">
                        <label>Títol *</label>
                        <input type="text" name="titulo" class="form-control" value="<?php echo $articulo ? e($articulo['titulo']) : ''; ?>" required placeholder="Títol de l'article" style="font-size:1.2rem;">
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" class="form-control" value="<?php echo $articulo ? e($articulo['slug']) : ''; ?>" placeholder="Auto-generat si buit">
                    </div>
                    <div class="form-group">
                        <label>Extracte</label>
                        <textarea name="extracto" class="form-control" rows="3" placeholder="Resum breu de l'article (apareix a les targetes i meta description)"><?php echo $articulo ? e($articulo['extracto']) : ''; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Contingut</h3>
                    <small style="color:var(--text-muted)">Suporta HTML. Usa &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;blockquote&gt;, &lt;ul&gt;, &lt;img&gt;...</small>
                </div>
                <div class="card-body">
                    <textarea name="contenido" id="contenido" class="form-control" rows="25" style="font-family:monospace;font-size:0.9rem;line-height:1.6;"><?php echo $articulo ? e($articulo['contenido']) : ''; ?></textarea>
                </div>
            </div>

            <div class="card" style="margin-top:1.5rem;">
                <div class="card-header">
                    <h3>Consell de la botiga («Te lo aconsejamos»)</h3>
                    <small style="color:var(--text-muted)">Com ho aconsellem a la botiga. Si és buit, surt el text general amb WhatsApp i telèfon.</small>
                </div>
                <div class="card-body">
                    <textarea name="consejo" class="form-control" rows="4"><?php echo $articulo ? e($articulo['consejo'] ?? '') : ''; ?></textarea>
                </div>
            </div>

            <div class="card" style="margin-top:1.5rem;">
                <div class="card-header">
                    <h3>Preguntes freqüents</h3>
                    <small style="color:var(--text-muted)">«P: …» i a sota «R: …», amb una línia en blanc entre blocs. Surten a la pàgina i al schema FAQPage.</small>
                </div>
                <div class="card-body">
                    <textarea name="faqs" class="form-control" rows="10" style="font-family:monospace;font-size:0.9rem;line-height:1.6;"><?php echo $articulo ? e(wwFaqsAText($articulo['faqs'] ?? null)) : ''; ?></textarea>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header"><h3>Publicació</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer">
                            <input type="checkbox" name="publicado" <?php echo ($articulo && $articulo['publicado']) ? 'checked' : ''; ?>>
                            <strong>Publicat</strong>
                        </label>
                        <?php if ($programat): ?>
                        <small style="color:var(--text-muted)">Programat: surt al web el <?php echo date('d/m/Y H:i', strtotime($articulo['fecha_publicacion'])); ?>.</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Data de publicació</label>
                        <input type="datetime-local" name="fecha_publicacion" class="form-control"
                               value="<?php echo $articulo && $articulo['fecha_publicacion'] ? date('Y-m-d\TH:i', strtotime($articulo['fecha_publicacion'])) : ''; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">
                        <?php echo $id ? 'Actualitzar article' : 'Crear article'; ?>
                    </button>
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header"><h3>Categoria</h3></div>
                <div class="card-body">
                    <div class="form-group" style="margin:0">
                        <select name="id_categoria" class="form-control" required>
                            <option value="">Selecciona categoria...</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($articulo && $articulo['id_categoria'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo e($cat['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header"><h3>On comprar-ho</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Categoria principal</label>
                        <select name="relacion_principal" class="form-control">
                            <option value="">Cap</option>
                            <?php foreach ($paginasLlistat as $grup => $llista): ?>
                            <optgroup label="<?php echo e($grup); ?>">
                                <?php foreach ($llista as $p): $v = $p['ambito'] . ':' . $p['id_ref']; ?>
                                <option value="<?php echo $v; ?>" <?php echo ($relacionesActuales[0] ?? '') === $v ? 'selected' : ''; ?>><?php echo e($p['nom']); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <small style="color:var(--text-muted)">La que ven: 4 fitxes amb estoc al final de l'article.</small>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label>Altres categories que tracta</label>
                        <select name="relaciones[]" class="form-control" multiple size="10">
                            <?php foreach ($paginasLlistat as $grup => $llista): ?>
                            <optgroup label="<?php echo e($grup); ?>">
                                <?php foreach ($llista as $p): $v = $p['ambito'] . ':' . $p['id_ref']; ?>
                                <option value="<?php echo $v; ?>" <?php echo in_array($v, array_slice($relacionesActuales, 1), true) ? 'selected' : ''; ?>><?php echo e($p['nom']); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <small style="color:var(--text-muted)">Ctrl + clic per triar-ne diverses. L'article surt al bloc «Del blog» de totes.</small>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header"><h3>Imatge destacada</h3></div>
                <div class="card-body">
                    <?php if ($articulo && !empty($articulo['imagen'])): ?>
                    <div style="margin-bottom:0.75rem;">
                        <img src="/images/wineworld/<?php echo e($articulo['imagen']); ?>" alt="" style="width:100%;border-radius:8px;aspect-ratio:16/10;object-fit:cover;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Autor</h3></div>
                <div class="card-body">
                    <input type="text" name="autor" class="form-control" value="<?php echo $articulo ? e($articulo['autor']) : ''; ?>" placeholder="Nom de l'autor">
                </div>
            </div>
        </div>
    </div>
</form>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
