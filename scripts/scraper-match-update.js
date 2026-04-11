/**
 * Scraper + Matcher + Updater de precios desde vilaviniteca.es
 *
 * 1. Lee productos_target.json (productos sin precio o sin compra 60d)
 * 2. Scrapea vilaviniteca.es (vinos, espumosos, destilados)
 * 3. Compara nombres con fuzzy matching
 * 4. Genera un CSV de matches para revisión
 * 5. Opcionalmente actualiza la DB
 *
 * Uso:
 *   node scripts/scraper-match-update.js                    # Solo scrape + match (genera CSV)
 *   node scripts/scraper-match-update.js --apply            # Scrape + match + actualizar DB
 *   node scripts/scraper-match-update.js --skip-scrape      # Usar cache, solo match
 *   node scripts/scraper-match-update.js --threshold=0.85   # Cambiar umbral de match (default 0.80)
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// --- Config ---
const SCRIPTS_DIR = __dirname;
const PRODUCTS_FILE = path.join(SCRIPTS_DIR, 'productos_target.json');
const SCRAPED_FILE = path.join(SCRIPTS_DIR, 'vilaviniteca_scraped.json');
const MATCHES_FILE = path.join(SCRIPTS_DIR, 'matches_preview.csv');
const RESULTS_FILE = path.join(SCRIPTS_DIR, 'update_results.log');
const DELAY_MS = 1500;

const CATEGORIES = [
    { name: 'vinos', url: 'https://www.vilaviniteca.es/es/vinos.html?product_list_limit=36', pages: 210 },
    { name: 'espumosos', url: 'https://www.vilaviniteca.es/es/espumosos.html?product_list_limit=36', pages: 20 },
    { name: 'destilados', url: 'https://www.vilaviniteca.es/es/destilados.html?product_list_limit=36', pages: 45 },
];

// --- Parse args ---
const args = process.argv.slice(2);
const applyChanges = args.includes('--apply');
const skipScrape = args.includes('--skip-scrape');
const thresholdArg = args.find(a => a.startsWith('--threshold='));
const MATCH_THRESHOLD = thresholdArg ? parseFloat(thresholdArg.split('=')[1]) : 0.80;

// ============================================================
// FUZZY MATCHING
// ============================================================

/**
 * Normaliza un nombre de producto para comparación
 */
function normalize(str) {
    return str
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // quitar acentos
        .replace(/\b(vino|vi|cava|cerveza|beer|licor)\b/gi, '')
        .replace(/\b\d{2,4}\s*(cl|ml|l|lt)\b/gi, '')     // quitar formatos 75cl, 700ml
        .replace(/\b(tinto|blanco|rosado|reserva|gran reserva|crianza|joven)\b/gi, (m) => m) // mantener tipo
        .replace(/[^a-z0-9\s]/g, ' ')                      // quitar símbolos
        .replace(/\s+/g, ' ')
        .trim();
}

/**
 * Extrae el nombre base del vino (sin añada, formato, etc.)
 */
function extractBaseName(str) {
    return str
        .replace(/\b(20\d{2}|19\d{2})\b/g, '')          // quitar año
        .replace(/\b\d+\s*\/\s*\d+\b/g, '')              // quitar 3/8, 1/2
        .replace(/\b(magnum|jeroboam|doble magnum)\b/gi, '')
        .replace(/\b\d{2,4}\s*(cl|ml|l|lt|litros?)\b/gi, '')
        .trim();
}

/**
 * Levenshtein distance
 */
function levenshtein(a, b) {
    const m = a.length, n = b.length;
    const dp = Array.from({ length: m + 1 }, () => Array(n + 1).fill(0));
    for (let i = 0; i <= m; i++) dp[i][0] = i;
    for (let j = 0; j <= n; j++) dp[0][j] = j;
    for (let i = 1; i <= m; i++) {
        for (let j = 1; j <= n; j++) {
            dp[i][j] = a[i - 1] === b[j - 1]
                ? dp[i - 1][j - 1]
                : 1 + Math.min(dp[i - 1][j], dp[i][j - 1], dp[i - 1][j - 1]);
        }
    }
    return dp[m][n];
}

/**
 * Similitud entre 0 y 1
 */
function similarity(a, b) {
    if (!a || !b) return 0;
    const na = normalize(extractBaseName(a));
    const nb = normalize(extractBaseName(b));
    if (na === nb) return 1;
    const maxLen = Math.max(na.length, nb.length);
    if (maxLen === 0) return 0;
    return 1 - levenshtein(na, nb) / maxLen;
}

