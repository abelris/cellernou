"""Prepara la carga de una tanda de fichas (textos, fotos y correcciones).

Uso: python cargar_tanda.py <carpeta_tanda> <lote> [archivos_en_prod.txt]

<carpeta_tanda>/resultados.json  lista de fichas revisadas:
    {"id", "descripcion", "nota_cata", "foto" (archivo en img/ o null),
     "tipo": "Vino Rosado" (opcional), "procedencia": "D.O. Rueda" (opcional)}
<carpeta_tanda>/img/             fotos tal como se descargaron

Deja en <carpeta_tanda>/out/ las fotos optimizadas (JPEG, fondo blanco,
máx. 800 px de alto) y <carpeta_tanda>/carga.sql. Los textos se cargan con
ON DUPLICATE KEY UPDATE; la foto solo si el producto no tiene principal;
tipo y procedencia quedan en productos_clasif_log con el lote.
"""
import json
import os
import re
import sys
import unicodedata

from PIL import Image, ImageChops, ImageDraw

ALTO_MAX = 800


def slug(texto):
    t = unicodedata.normalize('NFKD', texto).encode('ascii', 'ignore').decode().lower()
    return re.sub(r'[^a-z0-9]+', '-', t).strip('-')


def sql_str(s):
    return "'" + s.replace('\\', '\\\\').replace("'", "''") + "'"


def optimizar(origen, destino):
    img = Image.open(origen)
    img.load()
    if img.mode in ('RGBA', 'LA', 'P'):
        img = img.convert('RGBA')
        fondo = Image.new('RGB', img.size, (255, 255, 255))
        fondo.paste(img, mask=img.split()[-1])
        img = fondo
    else:
        img = img.convert('RGB')
    # Fondos claros pero no blancos (crema, gris de estudio): se blanquean
    # rellenando desde las esquinas, sin tocar la botella.
    for esquina in ((0, 0), (img.width - 1, 0), (0, img.height - 1), (img.width - 1, img.height - 1)):
        if min(img.getpixel(esquina)) > 200 and img.getpixel(esquina) != (255, 255, 255):
            ImageDraw.floodfill(img, esquina, (255, 255, 255), thresh=18)
    # Recortar el margen blanco (hay fotos cuadradas con la botella pequeña en
    # el centro) y dejar un 4 % de aire alrededor.
    caja = ImageChops.difference(img, Image.new('RGB', img.size, (255, 255, 255))).point(lambda v: 255 if v > 12 else 0).getbbox()
    if caja:
        m = round(max(caja[2] - caja[0], caja[3] - caja[1]) * 0.04)
        img = img.crop((max(caja[0] - m, 0), max(caja[1] - m, 0), min(caja[2] + m, img.width), min(caja[3] + m, img.height)))
    if img.height > ALTO_MAX:
        img = img.resize((round(img.width * ALTO_MAX / img.height), ALTO_MAX), Image.LANCZOS)
    img.save(destino, 'JPEG', quality=85, optimize=True, progressive=True)
    return img.size


def main():
    carpeta, lote = sys.argv[1], sys.argv[2]
    en_prod = set()
    if len(sys.argv) > 3:
        # Hay nombres antiguos en latin1 en el servidor: no se pueden leer como UTF-8.
        en_prod = {l.strip() for l in open(sys.argv[3], encoding='utf-8', errors='replace') if l.strip()}

    fichas = json.load(open(os.path.join(carpeta, 'resultados.json'), encoding='utf-8'))
    os.makedirs(os.path.join(carpeta, 'out'), exist_ok=True)

    sql = ["SET NAMES utf8mb4;"]
    for f in fichas:
        pid = int(f['id'])
        for tipo in ('descripcion', 'nota_cata'):
            if f.get(tipo):
                sql.append("INSERT INTO producto_textos (idProducto, tipo, contenido) VALUES "
                           f"({pid}, '{tipo}', {sql_str(f[tipo].strip())}) "
                           "ON DUPLICATE KEY UPDATE contenido = VALUES(contenido), updated_at = NOW();")

        if f.get('foto'):
            base = slug(os.path.splitext(f['foto'])[0]) or f'producto-{pid}'
            nombre = base + '.jpg'
            n = 2
            while nombre in en_prod:
                nombre = f'{base}-{n}.jpg'
                n += 1
            ancho, alto = optimizar(os.path.join(carpeta, 'img', f['foto']), os.path.join(carpeta, 'out', nombre))
            f['_foto_final'] = nombre
            print(f'  foto {pid}: {nombre} ({ancho}x{alto}, {os.path.getsize(os.path.join(carpeta, "out", nombre)) // 1024} KB)')
            sql.append("INSERT INTO producto_fotos (idProducto, foto, principal, orden) "
                       f"SELECT {pid}, {sql_str(nombre)}, 1, 0 FROM DUAL WHERE NOT EXISTS "
                       f"(SELECT 1 FROM producto_fotos WHERE idProducto = {pid} AND principal = 1);")

        cambios = []
        if f.get('tipo'):
            # COALESCE: si el nombre no existe, se queda como estaba en vez de quedar a NULL.
            cambios.append(f"idTipo = COALESCE((SELECT id FROM tipo_producto WHERE nombre_es = {sql_str(f['tipo'])} LIMIT 1), idTipo)")
        if f.get('procedencia'):
            cambios.append(f"idProcedencia = COALESCE((SELECT id FROM procedencia WHERE nombre_es = {sql_str(f['procedencia'])} LIMIT 1), idProcedencia)")
        if cambios:
            nota = '; '.join(x for x in [f.get('tipo') and 'tipo ' + f['tipo'], f.get('procedencia') and 'procedencia ' + f['procedencia']] if x)
            sql.append("INSERT INTO productos_clasif_log (idProducto, idSector_antes, idTipo_antes, idSector_nuevo, idTipo_nuevo, lote, nota) "
                       f"SELECT id, idSector, idTipo, idSector, idTipo, {sql_str(lote)}, "
                       f"CONCAT({sql_str(nota)}, ' | procedencia antes ', IFNULL(idProcedencia, 'NULL')) FROM productos WHERE id = {pid};")
            sql.append(f"UPDATE productos SET {', '.join(cambios)} WHERE id = {pid};")

    open(os.path.join(carpeta, 'carga.sql'), 'w', encoding='utf-8').write('\n'.join(sql) + '\n')
    json.dump(fichas, open(os.path.join(carpeta, 'resultados.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    print(f'{len(fichas)} fichas -> carga.sql ({len(sql) - 1} sentencias)')


if __name__ == '__main__':
    main()
