<?php
/**
 * WineWorld - Control SEO y Estrategia de Contenidos
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

// --- AJAX: Cambiar estado de artículo SEO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cambiar_estado_seo') {
    header('Content-Type: application/json');
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido']);
        exit;
    }
    $codigo = mysqli_real_escape_string($bd, $_POST['codigo'] ?? '');
    $estado = $_POST['estado'] ?? '';
    if (!in_array($estado, ['publicado', 'planificado', 'idea'])) {
        echo json_encode(['ok' => false, 'error' => 'Estado inválido']);
        exit;
    }
    $ok = mysqli_query($bd, "UPDATE ww_seo_plan SET estado = '$estado' WHERE codigo = '$codigo'");
    if ($ok && mysqli_affected_rows($bd) >= 0) {
        logActivity('seo_plan', "Estado de $codigo cambiado a $estado");
        echo json_encode(['ok' => true, 'codigo' => $codigo, 'estado' => $estado]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Error al actualizar']);
    }
    exit;
}

$pageTitle = 'WineWorld - Estrategia SEO';

// Obtener estados del plan SEO
$seoPlan = [];
$resSeo = mysqli_query($bd, "SELECT codigo, estado FROM ww_seo_plan");
if ($resSeo) {
    while ($row = mysqli_fetch_assoc($resSeo)) {
        $seoPlan[$row['codigo']] = $row['estado'];
    }
}

// Obtener datos actuales
$articulos = [];
$res = mysqli_query($bd, "SELECT a.*, c.nombre as categoria_nombre, c.slug as categoria_slug
    FROM ww_articulos a
    LEFT JOIN ww_categorias c ON a.id_categoria = c.id
    ORDER BY a.fecha_publicacion DESC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $articulos[] = $row;
    }
}

$categorias = [];
$resCat = mysqli_query($bd, "SELECT c.*, (SELECT COUNT(*) FROM ww_articulos WHERE id_categoria = c.id AND publicado = 1) as num_publicados FROM ww_categorias c ORDER BY c.orden");
if ($resCat) {
    while ($row = mysqli_fetch_assoc($resCat)) {
        $categorias[] = $row;
    }
}

// Helper para renderizar el selector de estado
function seoEstadoSelect($codigo, $estadoActual, $csrfToken) {
    $opciones = ['publicado' => 'Publicado', 'planificado' => 'Planificado', 'idea' => 'Idea'];
    $clases = ['publicado' => 'seo-status-done', 'planificado' => 'seo-status-planned', 'idea' => 'seo-status-idea'];
    $html = '<select class="seo-estado-select ' . $clases[$estadoActual] . '" data-codigo="' . htmlspecialchars($codigo) . '" data-csrf="' . htmlspecialchars($csrfToken) . '">';
    foreach ($opciones as $val => $label) {
        $sel = ($val === $estadoActual) ? ' selected' : '';
        $html .= '<option value="' . $val . '"' . $sel . '>' . $label . '</option>';
    }
    $html .= '</select>';
    return $html;
}

$csrfToken = generateCsrfToken();

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<style>
.seo-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.seo-kw { display: inline-block; background: #f0f0f0; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.8rem; margin: 0.15rem; }
.seo-kw-main { background: #722F37; color: white; }
.seo-kw-long { background: #C9A962; color: white; }
.seo-kw-opp { background: #2c3e50; color: white; }
.seo-status { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
.seo-status-done { background: #d1fae5; color: #065f46; }
.seo-status-planned { background: #e0e7ff; color: #3730a3; }
.seo-status-idea { background: #f3f4f6; color: #6b7280; }
.seo-table td { vertical-align: top; padding: 0.75rem; }
.seo-table th { text-align: left; padding: 0.75rem; background: var(--bg-secondary); }
.seo-section { margin-bottom: 2rem; }
.seo-arrow { color: #722F37; font-weight: bold; }
.seo-note { background: #fffbeb; border-left: 4px solid #C9A962; padding: 1rem; border-radius: 0 8px 8px 0; margin: 1rem 0; font-size: 0.9rem; }
.seo-danger { background: #fef2f2; border-left: 4px solid #ef4444; padding: 1rem; border-radius: 0 8px 8px 0; margin: 1rem 0; font-size: 0.9rem; }
.seo-legend { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
.seo-estado-select { padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; border: 1px solid transparent; cursor: pointer; appearance: auto; }
.seo-estado-select.seo-status-done { background: #d1fae5; color: #065f46; }
.seo-estado-select.seo-status-planned { background: #e0e7ff; color: #3730a3; }
.seo-estado-select.seo-status-idea { background: #f3f4f6; color: #6b7280; }
.seo-estado-select.saving { opacity: 0.5; pointer-events: none; }
</style>

<div class="content-header">
    <div>
        <h2>Estrategia SEO - WineWorld</h2>
        <p>Control de keywords, canibalización e interlinking</p>
    </div>
    <div style="display:flex;gap:0.5rem;">
        <a href="<?php echo getAdminUrl('modules/wineworld/'); ?>" class="btn btn-secondary">← WineWorld</a>
    </div>
</div>

<!-- Leyenda -->
<div class="seo-legend">
    <span class="seo-kw seo-kw-main">Keyword principal</span>
    <span class="seo-kw seo-kw-long">Long-tail secundaria</span>
    <span class="seo-kw seo-kw-opp">Oportunidad nuevo artículo</span>
    <span class="seo-status seo-status-done">Publicado</span>
    <span class="seo-status seo-status-planned">Planificado</span>
    <span class="seo-status seo-status-idea">Idea</span>
</div>

<!-- ============================================================ -->
<!-- ARTÍCULO 1: Guía Regalar Vino -->
<!-- ============================================================ -->

<div class="card seo-section">
    <div class="card-header">
        <h3>ART-01: Guía Definitiva para Regalar Vino, Cava y Licores</h3>
        <?php echo seoEstadoSelect('ART-01', $seoPlan['ART-01'] ?? 'publicado', $csrfToken); ?>
    </div>
    <div class="card-body">
        <p><strong>URL:</strong> <a href="/wineworld/guias-de-vino/guia-definitiva-regalar-vino-cava-licores" target="_blank"><code>/wineworld/guias-de-vino/guia-definitiva-regalar-vino-cava-licores</code></a></p>
        <p><strong>Categoría:</strong> Guías de Vino</p>
        <p><strong>Rol SEO:</strong> Pillar page (página pilar). Artículo paraguas que enlaza a artículos satélite más específicos.</p>

        <h4 style="margin-top:1.5rem;">Keywords principales que ataca este artículo</h4>
        <p>
            <span class="seo-kw seo-kw-main">regalar vino</span>
            <span class="seo-kw seo-kw-main">vino para regalar</span>
            <span class="seo-kw seo-kw-main">mejor vino para regalar</span>
            <span class="seo-kw seo-kw-main">qué vino regalar</span>
            <span class="seo-kw seo-kw-main">guía para regalar vino</span>
            <span class="seo-kw seo-kw-main">cómo elegir un vino para regalar</span>
        </p>

        <h4 style="margin-top:1rem;">Long-tails secundarias cubiertas</h4>
        <p>
            <span class="seo-kw seo-kw-long">vino para regalar a un hombre</span>
            <span class="seo-kw seo-kw-long">vino para regalar a una mujer</span>
            <span class="seo-kw seo-kw-long">vino para regalar a los suegros</span>
            <span class="seo-kw seo-kw-long">vino para primera cita</span>
            <span class="seo-kw seo-kw-long">vino para regalar en Navidad</span>
            <span class="seo-kw seo-kw-long">champagne para regalar</span>
            <span class="seo-kw seo-kw-long">whisky para regalar</span>
            <span class="seo-kw seo-kw-long">licores para regalar</span>
            <span class="seo-kw seo-kw-long">vino bueno para regalar</span>
            <span class="seo-kw seo-kw-long">vino para regalar cumpleaños</span>
            <span class="seo-kw seo-kw-long">vino para regalar San Valentín</span>
            <span class="seo-kw seo-kw-long">vino para regalar aniversario</span>
            <span class="seo-kw seo-kw-long">vino para regalar boda</span>
            <span class="seo-kw seo-kw-long">ron para regalar</span>
            <span class="seo-kw seo-kw-long">ginebra para regalar</span>
            <span class="seo-kw seo-kw-long">brandy cognac para regalar</span>
            <span class="seo-kw seo-kw-long">tequila para regalar</span>
        </p>

        <div class="seo-danger">
            <strong>⚠ Riesgo de canibalización:</strong> Este artículo cubre muchos temas de forma amplia. Los artículos satélite deben profundizar en un ángulo concreto y NO competir por las keywords genéricas (<em>regalar vino</em>, <em>mejor vino para regalar</em>). Cada artículo nuevo debe tener su propio foco semántico claro.
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- OPORTUNIDADES DE ARTÍCULOS SATÉLITE -->
<!-- ============================================================ -->

<div class="card seo-section">
    <div class="card-header">
        <h3>Oportunidades de artículos satélite (sin canibalización)</h3>
    </div>
    <div class="card-body">

        <div class="seo-note">
            <strong>Estrategia Topic Cluster:</strong> El ART-01 es la pillar page. Cada artículo satélite profundiza en un tema concreto y enlaza de vuelta al pilar. El pilar enlaza a cada satélite. Así creamos autoridad temática sin competir entre nosotros.
        </div>

        <table class="data-table seo-table" style="width:100%">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Título propuesto</th>
                    <th>Keyword principal</th>
                    <th>Categoría</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>

                <!-- PRIORIDAD ALTA: Estacionales + Alto volumen -->

                <tr>
                    <td><strong>02</strong></td>
                    <td>
                        <strong>Los Mejores Vinos para Regalar en Navidad</strong>
                        <br><small style="color:var(--text-muted)">Enfoque estacional. Lotes, cestas, cenas navideñas, amigo invisible. Actualizar cada año con la fecha.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">vino para regalar en Navidad</span>
                        <span class="seo-kw seo-kw-opp">mejores vinos Navidad</span>
                        <span class="seo-kw seo-kw-opp">cestas navideñas vino</span>
                        <span class="seo-kw seo-kw-opp">amigo invisible vino</span>
                        <span class="seo-kw seo-kw-opp">lotes de Navidad</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">vino para regalar en Navidad</span></td>
                    <td>Guías de Vino</td>
                    <td>🔴 Alta</td>
                    <td><?php echo seoEstadoSelect('ART-02', $seoPlan['ART-02'] ?? 'planificado', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>03</strong></td>
                    <td>
                        <strong>Guía de Champagne: Tipos, Marcas y Cuál Elegir</strong>
                        <br><small style="color:var(--text-muted)">Todo sobre champagne: brut, rosé, blanc de blancs, las grandes maisons. Sin enfocar en "regalar" sino en conocimiento.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">tipos de champagne</span>
                        <span class="seo-kw seo-kw-opp">mejor champagne</span>
                        <span class="seo-kw seo-kw-opp">champagne brut vs seco</span>
                        <span class="seo-kw seo-kw-opp">marcas de champagne</span>
                        <span class="seo-kw seo-kw-opp">diferencia cava champagne</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">tipos de champagne</span></td>
                    <td>Guías de Vino</td>
                    <td>🔴 Alta</td>
                    <td><?php echo seoEstadoSelect('ART-03', $seoPlan['ART-03'] ?? 'planificado', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>04</strong></td>
                    <td>
                        <strong>Guía de Whisky: Single Malt, Blended y Cómo Elegir</strong>
                        <br><small style="color:var(--text-muted)">Guía educativa sobre whisky: regiones escocesas, japonés, irlandés, bourbon. No compite con ART-01 porque el foco no es "regalar" sino "entender".</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">tipos de whisky</span>
                        <span class="seo-kw seo-kw-opp">mejor whisky single malt</span>
                        <span class="seo-kw seo-kw-opp">whisky escocés vs irlandés</span>
                        <span class="seo-kw seo-kw-opp">whisky japonés</span>
                        <span class="seo-kw seo-kw-opp">cómo catar whisky</span>
                        <br><small style="color:#065f46"><strong>URL:</strong> <a href="/wineworld/destilados/guia-whisky-single-malt-blended-como-elegir" target="_blank">/wineworld/destilados/guia-whisky-single-malt-blended-como-elegir</a></small>
                    </td>
                    <td><span class="seo-kw seo-kw-main">tipos de whisky</span></td>
                    <td>Destilados</td>
                    <td>🔴 Alta</td>
                    <td><?php echo seoEstadoSelect('ART-04', $seoPlan['ART-04'] ?? 'publicado', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>05</strong></td>
                    <td>
                        <strong>Vinos del Priorat: La Guía Completa</strong>
                        <br><small style="color:var(--text-muted)">D.O.Ca Priorat en profundidad: terroir, bodegas, garnachas viejas, cariñenas. Enlace fuerte a productos Priorat del catálogo.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">vinos Priorat</span>
                        <span class="seo-kw seo-kw-opp">mejores vinos Priorat</span>
                        <span class="seo-kw seo-kw-opp">bodegas Priorat</span>
                        <span class="seo-kw seo-kw-opp">D.O. Priorat</span>
                        <span class="seo-kw seo-kw-opp">Álvaro Palacios Priorat</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">vinos Priorat</span></td>
                    <td>Guías de Vino</td>
                    <td>🔴 Alta</td>
                    <td><?php echo seoEstadoSelect('ART-05', $seoPlan['ART-05'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <!-- PRIORIDAD MEDIA: Buenos volúmenes, evergreen -->

                <tr>
                    <td><strong>06</strong></td>
                    <td>
                        <strong>Diferencias entre Crianza, Reserva y Gran Reserva</strong>
                        <br><small style="color:var(--text-muted)">Artículo educativo muy buscado. Explica envejecimiento, barrica vs botella, cómo leer etiquetas. Alto potencial de tráfico informacional.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">diferencia crianza reserva gran reserva</span>
                        <span class="seo-kw seo-kw-opp">qué es un vino crianza</span>
                        <span class="seo-kw seo-kw-opp">qué significa reserva en vino</span>
                        <span class="seo-kw seo-kw-opp">clasificación vinos españoles</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">diferencia crianza reserva gran reserva</span></td>
                    <td>Guías de Vino</td>
                    <td>🟡 Media</td>
                    <td><?php echo seoEstadoSelect('ART-06', $seoPlan['ART-06'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>07</strong></td>
                    <td>
                        <strong>Guía de Maridaje: Qué Vino con Cada Comida</strong>
                        <br><small style="color:var(--text-muted)">Maridaje por tipo de plato: carnes, pescados, quesos, postres, cocina asiática, sushi... ART-01 lo menciona de pasada, este profundiza.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">maridaje vino y comida</span>
                        <span class="seo-kw seo-kw-opp">qué vino con carne</span>
                        <span class="seo-kw seo-kw-opp">qué vino con pescado</span>
                        <span class="seo-kw seo-kw-opp">qué vino con queso</span>
                        <span class="seo-kw seo-kw-opp">maridaje vino tinto</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">maridaje vino y comida</span></td>
                    <td>Maridajes</td>
                    <td>🟡 Media</td>
                    <td><?php echo seoEstadoSelect('ART-07', $seoPlan['ART-07'] ?? 'planificado', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>08</strong></td>
                    <td>
                        <strong>Cava vs Champagne: Diferencias, Similitudes y Cuál Elegir</strong>
                        <br><small style="color:var(--text-muted)">Comparativa directa. Método tradicional, terroir, precios, cuándo elegir cada uno. Muy buscado, intención informacional pura.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">diferencia cava champagne</span>
                        <span class="seo-kw seo-kw-opp">cava o champagne</span>
                        <span class="seo-kw seo-kw-opp">qué es mejor cava o champagne</span>
                        <span class="seo-kw seo-kw-opp">método champenoise</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">diferencia cava champagne</span></td>
                    <td>Guías de Vino</td>
                    <td>🟡 Media</td>
                    <td><?php echo seoEstadoSelect('ART-08', $seoPlan['ART-08'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>09</strong></td>
                    <td>
                        <strong>Las Mejores Denominaciones de Origen de España</strong>
                        <br><small style="color:var(--text-muted)">Mapa de D.O. españolas: Rioja, Ribera, Priorat, Rueda, Rías Baixas, Toro, Montsant... Con enlaces a cada D.O. del catálogo.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">denominaciones de origen España</span>
                        <span class="seo-kw seo-kw-opp">mejores D.O. vino España</span>
                        <span class="seo-kw seo-kw-opp">D.O. vinos españoles</span>
                        <span class="seo-kw seo-kw-opp">mapa vinos España</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">denominaciones de origen España</span></td>
                    <td>Cultura y Tradición</td>
                    <td>🟡 Media</td>
                    <td><?php echo seoEstadoSelect('ART-09', $seoPlan['ART-09'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>10</strong></td>
                    <td>
                        <strong>Cómo Catar Vino como un Profesional</strong>
                        <br><small style="color:var(--text-muted)">Guía práctica: vista, nariz, boca. Vocabulario de cata. Temperatura de servicio. Copas adecuadas. Contenido educativo de alta permanencia.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">cómo catar vino</span>
                        <span class="seo-kw seo-kw-opp">cata de vinos pasos</span>
                        <span class="seo-kw seo-kw-opp">vocabulario cata de vinos</span>
                        <span class="seo-kw seo-kw-opp">temperatura servir vino</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">cómo catar vino</span></td>
                    <td>Guías de Vino</td>
                    <td>🟡 Media</td>
                    <td><?php echo seoEstadoSelect('ART-10', $seoPlan['ART-10'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>11</strong></td>
                    <td>
                        <strong>Guía de Ron Premium: Tipos, Marcas y los Mejores para Disfrutar</strong>
                        <br><small style="color:var(--text-muted)">Ron solera, agrícola, añejo. Regiones (Caribe, Guatemala, Barbados). ART-01 toca ron solo para regalo, aquí se profundiza en el producto.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">tipos de ron</span>
                        <span class="seo-kw seo-kw-opp">mejor ron premium</span>
                        <span class="seo-kw seo-kw-opp">ron añejo</span>
                        <span class="seo-kw seo-kw-opp">ron Zacapa</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">tipos de ron</span></td>
                    <td>Destilados</td>
                    <td>🟡 Media</td>
                    <td><?php echo seoEstadoSelect('ART-11', $seoPlan['ART-11'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>12</strong></td>
                    <td>
                        <strong>Vinos de Rioja: Guía Completa de la D.O. más Famosa de España</strong>
                        <br><small style="color:var(--text-muted)">Historia, subzonas (Alavesa, Alta, Oriental), uvas, bodegas emblemáticas, clasificaciones. Alto valor SEO local.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">vinos de Rioja</span>
                        <span class="seo-kw seo-kw-opp">mejores vinos Rioja</span>
                        <span class="seo-kw seo-kw-opp">D.O. Rioja guía</span>
                        <span class="seo-kw seo-kw-opp">bodegas Rioja</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">vinos de Rioja</span></td>
                    <td>Cultura y Tradición</td>
                    <td>🟡 Media</td>
                    <td><?php echo seoEstadoSelect('ART-12', $seoPlan['ART-12'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <!-- PRIORIDAD BAJA: Nicho, bajo volumen pero buena conversión -->

                <tr>
                    <td><strong>13</strong></td>
                    <td>
                        <strong>Vino para San Valentín: Ideas Románticas con Vino y Champagne</strong>
                        <br><small style="color:var(--text-muted)">Estacional (Enero-Febrero). Rosados, champagne rosé, cenas románticas, experiencias. Publicar en enero.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">vino San Valentín</span>
                        <span class="seo-kw seo-kw-opp">champagne romántico</span>
                        <span class="seo-kw seo-kw-opp">cena romántica vino</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">vino San Valentín</span></td>
                    <td>Guías de Vino</td>
                    <td>🟢 Baja</td>
                    <td><?php echo seoEstadoSelect('ART-13', $seoPlan['ART-13'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>14</strong></td>
                    <td>
                        <strong>Guía del Gin Tonic Perfecto: Ginebras, Tónicas y Botánicos</strong>
                        <br><small style="color:var(--text-muted)">Guía práctica sobre gin-tonics. Tipos de ginebra, tónicas recomendadas, proporciones, garnish. Buen engagement.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">mejor ginebra para gin tonic</span>
                        <span class="seo-kw seo-kw-opp">cómo hacer gin tonic perfecto</span>
                        <span class="seo-kw seo-kw-opp">tipos de ginebra</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">gin tonic perfecto</span></td>
                    <td>Destilados</td>
                    <td>🟢 Baja</td>
                    <td><?php echo seoEstadoSelect('ART-14', $seoPlan['ART-14'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>15</strong></td>
                    <td>
                        <strong>Cómo Conservar el Vino en Casa: Temperatura, Luz y Posición</strong>
                        <br><small style="color:var(--text-muted)">Guía práctica. Vinotecas, temperatura, humedad, cuánto dura abierto, errores comunes. Tráfico informacional constante.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">cómo conservar vino en casa</span>
                        <span class="seo-kw seo-kw-opp">temperatura vino</span>
                        <span class="seo-kw seo-kw-opp">cuánto dura una botella abierta</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">cómo conservar vino en casa</span></td>
                    <td>Guías de Vino</td>
                    <td>🟢 Baja</td>
                    <td><?php echo seoEstadoSelect('ART-15', $seoPlan['ART-15'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>16</strong></td>
                    <td>
                        <strong>Vinos de Ribera del Duero: Guía para Conocer esta D.O.</strong>
                        <br><small style="color:var(--text-muted)">Tempranillo, Tinta del País, bodegas emblemáticas, diferencias con Rioja. Enlace a productos Ribera del catálogo.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">vinos Ribera del Duero</span>
                        <span class="seo-kw seo-kw-opp">mejores vinos Ribera del Duero</span>
                        <span class="seo-kw seo-kw-opp">bodegas Ribera del Duero</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">vinos Ribera del Duero</span></td>
                    <td>Cultura y Tradición</td>
                    <td>🟢 Baja</td>
                    <td><?php echo seoEstadoSelect('ART-16', $seoPlan['ART-16'] ?? 'idea', $csrfToken); ?></td>
                </tr>

                <tr>
                    <td><strong>17</strong></td>
                    <td>
                        <strong>Tequila Premium: De México a Tu Copa</strong>
                        <br><small style="color:var(--text-muted)">Tipos (blanco, reposado, añejo, extra añejo), regiones, 100% agave, cómo beber tequila. Don Julio, Patrón, Clase Azul.</small>
                        <br>
                        <span class="seo-kw seo-kw-opp">tipos de tequila</span>
                        <span class="seo-kw seo-kw-opp">mejor tequila premium</span>
                        <span class="seo-kw seo-kw-opp">Don Julio 1942</span>
                    </td>
                    <td><span class="seo-kw seo-kw-main">tipos de tequila</span></td>
                    <td>Destilados</td>
                    <td>🟢 Baja</td>
                    <td><?php echo seoEstadoSelect('ART-17', $seoPlan['ART-17'] ?? 'idea', $csrfToken); ?></td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

<!-- ============================================================ -->
<!-- MAPA DE INTERLINKING -->
<!-- ============================================================ -->

<div class="card seo-section">
    <div class="card-header">
        <h3>Mapa de Interlinking</h3>
    </div>
    <div class="card-body">

        <div class="seo-note">
            <strong>Regla de oro:</strong> Cada artículo satélite enlaza al pilar (ART-01) y el pilar enlaza de vuelta al satélite. Los satélites pueden enlazarse entre sí cuando sea relevante. Todos enlazan a páginas de producto y categoría.
        </div>

        <pre style="background:var(--bg-secondary);padding:1.5rem;border-radius:8px;font-size:0.85rem;line-height:1.8;overflow-x:auto;">
<strong>ART-01</strong> Guía Regalar Vino (PILAR)
  │
  ├──→ <strong>ART-02</strong> Vinos Navidad          ←──→ ART-07 Maridajes
  ├──→ <strong>ART-03</strong> Guía Champagne         ←──→ ART-08 Cava vs Champagne
  ├──→ <strong>ART-04</strong> Guía Whisky ✅         ←──→ ART-11 Guía Ron, ART-DP Día del Padre
  ├──→ <strong>ART-05</strong> Vinos Priorat          ←──→ ART-09 D.O. España
  ├──→ <strong>ART-06</strong> Crianza/Reserva/Gran R ←──→ ART-10 Cómo Catar
  ├──→ <strong>ART-07</strong> Maridajes              ←──→ ART-02 Navidad
  ├──→ <strong>ART-08</strong> Cava vs Champagne      ←──→ ART-03 Guía Champagne
  ├──→ <strong>ART-09</strong> D.O. España            ←──→ ART-05 Priorat, ART-12 Rioja
  ├──→ <strong>ART-13</strong> San Valentín           ←──→ ART-03 Champagne
  ├──→ <strong>ART-14</strong> Gin Tonic              ←──→ ART-04 Whisky, ART-11 Ron
  └──→ <strong>ART-DP</strong> Día del Padre ✅       ←──→ ART-04 Whisky

<strong>Interlinking implementado:</strong>
  ART-01 → ART-04 (sección whisky + FAQ + sección día del padre)
  ART-04 → ART-01 (callout regalos + artículos relacionados)
  ART-04 → ART-DP (artículos relacionados)
  ART-DP → ART-04 (sección destilados + según lo que bebe)
  ART-DP → ART-01 (múltiples enlaces existentes)

Todos los artículos enlazan a:
  ├──→ /vinos, /cavas, /licores (categorías)
  ├──→ /vinos/do/{id}/{slug} (D.O. relevantes)
  ├──→ /vinos/tipo/{id}/{slug} (tipos de vino)
  └──→ /producto/{id}/{slug} (productos específicos)
        </pre>
    </div>
</div>

<!-- ============================================================ -->
<!-- REGLAS ANTI-CANIBALIZACIÓN -->
<!-- ============================================================ -->

<div class="card seo-section">
    <div class="card-header">
        <h3>Reglas Anti-Canibalización</h3>
    </div>
    <div class="card-body">
        <table class="data-table seo-table" style="width:100%">
            <thead>
                <tr>
                    <th>Regla</th>
                    <th>Ejemplo correcto</th>
                    <th>Ejemplo incorrecto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>1. Una keyword principal por artículo</strong><br><small>Cada artículo ataca un foco semántico distinto.</small></td>
                    <td style="color:#065f46">ART-01 → "regalar vino"<br>ART-03 → "tipos de champagne"</td>
                    <td style="color:#991b1b">ART-01 → "regalar vino"<br>ART-nuevo → "mejores vinos para regalar"<br><em>(mismo foco, canibaliza)</em></td>
                </tr>
                <tr>
                    <td><strong>2. Satélite = profundidad, no repetición</strong><br><small>El satélite profundiza donde el pilar solo roza.</small></td>
                    <td style="color:#065f46">ART-04 explica tipos de whisky, regiones, cata → ART-01 solo recomienda 4 whiskies para regalo</td>
                    <td style="color:#991b1b">ART-nuevo repite las mismas recomendaciones de whisky que ART-01 con título diferente</td>
                </tr>
                <tr>
                    <td><strong>3. Intent diferente</strong><br><small>Informacional vs transaccional vs comparativo.</small></td>
                    <td style="color:#065f46">ART-01: "qué regalar" (transaccional)<br>ART-10: "cómo catar" (informacional)</td>
                    <td style="color:#991b1b">Dos artículos con intent "qué vino comprar para X" → competirán entre sí</td>
                </tr>
                <tr>
                    <td><strong>4. Estacional ≠ Evergreen</strong><br><small>Artículos de temporada tienen su propio espacio.</small></td>
                    <td style="color:#065f46">ART-02: "Vinos Navidad 2026" (estacional, se actualiza)<br>ART-01: pilar evergreen</td>
                    <td style="color:#991b1b">Escribir "regalar vino en Navidad" sin diferenciar del pilar que ya tiene sección de Navidad</td>
                </tr>
                <tr>
                    <td><strong>5. Cuando el pilar ya cubre un tema, el satélite cambia el ángulo</strong></td>
                    <td style="color:#065f46">Pilar: "champagne para regalar" (4 recomendaciones)<br>ART-03: "Guía de Champagne" (historia, tipos, maisons, cata)</td>
                    <td style="color:#991b1b">ART-nuevo: "Los 10 mejores champagnes para regalar" → compite directamente con la sección del pilar</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================================ -->
<!-- CALENDARIO EDITORIAL SUGERIDO -->
<!-- ============================================================ -->

<div class="card seo-section">
    <div class="card-header">
        <h3>Calendario Editorial Sugerido</h3>
    </div>
    <div class="card-body">
        <div class="seo-note">
            <strong>Estrategia en 3 fases:</strong> Arranque intensivo (3/mes) para crear masa crítica, crucero (2/mes) para mantener ritmo, y mantenimiento (1 nuevo + 1 actualización) cuando los 16 satélites estén publicados.
        </div>

        <!-- Fase 1 -->
        <h4 style="margin:1.5rem 0 0.75rem;color:#722F37;">Fase 1 — Lanzamiento (3 artículos/mes)</h4>
        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Objetivo: llegar a 7-8 artículos rápido para que Google reconozca WineWorld como hub de contenido.</p>
        <table class="data-table seo-table" style="width:100%">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Semana 1</th>
                    <th>Semana 2-3</th>
                    <th>Semana 4</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Abril 2026</strong></td>
                    <td>ART-06: Crianza, Reserva y Gran Reserva</td>
                    <td>ART-10: Cómo Catar Vino</td>
                    <td>ART-07: Guía de Maridajes</td>
                    <td>3 evergreen educativos para crear base sólida de autoridad temática.</td>
                </tr>
                <tr>
                    <td><strong>Mayo 2026</strong></td>
                    <td>ART-15: Conservar Vino en Casa</td>
                    <td>ART-08: Cava vs Champagne</td>
                    <td>ART-14: Gin Tonic Perfecto</td>
                    <td>Contenido práctico verano + bodas/comuniones. Con esto ya tenemos 10 artículos.</td>
                </tr>
            </tbody>
        </table>

        <!-- Fase 2 -->
        <h4 style="margin:1.5rem 0 0.75rem;color:#C9A962;">Fase 2 — Crucero (2 artículos/mes)</h4>
        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Ritmo sostenible. Priorizar artículos según estacionalidad.</p>
        <table class="data-table seo-table" style="width:100%">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Semana 1-2</th>
                    <th>Semana 3-4</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Junio 2026</strong></td>
                    <td>ART-11: Guía de Ron Premium</td>
                    <td>ART-17: Tequila Premium</td>
                    <td>Destilados para verano, cócteles, terrazas.</td>
                </tr>
                <tr>
                    <td><strong>Sept 2026</strong></td>
                    <td>ART-05: Vinos del Priorat</td>
                    <td>ART-12: Vinos de Rioja</td>
                    <td>Vendimia, enoturismo. Posicionar D.O. de cara a otoño.</td>
                </tr>
                <tr>
                    <td><strong>Oct 2026</strong></td>
                    <td>ART-03: Guía de Champagne</td>
                    <td><del>ART-04: Guía de Whisky</del> <span class="seo-status seo-status-done">Publicado Abr 2026</span></td>
                    <td>Pre-Navidad: posicionar 2 meses antes del pico de búsquedas. ART-04 ya publicado anticipadamente.</td>
                </tr>
                <tr>
                    <td><strong>Nov 2026</strong></td>
                    <td>ART-02: Vinos para Navidad 2026</td>
                    <td>ART-09: D.O. de España</td>
                    <td>Pico navideño + mapa D.O. para interlinking masivo.</td>
                </tr>
            </tbody>
        </table>

        <!-- Fase 3 -->
        <h4 style="margin:1.5rem 0 0.75rem;color:#2c3e50;">Fase 3 — Mantenimiento (1 nuevo + 1 actualización/mes)</h4>
        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Los 16 satélites ya están publicados. Foco en refrescar contenido existente y publicar nuevos temas.</p>
        <table class="data-table seo-table" style="width:100%">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Artículo nuevo</th>
                    <th>Actualización</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Ene 2027</strong></td>
                    <td>ART-13: Vino San Valentín</td>
                    <td>ART-01: Actualizar pilar con enlaces a todos los satélites</td>
                    <td>Pre-San Valentín + consolidar interlinking del pilar.</td>
                </tr>
                <tr>
                    <td><strong>Feb 2027</strong></td>
                    <td>ART-16: Vinos Ribera del Duero</td>
                    <td>ART-02: Refrescar Navidad → "Navidad 2027"</td>
                    <td>Último satélite + preparar contenido navideño para siguiente año.</td>
                </tr>
                <tr>
                    <td><strong>Mar 2027+</strong></td>
                    <td>Nuevos temas (vinos naturales, vermut, sake...)</td>
                    <td>Rotar actualizaciones de artículos existentes</td>
                    <td>Google premia contenido actualizado tanto como el nuevo.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Estado actual de artículos publicados -->
<div class="card seo-section">
    <div class="card-header">
        <h3>Artículos publicados actualmente</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($articulos)): ?>
        <table class="data-table seo-table" style="width:100%">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>URL</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articulos as $art): ?>
                <tr>
                    <td><strong><?php echo e($art['titulo']); ?></strong></td>
                    <td><?php echo e($art['categoria_nombre']); ?></td>
                    <td><a href="/wineworld/<?php echo e($art['categoria_slug']); ?>/<?php echo e($art['slug']); ?>" target="_blank"><code>/wineworld/.../<?php echo e($art['slug']); ?></code></a></td>
                    <td><?php echo $art['fecha_publicacion'] ? date('d/m/Y', strtotime($art['fecha_publicacion'])) : '-'; ?></td>
                    <td><span class="seo-status <?php echo $art['publicado'] ? 'seo-status-done' : 'seo-status-idea'; ?>"><?php echo $art['publicado'] ? 'Publicado' : 'Borrador'; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:var(--text-muted)">No hay artículos publicados todavía.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.seo-estado-select').forEach(function(select) {
    select.addEventListener('change', function() {
        var el = this;
        var codigo = el.dataset.codigo;
        var estado = el.value;
        var csrf = el.dataset.csrf;

        // Actualizar clase visual inmediatamente
        el.className = 'seo-estado-select saving ' +
            (estado === 'publicado' ? 'seo-status-done' :
             estado === 'planificado' ? 'seo-status-planned' : 'seo-status-idea');

        var formData = new FormData();
        formData.append('action', 'cambiar_estado_seo');
        formData.append('codigo', codigo);
        formData.append('estado', estado);
        formData.append('csrf_token', csrf);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            el.classList.remove('saving');
            if (!data.ok) {
                alert('Error: ' + (data.error || 'No se pudo guardar'));
                location.reload();
            }
        })
        .catch(function() {
            el.classList.remove('saving');
            alert('Error de conexión');
            location.reload();
        });
    });
});
</script>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
