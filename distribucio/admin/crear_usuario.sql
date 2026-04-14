-- =============================================
-- El Celler Nou - Crear Usuario Administrador
-- =============================================

-- Opción 1: Con contraseña en texto plano (se actualizará automáticamente a bcrypt en el primer login)
INSERT INTO usuarios (nombre, usuario, password)
VALUES ('Almab', 'almab', 'cellernou');

-- Opción 2: Con contraseña ya hasheada (bcrypt)
-- Si prefieres insertar directamente con hash, usa esta en lugar de la anterior:
-- INSERT INTO usuarios (nombre, usuario, password)
-- VALUES ('Almab', 'almab', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4dS.LRDP8KAWW6IG');

-- =============================================
-- Verificar que se creó correctamente:
-- SELECT * FROM usuarios WHERE usuario = 'almab';
-- =============================================
