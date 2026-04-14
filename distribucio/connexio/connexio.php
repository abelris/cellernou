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
    // Configuración de PRODUCCIÓN - cellernou.com
    $db_host = 'localhost';
    $db_user = 'mycellerno7d';
    $db_pass = 'Cellernou2025%';
    $db_name = 'cellernodb';
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

// Charset UTF-8 (utf8mb4 para soporte completo de Unicode)
mysqli_set_charset($bd, 'utf8mb4');

// Permitir fechas '0000-00-00' en queries
mysqli_query($bd, "SET SESSION sql_mode=''");
?>
