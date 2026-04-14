-- Migración: Crear tabla pagos_factura para pagos parciales y completos
-- Ejecutar: mysql -u root cellernodb < sql/migration_pagos_factura.sql

CREATE TABLE IF NOT EXISTS `pagos_factura` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `idFactura` INT(11) NOT NULL,
    `fecha` DATE NOT NULL,
    `importe` DECIMAL(10,2) NOT NULL,
    `metodo_pago` INT(11) NOT NULL DEFAULT 1,
    `comentario` VARCHAR(255) NOT NULL DEFAULT '',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_factura` (`idFactura`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