/**
 * Token-based similarity (Jaccard con bonus por tokens ordenados)
 */
function tokenSimilarity(a, b) {
    const ta = new Set(normalize(extractBaseName(a)).split(' ').filter(t => t.length > 1));
    const tb = new Set(normalize(extractBaseName(b)).split(' ').filter(t => t.length > 1));
    if (ta.size === 0 || tb.size === 0) return 0;
    let intersection = 0;
    for (const t of ta) { if (tb.has(t)) intersection++; }
    const union = new Set([...ta, ...tb]).size;
    return intersection / union;
}

/**
 * Score combinado
 */
function matchScore(local, scraped) {
    const levSim = similarity(local, scraped);
    const tokSim = tokenSimilarity(local, scraped);
    // Peso mayor al token similarity (más robusto con reordenamientos)
    return levSim * 0.4 + tokSim * 0.6;
}

// ============================================================
// SCRAPING
// ============================================================

async function scrapeVilaViniteca() {
    console.log('\n🔍 Iniciando scraping de vilaviniteca.es...');
    console.log(`   Categorías: ${CATEGORIES.map(c => c.name).join(', ')}`);

    const browser = await chromium.launch({
        headless: true,
        args: ['--disable-blink-features=AutomationControlled'],
    });

    const context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
        locale: 'es-ES',
        viewport: { width: 1366, height: 768 },
    });

    const page = await context.newPage();
    const allProducts = [];

    try {
        // Aceptar cookies una vez
        await page.goto(CATEGORIES[0].url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await page.waitForTimeout(2000);
        try {
            const cookieBtn = await page.$('#onetrust-accept-btn-handler, button[class*="cookie"], .accept-cookies, [data-action="accept"]');
            if (cookieBtn) { await cookieBtn.click(); await page.waitForTimeout(1000); }
        } catch (_) {}

        for (const cat of CATEGORIES) {
            console.log(`\n📦 Categoría: ${cat.name.toUpperCase()}`);
            let pageNum = 1;
            let emptyCount = 0;

            while (pageNum <= cat.pages) {
                const url = pageNum === 1 ? cat.url : `${cat.url}&p=${pageNum}`;

                try {
                    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 20000 });
                    await page.waitForTimeout(1500);

                    const products = await page.evaluate((category) => {
                        const items = [];
                        // Magento 2 product list selectors
                        const productEls = document.querySelectorAll('.product-item, .product-item-info, li.item.product');

                        productEls.forEach(el => {
                            const nameEl = el.querySelector('.product-item-link, .product-item-name a, a.product-item-link');
                            const name = nameEl ? nameEl.textContent.trim() : '';

                            const priceEl = el.querySelector('[data-price-amount], .price-wrapper [data-price-amount], .price');
                            let price = 0;
                            if (priceEl) {
                                const amt = priceEl.getAttribute('data-price-amount');
                                if (amt) {
                                    price = parseFloat(amt);
                                } else {
                                    const m = priceEl.textContent.match(/([\d.,]+)/);
                                    if (m) price = parseFloat(m[1].replace(',', '.'));
                                }
                            }

                            const linkEl = el.querySelector('a[href]');
                            const link = linkEl ? linkEl.href : '';

                            if (name && name.length > 2 && price > 0) {
                                items.push({ name, price, link, category });
                            }
                        });

                        // Dedup
                        const seen = new Set();
                        return items.filter(p => {
                            const key = p.name + p.price;
                            if (seen.has(key)) return false;
                            seen.add(key);
                            return true;
                        });
                    }, cat.name);

                    if (products.length === 0) {
                        emptyCount++;
                        if (emptyCount >= 2) {
                            console.log(`   Página ${pageNum}: vacía (fin de categoría)`);
                            break;
                        }
                    } else {
                        emptyCount = 0;
                        allProducts.push(...products);
                    }

                    if (pageNum % 10 === 0 || pageNum === 1) {
                        console.log(`   Página ${pageNum}/${cat.pages}: ${products.length} productos (total acumulado: ${allProducts.length})`);
                    }
                } catch (err) {
                    console.log(`   ⚠️  Página ${pageNum}: error (${err.message}), reintentando...`);
                    await page.waitForTimeout(3000);
                    continue;
                }

                pageNum++;
                await page.waitForTimeout(DELAY_MS);
            }
            console.log(`   ✅ ${cat.name}: completado`);
        }
    } finally {
        await browser.close();
    }

    // Dedup global
    const seen = new Set();
    const unique = allProducts.filter(p => {
        if (seen.has(p.name)) return false;
        seen.add(p.name);
        return true;
    });

    fs.writeFileSync(SCRAPED_FILE, JSON.stringify(unique, null, 2), 'utf-8');
    console.log(`\n✅ Scraping completado: ${unique.length} productos únicos guardados en ${SCRAPED_FILE}`);
    return unique;
}

