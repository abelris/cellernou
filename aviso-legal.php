<?php
/**
 * El Celler Nou - Aviso Legal
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

$pageTitle = 'Aviso Legal | El Celler Nou';
$pageDescription = 'Aviso legal y términos de uso del sitio web El Celler Nou.';

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => getBaseUrl()],
    ['name' => 'Aviso Legal', 'url' => getBaseUrl() . '/aviso-legal']
];

$schemaExtra = schemaBreadcrumb($breadcrumbItems);

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Aviso Legal</h1>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width: 800px;">
        <h2>Datos identificativos</h2>
        <p>En cumplimiento con el deber de información recogido en artículo 10 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y del Comercio Electrónico, a continuación se reflejan los siguientes datos:</p>

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

        <h2>Exclusión de garantías y responsabilidad</h2>
        <p>El Celler Nou no se hace responsable, en ningún caso, de los daños y perjuicios de cualquier naturaleza que pudieran ocasionar, a título enunciativo: errores u omisiones en los contenidos, falta de disponibilidad del portal o la transmisión de virus o programas maliciosos o lesivos en los contenidos.</p>

        <h2>Legislación aplicable y jurisdicción</h2>
        <p>Para la resolución de todas las controversias o cuestiones relacionadas con el presente sitio web, será de aplicación la legislación española.</p>

        <p style="margin-top: var(--spacing-2xl); color: var(--color-text-light);">Última actualización: <?php echo date('d/m/Y'); ?></p>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
