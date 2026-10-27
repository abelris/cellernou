<?php
/**
 * El Celler Nou - Catas
 *
 * /catas   Catas abiertas (la gente se apunta y, al llegar al mínimo, se pone
 *          fecha y se les avisa) y catas privadas a medida.
 *
 * Confirmado por el usuario (26-oct-2026, docs/seo/datos-productos-usuario.md):
 * catas públicas con un mínimo de asistentes antes de fijar fecha, y catas
 * privadas personalizadas. Precio y lugar van por cata (admin WineWorld >
 * Catas); si no están, no se anuncian.
 */

require_once ROOT_PATH . '/includes/catas.php';

$breadcrumbItems = [
    ['name' => 'Inicio', 'url' => Router::url('home')],
    ['name' => 'Servicios', 'url' => Router::url('servicios')],
    ['name' => 'Catas', 'url' => Router::url('catas')]
];

// Envío de formularios: PRG para que recargar no duplique la inscripción.
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = guardarInscripcionCata($_POST);
    if ($resultado['ok']) {
        $ancla = $resultado['tipo'] === 'privada' ? 'privadas' : 'abiertas';
        Router::redirect(Router::url('catas') . '?enviado=' . $resultado['tipo'] . '#' . $ancla, 303);
    }
    $error = [
        'tipo'    => $_POST['tipo'] ?? 'publica',
        'id_cata' => (int) ($_POST['id_cata'] ?? 0),
        'texto'   => $resultado['error'],
    ];
}
$enviado = in_array($_GET['enviado'] ?? '', CATAS_TIPOS, true) ? $_GET['enviado'] : null;

$catas = getCatasAbiertas();

// Si la cata ya no se muestra (se cerró mientras tenían la página abierta),
// el error sale en el bloque de «avisadme».
if ($error && $error['tipo'] === 'publica' && !in_array($error['id_cata'], array_map('intval', array_column($catas, 'id')), true)) {
    $error['tipo'] = 'aviso';
    $error['id_cata'] = 0;
}

$pageTitle = 'Catas de vino en el Maresme: abiertas y privadas | El Celler Nou';
$pageDescription = 'Catas de vino en Premià de Mar: apúntate a las catas abiertas y, al llegar al mínimo de personas, '
    . 'te avisamos con la fecha. También catas privadas para grupos y empresas.';

$faqs = [
    [
        'pregunta'  => '¿Cómo funcionan las catas abiertas?',
        'respuesta' => 'Cada cata tiene un mínimo de personas. Te apuntas a la que te apetezca y, cuando se llega al mínimo, '
            . 'ponemos la fecha y avisamos a todos los apuntados por correo. Apuntarte no te compromete: con la fecha '
            . 'delante nos confirmas si puedes venir.',
    ],
    [
        'pregunta'  => '¿Cuándo es la próxima cata?',
        'respuesta' => 'Depende de cuándo se llene cada una. En esta página ves cuántas personas hay apuntadas a cada cata '
            . 'y cuántas faltan. Si ninguna te encaja, déjanos tu correo y te avisamos de las próximas.',
    ],
    [
        'pregunta'  => '¿Hacéis catas privadas o para empresas?',
        'respuesta' => 'Sí. Para un grupo de amigos, la familia, tu empresa o una celebración: nos dices cuántos sois, '
            . 'qué os apetece probar y cuándo os va bien, y preparamos la cata a vuestra medida.',
    ],
    [
        'pregunta'  => '¿Y si me apunto y luego no puedo ir?',
        'respuesta' => 'Escríbenos por WhatsApp o responde al correo de la cata y te quitamos de la lista, sin problema.',
    ],
];

// Schema: el servicio de catas y, para las que ya tienen fecha, un Event.
$url = rtrim(SITE_URL, '/') . '/catas';
$grafo = [[
    '@type'       => 'Service',
    '@id'         => $url . '#servicio',
    'name'        => 'Catas de vino',
    'serviceType' => 'Catas de vino abiertas y privadas',
    'description' => $pageDescription,
    'provider'    => ['@id' => schemaTiendaId()],
    'areaServed'  => ['@type' => 'AdministrativeArea', 'name' => 'Maresme'],
    'url'         => $url,
]];
foreach ($catas as $c) {
    if ($c['estado'] !== 'confirmada' || !$c['fecha']) continue;
    $evento = [
        '@type'               => 'Event',
        '@id'                 => $url . '#cata-' . $c['id'],
        'name'                => $c['titulo'],
        'description'         => $c['descripcion'] ?: $c['titulo'],
        'startDate'           => date('c', strtotime($c['fecha'])),
        'eventStatus'         => 'https://schema.org/EventScheduled',
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'location'            => $c['lugar']
            ? ['@type' => 'Place', 'name' => $c['lugar'], 'address' => $c['lugar']]
            : ['@id' => schemaTiendaId()],
        'organizer'           => ['@id' => schemaTiendaId()],
        'url'                 => $url . '#cata-' . $c['id'],
    ];
    if ($c['precio'] !== null) {
        $evento['offers'] = [
            '@type'         => 'Offer',
            'price'         => number_format((float) $c['precio'], 2, '.', ''),
            'priceCurrency' => 'EUR',
            'availability'  => cataTienePlazas($c) ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
            'url'           => $url . '#cata-' . $c['id'],
        ];
    }
    $grafo[] = $evento;
}
$schemaExtra = schemaBreadcrumb($breadcrumbItems)
    . schemaJson(['@context' => 'https://schema.org', '@graph' => $grafo])
    . schemaFAQ($faqs);

