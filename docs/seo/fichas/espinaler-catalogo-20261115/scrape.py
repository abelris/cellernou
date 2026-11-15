"""Lee las fichas de tienda.espinaler.com (catálogo Espinaler, con permiso de la marca)."""
import html, json, os, re, sys, time, urllib.request

A = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(A, '..', 'espinaler', 'tienda-productos.json')
UA = {'User-Agent': 'Mozilla/5.0 (catalogo El Celler Nou)'}

# Ya en el catálogo de El Celler Nou (15-nov-2026)
YA = {
    'ofertas/salsa-espinaler-92ml/', 'ofertas/salsa-espinaler-250ml/', 'ofertas/salsa-espinaler-750ml/',
    'ofertas/salsa-espinaler-200ml/', 'salsas/salsa-espinaler-picante-92ml/', 'bebidas/vermut-negro-espinaler/',
    'bebidas/vermut-blanco-espinaler/', 'bebidas/vermut-negro-vintage-50cl/',
    'conservas/conservas-marisco/berberechos/berberechos-al-natural-45-55/',
    'conservas/conservas-marisco/berberechos/berberechos-al-natural-55-65/',
    'conservas/conservas-marisco/berberechos/berberechos-al-natural-35-45/',
    'conservas/conservas-marisco/mejillones/mejillones-en-escabeche-10-12/',
    'conservas/conservas-marisco/navajas/navajas-al-natural-5-8/',
    'regala/cestas-lotes-packs/pack-espinaler-garbi/',
    'conservas/conservas-marisco/almejas/almeja-al-natural-ol-120/',
}


def grupo(s):
    if 'pepus' in s:
        return None
    if 'merchandising' in s or re.search(r'\bbol-|lata-metalica', s):
        return None
    if re.search(r'-(2|5)l/$|-10-l/$', s):
        return None
    if 'regala' in s or re.search(r'\bpack-|lote-', s):
        return 'pack'
    return s.split('/')[0]


def limpiar(t):
    t = re.sub(r'<(script|style)[^>]*>.*?</\1>', ' ', t, flags=re.S)
    t = re.sub(r'<br\s*/?>|</p>|</li>|</h\d>', '\n', t)
    t = re.sub(r'<[^>]+>', ' ', t)
    t = html.unescape(t)
    return re.sub(r'[ \t]+', ' ', re.sub(r'\n\s*\n+', '\n', t)).strip()


def leer(url):
    for i in range(3):
        try:
            return urllib.request.urlopen(urllib.request.Request(url, headers=UA), timeout=30).read().decode('utf-8', 'replace')
        except Exception as e:
            err = e
            time.sleep(2)
    raise err


prods = json.load(open(SRC, encoding='utf-8'))
salida = []
vistos = set()
for p in prods:
    s = p['url'].split('/producto/')[-1]
    g = grupo(s)
    if not g or s in YA:
        continue
    h = leer(p['url'])
    ld = {}
    for m in re.finditer(r'<script type="application/ld\+json"[^>]*>(.*?)</script>', h, re.S):
        try:
            d = json.loads(m.group(1))
        except Exception:
            continue
        for x in (d.get('@graph', [d]) if isinstance(d, dict) else d):
            if isinstance(x, dict) and x.get('@type') == 'Product':
                ld = x
    nombre = html.unescape(ld.get('name') or (re.search(r'<h1[^>]*>(.*?)</h1>', h, re.S) or [None, ''])[1]).strip()
    nombre = re.sub(r'<[^>]+>', '', nombre).strip()
    if nombre.lower() in vistos:
        continue
    vistos.add(nombre.lower())
    corta = re.search(r'woocommerce-product-details__short-description[^>]*>(.*?)</div>', h, re.S)
    larga = re.search(r'id="tab-description"[^>]*>(.*?)</div>\s*(?:<div class="woocommerce-Tabs-panel|</div>)', h, re.S)
    attrs = dict((limpiar(a), limpiar(b)) for a, b in re.findall(r'<th[^>]*class="[^"]*woocommerce-product-attributes-item__label[^"]*"[^>]*>(.*?)</th>\s*<td[^>]*>(.*?)</td>', h, re.S))
    oferta = ld.get('offers', [{}])
    oferta = oferta[0] if isinstance(oferta, list) and oferta else (oferta if isinstance(oferta, dict) else {})
    salida.append({
        'url': p['url'], 'grupo': g, 'cat': s.rsplit('/', 2)[0], 'nombre': nombre,
        'sku': ld.get('sku'), 'gtin': ld.get('gtin13') or ld.get('gtin') or ld.get('gtin8'),
        'precio': (oferta.get('price') or (oferta.get('priceSpecification') or [{}])[0].get('price') if isinstance(oferta.get('priceSpecification'), list) else oferta.get('price')),
        'img': p.get('img'),
        'corta': limpiar(corta.group(1)) if corta else '',
        'larga': limpiar(larga.group(1))[:2500] if larga else limpiar(ld.get('description', ''))[:2500],
        'atributos': attrs,
    })
    print(len(salida), g, nombre[:60], flush=True)
    time.sleep(0.6)

json.dump(salida, open(os.path.join(A, 'catalogo.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print('TOTAL', len(salida))
