"""Paso 1: SQL de alta de los productos del catálogo Espinaler (tipos nuevos, productos, precio 0).
Paso 2 (con ids.tsv del servidor): resultados.json para scripts/cargar_tanda.py (textos y fotos)."""
import json, os, sys

A = os.path.dirname(os.path.abspath(__file__))
LOTE = 'espcat-20261115'
TIPOS = {
    'NUEVO_VEGETALES': ('Conservas vegetales', 'Conserves vegetals'),
    'NUEVO_ENCURTIDOS': ('Encurtidos', 'Encurtits'),
    'NUEVO_MARISCO': ('Marisco y pescado en conserva', 'Marisc i peix en conserva'),
}


def q(s):
    return "'" + str(s).replace('\\', '\\\\').replace("'", "''") + "'"


plan = json.load(open(os.path.join(A, 'plan.json'), encoding='utf-8'))
textos = {}
for i in range(1, 7):
    f = os.path.join(A, f'resultado-{i}.json')
    for r in json.load(open(f, encoding='utf-8')):
        textos[r['url']] = r

if sys.argv[1:] == ['alta']:
    sql = ["SET NAMES utf8mb4;"]
    for k, (es, cat) in TIPOS.items():
        sql.append(f"INSERT INTO tipo_producto (nombre, nombre_es, nombre_cat, descripcion, activo, idSector) "
                   f"SELECT {q(es)}, {q(es)}, {q(cat)}, '', 1, 12 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tipo_producto WHERE nombre_es = {q(es)});")
    for p in plan:
        tipo = (f"(SELECT id FROM tipo_producto WHERE nombre_es = {q(TIPOS[p['idTipo']][0])} LIMIT 1)"
                if str(p['idTipo']).startswith('NUEVO') else str(int(p['idTipo'])))
        n = q(p['nombre_web'])
        sql.append(
            "INSERT INTO productos (codigo, nombre, descripcion, idSector, idTipo, idProcedencia, unidades, precio_medio, "
            "precio_venta_dis, foto, iva, activo, uvas_es, alta) "
            f"SELECT '', {n}, '', {int(p['idSector'])}, {tipo}, {int(p['idProcedencia'])}, 0, 0, 0, '', {int(p['iva'])}, 1, '', NOW() "
            f"FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = {n});")
        sql.append(f"INSERT INTO precios_producto_tienda (idTienda, idProducto, pvp) SELECT 1, id, 0 FROM productos WHERE nombre = {n} "
                   f"AND NOT EXISTS (SELECT 1 FROM precios_producto_tienda pt WHERE pt.idProducto = productos.id AND pt.idTienda = 1);")
        sql.append(f"INSERT INTO productos_clasif_log (idProducto, idSector_antes, idTipo_antes, idSector_nuevo, idTipo_nuevo, lote, nota) "
                   f"SELECT id, 0, 0, idSector, idTipo, '{LOTE}', {q('Alta catálogo Espinaler: ' + p['url'])} FROM productos WHERE nombre = {n} "
                   f"AND NOT EXISTS (SELECT 1 FROM productos_clasif_log l WHERE l.idProducto = productos.id AND l.lote = '{LOTE}');")
    open(os.path.join(A, 'alta.sql'), 'w', encoding='utf-8').write('\n'.join(sql) + '\n')
    nombres = '\n'.join(p['nombre_web'] for p in plan)
    open(os.path.join(A, 'nombres.txt'), 'w', encoding='utf-8').write(nombres + '\n')
    print('alta.sql:', len(plan), 'productos; textos disponibles:', sum(1 for p in plan if p['url'] in textos))

elif sys.argv[1:] == ['tanda']:
    ids = {}
    for line in open(os.path.join(A, 'ids.tsv'), encoding='utf-8'):
        i, n = line.rstrip('\n').split('\t', 1)
        ids[n] = int(i)
    os.makedirs(os.path.join(A, 'tanda', 'img'), exist_ok=True)
    res = []
    import shutil
    for p in plan:
        pid = ids.get(p['nombre_web'])
        t = textos.get(p['url'])
        if not pid or not t:
            print('FALTA', p['nombre_web'], pid, bool(t))
            continue
        foto = None
        if p.get('img_local'):
            ext = os.path.splitext(p['img_local'])[1]
            foto = f"{pid}-{os.path.splitext(os.path.basename(p['img_local']))[0]}{ext}"
            shutil.copy(os.path.join(A, p['img_local']), os.path.join(A, 'tanda', 'img', foto))
        res.append({'id': pid, 'descripcion': t['descripcion'], 'nota_cata': t.get('nota_cata'), 'foto': foto, 'tipo': None, 'procedencia': None})
    json.dump(res, open(os.path.join(A, 'tanda', 'resultados.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    print('tanda:', len(res))
