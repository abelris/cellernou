<?php
// Simular la misma cadena de includes que usa el admin
require_once 'C:/laragon/www/cellernou/distribucio/connexio/connexio.php';

echo "Connection charset: " . mysqli_character_set_name($bd) . "\n";

$r = mysqli_query($bd, "SELECT id, nombre FROM odr_clientes WHERE id = 8");
$row = mysqli_fetch_assoc($r);
echo "Raw nombre: [" . $row['nombre'] . "]\n";
echo "strlen: " . strlen($row['nombre']) . "\n";
echo "bin2hex: " . bin2hex($row['nombre']) . "\n";
echo "htmlspecialchars: [" . htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') . "]\n";
echo "htmlspecialchars strict: [" . htmlspecialchars($row['nombre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "]\n";
