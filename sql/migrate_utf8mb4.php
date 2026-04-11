<?php
/**
 * Migración de latin1 a utf8mb4
 *
 * Dos estrategias según el tipo de datos:
 *   A) Tablas antiguas con mojibake (UTF-8 bytes guardados como latin1):
 *      → CONVERT TO binary, luego CONVERT TO utf8mb4
 *   B) Tablas nuevas con datos correctos (insertados con conexión utf8):
 *      → CONVERT TO utf8mb4 directamente
 *
 * USO: php sql/migrate_utf8mb4.php [--dry-run]
 */
require __DIR__ . '/../distribucio/connexio/connexio.php';

// Desactivar strict mode para permitir fechas 0000-00-00
mysqli_query($bd, "SET SESSION sql_mode = ''");

$dryRun = in_array('--dry-run', $argv ?? []);

if ($dryRun) {
    echo "=== MODO DRY-RUN (no se ejecuta nada) ===\n\n";
}

// Tablas con datos CORRECTOS (insertados con conexión utf8) → conversión directa
$tablasLimpias = ['producto_textos', 'configuracion_web', 'admin_logs'];

// Obtener todas las tablas latin1
$tablas = [];
$r = mysqli_query($bd, "SELECT TABLE_NAME, TABLE_COLLATION, ENGINE
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = 'cellernodb'
    AND TABLE_COLLATION LIKE 'latin1%'
    ORDER BY TABLE_NAME");
while ($row = mysqli_fetch_assoc($r)) {
    $tablas[] = $row;
}

echo "Tablas latin1 a migrar: " . count($tablas) . "\n\n";

$ok = 0;
$errors = 0;

foreach ($tablas as $t) {
    $tabla = $t['TABLE_NAME'];
    $engine = $t['ENGINE'];

    if (in_array($tabla, $tablasLimpias)) {
        // Estrategia B: conversión directa
        echo "[$tabla] Conversión directa (datos limpios)...\n";
        $sql = "ALTER TABLE `$tabla` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        if ($dryRun) {
            echo "  SQL: $sql\n";
            $ok++;
        } else {
            if (mysqli_query($bd, $sql)) {
                echo "  OK\n";
                $ok++;
            } else {
                echo "  ERROR: " . mysqli_error($bd) . "\n";
                $errors++;
            }
        }
    } else {
        // Estrategia A: binario → utf8mb4 (para mojibake)
        echo "[$tabla] Conversión binaria (mojibake)...\n";

        $sql1 = "ALTER TABLE `$tabla` CONVERT TO CHARACTER SET binary";
        $sql2 = "ALTER TABLE `$tabla` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        if ($dryRun) {
            echo "  SQL1: $sql1\n";
            echo "  SQL2: $sql2\n";
            $ok++;
        } else {
            if (!mysqli_query($bd, $sql1)) {
                echo "  ERROR (paso 1): " . mysqli_error($bd) . "\n";
                $errors++;
                continue;
            }
            if (!mysqli_query($bd, $sql2)) {
                echo "  ERROR (paso 2): " . mysqli_error($bd) . "\n";
                $errors++;
                continue;
            }
            echo "  OK\n";
            $ok++;
        }
    }
}

// Actualizar default de la BD (por si acaso)
if (!$dryRun) {
    mysqli_query($bd, "ALTER DATABASE cellernodb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
}

echo "\n=== RESULTADO ===\n";
echo "Tablas procesadas: $ok OK, $errors errores\n";

if (!$dryRun && $errors === 0) {
    echo "\nMigración completada. Actualiza connexio.php para usar utf8mb4.\n";
}
