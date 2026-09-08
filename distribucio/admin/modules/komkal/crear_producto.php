<?php
/**
 * El Celler Nou - Mòdul Komkal - Crear Producte des de Komkal
 */
$pageTitle = 'Komkal - Crear Producte';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/config.php';
requireAuth();

$allowedTables = ['vinos', 'licores'];
$taula = (isset($_GET['taula']) && in_array($_GET['taula'], $allowedTables)) ? $_GET['taula'] : 'vinos';
$komkalId = (int)($_GET['komkal_id'] ?? 0);

if ($komkalId <= 0) {
    header('Location: ' . getAdminUrl('modules/komkal/matching.php?taula=' . $taula));
    exit;
}

$taulaDb = 'komkal_' . $taula;

// Get komkal entry
$r = mysqli_query($bd, "SELECT * FROM $taulaDb WHERE id = $komkalId");
if (!$r || mysqli_num_rows($r) === 0) {
    header('Location: ' . getAdminUrl('modules/komkal/matching.php?taula=' . $taula . '&msg=not_found'));
    exit;
}
$komkal = mysqli_fetch_assoc($r);

// Default sector and margin based on table
$defaultSector = ($taula === 'vinos') ? 8 : 2;
$margen = ($taula === 'vinos') ? 0.25 : 0.20; // 25% vinos, 20% licores
$iva = 21;

$precioCompra = (float)($komkal['precio_sin_iva'] ?? 0);
$precioVentaDis = round($precioCompra * (1 + $margen), 2);
$pvpConIva = round($precioVentaDis * (1 + $iva / 100), 2);

$errors = [];
$producto = [
    'codigo' => $komkal['codigo_komkal'] ?? '',
    'nombre' => $komkal['nombre'] ?? '',
    'descripcion' => '',
    'idSector' => $defaultSector,
    'idTipo' => 0,
    'idProcedencia' => 0,
    'precio_medio' => $precioCompra,
    'precio_venta_dis' => $precioVentaDis,
    'precio_venta' => $pvpConIva,
    'iva' => $iva,
    'activo' => 1,
];

// Process POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        $producto['codigo'] = trim($_POST['codigo'] ?? '');
        $producto['nombre'] = trim($_POST['nombre'] ?? '');
        $producto['descripcion'] = trim($_POST['descripcion'] ?? '');
        $producto['idSector'] = (int)($_POST['idSector'] ?? 0);
        $producto['idTipo'] = (int)($_POST['idTipo'] ?? 0);
        $producto['idProcedencia'] = (int)($_POST['idProcedencia'] ?? 0);
        $producto['precio_medio'] = (float)str_replace(',', '.', $_POST['precio_medio'] ?? 0);
        $producto['precio_venta_dis'] = (float)str_replace(',', '.', $_POST['precio_venta_dis'] ?? 0);
        $producto['precio_venta'] = (float)str_replace(',', '.', $_POST['precio_venta'] ?? 0);
        $producto['iva'] = (float)($_POST['iva'] ?? 21);
        $producto['activo'] = isset($_POST['activo']) ? 1 : 0;

        $komkalIdPost = (int)($_POST['komkal_id'] ?? 0);
        $taulaPost = ($_POST['taula'] ?? '');
        if (!in_array($taulaPost, $allowedTables)) $taulaPost = $taula;

        // Validations
        if (empty($producto['nombre'])) {
            $errors[] = 'El nom del producte és obligatori.';
        }
        if ($producto['idSector'] <= 0) {
            $errors[] = 'Has de seleccionar un sector.';
        }

        // Verify unique code if provided
        if (!empty($producto['codigo'])) {
            $codigoSafe = mysqli_real_escape_string($bd, $producto['codigo']);
            $result = mysqli_query($bd, "SELECT id FROM productos WHERE codigo = '$codigoSafe'");
            if ($result && mysqli_num_rows($result) > 0) {
                $errors[] = 'Ja existeix un producte amb aquest codi.';
            }
        }

        if (empty($errors)) {
            $sql = "INSERT INTO productos (codigo, nombre, descripcion, idSector, idTipo, idProcedencia,
                    unidades, precio_medio, precio_venta_dis, iva, activo, alta)
                    VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, NOW())";

            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'sssiiidddi',
                $producto['codigo'],
                $producto['nombre'],
                $producto['descripcion'],
                $producto['idSector'],
                $producto['idTipo'],
                $producto['idProcedencia'],
                $producto['precio_medio'],
                $producto['precio_venta_dis'],
                $producto['iva'],
                $producto['activo']
            );

            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($bd);

                // Save PVP in precios_producto_tienda (tienda 1) if provided
                if ($producto['precio_venta'] > 0) {
                    $stmtPvp = mysqli_prepare($bd, "INSERT INTO precios_producto_tienda (idTienda, idProducto, pvp) VALUES (1, ?, ?)");
                    mysqli_stmt_bind_param($stmtPvp, 'id', $newId, $producto['precio_venta']);
                    mysqli_stmt_execute($stmtPvp);
                }

                // Auto-link back to komkal entry + save permanent match
                if ($komkalIdPost > 0) {
                    $taulaDbPost = 'komkal_' . $taulaPost;
                    $stmtLink = mysqli_prepare($bd, "UPDATE $taulaDbPost SET id_producto_match = ?, match_tipo = 'nuevo' WHERE id = ?");
                    mysqli_stmt_bind_param($stmtLink, 'ii', $newId, $komkalIdPost);
                    mysqli_stmt_execute($stmtLink);

                    // Save to komkal_matches (permanent)
                    $codigoK = $komkal['codigo_komkal'];
                    $stmtMatch = mysqli_prepare($bd, "INSERT INTO komkal_matches (codigo_komkal, tipo_catalogo, id_producto, match_tipo) VALUES (?, ?, ?, 'nuevo') ON DUPLICATE KEY UPDATE id_producto = VALUES(id_producto), match_tipo = 'nuevo'");
                    mysqli_stmt_bind_param($stmtMatch, 'ssi', $codigoK, $taulaPost, $newId);
                    mysqli_stmt_execute($stmtMatch);
                }

                logActivity('komkal_crear_producto', "Producte creat des de Komkal: {$producto['nombre']} (ID: $newId, komkal_$taulaPost ID: $komkalIdPost)");

                header('Location: ' . getAdminUrl('modules/komkal/matching.php?taula=' . $taulaPost . '&msg=created'));
                exit;
            } else {
                $errors[] = 'Error al crear el producte: ' . mysqli_error($bd);
            }
        }
    }
}

