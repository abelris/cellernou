-- WineWorld - Sección de artículos
-- Migración: crear tablas ww_categorias y ww_articulos

SET SESSION sql_mode = '';

CREATE TABLE IF NOT EXISTS ww_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    descripcion TEXT,
    imagen VARCHAR(255),
    color VARCHAR(7) DEFAULT '#722F37',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ww_articulos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorías iniciales de ejemplo
INSERT INTO ww_categorias (nombre, slug, descripcion, color, orden) VALUES
('Guías de Vino', 'guias-de-vino', 'Todo lo que necesitas saber sobre el mundo del vino: variedades, regiones, maridajes y más.', '#722F37', 1),
('Destilados', 'destilados', 'Descubre el fascinante mundo de los destilados: whisky, ron, ginebra, vodka y más.', '#C9A962', 2),
('Maridajes', 'maridajes', 'El arte de combinar vinos y bebidas con gastronomía para crear experiencias únicas.', '#2c3e50', 3),
('Cultura y Tradición', 'cultura-y-tradicion', 'Historia, tradiciones y curiosidades del mundo vinícola y de las bebidas espirituosas.', '#4a6741', 4);
