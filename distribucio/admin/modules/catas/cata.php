<?php
/**
 * Catas - Fitxa d'una cata
 *
 * Dades de la cata (mínim, data, preu, lloc, publicada), la llista
 * d'apuntats i l'avís amb la data: per correu a tots alhora i per WhatsApp
 * un a un.
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once __DIR__ . '/helpers.php';

$id = (int) ($_GET['id'] ?? 0);
$mensaje = '';

$carregar = function ($id) use ($bd) {
    $r = mysqli_query($bd, "SELECT * FROM catas WHERE id = " . (int) $id);
    return $r ? mysqli_fetch_assoc($r) : null;
};
$cata = $id ? $carregar($id) : null;
if ($id && !$cata) {
    header('Location: ' . getAdminUrl('modules/catas/'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $mensaje = ['type' => 'error', 'text' => 'Error de seguretat.'];
    } elseif (($_POST['accio'] ?? '') === 'desar') {
        $titulo = trim($_POST['titulo'] ?? '');
        $fecha = trim($_POST['fecha'] ?? '');
        $fechaDb = $fecha !== '' ? date('Y-m-d H:i:s', strtotime($fecha)) : null;
        $estado = in_array($_POST['estado'] ?? '', ['abierta', 'confirmada', 'hecha', 'cancelada'], true) ? $_POST['estado'] : 'abierta';
        // Posar data a una cata oberta la confirma; treure-la la torna a obrir.
        if ($fechaDb && $estado === 'abierta') $estado = 'confirmada';
        if (!$fechaDb && $estado === 'confirmada') $estado = 'abierta';

        if ($titulo === '') {
            $mensaje = ['type' => 'error', 'text' => 'Cal un títol.'];
        } else {
            $q = function ($v) use ($bd) {
                return $v === null || $v === '' ? 'NULL' : "'" . mysqli_real_escape_string($bd, (string) $v) . "'";
            };
            $precio = trim(str_replace(',', '.', $_POST['precio'] ?? ''));
            $precio = $precio !== '' ? (string) round((float) $precio, 2) : null;
            $maximo = (int) ($_POST['maximo'] ?? 0) ?: null;
            $camps = "titulo = {$q($titulo)}, descripcion = {$q(trim($_POST['descripcion'] ?? ''))},
                      precio = {$q($precio)}, minimo = " . max(1, (int) ($_POST['minimo'] ?? 8)) . ",
                      maximo = " . ($maximo ?: 'NULL') . ", fecha = {$q($fechaDb)}, lugar = {$q(trim($_POST['lugar'] ?? ''))},
                      estado = '$estado', publicada = " . (isset($_POST['publicada']) ? 1 : 0) . ",
                      orden = " . (int) ($_POST['orden'] ?? 0);
            $ok = $id
                ? mysqli_query($bd, "UPDATE catas SET $camps WHERE id = $id")
                : mysqli_query($bd, "INSERT INTO catas SET $camps");
            if ($ok) {
                if (!$id) $id = mysqli_insert_id($bd);
                logActivity('catas', "Cata desada: $titulo ($id)");
                header('Location: ' . getAdminUrl('modules/catas/cata.php?id=' . $id . '&desat=1'));
                exit;
            }
            $mensaje = ['type' => 'error', 'text' => 'Error: ' . mysqli_error($bd)];
        }
    } elseif (($_POST['accio'] ?? '') === 'avisar' && $cata) {
        // Correu amb la data als apuntats que encara no l'han rebut (o a tots).
        if (!$cata['fecha']) {
            $mensaje = ['type' => 'error', 'text' => 'Primer poseu la data i deseu.'];
        } else {
            $tots = !empty($_POST['tots']);
            $r = mysqli_query($bd, "SELECT * FROM catas_inscripciones WHERE id_cata = $id AND tipo = 'publica'
                                    AND estado <> 'baja'" . ($tots ? '' : ' AND avisada_at IS NULL'));
            $enviats = 0;
            $errors = [];
            while ($r && $ins = mysqli_fetch_assoc($r)) {
                if (catasCorreuData($cata, $ins)) {
                    $enviats++;
                    mysqli_query($bd, "UPDATE catas_inscripciones SET avisada_at = NOW(),
                                       estado = IF(estado = 'nueva', 'avisada', estado) WHERE id = " . (int) $ins['id']);
                } else {
                    $errors[] = $ins['email'];
                }
            }
            logActivity('catas', "Avís de data enviat: {$cata['titulo']} ($enviats correus)");
            $mensaje = $errors
                ? ['type' => 'error', 'text' => "Enviats $enviats. No s'han pogut enviar: " . implode(', ', $errors)]
                : ['type' => 'success', 'text' => $enviats ? "Data enviada per correu a $enviats apuntats." : 'No hi havia ningú pendent d\'avisar.'];
        }
    } elseif (($_POST['accio'] ?? '') === 'estat_inscripcio' && $cata) {
        $idIns = (int) ($_POST['id_inscripcion'] ?? 0);
        $nou = in_array($_POST['estado'] ?? '', ['nueva', 'avisada', 'confirmada', 'baja'], true) ? $_POST['estado'] : 'nueva';
        mysqli_query($bd, "UPDATE catas_inscripciones SET estado = '$nou' WHERE id = $idIns AND id_cata = $id");
        header('Location: ' . getAdminUrl('modules/catas/cata.php?id=' . $id) . '#apuntats');
        exit;
    }
}
if (isset($_GET['desat'])) $mensaje = ['type' => 'success', 'text' => 'Cata desada.'];

$cata = $id ? $carregar($id) : null;
$inscrits = [];
$apuntats = 0;
if ($cata) {
    $r = mysqli_query($bd, "SELECT * FROM catas_inscripciones WHERE id_cata = $id AND tipo = 'publica' ORDER BY created_at");
    while ($r && $row = mysqli_fetch_assoc($r)) {
        $inscrits[] = $row;
        if ($row['estado'] !== 'baja') $apuntats += (int) $row['personas'];
    }
}
$pendentsAvis = count(array_filter($inscrits, function ($i) { return $i['estado'] !== 'baja' && !$i['avisada_at']; }));

$v = function ($camp, $defecte = '') use ($cata) {
    return e($cata[$camp] ?? $defecte);
};
$estatsIns = ['nueva' => 'Apuntat', 'avisada' => 'Avisat', 'confirmada' => 'Confirmat', 'baja' => 'Baixa'];

$pageTitle = $cata ? 'Cata: ' . $cata['titulo'] : 'Nova cata';
include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="content-header">
    <div>
        <h2><?php echo $cata ? e($cata['titulo']) : 'Nova cata'; ?></h2>
        <?php if ($cata): ?>
        <p><strong><?php echo $apuntats; ?></strong> apuntats de <?php echo (int) $cata['minimo']; ?> de mínim<?php echo $apuntats >= (int) $cata['minimo'] && !$cata['fecha'] ? ' · <strong>ja hi ha mínim: poseu la data</strong>' : ''; ?></p>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:0.5rem;">
        <a href="<?php echo getAdminUrl('modules/catas/'); ?>" class="btn btn-secondary">← Catas</a>
        <?php if ($cata && $cata['publicada']): ?>
        <a href="<?php echo getBaseUrl(); ?>/catas#cata-<?php echo $id; ?>" class="btn btn-secondary" target="_blank">Veure al web ↗</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $mensaje['type']; ?>"><?php echo e($mensaje['text']); ?></div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="accio" value="desar">

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label>Títol (es veu a la web, en castellà)</label>
                    <input type="text" name="titulo" class="form-control" maxlength="150" required value="<?php echo $v('titulo'); ?>" placeholder="Vinos de Alella, la D.O. del Maresme">
                </div>
                <div class="form-group">
                    <label>Descripció</label>
                    <textarea name="descripcion" class="form-control" rows="4" placeholder="Què es tastarà, en dues o tres frases."><?php echo $v('descripcion'); ?></textarea>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1rem;">
                    <div class="form-group">
                        <label>Mínim de persones</label>
                        <input type="number" name="minimo" class="form-control" min="1" value="<?php echo $v('minimo', 8); ?>">
                    </div>
                    <div class="form-group">
                        <label>Màxim (opcional)</label>
                        <input type="number" name="maximo" class="form-control" min="1" value="<?php echo $v('maximo'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Preu per persona (opcional)</label>
                        <input type="text" name="precio" class="form-control" inputmode="decimal" value="<?php echo $cata && $cata['precio'] !== null ? e(str_replace('.', ',', (string) (float) $cata['precio'])) : ''; ?>" placeholder="Sense preu no es mostra">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label>Data i hora (quan s'arribi al mínim)</label>
                        <input type="datetime-local" name="fecha" class="form-control" value="<?php echo $cata && $cata['fecha'] ? date('Y-m-d\TH:i', strtotime($cata['fecha'])) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Lloc (opcional)</label>
                        <input type="text" name="lugar" class="form-control" maxlength="200" value="<?php echo $v('lugar'); ?>" placeholder="Per exemple: a la botiga, C/ Ramón Llull 15">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Publicació</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer">
                        <input type="checkbox" name="publicada" <?php echo $cata && $cata['publicada'] ? 'checked' : ''; ?>>
                        <strong>Publicada a /catas</strong>
                    </label>
                </div>
                <div class="form-group">
                    <label>Estat</label>
                    <select name="estado" class="form-control">
                        <?php foreach (['abierta' => 'Oberta (sense data)', 'confirmada' => 'Amb data', 'hecha' => 'Feta', 'cancelada' => 'Cancel·lada'] as $k => $t): ?>
                        <option value="<?php echo $k; ?>"<?php echo ($cata['estado'] ?? 'abierta') === $k ? ' selected' : ''; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:var(--text-muted)">Si poseu data, passa sola a «Amb data». Les fetes i cancel·lades ja no surten a la web.</small>
                </div>
                <div class="form-group">
                    <label>Ordre</label>
                    <input type="number" name="orden" class="form-control" value="<?php echo $v('orden', 0); ?>">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Desar</button>
            </div>
        </div>
    </div>
</form>

<?php if ($cata): ?>
<div class="card" style="margin-top:1.5rem;" id="apuntats">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
        <h3>Apuntats (<?php echo $apuntats; ?> persones)</h3>
        <?php if ($cata['fecha']): ?>
        <form method="POST" style="margin:0;display:flex;gap:0.5rem;">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="accio" value="avisar">
            <button type="submit" class="btn btn-primary" <?php echo $pendentsAvis ? '' : 'disabled'; ?>>Enviar la data per correu (<?php echo $pendentsAvis; ?> pendents)</button>
            <button type="submit" name="tots" value="1" class="btn btn-secondary">Tornar a enviar a tots</button>
        </form>
        <?php else: ?>
        <small style="color:var(--text-muted)">Quan poseu la data, des d'aquí s'avisa tothom per correu.</small>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr><th>Apuntat el</th><th>Qui</th><th>Persones</th><th>Estat</th><th>Accions</th></tr>
            </thead>
            <tbody>
                <?php if (!$inscrits): ?>
                <tr><td colspan="5" style="color:var(--text-muted)">Encara no s'ha apuntat ningú.</td></tr>
                <?php endif; ?>
                <?php foreach ($inscrits as $i): ?>
                <?php $wa = $cata['fecha'] ? catasWhatsapp($i['telefono'], catasTextWhatsappData($cata, $i)) : catasWhatsapp($i['telefono'], 'Hola ' . $i['nombre'] . ', somos El Celler Nou.'); ?>
                <tr<?php echo $i['estado'] === 'baja' ? ' style="opacity:0.5"' : ''; ?>>
                    <td><?php echo date('d/m/Y H:i', strtotime($i['created_at'])); ?></td>
                    <td>
                        <strong><?php echo e($i['nombre']); ?></strong><br>
                        <a href="mailto:<?php echo e($i['email']); ?>"><?php echo e($i['email']); ?></a>
                        <?php if ($i['telefono']): ?><br><?php echo e($i['telefono']); ?><?php endif; ?>
                    </td>
                    <td><?php echo (int) $i['personas']; ?></td>
                    <td>
                        <?php echo $estatsIns[$i['estado']]; ?>
                        <?php if ($i['avisada_at']): ?><br><small style="color:var(--text-muted)">correu <?php echo date('d/m H:i', strtotime($i['avisada_at'])); ?></small><?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="margin:0;display:flex;gap:0.25rem;flex-wrap:wrap;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="accio" value="estat_inscripcio">
                            <input type="hidden" name="id_inscripcion" value="<?php echo (int) $i['id']; ?>">
                            <?php if ($wa): ?><a href="<?php echo e($wa); ?>" target="_blank" class="btn btn-sm btn-secondary">WhatsApp</a><?php endif; ?>
                            <?php if ($i['estado'] !== 'confirmada' && $i['estado'] !== 'baja'): ?>
                            <button type="submit" name="estado" value="confirmada" class="btn btn-sm btn-primary">Confirma</button>
                            <?php endif; ?>
                            <?php if ($i['estado'] !== 'baja'): ?>
                            <button type="submit" name="estado" value="baja" class="btn btn-sm btn-secondary">Baixa</button>
                            <?php else: ?>
                            <button type="submit" name="estado" value="nueva" class="btn btn-sm btn-secondary">Recuperar</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