// ============================================================
// MATCHING
// ============================================================

function findMatches(localProducts, scrapedProducts) {
    console.log(`\n🔗 Buscando matches entre ${localProducts.length} productos locales y ${scrapedProducts.length} de vilaviniteca...`);

    const matches = [];
    let processed = 0;

    for (const local of localProducts) {
        let bestMatch = null;
        let bestScore = 0;

        for (const scraped of scrapedProducts) {
            const score = matchScore(local.nombre, scraped.name);
            if (score > bestScore) {
                bestScore = score;
                bestMatch = scraped;
            }
        }

        if (bestScore >= MATCH_THRESHOLD && bestMatch) {
            const newPvp = Math.round((bestMatch.price + 1) * 100) / 100; // +1€, redondear a 2 decimales
            matches.push({
                localId: local.id,
                localName: local.nombre,
                localSector: local.sector,
                localPvp: parseFloat(local.pvp),
                scrapedName: bestMatch.name,
                scrapedPrice: bestMatch.price,
                scrapedCategory: bestMatch.category,
                newPvp,
                score: Math.round(bestScore * 100) / 100,
                url: bestMatch.link,
            });
        }

        processed++;
        if (processed % 500 === 0) {
            console.log(`   Procesados ${processed}/${localProducts.length}... (${matches.length} matches)`);
        }
    }

    // Ordenar por score descendente
    matches.sort((a, b) => b.score - a.score);

    console.log(`   ✅ ${matches.length} matches encontrados (umbral: ${MATCH_THRESHOLD})`);
    return matches;
}

// ============================================================
// OUTPUT + UPDATE
// ============================================================

function generateCSV(matches) {
    const header = 'ID;Nombre Local;Sector;PVP Actual;Nombre Vilaviniteca;Precio Vila;Nuevo PVP;Score;URL';
    const rows = matches.map(m => {
        const esc = s => `"${String(s).replace(/"/g, '""')}"`;
        return `${m.localId};${esc(m.localName)};${esc(m.localSector)};${m.localPvp};${esc(m.scrapedName)};${m.scrapedPrice};${m.newPvp};${m.score};${esc(m.url)}`;
    });
    const csv = '\ufeff' + [header, ...rows].join('\n');
    fs.writeFileSync(MATCHES_FILE, csv, 'utf-8');
    console.log(`\n📊 CSV de matches generado: ${MATCHES_FILE}`);
    console.log(`   Total matches: ${matches.length}`);

    // Resumen por sector
    const bySector = {};
    matches.forEach(m => {
        bySector[m.localSector] = (bySector[m.localSector] || 0) + 1;
    });
    console.log('   Por sector:');
    Object.entries(bySector).forEach(([s, c]) => console.log(`     - ${s}: ${c}`));

    // Resumen por calidad de match
    const high = matches.filter(m => m.score >= 0.95).length;
    const med = matches.filter(m => m.score >= 0.85 && m.score < 0.95).length;
    const low = matches.filter(m => m.score < 0.85).length;
    console.log(`   Por calidad: 🟢 excelente(≥0.95): ${high}  🟡 bueno(≥0.85): ${med}  🟠 aceptable(<0.85): ${low}`);
}