// Get sectors
$sectores = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM sectores ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sectores[] = $row;
    }
}

// Get types
$tipos = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM tipo_producto ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tipos[] = $row;
    }
}

// Get procedencias
$procedencias = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM procedencia ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $procedencias[] = $row;
    }
}

$csrfToken = generateCsrfToken();

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div style="margin-bottom: 1.5rem; display:flex; gap:1rem; align-items:center;">
    <a href="<?php echo getAdminUrl('modules/komkal/matching.php?taula=' . $taula); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar al Matching
    </a>
</div>

<!-- Komkal info card -->
<div class="card" style="margin-bottom:1.5rem; background:#fffbeb; border:1px solid #fde68a;">
    <div class="card-body" style="padding:1rem;">
        <h4 style="margin:0 0 0.5rem 0; font-size:0.875rem; text-transform:uppercase; color:var(--gray-500);">Dades Komkal</h4>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:0.75rem; font-size:0.875rem;">
            <div><strong>Codi:</strong> <?php echo e($komkal['codigo_komkal'] ?: '-'); ?></div>
            <div><strong>Nom:</strong> <?php echo e($komkal['nombre']); ?></div>
            <div><strong>Preu s/IVA:</strong> <?php echo $komkal['precio_sin_iva'] !== null ? number_format((float)$komkal['precio_sin_iva'], 2, ',', '.') . ' &euro;' : '-'; ?></div>
            <div><strong>Disponible:</strong> <?php echo $komkal['disponible'] ? 'Si' : 'No'; ?></div>
            <?php if (!empty($komkal['url'])): ?>
            <div><strong>URL:</strong> <a href="<?php echo e($komkal['url']); ?>" target="_blank" style="word-break:break-all;">Veure</a></div>
            <?php endif; ?>
        </div>
    </div>
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
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="komkal_id" value="<?php echo $komkalId; ?>">
    <input type="hidden" name="taula" value="<?php echo e($taula); ?>">

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Informació del Producte</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="codigo">Codi</label>
                    <input type="text" id="codigo" name="codigo" class="form-input"
                           value="<?php echo e($producto['codigo']); ?>">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label" for="nombre">Nom del producte *</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required
                           value="<?php echo e($producto['nombre']); ?>">
                </div>

                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="descripcion">Descripció</label>
                    <textarea id="descripcion" name="descripcion" class="form-input" rows="2"><?php echo e($producto['descripcion']); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="idSector">Sector *</label>
                    <select id="idSector" name="idSector" class="form-input" required>
                        <option value="">Selecciona sector...</option>
                        <?php foreach ($sectores as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo $producto['idSector'] == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo e($s['nombre_es']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="idTipo">Tipus</label>
                    <select id="idTipo" name="idTipo" class="form-input">
                        <option value="0">Selecciona tipus...</option>
                        <?php foreach ($tipos as $t): ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo $producto['idTipo'] == $t['id'] ? 'selected' : ''; ?>>
                            <?php echo e($t['nombre_es']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="idProcedencia">Procedència / D.O.</label>
                    <select id="idProcedencia" name="idProcedencia" class="form-input">
                        <option value="0">Sense especificar</option>
                        <?php foreach ($procedencias as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo $producto['idProcedencia'] == $p['id'] ? 'selected' : ''; ?>>
                            <?php echo e($p['nombre_es']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Preus</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="iva">IVA (%)</label>
                    <select id="iva" name="iva" class="form-input">
                        <option value="21" <?php echo $producto['iva'] == 21 ? 'selected' : ''; ?>>21%</option>
                        <option value="10" <?php echo $producto['iva'] == 10 ? 'selected' : ''; ?>>10%</option>
                        <option value="4" <?php echo $producto['iva'] == 4 ? 'selected' : ''; ?>>4%</option>
                        <option value="0" <?php echo $producto['iva'] == 0 ? 'selected' : ''; ?>>0%</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; padding:0.625rem 0;">
                        <input type="checkbox" name="activo" <?php echo $producto['activo'] ? 'checked' : ''; ?> style="width:18px;height:18px;">
                        <span style="font-size:0.875rem;">Producte actiu</span>
                    </label>
                </div>
                <div></div>

                <div class="form-group">
                    <label class="form-label" for="precio_medio">Preu de compra (sense IVA)</label>
                    <input type="text" id="precio_medio" name="precio_medio" class="form-input"
                           value="<?php echo number_format((float)$producto['precio_medio'], 2, ',', ''); ?>"
                           style="font-weight:600; text-align:right;">
                </div>

                <div class="form-group">
                    <label class="form-label" for="precio_venta_dis">Preu distribució (sense IVA)</label>
                    <input type="text" id="precio_venta_dis" name="precio_venta_dis" class="form-input"
                           value="<?php echo number_format((float)$producto['precio_venta_dis'], 2, ',', ''); ?>"
                           style="text-align:right;">
                </div>

                <div class="form-group">
                    <label class="form-label" for="precio_venta">PVP (amb IVA)</label>
                    <input type="text" id="precio_venta" name="precio_venta" class="form-input"
                           value="<?php echo number_format((float)$producto['precio_venta'], 2, ',', ''); ?>"
                           style="text-align:right;">
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:1rem; padding-top:1rem;">
        <a href="<?php echo getAdminUrl('modules/komkal/matching.php?taula=' . $taula); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Crear i Vincular
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
.form-group { margin-bottom: 0; }
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
textarea.form-input { resize: vertical; }
.alert-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    padding: 1rem;
    border-radius: 8px;
}
</style>

<script>
// Auto-calcular PVP quan canvia preu de compra o IVA
(function() {
    var margen = <?php echo $margen; ?>;
    var precioInput = document.getElementById('precio_medio');
    var ventaDisInput = document.getElementById('precio_venta_dis');
    var pvpInput = document.getElementById('precio_venta');
    var ivaSelect = document.getElementById('iva');

    function recalc() {
        var coste = parseFloat(precioInput.value.replace(',', '.')) || 0;
        var iva = parseFloat(ivaSelect.value) || 21;
        var ventaDis = Math.round(coste * (1 + margen) * 100) / 100;
        var pvp = Math.round(ventaDis * (1 + iva / 100) * 100) / 100;
        ventaDisInput.value = ventaDis.toFixed(2).replace('.', ',');
        pvpInput.value = pvp.toFixed(2).replace('.', ',');
    }

    precioInput.addEventListener('input', recalc);
    ivaSelect.addEventListener('change', recalc);
})();
</script>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
