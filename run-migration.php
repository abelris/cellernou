<?php
/**
 * Script temporal para ejecutar la migración de WineWorld en producción.
 * ELIMINAR DESPUÉS DE EJECUTAR.
 * URL: https://cellernou.com/run-migration.php
 */

echo "<pre style='font-family:monospace;padding:2rem;'>";
echo "=== WineWorld Migration ===\n\n";

// Detectar entorno
$isLocal = (strpos($_SERVER['HTTP_HOST'] ?? '', 'cellernou.com') === false);
if ($isLocal) {
    $bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
} else {
    $bd = mysqli_connect('localhost', 'mycellerno7d', 'Cellernou2025%', 'cellernodb');
}

if (!$bd) {
    die('Error de conexión: ' . mysqli_connect_error());
}
mysqli_set_charset($bd, 'utf8mb4');
mysqli_query($bd, "SET SESSION sql_mode = ''");

// 1. Crear tablas
echo "1. Creando tablas...\n";

$sql1 = "CREATE TABLE IF NOT EXISTS ww_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    descripcion TEXT,
    imagen VARCHAR(255),
    color VARCHAR(7) DEFAULT '#722F37',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($bd, $sql1)) {
    echo "   ✓ Tabla ww_categorias OK\n";
} else {
    echo "   ✗ Error: " . mysqli_error($bd) . "\n";
}

$sql2 = "CREATE TABLE IF NOT EXISTS ww_articulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    titulo VARCHAR(300) NOT NULL,
    slug VARCHAR(300) NOT NULL UNIQUE,
    extracto TEXT,
    contenido LONGTEXT,
    imagen VARCHAR(255),
    autor VARCHAR(100),
    publicado TINYINT(1) DEFAULT 0,
    fecha_publicacion DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES ww_categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($bd, $sql2)) {
    echo "   ✓ Tabla ww_articulos OK\n";
} else {
    echo "   ✗ Error: " . mysqli_error($bd) . "\n";
}

// 2. Insertar categorías (solo si no existen)
echo "\n2. Insertando categorías...\n";

$check = mysqli_query($bd, "SELECT COUNT(*) as c FROM ww_categorias");
$row = mysqli_fetch_assoc($check);

if ($row['c'] == 0) {
    $cats = "INSERT INTO ww_categorias (nombre, slug, descripcion, color, orden) VALUES
    ('Guías de Vino', 'guias-de-vino', 'Todo lo que necesitas saber sobre el mundo del vino: variedades, regiones, maridajes y más.', '#722F37', 1),
    ('Destilados', 'destilados', 'Descubre el fascinante mundo de los destilados: whisky, ron, ginebra, vodka y más.', '#C9A962', 2),
    ('Maridajes', 'maridajes', 'El arte de combinar vinos y bebidas con gastronomía para crear experiencias únicas.', '#2c3e50', 3),
    ('Cultura y Tradición', 'cultura-y-tradicion', 'Historia, tradiciones y curiosidades del mundo vinícola y de las bebidas espirituosas.', '#4a6741', 4)";

    if (mysqli_query($bd, $cats)) {
        echo "   ✓ 4 categorías insertadas\n";
    } else {
        echo "   ✗ Error: " . mysqli_error($bd) . "\n";
    }
} else {
    echo "   → Ya existen {$row['c']} categorías, saltando.\n";
}

// 3. Insertar artículo
echo "\n3. Insertando artículo...\n";

$checkArt = mysqli_query($bd, "SELECT COUNT(*) as c FROM ww_articulos WHERE slug = 'guia-definitiva-regalar-vino-cava-licores'");
$rowArt = mysqli_fetch_assoc($checkArt);

if ($rowArt['c'] == 0) {
    // Cargar y ejecutar el script de inserción (reutiliza $bd)
    $scriptPath = __DIR__ . '/sql/insert_articulo_regalar_vino.php';
    if (file_exists($scriptPath)) {
        include $scriptPath;
    } else {
        echo "   ✗ Script de artículo no encontrado en: $scriptPath\n";
        echo "   → Puedes crear el artículo manualmente desde el admin.\n";
    }
} else {
    echo "   → El artículo ya existe, saltando.\n";
}

// 4. Verificación final
echo "\n=== Verificación ===\n";
$res = mysqli_query($bd, "SELECT COUNT(*) as c FROM ww_categorias");
$r = mysqli_fetch_assoc($res);
echo "Categorías: {$r['c']}\n";

$res = mysqli_query($bd, "SELECT COUNT(*) as c FROM ww_articulos");
$r = mysqli_fetch_assoc($res);
echo "Artículos: {$r['c']}\n";

echo "\n✓ Migración completada.\n";
echo "\n⚠ IMPORTANTE: Elimina este archivo (run-migration.php) del servidor.\n";
echo "</pre>";

mysqli_close($bd);
