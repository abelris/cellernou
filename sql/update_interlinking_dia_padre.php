<?php
/**
 * Interlinking: Añadir enlace al artículo del Día del Padre
 * desde el artículo pillar (id=1)
 */
require __DIR__ . '/../distribucio/connexio/connexio.php';

$r = mysqli_query($bd, "SELECT contenido FROM ww_articulos WHERE id = 1");
$row = mysqli_fetch_assoc($r);
$content = $row['contenido'];

$search = "Cardenal Mendoza Carta Real</a>.</li>\r\n</ul>\r\n\r\n<h3>Día de la Madre</h3>";

$callout = <<<'HTML'
<div style="background: #f0fdf4; border-left: 4px solid #4a6741; padding: 1.25rem; border-radius: 0 8px 8px 0; margin: 1.5rem 0;">
<strong>Artículo relacionado:</strong> Descubre nuestra guía completa <a href="/wineworld/cultura-y-tradicion/dia-del-padre-vino-regalar-tradicion" title="Día del Padre y Vino: tradición, cultura y los mejores vinos para regalar a papá"><strong>Día del Padre y Vino: La Tradición de Regalar con Alma</strong></a>, donde profundizamos en la historia de esta celebración, los mejores tintos por D.O., la diferencia entre Crianza, Reserva y Gran Reserva para regalo, y recomendaciones por presupuesto.
</div>
HTML;

$replace = "Cardenal Mendoza Carta Real</a>.</li>\r\n</ul>\r\n\r\n" . $callout . "\r\n\r\n<h3>Día de la Madre</h3>";

if (strpos($content, $search) === false) {
    echo "ERROR: No se encontró el texto a reemplazar.\n";
    exit(1);
}

$newContent = str_replace($search, $replace, $content);
$safe = mysqli_real_escape_string($bd, $newContent);
$ok = mysqli_query($bd, "UPDATE ww_articulos SET contenido = '$safe', updated_at = NOW() WHERE id = 1");

if ($ok) {
    echo "OK - Enlace insertado correctamente en artículo id=1\n";
    // Verify
    $r2 = mysqli_query($bd, "SELECT CASE WHEN contenido LIKE '%dia-del-padre-vino-regalar-tradicion%' THEN 'Verificado' ELSE 'No encontrado' END as check_result FROM ww_articulos WHERE id = 1");
    $row2 = mysqli_fetch_assoc($r2);
    echo "Verificación: " . $row2['check_result'] . "\n";
} else {
    echo "ERROR: " . mysqli_error($bd) . "\n";
}
