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
define('SITE_DESCRIPTION', 'El Celler Nou - Tu tienda especializada en vinos, cavas, licores, cervezas y conservas de calidad. Amplia selección de D.O. españolas y productos internacionales.');

// -----------------------------------------------------------------
// Datos fiscales y de local (NAP) - usados en schema.org y páginas legales.
// Deben coincidir EXACTAMENTE con Google Business Profile y el resto de
// directorios: un NAP inconsistente perjudica el posicionamiento local.
// -----------------------------------------------------------------
define('COMPANY_LEGAL_NAME', 'ALMABRIS GROUP 2024 S.L.');
define('COMPANY_VAT_ID', 'B66318684');
define('SITE_STREET', 'C/ Ramón Llull, 15');
define('SITE_LOCALITY', 'Premià de Mar');
define('SITE_REGION', 'Barcelona');
define('SITE_POSTAL_CODE', '08330');
define('SITE_COUNTRY', 'ES');
define('SITE_ADDRESS', SITE_STREET . ', ' . SITE_POSTAL_CODE . ' ' . SITE_LOCALITY . ' (' . SITE_REGION . ')');

// Coordenadas del local (Carrer de Ramon Llull, 15).
// Verificadas: la geocodificación inversa de Nominatim/OpenStreetMap sobre este
// punto devuelve "Carrer de Ramon Llull, Premià de Mar, 08330", y cae dentro
// del bounding box de la calle.
define('SITE_GEO_LAT', 41.493308);
define('SITE_GEO_LNG', 2.360674);

// Perfiles oficiales (sameAs). Añadir las URLs reales cuando existan.
define('SITE_SAME_AS', []);

// Horario comercial. Se publica en openingHoursSpecification Y se muestra en
// /contacto, para que lo marcado coincida con lo visible.
// Jornada partida; el viernes cierra media hora antes a mediodía.
// Formato: [dias => [...], opens => 'HH:MM', closes => 'HH:MM']
define('SITE_OPENING_HOURS', [
    ['dias' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday'], 'opens' => '09:30', 'closes' => '14:00'],
    ['dias' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday'], 'opens' => '17:00', 'closes' => '20:30'],
    ['dias' => ['Friday'],   'opens' => '09:30', 'closes' => '13:30'],
    ['dias' => ['Friday'],   'opens' => '17:00', 'closes' => '20:30'],
    ['dias' => ['Saturday'], 'opens' => '09:30', 'closes' => '14:00'],
    ['dias' => ['Sunday'],   'opens' => '10:00', 'closes' => '14:00'],
]);

// Etiquetas en castellano para pintar el horario en /contacto.
define('DIAS_SEMANA_ES', [
    'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
]);

// Productos por página en los listados de categoría.
define('PRODUCTOS_POR_PAGINA', 36);

// -----------------------------------------------------------------
// Política de devoluciones y envíos - se publica en el schema Offer.
// Los valores salen de /politica-devoluciones y /politica-de-envios:
// si cambian esas páginas, hay que cambiarlos aquí también.
// -----------------------------------------------------------------
define('RETURN_POLICY_DAYS', 14);              // Desistimiento: 14 días naturales
define('RETURN_POLICY_FEES_CUSTOMER', true);   // Gastos de devolución a cargo del cliente

// Envío local gratuito. El resto de zonas figura como "Consultar" en la web,
// así que NO se declara tarifa para ellas: un shippingRate inventado es peor
// que la ausencia de la propiedad.
define('SHIPPING_FREE_POSTAL_CODE', '08330');  // Premià de Mar
define('SHIPPING_FREE_MIN_ORDER', 30);         // Gratuito en pedidos > 30 EUR
define('SHIPPING_HANDLING_DAYS', [0, 1]);      // Preparación: mismo día o siguiente
define('SHIPPING_TRANSIT_DAYS', [1, 3]);       // Tránsito local/metropolitano

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
    // En producción devolvemos SITE_URL absoluto para que canonical, og:url y og:image
    // sean URLs absolutas (Open Graph lo exige). En local devolvemos cadena vacía para
    // que CSS/JS/favicon sigan cargando desde el host actual (Laragon, localhost, etc.).
    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'cellernou.com') !== false) {
        return SITE_URL;
    }
    return '';
}
