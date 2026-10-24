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
} elseif (Router::is('legal.venta')) {
    $page = 'venta';
    $pageTitle = 'Condiciones de Venta | El Celler Nou';
    $pageDescription = 'Condiciones generales de venta de El Celler Nou. Información sobre precios, formas de pago, envíos y proceso de compra.';
    $breadcrumbItems = [
        ['name' => 'Inicio', 'url' => Router::url('home')],
        ['name' => 'Condiciones de Venta', 'url' => Router::url('legal.venta')]
    ];
} elseif (Router::is('legal.envios')) {
    $page = 'envios';
    $pageTitle = 'Política de Envíos | El Celler Nou';
    $pageDescription = 'Política de envíos y entrega de mercancía de El Celler Nou. Plazos, costes, zonas de cobertura y condiciones de entrega.';
    $breadcrumbItems = [
        ['name' => 'Inicio', 'url' => Router::url('home')],
        ['name' => 'Política de Envíos', 'url' => Router::url('legal.envios')]
    ];
} elseif (Router::is('legal.devoluciones')) {
    $page = 'devoluciones';
    $pageTitle = 'Política de Devoluciones | El Celler Nou';
    $pageDescription = 'Política de devoluciones y derecho de desistimiento de El Celler Nou. Condiciones para la devolución de vinos, licores y otros productos.';
    $breadcrumbItems = [
        ['name' => 'Inicio', 'url' => Router::url('home')],
        ['name' => 'Política de Devoluciones', 'url' => Router::url('legal.devoluciones')]
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
        <h1><?php
            $titols = [
                'aviso' => 'Aviso Legal',
                'privacidad' => 'Política de Privacidad',
                'venta' => 'Condiciones de Venta',
                'envios' => 'Política de Envíos',
                'devoluciones' => 'Política de Devoluciones',
                'cookies' => 'Política de Cookies',
            ];
            echo $titols[$page] ?? 'Información Legal';
        ?></h1>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width: 800px;">

<?php if ($page === 'aviso'): ?>
        <h2>Datos identificativos</h2>
        <p>En cumplimiento con el deber de información recogido en artículo 10 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y del Comercio Electrónico:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Denominación social:</strong> ALMABRIS GROUP 2024 S.L.</li>
            <li><strong>Nombre comercial:</strong> El Celler Nou</li>
            <li><strong>CIF:</strong> B66318684</li>
            <li><strong>Domicilio:</strong> C/ Ramón Llull, nº 15, 08330 Premià de Mar (Barcelona)</li>
            <li><strong>Teléfono:</strong> <?php echo SITE_PHONE; ?></li>
            <li><strong>Correo electrónico:</strong> <?php echo SITE_EMAIL; ?></li>
            <li><strong>Sitio web:</strong> <?php echo SITE_URL; ?></li>
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
            <li><strong>Identidad:</strong> ALMABRIS GROUP 2024 S.L.</li>
            <li><strong>Nombre comercial:</strong> El Celler Nou</li>
            <li><strong>CIF:</strong> B66318684</li>
            <li><strong>Dirección:</strong> C/ Ramón Llull, nº 15, 08330 Premià de Mar (Barcelona)</li>
            <li><strong>Teléfono:</strong> <?php echo SITE_PHONE; ?></li>
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

<?php elseif ($page === 'venta'): ?>
        <h2>Datos del vendedor</h2>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Denominación social:</strong> ALMABRIS GROUP 2024 S.L.</li>
            <li><strong>Nombre comercial:</strong> El Celler Nou</li>
            <li><strong>CIF:</strong> B66318684</li>
            <li><strong>Domicilio:</strong> C/ Ramón Llull, nº 15, 08330 Premià de Mar (Barcelona)</li>
            <li><strong>Teléfono:</strong> <?php echo SITE_PHONE; ?></li>
            <li><strong>Email:</strong> <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a></li>
        </ul>

        <h2>Ámbito de aplicación</h2>
        <p>Las presentes condiciones generales de venta regulan la relación comercial entre ALMABRIS GROUP 2024 S.L. (en adelante, El Celler Nou) y el usuario que realice una compra a través del sitio web <?php echo SITE_URL; ?> o en nuestra tienda física.</p>
        <p>La realización de un pedido implica la aceptación plena y sin reservas de estas condiciones generales de venta.</p>

        <h2>Productos</h2>
        <p>El Celler Nou ofrece una selección de vinos, cavas, espumosos, licores, cervezas, conservas y productos gourmet. Todos los productos de bebidas alcohólicas están destinados exclusivamente a personas <strong>mayores de 18 años</strong>.</p>
        <p>Las fotografías e imágenes de los productos son orientativas. Pueden existir variaciones en el diseño de etiquetas, añadas o presentación respecto a lo mostrado en la web.</p>

        <h2>Precios</h2>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>Todos los precios indicados en la web incluyen el <strong>IVA</strong> vigente.</li>
            <li>Los precios no incluyen los gastos de envío, que se calcularán y mostrarán antes de confirmar el pedido.</li>
            <li>El Celler Nou se reserva el derecho de modificar los precios en cualquier momento. No obstante, los productos se facturarán al precio vigente en el momento de la confirmación del pedido.</li>
            <li>En caso de error tipográfico evidente en el precio de un producto, nos pondremos en contacto con el cliente para informarle y ofrecerle la posibilidad de anular el pedido.</li>
        </ul>

        <h2>Proceso de compra</h2>
        <p>Para realizar un pedido a través de la web:</p>
        <ol style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>Seleccione los productos deseados y añádalos al carrito de compra.</li>
            <li>Revise el carrito e indique la dirección de envío y facturación.</li>
            <li>Seleccione el método de envío y revise el importe total (productos + envío).</li>
            <li>Seleccione la forma de pago y confirme el pedido.</li>
            <li>Recibirá un correo electrónico de confirmación con el resumen del pedido.</li>
        </ol>
        <p>También puede realizar pedidos por teléfono (<a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>"><?php echo SITE_PHONE; ?></a>) o por WhatsApp (<a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE_MOBILE); ?>"><?php echo SITE_PHONE_MOBILE; ?></a>), así como directamente en nuestra tienda física.</p>

        <h2>Formas de pago</h2>
        <p>Aceptamos las siguientes formas de pago:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Tarjeta de crédito/débito:</strong> Visa, Mastercard. El cobro se realiza en el momento de la confirmación del pedido mediante conexión segura SSL.</li>
            <li><strong>Transferencia bancaria:</strong> El pedido se tramitará una vez recibida la confirmación del pago.</li>
            <li><strong>Efectivo:</strong> Disponible para compras en tienda física y recogida en tienda.</li>
            <li><strong>Bizum:</strong> Disponible para pedidos por teléfono o WhatsApp.</li>
        </ul>

        <h2>Envíos y entrega</h2>
        <h3>Plazos de entrega</h3>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Zona metropolitana de Barcelona:</strong> 24-48 horas laborables.</li>
            <li><strong>Resto de Península:</strong> 48-72 horas laborables.</li>
            <li><strong>Baleares:</strong> 3-5 días laborables.</li>
            <li><strong>Canarias, Ceuta y Melilla:</strong> consultar.</li>
        </ul>
        <p>No se realizan entregas los sábados, domingos ni festivos. Los plazos indicados son orientativos y podrán variar en función de la disponibilidad y del servicio de transporte.</p>

        <h3>Embalaje</h3>
        <p>Todos los pedidos se embalan cuidadosamente en cajas específicas para botellas de vidrio, con protecciones individuales para garantizar la integridad del producto durante el transporte.</p>

        <h3>Recogida en tienda</h3>
        <p>El cliente puede optar por recoger su pedido gratuitamente en nuestra tienda: C/ Ramón Llull, nº 15, 08330 Premià de Mar (Barcelona).</p>

        <h2>Disponibilidad</h2>
        <p>Los pedidos están sujetos a la disponibilidad de los productos. En caso de que un producto no estuviera disponible tras la confirmación del pedido, le informaremos de inmediato y le ofreceremos un producto alternativo de características similares o el reembolso del importe.</p>

        <h2>Facturación</h2>
        <p>Se emitirá factura para todos los pedidos. Si necesita factura con datos fiscales específicos, indíquelo al realizar el pedido o contacte con nosotros.</p>

        <h2>Devoluciones</h2>
        <p>Para información detallada sobre devoluciones, consulte nuestra <a href="<?php echo Router::url('legal.devoluciones'); ?>">Política de Devoluciones</a>.</p>

        <h2>Legislación aplicable</h2>
        <p>Las presentes condiciones se rigen por la legislación española. Para cualquier controversia que pudiera derivarse, las partes se someten a los Juzgados y Tribunales de Barcelona, salvo que la normativa aplicable de consumo establezca otra jurisdicción.</p>

<?php elseif ($page === 'envios'): ?>
        <h2>Información general</h2>
        <p>En El Celler Nou trabajamos con empresas de transporte especializadas en el envío de botellas de vidrio y productos frágiles, garantizando que su pedido llegue en perfectas condiciones.</p>

        <h2>Zonas de envío y plazos de entrega</h2>
        <table style="width:100%; border-collapse:collapse; margin:var(--spacing-lg) 0; font-size:0.95rem;">
            <thead>
                <tr style="background:var(--color-bg-dark); color:white;">
                    <th style="padding:0.75rem 1rem; text-align:left;">Zona</th>
                    <th style="padding:0.75rem 1rem; text-align:left;">Plazo estimado</th>
                    <th style="padding:0.75rem 1rem; text-align:left;">Coste orientativo</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:0.75rem 1rem;">Premià de Mar y alrededores: <?php echo htmlspecialchars(listaNombresPoblaciones(getPoblacionesPorZona(1))); ?></td>
                    <td style="padding:0.75rem 1rem;"><?php echo htmlspecialchars(ZONAS_REPARTO[1]['plazo']); ?></td>
                    <td style="padding:0.75rem 1rem;">Gratuito (pedidos &gt; <?php echo SHIPPING_FREE_MIN_ORDER; ?>&euro;)</td>
                </tr>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:0.75rem 1rem;">Resto del Maresme cercano: <?php echo htmlspecialchars(listaNombresPoblaciones(getPoblacionesPorZona(2))); ?></td>
                    <td style="padding:0.75rem 1rem;"><?php echo htmlspecialchars(ZONAS_REPARTO[2]['plazo']); ?></td>
                    <td style="padding:0.75rem 1rem;">Consultar</td>
                </tr>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:0.75rem 1rem;">Área metropolitana de Barcelona</td>
                    <td style="padding:0.75rem 1rem;">24 - 48 horas laborables</td>
                    <td style="padding:0.75rem 1rem;">Consultar</td>
                </tr>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:0.75rem 1rem;">Resto de Cataluña</td>
                    <td style="padding:0.75rem 1rem;">24 - 48 horas laborables</td>
                    <td style="padding:0.75rem 1rem;">Consultar</td>
                </tr>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:0.75rem 1rem;">Península (resto de España)</td>
                    <td style="padding:0.75rem 1rem;">48 - 72 horas laborables</td>
                    <td style="padding:0.75rem 1rem;">Consultar</td>
                </tr>
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:0.75rem 1rem;">Baleares</td>
                    <td style="padding:0.75rem 1rem;">3 - 5 días laborables</td>
                    <td style="padding:0.75rem 1rem;">Consultar</td>
                </tr>
                <tr>
                    <td style="padding:0.75rem 1rem;">Canarias, Ceuta y Melilla</td>
                    <td style="padding:0.75rem 1rem;">Consultar</td>
                    <td style="padding:0.75rem 1rem;">Consultar</td>
                </tr>
            </tbody>
        </table>
        <p>Los plazos indicados son orientativos, comienzan a contar a partir de la confirmación del pago y se computan en días laborables (de lunes a viernes, excluidos festivos). Pueden variar en períodos de alta demanda (Navidad, campañas especiales).</p>

        <h2>Embalaje y protección</h2>
        <p>Todos los pedidos se preparan con el máximo cuidado:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Cajas reforzadas</strong> específicas para el transporte de botellas de vidrio.</li>
            <li><strong>Separadores individuales</strong> para cada botella, evitando el contacto directo entre ellas.</li>
            <li><strong>Protección adicional</strong> con material de relleno para amortiguar posibles impactos durante el transporte.</li>
            <li>Precintado con cinta adhesiva y etiquetado con indicación de <strong>"FRÁGIL"</strong>.</li>
        </ul>

        <h2>Recogida en tienda</h2>
        <p>El cliente puede optar por recoger su pedido <strong>gratuitamente</strong> en nuestra tienda:</p>
        <p style="padding:1rem; background:#f9fafb; border-radius:8px; margin:var(--spacing-lg) 0;">
            <strong>El Celler Nou</strong><br>
            C/ Ramón Llull, nº 15<br>
            08330 Premià de Mar (Barcelona)<br>
            Tel.: <?php echo SITE_PHONE; ?>
        </p>
        <p>Recibirá un aviso por email o teléfono cuando su pedido esté listo para recoger.</p>

        <h2>Entrega del pedido</h2>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>La entrega se realizará en la dirección indicada por el cliente al realizar el pedido.</li>
            <li>Es necesaria la <strong>presencia de una persona mayor de 18 años</strong> para recibir el pedido, dado que contiene bebidas alcohólicas. El transportista podrá solicitar identificación.</li>
            <li>Si en el momento de la entrega no hay nadie en el domicilio, el transportista dejará un aviso e intentará una segunda entrega. Tras dos intentos fallidos, el pedido será devuelto a nuestro almacén y el cliente deberá asumir los gastos de reenvío.</li>
            <li>En el momento de la entrega, el cliente deberá <strong>verificar el estado del paquete</strong> antes de firmar el albarán. Si el embalaje presenta daños visibles, deberá hacerlo constar en el albarán del transportista.</li>
        </ul>

        <h2>Incidencias en la entrega</h2>
        <h3>Paquete dañado</h3>
        <p>Si al recibir el pedido detecta que el embalaje presenta golpes, roturas o señales de manipulación:</p>
        <ol style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>Haga constar la incidencia en el albarán del transportista <strong>antes de firmar</strong>.</li>
            <li>Si es posible, tome fotografías del embalaje y del contenido dañado.</li>
            <li>Contacte con nosotros en un plazo máximo de <strong>24 horas</strong> al email <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a> o al teléfono <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>"><?php echo SITE_PHONE; ?></a>.</li>
            <li>Procederemos a la reposición del producto dañado o al reembolso, sin coste para el cliente.</li>
        </ol>

        <h3>Pedido incorrecto</h3>
        <p>Si el contenido del pedido no corresponde con lo solicitado, contacte con nosotros en un plazo de <strong>48 horas</strong>. Nos haremos cargo de la recogida del producto incorrecto y del envío del correcto sin coste alguno.</p>

        <h3>Retraso en la entrega</h3>
        <p>Si su pedido no llega dentro del plazo estimado, contacte con nosotros. Realizaremos el seguimiento con la empresa de transporte y le mantendremos informado. Si el retraso supera los 30 días desde la confirmación del pedido, podrá cancelarlo y solicitar el reembolso íntegro.</p>

        <h2>Condiciones especiales para épocas de calor</h2>
        <p>Durante los meses de verano (junio-septiembre), los vinos y productos sensibles a la temperatura se envían con las precauciones necesarias para mantener la cadena de frío. Recomendamos a nuestros clientes que, si no van a estar en el domicilio de entrega, elijan la opción de recogida en tienda o se aseguren de que haya alguien para recibir el paquete y refrigerarlo cuanto antes.</p>

        <h2>Contacto</h2>
        <p>Para cualquier consulta sobre envíos:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Email:</strong> <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a></li>
            <li><strong>Teléfono:</strong> <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>"><?php echo SITE_PHONE; ?></a></li>
            <li><strong>WhatsApp:</strong> <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', SITE_PHONE_MOBILE); ?>"><?php echo SITE_PHONE_MOBILE; ?></a></li>
        </ul>

<?php elseif ($page === 'devoluciones'): ?>
        <h2>Derecho de desistimiento</h2>
        <p>De conformidad con la legislación vigente, el cliente tiene derecho a desistir de la compra en un plazo de <strong>14 días naturales</strong> a partir de la recepción del pedido, sin necesidad de justificación alguna.</p>

        <h2>Condiciones para la devolución</h2>
        <p>Dada la naturaleza de nuestros productos (botellas de vidrio con contenido alcohólico), para que la devolución sea aceptada deberán cumplirse las siguientes condiciones:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>El producto debe estar <strong>sin abrir</strong>, con su precinto original intacto y en las mismas condiciones en las que fue entregado.</li>
            <li>La botella debe conservarse en su <strong>embalaje original</strong> o, en su defecto, en un embalaje que garantice su correcta protección durante el transporte (las botellas de vidrio requieren embalaje específico para evitar roturas).</li>
            <li>No se aceptarán devoluciones de productos que hayan sido abiertos, manipulados o conservados en condiciones inadecuadas de temperatura o humedad.</li>
            <li>Es imprescindible adjuntar una copia del <strong>justificante de compra</strong> o número de pedido.</li>
        </ul>

        <h2>Exclusiones del derecho de desistimiento</h2>
        <p>De acuerdo con el artículo 103 del Real Decreto Legislativo 1/2007, no será posible ejercer el derecho de desistimiento en los siguientes casos:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>Productos precintados que hayan sido desprecintados tras la entrega y que no puedan ser devueltos por razones de higiene o protección de la salud.</li>
            <li>Bebidas alcohólicas cuyo precio haya sido acordado en el momento de celebrar el contrato de compra y que no puedan ser entregadas antes de 30 días, y cuyo valor real dependa de fluctuaciones del mercado que el empresario no pueda controlar.</li>
            <li>Productos personalizados o confeccionados según las indicaciones del cliente.</li>
        </ul>

        <h2>Procedimiento de devolución</h2>
        <p>Para iniciar una devolución, siga estos pasos:</p>
        <ol style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>Comunique su intención de devolver el producto enviando un correo a <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a> o llamando al <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>"><?php echo SITE_PHONE; ?></a>, indicando el número de pedido y los productos a devolver.</li>
            <li>Le confirmaremos la dirección y las instrucciones de envío.</li>
            <li>Embale los productos de forma segura, especialmente las botellas de vidrio, utilizando protecciones adecuadas para evitar roturas durante el transporte.</li>
            <li>Envíe el paquete a la dirección indicada.</li>
        </ol>

        <h2>Gastos de devolución</h2>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Desistimiento del cliente:</strong> Los gastos de envío de la devolución corren a cargo del cliente.</li>
            <li><strong>Producto defectuoso o error en el envío:</strong> Si el producto recibido está dañado, defectuoso o no se corresponde con lo solicitado, ALMABRIS GROUP 2024 S.L. asumirá todos los gastos de recogida y envío del producto correcto.</li>
        </ul>

        <h2>Productos dañados durante el transporte</h2>
        <p>Si al recibir su pedido detecta que alguna botella está rota o el embalaje presenta daños evidentes:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li>Haga constar la incidencia en el albarán del transportista en el momento de la entrega.</li>
            <li>Contacte con nosotros en un plazo máximo de <strong>24 horas</strong> desde la recepción, aportando fotografías de los daños.</li>
            <li>Procederemos a la reposición del producto o al reembolso íntegro, sin coste alguno para el cliente.</li>
        </ul>

        <h2>Reembolsos</h2>
        <p>Una vez recibido y verificado el producto devuelto, procederemos al reembolso del importe en un plazo máximo de <strong>14 días naturales</strong> utilizando el mismo medio de pago empleado en la compra original. El reembolso incluirá el precio del producto. Los gastos de envío iniciales solo se reembolsarán en caso de error por nuestra parte o producto defectuoso.</p>

        <h2>Contacto</h2>
        <p>Para cualquier consulta relacionada con devoluciones, puede contactar con nosotros:</p>
        <ul style="margin: var(--spacing-lg) 0; padding-left: var(--spacing-xl);">
            <li><strong>Email:</strong> <a href="mailto:<?php echo SITE_EMAIL; ?>"><?php echo SITE_EMAIL; ?></a></li>
            <li><strong>Teléfono:</strong> <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>"><?php echo SITE_PHONE; ?></a></li>
            <li><strong>Dirección:</strong> C/ Ramón Llull, nº 15, 08330 Premià de Mar (Barcelona)</li>
        </ul>

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