// Valor anterior de un campo si el formulario que falló es este.
$valor = function ($campo, $tipo, $idCata = 0) use ($error) {
    if (!$error || $error['tipo'] !== $tipo || $error['id_cata'] !== $idCata) return '';
    return htmlspecialchars((string) ($_POST[$campo] ?? ''));
};
$errorEn = function ($tipo, $idCata = 0) use ($error) {
    return $error && $error['tipo'] === $tipo && $error['id_cata'] === $idCata;
};

include ROOT_PATH . '/includes/header.php';

/** Campos comunes: tipo, cata, antispam, nombre, correo y teléfono. */
function camposBaseCata($tipo, $idCata, $valor, $prefijo) {
    ?>
    <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
    <input type="hidden" name="id_cata" value="<?php echo (int) $idCata; ?>">
    <input type="hidden" name="t" value="<?php echo time(); ?>">
    <div class="campo-trampa" aria-hidden="true">
        <label for="<?php echo $prefijo; ?>-web">No rellenes este campo</label>
        <input type="text" id="<?php echo $prefijo; ?>-web" name="web" tabindex="-1" autocomplete="off">
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="<?php echo $prefijo; ?>-nombre">Nombre *</label>
            <input type="text" id="<?php echo $prefijo; ?>-nombre" name="nombre" required maxlength="120" autocomplete="name" value="<?php echo $valor('nombre', $tipo, $idCata); ?>">
        </div>
        <div class="form-group">
            <label for="<?php echo $prefijo; ?>-email">Correo *</label>
            <input type="email" id="<?php echo $prefijo; ?>-email" name="email" required maxlength="150" autocomplete="email" value="<?php echo $valor('email', $tipo, $idCata); ?>">
        </div>
    </div>
    <?php
}
?>

<section class="page-header domicilio-header">
    <div class="container">
        <?php echo generarBreadcrumb($breadcrumbItems); ?>
        <h1>Catas de vino en Premià de Mar</h1>
        <p>Catas abiertas para conocer los vinos de una zona, de una uva o de un estilo, probándolos con calma y con alguien que te los explica. Y catas privadas a vuestra medida, para amigos, familias, empresas o celebraciones.</p>
        <div class="domicilio-cta">
            <a href="#abiertas" class="btn btn-primary btn-lg">Ver las catas abiertas</a>
            <a href="#privadas" class="btn btn-outline btn-lg">Pedir una cata privada</a>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>Cómo funcionan las catas abiertas</h2>
        </div>
        <ol class="domicilio-pasos">
            <li>
                <h3>Te apuntas a la que te apetece</h3>
                <p>Eliges la cata, nos dejas tu nombre y tu correo y nos dices cuántos venís. Apuntarte no te compromete a nada.</p>
            </li>
            <li>
                <h3>Llegamos al mínimo y ponemos fecha</h3>
                <p>Cada cata necesita un número mínimo de personas. En cuanto se llega, fijamos el día y os avisamos a todos por correo.</p>
            </li>
            <li>
                <h3>Nos confirmas y catamos</h3>
                <p>Con la fecha delante nos dices si puedes venir. Y a probar, preguntar y descubrir vinos nuevos.</p>
            </li>
        </ol>
    </div>
</section>

