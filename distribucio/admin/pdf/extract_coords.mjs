// Extract text items with coordinates from a PDF using pdfjs-dist
import { readFileSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const pdfPath = join(__dirname, 'fac.pdf');

// Dynamic import of pdfjs-dist (will use the one from OCR Facturas if available)
async function run() {
    let pdfjsLib;
    try {
        pdfjsLib = await import('pdfjs-dist/legacy/build/pdf.mjs');
    } catch(e) {
        console.error('pdfjs-dist not found. Installing...');
        const { execSync } = await import('child_process');
        execSync('npm install pdfjs-dist', { cwd: __dirname, stdio: 'inherit' });
        pdfjsLib = await import('pdfjs-dist/legacy/build/pdf.mjs');
    }

    const data = new Uint8Array(readFileSync(pdfPath));
    const pdf = await pdfjsLib.getDocument({ data, useSystemFonts: true }).promise;
    console.log(`Pages: ${pdf.numPages}`);

    const page = await pdf.getPage(1);
    const textContent = await page.getTextContent();

    // Filter non-empty items
    const items = textContent.items
        .filter(it => it.str && it.str.trim().length > 0)
        .map(it => ({
            str: it.str,
            x: it.transform[4],
            y: it.transform[5],
            width: it.width,
            height: it.height
        }));

    console.log(`\nTotal non-empty items: ${items.length}\n`);

    // Group by line (similar y, threshold based on avg height)
    const avgHeight = items.reduce((s, it) => s + (it.height || 0), 0) / items.length;
    const lineThreshold = avgHeight * 0.6;

    const sorted = items.slice().sort((a, b) => {
        if (Math.abs(a.y - b.y) > lineThreshold) return b.y - a.y;
        return a.x - b.x;
    });

    const lines = [];
    let curLine = null;
    for (const it of sorted) {
        if (!curLine || Math.abs(it.y - curLine.y) > lineThreshold) {
            curLine = { y: it.y, items: [it] };
            lines.push(curLine);
        } else {
            curLine.items.push(it);
        }
    }
    for (const line of lines) {
        line.items.sort((a, b) => a.x - b.x);
    }

    // Collect all gaps
    const allGaps = [];

    console.log('=== LINES WITH GAPS ===\n');
    for (let li = 0; li < lines.length; li++) {
        const line = lines[li];
        const parts = [];
        for (let i = 0; i < line.items.length; i++) {
            const it = line.items[i];
            let gap = '';
            if (i > 0) {
                const prev = line.items[i - 1];
                const g = it.x - (prev.x + prev.width);
                gap = `  [gap=${g.toFixed(1)}]  `;
                allGaps.push(g);
            }
            parts.push(gap + `"${it.str}" (x=${it.x.toFixed(1)} w=${it.width.toFixed(1)})`);
        }
        console.log(`LINE ${li} (y=${line.y.toFixed(1)}):`);
        console.log(`  ${parts.join('')}`);

        // Also show reconstructed text with | for large gaps
        const textParts = [];
        for (let i = 0; i < line.items.length; i++) {
            if (i > 0) {
                const prev = line.items[i - 1];
                const g = line.items[i].x - (prev.x + prev.width);
                // Mark column boundary
                if (g > avgHeight) {
                    textParts.push(' | ');
                } else if (g > avgHeight * 0.3) {
                    textParts.push(' ');
                }
            }
            textParts.push(line.items[i].str);
        }
        console.log(`  => ${textParts.join('')}`);
        console.log('');
    }

    // Gap statistics
    if (allGaps.length > 0) {
        allGaps.sort((a, b) => a - b);
        const median = allGaps[Math.floor(allGaps.length / 2)];
        const min = allGaps[0];
        const max = allGaps[allGaps.length - 1];
        const avg = allGaps.reduce((s, g) => s + g, 0) / allGaps.length;
        console.log(`\n=== GAP STATISTICS ===`);
        console.log(`Count: ${allGaps.length}`);
        console.log(`Min: ${min.toFixed(1)}, Max: ${max.toFixed(1)}`);
        console.log(`Avg: ${avg.toFixed(1)}, Median: ${median.toFixed(1)}`);
        console.log(`Avg char height: ${avgHeight.toFixed(1)}`);
        console.log(`\nLargest gaps (>10px):`);
        const largeGaps = allGaps.filter(g => g > 10).sort((a, b) => b - a);
        for (const g of largeGaps.slice(0, 30)) {
            console.log(`  ${g.toFixed(1)}`);
        }
    }
}

run().catch(console.error);
