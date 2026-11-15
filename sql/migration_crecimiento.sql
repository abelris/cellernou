-- Plan de crecimiento de la tienda (admin: modules/creixement).
--
-- Las tareas viven en modules/creixement/plan.php; aquí solo se guarda su
-- estado y los indicadores que no salen del TPV (reseñas de Google).

CREATE TABLE IF NOT EXISTS crecimiento_estado (
    codigo     VARCHAR(10) NOT NULL PRIMARY KEY,
    hecho      TINYINT(1) NOT NULL DEFAULT 0,
    hecho_en   DATETIME NULL,
    hecho_por  VARCHAR(100) NULL,
    nota       TEXT NULL,
    actualizado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS crecimiento_kpi (
    mes        CHAR(7) NOT NULL,          -- AAAA-MM
    indicador  VARCHAR(30) NOT NULL,      -- resenas, nota_google
    valor      DECIMAL(10,2) NOT NULL,
    actualizado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (mes, indicador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
