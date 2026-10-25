"""Convierte la exportación de Search Console en SQL para la tabla seo_trafico.

Uso: python importar_search_console.py <zip_de_search_console> <periodo> > seo_trafico.sql

<periodo> es una etiqueta, p. ej. "2026-07-24..2026-10-23". Se carga en
producción con `mariadb cellernou_com < seo_trafico.sql`. La tabla se vacía y
se rellena entera: siempre refleja la última exportación.

La web la usa para no quitar del índice fichas que ya traen visitas aunque no
tengan stock ni texto (functions.php, getIdsProductosConTrafico).
"""
import csv
import io
import re
import sys
import zipfile


def nombre(info):
    # Los zip de Search Console guardan los nombres en UTF-8 sin marcarlo.
    try:
        return info.filename.encode('cp437').decode('utf-8')
    except UnicodeError:
        return info.filename


def sql_str(s):
    return "'" + s.replace('\\', '\\\\').replace("'", "''") + "'"


def main():
    zip_path, periodo = sys.argv[1], sys.argv[2]
    z = zipfile.ZipFile(zip_path)
    paginas = next(i for i in z.infolist() if re.match(r'P.ginas\.csv$|Pages\.csv$', nombre(i)))
    filas = list(csv.reader(io.TextIOWrapper(z.open(paginas), encoding='utf-8-sig')))[1:]

    print("SET NAMES utf8mb4;")
    print("CREATE TABLE IF NOT EXISTS seo_trafico (\n"
          "  ruta VARCHAR(255) NOT NULL PRIMARY KEY,\n"
          "  idProducto INT NULL,\n"
          "  clics INT NOT NULL DEFAULT 0,\n"
          "  impresiones INT NOT NULL DEFAULT 0,\n"
          "  posicion DECIMAL(6,2) NULL,\n"
          "  periodo VARCHAR(40) NOT NULL,\n"
          "  actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n"
          "  KEY (idProducto)\n"
          ") DEFAULT CHARSET=utf8mb4;")
    print("DELETE FROM seo_trafico;")

    # La misma ruta puede venir con varios hosts (www, http, subdominios): se suman.
    rutas = {}
    for url, clics, impr, _ctr, pos in filas:
        ruta = re.sub(r'^https?://[^/]+', '', url) or '/'
        c, i, p = int(clics), int(impr), float(pos)
        if ruta in rutas:
            c0, i0, p0 = rutas[ruta]
            p = (p0 * i0 + p * i) / (i0 + i) if i0 + i else p
            c, i = c0 + c, i0 + i
        rutas[ruta] = (c, i, p)

    valores = []
    for ruta, (c, i, p) in rutas.items():
        m = re.match(r'/producto/(\d+)(/|$)', ruta)
        pid = m.group(1) if m else 'NULL'
        valores.append(f"({sql_str(ruta[:255])}, {pid}, {c}, {i}, {p:.2f}, {sql_str(periodo)})")
    for n in range(0, len(valores), 200):
        print("INSERT INTO seo_trafico (ruta, idProducto, clics, impresiones, posicion, periodo) VALUES\n  "
              + ",\n  ".join(valores[n:n + 200]) + ";")


if __name__ == '__main__':
    main()
