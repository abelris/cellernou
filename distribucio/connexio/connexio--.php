<?php
/**
 * El Celler Nou - Database Connection
 * Detecta automáticamente si estamos en local o producción
 */

// Detectar entorno
$isProduction = (
    isset($_SERVER['HTTP_HOST']) &&
    (strpos($_SERVER['HTTP_HOST'], 'cellernou.com') !== false)
);

if ($isProduction) {
    // Configuración de PRODUCCIÓN
    $db_host = 'localhost';
    $db_user = 'cellerno_db';        // Ajustar según tu hosting
    $db_pass = 'C334%e5lle&6rn(oU';  // Ajustar según tu hosting
    $db_name = 'cellerno_db';        // Ajustar según tu hosting
} else {
    // Configuración LOCAL (Laragon)
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'cellernodb';
}

// Conexión
$bd = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$bd) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

// Charset UTF-8
mysqli_set_charset($bd, 'utf8');
?>
