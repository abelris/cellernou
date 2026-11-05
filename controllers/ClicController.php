<?php
/**
 * El Celler Nou - Registro de clics en WhatsApp y Llamar
 *
 * POST /clic (lo envía js/main.js con sendBeacon). Suma 1 al contador del día,
 * el tipo y la página. El pueblo se deduce aquí de la ruta, no se acepta del
 * navegador. Siempre responde 204: al visitante no le afecta nada de esto.
 *
 * Tabla: sql/migration_clics_contacto.sql. Resumen: admin, BI > Clics.
 */

global $bd;

http_response_code(204);
header('Cache-Control: no-store');

$tipo = $_POST['tipo'] ?? '';
$pagina = $_POST['pagina'] ?? '';
$agente = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Solo peticiones de la propia web. Sec-Fetch-Site lo pone el navegador y una
// web ajena no puede falsearlo.
$mismoOrigen = ($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'same-origin';

if (
    !$mismoOrigen
    || !in_array($tipo, ['whatsapp', 'telefono'], true)
    || !is_string($pagina)
    || strlen($pagina) > 190
    || !preg_match('#^/[a-zA-Z0-9/_.\-]*$#', $pagina)
    || $agente === ''
    || preg_match('/bot|crawl|spider|slurp|headless|lighthouse/i', $agente)
) {
    exit;
}

$pueblo = null;
if (preg_match('#^/vino-a-domicilio/([a-z0-9\-]+)/?$#', $pagina, $m)) {
    $poblacion = getPoblacionReparto($m[1]);
    $pueblo = $poblacion ? $poblacion['nombre'] : null;
}

$stmt = mysqli_prepare(
    $bd,
    'INSERT INTO clics_contacto (fecha, tipo, pagina, pueblo, clics) VALUES (CURDATE(), ?, ?, ?, 1)
     ON DUPLICATE KEY UPDATE clics = clics + 1'
);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'sss', $tipo, $pagina, $pueblo);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
exit;
