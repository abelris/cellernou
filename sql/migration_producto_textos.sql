-- Migración: Crear tabla producto_textos
-- Tabla independiente para textos de producto (descripción, nota de cata, texto libre)

CREATE TABLE IF NOT EXISTS producto_textos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  idProducto INT NOT NULL,
  tipo ENUM('descripcion','nota_cata','texto_libre') NOT NULL,
  contenido TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE INDEX idx_producto_tipo (idProducto, tipo)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Migrar descripciones existentes desde productos.descripcion
INSERT INTO producto_textos (idProducto, tipo, contenido)
SELECT id, 'descripcion', descripcion FROM productos
WHERE descripcion != '' AND descripcion IS NOT NULL;
