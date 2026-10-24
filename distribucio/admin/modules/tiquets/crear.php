<?php
/**
 * El Celler Nou - Crear Tiquet
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/costos.php';
// Abans de processar el POST: header.php també ho comprova, però s'inclou
// després i el tiquet ja s'hauria desat.
requireAuth();

$pageTitle = 'Nou Tiquet';
$errors = [];

// Obtener clientes
$clientes = [];
$result = mysqli_query($bd, "SELECT id, nombre FROM clientes ORDER BY nombre");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $clientes[] = $row;
    }
}

// Obtener productos activos con precio de tienda 1
$productos = [];
$sql = "SELECT p.id, p.codigo, p.nombre, p.iva, COALESCE(pt.pvp, 0) as precio
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Error de seguretat.';
    } else {
        $idCliente = (int)($_POST['idCliente'] ?? 0);
        $tipoPago = mysqli_real_escape_string($bd, $_POST['tipo_pago'] ?? 'E');
        $comentarios = mysqli_real_escape_string($bd, trim($_POST['comentarios'] ?? ''));
        $idTienda = 1; // Solo tienda 1

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

        if (empty($lineasValidas)) {
            $errors[] = 'Has d\'afegir almenys un producte al tiquet.';
        }

        if (empty($errors)) {
            $sql = "INSERT INTO tiquets (idCliente, fecha, tipo_pago, comentarios, idTienda, idEstado, descuento, precio_desc, coste, precio_coste, albaran)
                    VALUES ($idCliente, NOW(), '$tipoPago', '$comentarios', $idTienda, 0, '', 0, '', 0, '')";

            if (mysqli_query($bd, $sql)) {
                $tiquetId = mysqli_insert_id($bd);
                $senseCost = [];

                foreach ($lineasValidas as $linea) {
                    $prodResult = mysqli_query($bd, "SELECT nombre, iva FROM productos WHERE id = {$linea['idProducto']}");
                    $prodData = mysqli_fetch_assoc($prodResult);
                    // Cost mitjà de compra (includes/costos.php). Si el producte
                    // no en té cap, es grava 0 però s'avisa a la fitxa del tiquet.
                    $precioCompra = costLiniaVenda($bd, (int)$linea['idProducto']);
                    if ($precioCompra <= 0) {
                        $senseCost[] = $prodData['nombre'] ?? ('Producte #' . $linea['idProducto']);
                    }
                    $iva = $prodData['iva'] ?? 21;

                    // El preu introduït és el de tarifa. El descompte es RESTA d'aquest preu:
                    // precio_unidad_sin_desc = tarifa, precio_unidad = tarifa amb el descompte ja aplicat.
                    $precioSinDesc = $linea['precio_unidad'];
                    $precioUnidad = $precioSinDesc;
                    if ($linea['descuento'] > 0) {
                        $precioUnidad = $precioSinDesc * (1 - ($linea['descuento'] / 100));
                    }
                    $precioSinImp = $precioUnidad / (1 + ($iva / 100));

                    $sqlLinea = "INSERT INTO linea_tiquet (idVenta, idProducto, idCliente, fecha, cantidad, precio_unidad, precio_unidad_sin_imp, descuento, precio_unidad_sin_desc, precio_compra, iva)
                                 VALUES ($tiquetId, {$linea['idProducto']}, $idCliente, NOW(), {$linea['cantidad']}, $precioUnidad, $precioSinImp, {$linea['descuento']}, $precioSinDesc, $precioCompra, $iva)";
                    mysqli_query($bd, $sqlLinea);

                    // Marcar stock como vendido (filas más antiguas primero)
                    $cantStock = intval(ceil($linea['cantidad']));
                    $rsStock = mysqli_query($bd, "SELECT id FROM stock_producto WHERE vendido=0 AND idProducto={$linea['idProducto']} ORDER BY fecha_entrada ASC LIMIT $cantStock");
                    if ($rsStock) {
                        while ($rowStock = mysqli_fetch_assoc($rsStock)) {
                            mysqli_query($bd, "UPDATE stock_producto SET vendido=1, fecha_vendido=NOW() WHERE id={$rowStock['id']}");
                        }
                    }
                }

                if ($senseCost) {
                    $_SESSION['tiquet_sense_cost'] = [$tiquetId => array_values(array_unique($senseCost))];
                }
                logActivity('tiquet_create', "Tiquet creat: #$tiquetId");
                header('Location: ' . getAdminUrl('modules/tiquets/veure.php?id=' . $tiquetId));
                exit;
            } else {
                $errors[] = 'Error al crear el tiquet.';
            }
        }
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom: 0.5rem;">
    <?php foreach ($errors as $e): ?><p style="margin: 0;"><?php echo e($e); ?></p><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" id="tiquetForm">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="idTienda" value="1">

    <div class="tiquet-header">
        <div class="header-field">
            <label class="header-label">Client</label>
            <select id="idCliente" name="idCliente" class="form-input">
                <option value="0">Client general</option>
                <?php foreach ($clientes as $c): ?>
                <option value="<?php echo $c['id']; ?>"><?php echo e($c['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="header-field" style="width: 120px;">
            <label class="header-label">Pagament</label>
            <select id="tipo_pago" name="tipo_pago" class="form-input">
                <option value="E">Efectiu</option>
                <option value="T">Targeta</option>
                <option value="C">Compte</option>
            </select>
        </div>
        <div class="header-field" style="flex: 1;">
            <label class="header-label">Comentaris</label>
            <input type="text" id="comentarios" name="comentarios" class="form-input" placeholder="Opcional">
        </div>
        <div class="header-field header-actions">
            <button type="button" id="addLineBtn" class="btn btn-sm btn-primary" title="Afegir Línia">+</button>
            <button type="submit" class="btn btn-sm btn-success">Crear</button>
            <a href="<?php echo getAdminUrl('modules/tiquets/llistat.php'); ?>" class="btn btn-sm btn-secondary" title="Tornar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
        </div>
    </div>

    <div style="overflow: visible;">
        <table class="table table-compact" id="lineasTable">
            <thead>
                <tr>
                    <th>Producte</th>
                    <th style="width: 80px;">Qty</th>
                    <th style="width: 100px;">Preu tarifa</th>
                    <th style="width: 80px;">Dto %</th>
                    <th style="width: 100px; text-align: right;">Total</th>
                    <th style="width: 40px;"></th>
                </tr>
            </thead>
            <tbody id="lineasBody"></tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: 600;">TOTAL:</td>
                    <td style="text-align: right; font-size: 1.1rem; font-weight: 700; color: var(--primary);" id="totalGeneral">0,00 &euro;</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</form>

<script>
const productos = <?php echo json_encode($productos); ?>;
let lineaCount = 0;

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
        <td><input type="number" name="lineas[${lineaCount}][precio_unidad]" class="form-input precio-input" value="0" min="0" step="any" onchange="calculateTotal()"></td>
        <td><input type="number" name="lineas[${lineaCount}][descuento]" class="form-input descuento-input" value="0" min="0" max="100" step="1" onchange="calculateTotal()"></td>
        <td class="linea-total" style="text-align: right; font-weight: 600;">0,00 &euro;</td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeLinea(${lineaCount})">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button></td>
    `;
    tbody.appendChild(tr);

    // Configurar búsqueda
    const searchInput = tr.querySelector('.product-search');
    setupProductSearch(searchInput);

    lineaCount++;
}

function selectProduct(item, input, hiddenInput, resultsDiv) {
    const id = item.dataset.id;
    const nombre = item.dataset.nombre;
    const codigo = item.dataset.codigo;
    const precio = item.dataset.precio;

    input.value = codigo ? `${codigo} - ${nombre}` : nombre;
    hiddenInput.value = id;

    const row = input.closest('tr');
    row.querySelector('.precio-input').value = parseFloat(precio).toFixed(2);

    resultsDiv.style.display = 'none';
    calculateTotal();

    // Add new line and focus its search input
    addLinea();
    const newRow = document.getElementById(`linea-${lineaCount - 1}`);
    if (newRow) {
        const newInput = newRow.querySelector('.product-search');
        if (newInput) newInput.focus();
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
        ).slice(0, 25);

        if (matches.length === 0) {
            resultsDiv.innerHTML = '<div class="search-result-item no-results">No s\'han trobat productes</div>';
        } else {
            resultsDiv.innerHTML = matches.map(p => `
                <div class="search-result-item" data-id="${p.id}" data-nombre="${p.nombre}" data-codigo="${p.codigo || ''}" data-precio="${p.precio}">
                    <span class="result-codigo">${p.codigo || '-'}</span>
                    <span class="result-nombre">${p.nombre}</span>
                    <span class="result-precio">${parseFloat(p.precio).toFixed(2)} &euro;</span>
                </div>
            `).join('');
        }
        resultsDiv.style.display = 'block';
    });

    resultsDiv.addEventListener('click', function(e) {
        const item = e.target.closest('.search-result-item');
        if (item && !item.classList.contains('no-results')) {
            selectProduct(item, input, hiddenInput, resultsDiv);
        }
    });

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });

    // Navegación con teclado
    input.addEventListener('keydown', function(e) {
        const items = resultsDiv.querySelectorAll('.search-result-item:not(.no-results)');
        const active = resultsDiv.querySelector('.search-result-item.active');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!active && items.length > 0) {
                items[0].classList.add('active');
            } else if (active && active.nextElementSibling) {
                active.classList.remove('active');
                active.nextElementSibling.classList.add('active');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (active && active.previousElementSibling) {
                active.classList.remove('active');
                active.previousElementSibling.classList.add('active');
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (active) {
                selectProduct(active, input, hiddenInput, resultsDiv);
            }
        } else if (e.key === 'Escape') {
            resultsDiv.style.display = 'none';
        }
    });
}

function removeLinea(index) {
    const row = document.getElementById(`linea-${index}`);
    if (row) {
        row.remove();
        calculateTotal();
    }
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('#lineasBody tr').forEach(row => {
        const cantidad = parseFloat(row.querySelector('.cantidad-input')?.value || 0);
        const precio = parseFloat(row.querySelector('.precio-input')?.value || 0);
        const descuento = parseFloat(row.querySelector('.descuento-input')?.value || 0);

        let lineaTotal = cantidad * precio;
        if (descuento > 0) {
            lineaTotal = lineaTotal * (1 - descuento / 100);
        }

        row.querySelector('.linea-total').textContent = lineaTotal.toFixed(2).replace('.', ',') + ' €';
        total += lineaTotal;
    });

    document.getElementById('totalGeneral').textContent = total.toFixed(2).replace('.', ',') + ' €';
}

document.getElementById('addLineBtn').addEventListener('click', addLinea);

// Añadir primera línea al cargar
addLinea();
</script>

<style>
.main-content { padding-top: 0.5rem !important; }
.tiquet-header {
    display: flex; gap: 0.5rem; align-items: flex-end;
    background: var(--gray-50); padding: 0.5rem 0.75rem; border-radius: 6px; margin-bottom: 0.5rem;
}
.header-field { display: flex; flex-direction: column; }
.header-field:first-child { width: 220px; }
.header-actions { flex-direction: row; gap: 0.3rem; align-items: flex-end; }
.header-label { font-size: 0.7rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.15rem; }
.form-input { width: 100%; padding: 0.3rem 0.5rem; font-size: 0.8rem; border: 1px solid var(--gray-300); border-radius: 4px; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 2px rgba(114, 47, 55, 0.1); }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 0.5rem; border-radius: 6px; }
.btn-success { background: #16a34a; color: white; border: none; }
.btn-success:hover { background: #15803d; }

.table-compact { border-collapse: collapse; width: 100%; }
.table-compact th { padding: 0.3rem 0.5rem; font-size: 0.7rem; text-transform: uppercase; color: var(--gray-500); border-bottom: 2px solid var(--gray-200); }
.table-compact td { padding: 0.25rem 0.3rem; vertical-align: middle; }
.table-compact .form-input { padding: 0.25rem 0.4rem; font-size: 0.8rem; }
.table-compact tfoot td { padding: 0.4rem 0.3rem; border-top: 2px solid var(--gray-200); }

.product-search-container { position: relative; }
.search-results {
    position: absolute; top: 100%; left: 0; right: 0;
    background: white; border: 1px solid var(--gray-300); border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 100;
    max-height: 400px; overflow-y: auto; display: none;
}
.search-result-item {
    padding: 0.2rem 0.5rem; cursor: pointer; display: flex; gap: 0.4rem; align-items: center;
    border-bottom: 1px solid var(--gray-100); font-size: 0.75rem; line-height: 1.15;
}
.search-result-item:last-child { border-bottom: none; }
.search-result-item:hover, .search-result-item.active { background: var(--gray-50); }
.search-result-item.no-results { color: var(--gray-500); cursor: default; }
.search-result-item.no-results:hover { background: white; }
.result-codigo { font-family: monospace; font-size: 0.7rem; color: var(--gray-500); min-width: 70px; }
.result-nombre { flex: 1; font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.result-precio { font-weight: 600; color: var(--primary); font-size: 0.75rem; white-space: nowrap; }

@media (max-width: 768px) {
    .tiquet-header { flex-wrap: wrap; }
    .header-field, .header-field:first-child { width: 100% !important; }
}
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
