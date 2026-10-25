"""Valida una tanda de textos de categoría y prepara su carga.

Uso: python docs/seo/textos-categoria/cargar.py tanda1 [--base http://127.0.0.1:8088]

Lee <tanda>.py (lista TEXTOS) y deja junto a él:
  <tanda>.sql            INSERT ... ON DUPLICATE KEY UPDATE en categoria_textos
  <tanda>-revision.html  página para revisar los textos tal como se verán

Comprueba: primera frase de la intro <= 155 caracteres (es la meta
description), longitud del texto, número de FAQ y que cada enlace interno
responda 200 en --base (si se pasa).
"""
import html
import importlib.util
import json
import os
import re
import sys
import urllib.request

AQUI = os.path.dirname(os.path.abspath(__file__))


def sql_str(s):
    if s is None:
        return 'NULL'
    return "'" + s.replace('\\', '\\\\').replace("'", "''") + "'"


def primera_frase(t):
    # Fin de frase: punto tras minúscula y antes de mayúscula (no corta "D.O.").
    m = re.match(r'^(.+?[a-záéíóúñ][.!?])\s+[A-ZÁÉÍÓÚÑ¿]', t.strip())
    return m.group(1) if m else t.strip()


def palabras(h):
    return len(re.sub(r'<[^>]+>', ' ', h).split())


def main():
    args = sys.argv[1:]
    if not args:
        sys.exit(__doc__)
    tanda = args[0]
    base = args[args.index('--base') + 1] if '--base' in args else None

    spec = importlib.util.spec_from_file_location(tanda, os.path.join(AQUI, tanda + '.py'))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    textos = mod.TEXTOS

    avisos = []
    enlaces = set()
    for t in textos:
        pf = primera_frase(t['intro'])
        if len(pf) > 155:
            avisos.append(f"{t['pagina']}: primera frase de {len(pf)} caracteres")
        n = palabras(t['intro']) + palabras(t['cuerpo'])
        if n < 150:
            avisos.append(f"{t['pagina']}: solo {n} palabras")
        if not 2 <= len(t['faqs']) <= 5:
            avisos.append(f"{t['pagina']}: {len(t['faqs'])} FAQ")
        enlaces.update(re.findall(r'href="(/[^"]*)"', t['cuerpo']))

    if base:
        for e in sorted(enlaces):
            try:
                codigo = urllib.request.urlopen(base + e, timeout=60).status
            except Exception as ex:
                codigo = getattr(ex, 'code', ex)
            if codigo != 200:
                avisos.append(f'enlace {e}: {codigo}')

    # SQL
    filas = []
    for t in textos:
        faqs = json.dumps([{'pregunta': p, 'respuesta': r} for p, r in t['faqs']], ensure_ascii=False)
        filas.append('(' + ', '.join([
            sql_str(t['ambito']), str(int(t['id_ref'])), sql_str(t.get('titulo_seo')),
            sql_str(t['intro']), sql_str(t['cuerpo']), sql_str(faqs), '1']) + ')')
    sql = ('-- ' + tanda + ': ' + str(len(textos)) + ' textos de categoría (docs/seo/textos-categoria/' + tanda + '.py)\n'
           'SET NAMES utf8mb4;\n'
           'INSERT INTO categoria_textos (ambito, id_ref, titulo_seo, intro, cuerpo, faqs, publicado) VALUES\n'
           + ',\n'.join(filas) + '\n'
           'ON DUPLICATE KEY UPDATE titulo_seo = VALUES(titulo_seo), intro = VALUES(intro), cuerpo = VALUES(cuerpo),\n'
           '                        faqs = VALUES(faqs), publicado = VALUES(publicado);\n')
    with open(os.path.join(AQUI, tanda + '.sql'), 'w', encoding='utf-8', newline='\n') as f:
        f.write(sql)

    # Página de revisión
    bloques = []
    for i, t in enumerate(textos, 1):
        pf = primera_frase(t['intro'])
        faqs = ''.join(f'<details><summary>{html.escape(p)}</summary><p>{html.escape(r)}</p></details>' for p, r in t['faqs'])
        titulo = html.escape(t.get('titulo_seo') or '(título automático)')
        bloques.append(f'''<article id="t{i}">
<p class="ruta">{i}. <a href="https://cellernou.com{t['pagina']}" target="_blank">cellernou.com{t['pagina']}</a></p>
<p class="meta"><b>Title:</b> {titulo}<br><b>Meta description</b> ({len(pf)} car.): {html.escape(pf)}</p>
<p class="intro">{html.escape(t['intro'])}</p>
<div class="cuerpo">{t['cuerpo']}</div>
<h3>Preguntas frecuentes</h3>{faqs}
<p class="palabras">{palabras(t['intro']) + palabras(t['cuerpo'])} palabras</p>
</article>''')
    indice = ''.join(f'<li><a href="#t{i}">{html.escape(t["pagina"])}</a></li>' for i, t in enumerate(textos, 1))
    pagina = f'''<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Textos de categoría · {tanda}</title>
<style>
:root {{ --fg:#222; --bg:#fff; --muted:#666; --line:#e5e5e5; --acc:#722F37; --alt:#f8f7f5; }}
@media (prefers-color-scheme: dark) {{ :root {{ --fg:#eee; --bg:#1b1b1b; --muted:#aaa; --line:#333; --acc:#e7a1a8; --alt:#242424; }} }}
body {{ font: 16px/1.65 system-ui, sans-serif; color: var(--fg); background: var(--bg); margin: 0; padding: 0 16px; }}
main {{ max-width: 820px; margin: 0 auto; padding: 24px 0 80px; }}
h1 {{ font-size: 1.6rem; }} a {{ color: var(--acc); }}
article {{ border-top: 3px solid var(--acc); padding: 16px 0 24px; margin-top: 32px; }}
.ruta {{ font-weight: 600; }} .meta {{ background: var(--alt); padding: 10px 12px; font-size: .9rem; }}
.intro {{ font-size: 1.1rem; font-style: italic; }} .palabras {{ color: var(--muted); font-size: .85rem; }}
details {{ border: 1px solid var(--line); padding: 6px 10px; margin: 4px 0; }} summary {{ cursor: pointer; font-weight: 600; }}
.avisos {{ background: #fff3cd; color: #5c4400; padding: 10px 14px; }}
</style></head><body><main>
<h1>Textos de categoría · {tanda} ({len(textos)} páginas)</h1>
<p>Para revisar antes de publicar. La primera frase de cada introducción es la meta description. Lo que haya que cambiar, dímelo con el número de la página.</p>
{'<div class="avisos"><b>Avisos:</b><br>' + '<br>'.join(html.escape(a) for a in avisos) + '</div>' if avisos else ''}
<ol>{indice}</ol>
{''.join(bloques)}
</main></body></html>'''
    with open(os.path.join(AQUI, tanda + '-revision.html'), 'w', encoding='utf-8', newline='\n') as f:
        f.write(pagina)

    print(f'{len(textos)} textos, {len(enlaces)} enlaces internos')
    for a in avisos:
        print('AVISO', a)


if __name__ == '__main__':
    main()
