<?php
/**
 * El Celler Nou - Legal Controller
 */

$page = '';
if (Router::is('legal.aviso')) {
    $page = 'aviso';
    $pageTitle = 'Aviso Legal | El Celler Nou';
    $pageDescription = 'Aviso legal y términos de uso del sitio web El Celler Nou.';
    $breadcrumbItems = [
        ['name' => 'Inicio', 'url' => Router::url('home')],
        ['name' => 'Aviso Legal', 'url' => Router::url('legal.aviso')]
    ];
} elseif (Router::is('legal.privacidad')) {
    $page = 'privacidad';
    $pageTitle = 'Política de Privacidad | El Celler Nou';
    $pageDescription = 'Política de privacidad y protección de datos de El Celler Nou.';
    $breadcrumbItems = [
        ['name' => 'Inicio', 'url' => Router::url('home')],
        ['name' => 'Política de Privacidad', 'url' => Router::url('legal.privacidad')]
    ];
} else {
    $page = 'cookies';
    $pageTitle = 'Política de Cookies | El Celler Nou';
    $pageDescription = 'Política de cookies del sitio web El Celler Nou.';
    $breadcrumbItems = [
        ['name' => 'Inicio', 'url' => Router::url('home')],
        ['name' => 'Política de Cookies', 'url' => Router::url('legal.cookies')]
    ];
}

$schemaExtra = schemaBreadcrumb($breadcrumbItems);

include ROOT_PATH . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1><?php echo $page === 'aviso' ? 'Aviso Legal' : ($page === 'privacidad' ? 'Política de Privacidad' : 'Política de Cookies'); ?></h1>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width: 800px;">

<?php if ($page === 'aviso'): ?>
        <h2>Datos identificativos</h2>
        <p>En cumplimiento con el deber de información recogido en artículo 10 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y del Comercio Electrónico:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Denominación social:</strong> El Celler Nou</li>
            <li><strong>Domicilio:</strong> <?php echo SITE_ADDRESS; ?></li>
            <li><strong>Correo electrónico:</strong> <?php echo SITE_EMAIL; ?></li>
        </ul>

        <h2>Usuarios</h2>
        <p>El acceso y/o uso de este portal atribuye la condición de USUARIO, que acepta, desde dicho acceso y/o uso, las Condiciones Generales de Uso aquí reflejadas.</p>

        <h2>Uso del portal</h2>
        <p>El usuario se compromete a utilizar el portal, los contenidos y servicios de conformidad con la Ley, el presente Aviso Legal, las buenas costumbres y el orden público.</p>

        <h2>Propiedad intelectual e industrial</h2>
        <p>Todos los contenidos del sitio web, incluyendo textos, fotografías, gráficos, imágenes, iconos, tecnología, software, así como su diseño gráfico y códigos fuente, constituyen una obra cuya propiedad pertenece a El Celler Nou.</p>

        <h2>Legislación aplicable y jurisdicción</h2>
        <p>Para la resolución de todas las controversias o cuestiones relacionadas con el presente sitio web, será de aplicación la legislación española.</p>

<?php elseif ($page === 'privacidad'): ?>
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
        <p>La base legal para el tratamiento de tus datos es el consentimiento del interesado y el interés legítimo del responsable.</p>

        <h2>Conservación de los datos</h2>
        <p>Los datos personales proporcionados se conservarán mientras se mantenga la relación o no solicites su supresión.</p>

        <h2>Derechos del interesado</h2>
        <p>Puedes ejercer tus derechos de acceso, rectificación, supresión, oposición, limitación del tratamiento y portabilidad de los datos enviando un correo a <?php echo SITE_EMAIL; ?>.</p>

<?php else: ?>
        <h2>¿Qué son las cookies?</h2>
        <p>Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo cuando visitas un sitio web. Se utilizan ampliamente para hacer que los sitios web funcionen de manera más eficiente.</p>

        <h2>¿Qué tipos de cookies utilizamos?</h2>
        <h3>Cookies técnicas</h3>
        <p>Son necesarias para el funcionamiento del sitio web y no pueden ser desactivadas.</p>

        <h3>Cookies de análisis</h3>
        <p>Nos permiten contar las visitas y fuentes de tráfico para poder medir y mejorar el rendimiento de nuestro sitio.</p>

        <h2>¿Cómo gestionar las cookies?</h2>
        <p>Puedes configurar tu navegador para que rechace las cookies o te avise cuando se envíen.</p>

        <h3>Gestión de cookies en navegadores:</h3>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a></li>
            <li><a href="https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web-rastrear-preferencias" target="_blank" rel="noopener">Mozilla Firefox</a></li>
            <li><a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank" rel="noopener">Safari</a></li>
            <li><a href="https://support.microsoft.com/es-es/microsoft-edge/eliminar-cookies-en-microsoft-edge" target="_blank" rel="noopener">Microsoft Edge</a></li>
        </ul>
<?php endif; ?>

        <p style="margin-top: var(--spacing-2xl); color: var(--color-text-light);">Última actualización: <?php echo date('d/m/Y'); ?></p>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
