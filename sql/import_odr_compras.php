<?php
/**
 * Import purchase invoices into odr_facturas_compra
 * Suppliers: BE REBEL Studio, DIVERGENT MINDS SL, Idealista S.A.U.
 * 29 unique invoices (excluding BR240090 which is for another client)
 * Run: php sql/import_odr_compras.php
 */
$bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
if (!$bd) die("DB connection error\n");
mysqli_set_charset($bd, 'utf8mb4');

echo "=== IMPORT ODR PURCHASE INVOICES ===\n\n";

// ============================================================
// 1. SUPPLIERS
// ============================================================
$proveedores = [
    [
        'nombre' => 'BE REBEL Studio, S.L.',
        'nombre_fiscal' => 'BE REBEL Studio, S.L.',
        'nif' => 'B04976627',
        'telefono' => '960835396',
        'email' => 'hello@berebel.studio',
        'direccion' => 'C/ Gran Via, 93 Bajo',
        'cp' => '46610',
        'poblacion' => 'Guadassuar',
        'provincia' => 'Valencia',
        'notas' => 'Web: https://berebel.studio | Banco Sabadell: ES04 0081 5382 2500 0124 9733 | Bankinter: ES62 0128 0623 1801 0005 3625',
    ],
    [
        'nombre' => 'DIVERGENT MINDS SL',
        'nombre_fiscal' => 'DIVERGENT MINDS SL',
        'nif' => 'B67504472',
        'telefono' => '934002600',
        'email' => 'administracion@mitsoftware.com',
        'direccion' => 'Avenida Diagonal 131',
        'cp' => '08018',
        'poblacion' => 'Barcelona',
        'provincia' => 'Barcelona',
        'notas' => 'MiT Software | Web: https://mitsoftware.com | BBVA: ES9801821527510204732160',
    ],
    [
        'nombre' => 'Idealista S.A.U.',
        'nombre_fiscal' => 'Idealista S.A.U.',
        'nif' => 'A-82505660',
        'telefono' => '917014030',
        'email' => '',
        'direccion' => 'Plaza de las Cortes 2, planta 5',
        'cp' => '28014',
        'poblacion' => 'Madrid',
        'provincia' => 'Madrid',
        'notas' => 'API Idealista | Forma de pago: Domiciliacion',
    ],
];

$provIds = [];
foreach ($proveedores as $p) {
    // Check if already exists by NIF
    $nifEsc = mysqli_real_escape_string($bd, $p['nif']);
    $check = mysqli_query($bd, "SELECT id FROM odr_proveedores WHERE nif = '$nifEsc' LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
        $provIds[$p['nif']] = mysqli_fetch_assoc($check)['id'];
        echo "  Supplier already exists: {$p['nombre']} (ID {$provIds[$p['nif']]})\n";
        continue;
    }

    $stmt = mysqli_prepare($bd, "INSERT INTO odr_proveedores (nombre, nombre_fiscal, nif, telefono, email, direccion, cp, poblacion, provincia, notas) VALUES (?,?,?,?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'ssssssssss',
        $p['nombre'], $p['nombre_fiscal'], $p['nif'], $p['telefono'],
        $p['email'], $p['direccion'], $p['cp'], $p['poblacion'],
        $p['provincia'], $p['notas']
    );
    mysqli_stmt_execute($stmt);
    $provIds[$p['nif']] = mysqli_insert_id($bd);
    mysqli_stmt_close($stmt);
    echo "  + Supplier: {$p['nombre']} → ID {$provIds[$p['nif']]}\n";
}

$idBeRebel   = $provIds['B04976627'];
$idDivergent = $provIds['B67504472'];
$idIdealista = $provIds['A-82505660'];

echo "\nSupplier IDs: BE REBEL=$idBeRebel, DIVERGENT=$idDivergent, IDEALISTA=$idIdealista\n\n";