<section class="section" id="abiertas">
    <div class="container">
        <div class="section-header">
            <h2>Próximas catas abiertas</h2>
            <?php if ($catas): ?>
            <p>Apúntate a la que quieras. Debajo de cada una ves cuántas personas hay y cuántas faltan para ponerle fecha.</p>
            <?php endif; ?>
        </div>

        <?php if ($enviado === 'publica'): ?>
        <div class="form-message success">¡Apuntado! Cuando la cata llegue al mínimo de personas te escribimos con la fecha.</div>
        <?php elseif ($enviado === 'aviso'): ?>
        <div class="form-message success">Hecho. Te escribiremos cuando abramos nuevas catas.</div>
        <?php endif; ?>

        <?php if (!$catas): ?>
        <p class="catas-vacio">Ahora mismo estamos preparando las próximas catas. Déjanos tu correo aquí debajo y te avisamos en cuanto abramos una.</p>
        <?php else: ?>
        <div class="catas-lista">
            <?php foreach ($catas as $c): ?>
            <?php
            $idC = (int) $c['id'];
            $faltan = max(0, $c['minimo'] - $c['apuntados']);
            $hayPlazas = cataTienePlazas($c);
            $porcentaje = min(100, $c['minimo'] > 0 ? round($c['apuntados'] / $c['minimo'] * 100) : 100);
            ?>
            <article class="cata" id="cata-<?php echo $idC; ?>">
                <h3><?php echo htmlspecialchars($c['titulo']); ?></h3>
                <?php if ($c['descripcion']): ?>
                <p><?php echo nl2br(htmlspecialchars($c['descripcion'])); ?></p>
                <?php endif; ?>

                <dl class="cata-datos">
                    <div>
                        <dt>Fecha</dt>
                        <dd><?php echo $c['estado'] === 'confirmada' && $c['fecha']
                            ? htmlspecialchars(ucfirst(fechaCataLegible($c['fecha'])))
                            : 'La ponemos cuando seamos ' . $c['minimo']; ?></dd>
                    </div>
                    <?php if ($c['lugar']): ?>
                    <div>
                        <dt>Lugar</dt>
                        <dd><?php echo htmlspecialchars($c['lugar']); ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($c['precio'] !== null): ?>
                    <div>
                        <dt>Precio</dt>
                        <dd><?php echo precioCata($c['precio']); ?> por persona</dd>
                    </div>
                    <?php endif; ?>
                </dl>

                <?php if ($c['estado'] === 'abierta'): ?>
                <div class="cata-progreso" role="img" aria-label="<?php echo $c['apuntados'] . ' de ' . $c['minimo'] . ' personas apuntadas'; ?>">
                    <span style="width: <?php echo $porcentaje; ?>%"></span>
                </div>
                <p class="cata-progreso-texto">
                    <?php if ($faltan > 0): ?>
                    <strong><?php echo $c['apuntados']; ?> de <?php echo $c['minimo']; ?></strong> apuntados · <?php echo $faltan === 1 ? 'falta 1' : 'faltan ' . $faltan; ?> para ponerle fecha
                    <?php else: ?>
                    <strong>¡Ya somos <?php echo $c['apuntados']; ?>!</strong> Estamos cerrando la fecha
                    <?php endif; ?>
                </p>
                <?php elseif ($c['maximo'] !== null): ?>
                <p class="cata-progreso-texto">
                    <?php echo $hayPlazas ? 'Quedan ' . ($c['maximo'] - $c['apuntados']) . ' plazas' : '<strong>Completa</strong>'; ?>
                </p>
                <?php endif; ?>

                <?php if ($hayPlazas): ?>
                <details class="cata-apuntarse"<?php echo $errorEn('publica', $idC) ? ' open' : ''; ?>>
                    <summary class="btn btn-primary">Apuntarme</summary>
                    <form action="<?php echo Router::url('catas'); ?>#cata-<?php echo $idC; ?>" method="POST" class="cata-form">
                        <?php if ($errorEn('publica', $idC)): ?>
                        <div class="form-message error"><?php echo htmlspecialchars($error['texto']); ?></div>
                        <?php endif; ?>
                        <?php camposBaseCata('publica', $idC, $valor, 'c' . $idC); ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="c<?php echo $idC; ?>-telefono">Teléfono</label>
                                <input type="tel" id="c<?php echo $idC; ?>-telefono" name="telefono" maxlength="30" autocomplete="tel" value="<?php echo $valor('telefono', 'publica', $idC); ?>">
                            </div>
                            <div class="form-group">
                                <label for="c<?php echo $idC; ?>-personas">¿Cuántos venís?</label>
                                <input type="number" id="c<?php echo $idC; ?>-personas" name="personas" min="1" max="20" value="<?php echo $valor('personas', 'publica', $idC) ?: 1; ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Apuntarme a esta cata</button>
                        <p class="cata-form-nota">Solo usamos tus datos para avisarte de esta cata. <a href="<?php echo Router::url('legal.privacidad'); ?>">Privacidad</a>.</p>
                    </form>
                </details>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="cata-aviso">
            <h3><?php echo $catas ? '¿Ninguna te encaja?' : 'Avísame de las próximas catas'; ?></h3>
            <p>Déjanos tu correo y te escribimos cuando abramos una cata nueva.</p>
            <form action="<?php echo Router::url('catas'); ?>#abiertas" method="POST" class="cata-form">
                <?php if ($errorEn('aviso')): ?>
                <div class="form-message error"><?php echo htmlspecialchars($error['texto']); ?></div>
                <?php endif; ?>
                <?php camposBaseCata('aviso', 0, $valor, 'aviso'); ?>
                <div class="form-group">
                    <label for="aviso-comentario">¿Qué te gustaría catar? (opcional)</label>
                    <input type="text" id="aviso-comentario" name="comentario" maxlength="300" placeholder="Una zona, una uva, cavas, whiskies…" value="<?php echo $valor('comentario', 'aviso'); ?>">
                </div>
                <button type="submit" class="btn btn-outline">Avisadme</button>
            </form>
        </div>
    </div>
