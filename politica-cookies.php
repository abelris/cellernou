<?php
/**
 * El Celler Nou - Política de Cookies
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

$pageTitle = 'Política de Cookies | El Celler Nou';
$pageDescription = 'Política de cookies del sitio web El Celler Nou.';

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => getBaseUrl()],
    ['name' => 'Política de Cookies', 'url' => getBaseUrl() . '/politica-cookies']
];

$schemaExtra = schemaBreadcrumb($breadcrumbItems);

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Política de Cookies</h1>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width: 800px;">
        <h2>¿Qué son las cookies?</h2>
        <p>Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo cuando visitas un sitio web. Se utilizan ampliamente para hacer que los sitios web funcionen de manera más eficiente y proporcionar información a los propietarios del sitio.</p>

        <h2>¿Qué tipos de cookies utilizamos?</h2>

        <h3>Cookies técnicas</h3>
        <p>Son necesarias para el funcionamiento del sitio web y no pueden ser desactivadas. Generalmente solo se configuran en respuesta a acciones realizadas por ti, como establecer tus preferencias de privacidad.</p>

        <h3>Cookies de análisis</h3>
        <p>Nos permiten contar las visitas y fuentes de tráfico para poder medir y mejorar el rendimiento de nuestro sitio. Nos ayudan a saber qué páginas son las más y menos populares.</p>

        <h2>¿Cómo gestionar las cookies?</h2>
        <p>Puedes configurar tu navegador para que rechace las cookies o te avise cuando se envíen. Sin embargo, algunas partes del sitio pueden no funcionar correctamente si decides rechazar las cookies.</p>

        <h3>Gestión de cookies en navegadores:</h3>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a></li>
            <li><a href="https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web-rastrear-preferencias" target="_blank" rel="noopener">Mozilla Firefox</a></li>
            <li><a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Safari</a></li>
            <li><a href="https://support.microsoft.com/es-es/help/17442/windows-internet-explorer-delete-manage-cookies" target="_blank" rel="noopener">Internet Explorer</a></li>
            <li><a href="https://support.microsoft.com/es-es/microsoft-edge/eliminar-cookies-en-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener">Microsoft Edge</a></li>
        </ul>

        <h2>Actualización de la política</h2>
        <p>Esta política de cookies puede ser actualizada periódicamente. Te recomendamos revisarla regularmente para estar informado sobre cómo utilizamos las cookies.</p>

        <p style="margin-top: var(--spacing-2xl); color: var(--color-text-light);">Última actualización: <?php echo date('d/m/Y'); ?></p>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
