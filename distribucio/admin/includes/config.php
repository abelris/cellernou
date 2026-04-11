<?php
/**
 * El Celler Nou - Configuración Central
 * Este archivo define las rutas base para que funcionen en cualquier dominio/subdominio
 */

// Detectar si estamos en producción
$isProduction = (
    isset($_SERVER['HTTP_HOST']) &&
    (strpos($_SERVER['HTTP_HOST'], 'cellernou.com') !== false)
);

if ($isProduction) {
    // PRODUCCIÓN: cellernou.com - todo en la raíz del dominio
    $basePath = '/distribucio';
} else {
    // LOCAL: Detectar automáticamente la ruta
    $scriptPath = str_replace('\\', '/', dirname(dirname(__DIR__)));
    $documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $basePath = str_replace($documentRoot, '', $scriptPath);

    // Limpiar path
    $basePath = '/' . trim($basePath, '/');
    if ($basePath === '/') {
        $basePath = '/distribucio';
    }
}

// Definir constantes de rutas
define('BASE_PATH', $basePath);
define('ADMIN_BASE_PATH', $basePath . '/admin');

/**
 * Obtener URL base del proyecto (distribucio)
 */
function getBaseUrl(string $path = ''): string {
    return BASE_PATH . '/' . ltrim($path, '/');
}

/**
 * Obtener URL del admin
 */
function getAdminUrl(string $path = ''): string {
    return ADMIN_BASE_PATH . '/' . ltrim($path, '/');
}
