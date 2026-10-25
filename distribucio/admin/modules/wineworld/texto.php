<?php
/**
 * WineWorld Admin - Editar el text d'un llistat (taula categoria_textos)
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once __DIR__ . '/textos_helpers.php';

$ambito = $_GET['ambito'] ?? '';
$idRef = intval($_GET['id'] ?? 0);
if (!in_array($ambito, ['sector', 'tipo', 'procedencia', 'origen'], true) || $idRef <= 0) {
    header('Location: ' . getAdminUrl('modules/wineworld/textos.php'));
    exit;
}

$pagina = null;
foreach (wwPaginesLlistat($bd) as $p) {
    if ($p['ambito'] === $ambito && $p['id_ref'] === $idRef) $pagina = $p;
}
if (!$pagina) {
    header('Location: ' . getAdminUrl('modules/wineworld/textos.php'));
    exit;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $mensaje = ['type' => 'error', 'text' => 'Error de seguretat.'];
    } else {
        $tituloSeo = trim($_POST['titulo_seo'] ?? '');
        $intro = trim($_POST['intro'] ?? '');
        $cuerpo = trim($_POST['cuerpo'] ?? '');
        $faqs = wwTextAFaqs($_POST['faqs'] ?? '');
        $publicado = isset($_POST['publicado']) ? 1 : 0;

        $q = function ($v) use ($bd) {
            return $v === null || $v === '' ? 'NULL' : "'" . mysqli_real_escape_string($bd, $v) . "'";
        };
        $sql = "INSERT INTO categoria_textos (ambito, id_ref, titulo_seo, intro, cuerpo, faqs, publicado)
                VALUES ('$ambito', $idRef, {$q($tituloSeo)}, {$q($intro)}, {$q($cuerpo)}, {$q($faqs)}, $publicado)
                ON DUPLICATE KEY UPDATE titulo_seo = VALUES(titulo_seo), intro = VALUES(intro), cuerpo = VALUES(cuerpo),
                                        faqs = VALUES(faqs), publicado = VALUES(publicado)";
        if (mysqli_query($bd, $sql)) {
            logActivity('wineworld', "Text de categoria desat: {$pagina['nom']} ($ambito $idRef)");
            $mensaje = ['type' => 'success', 'text' => 'Text desat.'];
        } else {
            $mensaje = ['type' => 'error', 'text' => 'Error: ' . mysqli_error($bd)];
        }
    }
}

$texto = null;
$r = mysqli_query($bd, "SELECT * FROM categoria_textos WHERE ambito = '$ambito' AND id_ref = $idRef");
if ($r) $texto = mysqli_fetch_assoc($r);

// Articles que tracten aquesta categoria (es relacionen des de l'article).
$articles = [];
$r = mysqli_query($bd, "SELECT a.id, a.titulo, r.principal FROM ww_articulo_relaciones r
                        JOIN ww_articulos a ON a.id = r.id_articulo
                        WHERE r.ambito = '$ambito' AND r.id_ref = $idRef ORDER BY a.titulo");
while ($r && $row = mysqli_fetch_assoc($r)) $articles[] = $row;

$primeraFrase = '';
if ($texto && !empty($texto['intro'])) {
    $primeraFrase = preg_match('/^(.+?\p{Ll}[.!?])(\s|$)/u', trim($texto['intro']), $m) ? $m[1] : trim($texto['intro']);
}

$pageTitle = 'Text: ' . $pagina['nom'];
include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="content-header">
    <div>
        <h2><?php echo e($pagina['nom']); ?></h2>
        <p><?php echo e($pagina['grup']); ?> · <?php echo e($pagina['url']); ?></p>
    </div>
    <div style="display:flex;gap:0.5rem;">
        <a href="<?php echo getAdminUrl('modules/wineworld/textos.php'); ?>" class="btn btn-secondary">← Textos</a>
        <a href="<?php echo e($pagina['url']); ?>" class="btn btn-secondary" target="_blank">Veure al web ↗</a>
    </div>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $mensaje['type']; ?>"><?php echo e($mensaje['text']); ?></div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;">
        <div>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body">
                    <div class="form-group">
                        <label>Títol SEO (opcional)</label>
                        <input type="text" name="titulo_seo" class="form-control" maxlength="90" value="<?php echo $texto ? e($texto['titulo_seo']) : ''; ?>" placeholder="Si és buit, es fa servir el títol automàtic">
                    </div>
                    <div class="form-group">
                        <label>Introducció (sota el títol)</label>
                        <textarea name="intro" class="form-control" rows="3" placeholder="Dues o tres frases. La primera és la meta description."><?php echo $texto ? e($texto['intro']) : ''; ?></textarea>
                        <?php if ($primeraFrase): ?>
                        <small style="color:<?php echo mb_strlen($primeraFrase) > 155 ? 'var(--danger, #c0392b)' : 'var(--text-muted)'; ?>">
                            Primera frase: <?php echo mb_strlen($primeraFrase); ?> caràcters<?php echo mb_strlen($primeraFrase) > 155 ? ' (massa llarga per a la meta description)' : ''; ?>
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <h3>Text llarg</h3>
                    <small style="color:var(--text-muted)">HTML: &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;a href="/..."&gt;. O text pla amb paràgrafs separats per una línia en blanc.</small>
                </div>
                <div class="card-body">
                    <textarea name="cuerpo" class="form-control" rows="20" style="font-family:monospace;font-size:0.9rem;line-height:1.6;"><?php echo $texto ? e($texto['cuerpo']) : ''; ?></textarea>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Preguntes freqüents</h3>
                    <small style="color:var(--text-muted)">Una pregunta per bloc: «P: …» i a sota «R: …», amb una línia en blanc entre blocs. Surten a la pàgina i al schema FAQPage.</small>
                </div>
                <div class="card-body">
                    <textarea name="faqs" class="form-control" rows="14" style="font-family:monospace;font-size:0.9rem;line-height:1.6;"><?php echo $texto ? e(wwFaqsAText($texto['faqs'])) : ''; ?></textarea>
                </div>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header"><h3>Publicació</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer">
                            <input type="checkbox" name="publicado" <?php echo (!$texto || $texto['publicado']) ? 'checked' : ''; ?>>
                            <strong>Publicat</strong>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Desar</button>
                    <?php if ($texto): ?>
                    <p style="margin-top:0.75rem;color:var(--text-muted);font-size:0.85rem;">Actualitzat el <?php echo date('d/m/Y H:i', strtotime($texto['updated_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Articles relacionats</h3></div>
                <div class="card-body">
                    <?php if (!$articles): ?>
                    <p style="color:var(--text-muted);font-size:0.9rem;">Cap. Es relacionen des de l'editor de cada article.</p>
                    <?php else: ?>
                    <ul style="font-size:0.9rem;line-height:1.6;">
                        <?php foreach ($articles as $a): ?>
                        <li><a href="<?php echo getAdminUrl('modules/wineworld/articulo.php?id=' . $a['id']); ?>"><?php echo e($a['titulo']); ?></a><?php echo $a['principal'] ? ' <small>(principal)</small>' : ''; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
