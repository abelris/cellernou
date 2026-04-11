<?php
/**
 * Import OnDigitalRis invoices from old PDFs
 * Run once: php import_odr_facturas.php
 */
$bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
if (!$bd) { die("Connection failed\n"); }
mysqli_set_charset($bd, 'utf8mb4');

// =============================================
// 1. INSERT CLIENTS
// =============================================
$clients = [
    ['ALMABRIS GROUP 2024 S.L.', 'ALMABRIS GROUP 2024 S.L.', 'B66318684', '', '', 'C/ Ramón Llull, 15', '08330', 'Premià de Mar', 'Barcelona'],
    ['DYKINSON S.L.', 'DYKINSON S.L.', 'B01385822', '', '', 'C/ Meléndez Valdés 61', '28015', 'Madrid', 'Madrid'],
    ['TECNISAMPLE S.L.', 'TECNISAMPLE S.L.', 'B66365024', '', '', 'Av. Diagonal, 474', '08006', 'Barcelona', 'Barcelona'],
    ['FRIO BOX S.L.', 'FRIO BOX S.L.', 'B66342700', '', '', 'C/ de la Ciència, 12 - Pol. Ind. Les Massotes', '08850', 'Gavá', 'Barcelona'],
    ['ESPACIO DYKINSON S.L.', 'ESPACIO DYKINSON S.L.', 'B88509061', '', '', 'C/ Meléndez Valdés 61', '28015', 'Madrid', 'Madrid'],
    ['SOSMATIC SL', 'SOSMATIC SL', 'B61768834', '', '', 'Calle de la Llacuna 161', '08018', 'Barcelona', 'Barcelona'],
    ['EVARIST GOURMET S.L.', 'EVARIST GOURMET S.L.', 'B67432568', '', '', 'C/ Evarist Arnús, 1', '08340', 'Vilassar de Mar', 'Barcelona'],
    ['IGUÑA PHARMA S.L.', 'IGUÑA PHARMA S.L.', 'B56334918', '', '', 'C/ Cartagena 70', '08025', 'Barcelona', 'Barcelona'],
];

$clientIds = []; // nombre => id

$stmtClient = mysqli_prepare($bd, "INSERT INTO odr_clientes (nombre, nombre_fiscal, nif, telefono, email, direccion, cp, poblacion, provincia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($clients as $c) {
    // Check if already exists
    $nifEsc = mysqli_real_escape_string($bd, $c[2]);
    $check = mysqli_query($bd, "SELECT id FROM odr_clientes WHERE nif = '$nifEsc'");
    if ($check && mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        $clientIds[$c[0]] = $row['id'];
        echo "Client exists: {$c[0]} (ID: {$row['id']})\n";
        continue;
    }
    mysqli_stmt_bind_param($stmtClient, 'sssssssss', $c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7], $c[8]);
    mysqli_stmt_execute($stmtClient);
    $clientIds[$c[0]] = mysqli_insert_id($bd);
    echo "Client created: {$c[0]} (ID: {$clientIds[$c[0]]})\n";
}
mysqli_stmt_close($stmtClient);

echo "\n--- Clients done ---\n\n";

