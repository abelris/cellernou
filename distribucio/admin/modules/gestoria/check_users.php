<?php
require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(dirname(__DIR__))) . '/connexio/connexio.php';
mysqli_query($bd, "SET SESSION sql_mode=''");
$r = mysqli_query($bd, 'DESCRIBE usuarios');
while ($row = mysqli_fetch_assoc($r)) { echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Default'] . "\n"; }
echo "---\n";
$r2 = mysqli_query($bd, 'SELECT id, nombre, usuario FROM usuarios');
while ($row = mysqli_fetch_assoc($r2)) { echo $row['id'] . ' | ' . $row['nombre'] . ' | ' . $row['usuario'] . "\n"; }
