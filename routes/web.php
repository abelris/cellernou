<?php
/**
 * El Celler Nou - Definición de Rutas
 *
 * Todas las rutas de la aplicación se definen aquí.
 * Patrón: Router::get('patrón', 'controlador', 'nombre');
 *
 * Parámetros dinámicos:
 * - {param}  : Parámetro obligatorio
 * - {param?} : Parámetro opcional
 */

// =============================================
// PÁGINAS PRINCIPALES
// =============================================

Router::get('/', 'HomeController', 'home');
Router::get('/contacto', 'ContactoController', 'contacto');
Router::post('/contacto', 'ContactoController', 'contacto.submit');
Router::get('/buscar', 'BuscarController', 'buscar');

// =============================================
// VINOS
// =============================================

Router::get('/vinos', 'VinosController', 'vinos');
Router::get('/vinos/tipo/{id}/{slug}', 'VinosController', 'vinos.tipo');
Router::get('/vinos/do/{id}/{slug}', 'VinosController', 'vinos.do');

// =============================================
// CAVAS Y ESPUMOSOS
// =============================================

Router::get('/cavas', 'CavasController', 'cavas');
// Alias histórico con nombre propio: si reutiliza el nombre 'cavas' sobreescribe
// la ruta real en namedRoutes y Router::url('cavas') pasa a devolver el alias.
// El controlador lo redirige 301 a /cavas.
Router::get('/cavas-espumosos', 'CavasController', 'cavas.alias');
Router::get('/cavas/tipo/{id}/{slug}', 'CavasController', 'cavas.tipo');

// =============================================
// LICORES
// =============================================

Router::get('/licores', 'LicoresController', 'licores');
Router::get('/licores/tipo/{id}/{slug}', 'LicoresController', 'licores.tipo');

// =============================================
// CERVEZAS
// =============================================

Router::get('/cervezas', 'CervezasController', 'cervezas');
Router::get('/cervezas/origen/{id}/{slug}', 'CervezasController', 'cervezas.origen');

// =============================================
// CONSERVAS
// =============================================

Router::get('/conservas', 'ConservasController', 'conservas');
Router::get('/conservas/tipo/{id}/{slug}', 'ConservasController', 'conservas.tipo');

// =============================================
// WINEWORLD (Artículos)
// =============================================

Router::get('/wineworld', 'WineWorldController', 'wineworld');
Router::get('/wineworld/{cat_slug}', 'WineWorldController', 'wineworld.categoria');
Router::get('/wineworld/{cat_slug}/{art_slug}', 'WineWorldController', 'wineworld.articulo');

// =============================================
// VINO A DOMICILIO (posicionamiento local)
// =============================================

Router::get('/vino-a-domicilio', 'DomicilioController', 'domicilio');
Router::get('/vino-a-domicilio/{slug}', 'DomicilioController', 'domicilio.poblacion');
Router::get('/servicios', 'ServiciosController', 'servicios');

// =============================================
// PRODUCTOS
// =============================================

Router::get('/producto/k/{tipo}/{id}/{slug}', 'KomkalProductoController', 'producto.komkal');
Router::get('/producto/{id}/{slug}', 'ProductoController', 'producto');
Router::get('/producto/{id}', 'ProductoController', 'producto.simple');

// =============================================
// PÁGINAS LEGALES
// =============================================

Router::get('/aviso-legal', 'LegalController', 'legal.aviso');
Router::get('/politica-privacidad', 'LegalController', 'legal.privacidad');
Router::get('/politica-cookies', 'LegalController', 'legal.cookies');
Router::get('/politica-devoluciones', 'LegalController', 'legal.devoluciones');
Router::get('/condiciones-de-venta', 'LegalController', 'legal.venta');
Router::get('/politica-de-envios', 'LegalController', 'legal.envios');

// =============================================
// SITEMAP Y SEO
// =============================================

Router::get('/sitemap.xml', 'SitemapController', 'sitemap');
