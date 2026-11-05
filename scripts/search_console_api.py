"""Consulta la API de Search Console de cellernou.com con una cuenta de servicio.

Uso:
  python search_console_api.py sitios
  python search_console_api.py consultas  [dias] > consultas.tsv   (página, consulta, clics, impresiones, posición)
  python search_console_api.py trafico    [dias] > seo_trafico.sql (mismo SQL que importar_search_console.py)

La clave (JSON de la cuenta de servicio seo-lectura@cellernou-seo) NO va en el
repositorio: se busca en SEARCH_CONSOLE_CLAVE o en C:/Users/PC/.claves/cellernou-seo-*.json.
La cuenta tiene permiso «Restringido» (solo lectura) en la propiedad.
Solo usa la librería estándar y `cryptography` para firmar el JWT.
"""
import base64
import datetime as dt
import glob
import json
import os
import re
import sys
import time
import urllib.parse
import urllib.request

from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.asymmetric import padding

ALCANCE = 'https://www.googleapis.com/auth/webmasters.readonly'
API = 'https://searchconsole.googleapis.com/webmasters/v3'


def clave():
    ruta = os.environ.get('SEARCH_CONSOLE_CLAVE') or next(iter(sorted(glob.glob('C:/Users/PC/.claves/cellernou-seo-*.json'))), None)
    if not ruta:
        sys.exit('No encuentro la clave: define SEARCH_CONSOLE_CLAVE')
    return json.load(open(ruta, encoding='utf-8'))


def b64(datos):
    return base64.urlsafe_b64encode(datos).rstrip(b'=').decode()


def token(k):
    ahora = int(time.time())
    cab = b64(json.dumps({'alg': 'RS256', 'typ': 'JWT'}).encode())
    cuerpo = b64(json.dumps({'iss': k['client_email'], 'scope': ALCANCE, 'aud': k['token_uri'],
                             'iat': ahora, 'exp': ahora + 3600}).encode())
    privada = serialization.load_pem_private_key(k['private_key'].encode(), password=None)
    firma = b64(privada.sign(f'{cab}.{cuerpo}'.encode(), padding.PKCS1v15(), hashes.SHA256()))
    datos = urllib.parse.urlencode({'grant_type': 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                                    'assertion': f'{cab}.{cuerpo}.{firma}'}).encode()
    return json.load(urllib.request.urlopen(k['token_uri'], datos))['access_token']


def llamar(tk, ruta, cuerpo=None):
    req = urllib.request.Request(API + ruta, data=json.dumps(cuerpo).encode() if cuerpo else None,
                                 headers={'Authorization': 'Bearer ' + tk, 'Content-Type': 'application/json'})
    return json.load(urllib.request.urlopen(req))


def propiedad(tk):
    sitios = [s['siteUrl'] for s in llamar(tk, '/sites').get('siteEntry', [])]
    for preferida in ('sc-domain:cellernou.com', 'https://cellernou.com/', 'https://www.cellernou.com/'):
        if preferida in sitios:
            return preferida
    sys.exit(f'La cuenta no ve cellernou.com. Propiedades visibles: {sitios}')


def filas(tk, sitio, dias, dimensiones):
    # Search Console va con 2-3 días de retraso.
    fin = dt.date.today() - dt.timedelta(days=3)
    ini = fin - dt.timedelta(days=dias - 1)
    todas, desde = [], 0
    while True:
        r = llamar(tk, f'/sites/{urllib.parse.quote(sitio, safe="")}/searchAnalytics/query',
                   {'startDate': ini.isoformat(), 'endDate': fin.isoformat(), 'dimensions': dimensiones,
                    'rowLimit': 25000, 'startRow': desde, 'dataState': 'final'})
        lote = r.get('rows', [])
        todas += lote
        if len(lote) < 25000:
            return todas, f'{ini}..{fin}'
        desde += 25000


def sql_str(s):
    return "'" + s.replace(chr(92), chr(92) * 2).replace("'", "''") + "'"


def main():
    orden = sys.argv[1] if len(sys.argv) > 1 else 'sitios'
    dias = int(sys.argv[2]) if len(sys.argv) > 2 else 90
    k = clave()
    tk = token(k)
    if orden == 'sitios':
        for s in llamar(tk, '/sites').get('siteEntry', []):
            print(s['siteUrl'], s['permissionLevel'])
        return
    sitio = propiedad(tk)
    sys.stdout.reconfigure(encoding='utf-8')
    if orden == 'consultas':
        rows, periodo = filas(tk, sitio, dias, ['page', 'query'])
        print('pagina\tconsulta\tclics\timpresiones\tposicion')
        for r in rows:
            pag, q = r['keys']
            print(f"{re.sub(r'^https?://[^/]+', '', pag) or '/'}\t{q}\t{r['clicks']:.0f}\t{r['impressions']:.0f}\t{r['position']:.1f}")
        print(f'# {sitio} {periodo}: {len(rows)} filas', file=sys.stderr)
    elif orden == 'trafico':
        rows, periodo = filas(tk, sitio, dias, ['page'])
        rutas = {}
        for r in rows:
            ruta = re.sub(r'^https?://[^/]+', '', r['keys'][0]) or '/'
            c, i, p = r['clicks'], r['impressions'], r['position']
            if ruta in rutas:
                c0, i0, p0 = rutas[ruta]
                p = (p0 * i0 + p * i) / (i0 + i) if i0 + i else p
                c, i = c0 + c, i0 + i
            rutas[ruta] = (c, i, p)
        print('SET NAMES utf8mb4;')
        print('DELETE FROM seo_trafico;')
        valores = []
        for ruta, (c, i, p) in rutas.items():
            m = re.match(r'/producto/(\d+)(/|$)', ruta)
            valores.append(f"({sql_str(ruta[:255])}, {m.group(1) if m else 'NULL'}, {c:.0f}, {i:.0f}, {p:.2f}, {sql_str(periodo)})")
        for n in range(0, len(valores), 200):
            print('INSERT INTO seo_trafico (ruta, idProducto, clics, impresiones, posicion, periodo) VALUES\n  '
                  + ',\n  '.join(valores[n:n + 200]) + ';')
        print(f'# {sitio} {periodo}: {len(rutas)} rutas', file=sys.stderr)
    else:
        sys.exit(__doc__)


if __name__ == '__main__':
    main()
