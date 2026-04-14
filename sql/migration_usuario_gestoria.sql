-- =====================================================
-- El Celler Nou - Migración: Usuario Gestoria con rol
-- Fecha: 2026-03-12
-- =====================================================

-- 1. Añadir columna 'rol' a la tabla usuarios (si no existe)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'rol');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN rol VARCHAR(20) NOT NULL DEFAULT ''admin''',
    'SELECT ''Column rol already exists''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Asegurar que usuarios existentes tienen rol 'admin'
UPDATE usuarios SET rol = 'admin' WHERE rol = '' OR rol IS NULL;

-- 3. Crear usuario gestoria (si no existe)
INSERT INTO usuarios (nombre, usuario, password, rol)
SELECT 'Gestoria', 'gestoria', 'gestoria2025', 'gestoria'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE usuario = 'gestoria');
