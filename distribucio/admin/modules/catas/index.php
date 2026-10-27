<?php
/**
 * Catas - Llistat
 *
 * Catas obertes de /catas (amb els apuntats i el mínim), peticions de cata
 * privada i gent que vol que l'avisin de les properes.
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once __DIR__ . '/helpers.php';

$pageTitle = 'Catas';

// Canvi d'estat d'una petició privada (atesa / pendent).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $idIns = (int) ($_POST['id_inscripcion'] ?? 0);
    $nou = ($_POST['estado'] ?? '') === 'confirmada' ? 'confirmada' : 'nueva';
    mysqli_query($bd, "UPDATE catas_inscripciones SET estado = '$nou' WHERE id = $idIns AND tipo = 'privada'");
    header('Location: ' . getAdminUrl('modules/catas/') . '#privades');
    exit;
}

$catas = [];
$r = mysqli_query($bd, "SELECT c.*,
            (SELECT COALESCE(SUM(i.personas), 0) FROM catas_inscripciones i
              WHERE i.id_cata = c.id AND i.tipo = 'publica' AND i.estado <> 'baja') AS apuntats
        FROM catas c
        ORDER BY FIELD(c.estado, 'abierta', 'confirmada', 'hecha', 'cancelada'), c.fecha IS NULL, c.fecha, c.orden, c.id");
while ($r && $row = mysqli_fetch_assoc($r)) $catas[] = $row;

$peticions = ['privada' => [], 'aviso' => []];
$r = mysqli_query($bd, "SELECT * FROM catas_inscripciones WHERE tipo IN ('privada', 'aviso') ORDER BY created_at DESC LIMIT 200");
while ($r && $row = mysqli_fetch_assoc($r)) $peticions[$row['tipo']][] = $row;

$estats = [
    'abierta'    => ['Oberta', 'warning'],
    'confirmada' => ['Amb data', 'success'],
    'hecha'      => ['Feta', 'secondary'],
    'cancelada'  => ['Cancel·lada', 'danger'],
];

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="content-header">
    <div>
        <h2>Catas</h2>
        <p>La gent s'apunta a les catas obertes de <a href="<?php echo getBaseUrl(); ?>/catas" target="_blank">/catas</a>. Quan s'arriba al mínim, poseu la data i avisa els apuntats.</p>
    </div>
    <div style="display:flex;gap:0.5rem;">
        <a href="<?php echo getAdminUrl('modules/catas/cata.php'); ?>" class="btn btn-primary">+ Nova cata</a>
        <a href="<?php echo getBaseUrl(); ?>/catas" class="btn btn-secondary" target="_blank">Veure al web ↗</a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header"><h3>Catas obertes</h3></div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cata</th>
                    <th>Estat</th>
                    <th>Apuntats / mínim</th>
                    <th>Data</th>
                    <th>Preu</th>
                    <th style="width:110px">Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$catas): ?>
                <tr><td colspan="6" style="color:var(--text-muted)">Encara no hi ha cap cata.</td></tr>
                <?php endif; ?>
                <?php foreach ($catas as $c): ?>
                <?php $arriba = (int) $c['apuntats'] >= (int) $c['minimo']; ?>
                <tr>
                    <td>
                        <strong><?php echo e($c['titulo']); ?></strong>
                        <?php if (!$c['publicada']): ?><span class="badge badge-warning">Esborrany</span><?php endif; ?>
                    </td>
                    <td><span class="badge badge-<?php echo $estats[$c['estado']][1]; ?>"><?php echo $estats[$c['estado']][0]; ?></span></td>
                    <td>
                        <strong><?php echo (int) $c['apuntats']; ?></strong> / <?php echo (int) $c['minimo']; ?>
                        <?php if ($c['estado'] === 'abierta' && $arriba): ?>
                        <span class="badge badge-success">Ja hi ha mínim: poseu data</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $c['fecha'] ? date('d/m/Y H:i', strtotime($c['fecha'])) : '-'; ?></td>
                    <td><?php echo $c['precio'] !== null ? number_format((float) $c['precio'], 2, ',', '.') . ' €' : '-'; ?></td>
                    <td><a href="<?php echo getAdminUrl('modules/catas/cata.php?id=' . (int) $c['id']); ?>" class="btn btn-sm btn-secondary">Obrir</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;" id="privades">
    <div class="card-header"><h3>Peticions de cata privada (<?php echo count($peticions['privada']); ?>)</h3></div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Qui</th>
                    <th>Persones</th>
                    <th>Per a</th>
                    <th>Comentari</th>
                    <th>Estat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$peticions['privada']): ?>
                <tr><td colspan="6" style="color:var(--text-muted)">Cap petició.</td></tr>
                <?php endif; ?>
                <?php foreach ($peticions['privada'] as $p): ?>
                <?php $wa = catasWhatsapp($p['telefono'], 'Hola ' . $p['nombre'] . ', som El Celler Nou. Us escrivim per la cata privada que ens vau demanar.'); ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></td>
                    <td>
                        <strong><?php echo e($p['nombre']); ?></strong><br>
                        <a href="mailto:<?php echo e($p['email']); ?>"><?php echo e($p['email']); ?></a>
                        <?php if ($p['telefono']): ?><br><?php echo e($p['telefono']); ?><?php endif; ?>
                        <?php if ($wa): ?> · <a href="<?php echo e($wa); ?>" target="_blank">WhatsApp</a><?php endif; ?>
                    </td>
                    <td><?php echo (int) $p['personas']; ?></td>
                    <td><?php echo e(CATAS_MOTIVOS[$p['motivo']] ?? '-'); ?></td>
                    <td style="max-width:320px;white-space:pre-line;"><?php echo e($p['comentario'] ?? ''); ?></td>
                    <td>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="id_inscripcion" value="<?php echo (int) $p['id']; ?>">
                            <?php if ($p['estado'] === 'confirmada'): ?>
                            <span class="badge badge-success">Atesa</span>
                            <button type="submit" name="estado" value="nueva" class="btn btn-sm btn-secondary">Desfer</button>
                            <?php else: ?>
                            <button type="submit" name="estado" value="confirmada" class="btn btn-sm btn-primary">Marcar atesa</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Volen que els avisin de les properes catas (<?php echo count($peticions['aviso']); ?>)</h3>
        <?php if ($peticions['aviso']): ?>
        <a href="mailto:?bcc=<?php echo e(implode(',', array_unique(array_column($peticions['aviso'], 'email')))); ?>&subject=<?php echo rawurlencode('Noves catas a El Celler Nou'); ?>" class="btn btn-sm btn-secondary">Escriure a tots (en còpia oculta)</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr><th>Data</th><th>Qui</th><th>Què li agradaria</th></tr>
            </thead>
            <tbody>
                <?php if (!$peticions['aviso']): ?>
                <tr><td colspan="3" style="color:var(--text-muted)">Ningú encara.</td></tr>
                <?php endif; ?>
                <?php foreach ($peticions['aviso'] as $p): ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></td>
                    <td><strong><?php echo e($p['nombre']); ?></strong> · <a href="mailto:<?php echo e($p['email']); ?>"><?php echo e($p['email']); ?></a></td>
                    <td><?php echo e($p['comentario'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
