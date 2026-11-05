-- Clics en WhatsApp y Llamar de la web pública (planner: tarea 48).
--
-- Contadores por día, tipo y página. No guarda IP, cookies ni nada que
-- identifique a la persona: solo cuántos clics hubo.

CREATE TABLE IF NOT EXISTS clics_contacto (
    fecha  DATE NOT NULL,
    tipo   ENUM('whatsapp','telefono') NOT NULL,
    pagina VARCHAR(190) NOT NULL,
    pueblo VARCHAR(80) NULL,
    clics  INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (fecha, tipo, pagina),
    KEY idx_pueblo (pueblo, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
