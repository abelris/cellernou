"""Nombre, sector, tipo, procedencia e IVA de cada producto del catálogo Espinaler."""
import html, json, os, re

A = os.path.dirname(os.path.abspath(__file__))
c = json.load(open(os.path.join(A, 'catalogo.json'), encoding='utf-8'))

FUERA = re.compile(r'juanita calamidad|señorío de montanera|jamonera|^tienda online', re.I)
YA_NOMBRES = {'aceitunas espinaler rellenas (350gr)'}  # = 1646

# Tipos nuevos (se crean en la carga): claves simbólicas
T_VEG, T_ENC, T_MAR = 'NUEVO_VEGETALES', 'NUEVO_ENCURTIDOS', 'NUEVO_MARISCO'


def nombre_web(n):
    n = html.unescape(n).replace('&amp;', '&').strip()
    n = re.sub(r'\s+', ' ', n)
    if 'espinaler' not in n.lower():
        m = re.search(r'\s(\d+/\d+|\d+\s*x\s*\d+\s*\w+|\(.*\))$', n)
        n = (n[:m.start()] + ' Espinaler' + n[m.start():]) if m else n + ' Espinaler'
    return n[:100]


def clasificar(x):
    n = x['nombre'].lower()
    cat = x['cat']
    g = x['grupo']
    # Bebidas
    if 'vermut' in n and g != 'pack' and 'pack' not in n and 'trío' not in n and 'lote' not in n:
        return 2, 8, 10, 21
    if 'trío de vermuts' in n:
        return 2, 8, 10, 21
    if 'sangría' in n:
        return 2, 118, 10, 21
    if 'ratafía' in n:
        return 2, 55, 10, 21
    if 'limoncello' in n:
        return 2, 21, 10, 21
    if 'moscatel' in n:
        return 8, 60, 10, 21
    if 'rancio' in n:
        return 8, 62, 10, 21
    if 'cava' in n:
        return 14, 22, 80, 21
    # Packs y lotes
    if g == 'pack':
        return 12, 116, 0, (21 if re.search(r'vermut', n) else 10)
    # Salsas, aceites, vinagres
    if re.search(r'vinagre|aceite picante', n):
        return 12, 104, 0, 10
    if re.search(r'salsa|alioli|tomate frito', n) and 'patatas' not in n:
        return 12, 125, 0, 10
    # Patés (antes que aceitunas: «paté de aceituna»)
    if 'paté' in n:
        return 12, (T_VEG if 'aceituna' in n else T_MAR), 0, 10
    if 'langostillo' in n:
        return 12, 151, 0, 10
    # Aperitivos
    if 'patatas' in n or 'chips' in n:
        return 12, 109, 0, 10
    if re.search(r'aceituna|olivas', n):
        return 12, (47 if 'negra' in n else 46), 0, 10
    if re.search(r'pepinillo|alcaparra|piparra|banderilla', n):
        return 12, T_ENC, 0, 10
    if 'paleta ibérica' in n:
        return 12, 33, 0, 10
    if 'chocolate' in n:
        return 12, 162, 0, 10
    if re.search(r'almendra|torrezno', n):
        return 12, 56, 0, 10
    # Conservas
    if 'mejill' in n:
        return 12, 119, 0, 10
    if 'berberech' in n:
        return 12, 73, 0, 10
    if re.search(r'almej', n):
        return 12, 120, 0, 10
    if 'navaj' in n:
        return 12, 145, 0, 10
    if 'sardin' in n:
        return 12, 167, 0, 10
    if re.search(r'bonito|atún|atun|ventresca', n):
        return 12, 169, 0, 10
    if 'anchoa' in n:
        return 12, 71, 0, 10
    if 'boquer' in n:
        return 12, 146, 0, 10
    if 'pulpo' in n:
        return 12, 168, 0, 10
    if 'chipir' in n:
        return 12, 147, 0, 10
    if 'calamar' in n:
        return 12, 101, 0, 10
    if 'alcachofa' in n:
        return 12, 136, 0, 10
    if 'vegetales' in cat or re.search(r'lenteja|alubia|garbanzo|judía|pimiento|espárrago|paté', n):
        return 12, T_VEG, 0, 10
    if 'conservas-marisco' in cat:
        return 12, T_MAR, 0, 10
    return 12, 56, 0, 10


plan = []
for x in c:
    if FUERA.search(x['nombre']) or x['nombre'].lower() in YA_NOMBRES or x['grupo'] == 'https:':
        continue
    sec, tipo, proc, iva = clasificar(x)
    plan.append({**x, 'nombre_web': nombre_web(x['nombre']), 'idSector': sec, 'idTipo': tipo, 'idProcedencia': proc, 'iva': iva})

# Nombres repetidos tras normalizar: se marca para revisar
vistos = {}
for p in plan:
    k = p['nombre_web'].lower()
    vistos[k] = vistos.get(k, 0) + 1
rep = [k for k, v in vistos.items() if v > 1]
json.dump(plan, open(os.path.join(A, 'plan.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
import collections
print(len(plan), 'productos'); print(collections.Counter((p['idSector'], p['idTipo']) for p in plan))
print('repetidos:', rep)
for p in plan:
    if p['idTipo'] in (56,) or str(p['idTipo']).startswith('NUEVO'):
        print(p['idTipo'], '|', p['nombre_web'])
