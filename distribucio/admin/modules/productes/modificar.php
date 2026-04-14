<?php
/**
 * El Celler Nou - Mòdul Productes - Modificar
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Editar Producte';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/productes/?msg=error'));
    exit;
}

// Obtener producto con precio de tienda 1
$sql = "SELECT p.*, pf.foto as foto, COALESCE(ppt.pvp, 0) as precio_venta
        FROM productos p
        LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
        LEFT JOIN precios_producto_tienda ppt ON ppt.idProducto = p.id AND ppt.idTienda = 1
        WHERE p.id = $id";
$result = mysqli_query($bd, $sql);
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/productes/?msg=error'));
    exit;
}

$producto = mysqli_fetch_assoc($result);
$errors = [];

// Cargar textos del producto
$productoTextos = ['descripcion' => '', 'nota_cata' => '', 'texto_libre' => ''];
$resultTextos = mysqli_query($bd, "SELECT tipo, contenido FROM producto_textos WHERE idProducto = $id");
if ($resultTextos) {
    while ($rowTexto = mysqli_fetch_assoc($resultTextos)) {
        $productoTextos[$rowTexto['tipo']] = $rowTexto['contenido'];
    }
}

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
        $producto['precio_venta'] = (float)str_replace(',', '.', $_POST['precio_venta'] ?? 0);
        $producto['precio_venta_dis'] = (float)str_replace(',', '.', $_POST['precio_venta_dis'] ?? 0);
        $producto['iva'] = (float)($_POST['iva'] ?? 21);
        $producto['activo'] = isset($_POST['activo']) ? 1 : 0;
        $producto['uvas_es'] = trim($_POST['uvas_es'] ?? '');

        // Mantener textos del POST para repoblar en caso de error
        $productoTextos['descripcion'] = trim($_POST['texto_descripcion'] ?? '');
        $productoTextos['nota_cata'] = trim($_POST['texto_nota_cata'] ?? '');
        $productoTextos['texto_libre'] = trim($_POST['texto_texto_libre'] ?? '');

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
            $result = mysqli_query($bd, "SELECT id FROM productos WHERE codigo = '$codigoSafe' AND id != $id");
            if (mysqli_num_rows($result) > 0) {
                $errors[] = 'Ja existeix un altre producte amb aquest codi.';
            }
        }

        // Actualizar si no hay errores
        if (empty($errors)) {
            $sql = "UPDATE productos SET
                    codigo = ?,
                    nombre = ?,
                    descripcion = ?,
                    idSector = ?,
                    idTipo = ?,
                    idProcedencia = ?,
                    unidades = ?,
                    precio_medio = ?,
                    precio_venta_dis = ?,
                    iva = ?,
                    activo = ?,
                    uvas_es = ?
                    WHERE id = ?";

            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'sssiiidddiisi',
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
                $producto['uvas_es'],
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                // Actualizar o insertar precio de venta en precios_producto_tienda (tienda 1)
                $checkPrecio = mysqli_query($bd, "SELECT id FROM precios_producto_tienda WHERE idProducto = $id AND idTienda = 1");
                if (mysqli_num_rows($checkPrecio) > 0) {
                    $sqlPrecio = "UPDATE precios_producto_tienda SET pvp = ? WHERE idProducto = ? AND idTienda = 1";
                    $stmtPrecio = mysqli_prepare($bd, $sqlPrecio);
                    mysqli_stmt_bind_param($stmtPrecio, 'di', $producto['precio_venta'], $id);
                } else {
                    $sqlPrecio = "INSERT INTO precios_producto_tienda (idTienda, idProducto, pvp) VALUES (1, ?, ?)";
                    $stmtPrecio = mysqli_prepare($bd, $sqlPrecio);
                    mysqli_stmt_bind_param($stmtPrecio, 'id', $id, $producto['precio_venta']);
                }
                mysqli_stmt_execute($stmtPrecio);

                // Guardar textos del producto
                $tiposTexto = ['descripcion', 'nota_cata', 'texto_libre'];
                foreach ($tiposTexto as $tipo) {
                    $contenido = trim($_POST['texto_' . $tipo] ?? '');
                    if ($contenido !== '') {
                        $contenidoSafe = mysqli_real_escape_string($bd, $contenido);
                        $tipoSafe = mysqli_real_escape_string($bd, $tipo);
                        mysqli_query($bd, "DELETE FROM producto_textos WHERE idProducto = $id AND tipo = '$tipoSafe'");
                        mysqli_query($bd, "INSERT INTO producto_textos (idProducto, tipo, contenido) VALUES ($id, '$tipoSafe', '$contenidoSafe')");
                    } else {
                        $tipoSafe = mysqli_real_escape_string($bd, $tipo);
                        mysqli_query($bd, "DELETE FROM producto_textos WHERE idProducto = $id AND tipo = '$tipoSafe'");
                    }
                }

                logActivity('producto_modificar', "Producte modificat: {$producto['nombre']} (ID: $id)");
                header('Location: ' . getAdminUrl('modules/productes/?msg=updated'));
                exit;
            } else {
                $errors[] = 'Error al actualitzar el producte: ' . mysqli_error($bd);
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

$pageTitle = 'Editar: ' . $producto['nombre'];

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo getAdminUrl('modules/productes/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar al llistat
    </a>
    <span class="badge badge-info">ID: <?php echo $id; ?></span>
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
            <h3 class="card-title">Textos del Producte</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="texto_descripcion">Descripció</label>
                    <textarea id="texto_descripcion" name="texto_descripcion" class="form-input" rows="4"
                              placeholder="Descripció del producte..."><?php echo e($productoTextos['descripcion']); ?></textarea>
                </div>

                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="texto_nota_cata">Nota de cata</label>
                    <textarea id="texto_nota_cata" name="texto_nota_cata" class="form-input" rows="4"
                              placeholder="Notes de cata del producte..."><?php echo e($productoTextos['nota_cata']); ?></textarea>
                </div>

                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="texto_texto_libre">Text lliure</label>
                    <textarea id="texto_texto_libre" name="texto_texto_libre" class="form-input" rows="6"
                              placeholder="Text addicional..."><?php echo e($productoTextos['texto_libre']); ?></textarea>
                    <small style="color: var(--gray-500); font-size: 0.75rem;">El text comença directament sense títol a la fitxa pública.</small>
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
                    <label class="form-label" for="unidades">Unitats en estoc</label>
                    <input type="number" id="unidades" name="unidades" class="form-input" min="0"
                           value="<?php echo $producto['unidades']; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="precio_medio">Preu de compra (&euro;)</label>
                    <input type="text" id="precio_medio" name="precio_medio" class="form-input"
                           value="<?php echo number_format($producto['precio_medio'], 2, ',', ''); ?>"
                           placeholder="0,00" oninput="calcularMarges()">
                </div>

                <div class="form-group">
                    <label class="form-label" for="precio_venta">PVP - Preu Venda Públic (&euro;)</label>
                    <input type="text" id="precio_venta" name="precio_venta" class="form-input"
                           value="<?php echo number_format($producto['precio_venta'], 2, ',', ''); ?>"
                           placeholder="0,00" oninput="calcularMarges()">
                    <small id="margePvp" class="marge-indicator"></small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="precio_venta_dis">PVD - Preu Venda Distribuïdor (&euro;)</label>
                    <input type="text" id="precio_venta_dis" name="precio_venta_dis" class="form-input"
                           value="<?php echo number_format($producto['precio_venta_dis'], 2, ',', ''); ?>"
                           placeholder="0,00" oninput="calcularMarges()">
                    <small id="margePvd" class="marge-indicator"></small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="iva">IVA (%)</label>
                    <select id="iva" name="iva" class="form-input" onchange="calcularMarges()">
                        <option value="21" <?php echo $producto['iva'] == 21 ? 'selected' : ''; ?>>21%</option>
                        <option value="10" <?php echo $producto['iva'] == 10 ? 'selected' : ''; ?>>10%</option>
                        <option value="4" <?php echo $producto['iva'] == 4 ? 'selected' : ''; ?>>4%</option>
                        <option value="0" <?php echo $producto['iva'] == 0 ? 'selected' : ''; ?>>0%</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="activo" <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                        <span>Producte actiu</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Informació del Sistema</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Data d'alta</strong>
                    <p style="margin: 0.25rem 0 0;"><?php echo date('d/m/Y H:i', strtotime($producto['alta'])); ?></p>
                </div>
                <div>
                    <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">ID Producte</strong>
                    <p style="margin: 0.25rem 0 0;"><?php echo $producto['id']; ?></p>
                </div>
                <?php if (!empty($producto['foto'])): ?>
                <div>
                    <strong style="color: var(--gray-500); font-size: 0.75rem; text-transform: uppercase;">Foto</strong>
                    <p style="margin: 0.25rem 0 0;"><?php echo e($producto['foto']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="button" class="btn btn-danger" onclick="confirmarEliminar()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
        </button>
        <div style="margin-left: auto; display: flex; gap: 1rem;">
            <a href="<?php echo getAdminUrl('modules/productes/'); ?>" class="btn btn-secondary">Cancel·lar</a>
            <button type="submit" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar Canvis
            </button>
        </div>
    </div>
</form>

<!-- Modal de confirmación de eliminación -->
<div id="deleteModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="tancarModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar eliminació</h3>
        </div>
        <div class="modal-body">
            <p>Estàs segur que vols eliminar el producte <strong><?php echo e($producto['nombre']); ?></strong>?</p>
            <p style="color: var(--gray-500); font-size: 0.875rem;">Aquesta acció no es pot desfer.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="tancarModal()">Cancel·lar</button>
            <form method="POST" action="<?php echo getAdminUrl('modules/productes/eliminar.php'); ?>" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

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
    align-items: center;
    gap: 1rem;
    padding-top: 1rem;
}
.marge-indicator {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    min-height: 1.2em;
}
.alert-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    padding: 1rem;
    border-radius: 8px;
}
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
}
.modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 400px;
    margin: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}
.modal-header h3 {
    margin: 0;
    font-size: 1.125rem;
}
.modal-body {
    padding: 1.5rem;
}
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}
</style>

<script>
function confirmarEliminar() {
    document.getElementById('deleteModal').style.display = 'flex';
}

function tancarModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        tancarModal();
    }
});

function parseNum(val) {
    return parseFloat(String(val).replace(',', '.')) || 0;
}

function formatMarge(el, marge) {
    if (isNaN(marge) || !isFinite(marge)) {
        el.textContent = '';
        return;
    }
    let color = '#059669';
    if (marge < 10) color = '#dc2626';
    else if (marge < 20) color = '#d97706';
    el.innerHTML = `<span style="color:${color}; font-weight:600;">Marge: ${marge.toFixed(1)}%</span>`;
}

function calcularMarges() {
    const cost = parseNum(document.getElementById('precio_medio').value);
    const pvp = parseNum(document.getElementById('precio_venta').value);
    const pvd = parseNum(document.getElementById('precio_venta_dis').value);
    const iva = parseFloat(document.getElementById('iva').value) || 0;

    const elPvp = document.getElementById('margePvp');
    const elPvd = document.getElementById('margePvd');

    if (pvp > 0 && cost > 0) {
        const pvpSinIva = pvp / (1 + iva / 100);
        const mgPvp = ((pvpSinIva - cost) / pvpSinIva) * 100;
        formatMarge(elPvp, mgPvp);
    } else {
        elPvp.textContent = '';
    }

    if (pvd > 0 && cost > 0) {
        const mgPvd = ((pvd - cost) / pvd) * 100;
        formatMarge(elPvd, mgPvd);
    } else {
        elPvd.textContent = '';
    }
}

// Calcular al carregar
calcularMarges();
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
