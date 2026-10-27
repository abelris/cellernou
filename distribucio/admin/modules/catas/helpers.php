<?php
/**
 * Catas - comú a les pàgines de l'admin
 *
 * Reutilitza includes/catas.php de la web (dates, preus, correu). Aquí no es
 * carrega la configuració pública, així que es defineixen les dues constants
 * que fa servir el correu.
 */
if (!defined('SITE_NAME')) define('SITE_NAME', 'El Celler Nou');
if (!defined('SITE_EMAIL')) define('SITE_EMAIL', 'cellernou@cellernou.com');
if (!defined('CATAS_TELEFONO')) define('CATAS_TELEFONO', '937 520 271');
if (!defined('CATAS_WHATSAPP')) define('CATAS_WHATSAPP', '637 626 302');

require_once dirname(DISTRIBUCIO_PATH) . '/includes/catas.php';

/** Enllaç de WhatsApp a un client (9 xifres = mòbil espanyol). */
function catasWhatsapp($tel, $text) {
    $num = preg_replace('/[^0-9]/', '', (string) $tel);
    if ($num === '') return null;
    if (strlen($num) === 9) $num = '34' . $num;
    return 'https://wa.me/' . $num . '?text=' . rawurlencode($text);
}

/** Correu (en castellà) que rep cada apuntat quan la cata ja té data. */
function catasCorreuData(array $cata, array $ins) {
    $assumpte = 'Cata «' . $cata['titulo'] . '»: ya tenemos fecha';
    $cos = 'Hola, ' . $ins['nombre'] . ":\n\n"
        . '¡Ya somos suficientes para la cata «' . $cata['titulo'] . '»! La hacemos el '
        . fechaCataLegible($cata['fecha'])
        . ($cata['lugar'] ? ', en ' . $cata['lugar'] : '') . ".\n";
    if ($cata['precio'] !== null && $cata['precio'] !== '') {
        $cos .= 'Precio: ' . precioCata($cata['precio']) . " por persona.\n";
    }
    $cos .= "\n" . 'Te apuntaste para ' . (int) $ins['personas'] . ((int) $ins['personas'] === 1 ? ' persona' : ' personas')
        . '. ¿Nos confirmas si podéis venir? Basta con responder a este correo o escribirnos por WhatsApp al '
        . CATAS_WHATSAPP . ".\n\n"
        . "Si al final no podéis, dínoslo también y dejamos la plaza a otra persona.\n\n"
        . "Un saludo,\n" . SITE_NAME . "\n"
        . 'C/ Ramón Llull 15, Premià de Mar · ' . CATAS_TELEFONO . "\nhttps://cellernou.com/catas\n";
    return enviarCorreoCata($ins['email'], $assumpte, $cos);
}

/** Text del WhatsApp amb la data, per als que tenen telèfon. */
function catasTextWhatsappData(array $cata, array $ins) {
    return 'Hola ' . $ins['nombre'] . ', somos El Celler Nou. ¡Ya tenemos fecha para la cata «' . $cata['titulo'] . '»! '
        . 'Es el ' . fechaCataLegible($cata['fecha']) . ($cata['lugar'] ? ', en ' . $cata['lugar'] : '')
        . '. ¿Nos confirmas si venís?';
}
