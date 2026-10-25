<?php
/**
 * Helpers compartits pels textos de categoria (textos.php, texto.php) i les
 * relacions article <-> categoria (articulo.php).
 * Taules: categoria_textos, ww_articulo_relaciones (sql/migration_categoria_textos.sql).
 */

// El mateix slug que la web (includes/config.php), que l'admin no carrega.
if (!function_exists('createSlug')) {
    function createSlug($string) {
        $string = strtolower($string);
        $string = preg_replace('/[áàäâ]/u', 'a', $string);
        $string = preg_replace('/[éèëê]/u', 'e', $string);
        $string = preg_replace('/[íìïî]/u', 'i', $string);
        $string = preg_replace('/[óòöô]/u', 'o', $string);
        $string = preg_replace('/[úùüû]/u', 'u', $string);
        $string = preg_replace('/ñ/u', 'n', $string);
        $string = preg_replace('/ç/u', 'c', $string);
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }
}

/** Llistats de sector amb pàgina pública: id => [nom, url]. */
function wwSectorsLlistat() {
    return [
        8  => ['Vins', '/vinos'],
        14 => ['Caves i escumosos', '/cavas'],
        2  => ['Licors', '/licores'],
        21 => ['Cerveses', '/cervezas'],
        12 => ['Conserves', '/conservas'],
    ];
}

/**
 * Totes les pàgines de llistat que poden tenir text: sectors, tipus i
 * procedències amb productes actius. Cada fila: ambito, id_ref, nom, grup,
 * url, productes. Els tipus van a la secció on tenen més productes (com a la web).
 */
function wwPaginesLlistat($bd) {
    $rutesTipus = [8 => 'vinos', 14 => 'cavas', 2 => 'licores', 12 => 'conservas'];
    $pagines = [];

    foreach (wwSectorsLlistat() as $id => $s) {
        $pagines[] = ['ambito' => 'sector', 'id_ref' => $id, 'nom' => $s[0], 'grup' => 'Seccions', 'url' => $s[1], 'productes' => null];
    }

    $r = mysqli_query($bd, "SELECT tp.id, tp.nombre_es, p.idSector, COUNT(*) AS n
                            FROM productos p JOIN tipo_producto tp ON tp.id = p.idTipo
                            WHERE p.activo = 1 AND p.idSector IN (" . implode(',', array_keys($rutesTipus)) . ")
                            GROUP BY tp.id, tp.nombre_es, p.idSector
                            ORDER BY n DESC");
    $vist = [];
    while ($r && $row = mysqli_fetch_assoc($r)) {
        if (isset($vist[$row['id']])) continue;
        $vist[$row['id']] = true;
        $sector = (int) $row['idSector'];
        $pagines[] = [
            'ambito' => 'tipo', 'id_ref' => (int) $row['id'], 'nom' => $row['nombre_es'],
            'grup' => 'Tipus · ' . wwSectorsLlistat()[$sector][0],
            'url' => '/' . $rutesTipus[$sector] . '/tipo/' . $row['id'] . '/' . createSlug($row['nombre_es']),
            'productes' => (int) $row['n'],
        ];
    }

    $r = mysqli_query($bd, "SELECT pr.id, pr.nombre_es, SUM(p.idSector = 21) AS cerveses, COUNT(*) AS n
                            FROM productos p JOIN procedencia pr ON pr.id = p.idProcedencia
                            WHERE p.activo = 1 AND p.idSector IN (8, 21)
                            GROUP BY pr.id, pr.nombre_es
                            ORDER BY n DESC");
    while ($r && $row = mysqli_fetch_assoc($r)) {
        $cervesa = $row['cerveses'] * 2 > $row['n'];
        $pagines[] = [
            'ambito' => 'procedencia', 'id_ref' => (int) $row['id'], 'nom' => $row['nombre_es'],
            'grup' => $cervesa ? 'Origen de cervesa' : 'D.O. de vins',
            'url' => ($cervesa ? '/cervezas/origen/' : '/vinos/do/') . $row['id'] . '/' . createSlug($row['nombre_es']),
            'productes' => (int) $row['n'],
        ];
    }
    return $pagines;
}

/** FAQ en JSON -> text editable: "P: ...\nR: ...", separades per una línia en blanc. */
function wwFaqsAText($json) {
    $faqs = json_decode((string) $json, true);
    if (!is_array($faqs)) return '';
    $blocs = [];
    foreach ($faqs as $f) {
        $blocs[] = 'P: ' . trim($f['pregunta'] ?? '') . "\nR: " . trim($f['respuesta'] ?? '');
    }
    return implode("\n\n", $blocs);
}

/** Text editable -> JSON. Cada pregunta comença per "P:" i la resposta per "R:". */
function wwTextAFaqs($text) {
    $faqs = [];
    $actual = null;
    foreach (preg_split('/\r?\n/', (string) $text) as $linia) {
        $linia = trim($linia);
        if (preg_match('/^P\s*:\s*(.+)$/u', $linia, $m)) {
            if ($actual && $actual['respuesta'] !== '') $faqs[] = $actual;
            $actual = ['pregunta' => $m[1], 'respuesta' => ''];
        } elseif (preg_match('/^R\s*:\s*(.*)$/u', $linia, $m) && $actual) {
            $actual['respuesta'] = trim($m[1]);
        } elseif ($linia !== '' && $actual && $actual['respuesta'] !== '') {
            $actual['respuesta'] .= ' ' . $linia;
        }
    }
    if ($actual && $actual['respuesta'] !== '') $faqs[] = $actual;
    return $faqs ? json_encode($faqs, JSON_UNESCAPED_UNICODE) : null;
}
