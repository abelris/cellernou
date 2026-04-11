<?php
/**
 * El Celler Nou - OCR Factures de Proveïdor
 * Upload + OCR (Tesseract.js/PDF.js) + Formulari de revisió + Guardar a BD
 */

require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(dirname(__DIR__))) . '/connexio/connexio.php';

$pageTitle = 'OCR Factures';
$csrfToken = generateCsrfToken();

// Obtener formas de pago
$formasPago = [];
$result = mysqli_query($bd, "SELECT * FROM formas_pago ORDER BY id");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $formasPago[] = $row;
    }
}

// Error de sesión (de guardar.php)
$sessionError = $_SESSION['ocr_error'] ?? '';
unset($_SESSION['ocr_error']);

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<style>
.ocr-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; min-height: 600px; }
.ocr-preview { background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; position: relative; overflow: hidden; min-height: 600px; }
.ocr-preview.has-file { border-style: solid; border-color: #e5e7eb; }
.ocr-preview canvas, .ocr-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
.ocr-form { display: flex; flex-direction: column; gap: 1rem; }
.upload-zone { cursor: pointer; padding: 3rem; text-align: center; transition: all 0.2s; }
.upload-zone:hover, .upload-zone.dragover { background: #eff6ff; border-color: #3b82f6; }
.upload-zone svg { width: 48px; height: 48px; margin: 0 auto 1rem; color: #9ca3af; }
.form-group { margin-bottom: 0.75rem; }
.form-group label { display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; color: #374151; }
.form-group input, .form-group select { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #722F37; box-shadow: 0 0 0 2px rgba(114,47,55,0.15); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.lineas-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.lineas-table th { background: #f3f4f6; padding: 0.5rem; text-align: left; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
.lineas-table td { padding: 0.35rem 0.5rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.lineas-table input { padding: 0.35rem 0.5rem; font-size: 0.8rem; border: 1px solid #d1d5db; border-radius: 4px; width: 100%; }
.lineas-table input.prod-search { min-width: 180px; }
.lineas-table input[type="number"] { width: 75px; }
.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border: none; border-radius: 6px; font-weight: 600; font-size: 0.875rem; cursor: pointer; transition: all 0.15s; }
.btn-primary { background: #722F37; color: white; }
.btn-primary:hover { background: #5a252c; }
.btn-primary:disabled { background: #9ca3af; cursor: not-allowed; }
.btn-sm { padding: 0.3rem 0.6rem; font-size: 0.75rem; }
.btn-success { background: #059669; color: white; }
.btn-danger { background: #dc2626; color: white; }
.btn-secondary { background: #6b7280; color: white; }
.totals-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; background: #f9fafb; padding: 1rem; border-radius: 6px; margin-top: 0.5rem; }
.totals-row .total-item { text-align: center; }
.totals-row .total-label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600; }
.totals-row .total-value { font-size: 1.25rem; font-weight: 700; color: #111827; }
.ocr-progress { display: none; padding: 1rem; text-align: center; }
.ocr-progress .bar { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; margin: 0.5rem 0; }
.ocr-progress .bar-fill { height: 100%; background: #722F37; border-radius: 3px; transition: width 0.3s; width: 0%; }
.autocomplete-list { position: absolute; z-index: 100; background: white; border: 1px solid #d1d5db; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-height: 200px; overflow-y: auto; width: 100%; }
.autocomplete-list .ac-item { padding: 0.5rem 0.75rem; cursor: pointer; font-size: 0.85rem; border-bottom: 1px solid #f3f4f6; }
.autocomplete-list .ac-item:hover, .autocomplete-list .ac-item.active { background: #eff6ff; }
.autocomplete-list .ac-item small { color: #6b7280; }
.pos-relative { position: relative; }
.ocr-text-panel { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; max-height: 200px; overflow-y: auto; padding: 0.75rem; font-size: 0.75rem; font-family: monospace; white-space: pre-wrap; color: #4b5563; display: none; }
.pdf-nav { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; background: #f3f4f6; border-radius: 6px; margin-bottom: 0.5rem; }
.pdf-nav button { padding: 0.25rem 0.5rem; border: 1px solid #d1d5db; background: white; border-radius: 4px; cursor: pointer; font-size: 0.8rem; }
.pdf-nav span { font-size: 0.8rem; color: #6b7280; }
.alert { padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.section-title { font-size: 1rem; font-weight: 700; color: #374151; margin: 0.5rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #722F37; }
.ocr-desc-hint { font-size: 0.7rem; color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-radius: 3px; padding: 2px 6px; margin-bottom: 3px; display: block; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px; }
.ocr-desc-hint:before { content: 'OCR: '; font-weight: 700; }
.linea-assigned { background: #f0fdf4; }
.linea-assigned .ocr-desc-hint { background: #dcfce7; border-color: #86efac; color: #166534; }
.ocr-prov-panel { border: 1px solid #e5e7eb; border-radius: 6px; padding: 0.75rem; margin-bottom: 0.75rem; font-size: 0.85rem; }
.ocr-prov-panel.detected { background: #fffbeb; border-color: #fde68a; }
.ocr-prov-panel.matched { background: #f0fdf4; border-color: #86efac; }
.ocr-prov-panel .ocr-prov-label { font-weight: 700; font-size: 0.75rem; text-transform: uppercase; color: #92400e; margin-bottom: 0.35rem; }
.ocr-prov-panel.matched .ocr-prov-label { color: #166534; }
.ocr-prov-panel .ocr-prov-value { font-family: monospace; background: white; padding: 2px 6px; border-radius: 3px; display: inline-block; margin: 2px 0; }
.ocr-prov-panel .ocr-prov-actions { margin-top: 0.5rem; display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
.ocr-prov-panel .ocr-prov-actions .btn { font-size: 0.75rem; padding: 0.25rem 0.6rem; }
.ocr-prov-saved { display: inline-block; color: #166534; font-weight: 600; font-size: 0.8rem; }
.ocr-config-panel { border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem; margin-bottom: 1rem; background: #f8fafc; }
.ocr-config-panel .section-title { margin-top: 0; }
.ocr-config-preview { overflow-x: auto; margin: 0.75rem 0; border: 1px solid #d1d5db; border-radius: 4px; max-height: 220px; overflow-y: auto; }
.ocr-config-preview table { width: 100%; border-collapse: collapse; font-size: 0.75rem; font-family: monospace; }
.ocr-config-preview th { background: #e5e7eb; padding: 0.3rem 0.5rem; font-weight: 700; text-align: center; position: sticky; top: 0; }
.ocr-config-preview td { padding: 0.25rem 0.5rem; border-bottom: 1px solid #f3f4f6; white-space: nowrap; }
.ocr-config-preview tr:hover td { background: #eff6ff; }
.ocr-config-preview .col-selected { background: #dbeafe; font-weight: 600; }
.ocr-config-fields { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-top: 0.75rem; }
.ocr-config-fields label { font-size: 0.75rem; font-weight: 600; color: #374151; display: block; margin-bottom: 0.15rem; }
.ocr-config-fields select, .ocr-config-fields input { width: 100%; padding: 0.35rem 0.5rem; font-size: 0.8rem; border: 1px solid #d1d5db; border-radius: 4px; }
.ocr-config-actions { margin-top: 0.75rem; display: flex; gap: 0.5rem; align-items: center; }
.ocr-config-ok { color: #166534; font-weight: 600; font-size: 0.85rem; display: none; }
.ocr-map-saved { display: inline-block; color: #166534; font-weight: 600; font-size: 0.7rem; background: #dcfce7; border-radius: 3px; padding: 1px 5px; margin-left: 4px; animation: ocrFadeIn 0.3s; }
@keyframes ocrFadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
.linea-auto-matched { background: #eff6ff; }
.linea-auto-matched .ocr-desc-hint { background: #dbeafe; border-color: #93c5fd; color: #1e40af; }
</style>

<?php if ($sessionError): ?>
<div class="alert alert-error"><?= $sessionError ?></div>
<?php endif; ?>

<div class="ocr-layout" id="ocrLayout" style="display: none;">
    <!-- Columna esquerra: Preview -->
    <div class="ocr-preview has-file" id="previewPanel">
        <div class="pdf-nav" id="pdfNav" style="display:none;">
            <button onclick="pdfPrevPage()">&laquo; Anterior</button>
            <span id="pdfPageInfo">1 / 1</span>
            <button onclick="pdfNextPage()">Següent &raquo;</button>
        </div>
        <canvas id="previewCanvas"></canvas>
        <img id="previewImg" style="display:none;" />
    </div>

    <!-- Columna dreta: Formulari -->
    <div class="ocr-form">
        <div class="ocr-progress" id="ocrProgress">
            <div class="bar"><div class="bar-fill" id="ocrBar"></div></div>
            <small id="ocrStatus">Iniciant OCR...</small>
        </div>

        <form method="POST" action="<?= e(getAdminUrl('modules/ocr/guardar.php')) ?>" id="invoiceForm">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <div class="section-title">Dades de la factura</div>

            <!-- Panel detecció OCR proveïdor -->
            <div class="ocr-prov-panel detected" id="ocrProvPanel" style="display:none;">
                <div class="ocr-prov-label" id="ocrProvLabel">Dades detectades per OCR</div>
                <div id="ocrProvDetected"></div>
                <div class="ocr-prov-actions" id="ocrProvActions">
                    <span style="font-size:0.8rem;color:#6b7280;">Associar amb:</span>
                    <div class="pos-relative" style="flex:1;min-width:200px;">
                        <input type="text" id="ocrProvAssocSearch" placeholder="Cerca proveïdor per associar..." autocomplete="off" style="padding:0.35rem 0.6rem;font-size:0.8rem;border:1px solid #d1d5db;border-radius:4px;width:100%;">
                        <div class="autocomplete-list" id="ocrProvAssocList" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <!-- Proveïdor -->
            <div class="form-group pos-relative">
                <label>Proveïdor *</label>
                <input type="hidden" name="idProveedor" id="idProveedor" required>
                <input type="text" id="proveedorSearch" placeholder="Cerca proveïdor..." autocomplete="off">
                <div class="autocomplete-list" id="proveedorList" style="display:none;"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Núm. Factura *</label>
                    <input type="text" name="numFactura" id="numFactura" required>
                </div>
                <div class="form-group">
                    <label>Forma de Pagament</label>
                    <select name="idFormaPago" id="idFormaPago">
                        <option value="0">-- Seleccionar --</option>
                        <?php foreach ($formasPago as $fp): ?>
                        <option value="<?= (int)$fp['id'] ?>"><?= e($fp['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Data emissió</label>
                    <input type="date" name="fechaEmision" id="fechaEmision">
                </div>
                <div class="form-group">
                    <label>Data venciment</label>
                    <input type="date" name="fechaVencimiento" id="fechaVencimiento">
                </div>
            </div>

            <!-- Configuració columnes OCR per proveïdor -->
            <div class="ocr-config-panel" id="ocrConfigPanel" style="display:none;">
                <div class="section-title">Configuració de columnes OCR</div>
                <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.5rem;">Indica quina columna correspon a cada camp. Aquesta configuració es guardarà per aquest proveïdor.</p>

                <!-- Delimitadors de línies de producte -->
                <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:180px;">
                        <label style="font-size:0.75rem;font-weight:600;color:#374151;display:block;margin-bottom:0.15rem;">Línia inici productes</label>
                        <input type="text" id="cfgLineaInicio" placeholder="Ex: DESCRIPCIÓN, Concepto..."
                               style="width:100%;padding:0.35rem 0.5rem;font-size:0.8rem;border:1px solid #d1d5db;border-radius:4px;">
                    </div>
                    <div style="flex:1;min-width:180px;">
                        <label style="font-size:0.75rem;font-weight:600;color:#374151;display:block;margin-bottom:0.15rem;">Línia final productes</label>
                        <input type="text" id="cfgLineaFin" placeholder="Ex: BASE IMPONIBLE, Total..."
                               style="width:100%;padding:0.35rem 0.5rem;font-size:0.8rem;border:1px solid #d1d5db;border-radius:4px;">
                    </div>
                </div>

                <!-- Separador de columnes + umbral gap + botó re-parsejar -->
                <div style="display:flex;gap:0.5rem;align-items:end;margin-bottom:0.5rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:180px;">
                        <label style="font-size:0.75rem;font-weight:600;color:#374151;display:block;margin-bottom:0.15rem;">Separador de columnes</label>
                        <select id="cfgColSep" style="width:100%;padding:0.35rem 0.5rem;font-size:0.8rem;border:1px solid #d1d5db;border-radius:4px;">
                            <option value="auto">Automàtic</option>
                            <option value="pipe">| (pipe - distància PDF)</option>
                            <option value="tab">Tabuladors</option>
                            <option value="2spaces">2+ espais</option>
                            <option value="1space">Qualsevol espai</option>
                        </select>
                    </div>
                    <div style="flex:1;min-width:120px;">
                        <label style="font-size:0.75rem;font-weight:600;color:#374151;display:block;margin-bottom:0.15rem;">Dist. mín. columna (px)</label>
                        <input type="number" id="cfgColGapPx" value="-1" min="-1" step="1"
                               placeholder="Auto" style="width:100%;padding:0.35rem 0.5rem;font-size:0.8rem;border:1px solid #d1d5db;border-radius:4px;">
                        <small style="color:#9ca3af;font-size:0.7rem;">-1 = automàtic</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="reparseOcrLines()" style="white-space:nowrap;">&#x21bb; Re-parsejar</button>
                </div>

                <div class="ocr-config-preview" id="ocrConfigPreview">
                    <!-- La taula de preview de columnes OCR es genera per JS -->
                </div>
                <div class="ocr-config-fields">
                    <div>
                        <label>Col. Referència</label>
                        <select id="cfgColRef"><option value="-1">-- No usar --</option></select>
                    </div>
                    <div>
                        <label>Col. Nom producte</label>
                        <select id="cfgColNom"><option value="-1">-- No usar --</option></select>
                    </div>
                    <div>
                        <label>Col. Quantitat</label>
                        <select id="cfgColQty"><option value="-1">-- No usar --</option></select>
                    </div>
                    <div>
                        <label>Col. Preu unitari</label>
                        <select id="cfgColPreu"><option value="-1">-- No usar --</option></select>
                    </div>
                    <div>
                        <label>Col. IVA %</label>
                        <select id="cfgColIva"><option value="-1">-- No usar (21%) --</option></select>
                    </div>
                    <div>
                        <label>Signe decimal</label>
                        <select id="cfgDecSep">
                            <option value=",">, (coma)</option>
                            <option value=".">. (punt)</option>
                        </select>
                    </div>
                    <div>
                        <label>Signe milers</label>
                        <select id="cfgMilSep">
                            <option value=".">. (punt)</option>
                            <option value=",">, (coma)</option>
                            <option value=""> (cap)</option>
                        </select>
                    </div>
                </div>
                <div class="ocr-config-actions">
                    <button type="button" class="btn btn-sm btn-primary" id="btnSaveConfig" onclick="saveAndApplyConfig()">Guardar configuració i aplicar</button>
                    <span class="ocr-config-ok" id="cfgSavedMsg">&#10003; Configuració guardada</span>
                </div>
            </div>

            <!-- Línies -->
            <div class="section-title" style="margin-top:1rem;">Línies de compra</div>
            <div style="overflow-x: auto;">
                <table class="lineas-table" id="lineasTable">
                    <thead>
                        <tr>
                            <th>Producte</th>
                            <th style="width:70px">Qty</th>
                            <th style="width:85px">Preu</th>
                            <th style="width:60px">Dto%</th>
                            <th style="width:60px">IVA%</th>
                            <th style="width:85px">Subtotal</th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody id="lineasBody">
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addLinea()" style="margin-top:0.5rem;">+ Afegir línia</button>

            <!-- Totals -->
            <div class="totals-row">
                <div class="total-item">
                    <div class="total-label">Base</div>
                    <div class="total-value" id="totalBase">0,00 &euro;</div>
                </div>
                <div class="total-item">
                    <div class="total-label">IVA</div>
                    <div class="total-value" id="totalIva">0,00 &euro;</div>
                </div>
                <div class="total-item">
                    <div class="total-label">Total</div>
                    <div class="total-value" id="totalTotal">0,00 &euro;</div>
                </div>
            </div>

            <!-- OCR Text -->
            <div style="margin-top:1rem;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleOcrText()">Mostrar/Amagar text OCR</button>
                <div class="ocr-text-panel" id="ocrTextPanel"></div>
            </div>

            <div style="margin-top:1rem; display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary" id="btnGuardar" disabled>
                    <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Guardar Factura
                </button>
                <a href="<?= e(getAdminUrl('modules/ocr/dist/')) ?>" class="btn btn-secondary" target="_blank">
                    Obrir app Excel/CSV
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Upload zone (visible inicialment) -->
<div class="ocr-preview" id="uploadZone">
    <div class="upload-zone" id="dropZone">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        <p style="font-size:1.1rem;font-weight:600;color:#374151;">Arrossega un PDF o imatge aquí</p>
        <p style="color:#9ca3af;font-size:0.85rem;margin-top:0.5rem;">o fes clic per seleccionar un fitxer</p>
        <input type="file" id="fileInput" accept=".pdf,.png,.jpg,.jpeg,.webp,.tiff,.bmp" style="display:none;">
    </div>
</div>

<!-- IMPORTANT: Prevent browser from opening dropped files (separate script, always runs) -->
<script>
document.addEventListener('dragover', function(e) { e.preventDefault(); });
document.addEventListener('drop', function(e) { e.preventDefault(); });
</script>

<!-- Tesseract.js v5 (global UMD script) -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<!-- Main app logic (plain script, not module - won't die if CDN fails) -->
<script>
(async function() {
'use strict';

// --- Load PDF.js dynamically ---
var pdfjsLib = null;
try {
    var mod = await import('https://cdn.jsdelivr.net/npm/pdfjs-dist@4.9.155/build/pdf.min.mjs');
    pdfjsLib = mod;
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.9.155/build/pdf.worker.min.mjs';
    console.log('PDF.js loaded OK');
} catch(e) {
    console.warn('PDF.js CDN load failed, trying fallback...', e);
    try {
        var mod2 = await import('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.mjs');
        pdfjsLib = mod2;
        pdfjsLib.GlobalWorkerOptions.workerSrc = '<?= getAdminUrl("modules/ocr/dist/assets/pdf.worker.min.mjs") ?>';
        console.log('PDF.js loaded from fallback');
    } catch(e2) {
        console.error('PDF.js failed completely:', e2);
    }
}

var API_URL = '<?= getAdminUrl("modules/ocr/api.php") ?>';

// State
var pdfDoc = null;
var currentPage = 1;
var totalPages = 0;
var ocrText = '';
var lastOcrResultData = []; // array de Tesseract data objects (un per pàgina) per re-build
var lastPdfTextItems = [];  // array de {items, page} per PDFs amb text embegut

// DOM refs
var uploadZone = document.getElementById('uploadZone');
var dropZone = document.getElementById('dropZone');
var fileInput = document.getElementById('fileInput');
var ocrLayout = document.getElementById('ocrLayout');
var previewCanvas = document.getElementById('previewCanvas');
var previewImg = document.getElementById('previewImg');
var pdfNav = document.getElementById('pdfNav');
var pdfPageInfo = document.getElementById('pdfPageInfo');
var ocrProgress = document.getElementById('ocrProgress');
var ocrBar = document.getElementById('ocrBar');
var ocrStatus = document.getElementById('ocrStatus');
var ocrTextPanel = document.getElementById('ocrTextPanel');
var btnGuardar = document.getElementById('btnGuardar');

// --- Upload ---
dropZone.addEventListener('click', function() { fileInput.click(); });
fileInput.addEventListener('change', function(e) { if (e.target.files[0]) handleFile(e.target.files[0]); });

dropZone.addEventListener('dragover', function(e) { e.preventDefault(); e.stopPropagation(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', function() { dropZone.classList.remove('dragover'); });
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
});

async function handleFile(file) {
    uploadZone.style.display = 'none';
    ocrLayout.style.display = 'grid';
    ocrProgress.style.display = 'block';

    if (file.type === 'application/pdf') {
        if (!pdfjsLib) {
            ocrStatus.textContent = 'Error: No s\'ha pogut carregar PDF.js. Prova amb una imatge.';
            return;
        }
        await handlePdf(file);
    } else {
        await handleImage(file);
    }
}

/**
 * Reconstruir text d'un PDF preservant línies i marcant columnes amb | .
 * PDF.js retorna items amb posició (transform[4]=x, transform[5]=y) i mida (width, height).
 * Agrupem per línia (y similar) i quan el gap horitzontal entre dos fragments de text
 * és significativament major que un espai normal, inserim " | " com a separador de columna.
 */
function buildTextFromPdfItems(items, page, customThreshold) {
    if (!items || items.length === 0) return '';

    // Filtrar items buits
    var validItems = items.filter(function(it) { return it.str && it.str.trim().length > 0; });
    if (validItems.length === 0) return '';

    // Calcular alçada mitjana per determinar llindar de línia
    var totalHeight = 0, heightCount = 0;
    validItems.forEach(function(it) {
        if (it.height > 0) { totalHeight += it.height; heightCount++; }
    });
    var avgHeight = heightCount > 0 ? totalHeight / heightCount : 10;
    var lineThreshold = avgHeight * 0.6;

    // Calcular amplada mitjana d'un caràcter
    var totalWidth = 0, totalChars = 0;
    validItems.forEach(function(it) {
        if (it.width > 0 && it.str.length > 0) {
            totalWidth += it.width;
            totalChars += it.str.length;
        }
    });
    var avgCharWidth = totalChars > 0 ? totalWidth / totalChars : 5;

    // Recollir TOTS els gaps intra-línia per calcular un llindar adaptatiu
    // Primer: agrupar per línia i calcular gaps
    var sorted = validItems.slice().sort(function(a, b) {
        var ay = a.transform ? a.transform[5] : 0;
        var by = b.transform ? b.transform[5] : 0;
        if (Math.abs(ay - by) > lineThreshold) return by - ay;
        return (a.transform ? a.transform[4] : 0) - (b.transform ? b.transform[4] : 0);
    });

    var lineGroups = [];
    var curLine = [];
    var curY = null;
    sorted.forEach(function(item) {
        var y = item.transform ? item.transform[5] : 0;
        if (curY === null || Math.abs(y - curY) > lineThreshold) {
            if (curLine.length > 0) lineGroups.push(curLine);
            curLine = [item];
            curY = y;
        } else {
            curLine.push(item);
        }
    });
    if (curLine.length > 0) lineGroups.push(curLine);

    // Recollir tots els gaps entre items consecutius d'una mateixa línia
    var allGaps = [];
    lineGroups.forEach(function(lineItems) {
        lineItems.sort(function(a, b) {
            return (a.transform ? a.transform[4] : 0) - (b.transform ? b.transform[4] : 0);
        });
        for (var j = 1; j < lineItems.length; j++) {
            var prevEnd = (lineItems[j-1].transform ? lineItems[j-1].transform[4] : 0) + (lineItems[j-1].width || 0);
            var currStart = lineItems[j].transform ? lineItems[j].transform[4] : 0;
            var gap = currStart - prevEnd;
            if (gap > 0) allGaps.push(gap);
        }
    });

    // Determinar llindar: un gap de columna és significativament més gran que un gap de paraula.
    var colThreshold;
    if (customThreshold && customThreshold > 0) {
        colThreshold = customThreshold;
    } else {
        colThreshold = avgCharWidth * 2;
        if (allGaps.length > 2) {
            allGaps.sort(function(a, b) { return a - b; });
            var median = allGaps[Math.floor(allGaps.length / 2)];
            if (median < avgCharWidth * 1.5) {
                colThreshold = Math.max(avgCharWidth * 2, median * 3);
            } else {
                colThreshold = median * 1.8;
            }
        }
    }

    // Construir text amb " | " per gaps grans i " " per gaps normals
    var result = [];
    lineGroups.forEach(function(lineItems) {
        lineItems.sort(function(a, b) {
            return (a.transform ? a.transform[4] : 0) - (b.transform ? b.transform[4] : 0);
        });

        var lineText = '';
        for (var j = 0; j < lineItems.length; j++) {
            if (j > 0) {
                var prevEnd = (lineItems[j-1].transform ? lineItems[j-1].transform[4] : 0) + (lineItems[j-1].width || 0);
                var currStart = lineItems[j].transform ? lineItems[j].transform[4] : 0;
                var gap = currStart - prevEnd;
                if (gap >= colThreshold) {
                    lineText += ' | ';
                } else if (gap > avgCharWidth * 0.3) {
                    lineText += ' ';
                }
            }
            lineText += lineItems[j].str;
        }
        if (lineText.trim()) result.push(lineText);
    });

    return result.join('\n');
}

// --- PDF handling ---
async function handlePdf(file) {
    var arrayBuffer = await file.arrayBuffer();
    pdfDoc = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    totalPages = pdfDoc.numPages;
    currentPage = 1;

    if (totalPages > 1) pdfNav.style.display = 'flex';
    updatePageInfo();
    await renderPage(currentPage);

    // Try text layer extraction first (embedded text)
    ocrStatus.textContent = 'Extraient text del PDF...';
    var extractedText = '';
    lastPdfTextItems = [];
    for (var i = 1; i <= totalPages; i++) {
        var page = await pdfDoc.getPage(i);
        var textContent = await page.getTextContent();
        lastPdfTextItems.push({ items: textContent.items, page: page });
        var pageText = buildTextFromPdfItems(textContent.items, page);
        if (pageText.trim()) extractedText += pageText + '\n';
    }

    if (extractedText.trim().length > 50) {
        ocrText = extractedText;
        lastOcrResultData = []; // clear since we used PDF path
        ocrBar.style.width = '100%';
        ocrStatus.textContent = 'Text extret del PDF (sense OCR)';
        finishOcr();
    } else {
        lastPdfTextItems = []; // clear since we'll use Tesseract OCR path
        ocrStatus.textContent = 'PDF sense text, iniciant OCR...';
        await ocrAllPages();
    }
}

async function renderPage(num) {
    var page = await pdfDoc.getPage(num);
    var scale = 1.5;
    var viewport = page.getViewport({ scale: scale });
    previewCanvas.width = viewport.width;
    previewCanvas.height = viewport.height;
    previewCanvas.style.display = 'block';
    previewImg.style.display = 'none';
    await page.render({ canvasContext: previewCanvas.getContext('2d'), viewport: viewport }).promise;
}

function updatePageInfo() { pdfPageInfo.textContent = currentPage + ' / ' + totalPages; }
window.pdfPrevPage = async function() { if (currentPage > 1) { currentPage--; updatePageInfo(); await renderPage(currentPage); } };
window.pdfNextPage = async function() { if (currentPage < totalPages) { currentPage++; updatePageInfo(); await renderPage(currentPage); } };

async function ocrAllPages() {
    await waitForTesseract();
    var worker = await window.Tesseract.createWorker('spa+cat', 1, {
        logger: function(m) {
            if (m.status === 'recognizing text') {
                var pct = Math.round(m.progress * 100);
                ocrBar.style.width = pct + '%';
                ocrStatus.textContent = 'OCR pàgina... ' + pct + '%';
            }
        }
    });

    var fullText = '';
    lastOcrResultData = [];
    for (var i = 1; i <= totalPages; i++) {
        ocrStatus.textContent = 'OCR pàgina ' + i + ' de ' + totalPages + '...';
        var page = await pdfDoc.getPage(i);
        var scale = 2;
        var viewport = page.getViewport({ scale: scale });
        var tmpCanvas = document.createElement('canvas');
        tmpCanvas.width = viewport.width;
        tmpCanvas.height = viewport.height;
        await page.render({ canvasContext: tmpCanvas.getContext('2d'), viewport: viewport }).promise;
        var result = await worker.recognize(tmpCanvas);
        lastOcrResultData.push(result.data);
        fullText += buildTextFromOcrResult(result.data) + '\n';
    }

    await worker.terminate();
    ocrText = fullText;
    ocrBar.style.width = '100%';
    ocrStatus.textContent = 'OCR completat';
    finishOcr();
}

/**
 * Reconstruir text des del resultat OCR de Tesseract usant bounding boxes.
 * Tesseract retorna data.lines[].words[] amb bbox {x0, y0, x1, y1}.
 * Detectem gaps grans entre paraules i inserim " | " com a separador de columna.
 */
function buildTextFromOcrResult(data, customThreshold) {
    if (!data || !data.lines || data.lines.length === 0) return data ? data.text : '';

    // Recollir tots els gaps entre paraules consecutives de la mateixa línia
    var allGaps = [];
    var allWordWidths = [];
    data.lines.forEach(function(line) {
        if (!line.words || line.words.length < 2) return;
        for (var i = 0; i < line.words.length; i++) {
            var w = line.words[i];
            if (w.bbox) allWordWidths.push(w.bbox.x1 - w.bbox.x0);
            if (i > 0) {
                var prev = line.words[i - 1];
                if (prev.bbox && w.bbox) {
                    allGaps.push(w.bbox.x0 - prev.bbox.x1);
                }
            }
        }
    });

    if (allGaps.length === 0) return data.text;

    // Calcular amplada mitjana d'un caràcter
    var totalChars = 0;
    var totalWidth = 0;
    data.lines.forEach(function(line) {
        if (!line.words) return;
        line.words.forEach(function(w) {
            if (w.text && w.bbox) {
                totalChars += w.text.length;
                totalWidth += (w.bbox.x1 - w.bbox.x0);
            }
        });
    });
    var avgCharWidth = totalChars > 0 ? totalWidth / totalChars : 10;

    // Calcular llindar adaptatiu per distingir gap de paraula vs gap de columna
    // Filtrar gaps positius (negatius = solapaments OCR)
    var positiveGaps = allGaps.filter(function(g) { return g > 0; });
    if (positiveGaps.length === 0) return data.text;

    var sortedGaps = positiveGaps.slice().sort(function(a, b) { return a - b; });
    var median = sortedGaps[Math.floor(sortedGaps.length / 2)];

    // El llindar de columna: si la mediana és petita (text compacte),
    // un gap de columna serà molt més gran que la mediana.
    var colThreshold;
    if (customThreshold && customThreshold > 0) {
        colThreshold = customThreshold;
    } else {
        colThreshold = Math.max(median * 3, avgCharWidth * 3);
    }

    console.log('[OCR Column Detection] avgCharWidth=' + avgCharWidth.toFixed(1) +
        ' median gap=' + median.toFixed(1) +
        ' colThreshold=' + colThreshold.toFixed(1) +
        ' totalGaps=' + allGaps.length +
        ' min=' + sortedGaps[0].toFixed(1) +
        ' max=' + sortedGaps[sortedGaps.length - 1].toFixed(1));

    // Construir text línia per línia
    var result = [];
    data.lines.forEach(function(line) {
        if (!line.words || line.words.length === 0) return;
        var lineText = '';
        for (var i = 0; i < line.words.length; i++) {
            var w = line.words[i];
            if (i > 0 && w.bbox && line.words[i - 1].bbox) {
                var gap = w.bbox.x0 - line.words[i - 1].bbox.x1;
                if (gap >= colThreshold) {
                    lineText += ' | ';
                } else {
                    lineText += ' ';
                }
            }
            lineText += w.text;
        }
        if (lineText.trim()) result.push(lineText);
    });

    return result.join('\n');
}

// --- Image handling ---
async function handleImage(file) {
    var url = URL.createObjectURL(file);
    previewImg.src = url;
    previewImg.style.display = 'block';
    previewCanvas.style.display = 'none';

    await waitForTesseract();
    ocrStatus.textContent = 'Iniciant OCR de la imatge...';
    var worker = await window.Tesseract.createWorker('spa+cat', 1, {
        logger: function(m) {
            if (m.status === 'recognizing text') {
                var pct = Math.round(m.progress * 100);
                ocrBar.style.width = pct + '%';
                ocrStatus.textContent = 'OCR imatge... ' + pct + '%';
            }
        }
    });

    var result = await worker.recognize(file);
    await worker.terminate();
    lastOcrResultData = [result.data];
    lastPdfTextItems = [];
    ocrText = buildTextFromOcrResult(result.data);
    ocrBar.style.width = '100%';
    ocrStatus.textContent = 'OCR completat';
    finishOcr();
}

function waitForTesseract() {
    return new Promise(function(resolve) {
        if (window.Tesseract) return resolve();
        var check = setInterval(function() {
            if (window.Tesseract) { clearInterval(check); resolve(); }
        }, 200);
    });
}

// --- Parse OCR text ---
var ocrRawLines = []; // línies crues del OCR (array de arrays de "columnes")
var providerConfig = null; // config carregada del proveïdor

function finishOcr() {
    ocrTextPanel.textContent = ocrText;
    btnGuardar.disabled = false;
    parseOcrHeader(ocrText);
    // Parsejar línies crues amb marcadors si ja estan configurats
    var inicio = document.getElementById('cfgLineaInicio').value.trim();
    var fin = document.getElementById('cfgLineaFin').value.trim();
    ocrRawLines = extractRawLines(ocrText, 'auto', inicio, fin);
    // Mostrar sempre el panel de config (serà actualitzat si el proveïdor té config guardada)
    showConfigPanel();
    // Si ja tenim proveïdor amb config, aplicar directament
    if (providerConfig) {
        applyConfigToLines();
    } else {
        // Afegir una línia buida per si l'usuari vol entrar manualment
        addLinea();
    }
}

// Parsejar capçalera (factura, dates, proveïdor) - separat de les línies
function parseOcrHeader(text) {
    // Número factura
    var facMatch = text.match(/(?:factura|fra\.?)[\s:]*(?:n[ºo°]?\s*)?([A-Z0-9\-\/]+)/i);
    if (facMatch) document.getElementById('numFactura').value = facMatch[1].trim();

    // Fechas
    var datePattern = /(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/g;
    var dates = [];
    var dm;
    while ((dm = datePattern.exec(text)) !== null) {
        var year = dm[3];
        if (year.length === 2) year = '20' + year;
        var day = dm[1].padStart(2, '0');
        var month = dm[2].padStart(2, '0');
        if (parseInt(month) <= 12 && parseInt(day) <= 31) {
            dates.push(year + '-' + month + '-' + day);
        }
    }
    var meses = { 'enero':1,'febrero':2,'marzo':3,'abril':4,'mayo':5,'junio':6,'julio':7,'agosto':8,'septiembre':9,'octubre':10,'noviembre':11,'diciembre':12,
                  'gener':1,'febrer':2,'març':3,'abril':4,'maig':5,'juny':6,'juliol':7,'agost':8,'setembre':9,'octubre':10,'novembre':11,'desembre':12 };
    var mesPattern = /(\d{1,2})\s+(?:de\s+)?(gener|febrer|març|abril|maig|juny|juliol|agost|setembre|octubre|novembre|desembre|enero|febrero|marzo|mayo|junio|julio|agosto|septiembre|noviembre|diciembre)\s+(?:de\s+)?(\d{4})/gi;
    var mm;
    while ((mm = mesPattern.exec(text)) !== null) {
        var mday = mm[1].padStart(2, '0');
        var mmonth = String(meses[mm[2].toLowerCase()]).padStart(2, '0');
        dates.push(mm[3] + '-' + mmonth + '-' + mday);
    }
    if (dates.length >= 1) document.getElementById('fechaEmision').value = dates[0];
    if (dates.length >= 2) document.getElementById('fechaVencimiento').value = dates[1];

    // CIF/NIF i nom empresa per mapatge
    var ocrProvCandidates = [];
    var cifMatch = text.match(/(?:CIF|NIF|N\.I\.F|C\.I\.F)[\s.:]*([A-Z]\d{7}[A-Z0-9]|\d{8}[A-Z])/i);
    if (cifMatch) ocrProvCandidates.push({ type: 'CIF/NIF', value: cifMatch[1].toUpperCase() });
    var topLines = text.split(/\n/).slice(0, 8);
    for (var tl = 0; tl < topLines.length; tl++) {
        var tline = topLines[tl].trim();
        if (tline.length >= 5 && tline.length <= 80 &&
            !/^\d/.test(tline) &&
            !/^(factura|fra|fecha|data|cif|nif|iban|tel|email|www|http|base|iva|total)/i.test(tline) &&
            /[a-zA-ZÀ-ú]{3,}/.test(tline)) {
            ocrProvCandidates.push({ type: 'Nom', value: tline });
            break;
        }
    }
    if (ocrProvCandidates.length > 0) {
        matchOcrProvider(ocrProvCandidates);
    }
}

/**
 * Reconstruir ocrText des dels dades crus amb un threshold personalitzat.
 */
function rebuildOcrText(customThreshold) {
    var text = '';
    if (lastOcrResultData.length > 0) {
        lastOcrResultData.forEach(function(data) {
            text += buildTextFromOcrResult(data, customThreshold) + '\n';
        });
    } else if (lastPdfTextItems.length > 0) {
        lastPdfTextItems.forEach(function(item) {
            text += buildTextFromPdfItems(item.items, item.page, customThreshold) + '\n';
        });
    }
    return text.trim() ? text : ocrText;
}

/**
 * Filtrar línies de text pels marcadors d'inici i fi de productes.
 */
function filterLinesByMarkers(textLines, inicio, fin) {
    if (!inicio && !fin) return textLines;

    var inicioLower = inicio ? inicio.toLowerCase().trim() : '';
    var finLower = fin ? fin.toLowerCase().trim() : '';
    var startIdx = 0;
    var endIdx = textLines.length;

    if (inicioLower) {
        for (var i = 0; i < textLines.length; i++) {
            if (textLines[i].toLowerCase().indexOf(inicioLower) !== -1) {
                startIdx = i + 1;
                break;
            }
        }
    }
    if (finLower) {
        for (var i = startIdx; i < textLines.length; i++) {
            if (textLines[i].toLowerCase().indexOf(finLower) !== -1) {
                endIdx = i;
                break;
            }
        }
    }
    return textLines.slice(startIdx, endIdx);
}

/**
 * Extreure línies crues del text OCR.
 * @param {string} text - Text OCR
 * @param {string} sepMode - Mode de separació: 'auto', 'pipe', 'tab', '2spaces', '1space'
 * @param {string} inicio - Text marcador d'inici (opcional)
 * @param {string} fin - Text marcador de fi (opcional)
 */
function extractRawLines(text, sepMode, inicio, fin) {
    sepMode = sepMode || 'auto';
    var lines = text.split(/\n/);
    // Filtrar per marcadors d'inici/fi si estan definits
    lines = filterLinesByMarkers(lines, inicio, fin);
    var result = [];
    var skipPattern = /^(base|imponible|iva|i\.v\.a|total|subtotal|factura|fra|n[ºo°]|fecha|data|vencimiento|cif|nif|forma|pago|pagament|iban|swift|banco|banc|transfer|observ|condicion|email|tel[eè]fon|direcci|adre[çc]a|c[oó]digo?\s*postal|cp|provincia|poblaci|www\.|http)/i;

    // Determinar regex de split segons el mode
    var splitRegex;
    switch (sepMode) {
        case 'pipe':     splitRegex = /\s*\|\s*/; break;
        case 'tab':      splitRegex = /\t+/; break;
        case '2spaces':  splitRegex = /\s{2,}/; break;
        case '1space':   splitRegex = /\s+/; break;
        default:         splitRegex = null; break; // auto: provar en ordre
    }

    for (var i = 0; i < lines.length; i++) {
        var line = lines[i].trim();
        if (line.length < 3) continue;
        if (skipPattern.test(line)) continue;

        var cols;
        if (sepMode === 'auto') {
            // Auto: provar | primer, després tabs/2+espais, finalment 1 espai
            if (line.indexOf('|') !== -1) {
                cols = line.split(/\s*\|\s*/);
            } else {
                cols = line.split(/\t+|\s{2,}/);
            }
            cols = cols.map(function(c) { return c.trim(); }).filter(function(c) { return c.length > 0; });
            // Fallback a espai simple si no funciona i la línia té números
            if (cols.length < 2 && /\d+[.,]\d{2}/.test(line) && line.length >= 10) {
                cols = line.split(/\s+/).map(function(c) { return c.trim(); }).filter(function(c) { return c.length > 0; });
            }
        } else {
            cols = line.split(splitRegex).map(function(c) { return c.trim(); }).filter(function(c) { return c.length > 0; });
        }

        if (cols.length >= 2) {
            result.push(cols);
        }
    }

    return result;
}

/**
 * Mostrar el panel de configuració amb preview de les columnes detectades.
 * Es mostra SEMPRE que hi hagi text OCR disponible.
 */
function showConfigPanel() {
    var panel = document.getElementById('ocrConfigPanel');

    // Si no tenim línies, intentar re-parsejar amb el separador seleccionat
    var sepMode = document.getElementById('cfgColSep') ? document.getElementById('cfgColSep').value : 'auto';
    if (ocrRawLines.length === 0 && ocrText && ocrText.trim().length > 0) {
        var inicio = document.getElementById('cfgLineaInicio') ? document.getElementById('cfgLineaInicio').value.trim() : '';
        var fin = document.getElementById('cfgLineaFin') ? document.getElementById('cfgLineaFin').value.trim() : '';
        ocrRawLines = extractRawLines(ocrText, sepMode, inicio, fin);
    }

    // Mostrar sempre el panel (no amagar-lo)
    panel.style.display = 'block';

    if (ocrRawLines.length === 0) {
        // Cap línia detectada — mostrar missatge però mantenir els camps de separadors
        document.getElementById('ocrConfigPreview').innerHTML =
            '<div style="padding:1rem;text-align:center;color:#9ca3af;font-size:0.85rem;">' +
            'No s\'han detectat columnes automàticament.<br>Clica "Mostrar/Amagar text OCR" per veure el text extret.' +
            '</div>';
        // Posar selects buits
        ['cfgColRef', 'cfgColNom', 'cfgColQty', 'cfgColPreu', 'cfgColIva'].forEach(function(selId) {
            var sel = document.getElementById(selId);
            var defaultLabel = selId === 'cfgColIva' ? '-- No usar (21%) --' : '-- No usar --';
            sel.innerHTML = '<option value="-1">' + defaultLabel + '</option>';
        });
        return;
    }

    // Trobar el nombre màxim de columnes
    var maxCols = 0;
    ocrRawLines.forEach(function(row) { if (row.length > maxCols) maxCols = row.length; });

    // Generar taula preview
    var html = '<table><thead><tr>';
    for (var c = 0; c < maxCols; c++) {
        html += '<th>Col ' + c + '</th>';
    }
    html += '</tr></thead><tbody>';
    // Mostrar fins a 15 línies de preview
    var previewCount = Math.min(ocrRawLines.length, 15);
    for (var r = 0; r < previewCount; r++) {
        html += '<tr>';
        for (var c = 0; c < maxCols; c++) {
            html += '<td>' + escHtml(ocrRawLines[r][c] || '') + '</td>';
        }
        html += '</tr>';
    }
    if (ocrRawLines.length > 15) {
        html += '<tr><td colspan="' + maxCols + '" style="color:#9ca3af;text-align:center;">... i ' + (ocrRawLines.length - 15) + ' línies més</td></tr>';
    }
    html += '</tbody></table>';
    document.getElementById('ocrConfigPreview').innerHTML = html;

    // Poblar els selects de columnes
    var selects = ['cfgColRef', 'cfgColNom', 'cfgColQty', 'cfgColPreu', 'cfgColIva'];
    selects.forEach(function(selId) {
        var sel = document.getElementById(selId);
        var defaultLabel = selId === 'cfgColIva' ? '-- No usar (21%) --' : '-- No usar --';
        sel.innerHTML = '<option value="-1">' + defaultLabel + '</option>';
        for (var c = 0; c < maxCols; c++) {
            // Mostrar un exemple del contingut de la columna
            var sample = ocrRawLines[0] ? (ocrRawLines[0][c] || '') : '';
            if (sample.length > 25) sample = sample.substring(0, 25) + '...';
            sel.innerHTML += '<option value="' + c + '">Col ' + c + ' (' + escHtml(sample) + ')</option>';
        }
    });

    // Afegir listener per highlight de columnes seleccionades (evitar duplicats)
    selects.forEach(function(selId) {
        var el = document.getElementById(selId);
        if (!el.dataset.hasListener) {
            el.addEventListener('change', highlightSelectedCols);
            el.dataset.hasListener = '1';
        }
    });
}

/**
 * Re-parsejar les línies OCR amb el separador seleccionat i refrescar el preview.
 */
window.reparseOcrLines = function() {
    if (!ocrText || ocrText.trim().length === 0) return;
    var gapPx = parseInt(document.getElementById('cfgColGapPx').value) || -1;
    // Si hi ha threshold custom i dades crues, re-construir el text OCR
    if (gapPx > 0 && (lastOcrResultData.length > 0 || lastPdfTextItems.length > 0)) {
        ocrText = rebuildOcrText(gapPx);
        ocrTextPanel.textContent = ocrText;
    }
    var sepMode = document.getElementById('cfgColSep').value;
    var inicio = document.getElementById('cfgLineaInicio').value.trim();
    var fin = document.getElementById('cfgLineaFin').value.trim();
    ocrRawLines = extractRawLines(ocrText, sepMode, inicio, fin);
    showConfigPanel();
};

function highlightSelectedCols() {
    // Treure highlights anteriors
    document.querySelectorAll('#ocrConfigPreview td.col-selected').forEach(function(td) {
        td.classList.remove('col-selected');
    });
    // Afegir highlights nous
    var selectedCols = {};
    ['cfgColRef', 'cfgColNom', 'cfgColQty', 'cfgColPreu', 'cfgColIva'].forEach(function(selId) {
        var val = parseInt(document.getElementById(selId).value);
        if (val >= 0) selectedCols[val] = true;
    });
    var rows = document.querySelectorAll('#ocrConfigPreview tbody tr');
    rows.forEach(function(tr) {
        var tds = tr.querySelectorAll('td');
        tds.forEach(function(td, idx) {
            if (selectedCols[idx]) td.classList.add('col-selected');
        });
    });
}

/**
 * Parsejar un número segons la config de separadors del proveïdor.
 */
function parseConfigNumber(str, cfg) {
    if (!str) return 0;
    str = str.replace(/[^\d.,\-]/g, ''); // treure lletres, €, etc.
    if (!cfg) return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
    // Treure separador de milers
    if (cfg.miles_sep) {
        str = str.split(cfg.miles_sep).join('');
    }
    // Convertir separador decimal a punt
    if (cfg.decimal_sep && cfg.decimal_sep !== '.') {
        str = str.replace(cfg.decimal_sep, '.');
    }
    return parseFloat(str) || 0;
}

/**
 * Aplicar la configuració de columnes a les línies OCR i generar línies de compra.
 * Inclou auto-match de productes per referència del proveïdor.
 */
async function applyConfigToLines() {
    // Netejar línies existents
    document.getElementById('lineasBody').innerHTML = '';

    var cfg = providerConfig;
    if (!cfg || ocrRawLines.length === 0) {
        addLinea();
        return;
    }

    // 1. Preparar dades de les línies i recollir referències
    var lineData = [];
    var allRefs = [];
    ocrRawLines.forEach(function(row) {
        var ref = cfg.col_referencia >= 0 ? (row[cfg.col_referencia] || '').trim() : '';
        var nom = cfg.col_nombre >= 0 ? (row[cfg.col_nombre] || '') : '';
        var qtyStr = cfg.col_cantidad >= 0 ? (row[cfg.col_cantidad] || '') : '';
        var preuStr = cfg.col_precio >= 0 ? (row[cfg.col_precio] || '') : '';
        var ivaStr = cfg.col_iva >= 0 ? (row[cfg.col_iva] || '') : '';

        var qty = parseConfigNumber(qtyStr, cfg);
        var precio = parseConfigNumber(preuStr, cfg);
        var iva = cfg.col_iva >= 0 ? parseConfigNumber(ivaStr, cfg) : 21;

        // Filtrar línies sense dades útils
        if (!nom && !ref) return;
        if (qty <= 0) qty = 1;
        if (iva <= 0) iva = 21;

        var ocrDesc = (ref ? ref + ' - ' : '') + nom;
        lineData.push({ ref: ref, ocrDesc: ocrDesc.trim(), cantidad: qty, precio: precio > 0 ? precio : '', iva: iva, descuento: 0 });
        if (ref) allRefs.push(ref);
    });

    // 2. Buscar matches automàtics per referència si tenim proveïdor
    var productMatches = {};
    var idProv = document.getElementById('idProveedor').value;
    if (idProv && allRefs.length > 0) {
        try {
            var uniqueRefs = allRefs.filter(function(r, i) { return allRefs.indexOf(r) === i; });
            var res = await fetch(API_URL + '?action=match_products&idProveedor=' + idProv + '&refs=' + encodeURIComponent(JSON.stringify(uniqueRefs)));
            var data = await res.json();
            if (data.matches) productMatches = data.matches;
        } catch(e) {
            console.warn('Error fetching product matches:', e);
        }
    }

    // 3. Crear les línies amb auto-match
    var count = 0;
    lineData.forEach(function(line) {
        var match = line.ref ? productMatches[line.ref] : null;
        addLinea({
            ocrDesc: line.ocrDesc,
            ocrRef: line.ref,
            cantidad: line.cantidad,
            precio: line.precio,
            iva: line.iva,
            descuento: line.descuento,
            idProducto: match ? match.idProducto : '',
            prodName: match ? match.nombre : '',
            autoMatched: !!match
        });
        count++;
    });

    if (count === 0) addLinea();
    // Sempre afegir una línia buida al final
    addLinea();
}

/**
 * Guardar config i reaplicar.
 */
window.saveAndApplyConfig = async function() {
    var idProv = document.getElementById('idProveedor').value;
    if (!idProv) {
        alert('Cal seleccionar un proveïdor primer.');
        return;
    }

    var cfg = {
        col_referencia: parseInt(document.getElementById('cfgColRef').value),
        col_nombre: parseInt(document.getElementById('cfgColNom').value),
        col_cantidad: parseInt(document.getElementById('cfgColQty').value),
        col_precio: parseInt(document.getElementById('cfgColPreu').value),
        col_iva: parseInt(document.getElementById('cfgColIva').value),
        decimal_sep: document.getElementById('cfgDecSep').value,
        miles_sep: document.getElementById('cfgMilSep').value,
        linea_inicio: document.getElementById('cfgLineaInicio').value.trim(),
        linea_fin: document.getElementById('cfgLineaFin').value.trim(),
        col_gap_px: parseInt(document.getElementById('cfgColGapPx').value) || -1
    };

    // Guardar a BD
    var formData = new FormData();
    formData.append('action', 'save_provider_config');
    formData.append('csrf_token', '<?= e($csrfToken) ?>');
    formData.append('idProveedor', idProv);
    formData.append('col_referencia', cfg.col_referencia);
    formData.append('col_nombre', cfg.col_nombre);
    formData.append('col_cantidad', cfg.col_cantidad);
    formData.append('col_precio', cfg.col_precio);
    formData.append('col_iva', cfg.col_iva);
    formData.append('decimal_sep', cfg.decimal_sep);
    formData.append('miles_sep', cfg.miles_sep);
    formData.append('linea_inicio', cfg.linea_inicio);
    formData.append('linea_fin', cfg.linea_fin);
    formData.append('col_gap_px', cfg.col_gap_px);

    try {
        var res = await fetch(API_URL, { method: 'POST', body: formData });
        var data = await res.json();
        if (data.success) {
            document.getElementById('cfgSavedMsg').style.display = 'inline';
            setTimeout(function() { document.getElementById('cfgSavedMsg').style.display = 'none'; }, 3000);
        }
    } catch(e) {
        console.warn('Error saving config:', e);
    }

    // Aplicar
    providerConfig = cfg;
    applyConfigToLines();
};

/**
 * Carregar config d'un proveïdor i, si existeix, aplicar-la.
 */
async function loadProviderConfig(idProveedor) {
    try {
        var res = await fetch(API_URL + '?action=get_provider_config&idProveedor=' + idProveedor);
        var data = await res.json();
        if (data.found) {
            providerConfig = data;
            // Si ja tenim línies OCR, aplicar
            if (ocrRawLines.length > 0 || (ocrText && ocrText.trim().length > 0)) {
                // Poblar camps nous primer (abans de re-build)
                document.getElementById('cfgLineaInicio').value = data.linea_inicio || '';
                document.getElementById('cfgLineaFin').value = data.linea_fin || '';
                document.getElementById('cfgColGapPx').value = data.col_gap_px != null ? data.col_gap_px : -1;

                // Si hi ha threshold custom, re-construir el text OCR
                var gapPx = data.col_gap_px || -1;
                if (gapPx > 0 && (lastOcrResultData.length > 0 || lastPdfTextItems.length > 0)) {
                    ocrText = rebuildOcrText(gapPx);
                    ocrTextPanel.textContent = ocrText;
                }

                // Re-parsejar amb marcadors
                var inicio = data.linea_inicio || '';
                var fin = data.linea_fin || '';
                var sepMode = document.getElementById('cfgColSep') ? document.getElementById('cfgColSep').value : 'auto';
                ocrRawLines = extractRawLines(ocrText, sepMode, inicio, fin);

                // Mostrar panel amb valors pre-omplerts
                showConfigPanel();
                // Posar els valors als selects
                document.getElementById('cfgColRef').value = data.col_referencia;
                document.getElementById('cfgColNom').value = data.col_nombre;
                document.getElementById('cfgColQty').value = data.col_cantidad;
                document.getElementById('cfgColPreu').value = data.col_precio;
                document.getElementById('cfgColIva').value = data.col_iva;
                document.getElementById('cfgDecSep').value = data.decimal_sep;
                document.getElementById('cfgMilSep').value = data.miles_sep;
                highlightSelectedCols();
                // Aplicar directament
                applyConfigToLines();
            }
            return true;
        }
    } catch(e) {
        console.warn('Error loading provider config:', e);
    }
    providerConfig = null;
    return false;
}

// --- OCR Provider matching ---
var ocrProvPanel = document.getElementById('ocrProvPanel');
var ocrProvLabel = document.getElementById('ocrProvLabel');
var ocrProvDetected = document.getElementById('ocrProvDetected');
var ocrProvActions = document.getElementById('ocrProvActions');
var currentOcrProvCandidates = []; // guardar per poder fer save_map

async function matchOcrProvider(candidates) {
    currentOcrProvCandidates = candidates;

    // Mostrar panel amb les dades detectades
    ocrProvPanel.style.display = 'block';
    ocrProvPanel.className = 'ocr-prov-panel detected';
    ocrProvLabel.textContent = 'Dades detectades per OCR';
    ocrProvDetected.innerHTML = candidates.map(function(c) {
        return '<span class="ocr-prov-value">' + escHtml(c.type) + ': ' + escHtml(c.value) + '</span> ';
    }).join('');
    ocrProvActions.style.display = 'flex';

    // Consultar la taula de mapatge
    var textsParam = candidates.map(function(c) { return c.value; }).join('|');
    try {
        var res = await fetch(API_URL + '?action=match_provider&texts=' + encodeURIComponent(textsParam));
        var data = await res.json();
        if (data.found) {
            // Match automàtic!
            selectProvider(data.idProveedor, data.nombre);
            ocrProvPanel.className = 'ocr-prov-panel matched';
            ocrProvLabel.textContent = 'Proveïdor reconegut automàticament';
            ocrProvDetected.innerHTML += ' &rarr; <strong>' + escHtml(data.nombre) + '</strong>';
            ocrProvActions.style.display = 'none';
            // Carregar config del proveïdor
            var hasConfig = await loadProviderConfig(data.idProveedor);
            if (!hasConfig && ocrRawLines.length > 0) showConfigPanel();
            return;
        }
    } catch(e) {
        console.warn('Error checking provider map:', e);
    }

    // No match → mostrar panel d'associació
    ocrProvLabel.textContent = 'Proveïdor no reconegut - Associar manualment';
}

function selectProvider(id, nombre) {
    document.getElementById('idProveedor').value = id;
    document.getElementById('proveedorSearch').value = nombre;
}

async function saveProviderMap(ocrText, idProveedor) {
    var formData = new FormData();
    formData.append('action', 'save_provider_map');
    formData.append('csrf_token', '<?= e($csrfToken) ?>');
    formData.append('ocr_text', ocrText);
    formData.append('idProveedor', idProveedor);
    try {
        var res = await fetch(API_URL, { method: 'POST', body: formData });
        var data = await res.json();
        return data.success;
    } catch(e) {
        console.warn('Error saving provider map:', e);
        return false;
    }
}

// Autocomplete dins del panel d'associació OCR
var ocrAssocSearch = document.getElementById('ocrProvAssocSearch');
var ocrAssocList = document.getElementById('ocrProvAssocList');
var ocrAssocTimeout;

ocrAssocSearch.addEventListener('input', function() {
    clearTimeout(ocrAssocTimeout);
    var q = ocrAssocSearch.value.trim();
    if (q.length < 2) { ocrAssocList.style.display = 'none'; return; }
    ocrAssocTimeout = setTimeout(async function() {
        var res = await fetch(API_URL + '?action=search_providers&q=' + encodeURIComponent(q));
        var data = await res.json();
        if (!data.length) {
            ocrAssocList.innerHTML = '<div class="ac-item" style="color:#9ca3af;">Cap resultat</div>';
            ocrAssocList.style.display = 'block';
            return;
        }
        ocrAssocList.innerHTML = data.map(function(p) {
            return '<div class="ac-item" data-id="' + p.id + '" data-name="' + escHtml(p.nombre) + '">' + escHtml(p.nombre) + '</div>';
        }).join('');
        ocrAssocList.style.display = 'block';
        ocrAssocList.querySelectorAll('.ac-item[data-id]').forEach(function(item) {
            item.addEventListener('click', async function() {
                var provId = item.dataset.id;
                var provName = item.dataset.name;
                ocrAssocList.style.display = 'none';

                // Seleccionar proveïdor al formulari
                selectProvider(provId, provName);

                // Guardar tots els candidats OCR com a mapatge
                var savedCount = 0;
                for (var i = 0; i < currentOcrProvCandidates.length; i++) {
                    var ok = await saveProviderMap(currentOcrProvCandidates[i].value, provId);
                    if (ok) savedCount++;
                }

                // Actualizar panel visual
                ocrProvPanel.className = 'ocr-prov-panel matched';
                ocrProvLabel.textContent = 'Proveïdor associat i guardat';
                ocrProvActions.innerHTML = '<span class="ocr-prov-saved">&#10003; ' + escHtml(provName) + ' — Mapatge guardat. Les pròximes factures es reconeixeran automàticament.</span>';
                // Carregar config (si existeix) o mostrar panel per configurar
                await loadProviderConfig(provId);
                if (!providerConfig && ocrRawLines.length > 0) showConfigPanel();
            });
        });
    }, 300);
});

// --- Autocomplete Proveïdor (camp principal, per si l'usuari vol canviar manualment) ---
var provTimeout;
var provSearch = document.getElementById('proveedorSearch');
var provList = document.getElementById('proveedorList');

provSearch.addEventListener('input', function() {
    clearTimeout(provTimeout);
    var q = provSearch.value.trim();
    if (q.length < 2) { provList.style.display = 'none'; return; }
    provTimeout = setTimeout(async function() {
        var res = await fetch(API_URL + '?action=search_providers&q=' + encodeURIComponent(q));
        var data = await res.json();
        if (!data.length) { provList.style.display = 'none'; return; }
        provList.innerHTML = data.map(function(p) {
            return '<div class="ac-item" data-id="' + p.id + '" data-name="' + escHtml(p.nombre) + '">' + escHtml(p.nombre) + '</div>';
        }).join('');
        provList.style.display = 'block';
        provList.querySelectorAll('.ac-item').forEach(function(item) {
            item.addEventListener('click', async function() {
                selectProvider(item.dataset.id, item.dataset.name);
                provList.style.display = 'none';
                // Carregar config del proveïdor
                var hasConfig = await loadProviderConfig(item.dataset.id);
                if (!hasConfig && ocrRawLines.length > 0) showConfigPanel();
            });
        });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.pos-relative')) {
        provList.style.display = 'none';
        ocrAssocList.style.display = 'none';
        document.querySelectorAll('.prod-ac-list').forEach(function(l) { l.style.display = 'none'; });
    }
});

// --- Línies de compra ---
var lineaIdx = 0;

window.addLinea = function(data) {
    data = data || {};
    var idx = lineaIdx++;
    var tr = document.createElement('tr');
    tr.dataset.idx = idx;
    if (data.ocrRef) tr.dataset.ocrRef = data.ocrRef;
    var ocrHint = data.ocrDesc ? '<span class="ocr-desc-hint" title="' + escHtml(data.ocrDesc) + '">' + escHtml(data.ocrDesc) + '</span>' : '';
    tr.innerHTML =
        '<td class="pos-relative">' +
            ocrHint +
            '<input type="hidden" name="linea_producto[]" class="linea-prod-id" value="' + (data.idProducto || '') + '">' +
            '<input type="text" class="prod-search" placeholder="' + (data.ocrDesc ? escHtml(data.ocrDesc.substring(0, 30)) + '...' : 'Cerca producte...') + '" value="' + escHtml(data.prodName || '') + '" autocomplete="off">' +
            '<div class="autocomplete-list prod-ac-list" style="display:none;"></div>' +
        '</td>' +
        '<td><input type="number" name="linea_cantidad[]" class="linea-qty" value="' + (data.cantidad || 1) + '" min="0.01" step="any"></td>' +
        '<td><input type="number" name="linea_precio[]" class="linea-precio" value="' + (data.precio || '') + '" min="0" step="any" placeholder="0,00"></td>' +
        '<td><input type="number" name="linea_descuento[]" class="linea-dto" value="' + (data.descuento || 0) + '" min="0" max="100" step="any"></td>' +
        '<td><input type="number" name="linea_iva[]" class="linea-iva" value="' + (data.iva != null ? data.iva : 21) + '" min="0" step="any"></td>' +
        '<td class="linea-subtotal" style="font-weight:600;white-space:nowrap;">0,00</td>' +
        '<td><button type="button" class="btn btn-sm btn-danger" onclick="removeLinea(this)">&times;</button></td>';
    // Marcar files auto-matched
    if (data.autoMatched) {
        tr.classList.add('linea-assigned', 'linea-auto-matched');
    }
    document.getElementById('lineasBody').appendChild(tr);
    setupProductAutocomplete(tr);
    setupCalcListeners(tr);
    calcTotals();
};

window.removeLinea = function(btn) {
    btn.closest('tr').remove();
    calcTotals();
};

function setupProductAutocomplete(tr) {
    var input = tr.querySelector('.prod-search');
    var list = tr.querySelector('.prod-ac-list');
    var hiddenId = tr.querySelector('.linea-prod-id');
    var timeout;

    input.addEventListener('input', function() {
        clearTimeout(timeout);
        hiddenId.value = '';
        var q = input.value.trim();
        if (q.length < 2) { list.style.display = 'none'; return; }
        timeout = setTimeout(async function() {
            var res = await fetch(API_URL + '?action=search_products&q=' + encodeURIComponent(q));
            var data = await res.json();
            if (!data.length) { list.style.display = 'none'; return; }
            list.innerHTML = data.map(function(p) {
                return '<div class="ac-item" data-id="' + p.id + '" data-name="' + escHtml(p.nombre) + '" data-iva="' + p.iva + '" data-pvp="' + p.pvp + '">' +
                    escHtml(p.nombre) + ' <small>' + escHtml(p.codigo || '') + '</small></div>';
            }).join('');
            list.style.display = 'block';
            list.querySelectorAll('.ac-item').forEach(function(item) {
                item.addEventListener('click', async function() {
                    hiddenId.value = item.dataset.id;
                    input.value = item.dataset.name;
                    list.style.display = 'none';
                    // Mark row as assigned
                    tr.classList.add('linea-assigned');
                    var ivaInput = tr.querySelector('.linea-iva');
                    if (item.dataset.iva && !ivaInput.value) ivaInput.value = item.dataset.iva;
                    calcTotals();

                    // Guardar mapatge ref_proveedor → producte si tenim referència OCR
                    var ocrRef = tr.dataset.ocrRef;
                    var idProv = document.getElementById('idProveedor').value;
                    if (ocrRef && idProv) {
                        saveProductMap(idProv, ocrRef, item.dataset.id, tr);
                    }
                });
            });
        }, 300);
    });
}

function setupCalcListeners(tr) {
    tr.querySelectorAll('.linea-qty, .linea-precio, .linea-dto, .linea-iva').forEach(function(input) {
        input.addEventListener('input', calcTotals);
    });
}

async function saveProductMap(idProveedor, refProveedor, idProducto, tr) {
    var formData = new FormData();
    formData.append('action', 'save_product_map');
    formData.append('csrf_token', '<?= e($csrfToken) ?>');
    formData.append('idProveedor', idProveedor);
    formData.append('ref_proveedor', refProveedor);
    formData.append('idProducto', idProducto);
    try {
        var res = await fetch(API_URL, { method: 'POST', body: formData });
        var data = await res.json();
        if (data.success && tr) {
            // Mostrar indicador visual
            var hint = tr.querySelector('.ocr-desc-hint');
            if (hint) {
                var badge = document.createElement('span');
                badge.className = 'ocr-map-saved';
                badge.textContent = '\u2713 Mapatge guardat';
                hint.parentNode.insertBefore(badge, hint.nextSibling);
                setTimeout(function() { badge.remove(); }, 4000);
            }
        }
    } catch(e) {
        console.warn('Error saving product map:', e);
    }
}

function calcTotals() {
    var totalBase = 0, totalIva = 0;
    document.querySelectorAll('#lineasBody tr').forEach(function(tr) {
        var qty = parseFloat(tr.querySelector('.linea-qty').value) || 0;
        var precio = parseFloat(tr.querySelector('.linea-precio').value) || 0;
        var dto = parseFloat(tr.querySelector('.linea-dto').value) || 0;
        var iva = parseFloat(tr.querySelector('.linea-iva').value) || 0;
        var precioConDto = precio * (1 - dto / 100);
        var subtotal = qty * precioConDto;
        tr.querySelector('.linea-subtotal').textContent = formatNum(subtotal);
        totalBase += subtotal;
        totalIva += subtotal * (iva / 100);
    });
    document.getElementById('totalBase').innerHTML = formatNum(totalBase) + ' &euro;';
    document.getElementById('totalIva').innerHTML = formatNum(totalIva) + ' &euro;';
    document.getElementById('totalTotal').innerHTML = formatNum(totalBase + totalIva) + ' &euro;';
}

function formatNum(n) {
    return n.toLocaleString('ca-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escHtml(str) {
    var div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}

window.toggleOcrText = function() {
    var panel = document.getElementById('ocrTextPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
};

// Form validation
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    var idProv = document.getElementById('idProveedor').value;
    if (!idProv) { e.preventDefault(); alert('Cal seleccionar un proveïdor de la llista.'); return; }
    var rows = document.querySelectorAll('#lineasBody tr');
    if (rows.length === 0) { e.preventDefault(); alert('Cal afegir almenys una línia.'); return; }
    var valid = true;
    rows.forEach(function(tr) {
        if (!tr.querySelector('.linea-prod-id').value) valid = false;
    });
    if (!valid) { e.preventDefault(); alert('Cal seleccionar un producte de la llista per cada línia.'); return; }
});

// Init: add one empty line (only if upload zone is visible = no file loaded yet)
if (document.getElementById('uploadZone').style.display !== 'none') addLinea();

})();
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