</section>

<section class="section section-alt" id="privadas">
    <div class="container">
        <div class="catas-privadas">
            <div>
                <h2>Catas privadas, a vuestra medida</h2>
                <p>Para un grupo de amigos, la familia, tu empresa o una celebración. Nos dices cuántos sois, qué os apetece probar y cuándo os va bien, y preparamos la cata para vosotros: los vinos, el ritmo y la explicación.</p>
                <p>Puede ser una vuelta por las D.O. de Catalunya, los vinos del Maresme, cavas, tintos de guarda o lo que tengáis en mente.</p>
                <p class="servicio-acciones">
                    <a href="<?php echo htmlspecialchars(whatsappUrl('Hola, me gustaría organizar una cata privada.')); ?>" target="_blank" rel="noopener noreferrer">
                        <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"></use></svg>
                        Pregúntanos por WhatsApp
                    </a>
                    <a href="tel:<?php echo preg_replace('/\s+/', '', SITE_PHONE); ?>">Llamar al <?php echo SITE_PHONE; ?></a>
                </p>
            </div>
            <div class="contact-form-wrapper">
                <?php if ($enviado === 'privada'): ?>
                <div class="form-message success">¡Gracias! Hemos recibido tu petición y te escribimos para preparar la cata.</div>
                <?php endif; ?>
                <form action="<?php echo Router::url('catas'); ?>#privadas" method="POST" class="cata-form">
                    <?php if ($errorEn('privada')): ?>
                    <div class="form-message error"><?php echo htmlspecialchars($error['texto']); ?></div>
                    <?php endif; ?>
                    <?php camposBaseCata('privada', 0, $valor, 'privada'); ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="privada-telefono">Teléfono</label>
                            <input type="tel" id="privada-telefono" name="telefono" maxlength="30" autocomplete="tel" value="<?php echo $valor('telefono', 'privada'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="privada-personas">¿Cuántos seréis?</label>
                            <input type="number" id="privada-personas" name="personas" min="1" max="50" value="<?php echo $valor('personas', 'privada') ?: 6; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="privada-motivo">Es para…</label>
                        <select id="privada-motivo" name="motivo">
                            <?php foreach (CATAS_MOTIVOS as $clave => $texto): ?>
                            <option value="<?php echo $clave; ?>"<?php echo $valor('motivo', 'privada') === $clave ? ' selected' : ''; ?>><?php echo htmlspecialchars($texto); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="privada-comentario">Cuéntanos un poco</label>
                        <textarea id="privada-comentario" name="comentario" rows="4" maxlength="2000" placeholder="Qué os gustaría probar, qué días os van bien, si tenéis un presupuesto…"><?php echo $valor('comentario', 'privada'); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">Pedir mi cata privada</button>
                    <p class="cata-form-nota">Solo usamos tus datos para preparar la cata. <a href="<?php echo Router::url('legal.privacidad'); ?>">Privacidad</a>.</p>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Más que catas</h2>
            <p>Lo que probéis en la cata lo tenéis en la tienda, y si os gusta os lo llevamos a casa.</p>
        </div>
        <div class="text-center servicio-acciones" style="justify-content:center;">
            <a href="<?php echo Router::url('vinos.do', ['id' => 33, 'slug' => 'd-o-alella']); ?>">Vinos de Alella</a>
            <a href="<?php echo Router::url('vinos'); ?>">Todos los vinos</a>
            <a href="<?php echo Router::url('lotes'); ?>">Lotes y regalos de empresa</a>
            <a href="<?php echo Router::url('servicios'); ?>">Todos nuestros servicios</a>
        </div>
    </div>
</section>

<?php echo renderFaqs($faqs, 'Preguntas frecuentes sobre las catas'); ?>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
