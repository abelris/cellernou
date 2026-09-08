<?php
/**
 * El Celler Nou - Modificar Tiquet
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php'));
    exit;
}

$result = mysqli_query($bd, "SELECT * FROM tiquets WHERE id = $id");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php'));
    exit;
}

$tiquet = mysqli_fetch_assoc($result);
$pageTitle = 'Editar Tiquet #' . $id;
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

// Obtener líneas existentes
$lineasExistentes = [];
$result = mysqli_query($bd, "SELECT lt.*, p.codigo as producto_codigo, p.nombre as producto_nombre
                             FROM linea_tiquet lt
                             LEFT JOIN productos p ON lt.idProducto = p.id
                             WHERE lt.idVenta = $id ORDER BY lt.id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $lineasExistentes[] = $row;
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
            // Restaurar stock de líneas antiguas (desmarcar como vendido)
            foreach ($lineasExistentes as $lineaAntigua) {
                $cantRestore = intval(ceil($lineaAntigua['cantidad']));
                $rsRestore = mysqli_query($bd, "SELECT id FROM stock_producto WHERE vendido=1 AND idProducto={$lineaAntigua['idProducto']} ORDER BY fecha_vendido DESC LIMIT $cantRestore");
                if ($rsRestore) {
                    while ($rowRestore = mysqli_fetch_assoc($rsRestore)) {
                        mysqli_query($bd, "UPDATE stock_producto SET vendido=0, fecha_vendido='0000-00-00 00:00:00' WHERE id={$rowRestore['id']}");
                    }
                }
            }

            // Eliminar líneas antiguas
            mysqli_query($bd, "DELETE FROM linea_tiquet WHERE idVenta = $id");

            // Actualizar tiquet
            $sql = "UPDATE tiquets SET idCliente = $idCliente, tipo_pago = '$tipoPago', comentarios = '$comentarios', idTienda = $idTienda WHERE id = $id";

            if (mysqli_query($bd, $sql)) {
                foreach ($lineasValidas as $linea) {
                    $prodResult = mysqli_query($bd, "SELECT precio_medio, iva FROM productos WHERE id = {$linea['idProducto']}");
                    $prodData = mysqli_fetch_assoc($prodResult);
                    $precioCompra = $prodData['precio_medio'] ?? 0;
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
                                 VALUES ($id, {$linea['idProducto']}, $idCliente, NOW(), {$linea['cantidad']}, $precioUnidad, $precioSinImp, {$linea['descuento']}, $precioSinDesc, $precioCompra, $iva)";
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

                logActivity('tiquet_update', "Tiquet actualitzat: #$id");
                header('Location: ' . getAdminUrl('modules/tiquets/veure.php?id=' . $id));
                exit;
            } else {
                $errors[] = 'Error al actualitzar el tiquet.';
            }
        }
    }
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="page-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo getAdminUrl('modules/tiquets/veure.php?id=' . $id); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar
    </a>
    <form method="POST" action="<?php echo getAdminUrl('modules/tiquets/eliminar.php'); ?>" style="display: inline;"
          onsubmit="return confirm('Estàs segur que vols eliminar aquest tiquet?');">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <button type="submit" class="btn btn-danger">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
        </button>
    </form>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error" style="margin-bottom: 1rem;">
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
                <option value="<?php echo $c['id']; ?>" <?php echo $tiquet['idCliente'] == $c['id'] ? 'selected' : ''; ?>>
                    <?php echo e($c['nombre']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="header-field" style="width: 150px;">
            <label class="header-label">Pagament</label>
            <select id="tipo_pago" name="tipo_pago" class="form-input">
                <option value="E" <?php echo $tiquet['tipo_pago'] === 'E' ? 'selected' : ''; ?>>Efectiu</option>
                <option value="T" <?php echo $tiquet['tipo_pago'] === 'T' ? 'selected' : ''; ?>>Targeta</option>
                <option value="C" <?php echo $tiquet['tipo_pago'] === 'C' ? 'selected' : ''; ?>>Compte</option>
            </select>
        </div>
        <div class="header-field" style="flex: 1;">
            <label class="header-label">Comentaris</label>
            <input type="text" id="comentarios" name="comentarios" class="form-input"
                   value="<?php echo e($tiquet['comentarios']); ?>">
        </div>
    </div>

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
                            <th style="width: 100px;">Quantitat</th>
                            <th style="width: 120px;">Preu tarifa</th>
                            <th style="width: 100px;">Dto %</th>
                            <th style="width: 120px; text-align: right;">Total</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lineasBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: 600;">TOTAL:</td>
                            <td style="text-align: right; font-size: 1.25rem; font-weight: 700; color: var(--primary);" id="totalGeneral">0,00 &euro;</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 1rem;">
        <a href="<?php echo getAdminUrl('modules/tiquets/veure.php?id=' . $id); ?>" class="btn btn-secondary">Cancel·lar</a>
        <button type="submit" class="btn btn-primary">Guardar Canvis</button>
    </div>
</form>

<script>
const productos = <?php echo json_encode($productos); ?>;
const lineasExistentes = <?php echo json_encode($lineasExistentes); ?>;
let lineaCount = 0;

function createProductSearch(index, productoNombre = '') {
    return `
        <div class="product-search-container">
            <input type="text" class="form-input product-search" placeholder="Escriu per cercar..." autocomplete="off" data-index="${index}" value="${productoNombre}">
            <input type="hidden" name="lineas[${index}][idProducto]" class="product-id">
            <div class="search-results"></div>
        </div>
    `;
}

function addLinea(productoId = '', cantidad = 1, precio = 0, descuento = 0, productoNombre = '') {
    const tbody = document.getElementById('lineasBody');
    const tr = document.createElement('tr');
    tr.id = `linea-${lineaCount}`;
    tr.innerHTML = `
        <td>${createProductSearch(lineaCount, productoNombre)}</td>
        <td><input type="number" name="lineas[${lineaCount}][cantidad]" class="form-input cantidad-input" value="${cantidad}" min="1" step="any" onchange="calculateTotal()"></td>
        <td><input type="number" name="lineas[${lineaCount}][precio_unidad]" class="form-input precio-input" value="${precio}" min="0" step="any" onchange="calculateTotal()"></td>
        <td><input type="number" name="lineas[${lineaCount}][descuento]" class="form-input descuento-input" value="${descuento}" min="0" max="100" step="1" onchange="calculateTotal()"></td>
        <td class="linea-total" style="text-align: right; font-weight: 600;">0,00 &euro;</td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeLinea(${lineaCount})">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button></td>
    `;
    tbody.appendChild(tr);

    // Configurar búsqueda y establecer ID si existe
    const searchInput = tr.querySelector('.product-search');
    const hiddenInput = tr.querySelector('.product-id');
    if (productoId) {
        hiddenInput.value = productoId;
    }
    setupProductSearch(searchInput);

    lineaCount++;
    calculateTotal();
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
        }
    });

    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });

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
                active.click();
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

document.getElementById('addLineBtn').addEventListener('click', () => addLinea());

// Cargar líneas existentes
lineasExistentes.forEach(l => {
    const nombreCompleto = l.producto_codigo ? `${l.producto_codigo} - ${l.producto_nombre}` : l.producto_nombre;
    // El camp "Preu tarifa" ha de mostrar el preu ABANS del descompte,
    // perquè al guardar es torna a aplicar el descompte sobre aquest valor.
    const precioTarifa = parseFloat(l.precio_unidad_sin_desc) > 0 ? l.precio_unidad_sin_desc : l.precio_unidad;
    addLinea(l.idProducto, l.cantidad, precioTarifa, l.descuento, nombreCompleto);
});

if (lineasExistentes.length === 0) {
    addLinea();
}
</script>

<style>
.tiquet-header {
    display: flex; gap: 1rem; align-items: flex-end;
    background: var(--gray-50); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;
}
.header-field { display: flex; flex-direction: column; }
.header-field:first-child { width: 250px; }
.header-label { font-size: 0.75rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 0.25rem; }
.form-input { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid var(--gray-300); border-radius: 6px; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1); }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: 8px; }
#lineasTable .form-input { padding: 0.4rem 0.5rem; }

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
.result-codigo { font-family: monospace; font-size: 0.8rem; color: var(--gray-500); min-width: 100px; }
.result-nombre { flex: 1; font-size: 0.875rem; }
.result-precio { font-weight: 600; color: var(--primary); font-size: 0.875rem; }

@media (max-width: 768px) {
    .tiquet-header { flex-direction: column; align-items: stretch; }
    .header-field, .header-field:first-child { width: 100% !important; }
}
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
