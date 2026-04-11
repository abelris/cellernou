-- Migration: Mover fotos de productos a tabla independiente
-- Fecha: 2026-03-04

CREATE TABLE IF NOT EXISTS producto_fotos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  idProducto INT NOT NULL,
  foto VARCHAR(100) NOT NULL,
  principal TINYINT(1) NOT NULL DEFAULT 1,
  orden INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_producto (idProducto),
  INDEX idx_principal (idProducto, principal)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Migrar datos existentes
INSERT INTO producto_fotos (idProducto, foto, principal)
SELECT id, foto, 1 FROM productos WHERE foto != '' AND foto IS NOT NULL;
