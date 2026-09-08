-- ---------------------------------------------------------------
-- Fotos principales duplicadas en producto_fotos
--
-- 18 productos tenian dos filas con principal = 1. Como todas las consultas
-- del catalogo hacen
--     LEFT JOIN producto_fotos pf ON pf.idProducto = p.id AND pf.principal = 1
-- ese duplicado multiplicaba la fila del producto: esos 18 productos salian
-- DOS VECES en /vinos, /cavas y /cervezas, y dos veces en el sitemap.
--
-- No se borra nada: se deja como principal la fila de id mas bajo (la primera
-- que se inserto) y las demas pasan a principal = 0, con lo que siguen
-- disponibles como fotos secundarias.
--
-- Ejecutar una sola vez en cada entorno.
-- ---------------------------------------------------------------

-- Comprobacion previa: cuantos productos estan afectados
SELECT COUNT(*) AS productos_afectados FROM (
    SELECT idProducto
    FROM producto_fotos
    WHERE principal = 1
    GROUP BY idProducto
    HAVING COUNT(*) > 1
) t;

-- Correccion
UPDATE producto_fotos pf
JOIN (
    SELECT idProducto, MIN(id) AS id_bueno
    FROM producto_fotos
    WHERE principal = 1
    GROUP BY idProducto
    HAVING COUNT(*) > 1
) d ON d.idProducto = pf.idProducto
SET pf.principal = 0
WHERE pf.principal = 1
  AND pf.id <> d.id_bueno;

-- Comprobacion posterior: debe devolver 0
SELECT COUNT(*) AS quedan_duplicados FROM (
    SELECT idProducto
    FROM producto_fotos
    WHERE principal = 1
    GROUP BY idProducto
    HAVING COUNT(*) > 1
) t;
