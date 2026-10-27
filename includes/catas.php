<?php
/**
 * El Celler Nou - Catas
 *
 * Catas abiertas (/catas): se publican sin fecha y la gente se apunta. Cuando
 * los apuntados llegan al mínimo de la cata, se pone la fecha en el admin
 * (WineWorld > Catas) y se avisa a todos por correo.
 * Las catas privadas y el «avisadme de las próximas» se guardan en la misma
 * tabla de inscripciones, sin cata.
 *
 * Tablas: sql/migration_catas.sql.
 */

/** Tipos de inscripción que admite el formulario. */
const CATAS_TIPOS = ['publica', 'privada', 'aviso'];

/** Para qué es una cata privada (opciones del formulario). */
const CATAS_MOTIVOS = [
    'amigos'      => 'Con amigos o en familia',
    'empresa'     => 'Para una empresa o un equipo',
    'celebracion' => 'Una celebración (cumpleaños, despedida…)',
    'regalo'      => 'Para regalar',
    'otro'        => 'Otra cosa',
];

/**
 * Catas publicadas que aún se pueden ver: abiertas o confirmadas con fecha
 * futura. Cada una lleva 'apuntados' (suma de personas, sin bajas).
 */
function getCatasAbiertas() {
    global $bd;
    $catas = [];
    $r = @mysqli_query($bd, "SELECT c.*,
                (SELECT COALESCE(SUM(i.personas), 0) FROM catas_inscripciones i
                  WHERE i.id_cata = c.id AND i.tipo = 'publica' AND i.estado <> 'baja') AS apuntados
            FROM catas c
            WHERE c.publicada = 1
              AND (c.estado = 'abierta' OR (c.estado = 'confirmada' AND c.fecha >= CURDATE()))
            ORDER BY c.fecha IS NULL, c.fecha, c.orden, c.id");
    while ($r && $row = mysqli_fetch_assoc($r)) {
        $row['apuntados'] = (int) $row['apuntados'];
        $row['minimo'] = (int) $row['minimo'];
        $row['maximo'] = $row['maximo'] !== null ? (int) $row['maximo'] : null;
        $catas[] = $row;
    }
    return $catas;
}

/** ¿Quedan plazas? Sin máximo, siempre. */
function cataTienePlazas(array $cata, $personas = 1) {
    return $cata['maximo'] === null || $cata['apuntados'] + $personas <= $cata['maximo'];
}

/** «sábado 15 de noviembre a las 19:00» */
function fechaCataLegible($fecha) {
    if (!$fecha) return '';
    $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $t = strtotime($fecha);
    $texto = $dias[(int) date('w', $t)] . ' ' . (int) date('j', $t) . ' de ' . $meses[(int) date('n', $t)];
    if (date('Y', $t) !== date('Y')) $texto .= ' de ' . date('Y', $t);
    if (date('H:i', $t) !== '00:00') $texto .= ' a las ' . date('H:i', $t);
    return $texto;
}

/** Precio con coma: «25 €», «12,50 €». */
function precioCata($precio) {
    $p = (float) $precio;
    return (floor($p) == $p ? number_format($p, 0, ',', '.') : number_format($p, 2, ',', '.')) . ' €';
}

/**
 * Valida y guarda una inscripción del formulario de /catas.
 * Devuelve ['ok' => true, 'tipo' => ...] o ['ok' => false, 'error' => '...'].
 */
function guardarInscripcionCata(array $post) {
    global $bd;

    // Antispam: el campo trampa va oculto y el formulario no se envía en menos de 3 s.
    if (!empty($post['web']) || (time() - (int) ($post['t'] ?? 0)) < 3) {
        return ['ok' => false, 'error' => 'No hemos podido enviar el formulario. Inténtalo de nuevo.'];
    }

    $tipo = in_array($post['tipo'] ?? '', CATAS_TIPOS, true) ? $post['tipo'] : 'publica';
    $nombre = trim(mb_substr((string) ($post['nombre'] ?? ''), 0, 120));
    $email = trim(mb_substr((string) ($post['email'] ?? ''), 0, 150));
    $telefono = trim(mb_substr((string) ($post['telefono'] ?? ''), 0, 30));
    $personas = max(1, min(50, (int) ($post['personas'] ?? 1)));
    $motivo = array_key_exists($post['motivo'] ?? '', CATAS_MOTIVOS) ? $post['motivo'] : null;
    $comentario = trim(mb_substr((string) ($post['comentario'] ?? ''), 0, 2000));

    if ($nombre === '' || $email === '') {
        return ['ok' => false, 'error' => 'Necesitamos tu nombre y tu correo para avisarte.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'El correo no parece correcto. ¿Lo revisas?'];
    }

    $cata = null;
    if ($tipo === 'publica') {
        $idCata = (int) ($post['id_cata'] ?? 0);
        foreach (getCatasAbiertas() as $c) {
            if ((int) $c['id'] === $idCata) $cata = $c;
        }
        if (!$cata) {
            return ['ok' => false, 'error' => 'Esa cata ya no está abierta. Mira las demás o pídenos que te avisemos.'];
        }
        if (!cataTienePlazas($cata, $personas)) {
            return ['ok' => false, 'error' => 'No quedan plazas para ' . $personas . ' personas en esta cata. Escríbenos y lo miramos.'];
        }
    } else {
        $personas = $tipo === 'aviso' ? 1 : $personas;
    }

    $ip = substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);
    $ipEsc = mysqli_real_escape_string($bd, $ip);
    $r = mysqli_query($bd, "SELECT COUNT(*) FROM catas_inscripciones
                            WHERE ip = '$ipEsc' AND created_at > NOW() - INTERVAL 1 HOUR");
    if ($r && (int) mysqli_fetch_row($r)[0] >= 5) {
        return ['ok' => false, 'error' => 'Hemos recibido varios formularios seguidos desde tu conexión. Escríbenos por WhatsApp.'];
    }

    $stmt = mysqli_prepare($bd, "INSERT INTO catas_inscripciones
            (tipo, id_cata, nombre, email, telefono, personas, motivo, comentario, ip)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $idCataDb = $cata ? (int) $cata['id'] : null;
    $telefonoDb = $telefono !== '' ? $telefono : null;
    $comentarioDb = $comentario !== '' ? $comentario : null;
    mysqli_stmt_bind_param($stmt, 'sisssisss', $tipo, $idCataDb, $nombre, $email, $telefonoDb,
                           $personas, $motivo, $comentarioDb, $ip);
    if (!mysqli_stmt_execute($stmt)) {
        return ['ok' => false, 'error' => 'No hemos podido guardar tu inscripción. Inténtalo de nuevo o escríbenos por WhatsApp.'];
    }

    avisarTiendaInscripcionCata($tipo, $cata, compact('nombre', 'email', 'telefono', 'personas', 'motivo', 'comentario'));
    return ['ok' => true, 'tipo' => $tipo];
}

/** Correo a la tienda con cada inscripción. Avisa también cuando se llega al mínimo. */
function avisarTiendaInscripcionCata($tipo, $cata, array $d) {
    if ($tipo === 'publica') {
        $total = $cata['apuntados'] + $d['personas'];
        $asunto = 'Cata «' . $cata['titulo'] . '»: ' . $d['nombre'] . ' se apunta (' . $total . ' de ' . $cata['minimo'] . ')';
        if ($cata['apuntados'] < $cata['minimo'] && $total >= $cata['minimo']) {
            $asunto = 'Ya hay mínimo para la cata «' . $cata['titulo'] . '»: toca poner fecha';
        }
    } elseif ($tipo === 'privada') {
        $asunto = 'Petición de cata privada: ' . $d['nombre'];
    } else {
        $asunto = 'Quiere que le aviséis de las próximas catas: ' . $d['nombre'];
    }

    $cuerpo = $asunto . "\n\n"
        . 'Nombre: ' . $d['nombre'] . "\n"
        . 'Correo: ' . $d['email'] . "\n"
        . 'Teléfono: ' . ($d['telefono'] ?: '-') . "\n";
    if ($tipo !== 'aviso') $cuerpo .= 'Personas: ' . $d['personas'] . "\n";
    if ($d['motivo']) $cuerpo .= 'Para: ' . CATAS_MOTIVOS[$d['motivo']] . "\n";
    if ($d['comentario']) $cuerpo .= "\nComentario:\n" . $d['comentario'] . "\n";
    $cuerpo .= "\nLas inscripciones están en el admin: WineWorld > Catas.\n";

    enviarCorreoCata(SITE_EMAIL, $asunto, $cuerpo, $d['email']);
}

/** Correo de texto en UTF-8 desde la dirección de la tienda. */
function enviarCorreoCata($para, $asunto, $cuerpo, $responderA = null) {
    $cabeceras = 'From: ' . SITE_NAME . ' <' . SITE_EMAIL . ">\r\n"
        . 'Reply-To: ' . ($responderA ?: SITE_EMAIL) . "\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n";
    return @mail($para, '=?UTF-8?B?' . base64_encode($asunto) . '?=', $cuerpo, $cabeceras);
}
