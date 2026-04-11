<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(dirname(dirname(__DIR__))) . '/connexio/connexio.php';

$sql = "CREATE TABLE IF NOT EXISTS `pagos_factura` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idFactura` int NOT NULL,
  `fecha` date NOT NULL,
  `importe` decimal(10,2) NOT NULL,
  `metodo_pago` int NOT NULL DEFAULT '1',
  `comentario` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_factura` (`idFactura`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$r = mysqli_query($bd, $sql);
if ($r) {
    echo "OK - Tabla pagos_factura creada correctamente.";
} else {
    echo "ERROR: " . mysqli_error($bd);
}
