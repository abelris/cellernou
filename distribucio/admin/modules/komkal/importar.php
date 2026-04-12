<?php
/**
 * El Celler Nou - Mòdul Komkal - Importar XLSX
 */
$pageTitle = 'Komkal - Importar';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/config.php';
requireAuth();

require_once __DIR__ . '/lib/XlsxReader.php';

$mensaje = '';
$tipoMensaje = '';

/**
 * Run auto-matching on a komkal table.
 * Returns array with counts: ['codigo' => N, 'fuzzy' => N]
 */
function runAutoMatch(string $table, $bd): array {
    $counts = ['codigo' => 0, 'fuzzy' => 0];
    $tableEsc = ($table === 'vinos') ? 'komkal_vinos' : 'komkal_licores';

    // Phase 1: Code match
    $sql = "UPDATE $tableEsc k
            JOIN productos p ON k.codigo_komkal = p.codigo AND p.codigo != '' AND p.codigo IS NOT NULL
            SET k.id_producto_match = p.id, k.match_tipo = 'auto_codigo'
            WHERE k.id_producto_match IS NULL AND p.activo = 1";
    mysqli_query($bd, $sql);
    $counts['codigo'] = mysqli_affected_rows($bd);

    // Phase 2: Fuzzy name match
    $unmatched = [];
    $r = mysqli_query($bd, "SELECT id, nombre FROM $tableEsc WHERE id_producto_match IS NULL");
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $unmatched[] = $row;
        }
    }

    if (!empty($unmatched)) {
        // Load active products
        $sectorFilter = ($table === 'vinos') ? 8 : 2;
        $productos = [];
        $rp = mysqli_query($bd, "SELECT id, nombre FROM productos WHERE activo = 1");
        if ($rp) {
            while ($row = mysqli_fetch_assoc($rp)) {
                $productos[] = $row;
            }
        }

        foreach ($unmatched as $komkal) {
            $normK = normalizeForMatch($komkal['nombre']);
            if (strlen($normK) < 3) continue;

            $bestId = null;
            $bestScore = 0;
            $ambiguous = false;

            foreach ($productos as $prod) {
                $normP = normalizeForMatch($prod['nombre']);
                if (strlen($normP) < 3) continue;

                $score = 0;
                similar_text($normK, $normP, $score);

                if ($score >= 75) {
                    if ($bestId === null || $score > $bestScore + 2) {
                        $bestId = $prod['id'];
                        $bestScore = $score;
                        $ambiguous = false;
                    } elseif (abs($score - $bestScore) <= 2 && $prod['id'] !== $bestId) {
                        $ambiguous = true;
                    }
                }
            }

            if ($bestId !== null && !$ambiguous) {
                // Check not already linked to another komkal entry
                $stmt = mysqli_prepare($bd, "UPDATE $tableEsc SET id_producto_match = ?, match_tipo = 'auto_nom' WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'ii', $bestId, $komkal['id']);
                mysqli_stmt_execute($stmt);
                if (mysqli_affected_rows($bd) > 0) {
                    $counts['fuzzy']++;
                }
            }
        }
    }

    return $counts;
}

/**
 * Normalize a product name for fuzzy comparison.
 */
function normalizeForMatch(string $name): string {
    $name = mb_strtoupper($name, 'UTF-8');
    // Remove accents
    $accents = ['À','Á','Â','Ã','Ä','Å','È','É','Ê','Ë','Ì','Í','Î','Ï','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ñ','Ç'];
    $plain   = ['A','A','A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','N','C'];
    $name = str_replace($accents, $plain, $name);
    // Remove volume suffixes
    $name = preg_replace('/\b(75|70|37\.5|50)\s*CL\b/i', '', $name);
    $name = preg_replace('/\b1\s*L\b/i', '', $name);
    $name = preg_replace('/\b1\.5\s*L\b/i', '', $name);
    // Remove extra whitespace
    $name = preg_replace('/\s+/', ' ', trim($name));
    return $name;
}

