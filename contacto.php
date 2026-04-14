<?php
/**
 * El Celler Nou - Página de Contacto
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

// SEO
$pageTitle = 'Contacto | El Celler Nou - Contáctanos';
$pageDescription = 'Ponte en contacto con El Celler Nou. Estamos aquí para ayudarte con cualquier consulta sobre nuestros vinos, licores o productos.';

// Breadcrumb
$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => getBaseUrl()],
    ['name' => 'Contacto', 'url' => getBaseUrl() . '/contacto']
];

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';
$productoConsulta = isset($_GET['producto']) ? htmlspecialchars($_GET['producto']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? sanitize($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $telefono = isset($_POST['telefono']) ? sanitize($_POST['telefono']) : '';
    $asunto = isset($_POST['asunto']) ? sanitize($_POST['asunto']) : '';
    $mensajeForm = isset($_POST['mensaje']) ? sanitize($_POST['mensaje']) : '';

    // Validación básica
    if (empty($nombre) || empty($email) || empty($mensajeForm)) {
        $mensaje = 'Por favor, completa todos los campos obligatorios.';
        $tipoMensaje = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Por favor, introduce un email válido.';
        $tipoMensaje = 'error';
    } else {
        // Preparar email
        $to = SITE_EMAIL;
        $subject = "Contacto web: " . ($asunto ?: 'Consulta general');

        $body = "Nuevo mensaje de contacto desde la web:\n\n";
        $body .= "Nombre: $nombre\n";
        $body .= "Email: $email\n";
        $body .= "Teléfono: $telefono\n";
        $body .= "Asunto: $asunto\n\n";
        $body .= "Mensaje:\n$mensajeForm\n";

        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Enviar email
        if (mail($to, $subject, $body, $headers)) {
            $mensaje = '¡Gracias por tu mensaje! Te responderemos lo antes posible.';
            $tipoMensaje = 'success';
            // Limpiar campos
            $_POST = [];
        } else {
            $mensaje = 'Ha ocurrido un error al enviar el mensaje. Por favor, inténtalo de nuevo o contáctanos directamente por email.';
            $tipoMensaje = 'error';
        }
    }
}

// Schema
$schemaExtra = schemaBreadcrumb($breadcrumbItems);
$schemaExtra .= schemaContacto();

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Contacto</h1>
        <p>Estamos aquí para ayudarte. Contáctanos y te responderemos lo antes posible.</p>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info">
                <h2>Información de contacto</h2>
                <p>¿Tienes alguna pregunta sobre nuestros productos? ¿Necesitas asesoramiento para elegir el vino perfecto? No dudes en contactarnos.</p>

                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-item-icon">
                            <svg class="icon"><use href="#icon-email"></use></svg>
                        </div>
                        <div class="contact-item-content">
                            <h3>Email</h3>
                            <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon">
                            <svg class="icon"><use href="#icon-location"></use></svg>
                        </div>
                        <div class="contact-item-content">
                            <h3>Dirección</h3>
                            <p><?php echo SITE_ADDRESS; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div style="margin-top: var(--spacing-2xl); padding-top: var(--spacing-xl); border-top: 1px solid var(--color-border);">
                    <h3 style="font-size: 1rem; margin-bottom: var(--spacing-md);">Enlaces rápidos</h3>
                    <ul style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                        <li><a href="<?php echo getBaseUrl(); ?>/vinos" style="color: var(--color-primary);">Ver catálogo de vinos</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>/licores" style="color: var(--color-primary);">Ver catálogo de licores</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>/cavas" style="color: var(--color-primary);">Ver cavas y espumosos</a></li>
                    </ul>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <h2>Envíanos un mensaje</h2>

                <?php if ($mensaje): ?>
                <div class="form-message <?php echo $tipoMensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
                <?php endif; ?>

                <form action="<?php echo getBaseUrl(); ?>/contacto" method="POST" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre *</label>
                            <input type="text" id="nombre" name="nombre" required
                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono"
                                   value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="asunto">Asunto</label>
                            <select id="asunto" name="asunto">
                                <option value="">Selecciona un asunto</option>
                                <option value="Consulta sobre producto" <?php echo ($productoConsulta || (isset($_POST['asunto']) && $_POST['asunto'] == 'Consulta sobre producto')) ? 'selected' : ''; ?>>Consulta sobre producto</option>
                                <option value="Pedido" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'Pedido') ? 'selected' : ''; ?>>Realizar pedido</option>
                                <option value="Presupuesto" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'Presupuesto') ? 'selected' : ''; ?>>Solicitar presupuesto</option>
                                <option value="Otro" <?php echo (isset($_POST['asunto']) && $_POST['asunto'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mensaje">Mensaje *</label>
                        <textarea id="mensaje" name="mensaje" rows="6" required><?php
                            if ($productoConsulta) {
                                echo "Hola, me gustaría recibir más información sobre el producto: " . $productoConsulta . "\n\n";
                            } elseif (isset($_POST['mensaje'])) {
                                echo htmlspecialchars($_POST['mensaje']);
                            }
                        ?></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg">Enviar mensaje</button>
                    </div>

                    <p style="font-size: 0.875rem; color: var(--color-text-light);">
                        * Campos obligatorios. Al enviar este formulario aceptas nuestra
                        <a href="<?php echo getBaseUrl(); ?>/politica-privacidad" style="color: var(--color-primary);">política de privacidad</a>.
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
