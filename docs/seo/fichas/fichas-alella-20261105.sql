SET NAMES utf8mb4;
UPDATE producto_textos SET contenido = REPLACE(contenido, 'elaborado por Alta Alella', 'elaborado por Bouquet d''Alella')
 WHERE idProducto IN (302, 1157) AND tipo = 'descripcion' AND contenido LIKE '%elaborado por Alta Alella%';
SELECT ROW_COUNT() AS bouquet_corregidas;
UPDATE producto_textos SET contenido = REPLACE(contenido, 'Blanco clásico de la D.O. Alella elaborado por Alella Vinícola con pansa blanca', 'Blanco clásico de la D.O. Alella, heredero de la antigua marca Marqués de Alella, elaborado con pansa blanca')
 WHERE idProducto = 375 AND tipo = 'descripcion' AND contenido LIKE 'Blanco clásico de la D.O. Alella elaborado por Alella Vinícola%';
SELECT ROW_COUNT() AS raventos_corregida;
