<?php
/**
 * El Celler Nou - Política de Privacidad
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

$pageTitle = 'Política de Privacidad | El Celler Nou';
$pageDescription = 'Política de privacidad y protección de datos de El Celler Nou.';

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => getBaseUrl()],
    ['name' => 'Política de Privacidad', 'url' => getBaseUrl() . '/politica-privacidad']
];

$schemaExtra = schemaBreadcrumb($breadcrumbItems);

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Política de Privacidad</h1>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width: 800px;">
        <h2>Responsable del tratamiento</h2>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Identidad:</strong> El Celler Nou</li>
            <li><strong>Dirección:</strong> <?php echo SITE_ADDRESS; ?></li>
            <li><strong>Correo electrónico:</strong> <?php echo SITE_EMAIL; ?></li>
        </ul>

        <h2>Finalidad del tratamiento de datos</h2>
        <p>En El Celler Nou tratamos la información que nos facilitas con el fin de:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>Gestionar las consultas realizadas a través del formulario de contacto</li>
            <li>Enviar información comercial si has dado tu consentimiento</li>
            <li>Mejorar nuestros servicios y la experiencia de usuario</li>
        </ul>

        <h2>Legitimación</h2>
        <p>La base legal para el tratamiento de tus datos es:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>El consentimiento del interesado</li>
            <li>El interés legítimo del responsable</li>
        </ul>

        <h2>Conservación de los datos</h2>
        <p>Los datos personales proporcionados se conservarán mientras se mantenga la relación o no solicites su supresión.</p>

        <h2>Destinatarios</h2>
        <p>Los datos no serán cedidos a terceros salvo en los casos en que exista una obligación legal.</p>

        <h2>Derechos del interesado</h2>
        <p>Puedes ejercer tus derechos de acceso, rectificación, supresión, oposición, limitación del tratamiento y portabilidad de los datos enviando un correo a <?php echo SITE_EMAIL; ?>.</p>

        <p style="margin-top: var(--spacing-2xl); color: var(--color-text-light);">Última actualización: <?php echo date('d/m/Y'); ?></p>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
