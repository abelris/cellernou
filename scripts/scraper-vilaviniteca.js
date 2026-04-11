/**
 * Scraper de precios de vinos - vilaviniteca.es
 * Usa Playwright para simular un navegador real y evitar bloqueos anti-bot.
 *
 * Uso:
 *   node scripts/scraper-vilaviniteca.js
 *   node scripts/scraper-vilaviniteca.js --headless   (sin ventana)
 *   node scripts/scraper-vilaviniteca.js --max=50      (limitar a 50 productos)
 *
 * Resultado: genera scripts/vilaviniteca_precios.csv
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// --- Configuración ---
const BASE_URL = 'https://www.vilaviniteca.es/es/vinos/tipo-de-vino.html';
const OUTPUT_FILE = path.join(__dirname, 'vilaviniteca_precios.csv');
const DELAY_BETWEEN_PAGES = 2000; // ms entre páginas para no saturar
const MAX_PAGES = 100;            // límite de seguridad

// --- Parsear argumentos ---
const args = process.argv.slice(2);
const headless = args.includes('--headless');
const maxArg = args.find(a => a.startsWith('--max='));
const maxProducts = maxArg ? parseInt(maxArg.split('=')[1], 10) : Infinity;

async function run() {
    console.log('🚀 Iniciando scraper de vilaviniteca.es...');
    console.log(`   Modo: ${headless ? 'headless' : 'con ventana (visible)'}`);
    console.log(`   Límite productos: ${maxProducts === Infinity ? 'sin límite' : maxProducts}`);
    console.log('');

    const browser = await chromium.launch({
        headless,
        args: ['--disable-blink-features=AutomationControlled'],
    });

    const context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
        locale: 'es-ES',
        viewport: { width: 1366, height: 768 },
    });

    const page = await context.newPage();
    const allProducts = [];
    let currentPage = 1;

    try {
        // Navegar a la primera página
        console.log(`📄 Cargando página 1: ${BASE_URL}`);
        await page.goto(BASE_URL, { waitUntil: 'domcontentloaded', timeout: 30000 });

        // Esperar a que cargue contenido (dar tiempo a JS)
        await page.waitForTimeout(3000);

        // Aceptar cookies si aparece banner
        try {
            const cookieBtn = await page.$('button[id*="cookie"], button[class*="cookie"], .accept-cookies, #onetrust-accept-btn-handler, [data-action="accept"]');
            if (cookieBtn) {
                await cookieBtn.click();
                console.log('   ✅ Cookies aceptadas');
                await page.waitForTimeout(1000);
            }
        } catch (_) { /* sin banner de cookies */ }

        // Bucle de paginación
        while (currentPage <= MAX_PAGES) {
            // Intentar detectar los selectores de producto
            // Probar varios selectores comunes en tiendas online
            const products = await page.evaluate(() => {
                const items = [];

                // Selectores genéricos que cubren Magento, Prestashop, custom...
                const selectors = [
                    '.product-item',
                    '.product-card',
                    '.product',
                    '.item.product',
                    '[data-product]',
                    '.products-grid .item',
                    '.product-list .item',
                    'li.product-item',
                    '.card-product',
                    '.product-miniature',
                    '.product-tile',
                ];

                let productElements = [];
                for (const sel of selectors) {
                    const found = document.querySelectorAll(sel);
                    if (found.length > 0) {
                        productElements = found;
                        break;
                    }
                }

                // Si no encontramos con selectores específicos, buscar patrones de precio
                if (productElements.length === 0) {
                    // Buscar elementos que contengan precios (€)
                    const allElements = document.querySelectorAll('*');
                    // Fallback: intentar con estructura genérica
                    const containers = document.querySelectorAll('[class*="product"], [class*="wine"], [class*="vino"], [class*="item"]');
                    productElements = containers;
                }

                productElements.forEach(el => {
                    // Buscar nombre
                    const nameEl = el.querySelector('h2, h3, h4, .product-name, .product-title, .name, [class*="name"], [class*="title"], a[title]');
                    const name = nameEl
                        ? (nameEl.getAttribute('title') || nameEl.textContent || '').trim()
                        : '';

                    // Buscar precio
                    const priceEl = el.querySelector('.price, [class*="price"], [class*="precio"], .amount, [data-price]');
                    const priceText = priceEl ? priceEl.textContent.trim() : '';
                    const priceMatch = priceText.match(/([\d.,]+)\s*€/) || priceText.match(/€\s*([\d.,]+)/);
                    const price = priceMatch ? priceMatch[1].replace(',', '.') : priceText;

                    // Buscar bodega/denominación si está disponible
                    const extraEl = el.querySelector('.brand, .bodega, [class*="bodega"], [class*="winery"], [class*="denominacion"], .subtitle');
                    const extra = extraEl ? extraEl.textContent.trim() : '';

                    // Buscar enlace
                    const linkEl = el.querySelector('a[href]');
                    const link = linkEl ? linkEl.href : '';

                    if (name && name.length > 2) {
                        items.push({ name, price, extra, link });
                    }
                });

                // Deduplicar por nombre
                const seen = new Set();
                return items.filter(p => {
                    if (seen.has(p.name)) return false;
                    seen.add(p.name);
                    return true;
                });
            });

            if (products.length === 0 && currentPage === 1) {
                // Si no encontramos productos, volcar HTML para diagnóstico
                const html = await page.content();
                const debugFile = path.join(__dirname, 'vilaviniteca_debug.html');
                fs.writeFileSync(debugFile, html, 'utf-8');
                console.log(`⚠️  No se encontraron productos en la página.`);
                console.log(`   HTML guardado en: ${debugFile}`);
                console.log(`   Revisa el archivo para identificar los selectores correctos.`);
                break;
            }

            console.log(`   Página ${currentPage}: ${products.length} productos encontrados`);
            allProducts.push(...products);

            if (allProducts.length >= maxProducts) {
                allProducts.length = maxProducts;
                console.log(`   Límite de ${maxProducts} productos alcanzado.`);
                break;
            }

            // Buscar botón "siguiente" para paginación
            const nextPage = await page.$('a.next, a[rel="next"], .pages-item-next a, [class*="next"] a, li.next a, a[title="Siguiente"], .pagination .next a');
            if (!nextPage) {
                console.log('   No hay más páginas.');
                break;
            }

            // Navegar a la siguiente página
            await nextPage.click();
            currentPage++;
            console.log(`📄 Cargando página ${currentPage}...`);
            await page.waitForTimeout(DELAY_BETWEEN_PAGES);
            await page.waitForLoadState('domcontentloaded');
            await page.waitForTimeout(2000);
        }
    } catch (err) {
        console.error(`❌ Error: ${err.message}`);

        // Guardar screenshot para debug
        const screenshotFile = path.join(__dirname, 'vilaviniteca_error.png');
        await page.screenshot({ path: screenshotFile, fullPage: true });
        console.log(`   Screenshot guardado en: ${screenshotFile}`);
    } finally {
        await browser.close();
    }

    // Generar CSV
    if (allProducts.length > 0) {
        const csvHeader = 'Nombre;Precio;Bodega/DO;URL';
        const csvRows = allProducts.map(p => {
            const esc = (s) => `"${(s || '').replace(/"/g, '""')}"`;
            return `${esc(p.name)};${esc(p.price)};${esc(p.extra)};${esc(p.link)}`;
        });
        const csvContent = [csvHeader, ...csvRows].join('\n');
        fs.writeFileSync(OUTPUT_FILE, '\ufeff' + csvContent, 'utf-8'); // BOM para Excel

        console.log('');
        console.log(`✅ Scraping completado!`);
        console.log(`   Total productos: ${allProducts.length}`);
        console.log(`   Archivo CSV: ${OUTPUT_FILE}`);
        console.log('');
        console.log('Primeros 5 resultados:');
        allProducts.slice(0, 5).forEach((p, i) => {
            console.log(`   ${i + 1}. ${p.name} — ${p.price}€ ${p.extra ? '(' + p.extra + ')' : ''}`);
        });
    } else {
        console.log('');
        console.log('⚠️  No se extrajeron productos. Revisa el archivo de debug.');
    }
}

run().catch(err => {
    console.error('Error fatal:', err);
    process.exit(1);
});