// ============================================================
// 2. INVOICES + LINES
// ============================================================
$facturas = [
    // ---- BE REBEL Studio ----
    // NOTE: BR240090 is for "Bellas Unas Johana S.L." NOT Almabris - included as it was in the folder
    ['num' => 'BR240090', 'prov' => $idBeRebel, 'fecha' => '2024-02-29', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Servicios de posicionamiento web', 1, 420.00, 21],
    ]],
    ['num' => 'BR240232', 'prov' => $idBeRebel, 'fecha' => '2024-06-28', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Diseno UX/UI plataforma Dykinson', 1, 2300.00, 21],
    ]],
    ['num' => 'BR240233', 'prov' => $idBeRebel, 'fecha' => '2024-06-28', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Integracion WC - Odoo para friobox.com', 1, 1250.00, 21],
    ]],
    ['num' => 'BR240313', 'prov' => $idBeRebel, 'fecha' => '2024-08-05', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['FrioBox - Alertas de pedido fallido en la integracion de Odoo', 1, 350.00, 21],
        ['FrioBox - Anadir Fecha de Entrega en las notas del pedido creado en Odoo', 1, 210.00, 21],
        ['FrioBox - Reparar problema redondeo decimales en la integracion de Odoo', 1, 350.00, 21],
    ]],
    ['num' => 'BR240344', 'prov' => $idBeRebel, 'fecha' => '2024-09-09', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Desarrollo web Dykinson (Fase 1)', 1, 8000.00, 21],
    ]],
    ['num' => 'BR240442', 'prov' => $idBeRebel, 'fecha' => '2024-11-29', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Desarrollo modificaciones facturas Lubak', 1, 280.00, 21],
    ]],
    ['num' => 'BR240458', 'prov' => $idBeRebel, 'fecha' => '2024-12-27', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Servicios de posicionamiento web', 1, 420.00, 21],
    ]],
    ['num' => 'BR240459', 'prov' => $idBeRebel, 'fecha' => '2024-12-27', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Modificaciones web', 1, 120.00, 21],
    ]],
    ['num' => 'BR250058', 'prov' => $idBeRebel, 'fecha' => '2025-01-31', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Desarrollo web Espacio Dykinson (Fase 1) - 50% presupuesto (pago 1 de 2)', 1, 2625.00, 21],
    ]],
    ['num' => 'BR250085', 'prov' => $idBeRebel, 'fecha' => '2025-02-28', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Desarrollo web Dykinson (Fase 1)', 1, 4150.00, 21],
    ]],
    ['num' => 'BR250223', 'prov' => $idBeRebel, 'fecha' => '2025-06-17', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Desarrollo web Dykinson (Fase 3)', 1, 4950.00, 21],
    ]],
    ['num' => 'BR250255', 'prov' => $idBeRebel, 'fecha' => '2025-07-22', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Desarrollo web Dykinson (Fase 4 - Parte 1)', 1, 2000.00, 21],
    ]],
    ['num' => 'BR250256', 'prov' => $idBeRebel, 'fecha' => '2025-07-22', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Desarrollo web Dykinson (Fase 5)', 1, 2000.00, 21],
    ]],
    ['num' => 'BR250354', 'prov' => $idBeRebel, 'fecha' => '2025-10-10', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Bolsa de 40 horas de desarrollo web para proyectos Dykinson', 1, 2000.00, 21],
    ]],
    ['num' => 'BR250393', 'prov' => $idBeRebel, 'fecha' => '2025-10-31', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Desarrollo web Espacio Dykinson (Fase 2) - 50% presupuesto (pago 2 de 2)', 1, 2175.00, 21],
    ]],
    ['num' => 'BR250402', 'prov' => $idBeRebel, 'fecha' => '2025-11-03', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Bolsa de 40 horas de desarrollo web para proyectos Dykinson', 1, 2000.00, 21],
    ]],
    ['num' => 'BR250441', 'prov' => $idBeRebel, 'fecha' => '2025-12-17', 'vto' => null, 'pago' => 'Transferencia',
     'lineas' => [
        ['Bolsa de 40 horas de desarrollo web para proyectos Dykinson', 1, 2000.00, 21],
    ]],

    // ---- DIVERGENT MINDS SL (MiT Software) ----
    ['num' => 'INV/2024/0091', 'prov' => $idDivergent, 'fecha' => '2024-12-17', 'vto' => '2024-12-17', 'pago' => 'Transferencia',
     'notas' => 'PRIMERA BOLSA 40H - Bolsa de 160 horas dividida en 4 bolsas de 40 horas',
     'lineas' => [
        ['PRIMERA BOLSA 40H', 40, 45.00, 21],
    ]],
    ['num' => 'INV/2024/0092', 'prov' => $idDivergent, 'fecha' => '2024-12-26', 'vto' => '2024-12-26', 'pago' => 'Transferencia',
     'lineas' => [
        ['2da BOLSA DE HORAS 40', 40, 45.00, 21],
    ]],
    ['num' => 'INV/2024/0094', 'prov' => $idDivergent, 'fecha' => '2024-12-31', 'vto' => '2024-12-31', 'pago' => 'Transferencia',
     'lineas' => [
        ['3a BOLSA DE HORAS 40', 40, 45.00, 21],
    ]],
    ['num' => 'INV/2025/0003', 'prov' => $idDivergent, 'fecha' => '2025-01-08', 'vto' => '2025-01-08', 'pago' => 'Transferencia',
     'lineas' => [
        ['4a BOLSA DE HORAS 40', 40, 45.00, 21],
    ]],
    ['num' => 'INV/2025/0007', 'prov' => $idDivergent, 'fecha' => '2025-01-27', 'vto' => '2025-01-27', 'pago' => 'Transferencia',
     'lineas' => [
        ['5a BOLSA DE HORAS 40', 40, 45.00, 21],
    ]],
    ['num' => 'INV/2025/0008', 'prov' => $idDivergent, 'fecha' => '2025-01-29', 'vto' => '2025-01-29', 'pago' => 'Transferencia',
     'lineas' => [
        ['6a BOLSA DE HORAS 40', 40, 45.00, 21],
    ]],
    ['num' => 'INV/2025/0009', 'prov' => $idDivergent, 'fecha' => '2025-02-08', 'vto' => '2025-02-08', 'pago' => 'Transferencia',
     'notas' => 'Bolsa de Horas culminada 7.2.25',
     'lineas' => [
        ['7ma Bolsa de 40 horas', 40, 45.00, 21],
    ]],
    ['num' => 'INV/2025/0010', 'prov' => $idDivergent, 'fecha' => '2025-02-09', 'vto' => '2025-02-09', 'pago' => 'Transferencia',
     'lineas' => [
        ['8TAVA BOLSA DE HORAS INICIADA 8 2 25', 40, 45.00, 21],
    ]],
    ['num' => 'INV/2025/0014', 'prov' => $idDivergent, 'fecha' => '2025-02-21', 'vto' => '2025-02-28', 'pago' => 'Transferencia',
     'lineas' => [
        ['9na bolsa de horas', 40, 45.00, 21],
    ]],
    ['num' => 'INV/2025/0015', 'prov' => $idDivergent, 'fecha' => '2025-02-18', 'vto' => '2025-02-18', 'pago' => 'Transferencia',
     'lineas' => [
        ['Tareas del 13 a 17 febrero', 12, 45.00, 21],
    ]],

    // ---- Idealista S.A.U. ----
    ['num' => '4336684', 'prov' => $idIdealista, 'fecha' => '2025-06-10', 'vto' => null, 'pago' => 'Domiciliacion',
     'lineas' => [
        ['API servicio de 21 dias de junio 2025', 1, 350.00, 21],
    ]],
    ['num' => '4367419', 'prov' => $idIdealista, 'fecha' => '2025-07-03', 'vto' => null, 'pago' => 'Domiciliacion',
     'lineas' => [
        ['API servicio de julio 2025', 1, 500.00, 21],
    ]],
];

