<?php
/**
 * El Celler Nou - Generar Tiquet PDF (TCPDF8)
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
requireAuth();
require_once dirname(dirname(dirname(__DIR__))) . '/tcpdf8/tcpdf.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php'));
    exit;
}

// Obtener tiquet
$tiquet = null;
$sql = "SELECT t.*, c.nombre as cliente_nombre, c.direccion as cliente_direccion, c.telefono as cliente_telefono
        FROM tiquets t
        LEFT JOIN clientes c ON t.idCliente = c.id
        WHERE t.id = $id";
$result = mysqli_query($bd, $sql);
if ($result) {
    $tiquet = mysqli_fetch_assoc($result);
}

if (!$tiquet) {
    header('Location: ' . getAdminUrl('modules/tiquets/llistat.php'));
    exit;
}

// Obtener líneas
$lineas = [];
$totalBruto = 0;
$totalDescuento = 0;
$totalNeto = 0;

$sql = "SELECT lt.*, p.nombre as producto_nombre, p.codigo as producto_codigo
        FROM linea_tiquet lt
        LEFT JOIN productos p ON lt.idProducto = p.id
        WHERE lt.idVenta = $id
        ORDER BY lt.id";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // precio_unidad_sin_desc = preu de tarifa; precio_unidad = preu amb el descompte ja aplicat.
        $tarifa = $row['precio_unidad_sin_desc'] > 0 ? $row['precio_unidad_sin_desc'] : $row['precio_unidad'];
        $subtotalBruto = $row['cantidad'] * $tarifa;
        $subtotal = $row['cantidad'] * $row['precio_unidad'];
        $subtotalDesc = $subtotalBruto - $subtotal;

        $row['precio_tarifa'] = $tarifa;
        $row['subtotal_bruto'] = $subtotalBruto;
        $row['subtotal_descuento'] = $subtotalDesc;
        $row['subtotal'] = $subtotal;

        $totalBruto += $subtotalBruto;
        $totalDescuento += $subtotalDesc;
        $totalNeto += $subtotal;

        $lineas[] = $row;
    }
}

// Datos empresa
$empresa = [
    'nombre' => 'EL CELLER NOU',
    'razon' => 'ALMABRIS GROUP 2024 S.L.',
    'direccion' => 'C/ Ramon Llull, 15',
    'ciudad' => '08330 Premià de Mar',
    'telefono' => '93 752 02 71',
    'cif' => 'B66318684',
    'email' => 'cellernou@cellernou.com'
];

$tipoPagoTexto = ['E' => 'Efectiu', 'T' => 'Targeta', 'C' => 'Compte'];

// Crear PDF - tamaño ticket 80mm ancho
$pageW = 80; // mm
$pageH = 200; // mm (se ajustará)

// Calcular altura necesaria
$headerH = 32;
$infoH = 22;
$lineaH = 6;
$lineasH = 10 + (count($lineas) * $lineaH) + 6; // header + lineas + espacio desc
$totalesH = 20;
$footerH = 18;
$margin = 5;
$calcH = $headerH + $infoH + $lineasH + $totalesH + $footerH + ($margin * 2) + 10;
$pageH = max(120, $calcH);

$pdf = new TCPDF('P', 'mm', array($pageW, $pageH), true, 'UTF-8', false);

// Configurar
$pdf->SetCreator('El Celler Nou');
$pdf->SetAuthor('El Celler Nou');
$pdf->SetTitle('Tiquet #' . str_pad($id, 6, '0', STR_PAD_LEFT));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins($margin, $margin, $margin);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

$w = $pageW - ($margin * 2); // ancho útil
$x = $margin;
$y = $margin;

// === CABECERA EMPRESA ===
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetXY($x, $y);
$pdf->Cell($w, 6, $empresa['nombre'], 0, 1, 'C');
$y += 6;

$pdf->SetFont('helvetica', '', 7);
$pdf->SetXY($x, $y);
$pdf->Cell($w, 3.5, $empresa['direccion'], 0, 1, 'C');
$y += 3.5;
$pdf->SetXY($x, $y);
$pdf->Cell($w, 3.5, $empresa['ciudad'], 0, 1, 'C');
$y += 3.5;
$pdf->SetXY($x, $y);
$pdf->Cell($w, 3.5, 'Tel: ' . $empresa['telefono'], 0, 1, 'C');
$y += 3.5;
$pdf->SetXY($x, $y);
$pdf->Cell($w, 3.5, 'CIF: ' . $empresa['cif'], 0, 1, 'C');
$y += 5;

// Línea separadora
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineStyle(array('dash' => '2,2'));
$pdf->Line($x, $y, $x + $w, $y);
$y += 2;

// === INFO TIQUET ===
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetXY($x, $y);
$pdf->Cell($w / 2, 4, 'Tiquet: #' . str_pad($id, 6, '0', STR_PAD_LEFT), 0, 0, 'L');
$pdf->Cell($w / 2, 4, date('d/m/Y', strtotime($tiquet['fecha'])), 0, 1, 'R');
$y += 4;

$pdf->SetFont('helvetica', '', 8);
$pdf->SetXY($x, $y);
$pdf->Cell($w, 4, 'Hora: ' . date('H:i', strtotime($tiquet['fecha'])), 0, 1, 'L');
$y += 4;

if (!empty($tiquet['cliente_nombre'])) {
    $pdf->SetXY($x, $y);
    $pdf->Cell($w, 4, 'Client: ' . $tiquet['cliente_nombre'], 0, 1, 'L');
    $y += 4;
}

$pdf->SetXY($x, $y);
$pdf->Cell($w, 4, 'Pagament: ' . ($tipoPagoTexto[$tiquet['tipo_pago']] ?? $tiquet['tipo_pago']), 0, 1, 'L');
$y += 5;

// Línea separadora
$pdf->SetLineStyle(array('dash' => '2,2'));
$pdf->Line($x, $y, $x + $w, $y);
$y += 2;

// === CABECERA LÍNEAS ===
$colCant = 10;
$colProd = $w - 10 - 14 - 16;
$colPrecio = 14;
$colTotal = 16;

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetXY($x, $y);
$pdf->Cell($colCant, 4, 'Ut.', 0, 0, 'L');
$pdf->Cell($colProd, 4, 'Producte', 0, 0, 'L');
$pdf->Cell($colPrecio, 4, 'Preu', 0, 0, 'R');
$pdf->Cell($colTotal, 4, 'Total', 0, 1, 'R');
$y += 4;

// Línea sólida
$pdf->SetLineStyle(array('dash' => 0));
$pdf->Line($x, $y, $x + $w, $y);
$y += 1.5;

// === LÍNEAS DE PRODUCTO ===
$pdf->SetFont('helvetica', '', 7.5);
foreach ($lineas as $linea) {
    $nombre = $linea['producto_nombre'] ?? 'Producte #' . $linea['idProducto'];
    // Truncar nombre si es muy largo
    if (mb_strlen($nombre) > 28) {
        $nombre = mb_substr($nombre, 0, 26) . '..';
    }

    $pdf->SetXY($x, $y);
    $pdf->Cell($colCant, 4, number_format($linea['cantidad'], 2, ',', ''), 0, 0, 'L');
    $pdf->Cell($colProd, 4, $nombre, 0, 0, 'L');
    // Preu i import de tarifa: el descompte es resta a la línia de sota.
    $pdf->Cell($colPrecio, 4, number_format($linea['precio_tarifa'], 2, ',', '.'), 0, 0, 'R');
    $pdf->Cell($colTotal, 4, number_format($linea['subtotal_bruto'], 2, ',', '.'), 0, 1, 'R');
    $y += 4;

    if ($linea['descuento'] > 0) {
        $pdf->SetFont('helvetica', '', 6.5);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($x + 3, $y);
        $pdf->Cell($w - 3, 3.5, 'Dto ' . number_format($linea['descuento'], 0) . '%: -' . number_format($linea['subtotal_descuento'], 2, ',', '.') . ' EUR = ' . number_format($linea['subtotal'], 2, ',', '.') . ' EUR', 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 7.5);
        $y += 3.5;
    }
}

$y += 1;

// === TOTALES ===
$pdf->SetLineStyle(array('dash' => '2,2'));
$pdf->Line($x, $y, $x + $w, $y);
$y += 2;

if ($totalDescuento > 0) {
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY($x, $y);
    $pdf->Cell($w / 2, 4, 'Subtotal:', 0, 0, 'L');
    $pdf->Cell($w / 2, 4, number_format($totalBruto, 2, ',', '.') . ' EUR', 0, 1, 'R');
    $y += 4;

    $pdf->SetXY($x, $y);
    $pdf->Cell($w / 2, 4, 'Descompte:', 0, 0, 'L');
    $pdf->Cell($w / 2, 4, '-' . number_format($totalDescuento, 2, ',', '.') . ' EUR', 0, 1, 'R');
    $y += 5;
}

// Total final
$pdf->SetLineStyle(array('dash' => 0, 'width' => 0.4));
$pdf->Line($x, $y, $x + $w, $y);
$y += 2;

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetXY($x, $y);
$pdf->Cell($w / 2, 6, 'TOTAL', 0, 0, 'L');
$pdf->Cell($w / 2, 6, number_format($totalNeto, 2, ',', '.') . ' EUR', 0, 1, 'R');
$y += 8;

// === PIE ===
$pdf->SetLineStyle(array('dash' => '2,2', 'width' => 0.2));
$pdf->Line($x, $y, $x + $w, $y);
$y += 3;

$pdf->SetFont('helvetica', '', 7);
$pdf->SetXY($x, $y);
$pdf->Cell($w, 3.5, 'Gràcies per la seva compra!', 0, 1, 'C');
$y += 3.5;
$pdf->SetXY($x, $y);
$pdf->Cell($w, 3.5, 'IVA inclòs', 0, 1, 'C');
$y += 5;

$pdf->SetFont('helvetica', '', 6);
$pdf->SetXY($x, $y);
$pdf->Cell($w, 3, date('d/m/Y H:i:s'), 0, 1, 'C');

// Output
$filename = 'tiquet_' . str_pad($tiquet['id'], 6, '0', STR_PAD_LEFT) . '.pdf';
$pdf->Output($filename, 'I'); // I = inline (mostrar en navegador)
