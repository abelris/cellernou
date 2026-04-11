<?php
/**
 * El Celler Nou - Mòdul Vendes - Crear
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$pageTitle = 'Nova Venda';
$errors = [];

// Obtener clientes
$clientes = [];
$result = mysqli_query($bd, "SELECT id, nombre, profesional, precios_especiales, recarrec_equiv FROM clientes ORDER BY nombre");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $clientes[] = $row;
    }
}

// Obtener productos activos con precios
$productos = [];
$sql = "SELECT p.id, p.codigo, p.nombre, p.iva, p.precio_medio, p.precio_venta_dis,
        COALESCE(pt.pvp, 0) as pvp
        FROM productos p
        LEFT JOIN precios_producto_tienda pt ON pt.idProducto = p.id AND pt.idTienda = 1
        WHERE p.activo = 1
        ORDER BY p.nombre";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[] = $row;
    }
}

// Obtener precios especiales por cliente (precios_cliente)
$preciosCliente = [];
$rsPc = mysqli_query($bd, "SELECT idCliente, idProducto, precio FROM precios_cliente");
if ($rsPc) {
    while ($row = mysqli_fetch_assoc($rsPc)) {
        $preciosCliente[$row['idCliente'] . '_' . $row['idProducto']] = $row['precio'];
    }
}

// Obtener precios especiales proveedor
$preciosEspeciales = [];
$rsPe = mysqli_query($bd, "SELECT idProducto, precio FROM precios_especiales_proveidor");
if ($rsPe) {
    while ($row = mysqli_fetch_assoc($rsPe)) {
        $preciosEspeciales[$row['idProducto']] = $row['precio'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat.';
    } else {
        $idCliente = (int)($_POST['idCliente'] ?? 0);
        $comentarios = trim($_POST['comentarios'] ?? '');
        $fecha = $_POST['fecha'] ?? date('Y-m-d');

        $lineas = $_POST['lineas'] ?? [];
        $lineasValidas = [];

        foreach ($lineas as $linea) {
            $idProducto = (int)($linea['idProducto'] ?? 0);
            $cantidad = (float)($linea['cantidad'] ?? 0);
            $precioUnidad = (float)($linea['precio_unidad'] ?? 0);
            $descuento = (float)($linea['descuento'] ?? 0);

            if ($idProducto > 0 && $cantidad > 0) {
                $lineasValidas[] = [
                    'idProducto' => $idProducto,
                    'cantidad' => $cantidad,
                    'precio_unidad' => $precioUnidad,
                    'descuento' => $descuento
                ];
            }
        }

        if ($idCliente <= 0) {
            $errors[] = 'Has de seleccionar un client.';
        }

        if (empty($lineasValidas)) {
            $errors[] = 'Has d\'afegir almenys un producte.';
        }

        if (empty($errors)) {
            $comentariosEsc = mysqli_real_escape_string($bd, $comentarios);
            $fechaEsc = mysqli_real_escape_string($bd, $fecha);

            $sql = "INSERT INTO ventas (idCliente, fecha, descuento, precio_desc, coste, precio_coste, idEstado, comentarios, albaran)
                    VALUES ($idCliente, '$fechaEsc', '', 0, '', 0, 0, '$comentariosEsc', '')";

            if (mysqli_query($bd, $sql)) {
                $ventaId = mysqli_insert_id($bd);

                foreach ($lineasValidas as $linea) {
                    // Obtener datos producto
                    $prodResult = mysqli_query($bd, "SELECT precio_medio, iva FROM productos WHERE id = {$linea['idProducto']}");
                    $prodData = mysqli_fetch_assoc($prodResult);
                    $iva = $prodData['iva'] ?? 21;

                    // Último precio de compra
                    $precioCompra = $prodData['precio_medio'] ?? 0;
                    $rsPc2 = mysqli_query($bd, "SELECT precio_unidad FROM linea_compra WHERE idProducto = {$linea['idProducto']} ORDER BY fecha DESC LIMIT 1");
                    if ($rsPc2 && $pc2 = mysqli_fetch_assoc($rsPc2)) {
                        $precioCompra = $pc2['precio_unidad'];
                    }

                    // Calcular precio sin descuento
                    $precioSinDesc = $linea['precio_unidad'];
                    if ($linea['descuento'] > 0) {
                        $precioSinDesc = $linea['precio_unidad'] / (1 - ($linea['descuento'] / 100));
                    }

                    $sqlLinea = "INSERT INTO linea_venta (idVenta, idProducto, idCliente, fecha, cantidad, precio_unidad, descuento, precio_unidad_sin_desc, precio_compra, iva, albaran)
                                 VALUES ($ventaId, {$linea['idProducto']}, $idCliente, '$fechaEsc', {$linea['cantidad']}, {$linea['precio_unidad']}, {$linea['descuento']}, $precioSinDesc, $precioCompra, $iva, '')";
                    mysqli_query($bd, $sqlLinea);

                    // Marcar stock como vendido (más antiguos primero)
                    $cantStock = intval(ceil($linea['cantidad']));
                    $rsStock = mysqli_query($bd, "SELECT id FROM stock_producto WHERE vendido=0 AND idProducto={$linea['idProducto']} ORDER BY fecha_entrada ASC LIMIT $cantStock");
                    if ($rsStock) {
                        while ($rowStock = mysqli_fetch_assoc($rsStock)) {
                            mysqli_query($bd, "UPDATE stock_producto SET vendido=1, fecha_vendido=NOW() WHERE id={$rowStock['id']}");
                        }
                    }
                }

                logActivity('venda_crear', "Venda creada: #$ventaId per client $idCliente");
                header('Location: ' . getAdminUrl('modules/vendes/?msg=created'));
                exit;
            } else {
                $errors[] = 'Error al crear la venda: ' . mysqli_error($bd);
            }
        }
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/vendes/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom: 1rem;">
    <?php foreach ($errors as $e): ?><p style="margin: 0;"><?php echo e($e); ?></p><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" id="vendaForm">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <div class="venda-header">
        <div class="header-field" style="width: 280px;">
            <label class="header-label">Client *</label>
            <select id="idCliente" name="idCliente" class="form-input" required onchange="onClientChange()">
                <option value="0">-- Seleccionar client --</option>
                <?php foreach ($clientes as $c): ?>
                <option value="<?php echo $c['id']; ?>"
                    data-profesional="<?php echo $c['profesional']; ?>"
                    data-especiales="<?php echo $c['precios_especiales'] ?? 0; ?>">
                    <?php echo e($c['nombre']); ?>
                    <?php echo $c['profesional'] ? '(Pro)' : ''; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="header-field" style="width: 160px;">
            <label class="header-label">Data</label>
            <input type="date" name="fecha" class="form-input" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="header-field" style="flex: 1;">
            <label class="header-label">Comentaris</label>
            <input type="text" id="comentarios" name="comentarios" class="form-input" placeholder="Opcional">
        </div>
    </div>

    <div id="clientInfo" class="client-info" style="display: none; margin-bottom: 1rem;"></div>

    <div class="card" style="margin-bottom: 1.5rem; overflow: visible;">
        <div class="card-header">
            <h3 class="card-title">Productes</h3>
            <button type="button" id="addLineBtn" class="btn btn-sm btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Afegir Línia
            </button>
        </div>
        <div class="card-body" style="padding: 0; overflow: visible;">
            <div class="table-container" style="overflow: visible;">
                <table class="table" id="lineasTable">
                    <thead>
                        <tr>
                            <th>Producte</th>
                            <th style="width: 90px;">Quantitat</th>
                            <th style="width: 110px;">Preu s/IVA</th>
                            <th style="width: 110px;">Preu c/IVA</th>
                            <th style="width: 80px;">Dto %</th>
                            <th style="width: 80px;">IVA %</th>
                            <th style="width: 80px;">Marge</th>
                            <th style="width: 110px; text-align: right;">Total</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lineasBody"></tbody>
                    <tfoot>
                        <tr id="rowSubtotal" style="display: none;">
                            <td colspan="7" style="text-align: right; font-weight: 500; color: var(--gray-500);">Subtotal (sense dto.):</td>
                            <td style="text-align: right; color: var(--gray-500);" id="subtotalSinDesc">0,00 &euro;</td>
                            <td></td>
                        </tr>
                        <tr id="rowDescuento" style="display: none;">
                            <td colspan="7" style="text-align: right; font-weight: 500; color: #dc2626;">Descompte:</td>
                            <td style="text-align: right; color: #dc2626; font-weight: 600;" id="totalDescuento">-0,00 &euro;</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="7" style="text-align: right; font-weight: 600;">TOTAL:</td>
                            <td style="text-align: right; font-size: 1.25rem; font-weight: 700; color: var(--primary);" id="totalGeneral">0,00 &euro;</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 1rem;">
        <a href="<?php echo getAdminUrl('modules/vendes/'); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Crear Venda
        </button>
    </div>
</form>

<script>
const productos = <?php echo json_encode($productos); ?>;
const preciosCliente = <?php echo json_encode((object)$preciosCliente); ?>;
const preciosEspeciales = <?php echo json_encode((object)$preciosEspeciales); ?>;

let lineaCount = 0;
let currentClientId = 0;
let currentClientProfesional = 0;
let currentClientEspeciales = 0;

function onClientChange() {
    const sel = document.getElementById('idCliente');
    const opt = sel.options[sel.selectedIndex];
    currentClientId = parseInt(sel.value);
    currentClientProfesional = parseInt(opt.dataset.profesional || 0);
    currentClientEspeciales = parseInt(opt.dataset.especiales || 0);

    const info = document.getElementById('clientInfo');
    if (currentClientId > 0) {
        let badges = '';
        if (currentClientProfesional) badges += '<span class="badge badge-info">Professional</span> ';
        if (currentClientEspeciales) badges += '<span class="badge badge-special">Preus Especials</span> ';
        info.innerHTML = badges || '<span class="badge badge-default">Particular</span>';
        info.style.display = 'block';
    } else {
        info.style.display = 'none';
    }

    // Recalcular precios de las líneas existentes
    recalcPrices();
}

function getProductPrice(product) {
    // 1. Si cliente tiene precios_especiales → precios_especiales_proveidor
    if (currentClientEspeciales && preciosEspeciales[product.id]) {
        return parseFloat(preciosEspeciales[product.id]);
    }
    // 2. precios_cliente (precio específico del cliente)
    const key = currentClientId + '_' + product.id;
    if (preciosCliente[key]) {
        return parseFloat(preciosCliente[key]);
    }
    // 3. Si es profesional → precio_venta_dis
    if (currentClientProfesional && product.precio_venta_dis > 0) {
        return parseFloat(product.precio_venta_dis);
    }
    // 4. Particular → pvp / (1 + iva/100)
    if (product.pvp > 0) {
        return parseFloat((product.pvp / (1 + product.iva / 100)).toFixed(3));
    }
    return 0;
}

function recalcPrices() {
    document.querySelectorAll('#lineasBody tr').forEach(row => {
        const hiddenInput = row.querySelector('.product-id');
        if (hiddenInput && hiddenInput.value) {
            const prod = productos.find(p => p.id == hiddenInput.value);
            if (prod) {
                const precio = getProductPrice(prod);
                row.querySelector('.precio-input').value = precio.toFixed(2);
            }
        }
    });
    calculateTotal();
}

function createProductSearch(index) {
    return `
        <div class="product-search-container">
            <input type="text" class="form-input product-search" placeholder="Escriu per cercar..." autocomplete="off" data-index="${index}">
            <input type="hidden" name="lineas[${index}][idProducto]" class="product-id">
            <div class="search-results"></div>
        </div>
    `;
}

function addLinea() {
    const tbody = document.getElementById('lineasBody');
    const tr = document.createElement('tr');
    tr.id = `linea-${lineaCount}`;
    tr.innerHTML = `
        <td>${createProductSearch(lineaCount)}</td>
        <td><input type="number" name="lineas[${lineaCount}][cantidad]" class="form-input cantidad-input" value="1" min="0.01" step="any" onchange="calculateTotal()"></td>
        <td><input type="number" name="lineas[${lineaCount}][precio_unidad]" class="form-input precio-input" value="0" min="0" step="any" onchange="calculateTotal(); recalcMargin(this.closest('tr'))" oninput="calculateTotal(); recalcMargin(this.closest('tr'))"></td>
        <td class="linea-preu-iva" style="text-align: right; color: var(--gray-600); font-size: 0.875rem;">-</td>
        <td><input type="number" name="lineas[${lineaCount}][descuento]" class="form-input descuento-input" value="0" min="0" max="100" step="1" onchange="calculateTotal()"></td>
        <td class="linea-iva" style="text-align: right; color: var(--gray-500);">-</td>
        <td class="linea-marge" style="text-align: right; font-size: 0.8rem;">-</td>
        <td class="linea-total" style="text-align: right; font-weight: 600;">0,00 &euro;</td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeLinea(${lineaCount})">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button></td>
    `;
    tbody.appendChild(tr);
    setupProductSearch(tr.querySelector('.product-search'));
    lineaCount++;
}

function selectProduct(item, input, hiddenInput, resultsDiv) {
    const id = item.dataset.id;
    const nombre = item.dataset.nombre;
    const codigo = item.dataset.codigo;
    const iva = item.dataset.iva;
    const precioMedio = parseFloat(item.dataset.preciomedio || 0);

    const prod = productos.find(p => p.id == id);
    const precio = prod ? getProductPrice(prod) : 0;

    input.value = codigo ? `${codigo} - ${nombre}` : nombre;
    hiddenInput.value = id;

    const row = input.closest('tr');
    row.querySelector('.precio-input').value = precio.toFixed(2);
    row.querySelector('.linea-iva').textContent = iva + '%';
    row.dataset.iva = iva;
    row.dataset.precioMedio = precioMedio;

    recalcMargin(row);

    resultsDiv.style.display = 'none';
    calculateTotal();

    addLinea();
    const newRow = document.getElementById(`linea-${lineaCount - 1}`);
    if (newRow) newRow.querySelector('.product-search').focus();
}

function recalcMargin(row) {
    const precio = parseFloat(row.querySelector('.precio-input').value) || 0;
    const precioMedio = parseFloat(row.dataset.precioMedio) || 0;
    const margeCell = row.querySelector('.linea-marge');
    if (precio > 0 && precioMedio > 0) {
        const marge = ((precio - precioMedio) / precio * 100).toFixed(1);
        margeCell.textContent = marge + '%';
        if (marge < 10) margeCell.style.color = '#dc2626';
        else if (marge < 15) margeCell.style.color = '#d97706';
        else margeCell.style.color = '#059669';
    } else {
        margeCell.textContent = '-';
        margeCell.style.color = '';
    }
}

function setupProductSearch(input) {
    const container = input.closest('.product-search-container');
    const resultsDiv = container.querySelector('.search-results');
    const hiddenInput = container.querySelector('.product-id');

    input.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        if (query.length < 1) {
            resultsDiv.innerHTML = '';
            resultsDiv.style.display = 'none';
            return;
        }

        const matches = productos.filter(p =>
            p.nombre.toLowerCase().includes(query) ||
            (p.codigo && p.codigo.toLowerCase().includes(query))
        ).slice(0, 10);

        if (matches.length === 0) {
            resultsDiv.innerHTML = '<div class="search-result-item no-results">No s\'han trobat productes</div>';
        } else {
            resultsDiv.innerHTML = matches.map(p => {
                const precio = getProductPrice(p);
                return `<div class="search-result-item" data-id="${p.id}" data-nombre="${p.nombre}" data-codigo="${p.codigo || ''}" data-iva="${p.iva}" data-preciomedio="${p.precio_medio}">
                    <span class="result-codigo">${p.codigo || '-'}</span>
                    <span class="result-nombre">${p.nombre}</span>
                    <span class="result-precio">${precio.toFixed(2)} &euro;</span>
                </div>`;
            }).join('');
        }
        resultsDiv.style.display = 'block';
    });

    resultsDiv.addEventListener('click', function(e) {
        const item = e.target.closest('.search-result-item');
        if (item && !item.classList.contains('no-results')) {
            selectProduct(item, input, hiddenInput, resultsDiv);
        }
    });

    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) resultsDiv.style.display = 'none';
    });

    input.addEventListener('keydown', function(e) {
        const items = resultsDiv.querySelectorAll('.search-result-item:not(.no-results)');
        const active = resultsDiv.querySelector('.search-result-item.active');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!active && items.length > 0) items[0].classList.add('active');
            else if (active && active.nextElementSibling) { active.classList.remove('active'); active.nextElementSibling.classList.add('active'); }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (active && active.previousElementSibling) { active.classList.remove('active'); active.previousElementSibling.classList.add('active'); }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (active) selectProduct(active, input, hiddenInput, resultsDiv);
        } else if (e.key === 'Escape') {
            resultsDiv.style.display = 'none';
        }
    });
}

function removeLinea(index) {
    const row = document.getElementById(`linea-${index}`);
    if (row) { row.remove(); calculateTotal(); }
}

function calculateTotal() {
    let total = 0;
    let subtotalSinDesc = 0;
    let hayDescuento = false;

    document.querySelectorAll('#lineasBody tr').forEach(row => {
        const cantidad = parseFloat(row.querySelector('.cantidad-input')?.value || 0);
        const precio = parseFloat(row.querySelector('.precio-input')?.value || 0);
        const descuento = parseFloat(row.querySelector('.descuento-input')?.value || 0);
        const iva = parseFloat(row.dataset.iva || 0);

        // Mostrar precio unitario con IVA
        const preuIvaCell = row.querySelector('.linea-preu-iva');
        if (preuIvaCell) {
            if (precio > 0 && iva > 0) {
                const precioConIva = precio * (1 + iva / 100);
                preuIvaCell.textContent = precioConIva.toFixed(2).replace('.', ',') + ' \u20ac';
            } else if (precio > 0) {
                preuIvaCell.textContent = precio.toFixed(2).replace('.', ',') + ' \u20ac';
            } else {
                preuIvaCell.textContent = '-';
            }
        }

        let lineaBruto = cantidad * precio;
        subtotalSinDesc += lineaBruto;

        let lineaTotal = lineaBruto;
        if (descuento > 0) {
            lineaTotal = lineaBruto * (1 - descuento / 100);
            hayDescuento = true;
        }

        row.querySelector('.linea-total').textContent = lineaTotal.toFixed(2).replace('.', ',') + ' \u20ac';
        total += lineaTotal;
    });

    document.getElementById('totalGeneral').textContent = total.toFixed(2).replace('.', ',') + ' \u20ac';

    const rowSub = document.getElementById('rowSubtotal');
    const rowDto = document.getElementById('rowDescuento');
    if (hayDescuento) {
        rowSub.style.display = '';
        rowDto.style.display = '';
        document.getElementById('subtotalSinDesc').textContent = subtotalSinDesc.toFixed(2).replace('.', ',') + ' \u20ac';
        document.getElementById('totalDescuento').textContent = '-' + (subtotalSinDesc - total).toFixed(2).replace('.', ',') + ' \u20ac';
    } else {
        rowSub.style.display = 'none';
        rowDto.style.display = 'none';
    }
}

document.getElementById('addLineBtn').addEventListener('click', addLinea);
addLinea();
</script>

<style>
.venda-header {
    display: flex; gap: 1rem; align-items: flex-end;
    background: var(--gray-50); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;
}
.header-field { display: flex; flex-direction: column; }
.header-label { font-size: 0.75rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; }
#lineasTable .form-input { padding: 0.4rem 0.5rem; }
.client-info { background: var(--gray-50); padding: 0.5rem 1rem; border-radius: 6px; display: flex; gap: 0.5rem; }

.product-search-container { position: relative; }
.search-results {
    position: absolute; top: 100%; left: 0; right: 0;
    background: white; border: 1px solid var(--gray-300); border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 100;
    max-height: 250px; overflow-y: auto; display: none;
}
.search-result-item {
    padding: 0.6rem 0.75rem; cursor: pointer; display: flex; gap: 0.75rem; align-items: center;
    border-bottom: 1px solid var(--gray-100);
}
.search-result-item:last-child { border-bottom: none; }
.search-result-item:hover, .search-result-item.active { background: var(--gray-50); }
.search-result-item.no-results { color: var(--gray-500); cursor: default; }
.search-result-item.no-results:hover { background: white; }
.result-codigo { font-family: monospace; font-size: 0.8rem; color: var(--gray-500); min-width: 80px; }
.result-nombre { flex: 1; font-size: 0.875rem; }
.result-precio { font-weight: 600; color: var(--primary); font-size: 0.875rem; }

.badge { display: inline-block; padding: 0.25rem 0.625rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; }
.badge-info { background: #dbeafe; color: #1e40af; }
.badge-special { background: #fef3c7; color: #92400e; }
.badge-default { background: var(--gray-100); color: var(--gray-600); }

@media (max-width: 768px) {
    .venda-header { flex-direction: column; align-items: stretch; }
    .venda-header .header-field { width: 100% !important; }
}
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
