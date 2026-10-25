-- Ámbito 'origen' en categoria_textos y ww_articulo_relaciones (25-oct-2026).
--
-- Los orígenes de cerveza (/cervezas/origen/{id}) usaban el ámbito
-- 'procedencia', el mismo que las D.O. de vino (/vinos/do/{id}). Cataluña (10)
-- y España (24) tienen vinos y cervezas: un texto de cerveza salía también en
-- la página de vino. Ahora:
--   ambito 'procedencia' -> /vinos/do/{id}        (id = procedencia.id)
--   ambito 'origen'      -> /cervezas/origen/{id} (id = procedencia.id)

ALTER TABLE categoria_textos
    MODIFY ambito ENUM('sector','tipo','procedencia','origen') NOT NULL;

ALTER TABLE ww_articulo_relaciones
    MODIFY ambito ENUM('sector','tipo','procedencia','origen') NOT NULL;
