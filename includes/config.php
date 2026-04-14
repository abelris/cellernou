<?php
/**
 * Configuración general del sitio Celler Nou
 */

// Configuración del sitio
define('SITE_NAME', 'El Celler Nou');
//define('SITE_URL', 'https://www.cellernou.com');
define('SITE_URL', 'https://cellernou.com');
define('SITE_EMAIL', 'cellernou@cellernou.com');
define('SITE_PHONE', '+34 937 520 271');
define('SITE_PHONE_MOBILE', '+34 637 626 302');
define('SITE_ADDRESS', 'C/ Ramón Llull, Premia de Mar, 08339');
define('SITE_DESCRIPTION', 'El Celler Nou - Tu tienda especializada en vinos, cavas, licores, cervezas y conservas de calidad. Amplia selección de D.O. españolas y productos internacionales.');

// Sectores permitidos (IDs de la base de datos)
define('SECTORES_PERMITIDOS', [
    8,  // Vinos
    10, // Vinos granel
    2,  // Licores
    21, // Cervezas
    12, // Conservas Marisco
    14  // Cava/Espumosos
]);

// Mapeo de sectores a slugs URL
define('SECTOR_SLUGS', [
    8 => 'vinos',
    10 => 'vinos-granel',
    2 => 'licores',
    21 => 'cervezas',
    12 => 'conservas',
    14 => 'cavas-espumosos'
]);

// Mapeo de tipos de producto a sectores para el menú
define('TIPOS_VINOS', [1, 2, 3, 102, 103]); // Tinto, Blanco, Rosado, Dulce, Malvasía
define('TIPOS_LICORES', [5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 55, 56, 58, 59, 60, 61, 72, 74, 75, 76, 77, 78, 79, 105, 118, 123, 124, 131, 134, 135, 137, 138, 139, 140, 148, 155]);
define('TIPOS_CAVAS', [22, 135]); // Cava, Champagne
define('TIPOS_CERVEZAS', [53]);
define('TIPOS_CONSERVAS', [71, 73, 101, 119, 120, 145, 146, 147, 150, 151]);

// Conexión a base de datos
require_once __DIR__ . '/../distribucio/connexio/connexio.php';

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Detectar entorno para errores
$isProduction = (
    isset($_SERVER['HTTP_HOST']) &&
    (strpos($_SERVER['HTTP_HOST'], 'cellernou.com') !== false)
);

if ($isProduction) {
    // Producción: ocultar errores
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    // Local: mostrar errores
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Funciones de utilidad
function sanitize($string) {
    global $bd;
    return mysqli_real_escape_string($bd, trim($string));
}

function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[áàäâ]/u', 'a', $string);
    $string = preg_replace('/[éèëê]/u', 'e', $string);
    $string = preg_replace('/[íìïî]/u', 'i', $string);
    $string = preg_replace('/[óòöô]/u', 'o', $string);
    $string = preg_replace('/[úùüû]/u', 'u', $string);
    $string = preg_replace('/ñ/u', 'n', $string);
    $string = preg_replace('/ç/u', 'c', $string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

function getBaseUrl() {
    return '';
}
