<?php
/**
 * El Celler Nou - Mòdul Compres - Crear
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Nova Compra';

$errors = [];
$compra = [
    'idProveedor' => '',
    'referencia' => '',
    'fecha' => date('Y-m-d'),
    'oferta' => '',
    'total_base' => 0,
    'total_iva' => 0,
    'total' => 0
];

// Obtener proveedores
$proveedores = [];
$result = mysqli_query($bd, "SELECT id, nombre FROM proveedores ORDER BY nombre");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $proveedores[] = $row;
    }
}

// Obtener productos activos con PVP de tienda 1
$productos = [];
$result = mysqli_query($bd, "SELECT p.id, p.nombre, p.codigo, p.precio_medio, p.iva, p.precio_venta_dis, COALESCE(pt.pvp, 0) as pvp
        FROM productos p
        LEFT JOIN precios_producto_tienda pt ON pt.idProducto = p.id AND pt.idTienda = 1
        WHERE p.activo = 1
        ORDER BY p.nombre");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        $compra['idProveedor'] = (int)($_POST['idProveedor'] ?? 0);
        $compra['referencia'] = trim($_POST['referencia'] ?? '');
        $compra['fecha'] = trim($_POST['fecha'] ?? date('Y-m-d'));
        $compra['oferta'] = trim($_POST['oferta'] ?? '');
        $compra['total_base'] = (float)str_replace(',', '.', $_POST['total_base'] ?? 0);
        $compra['total_iva'] = (float)str_replace(',', '.', $_POST['total_iva'] ?? 0);
        $compra['total'] = (float)str_replace(',', '.', $_POST['total'] ?? 0);

        // Líneas de compra
        $lineas = [];
        if (isset($_POST['linea_producto']) && is_array($_POST['linea_producto'])) {
            foreach ($_POST['linea_producto'] as $i => $idProducto) {
                $idProducto = (int)$idProducto;
                $cantidad = (float)str_replace(',', '.', $_POST['linea_cantidad'][$i] ?? 0);
                $precioUnidad = (float)str_replace(',', '.', $_POST['linea_precio'][$i] ?? 0);
                $descuento = (float)str_replace(',', '.', $_POST['linea_descuento'][$i] ?? 0);
                $iva = (float)($_POST['linea_iva'][$i] ?? 21);

                if ($idProducto > 0 && $cantidad > 0) {
                    $precioConDesc = $precioUnidad * (1 - $descuento / 100);
                    $lineas[] = [
                        'idProducto' => $idProducto,
                        'cantidad' => $cantidad,
                        'precio_unidad' => $precioConDesc,
                        'precio_unidad_sin_desc' => $precioUnidad,
                        'descuento' => $descuento,
                        'iva' => $iva
                    ];
                }
            }
        }

        // Validaciones
        if ($compra['idProveedor'] <= 0) {
            $errors[] = 'Has de seleccionar un proveïdor.';
        }
        if (empty($lineas)) {
            $errors[] = 'Has d\'afegir almenys una línia de compra.';
        }

        if (empty($errors)) {
            // Calcular totales si no se han proporcionado
            if ($compra['total'] <= 0) {
                $compra['total_base'] = 0;
                $compra['total_iva'] = 0;
                foreach ($lineas as $l) {
                    $lineaBase = $l['cantidad'] * $l['precio_unidad'];
                    $compra['total_base'] += $lineaBase;
                    $compra['total_iva'] += $lineaBase * ($l['iva'] / 100);
                }
                $compra['total'] = $compra['total_base'] + $compra['total_iva'];
            }

            $fecha = !empty($compra['fecha']) ? $compra['fecha'] : date('Y-m-d');

            $sql = "INSERT INTO compras (idProveedor, referencia, fecha, oferta, total_base, total_iva, total, idEstado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
            $stmt = mysqli_prepare($bd, $sql);
            mysqli_stmt_bind_param($stmt, 'isssddd',
                $compra['idProveedor'],
                $compra['referencia'],
                $fecha,
                $compra['oferta'],
                $compra['total_base'],
                $compra['total_iva'],
                $compra['total']
            );

            if (mysqli_stmt_execute($stmt)) {
                $idCompra = mysqli_insert_id($bd);

                // Insertar líneas
                foreach ($lineas as $l) {
                    $sqlLinea = "INSERT INTO linea_compra (idCompra, idProducto, idProveedor, cantidad, precio_unidad, descuento, precio_unidad_sin_desc, iva)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtLinea = mysqli_prepare($bd, $sqlLinea);
                    mysqli_stmt_bind_param($stmtLinea, 'iiiddddd',
                        $idCompra,
                        $l['idProducto'],
                        $compra['idProveedor'],
                        $l['cantidad'],
                        $l['precio_unidad'],
                        $l['descuento'],
                        $l['precio_unidad_sin_desc'],
                        $l['iva']
                    );
                    mysqli_stmt_execute($stmtLinea);

                    // Insertar en stock_producto
                    for ($s = 0; $s < $l['cantidad']; $s++) {
                        $sqlStock = "INSERT INTO stock_producto (idTienda, idCompra, idProducto, fecha_entrada, precio_compra) VALUES (1, ?, ?, NOW(), ?)";
                        $stmtStock = mysqli_prepare($bd, $sqlStock);
                        mysqli_stmt_bind_param($stmtStock, 'iid', $idCompra, $l['idProducto'], $l['precio_unidad_sin_desc']);
                        mysqli_stmt_execute($stmtStock);
                    }
                }

                // Actualitzar PVP i PVD si s'han modificat
                if (isset($_POST['linea_producto']) && is_array($_POST['linea_producto'])) {
                    foreach ($_POST['linea_producto'] as $i => $idProd) {
                        $idProd = (int)$idProd;
                        if ($idProd <= 0) continue;

                        // PVP → precios_producto_tienda
                        $newPvp = (float)str_replace(',', '.', $_POST['linea_pvp'][$i] ?? 0);
                        if ($newPvp > 0) {
                            $chk = mysqli_query($bd, "SELECT id FROM precios_producto_tienda WHERE idProducto = $idProd AND idTienda = 1");
                            if ($chk && mysqli_num_rows($chk) > 0) {
                                mysqli_query($bd, "UPDATE precios_producto_tienda SET pvp = $newPvp WHERE idProducto = $idProd AND idTienda = 1");
                            } else {
                                mysqli_query($bd, "INSERT INTO precios_producto_tienda (idTienda, idProducto, pvp) VALUES (1, $idProd, $newPvp)");
                            }
                        }

                        // PVD → productos.precio_venta_dis
                        $newPvd = (float)str_replace(',', '.', $_POST['linea_pvd'][$i] ?? 0);
                        if ($newPvd > 0) {
                            mysqli_query($bd, "UPDATE productos SET precio_venta_dis = $newPvd WHERE id = $idProd");
                        }
                    }
                }

                logActivity('compra_crear', "Compra creada (ID: $idCompra) - Proveïdor: {$compra['idProveedor']}");
                header('Location: ' . getAdminUrl('modules/compres/?msg=created'));
                exit;
            } else {
                $errors[] = 'Error al crear la compra: ' . mysqli_error($bd);
            }
        }
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/compres/'); ?>" class="btn btn-secondary">
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

<form method="POST" action="" id="compraForm">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="total_base" id="totalBaseInput" value="0">
    <input type="hidden" name="total_iva" id="totalIvaInput" value="0">
    <input type="hidden" name="total" id="totalInput" value="0">

    <!-- Datos de la compra -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Dades de la Compra</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="idProveedor">Proveïdor *</label>
                    <select id="idProveedor" name="idProveedor" class="form-input" required>
                        <option value="">Selecciona proveïdor...</option>
                        <?php foreach ($proveedores as $prov): ?>
                        <option value="<?php echo $prov['id']; ?>" <?php echo $compra['idProveedor'] == $prov['id'] ? 'selected' : ''; ?>>
                            <?php echo e($prov['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="referencia">Referència Proveïdor</label>
                    <input type="text" id="referencia" name="referencia" class="form-input"
                           value="<?php echo e($compra['referencia']); ?>" placeholder="Ref. albarà o comanda">
                </div>

                <div class="form-group">
                    <label class="form-label" for="fecha">Data</label>
                    <input type="date" id="fecha" name="fecha" class="form-input"
                           value="<?php echo e($compra['fecha']); ?>">
                </div>

                <div class="form-group" style="grid-column: span 3;">
                    <label class="form-label" for="oferta">Notes / Oferta</label>
                    <input type="text" id="oferta" name="oferta" class="form-input"
                           value="<?php echo e($compra['oferta']); ?>" placeholder="Notes opcionals sobre la compra...">
                </div>
            </div>
        </div>
    </div>

    <!-- Líneas de compra -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Línies de Compra</h3>
            <button type="button" class="btn btn-sm btn-primary" onclick="afegirLinia()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Afegir Línia
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table" id="liniesTable">
                    <thead>
                        <tr>
                            <th style="min-width: 250px;">Producte</th>
                            <th style="width: 90px; text-align: center;">Quantitat</th>
                            <th style="width: 110px; text-align: right;">Preu s/IVA</th>
                            <th style="width: 80px; text-align: center;">Desc. %</th>
                            <th style="width: 70px; text-align: center;">IVA %</th>
                            <th style="width: 110px; text-align: right;">Total</th>
                            <th style="width: 90px; text-align: right;">PVP</th>
                            <th style="width: 90px; text-align: right;">PVD</th>
                            <th style="width: 100px; text-align: center;">Marge</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="liniesBody">
                        <!-- Las líneas se añaden dinámicamente -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: 500;">Base imposable:</td>
                            <td style="text-align: right;" id="totalBase">0,00 &euro;</td>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align: right; font-weight: 500;">IVA:</td>
                            <td style="text-align: right;" id="totalIva">0,00 &euro;</td>
                            <td colspan="4"></td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="5" style="text-align: right; font-weight: 600;">TOTAL:</td>
                            <td style="text-align: right; font-size: 1.125rem; font-weight: 700; color: var(--primary);" id="totalCompra">0,00 &euro;</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?php echo getAdminUrl('modules/compres/'); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Crear Compra
        </button>
    </div>
</form>

<style>
.form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.form-group { margin-bottom: 0; }
.form-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--gray-700); margin-bottom: 0.5rem; }
.form-input { width: 100%; padding: 0.625rem 0.875rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; transition: all 0.2s; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; }
.total-row { background: var(--gray-50); }
.total-row td { padding: 1rem !important; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; }
.linea-input { width: 100%; padding: 0.375rem 0.5rem; font-size: 0.8125rem; border: 1px solid var(--gray-300); border-radius: 4px; box-sizing: border-box; }
.linea-input:focus { outline: none; border-color: var(--primary); }
.btn-remove { background: none; border: none; color: var(--danger, #dc2626); cursor: pointer; padding: 0.25rem; }
.btn-remove:hover { color: #991b1b; }
.linea-marge { font-size: 0.8125rem; white-space: nowrap; }
.linea-pvp, .linea-pvd { font-size: 0.8125rem; }
@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-group[style*="grid-column"] { grid-column: span 1 !important; }
}
</style>

<script>
// Datos de productos para el selector
const productes = <?php echo json_encode($productos); ?>;
let lineaCount = 0;

function afegirLinia() {
    const tbody = document.getElementById('liniesBody');
    const idx = lineaCount++;

    let optionsHtml = '<option value="">Selecciona...</option>';
    productes.forEach(p => {
        optionsHtml += `<option value="${p.id}" data-preu="${p.precio_medio}" data-iva="${p.iva}" data-pvp="${p.pvp}" data-pvd="${p.precio_venta_dis}">${p.nombre}${p.codigo ? ' (' + p.codigo + ')' : ''}</option>`;
    });

    const tr = document.createElement('tr');
    tr.id = 'linia-' + idx;
    tr.innerHTML = `
        <td>
            <select name="linea_producto[]" class="linea-input linea-producte" onchange="onProducteChange(this, ${idx})" required>
                ${optionsHtml}
            </select>
        </td>
        <td><input type="number" name="linea_cantidad[]" class="linea-input" style="text-align: center;" value="1" min="1" step="1" onchange="calcularTotals()" onkeyup="calcularTotals()"></td>
        <td><input type="text" name="linea_precio[]" class="linea-input" style="text-align: right;" value="0,00" onchange="calcularTotals()" onkeyup="calcularTotals()"></td>
        <td><input type="text" name="linea_descuento[]" class="linea-input" style="text-align: center;" value="0" onchange="calcularTotals()" onkeyup="calcularTotals()"></td>
        <td><input type="number" name="linea_iva[]" class="linea-input" style="text-align: center;" value="21" step="1" onchange="calcularTotals()" onkeyup="calcularTotals()"></td>
        <td style="text-align: right; font-weight: 500;" class="linea-total">0,00 &euro;</td>
        <td><input type="text" name="linea_pvp[]" class="linea-input linea-pvp" style="text-align: right;" value="" placeholder="-" onchange="calcularTotals()" onkeyup="calcularTotals()"></td>
        <td><input type="text" name="linea_pvd[]" class="linea-input linea-pvd" style="text-align: right;" value="" placeholder="-" onchange="calcularTotals()" onkeyup="calcularTotals()"></td>
        <td style="text-align: center;" class="linea-marge">-</td>
        <td style="text-align: center;">
            <button type="button" class="btn-remove" onclick="eliminarLinia(${idx})" title="Eliminar línia">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function onProducteChange(select, idx) {
    const option = select.options[select.selectedIndex];
    if (option.value) {
        const preu = option.dataset.preu || '0';
        const iva = option.dataset.iva || '21';
        const pvp = parseFloat(option.dataset.pvp || 0);
        const pvd = parseFloat(option.dataset.pvd || 0);
        const row = document.getElementById('linia-' + idx);
        const inputs = row.querySelectorAll('input');
        inputs[1].value = parseFloat(preu).toFixed(2).replace('.', ','); // precio
        inputs[3].value = parseInt(iva); // iva
        // PVP i PVD
        row.querySelector('.linea-pvp').value = pvp > 0 ? pvp.toFixed(2).replace('.', ',') : '';
        row.querySelector('.linea-pvd').value = pvd > 0 ? pvd.toFixed(2).replace('.', ',') : '';
    }
    calcularTotals();
}

function eliminarLinia(idx) {
    const row = document.getElementById('linia-' + idx);
    if (row) {
        row.remove();
        calcularTotals();
    }
}

function parseNum(val) {
    return parseFloat(String(val).replace(',', '.')) || 0;
}

function formatNum(val) {
    return val.toFixed(2).replace('.', ',');
}

function calcularTotals() {
    let totalBase = 0;
    let totalIva = 0;

    document.querySelectorAll('#liniesBody tr').forEach(row => {
        const inputs = row.querySelectorAll('input');
        if (inputs.length < 4) return;

        const cantidad = parseNum(inputs[0].value);
        const precio = parseNum(inputs[1].value);
        const descuento = parseNum(inputs[2].value);
        const iva = parseNum(inputs[3].value);

        const precioDesc = precio * (1 - descuento / 100);
        const lineaBase = cantidad * precioDesc;
        const lineaIva = lineaBase * (iva / 100);

        totalBase += lineaBase;
        totalIva += lineaIva;

        row.querySelector('.linea-total').innerHTML = formatNum(lineaBase) + ' &euro;';

        // Calcular marges comercials (PVP i PVD)
        const pvp = parseNum(row.querySelector('.linea-pvp').value);
        const pvd = parseNum(row.querySelector('.linea-pvd').value);
        const margeCell = row.querySelector('.linea-marge');

        if (precioDesc > 0 && (pvp > 0 || pvd > 0)) {
            let html = '';
            if (pvp > 0) {
                const pvpSinIva = pvp / (1 + iva / 100);
                const mgPvp = ((pvpSinIva - precioDesc) / pvpSinIva) * 100;
                const colorPvp = mgPvp < 10 ? '#dc2626' : mgPvp < 20 ? '#d97706' : '#059669';
                html += `<div style="color:${colorPvp}">PVP: ${mgPvp.toFixed(1)}%</div>`;
            }
            if (pvd > 0) {
                const mgPvd = ((pvd - precioDesc) / pvd) * 100;
                const colorPvd = mgPvd < 10 ? '#dc2626' : mgPvd < 20 ? '#d97706' : '#059669';
                html += `<div style="color:${colorPvd}">PVD: ${mgPvd.toFixed(1)}%</div>`;
            }
            margeCell.innerHTML = html;
        } else {
            margeCell.textContent = '-';
        }
    });

    document.getElementById('totalBase').innerHTML = formatNum(totalBase) + ' &euro;';
    document.getElementById('totalIva').innerHTML = formatNum(totalIva) + ' &euro;';
    document.getElementById('totalCompra').innerHTML = formatNum(totalBase + totalIva) + ' &euro;';

    document.getElementById('totalBaseInput').value = totalBase.toFixed(2);
    document.getElementById('totalIvaInput').value = totalIva.toFixed(2);
    document.getElementById('totalInput').value = (totalBase + totalIva).toFixed(2);
}

// Añadir una línea inicial
afegirLinia();
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
