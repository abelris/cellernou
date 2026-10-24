<?php
/**
 * Cost de compra dels productes.
 *
 * productos.precio_medio és el cost de referència del producte (sense IVA):
 * el preu que proposa la línia de compra, el cost que graven els tiquets i
 * la base dels marges de l'inventari. Fins ara només canviava a mà o des de
 * Komkal (el càlcul de compres/alta.php estava comentat), així que quedava
 * desfasat. Ara es recalcula cada vegada que entra una compra:
 *
 *   mitjana ponderada (per quantitat) de les compres dels darrers 12 mesos;
 *   si no n'hi ha, el preu de la darrera compra.
 *
 * Si el producte no té cap compra, es manté el valor que s'hi hagi posat a mà.
 *
 * La mateixa regla, a una data passada, és la que fa servir
 * modules/bi/costos.php per corregir les vendes antigues.
 */

/** Mesos de compres que entren a la mitjana. */
define('COST_MESOS_MITJANA', 12);

/**
 * Preus de compra fora de mida respecte a la mediana del producte (×5 o ÷5).
 * N'hi ha d'errors d'introducció: un Coto Crianza a 3.983 € en lloc de 3,98.
 */
define('COST_FACTOR_ANOMAL', 5);

/**
 * Compres d'un producte: [[timestamp, quantitat, preu], ...] per data.
 */
function compresProducte($bd, int $idProducto): array {
    $compres = [];
    $r = mysqli_query($bd, "SELECT UNIX_TIMESTAMP(fecha) AS ts, cantidad, precio_unidad
                            FROM linea_compra
                            WHERE idProducto = $idProducto AND precio_unidad > 0 AND cantidad > 0
                              AND fecha > '2000-01-01'
                            ORDER BY fecha, id");
    while ($r && ($row = mysqli_fetch_assoc($r))) {
        $compres[] = [(int)$row['ts'], (float)$row['cantidad'], (float)$row['precio_unidad']];
    }
    return $compres;
}

/** Treure les compres amb un preu anòmal (vegeu COST_FACTOR_ANOMAL). */
function filtrarCompresAnomales(array $compres): array {
    if (count($compres) < 3) return $compres;
    $preus = array_column($compres, 2);
    sort($preus);
    $n = count($preus);
    $mediana = $n % 2 ? $preus[intdiv($n, 2)] : ($preus[$n / 2 - 1] + $preus[$n / 2]) / 2;
    return array_values(array_filter($compres, function ($c) use ($mediana) {
        return $c[2] <= $mediana * COST_FACTOR_ANOMAL && $c[2] >= $mediana / COST_FACTOR_ANOMAL;
    }));
}

/**
 * Cost mitjà a una data a partir d'una llista de compres (ja filtrada).
 *
 * Mitjana ponderada dels 12 mesos anteriors; si no n'hi ha, la darrera compra
 * anterior; si tampoc, la primera posterior (millor que deixar-lo a 0).
 * Torna 0 si no hi ha cap compra.
 */
function costMitjaDeCompres(array $compres, int $ts): float {
    $desde = strtotime('-' . COST_MESOS_MITJANA . ' months', $ts);
    $q = 0; $import = 0; $darrera = null; $primeraDespres = null;
    foreach ($compres as [$tsC, $cant, $preu]) {
        if ($tsC <= $ts) {
            $darrera = $preu;
            if ($tsC > $desde) { $q += $cant; $import += $cant * $preu; }
        } elseif ($primeraDespres === null) {
            $primeraDespres = $preu;
        }
    }
    if ($q > 0) return $import / $q;
    return $darrera ?? $primeraDespres ?? 0;
}

/** Cost mitjà de compra d'un producte avui. 0 si no té compres. */
function costMitjaCompra($bd, int $idProducto): float {
    return costMitjaDeCompres(filtrarCompresAnomales(compresProducte($bd, $idProducto)), time());
}

/**
 * Recalcular productos.precio_medio a partir de les compres.
 * Es crida després de desar una compra. No toca els productes sense compres.
 */
function actualitzarPreuMig($bd, int $idProducto): void {
    $cost = costMitjaCompra($bd, $idProducto);
    if ($cost > 0) {
        mysqli_query($bd, "UPDATE productos SET precio_medio = " . round($cost, 4) . " WHERE id = $idProducto");
    }
}

/**
 * Cost que s'ha de gravar en una línia de venda (tiquet).
 *
 * precio_medio si en té; si és 0, el calcula de les compres i de passada
 * l'actualitza. Torna 0 només si el producte no té cap cost conegut: la
 * pàgina que crida ho ha d'avisar.
 */
function costLiniaVenda($bd, int $idProducto): float {
    $r = mysqli_query($bd, "SELECT precio_medio FROM productos WHERE id = $idProducto");
    $cost = ($r && ($row = mysqli_fetch_assoc($r))) ? (float)$row['precio_medio'] : 0;
    if ($cost > 0) return $cost;

    $cost = costMitjaCompra($bd, $idProducto);
    if ($cost > 0) {
        mysqli_query($bd, "UPDATE productos SET precio_medio = " . round($cost, 4) . " WHERE id = $idProducto");
    }
    return $cost;
}