// Process POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $mensaje = 'Error de seguretat. Torna a intentar-ho.';
        $tipoMensaje = 'error';
    } else {
        $lote = trim($_POST['lote'] ?? date('Ymd'));
        if (empty($lote)) {
            $lote = date('Ymd');
        }
        $loteSafe = mysqli_real_escape_string($bd, $lote);
        $source = $_POST['source'] ?? 'upload'; // 'upload' or 'server'

        $countVinos = 0;
        $countLicores = 0;
        $matchCounts = ['codigo' => 0, 'fuzzy' => 0];
        $errors = [];

        // Determine file paths
        $vinosPath = null;
        $licoresPath = null;

        if ($source === 'server') {
            $serverDir = $_SERVER['DOCUMENT_ROOT'] . '/cellernou/importacionkomkal/' . $lote . '/';
            // Also try without cellernou prefix for production
            if (!is_dir($serverDir)) {
                $serverDir = $_SERVER['DOCUMENT_ROOT'] . '/importacionkomkal/' . $lote . '/';
            }
            // Also try distribucio-relative
            if (!is_dir($serverDir)) {
                $serverDir = dirname(__DIR__, 3) . '/importacionkomkal/' . $lote . '/';
            }

            if (!is_dir($serverDir)) {
                $errors[] = "No s'ha trobat el directori del servidor: importacionkomkal/$lote/";
            } else {
                if (file_exists($serverDir . 'vinos.xlsx')) {
                    $vinosPath = $serverDir . 'vinos.xlsx';
                }
                if (file_exists($serverDir . 'licores.xlsx')) {
                    $licoresPath = $serverDir . 'licores.xlsx';
                }
                if (!$vinosPath && !$licoresPath) {
                    $errors[] = "No s'han trobat fitxers vinos.xlsx ni licores.xlsx a: importacionkomkal/$lote/";
                }
            }
        } else {
            // Upload
            if (!empty($_FILES['vinos']['tmp_name']) && $_FILES['vinos']['error'] === UPLOAD_ERR_OK) {
                $vinosPath = $_FILES['vinos']['tmp_name'];
            }
            if (!empty($_FILES['licores']['tmp_name']) && $_FILES['licores']['error'] === UPLOAD_ERR_OK) {
                $licoresPath = $_FILES['licores']['tmp_name'];
            }

            if (!$vinosPath && !$licoresPath) {
                $errors[] = 'Has de seleccionar almenys un fitxer XLSX.';
            }
        }

        if (empty($errors)) {
            // Clear ALL previous data before importing (lote is just a label).
            // Permanent matches survive in komkal_matches and get re-applied after insert.
            if ($vinosPath) {
                mysqli_query($bd, "TRUNCATE TABLE komkal_vinos");
            }
            if ($licoresPath) {
                mysqli_query($bd, "TRUNCATE TABLE komkal_licores");
            }

            // Import vinos
            if ($vinosPath) {
                try {
                    $rows = XlsxReader::read($vinosPath);
                    $stmt = mysqli_prepare($bd, "INSERT INTO komkal_vinos
                        (codigo_komkal, nombre, precio_sin_iva, disponible, url, lote, fecha_importacion)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())");

                    foreach ($rows as $row) {
                        $codigo = trim($row['B'] ?? '');
                        $nombre = XlsxReader::cleanName($row['C'] ?? '');
                        $precio = XlsxReader::parsePrice($row['E'] ?? '');
                        $disponible = (stripos($row['F'] ?? '', 'DISPONIBLE') !== false) ? 1 : 0;
                        $url = trim($row['A'] ?? '');

                        if (empty($nombre) && empty($codigo)) continue;

                        mysqli_stmt_bind_param($stmt, 'ssdiss',
                            $codigo, $nombre, $precio, $disponible, $url, $lote
                        );
                        if (mysqli_stmt_execute($stmt)) {
                            $countVinos++;
                        }
                    }
                    mysqli_stmt_close($stmt);
                } catch (Exception $ex) {
                    $errors[] = 'Error processant vinos.xlsx: ' . $ex->getMessage();
                }
            }

            // Import licores
            if ($licoresPath) {
                try {
                    $rows = XlsxReader::read($licoresPath);
                    $stmt = mysqli_prepare($bd, "INSERT INTO komkal_licores
                        (codigo_komkal, nombre, precio_sin_iva, disponible, url, lote, fecha_importacion)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())");

                    foreach ($rows as $row) {
                        $codigo = trim($row['B'] ?? '');
                        $nombre = XlsxReader::cleanName($row['C'] ?? '');
                        $precio = XlsxReader::parsePrice($row['D'] ?? '');
                        $disponible = (stripos($row['E'] ?? '', 'DISPONIBLE') !== false) ? 1 : 0;
                        $url = trim($row['A'] ?? '');

                        if (empty($nombre) && empty($codigo)) continue;

                        mysqli_stmt_bind_param($stmt, 'ssdiss',
                            $codigo, $nombre, $precio, $disponible, $url, $lote
                        );
                        if (mysqli_stmt_execute($stmt)) {
                            $countLicores++;
                        }
                    }
                    mysqli_stmt_close($stmt);
                } catch (Exception $ex) {
                    $errors[] = 'Error processant licores.xlsx: ' . $ex->getMessage();
                }
            }

            // Apply permanent matches from komkal_matches (before fuzzy auto-match)
            $matchCounts['permanent'] = 0;
            if ($countVinos > 0) {
                mysqli_query($bd, "UPDATE komkal_vinos k
                    JOIN komkal_matches m ON k.codigo_komkal = m.codigo_komkal AND m.tipo_catalogo = 'vinos'
                    SET k.id_producto_match = m.id_producto, k.match_tipo = m.match_tipo
                    WHERE k.id_producto_match IS NULL");
                $matchCounts['permanent'] += mysqli_affected_rows($bd);
            }
            if ($countLicores > 0) {
                mysqli_query($bd, "UPDATE komkal_licores k
                    JOIN komkal_matches m ON k.codigo_komkal = m.codigo_komkal AND m.tipo_catalogo = 'licores'
                    SET k.id_producto_match = m.id_producto, k.match_tipo = m.match_tipo
                    WHERE k.id_producto_match IS NULL");
                $matchCounts['permanent'] += mysqli_affected_rows($bd);
            }

            // Run auto-matching (fuzzy, for remaining unmatched)
            if ($countVinos > 0) {
                $mc = runAutoMatch('vinos', $bd);
                $matchCounts['codigo'] += $mc['codigo'];
                $matchCounts['fuzzy'] += $mc['fuzzy'];
            }
            if ($countLicores > 0) {
                $mc = runAutoMatch('licores', $bd);
                $matchCounts['codigo'] += $mc['codigo'];
                $matchCounts['fuzzy'] += $mc['fuzzy'];
            }
        }

        if (!empty($errors)) {
            $mensaje = implode('<br>', array_map('htmlspecialchars', $errors));
            $tipoMensaje = 'error';
        } else {
            $totalImported = $countVinos + $countLicores;
            $totalMatched = $matchCounts['permanent'] + $matchCounts['codigo'] + $matchCounts['fuzzy'];
            logActivity('komkal_import', "Lot: $lote, Vins: $countVinos, Licors: $countLicores, Match permanents: {$matchCounts['permanent']}, Match codi: {$matchCounts['codigo']}, Match nom: {$matchCounts['fuzzy']}");
            $mensaje = "Importació completada (Lot: <strong>" . e($lote) . "</strong>):<br>"
                . "- Vins: <strong>$countVinos</strong> registres<br>"
                . "- Licors: <strong>$countLicores</strong> registres<br>"
                . "- Match permanents: <strong>{$matchCounts['permanent']}</strong><br>"
                . "- Auto-match per codi: <strong>{$matchCounts['codigo']}</strong><br>"
                . "- Auto-match per nom: <strong>{$matchCounts['fuzzy']}</strong>";
            $tipoMensaje = 'success';
        }
    }
}

$csrfToken = generateCsrfToken();

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo getAdminUrl('modules/komkal/'); ?>" class="btn btn-secondary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tornar
    </a>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipoMensaje; ?>" style="margin-bottom: 1rem;">
    <?php echo $mensaje; ?>
</div>
<?php endif; ?>

<!-- Upload form -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Pujar Fitxers XLSX</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="source" value="upload">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="vinos">Fitxer Vins (.xlsx)</label>
                    <input type="file" id="vinos" name="vinos" class="form-input" accept=".xlsx">
                </div>
                <div class="form-group">
                    <label class="form-label" for="licores">Fitxer Licors (.xlsx)</label>
                    <input type="file" id="licores" name="licores" class="form-input" accept=".xlsx">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="lote">Lot (nom del batch)</label>
                    <input type="text" id="lote" name="lote" class="form-input"
                           value="<?php echo date('Ymd'); ?>" placeholder="Ex: 20260410">
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <p style="font-size:0.8rem; color:var(--gray-500); padding:0.625rem 0;">Les dades anteriors del mateix lot es substituiran automàticament.</p>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Importar
            </button>
        </form>
    </div>
</div>

<!-- Server import -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">O importar des del servidor</h3>
    </div>
    <div class="card-body">
        <p style="font-size:0.875rem; color:var(--gray-500); margin-bottom:1rem;">
            Posa els fitxers <code>vinos.xlsx</code> i/o <code>licores.xlsx</code> al directori
            <code>importacionkomkal/{lot}/</code> del servidor.
        </p>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="source" value="server">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="lote_server">Lot (nom del directori)</label>
                    <input type="text" id="lote_server" name="lote" class="form-input"
                           value="<?php echo date('Ymd'); ?>" placeholder="Ex: 20260410">
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end;">
                    <p style="font-size:0.8rem; color:var(--gray-500); padding:0.625rem 0;">Les dades anteriors del mateix lot es substituiran automàticament.</p>
                </div>
            </div>

            <button type="submit" class="btn btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
                Importar des del servidor
            </button>
        </form>
    </div>
</div>

<style>
.form-group { margin-bottom: 0; }
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
.alert {
    padding: 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    line-height: 1.6;
}
.alert-success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}
.alert-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
