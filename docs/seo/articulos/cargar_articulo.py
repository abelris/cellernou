"""Prepara la carga de un artículo de WineWorld.

Uso: python docs/seo/articulos/cargar_articulo.py <archivo sin .py> <fecha AAAA-MM-DD HH:MM> [--borrador]

Lee <archivo>.py (dict ARTICULO) y deja junto a él:
  <archivo>.sql            artículo (por slug, ON DUPLICATE KEY UPDATE) y sus relaciones
  <archivo>-revision.html  el artículo para revisarlo antes de publicar

Con una fecha futura el artículo queda programado: sale solo ese día
(sqlWWVisible en includes/functions.php). --borrador lo deja sin publicar.
"""
import html
import importlib.util
import json
import os
import re
import sys

AQUI = os.path.dirname(os.path.abspath(__file__))


def sql_str(s):
    if s is None:
        return 'NULL'
    return "'" + s.replace('\\', '\\\\').replace("'", "''") + "'"


def main():
    if len(sys.argv) < 3:
        sys.exit(__doc__)
    nombre, fecha = sys.argv[1], sys.argv[2]
    publicado = 0 if '--borrador' in sys.argv else 1
    spec = importlib.util.spec_from_file_location(nombre, os.path.join(AQUI, nombre + '.py'))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    a = mod.ARTICULO

    faqs = json.dumps([{'pregunta': p, 'respuesta': r} for p, r in a['faqs']], ensure_ascii=False)
    slug = sql_str(a['slug'])
    rel = ',\n'.join(f"((SELECT id FROM ww_articulos WHERE slug = {slug}), '{amb}', {int(i)}, {1 if n == 0 else 0})"
                     for n, (amb, i) in enumerate(a['relaciones']))
    sql = f"""-- Artículo {a['slug']} (docs/seo/articulos/{nombre}.py)
SET NAMES utf8mb4;
INSERT INTO ww_articulos (id_categoria, titulo, slug, extracto, contenido, consejo, faqs, autor, imagen, publicado, fecha_publicacion)
VALUES ({int(a['id_categoria'])}, {sql_str(a['titulo'])}, {slug}, {sql_str(a['extracto'])}, {sql_str(a['contenido'])},
        {sql_str(a['consejo'])}, {sql_str(faqs)}, {sql_str(a['autor'])}, {sql_str(a.get('imagen'))}, {publicado}, '{fecha}:00')
ON DUPLICATE KEY UPDATE id_categoria = VALUES(id_categoria), titulo = VALUES(titulo), extracto = VALUES(extracto),
    contenido = VALUES(contenido), consejo = VALUES(consejo), faqs = VALUES(faqs), autor = VALUES(autor),
    imagen = COALESCE(VALUES(imagen), imagen), publicado = VALUES(publicado), fecha_publicacion = VALUES(fecha_publicacion);
DELETE FROM ww_articulo_relaciones WHERE id_articulo = (SELECT id FROM ww_articulos WHERE slug = {slug});
INSERT INTO ww_articulo_relaciones (id_articulo, ambito, id_ref, principal) VALUES
{rel};
"""
    with open(os.path.join(AQUI, nombre + '.sql'), 'w', encoding='utf-8', newline='\n') as f:
        f.write(sql)

    palabras = len(re.sub(r'<[^>]+>', ' ', a['contenido']).split())
    faqs_html = ''.join(f'<details><summary>{html.escape(p)}</summary><p>{html.escape(r)}</p></details>' for p, r in a['faqs'])
    pagina = f'''<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Revisión · {html.escape(a['titulo'])}</title>
<style>
:root {{ --fg:#222; --bg:#fff; --muted:#666; --line:#e5e5e5; --acc:#722F37; --alt:#f8f7f5; }}
@media (prefers-color-scheme: dark) {{ :root {{ --fg:#eee; --bg:#1b1b1b; --muted:#aaa; --line:#333; --acc:#e7a1a8; --alt:#242424; }} }}
body {{ font: 17px/1.75 Georgia, serif; color: var(--fg); background: var(--bg); margin: 0; padding: 0 16px; }}
main {{ max-width: 760px; margin: 0 auto; padding: 24px 0 80px; }}
a {{ color: var(--acc); }} h1, h2, h3 {{ font-family: system-ui, sans-serif; line-height: 1.25; }}
.meta, .consejo {{ font: 15px/1.6 system-ui, sans-serif; background: var(--alt); padding: 12px 14px; }}
.consejo {{ border-left: 4px solid #C9A962; }}
details {{ border: 1px solid var(--line); padding: 6px 10px; margin: 4px 0; font-family: system-ui, sans-serif; }}
summary {{ cursor: pointer; font-weight: 600; }}
</style></head><body><main>
<p class="meta"><b>Para revisar.</b> Se publicará el {html.escape(fecha)} en /wineworld/guias-de-vino/{html.escape(a['slug'])} · {palabras} palabras<br>
<b>Meta description:</b> {html.escape(a['extracto'])} ({len(a['extracto'])} car.)<br>
<b>Vende:</b> {html.escape(', '.join(f'{x} {y}' for x, y in a['relaciones']))} (la primera, con 4 fichas con stock al final)</p>
<h1>{html.escape(a['titulo'])}</h1>
<p><i>{html.escape(a['extracto'])}</i></p>
{a['contenido']}
<div class="consejo"><b>Te lo aconsejamos</b><br>{html.escape(a['consejo'])}<br>[WhatsApp] [Llámanos] [Cómo llegar]</div>
<h2>Preguntas frecuentes</h2>{faqs_html}
</main></body></html>'''
    with open(os.path.join(AQUI, nombre + '-revision.html'), 'w', encoding='utf-8', newline='\n') as f:
        f.write(pagina)
    print(f"{a['slug']}: {palabras} palabras, {len(a['faqs'])} FAQ, extracto {len(a['extracto'])} car.")


if __name__ == '__main__':
    main()