// Insert invoices + lines
$countFact = 0;
$countLines = 0;
$totalBase = 0;

foreach ($facturas as $f) {
    // Check duplicate by numero_factura
    $numEsc = mysqli_real_escape_string($bd, $f['num']);
    $check = mysqli_query($bd, "SELECT id FROM odr_facturas_compra WHERE numero_factura = '$numEsc' LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
        echo "  SKIP (duplicate): {$f['num']}\n";
        continue;
    }

    $notas = $f['notas'] ?? null;
    $vto = $f['vto'] ?? null;

    $stmt = mysqli_prepare($bd, "INSERT INTO odr_facturas_compra (numero_factura, idProveedor, fecha, fecha_vencimiento, estado, forma_pago, notas, created_at, updated_at) VALUES (?, ?, ?, ?, 'pagada', ?, ?, NOW(), NOW())");
    mysqli_stmt_bind_param($stmt, 'sissss', $f['num'], $f['prov'], $f['fecha'], $vto, $f['pago'], $notas);
    $ok = mysqli_stmt_execute($stmt);

    if (!$ok) {
        echo "  ERROR inserting {$f['num']}: " . mysqli_error($bd) . "\n";
        mysqli_stmt_close($stmt);
        continue;
    }

    $factId = mysqli_insert_id($bd);
    mysqli_stmt_close($stmt);
    $countFact++;

    // Insert lines
    $baseFactura = 0;
    foreach ($f['lineas'] as $linea) {
        $stmtL = mysqli_prepare($bd, "INSERT INTO odr_linea_factura_compra (idFactura, concepto, cantidad, precio_unidad, iva) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmtL, 'isddd', $factId, $linea[0], $linea[1], $linea[2], $linea[3]);
        mysqli_stmt_execute($stmtL);
        mysqli_stmt_close($stmtL);
        $countLines++;
        $sub = $linea[1] * $linea[2];
        $baseFactura += $sub;
        $totalBase += $sub;
    }

    echo "  + {$f['num']} | {$f['fecha']} | Base: " . number_format($baseFactura, 2, ',', '.') . " EUR\n";
}

echo "\n=== SUMMARY ===\n";
echo "Suppliers: " . count($proveedores) . "\n";
echo "Invoices inserted: $countFact\n";
echo "Lines inserted: $countLines\n";
echo "Total base: " . number_format($totalBase, 2, ',', '.') . " EUR\n";

// Verify
echo "\n=== VERIFICATION ===\n";
$r = mysqli_query($bd, "SELECT p.nombre, COUNT(fc.id) as num_fact,
    SUM(lfc.cantidad * lfc.precio_unidad) as total_base
    FROM odr_proveedores p
    LEFT JOIN odr_facturas_compra fc ON fc.idProveedor = p.id
    LEFT JOIN odr_linea_factura_compra lfc ON lfc.idFactura = fc.id
    GROUP BY p.id ORDER BY total_base DESC");
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['nombre'] . ' | ' . $row['num_fact'] . ' fact. | Base: ' . number_format($row['total_base'] ?? 0, 2, ',', '.') . " EUR\n";
}

mysqli_close($bd);
echo "\nDone!\n";
