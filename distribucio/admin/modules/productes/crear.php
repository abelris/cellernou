<?php
/**
 * El Celler Nou - Mòdul Productes - Crear
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Nou Producte';

$errors = [];
$producto = [
    'codigo' => '',
    'nombre' => '',
    'descripcion' => '',
    'idSector' => '',
    'idTipo' => '',
    'idProcedencia' => '',
    'unidades' => 0,
    'precio_medio' => 0,
    'precio_venta_dis' => 0,
    'precio_venta' => 0,
    'iva' => 21,
    'activo' => 1,
    'uvas_es' => ''
];

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        // Recoger datos
        $producto['codigo'] = trim($_POST['codigo'] ?? '');
        $producto['nombre'] = trim($_POST['nombre'] ?? '');
        $producto['descripcion'] = trim($_POST['descripcion'] ?? '');
        $producto['idSector'] = (int)($_POST['idSector'] ?? 0);
        $producto['idTipo'] = (int)($_POST['idTipo'] ?? 0);
        $producto['idProcedencia'] = (int)($_POST['idProcedencia'] ?? 0);
        $producto['unidades'] = (int)($_POST['unidades'] ?? 0);
        $producto['precio_medio'] = (float)str_replace(',', '.', $_POST['precio_medio'] ?? 0);
        $producto['precio_venta_dis'] = (float)str_replace(',', '.', $_POST['precio_venta_dis'] ?? 0);
        $producto['precio_venta'] = (float)str_replace(',', '.', $_POST['precio_venta'] ?? 0);
        $producto['iva'] = (float)($_POST['iva'] ?? 21);
        $producto['activo'] = isset($_POST['activo']) ? 1 : 0;
        $producto['uvas_es'] = trim($_POST['uvas_es'] ?? '');

        // Validaciones
        if (empty($producto['nombre'])) {
            $errors[] = 'El nom del producte és obligatori.';
        }
        if ($producto['idSector'] <= 0) {
            $errors[] = 'Has de seleccionar un sector.';
        }
        if ($producto['idTipo'] <= 0) {
            $errors[] = 'Has de seleccionar un tipus.';
        }

        // Verificar código único si se proporciona
        if (!empty($producto['codigo'])) {
            $codigoSafe = mysqli_real_escape_string($bd, $producto['codigo']);
            $result = mysqli_query($bd, "SELECT id FROM productos WHERE codigo = '$codigoSafe'");
            if (mysqli_num_rows($result) > 0) {
                $errors[] = 'Ja existeix un producte amb aquest codi.';
            }
        }

        // Insertar si no hay errores
        if (empty($errors)) {
            $sql = "INSERT INTO productos (codigo, nombre, descripcion, idSector, idTipo, idProcedencia,
                    unidades, precio_medio, precio_venta_dis, iva, activo, uvas_es, alta)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'sssiiidddiss',
                $producto['codigo'],
                $producto['nombre'],
                $producto['descripcion'],
                $producto['idSector'],
                $producto['idTipo'],
                $producto['idProcedencia'],
                $producto['unidades'],
                $producto['precio_medio'],
                $producto['precio_venta_dis'],
                $producto['iva'],
                $producto['activo'],
                $producto['uvas_es']
            );

            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($bd);

                // Guardar precio de venta en precios_producto_tienda (tienda 1)
                $sqlPrecio = "INSERT INTO precios_producto_tienda (idTienda, idProducto, pvp) VALUES (1, ?, ?)";
                $stmtPrecio = mysqli_prepare($bd, $sqlPrecio);
                mysqli_stmt_bind_param($stmtPrecio, 'id', $newId, $producto['precio_venta']);
                mysqli_stmt_execute($stmtPrecio);

                logActivity('producto_crear', "Producte creat: {$producto['nombre']} (ID: $newId)");
                header('Location: ' . getAdminUrl('modules/productes/?msg=created'));
                exit;
            } else {
                $errors[] = 'Error al crear el producte: ' . mysqli_error($bd);
            }
        }
    }
}

// Obtener sectores
$sectores = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM sectores ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sectores[] = $row;
    }
}

// Obtener tipos
$tipos = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM tipo_producto ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tipos[] = $row;
    }
}

// Obtener procedencias
$procedencias = [];
$result = mysqli_query($bd, "SELECT id, nombre_es FROM procedencia ORDER BY nombre_es");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $procedencias[] = $row;
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/productes/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar al llistat
    </a>
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
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Informació Bàsica</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="codigo">Codi / Codi de barres</label>
                    <input type="text" id="codigo" name="codigo" class="form-input"
                           value="<?php echo e($producto['codigo']); ?>" placeholder="Ex: 8410000000000">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label" for="nombre">Nom del producte *</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required
                           value="<?php echo e($producto['nombre']); ?>" placeholder="Ex: Vi Negre Criança">
                </div>

                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="descripcion">Descripció</label>
                    <textarea id="descripcion" name="descripcion" class="form-input" rows="3"
                              placeholder="Descripció del producte..."><?php echo e($producto['descripcion']); ?></textarea>
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
                    <label class="form-label" for="idTipo">Tipus *</label>
                    <select id="idTipo" name="idTipo" class="form-input" required>
                        <option value="">Selecciona tipus...</option>
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

                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="uvas_es">Varietats / Raïms</label>
                    <input type="text" id="uvas_es" name="uvas_es" class="form-input"
                           value="<?php echo e($producto['uvas_es']); ?>" placeholder="Ex: Tempranillo, Garnacha">
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Preus i Estoc</h3>
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
                    <label class="form-label" for="unidades">Unitats en estoc</label>
                    <input type="number" id="unidades" name="unidades" class="form-input" min="0"
                           value="<?php echo $producto['unidades']; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="activo" <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                        <span>Producte actiu</span>
                    </label>
                </div>
            </div>

            <div class="precios-panel">
                <div class="precio-col">
                    <label class="form-label" for="precio_medio">Preu de compra <small>(sense IVA)</small></label>
                    <input type="text" id="precio_medio" name="precio_medio" class="form-input precio-field"
                           value="<?php echo number_format($producto['precio_medio'], 2, ',', ''); ?>"
                           placeholder="0,00">
                </div>

                <div class="precio-arrow">
                    <div class="margin-box" id="marginDis">
                        <span class="margin-label">Marge Distribució</span>
                        <span class="margin-value" id="marginDisValue">0,00 &euro;</span>
                        <span class="margin-pct" id="marginDisPct">0%</span>
                    </div>
                </div>

                <div class="precio-col">
                    <label class="form-label" for="precio_venta_dis">Preu distribució <small>(sense IVA)</small></label>
                    <input type="text" id="precio_venta_dis" name="precio_venta_dis" class="form-input precio-field"
                           value="<?php echo number_format($producto['precio_venta_dis'], 2, ',', ''); ?>"
                           placeholder="0,00">
                </div>

                <div class="precio-arrow">
                    <div class="margin-box" id="marginPvp">
                        <span class="margin-label">Marge PVP</span>
                        <span class="margin-value" id="marginPvpValue">0,00 &euro;</span>
                        <span class="margin-pct" id="marginPvpPct">0%</span>
                    </div>
                </div>

                <div class="precio-col">
                    <label class="form-label" for="precio_venta">PVP <small>(amb IVA)</small></label>
                    <input type="text" id="precio_venta" name="precio_venta" class="form-input precio-field precio-pvp"
                           value="<?php echo number_format($producto['precio_venta'], 2, ',', ''); ?>"
                           placeholder="0,00">
                    <span class="pvp-sin-iva" id="pvpSinIva"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?php echo getAdminUrl('modules/productes/'); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Crear Producte
        </button>
    </div>
</form>

<script>
function parsePrice(val) {
    return parseFloat(String(val).replace(',', '.')) || 0;
}

function calcMargins() {
    const compra = parsePrice(document.getElementById('precio_medio').value);
    const dis = parsePrice(document.getElementById('precio_venta_dis').value);
    const pvp = parsePrice(document.getElementById('precio_venta').value);
    const iva = parseFloat(document.getElementById('iva').value) || 0;
    const pvpSinIva = pvp / (1 + iva / 100);

    // PVP sin IVA indicator
    document.getElementById('pvpSinIva').textContent = pvp > 0 ? 'Sense IVA: ' + pvpSinIva.toFixed(2).replace('.', ',') + ' \u20ac' : '';

    // Margen comercial distribución: (dis - compra) / dis
    const mDis = dis - compra;
    const mDisPct = dis > 0 ? (mDis / dis * 100) : 0;
    document.getElementById('marginDisValue').textContent = mDis.toFixed(2).replace('.', ',') + ' \u20ac';
    document.getElementById('marginDisPct').textContent = mDisPct.toFixed(1).replace('.', ',') + '%';
    setMarginColor('marginDis', mDis);

    // Margen comercial PVP: (pvpSinIva - compra) / pvpSinIva
    const mPvp = pvpSinIva - compra;
    const mPvpPct = pvpSinIva > 0 ? (mPvp / pvpSinIva * 100) : 0;
    document.getElementById('marginPvpValue').textContent = mPvp.toFixed(2).replace('.', ',') + ' \u20ac';
    document.getElementById('marginPvpPct').textContent = mPvpPct.toFixed(1).replace('.', ',') + '%';
    setMarginColor('marginPvp', mPvp);
}

function setMarginColor(id, value) {
    const el = document.getElementById(id);
    el.classList.remove('margin-positive', 'margin-negative', 'margin-zero');
    if (value > 0) el.classList.add('margin-positive');
    else if (value < 0) el.classList.add('margin-negative');
    else el.classList.add('margin-zero');
}

document.querySelectorAll('.precio-field, #iva').forEach(el => {
    el.addEventListener('input', calcMargins);
    el.addEventListener('change', calcMargins);
});

calcMargins();
</script>

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
textarea.form-input {
    resize: vertical;
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
    justify-content: flex-end;
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

/* Panel de precios y márgenes */
.precios-panel {
    display: flex;
    align-items: flex-start;
    gap: 0;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
}
.precio-col {
    flex: 1;
    min-width: 0;
}
.precio-col .form-label small {
    display: block;
    font-weight: 400;
    color: var(--gray-400);
    font-size: 0.75rem;
}
.precio-field {
    font-size: 1.1rem !important;
    font-weight: 600;
    text-align: right;
}
.precio-pvp {
    border-color: var(--primary) !important;
    background: #fdf6f7;
}
.pvp-sin-iva {
    display: block;
    font-size: 0.75rem;
    color: var(--gray-500);
    text-align: right;
    margin-top: 0.25rem;
}
.precio-arrow {
    display: flex;
    align-items: center;
    padding: 1.5rem 0.75rem 0;
}
.margin-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.4rem 0.75rem;
    border-radius: 6px;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    min-width: 100px;
    text-align: center;
}
.margin-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    font-weight: 600;
    color: var(--gray-500);
    letter-spacing: 0.03em;
}
.margin-value {
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1.3;
}
.margin-pct {
    font-size: 0.8rem;
    font-weight: 600;
}
.margin-positive { background: #f0fdf4; border-color: #bbf7d0; }
.margin-positive .margin-value, .margin-positive .margin-pct { color: #16a34a; }
.margin-negative { background: #fef2f2; border-color: #fecaca; }
.margin-negative .margin-value, .margin-negative .margin-pct { color: #dc2626; }
.margin-zero { background: var(--gray-50); border-color: var(--gray-200); }
.margin-zero .margin-value, .margin-zero .margin-pct { color: var(--gray-400); }

@media (max-width: 768px) {
    .precios-panel {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }
    .precio-arrow {
        padding: 0.25rem 0;
        justify-content: center;
    }
}
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
