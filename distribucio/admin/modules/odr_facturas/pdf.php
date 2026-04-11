<?php
/**
 * OnDigitalRis - Generació de factura en PDF (TCPDF8)
 * A4 portrait, electric blue #0066FF branding
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth_odr.php';
require_once dirname(dirname(dirname(__DIR__))) . '/tcpdf8/tcpdf.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die('ID de factura no vàlid.');
}

// Load invoice + client
$stmt = $bd->prepare("
    SELECT f.*, c.nombre as cliente_nombre, c.nombre_fiscal, c.nif as cliente_nif,
           c.direccion as cliente_direccion, c.cp as cliente_cp, c.poblacion as cliente_poblacion,
           c.provincia as cliente_provincia, c.telefono as cliente_telefono, c.email as cliente_email
    FROM odr_facturas f
    LEFT JOIN odr_clientes c ON f.idCliente = c.id
    WHERE f.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$factura = $result->fetch_assoc();
$stmt->close();

if (!$factura) {
    die('Factura no trobada.');
}

// Load lines
$stmt = $bd->prepare("SELECT * FROM odr_linea_factura WHERE idFactura = ? ORDER BY id");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$lineas = [];
while ($row = $result->fetch_assoc()) {
    $lineas[] = $row;
}
$stmt->close();

// Calculate totals
$base_total = 0;
$iva_total = 0;
foreach ($lineas as &$linea) {
    $linea['subtotal'] = $linea['cantidad'] * $linea['precio_unidad'];
    $linea['iva_importe'] = $linea['subtotal'] * $linea['iva'] / 100;
    $base_total += $linea['subtotal'];
    $iva_total += $linea['iva_importe'];
}
unset($linea);
$total_final = $base_total + $iva_total;

// Format helpers
function fDate($date) {
    if (empty($date) || $date === '0000-00-00') return '-';
    return date('d/m/Y', strtotime($date));
}

function fMoney($amount) {
    return number_format($amount, 2, ',', '.') . ' €';
}

// Electric blue RGB
$blue_r = 0;
$blue_g = 102;
$blue_b = 255;

// ─── Create PDF ───
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Document info
$pdf->SetCreator('ondigitalris');
$pdf->SetAuthor('ALMABRIS GROUP 2024 S.L.');
$pdf->SetTitle('Factura ' . $factura['numero']);

// Disable default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Margins: 15mm all sides
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 25);

$pdf->AddPage();

$pageW = $pdf->getPageWidth() - 30; // 180mm usable

// ═══════════════════════════════════════════
// HEADER AREA
// ═══════════════════════════════════════════
$headerY = 15;

// Left side: brand + company details
$pdf->SetXY(15, $headerY);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor($blue_r, $blue_g, $blue_b);
$pdf->Cell(90, 7, 'ondigitalris', 0, 1, 'L');

$pdf->SetXY(15, $headerY + 8);
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(90, 4,
    "ALMABRIS GROUP 2024 S.L.\n" .
    "CIF: B66318684\n" .
    "C/ Ramón Llull, 15\n" .
    "08330 Premià de Mar (Barcelona)\n" .
    "Tel: 93 752 02 71\n" .
    "info@ondigitalris.com",
    0, 'L', false
);

// Right side: FACTURA title + number + dates
$pdf->SetXY(120, $headerY);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor($blue_r, $blue_g, $blue_b);
$pdf->Cell(75, 8, 'FACTURA', 0, 1, 'R');

$pdf->SetXY(120, $headerY + 10);
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(33, 33, 33);
$pdf->Cell(75, 6, $factura['numero'], 0, 1, 'R');

$pdf->SetXY(120, $headerY + 18);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(75, 5, 'Data: ' . fDate($factura['fecha']), 0, 1, 'R');

$pdf->SetXY(120, $headerY + 23);
$pdf->Cell(75, 5, 'Venciment: ' . fDate($factura['fecha_vencimiento']), 0, 1, 'R');

// Blue separator line
$separatorY = $headerY + 34;
$pdf->SetDrawColor($blue_r, $blue_g, $blue_b);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $separatorY, 195, $separatorY);

// ═══════════════════════════════════════════
// CLIENT BOX
// ═══════════════════════════════════════════
$clientY = $separatorY + 4;

// Background fill
$pdf->SetFillColor(235, 242, 255); // #EBF2FF
$pdf->Rect(15, $clientY, $pageW, 28, 'F');

// Blue left border
$pdf->SetFillColor($blue_r, $blue_g, $blue_b);
$pdf->Rect(15, $clientY, 1.2, 28, 'F');

// Client text
$pdf->SetXY(19, $clientY + 2);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(33, 33, 33);
$pdf->Cell(80, 5, 'Client:', 0, 1, 'L');

$clientName = $factura['nombre_fiscal'] ?: $factura['cliente_nombre'];
$pdf->SetXY(19, $clientY + 7);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(160, 5, $clientName, 0, 1, 'L');

$clientDetails = '';
if (!empty($factura['cliente_nif'])) {
    $clientDetails .= 'NIF: ' . $factura['cliente_nif'] . "\n";
}
if (!empty($factura['cliente_direccion'])) {
    $clientDetails .= $factura['cliente_direccion'] . "\n";
}
$cityLine = '';
if (!empty($factura['cliente_cp'])) $cityLine .= $factura['cliente_cp'] . ' ';
if (!empty($factura['cliente_poblacion'])) $cityLine .= $factura['cliente_poblacion'];
if (!empty($factura['cliente_provincia'])) $cityLine .= ' (' . $factura['cliente_provincia'] . ')';
if (!empty($cityLine)) $clientDetails .= $cityLine;

$pdf->SetXY(19, $clientY + 13);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->MultiCell(160, 4, trim($clientDetails), 0, 'L', false);

// ═══════════════════════════════════════════
// LINES TABLE
// ═══════════════════════════════════════════
$tableY = $clientY + 34;
$pdf->SetY($tableY);

// Column widths
$colW = [85, 20, 25, 20, 30]; // Concepte, Quantitat, Preu, IVA, Subtotal

// Table header
$pdf->SetFillColor($blue_r, $blue_g, $blue_b);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetDrawColor($blue_r, $blue_g, $blue_b);

$x = 15;
$headers = ['Concepte', 'Quantitat', 'Preu/Ut.', 'IVA', 'Subtotal'];
$aligns = ['L', 'C', 'R', 'C', 'R'];
for ($i = 0; $i < 5; $i++) {
    $pdf->SetXY($x, $pdf->GetY());
    $pdf->Cell($colW[$i], 8, $headers[$i], 0, 0, $aligns[$i], true);
    $x += $colW[$i];
}
$pdf->Ln();

// Table rows
$pdf->SetFont('helvetica', '', 9);
$pdf->SetDrawColor(220, 220, 220);

foreach ($lineas as $idx => $linea) {
    // Check page break
    if ($pdf->GetY() + 8 > $pdf->getPageHeight() - 30) {
        $pdf->AddPage();
        $pdf->SetY(15);

        // Reprint header on new page
        $pdf->SetFillColor($blue_r, $blue_g, $blue_b);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 9);
        $x = 15;
        for ($i = 0; $i < 5; $i++) {
            $pdf->SetXY($x, $pdf->GetY());
            $pdf->Cell($colW[$i], 8, $headers[$i], 0, 0, $aligns[$i], true);
            $x += $colW[$i];
        }
        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 9);
    }

    // Alternating row colors
    if ($idx % 2 === 0) {
        $pdf->SetFillColor(255, 255, 255);
    } else {
        $pdf->SetFillColor(248, 249, 252);
    }

    $pdf->SetTextColor(33, 33, 33);
    $rowY = $pdf->GetY();

    // Calculate row height based on concept text
    $conceptHeight = $pdf->getStringHeight($colW[0], $linea['concepto']);
    $rowH = max(7, $conceptHeight);

    // Draw row background
    $pdf->Rect(15, $rowY, $pageW, $rowH, 'F');

    // Bottom border
    $pdf->Line(15, $rowY + $rowH, 195, $rowY + $rowH);

    // Concepte (MultiCell for wrapping)
    $pdf->SetXY(15, $rowY);
    $pdf->MultiCell($colW[0], $rowH, $linea['concepto'], 0, 'L', false);

    // Quantitat
    $pdf->SetXY(15 + $colW[0], $rowY);
    $pdf->Cell($colW[1], $rowH, number_format($linea['cantidad'], 2, ',', '.'), 0, 0, 'C');

    // Preu/Ut.
    $pdf->SetXY(15 + $colW[0] + $colW[1], $rowY);
    $pdf->Cell($colW[2], $rowH, number_format($linea['precio_unidad'], 2, ',', '.') . ' €', 0, 0, 'R');

    // IVA
    $pdf->SetXY(15 + $colW[0] + $colW[1] + $colW[2], $rowY);
    $pdf->Cell($colW[3], $rowH, number_format($linea['iva'], 0) . '%', 0, 0, 'C');

    // Subtotal
    $pdf->SetXY(15 + $colW[0] + $colW[1] + $colW[2] + $colW[3], $rowY);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell($colW[4], $rowH, number_format($linea['subtotal'], 2, ',', '.') . ' €', 0, 0, 'R');
    $pdf->SetFont('helvetica', '', 9);

    $pdf->SetY($rowY + $rowH);
}

// ═══════════════════════════════════════════
// TOTALS SECTION (right-aligned)
// ═══════════════════════════════════════════
$totalsY = $pdf->GetY() + 6;

// Check space for totals + footer
if ($totalsY + 40 > $pdf->getPageHeight() - 25) {
    $pdf->AddPage();
    $totalsY = 15;
}

$totalsX = 15 + $pageW - 70; // Right-aligned, 70mm wide
$labelW = 40;
$valueW = 30;

// Base imponible
$pdf->SetXY($totalsX, $totalsY);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell($labelW, 6, 'Base imponible:', 0, 0, 'L');
$pdf->SetTextColor(33, 33, 33);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($valueW, 6, fMoney($base_total), 0, 1, 'R');

// IVA
$pdf->SetXY($totalsX, $totalsY + 7);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell($labelW, 6, 'IVA:', 0, 0, 'L');
$pdf->SetTextColor(33, 33, 33);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($valueW, 6, fMoney($iva_total), 0, 1, 'R');

// Blue line before total
$pdf->SetDrawColor($blue_r, $blue_g, $blue_b);
$pdf->SetLineWidth(0.6);
$pdf->Line($totalsX, $totalsY + 15, $totalsX + $labelW + $valueW, $totalsY + 15);

// TOTAL
$pdf->SetXY($totalsX, $totalsY + 17);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(33, 33, 33);
$pdf->Cell($labelW, 7, 'TOTAL:', 0, 0, 'L');
$pdf->SetTextColor($blue_r, $blue_g, $blue_b);
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell($valueW, 7, fMoney($total_final), 0, 1, 'R');

// ═══════════════════════════════════════════
// PAYMENT & STATUS
// ═══════════════════════════════════════════
$payY = $totalsY + 30;
$pdf->SetXY(15, $payY);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);

$formaPago = !empty($factura['forma_pago']) ? $factura['forma_pago'] : '-';
$estado = !empty($factura['estado']) ? ucfirst($factura['estado']) : 'Pendent';

$pdf->Cell(90, 6, 'Forma de pagament: ' . $formaPago, 0, 0, 'L');
$pdf->Cell(90, 6, 'Estat: ' . $estado, 0, 1, 'R');

// Notes
if (!empty($factura['notas'])) {
    $notesY = $payY + 10;
    if ($notesY + 15 > $pdf->getPageHeight() - 25) {
        $pdf->AddPage();
        $notesY = 15;
    }
    $pdf->SetXY(15, $notesY);
    $pdf->SetFillColor(255, 253, 240);
    $pdf->SetDrawColor(240, 192, 64);
    $pdf->Rect(15, $notesY, $pageW, 12, 'F');
    $pdf->SetFillColor(240, 192, 64);
    $pdf->Rect(15, $notesY, 1, 12, 'F');
    $pdf->SetXY(18, $notesY + 1);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(102, 85, 0);
    $pdf->Cell(20, 4, 'Notes:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY(18, $notesY + 5);
    $pdf->MultiCell($pageW - 6, 4, $factura['notas'], 0, 'L', false);
}

// ═══════════════════════════════════════════
// FOOTER
// ═══════════════════════════════════════════
$footerY = $pdf->getPageHeight() - 18;
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetLineWidth(0.3);
$pdf->Line(15, $footerY, 195, $footerY);

$pdf->SetXY(15, $footerY + 2);
$pdf->SetFont('helvetica', '', 7.5);
$pdf->SetTextColor(160, 160, 160);
$pdf->Cell($pageW, 5, 'ondigitalris · ALMABRIS GROUP 2024 S.L. · CIF: B66318684', 0, 0, 'C');

// ─── Output ───
$filename = 'factura_' . $factura['numero'] . '.pdf';
$pdf->Output($filename, 'I');