async function applyToDatabase(matches) {
    console.log('\n💾 Aplicando cambios a la base de datos...');

    // Usar PHP para la actualización (mismo entorno DB)
    const updateScript = `<?php
$bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
if (!$bd) { echo json_encode(['error' => mysqli_connect_error()]); exit(1); }

$matches = json_decode(file_get_contents('${MATCHES_FILE.replace(/\\/g, '/')}'), true);
// Parse from CSV... better to use JSON
$matches = json_decode(file_get_contents('php://stdin'), true);

$updated = 0;
$inserted = 0;
$errors = [];

foreach ($matches as $m) {
    $id = intval($m['localId']);
    $pvp = floatval($m['newPvp']);

    // Check if row exists
    $check = mysqli_query($bd, "SELECT id FROM precios_producto_tienda WHERE idProducto = $id AND idTienda = 1");
    if (mysqli_num_rows($check) > 0) {
        $sql = "UPDATE precios_producto_tienda SET pvp = $pvp WHERE idProducto = $id AND idTienda = 1";
        if (mysqli_query($bd, $sql)) {
            $updated++;
        } else {
            $errors[] = "UPDATE id=$id: " . mysqli_error($bd);
        }
    } else {
        $sql = "INSERT INTO precios_producto_tienda (idTienda, idProducto, pvp) VALUES (1, $id, $pvp)";
        if (mysqli_query($bd, $sql)) {
            $inserted++;
        } else {
            $errors[] = "INSERT id=$id: " . mysqli_error($bd);
        }
    }
}

echo json_encode(['updated' => $updated, 'inserted' => $inserted, 'errors' => $errors]);
`;

    const phpFile = path.join(SCRIPTS_DIR, '_temp_update.php');
    fs.writeFileSync(phpFile, updateScript, 'utf-8');

    const { execSync } = require('child_process');
    const matchesJson = JSON.stringify(matches);

    try {
        const result = execSync(`php "${phpFile}"`, {
            input: matchesJson,
            timeout: 60000,
            encoding: 'utf-8',
        });
        const data = JSON.parse(result);
        console.log(`   ✅ Actualizados: ${data.updated}`);
        console.log(`   ✅ Insertados: ${data.inserted}`);
        if (data.errors.length > 0) {
            console.log(`   ❌ Errores: ${data.errors.length}`);
            data.errors.slice(0, 5).forEach(e => console.log(`      - ${e}`));
        }

        // Log
        const logEntry = `[${new Date().toISOString()}] Updated: ${data.updated}, Inserted: ${data.inserted}, Errors: ${data.errors.length}\n`;
        fs.appendFileSync(RESULTS_FILE, logEntry, 'utf-8');
    } finally {
        try { fs.unlinkSync(phpFile); } catch (_) {}
    }
}

// ============================================================
// MAIN
// ============================================================

async function main() {
    console.log('='.repeat(70));
    console.log(' SCRAPER + MATCHER + UPDATER DE PRECIOS - VILAVINITECA.ES');
    console.log('='.repeat(70));
    console.log(`Modo: ${applyChanges ? '🔴 APLICAR CAMBIOS' : '🟢 SOLO PREVIEW (CSV)'}`);
    console.log(`Umbral match: ${MATCH_THRESHOLD}`);
    console.log('');

    // 1. Cargar productos locales
    if (!fs.existsSync(PRODUCTS_FILE)) {
        console.error('❌ No se encontró productos_target.json. Ejecuta primero: php scripts/export_products.php');
        process.exit(1);
    }
    const localProducts = JSON.parse(fs.readFileSync(PRODUCTS_FILE, 'utf-8'));
    console.log(`📋 Productos locales cargados: ${localProducts.length}`);

    // 2. Scrape o cargar cache
    let scrapedProducts;
    if (skipScrape && fs.existsSync(SCRAPED_FILE)) {
        scrapedProducts = JSON.parse(fs.readFileSync(SCRAPED_FILE, 'utf-8'));
        console.log(`📋 Productos scrapeados (cache): ${scrapedProducts.length}`);
    } else {
        scrapedProducts = await scrapeVilaViniteca();
    }

    // 3. Match
    const matches = findMatches(localProducts, scrapedProducts);

    // 4. Generar CSV
    generateCSV(matches);

    // 5. Mostrar top 20 matches
    console.log('\n📌 Top 20 mejores matches:');
    console.log('-'.repeat(120));
    console.log(
        'Score'.padEnd(7) +
        'ID'.padEnd(7) +
        'Nombre Local'.padEnd(40) +
        'Nombre Vilaviniteca'.padEnd(40) +
        'PVP Actual'.padEnd(12) +
        'Nuevo PVP'
    );
    console.log('-'.repeat(120));
    matches.slice(0, 20).forEach(m => {
        console.log(
            String(m.score).padEnd(7) +
            String(m.localId).padEnd(7) +
            m.localName.substring(0, 38).padEnd(40) +
            m.scrapedName.substring(0, 38).padEnd(40) +
            String(m.localPvp + '€').padEnd(12) +
            m.newPvp + '€'
        );
    });

    // 6. Aplicar si se pidió
    if (applyChanges && matches.length > 0) {
        await applyToDatabase(matches);
    } else if (!applyChanges && matches.length > 0) {
        console.log('\n💡 Para aplicar los cambios, ejecuta:');
        console.log('   node scripts/scraper-match-update.js --skip-scrape --apply');
    }

    console.log('\n✅ Proceso completado.');
}

main().catch(err => {
    console.error('Error fatal:', err);
    process.exit(1);
});
