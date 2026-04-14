-- ============================================================
-- Interlinking: Añadir enlace al artículo del Día del Padre
-- desde el artículo pillar (id=1)
-- Fecha: 2026-03-17
-- ============================================================

SET SESSION sql_mode = '';

-- Insertar enlace al nuevo artículo en la sección "Día del Padre" del pillar
UPDATE ww_articulos
SET contenido = REPLACE(
    contenido,
    '<li><strong>Un brandy español</strong> si es tradicional: <a href="/producto/5886/cardenal-mendoza-carta-real">Cardenal Mendoza Carta Real</a>.</li>\n</ul>\n\n<h3>Día de la Madre</h3>',
    '<li><strong>Un brandy español</strong> si es tradicional: <a href="/producto/5886/cardenal-mendoza-carta-real">Cardenal Mendoza Carta Real</a>.</li>\n</ul>\n\n<div style="background: #f0fdf4; border-left: 4px solid #4a6741; padding: 1.25rem; border-radius: 0 8px 8px 0; margin: 1.5rem 0;">\n<strong>Artículo relacionado:</strong> Descubre nuestra guía completa <a href="/wineworld/cultura-y-tradicion/dia-del-padre-vino-regalar-tradicion" title="Día del Padre y Vino: tradición, cultura y los mejores vinos para regalar a papá"><strong>Día del Padre y Vino: La Tradición de Regalar con Alma</strong></a>, donde profundizamos en la historia de esta celebración, los mejores tintos por D.O., la diferencia entre Crianza, Reserva y Gran Reserva para regalo, y recomendaciones por presupuesto.\n</div>\n\n<h3>Día de la Madre</h3>'
),
updated_at = NOW()
WHERE id = 1;

-- Verificar que el cambio se aplicó
SELECT
    CASE
        WHEN contenido LIKE '%dia-del-padre-vino-regalar-tradicion%' THEN 'OK - Enlace insertado correctamente'
        ELSE 'ERROR - El enlace NO se insertó'
    END as resultado
FROM ww_articulos
WHERE id = 1;