// =============================================
// 2. INSERT INVOICES + LINES
// =============================================
// Format: [numero, clientName, fecha, forma_pago, [ [qty, concepto, precio_unidad, iva], ... ]]
$facturas = [
    ['INF20001', 'ALMABRIS GROUP 2024 S.L.', '2024-10-07', 'Transferencia', [
        [1, 'Creación de la Web Corporativa', 800.00, 21],
        [1, 'Creación Logotipo Corporativo', 200.00, 21],
        [1, 'Email Corporativo', 50.00, 21],
    ]],
    ['INF20002', 'DYKINSON S.L.', '2024-10-30', 'Transferencia', [
        [20, 'Horas Octubre Dykinson', 40.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20003', 'TECNISAMPLE S.L.', '2024-10-30', 'Transferencia', [
        [1, 'Consultoria de Marketing Q4', 350.00, 21],
    ]],
    ['INF20004', 'FRIO BOX S.L.', '2024-10-30', 'Transferencia', [
        [30, 'Horas bolsa descuento', 35.00, 21],
    ]],
    ['INF20005', 'ESPACIO DYKINSON S.L.', '2024-10-30', 'Transferencia', [
        [1, 'Diseño Web y Configuración Ecommerce', 1500.00, 21],
    ]],
    ['INF20006', 'SOSMATIC SL', '2024-10-30', 'Transferencia', [
        [1, 'Creación Web Sosmatic.com', 500.00, 21],
    ]],
    ['INF20007', 'SOSMATIC SL', '2024-11-04', 'Transferencia', [
        [1, 'Implementación Chatbot Inteligencia Artificial', 500.00, 21],
    ]],
    ['INF20008', 'SOSMATIC SL', '2024-11-30', 'Transferencia', [
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20009', 'SOSMATIC SL', '2024-12-02', 'Transferencia', [
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20010', 'SOSMATIC SL', '2024-12-02', 'Transferencia', [
        [16.40, 'Horas Noviembre Sosmatic', 25.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20011', 'FRIO BOX S.L.', '2024-12-02', 'Transferencia', [
        [30, 'Horas bolsa descuento', 35.00, 21],
        [1, 'Suscripcion Servidor VPS', 74.00, 21],
    ]],
    ['INF20012', 'TECNISAMPLE S.L.', '2024-12-02', 'Transferencia', [
        [1, 'Consultoria de Marketing Q4', 350.00, 21],
        [1, 'Hosting Web Tecnisample.es', 79.00, 21],
    ]],
    ['INF20013', 'EVARIST GOURMET S.L.', '2024-12-10', 'Transferencia', [
        [1, 'Diseño Web Ecommerce', 2000.00, 21],
    ]],
    ['INF20014', 'DYKINSON S.L.', '2024-12-31', 'Transferencia', [
        [24.50, 'Horas Diciembre Dykinson', 40.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20015', 'FRIO BOX S.L.', '2025-01-02', 'Transferencia', [
        [30, 'Horas bolsa descuento', 35.00, 21],
        [1, 'Suscripcion Servidor VPS', 74.00, 21],
    ]],
    ['INF20016', 'TECNISAMPLE S.L.', '2025-01-02', 'Transferencia', [
        [1, 'Consultoria de Marketing Q1', 350.00, 21],
        [1, 'Hosting Web Tecnisample.es', 79.00, 21],
    ]],
    ['INF20017', 'DYKINSON S.L.', '2025-01-02', 'Transferencia', [
        [16.50, 'Horas Diciembre Dykinson', 40.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20018', 'SOSMATIC SL', '2025-01-02', 'Transferencia', [
        [22.60, 'Horas Diciembre Sosmatic', 25.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20019', 'TECNISAMPLE S.L.', '2025-01-07', 'Transferencia', [
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20020', 'SOSMATIC SL', '2025-01-28', 'Transferencia', [
        [20.20, 'Horas Enero Sosmatic', 25.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20021', 'FRIO BOX S.L.', '2025-02-03', 'Transferencia', [
        [30, 'Horas bolsa descuento', 35.00, 21],
        [1, 'Suscripcion Servidor VPS', 74.00, 21],
    ]],
    ['INF20022', 'FRIO BOX S.L.', '2025-02-03', 'Transferencia', [
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20023', 'DYKINSON S.L.', '2025-02-03', 'Transferencia', [
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20024', 'ESPACIO DYKINSON S.L.', '2025-02-03', 'Transferencia', [
        [15.60, 'Horas Enero Espacio Dykinson', 40.00, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20025', 'ESPACIO DYKINSON S.L.', '2025-02-03', 'Transferencia', [
        [1, 'Hosting Espacio Dykinson', 60.00, 21],
    ]],
    ['INF20026', 'IGUÑA PHARMA S.L.', '2025-02-03', 'Transferencia', [
        [1, 'Diseño Web Corporativa', 1200.00, 21],
    ]],
    ['INF20027', 'ESPACIO DYKINSON S.L.', '2025-02-17', 'Transferencia', [
        [1, 'Hosting Espacio Dykinson', 60.00, 21],
    ]],
    ['INF20028', 'SOSMATIC SL', '2025-02-28', 'Transferencia', [
        [25.80, 'Horas Febrero Sosmatic', 25.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
        [1, 'Suscripcion Notion', 10.00, 21],
    ]],
    ['INF20029', 'ESPACIO DYKINSON S.L.', '2025-02-28', 'Transferencia', [
        [1, 'Hosting Espacio Dykinson', 60.00, 21],
    ]],
    ['INF20030', 'SOSMATIC SL', '2025-03-04', 'Transferencia', [
        [20.80, 'Horas Febrero Sosmatic', 25.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20031', 'TECNISAMPLE S.L.', '2025-03-04', 'Transferencia', [
        [1, 'Consultoria de Marketing Q1', 350.00, 21],
        [1, 'Hosting Web Tecnisample.es', 79.00, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20032', 'DYKINSON S.L.', '2025-03-04', 'Transferencia', [
        [20, 'Horas Febrero Dykinson', 40.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
    ['INF20033', 'ESPACIO DYKINSON S.L.', '2025-03-04', 'Transferencia', [
        [1, 'Hosting Espacio Dykinson', 60.00, 21],
    ]],
    ['INF20034', 'FRIO BOX S.L.', '2025-03-04', 'Transferencia', [
        [30, 'Horas bolsa descuento', 35.00, 21],
    ]],
    ['INF20035', 'SOSMATIC SL', '2025-03-04', 'Transferencia', [
        [24.80, 'Horas Febrero Sosmatic', 25.00, 21],
        [1, 'Suscripcion LEMLIST', 69.00, 21],
        [1, 'Suscripcion LEMWARM', 97.27, 21],
        [1, 'Servidor Hetzner', 5.54, 21],
    ]],
];

$stmtFact = mysqli_prepare($bd, "INSERT INTO odr_facturas (numero, idCliente, fecha, forma_pago, estado) VALUES (?, ?, ?, ?, 'pagada')");
$stmtLine = mysqli_prepare($bd, "INSERT INTO odr_linea_factura (idFactura, concepto, cantidad, precio_unidad, iva) VALUES (?, ?, ?, ?, ?)");

$countFacturas = 0;
$countLineas = 0;

foreach ($facturas as $f) {
    $numero = $f[0];
    $clientName = $f[1];
    $fecha = $f[2];
    $formaPago = $f[3];
    $lineas = $f[4];

    $idCliente = $clientIds[$clientName] ?? 0;
    if ($idCliente === 0) {
        echo "WARNING: Client not found: $clientName (invoice $numero)\n";
        continue;
    }

    // Check if invoice already exists
    $numEsc = mysqli_real_escape_string($bd, $numero);
    $check = mysqli_query($bd, "SELECT id FROM odr_facturas WHERE numero = '$numEsc'");
    if ($check && mysqli_num_rows($check) > 0) {
        echo "Invoice exists: $numero - skipped\n";
        continue;
    }

    mysqli_stmt_bind_param($stmtFact, 'siss', $numero, $idCliente, $fecha, $formaPago);
    mysqli_stmt_execute($stmtFact);
    $facturaId = mysqli_insert_id($bd);
    $countFacturas++;

    foreach ($lineas as $l) {
        $qty = $l[0];
        $concepto = $l[1];
        $precio = $l[2];
        $iva = $l[3];
        mysqli_stmt_bind_param($stmtLine, 'isddi', $facturaId, $concepto, $qty, $precio, $iva);
        mysqli_stmt_execute($stmtLine);
        $countLineas++;
    }

    // Calculate base for display
    $base = 0;
    foreach ($lineas as $l) {
        $base += $l[0] * $l[2];
    }
    echo "Invoice: $numero | $clientName | $fecha | Base: " . number_format($base, 2) . " EUR\n";
}

mysqli_stmt_close($stmtFact);
mysqli_stmt_close($stmtLine);

echo "\n=== IMPORT COMPLETE ===\n";
echo "Clients: " . count($clientIds) . "\n";
echo "Invoices: $countFacturas\n";
echo "Lines: $countLineas\n";
