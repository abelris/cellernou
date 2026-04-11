<?php
/**
 * El Celler Nou - Generar Venda PDF (TCPDF8)
 * Format A4 professional amb desglossament IVA
 * Basat en el format de factures
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/tcpdf8/tcpdf.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: ' . getAdminUrl('modules/vendes/'));
    exit;
}

// Obtener venta con datos del cliente
$sql = "SELECT v.*, c.nombre as cliente_nombre, c.nombre_fiscal, c.nif, c.direccion as cliente_direccion,
        c.cp, c.poblacion, c.provincia, c.telefono as cliente_telefono, c.email as cliente_email,
        c.profesional, c.recarrec_equiv
        FROM ventas v
        LEFT JOIN clientes c ON v.idCliente = c.id
        WHERE v.id = $id";
$result = mysqli_query($bd, $sql);
$venta = $result ? mysqli_fetch_assoc($result) : null;

if (!$venta) {
    header('Location: ' . getAdminUrl('modules/vendes/'));
    exit;
}

// Obtener líneas
$lineas = [];
$totalBase = 0;
$ivaBreakdown = [];

$sql = "SELECT lv.*, p.nombre as producto_nombre, p.codigo
        FROM linea_venta lv
        LEFT JOIN productos p ON lv.idProducto = p.id
        WHERE lv.idVenta = $id
        ORDER BY lv.id";
$result = mysqli_query($bd, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $subtotal = $row['cantidad'] * $row['precio_unidad'];
        $row['subtotal'] = $subtotal;
        $totalBase += $subtotal;

        $ivaRate = (int)$row['iva'];
        if (!isset($ivaBreakdown[$ivaRate])) {
            $ivaBreakdown[$ivaRate] = ['base' => 0, 'iva' => 0, 'recarrec' => 0];
        }
        $ivaBreakdown[$ivaRate]['base'] += $subtotal;
        $ivaBreakdown[$ivaRate]['iva'] += $subtotal * ($ivaRate / 100);

        if ($venta['recarrec_equiv']) {
            $recarrecRates = [4 => 0.005, 10 => 0.014, 21 => 0.052];
            if (isset($recarrecRates[$ivaRate])) {
                $ivaBreakdown[$ivaRate]['recarrec'] += $subtotal * $recarrecRates[$ivaRate];
            }
        }

        $lineas[] = $row;
    }
}

$totalIva = array_sum(array_column($ivaBreakdown, 'iva'));
$totalRecarrec = array_sum(array_column($ivaBreakdown, 'recarrec'));
$totalVenta = $totalBase + $totalIva + $totalRecarrec;

// Número albarán
$numAlbaran = $venta['albaran'] ?: 'V-' . str_pad($id, 6, '0', STR_PAD_LEFT);

// Datos empresa
$empresa = [
    'nombre' => 'EL CELLER NOU',
    'razon' => 'ALMABRIS GROUP 2024 S.L.',
    'cif' => 'B66318684',
    'direccion' => 'C/ Ramon Llull, 15',
    'ciudad' => '08330 Premià de Mar (Barcelona)',
    'telefono' => '93 752 02 71',
    'email' => 'cellernou@cellernou.com'
];

// ============================================================
// CREAR PDF A4
// ============================================================
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('El Celler Nou');
$pdf->SetAuthor('El Celler Nou');
$pdf->SetTitle('Venda ' . $numAlbaran);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

$w = 180; // ancho útil (210 - 15*2)
$x = 15;
$y = 15;

// Colores
$colorPrimary = [114, 47, 55]; // var(--primary) - vino
$colorDark = [30, 30, 30];
$colorGray = [120, 120, 120];
$colorLight = [240, 240, 240];
$colorLine = [200, 200, 200];

// ============================================================
// CABECERA - Empresa y datos venda
// ============================================================

// Nombre empresa grande
$pdf->SetFont('helvetica', 'B', 22);
$pdf->SetTextColor($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]);
$pdf->SetXY($x, $y);
$pdf->Cell(90, 10, $empresa['nombre'], 0, 0, 'L');

// Etiqueta ALBARÀ DE VENDA
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor($colorDark[0], $colorDark[1], $colorDark[2]);
$pdf->Cell(90, 10, utf8_encode('ALBARÀ DE VENDA'), 0, 1, 'R');
$y += 10;

// Datos empresa
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor($colorGray[0], $colorGray[1], $colorGray[2]);
$pdf->SetXY($x, $y);
$pdf->Cell(90, 4, $empresa['razon'], 0, 1, 'L');
$y += 4;
$pdf->SetXY($x, $y);
$pdf->Cell(90, 4, 'CIF: ' . $empresa['cif'], 0, 1, 'L');
$y += 4;
$pdf->SetXY($x, $y);
$pdf->Cell(90, 4, $empresa['direccion'] . ' - ' . $empresa['ciudad'], 0, 1, 'L');
$y += 4;
$pdf->SetXY($x, $y);
$pdf->Cell(90, 4, 'Tel: ' . $empresa['telefono'] . ' | ' . $empresa['email'], 0, 1, 'L');
$y += 8;

// Línea separadora decorativa
$pdf->SetDrawColor($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]);
$pdf->SetLineWidth(0.8);
$pdf->Line($x, $y, $x + $w, $y);
$y += 5;

// ============================================================
// DATOS VENDA + DATOS CLIENTE (dos columnas)
// ============================================================

$colLeft = $x;
$colRight = $x + 100;

// Caja datos venda
$pdf->SetFillColor($colorLight[0], $colorLight[1], $colorLight[2]);
$pdf->RoundedRect($colLeft, $y, 90, 22, 2, '1111', 'F');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]);
$pdf->SetXY($colLeft + 4, $y + 2);
$pdf->Cell(82, 5, 'DADES DE LA VENDA', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 8.5);
$pdf->SetTextColor($colorDark[0], $colorDark[1], $colorDark[2]);

$yData = $y + 8;
$pdf->SetXY($colLeft + 4, $yData);
$pdf->SetFont('helvetica', 'B', 8.5);
$pdf->Cell(25, 5, utf8_encode('N. Albarà:'), 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8.5);
$pdf->Cell(57, 5, $numAlbaran, 0, 1, 'L');
$yData += 5;

$pdf->SetXY($colLeft + 4, $yData);
$pdf->SetFont('helvetica', 'B', 8.5);
$pdf->Cell(25, 5, 'Data:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8.5);
$pdf->Cell(57, 5, date('d/m/Y', strtotime($venta['fecha'])), 0, 1, 'L');
$yData += 5;

$estadoLabels = [0 => 'Pendent', 1 => 'Falten productes', 2 => 'Preparat', 3 => 'Per recollir', 4 => 'Entregat', 5 => 'Facturat'];
$pdf->SetXY($colLeft + 4, $yData);
$pdf->SetFont('helvetica', 'B', 8.5);
$pdf->Cell(25, 5, 'Estat:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 8.5);
$pdf->Cell(57, 5, $estadoLabels[$venta['idEstado']] ?? '-', 0, 1, 'L');

// Caja datos cliente
$clientBoxH = 22;
$pdf->SetFillColor($colorLight[0], $colorLight[1], $colorLight[2]);
$pdf->RoundedRect($colRight, $y, 80, $clientBoxH, 2, '1111', 'F');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]);
$pdf->SetXY($colRight + 4, $y + 2);
$pdf->Cell(72, 5, 'DADES DEL CLIENT', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 8.5);
$pdf->SetTextColor($colorDark[0], $colorDark[1], $colorDark[2]);

$yClient = $y + 8;

$clientName = $venta['nombre_fiscal'] ?: $venta['cliente_nombre'];
if ($clientName) {
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetXY($colRight + 4, $yClient);
    $pdf->Cell(72, 5, $clientName, 0, 1, 'L');
    $yClient += 5;

    $pdf->SetFont('helvetica', '', 8.5);
    if ($venta['nif']) {
        $pdf->SetXY($colRight + 4, $yClient);
        $pdf->Cell(72, 5, 'NIF: ' . $venta['nif'], 0, 1, 'L');
        $yClient += 5;
    }

    if ($venta['cliente_direccion']) {
        $pdf->SetXY($colRight + 4, $yClient);
        $pdf->Cell(72, 5, $venta['cliente_direccion'], 0, 1, 'L');
        $yClient += 5;
    }

    $cityLine = trim(($venta['cp'] ?? '') . ' ' . ($venta['poblacion'] ?? ''));
    if ($venta['provincia']) $cityLine .= ' (' . $venta['provincia'] . ')';
    if (trim($cityLine)) {
        $pdf->SetXY($colRight + 4, $yClient);
        $pdf->Cell(72, 5, $cityLine, 0, 1, 'L');
    }
} else {
    $pdf->SetFont('helvetica', 'I', 8.5);
    $pdf->SetXY($colRight + 4, $yClient);
    $pdf->Cell(72, 5, 'Client general', 0, 1, 'L');
}

$y += $clientBoxH + 6;

// ============================================================
// TABLA DE LÍNEAS
// ============================================================

// Cabecera tabla
$pdf->SetFillColor($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 8);

$colNum = 10;
$colProd = 68;
$colCant = 18;
$colPrecio = 22;
$colDesc = 16;
$colIva = 14;
$colTotal = 32;

$pdf->SetXY($x, $y);
$pdf->Cell($colNum, 7, '#', 0, 0, 'C', true);
$pdf->Cell($colProd, 7, 'Producte', 0, 0, 'L', true);
$pdf->Cell($colCant, 7, 'Qty', 0, 0, 'R', true);
$pdf->Cell($colPrecio, 7, 'Preu/u', 0, 0, 'R', true);
$pdf->Cell($colDesc, 7, 'Dto%', 0, 0, 'R', true);
$pdf->Cell($colIva, 7, 'IVA', 0, 0, 'R', true);
$pdf->Cell($colTotal, 7, 'Total', 0, 1, 'R', true);
$y += 7;

// Líneas de producto
$pdf->SetTextColor($colorDark[0], $colorDark[1], $colorDark[2]);
$pdf->SetFont('helvetica', '', 8);
$altRow = false;

foreach ($lineas as $i => $linea) {
    // Salto de página si necesario
    if ($y > 245) {
        $pdf->AddPage();
        $y = 20;
    }

    if ($altRow) {
        $pdf->SetFillColor(248, 248, 248);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }

    $nombre = $linea['producto_nombre'] ?? 'Producte #' . $linea['idProducto'];
    if (mb_strlen($nombre) > 45) $nombre = mb_substr($nombre, 0, 43) . '..';

    $descText = $linea['descuento'] > 0 ? number_format($linea['descuento'], 0) . '%' : '';

    $pdf->SetXY($x, $y);
    $pdf->Cell($colNum, 6, $i + 1, 0, 0, 'C', true);
    $pdf->Cell($colProd, 6, $nombre, 0, 0, 'L', true);
    $pdf->Cell($colCant, 6, number_format($linea['cantidad'], 2, ',', ''), 0, 0, 'R', true);
    $pdf->Cell($colPrecio, 6, number_format($linea['precio_unidad_sin_desc'] ?: $linea['precio_unidad'], 3, ',', '.'), 0, 0, 'R', true);
    $pdf->Cell($colDesc, 6, $descText, 0, 0, 'R', true);
    $pdf->Cell($colIva, 6, number_format($linea['iva'], 0) . '%', 0, 0, 'R', true);
    $pdf->Cell($colTotal, 6, number_format($linea['subtotal'], 2, ',', '.') . ' €', 0, 1, 'R', true);
    $y += 6;

    // Línea separadora sutil
    $pdf->SetDrawColor($colorLine[0], $colorLine[1], $colorLine[2]);
    $pdf->SetLineWidth(0.1);
    $pdf->Line($x, $y, $x + $w, $y);

    $altRow = !$altRow;
}

$y += 4;

// ============================================================
// DESGLOSSAMENT IVA (tabla)
// ============================================================

if ($y > 230) {
    $pdf->AddPage();
    $y = 20;
}

// Caja desglossament IVA (a la izquierda)
$ivaBoxW = 110;
$totalBoxW = 65;

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]);
$pdf->SetXY($x, $y);
$pdf->Cell($ivaBoxW, 5, 'DESGLOSSAMENT IVA', 0, 1, 'L');
$y += 6;

// Cabecera tabla IVA
$pdf->SetFillColor($colorLight[0], $colorLight[1], $colorLight[2]);
$pdf->SetFont('helvetica', 'B', 7.5);
$pdf->SetTextColor($colorDark[0], $colorDark[1], $colorDark[2]);
$pdf->SetXY($x, $y);
$pdf->Cell(20, 5, 'Tipus', 0, 0, 'L', true);
$pdf->Cell(30, 5, 'Base', 0, 0, 'R', true);
$pdf->Cell(30, 5, 'Quota', 0, 0, 'R', true);
if ($venta['recarrec_equiv']) {
    $pdf->Cell(30, 5, utf8_encode('Recàrrec'), 0, 0, 'R', true);
}
$y += 5;

$pdf->SetFont('helvetica', '', 7.5);
krsort($ivaBreakdown); // Mostrar de mayor a menor

foreach ($ivaBreakdown as $rate => $data) {
    if ($data['base'] <= 0) continue;

    $pdf->SetXY($x, $y);
    $pdf->Cell(20, 5, 'IVA ' . $rate . '%', 0, 0, 'L');
    $pdf->Cell(30, 5, number_format($data['base'], 2, ',', '.') . ' €', 0, 0, 'R');
    $pdf->Cell(30, 5, number_format($data['iva'], 2, ',', '.') . ' €', 0, 0, 'R');
    if ($venta['recarrec_equiv']) {
        $recarrecPct = [4 => '0,5%', 10 => '1,4%', 21 => '5,2%'];
        $label = isset($recarrecPct[$rate]) ? number_format($data['recarrec'], 2, ',', '.') . ' €' : '-';
        $pdf->Cell(30, 5, $label, 0, 0, 'R');
    }
    $y += 5;
}

$y += 2;

// ============================================================
// TOTALES (caja a la derecha)
// ============================================================

$totalBoxX = $x + $w - $totalBoxW;
$totalBoxY = $y - (count(array_filter($ivaBreakdown, function($d) { return $d['base'] > 0; })) * 5) - 13;

// Fondo del recuadro de totales
$totalBoxH = 30;
if ($venta['recarrec_equiv'] && $totalRecarrec > 0) $totalBoxH += 6;

$pdf->SetFillColor($colorLight[0], $colorLight[1], $colorLight[2]);
$pdf->RoundedRect($totalBoxX, $totalBoxY, $totalBoxW, $totalBoxH, 2, '1111', 'F');

$yTot = $totalBoxY + 3;
$innerW = $totalBoxW - 8;

$pdf->SetFont('helvetica', '', 8.5);
$pdf->SetTextColor($colorDark[0], $colorDark[1], $colorDark[2]);

$pdf->SetXY($totalBoxX + 4, $yTot);
$pdf->Cell($innerW / 2, 5, 'Base Imposable:', 0, 0, 'L');
$pdf->Cell($innerW / 2, 5, number_format($totalBase, 2, ',', '.') . ' €', 0, 1, 'R');
$yTot += 5;

$pdf->SetXY($totalBoxX + 4, $yTot);
$pdf->Cell($innerW / 2, 5, 'IVA:', 0, 0, 'L');
$pdf->Cell($innerW / 2, 5, number_format($totalIva, 2, ',', '.') . ' €', 0, 1, 'R');
$yTot += 5;

if ($venta['recarrec_equiv'] && $totalRecarrec > 0) {
    $pdf->SetXY($totalBoxX + 4, $yTot);
    $pdf->Cell($innerW / 2, 5, utf8_encode('Recàrrec Eq.:'), 0, 0, 'L');
    $pdf->Cell($innerW / 2, 5, number_format($totalRecarrec, 2, ',', '.') . ' €', 0, 1, 'R');
    $yTot += 5;
}

// Línea total
$pdf->SetDrawColor($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]);
$pdf->SetLineWidth(0.5);
$pdf->Line($totalBoxX + 4, $yTot, $totalBoxX + $totalBoxW - 4, $yTot);
$yTot += 2;

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]);
$pdf->SetXY($totalBoxX + 4, $yTot);
$pdf->Cell($innerW / 2, 7, 'TOTAL:', 0, 0, 'L');
$pdf->Cell($innerW / 2, 7, number_format($totalVenta, 2, ',', '.') . ' €', 0, 1, 'R');

// ============================================================
// PIE
// ============================================================

$y = max($y, $yTot + 12);

// Comentarios
if (!empty($venta['comentarios'])) {
    if ($y > 265) {
        $pdf->AddPage();
        $y = 20;
    }

    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor($colorGray[0], $colorGray[1], $colorGray[2]);
    $pdf->SetXY($x, $y);
    $pdf->MultiCell($w, 4, 'Observacions: ' . $venta['comentarios'], 0, 'L');
    $y += 8;
}

// Mensaje final
$footerY = 275;
$pdf->SetDrawColor($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]);
$pdf->SetLineWidth(0.3);
$pdf->Line($x, $footerY - 3, $x + $w, $footerY - 3);

$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor($colorGray[0], $colorGray[1], $colorGray[2]);
$pdf->SetXY($x, $footerY);
$pdf->Cell($w / 2, 4, $empresa['razon'] . ' - CIF: ' . $empresa['cif'], 0, 0, 'L');
$pdf->Cell($w / 2, 4, utf8_encode('Pàgina ') . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages(), 0, 1, 'R');

// ============================================================
// OUTPUT
// ============================================================
$filename = 'venda_' . $numAlbaran . '.pdf';
$pdf->Output($filename, 'I');
