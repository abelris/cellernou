<?php
/**
 * El Celler Nou - Front Controller
 *
 * Punto de entrada único de la aplicación.
 * Todas las peticiones pasan por aquí.
 */

// Definir constantes de ruta
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Cargar configuración
require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/seo.php';

// Cargar Router
require_once ROOT_PATH . '/routes/Router.php';

// Cargar definición de rutas
require_once ROOT_PATH . '/routes/web.php';

// Despachar la solicitud
Router::dispatch();
