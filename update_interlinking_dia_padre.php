<?php
/**
 * Interlinking: Añadir enlace al artículo del Día del Padre
 * desde el artículo pillar (id=1)
 *
 * Ejecutar una vez y borrar.
 */
require __DIR__ . '/distribucio/connexio/connexio.php';

$r = mysqli_query($bd, "SELECT contenido FROM ww_articulos WHERE id = 1");
if (!$r) { die("Error query: " . mysqli_error($bd)); }
$row = mysqli_fetch_assoc($r);
if (!$row) { die("Artículo id=1 no encontrado."); }
$content = $row['contenido'];

// Buscar el punto de inserción (independiente de \r\n o \n)
$needle = 'Cardenal Mendoza Carta Real</a>.</li>';
$pos = strpos($content, $needle);
if ($pos === false) {
    die("ERROR: No se encontró el texto ancla en el artículo.");
}

// Ya tiene el enlace?
if (strpos($content, 'dia-del-padre-vino-regalar-tradicion') !== false) {
    die("El enlace ya existe en el artículo. No hace falta actualizar.");
}

// Buscar el siguiente <h3>Día de la Madre</h3> después del ancla
$afterAnchor = strpos($content, '<h3>', $pos);
if ($afterAnchor === false) {
    die("ERROR: No se encontró <h3> después del ancla.");
}

$callout = '<div style="background: #f0fdf4; border-left: 4px solid #4a6741; padding: 1.25rem; border-radius: 0 8px 8px 0; margin: 1.5rem 0;">' . "\n"
    . '<strong>Artículo relacionado:</strong> Descubre nuestra guía completa '
    . '<a href="/wineworld/cultura-y-tradicion/dia-del-padre-vino-regalar-tradicion" title="Día del Padre y Vino: tradición, cultura y los mejores vinos para regalar a papá">'
    . '<strong>Día del Padre y Vino: La Tradición de Regalar con Alma</strong></a>, '
    . 'donde profundizamos en la historia de esta celebración, los mejores tintos por D.O., '
    . 'la diferencia entre Crianza, Reserva y Gran Reserva para regalo, y recomendaciones por presupuesto.' . "\n"
    . '</div>' . "\n\n";

// Insertar el callout justo antes del <h3>
$newContent = substr($content, 0, $afterAnchor) . $callout . substr($content, $afterAnchor);

$safe = mysqli_real_escape_string($bd, $newContent);
$ok = mysqli_query($bd, "UPDATE ww_articulos SET contenido = '$safe', updated_at = NOW() WHERE id = 1");

if ($ok) {
    echo "OK - Enlace insertado correctamente en artículo id=1\n";
} else {
    echo "ERROR: " . mysqli_error($bd) . "\n";
}
