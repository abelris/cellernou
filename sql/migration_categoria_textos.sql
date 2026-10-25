-- Textos de categoría y enlazado blog <-> categorías (planner cellernou-plan-seo-contenidos, tareas 07, 08 y 49).
--
-- categoria_textos: introducción, texto largo y FAQ de cada listado (sector,
-- tipo o procedencia). tipo_producto.descripcion y procedencia.descripcion
-- solo admiten 200 caracteres.
--   ambito 'sector'      -> /vinos, /cavas, /licores, /cervezas, /conservas (id = sectores.id)
--   ambito 'tipo'        -> /{sector}/tipo/{id}  (id = tipo_producto.id)
--   ambito 'procedencia' -> /vinos/do/{id} y /cervezas/origen/{id} (id = procedencia.id)
--
-- ww_articulo_relaciones: categorías que trata cada artículo. La marcada como
-- principal es la que vende (bloque «Dónde comprarlo»); todas sacan el
-- artículo en el bloque «Del blog» de su listado.

CREATE TABLE IF NOT EXISTS categoria_textos (
    id INT NOT NULL AUTO_INCREMENT,
    ambito ENUM('sector','tipo','procedencia') NOT NULL,
    id_ref INT NOT NULL,
    titulo_seo VARCHAR(90) NULL,
    intro TEXT NULL,
    cuerpo MEDIUMTEXT NULL,
    faqs MEDIUMTEXT NULL,
    publicado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY idx_ambito_ref (ambito, id_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ww_articulo_relaciones (
    id_articulo INT NOT NULL,
    ambito ENUM('sector','tipo','procedencia') NOT NULL,
    id_ref INT NOT NULL,
    principal TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_articulo, ambito, id_ref),
    KEY idx_ambito_ref (ambito, id_ref),
    CONSTRAINT ww_articulo_relaciones_ibfk_1 FOREIGN KEY (id_articulo) REFERENCES ww_articulos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Plantilla de artículo: consejo de la tienda («Te lo aconsejamos») y FAQ (JSON).
ALTER TABLE ww_articulos
    ADD COLUMN IF NOT EXISTS consejo TEXT NULL AFTER contenido,
    ADD COLUMN IF NOT EXISTS faqs MEDIUMTEXT NULL AFTER consejo;
