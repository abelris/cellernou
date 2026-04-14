-- ================================================
-- OnDigitalRis - Tablas independientes
-- ================================================

CREATE TABLE IF NOT EXISTS odr_clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    nombre_fiscal VARCHAR(200) DEFAULT '',
    nif VARCHAR(20) DEFAULT '',
    telefono VARCHAR(50) DEFAULT '',
    email VARCHAR(200) DEFAULT '',
    direccion VARCHAR(300) DEFAULT '',
    cp VARCHAR(10) DEFAULT '',
    poblacion VARCHAR(100) DEFAULT '',
    provincia VARCHAR(100) DEFAULT '',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS odr_proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    nombre_fiscal VARCHAR(200) DEFAULT '',
    nif VARCHAR(20) DEFAULT '',
    telefono VARCHAR(50) DEFAULT '',
    email VARCHAR(200) DEFAULT '',
    direccion VARCHAR(300) DEFAULT '',
    cp VARCHAR(10) DEFAULT '',
    poblacion VARCHAR(100) DEFAULT '',
    provincia VARCHAR(100) DEFAULT '',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS odr_facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL DEFAULT '',
    idCliente INT NOT NULL DEFAULT 0,
    fecha DATE NOT NULL,
    fecha_vencimiento DATE DEFAULT NULL,
    estado ENUM('pendent','pagada') DEFAULT 'pendent',
    fecha_pago DATE DEFAULT NULL,
    forma_pago VARCHAR(50) DEFAULT '',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cliente (idCliente),
    INDEX idx_fecha (fecha),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS odr_linea_factura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idFactura INT NOT NULL,
    concepto VARCHAR(500) NOT NULL DEFAULT '',
    cantidad DECIMAL(10,2) DEFAULT 1,
    precio_unidad DECIMAL(10,2) DEFAULT 0,
    iva DECIMAL(5,2) DEFAULT 21,
    INDEX idx_factura (idFactura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS odr_facturas_compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_factura VARCHAR(50) DEFAULT '',
    idProveedor INT NOT NULL DEFAULT 0,
    fecha DATE NOT NULL,
    fecha_vencimiento DATE DEFAULT NULL,
    estado ENUM('pendent','pagada') DEFAULT 'pendent',
    fecha_pago DATE DEFAULT NULL,
    forma_pago VARCHAR(50) DEFAULT '',
    notas TEXT,
    archivo VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_proveedor (idProveedor),
    INDEX idx_fecha (fecha),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS odr_linea_factura_compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idFactura INT NOT NULL,
    concepto VARCHAR(500) NOT NULL DEFAULT '',
    cantidad DECIMAL(10,2) DEFAULT 1,
    precio_unidad DECIMAL(10,2) DEFAULT 0,
    iva DECIMAL(5,2) DEFAULT 21,
    INDEX idx_factura (idFactura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
