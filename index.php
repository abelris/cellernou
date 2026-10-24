<?php
/**
 * El Celler Nou - Front Controller
 */

// Definir constantes de ruta
define('ROOT_PATH', __DIR__);

// Cargar configuración
require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/seo.php';
require_once ROOT_PATH . '/includes/faqs.php';
require_once ROOT_PATH . '/includes/poblaciones.php';

// Cargar Router
require_once ROOT_PATH . '/routes/Router.php';

// Cargar definición de rutas
require_once ROOT_PATH . '/routes/web.php';

// Despachar la solicitud
Router::dispatch();
