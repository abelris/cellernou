-- Catas (26-oct-2026)
--
-- catas: las catas abiertas de /catas. Sin fecha hasta que se apunta el
-- mínimo de personas; entonces se pone la fecha y se avisa a los apuntados.
-- catas_inscripciones: las personas apuntadas a una cata (tipo 'publica'),
-- las peticiones de cata privada ('privada', sin cata) y quien quiere que le
-- avisen de las próximas ('aviso', sin cata).

CREATE TABLE IF NOT EXISTS catas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(7,2) NULL,
    minimo INT NOT NULL DEFAULT 8,
    maximo INT NULL,
    fecha DATETIME NULL,
    lugar VARCHAR(200) NULL,
    estado ENUM('abierta', 'confirmada', 'hecha', 'cancelada') NOT NULL DEFAULT 'abierta',
    publicada TINYINT(1) NOT NULL DEFAULT 0,
    orden INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS catas_inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('publica', 'privada', 'aviso') NOT NULL DEFAULT 'publica',
    id_cata INT NULL,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(30) NULL,
    personas INT NOT NULL DEFAULT 1,
    motivo VARCHAR(60) NULL,
    comentario TEXT NULL,
    estado ENUM('nueva', 'avisada', 'confirmada', 'baja') NOT NULL DEFAULT 'nueva',
    avisada_at DATETIME NULL,
    ip VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_cata (id_cata),
    KEY idx_tipo (tipo, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tres propuestas para empezar, en borrador: se revisan y se publican desde
-- el admin (WineWorld > Catas).
INSERT INTO catas (titulo, descripcion, minimo, publicada, orden)
SELECT * FROM (
    SELECT 'Vinos de Alella, la D.O. del Maresme' AS titulo,
           'Blancos y tintos de la denominación de origen que tenemos al lado de casa: la pansa blanca, la garnacha y lo que hacen hoy las bodegas de Alella.' AS descripcion,
           8 AS minimo, 0 AS publicada, 1 AS orden
    UNION ALL
    SELECT 'Cavas y espumosos para las fiestas',
           'Brut nature, reserva, rosado y un champagne para comparar: en qué se diferencian y cuál elegir para cada momento de la Navidad.',
           8, 0, 2
    UNION ALL
    SELECT 'Priorat y Montsant, garnacha y cariñena',
           'Dos zonas vecinas con las mismas uvas y vinos muy distintos. Probamos tintos de las dos y vemos por qué.',
           8, 0, 3
) AS semilla
WHERE NOT EXISTS (SELECT 1 FROM catas);
