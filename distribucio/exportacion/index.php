<?php


require_once('../../wp-content/plugins/woocommerce/vendor/autoload.php'); // Ajusta la ruta según tu estructura de directorios
use Automattic\WooCommerce\Client;

$woocommerce = new Client(
    'www.cellernou.com', // URL de tu tienda WooCommerce
    'ck_d8e7dc20bb570961690bcbe79348735308f15202', // Sustituye con tu clave de consumidor
    'cs_f8434c149455826cd0e229b682b5d22a57b8d962', // Sustituye con tu secreto de consumidor
    [
        'wp_api' => true,
        'version' => 'wc/v3',
    ]
);

$bd=mysqli_connect("localhost", "cellerno_db", "C334%e5lle&6rn(oU") or die("Could not connect");
mysqli_select_db($bd, "cellerno_db");

// Conexión a la base de datos de WordPress
$host = 'localhost';
$dbname = 'cellerno_db';
$user = 'cellerno_db';
$pass = 'C334%e5lle&6rn(oU';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
        $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

//print_r($csv_rows);


// Procesa los datos del CSV
//foreach ($csv_rows as $row) {
        //echo count($csv_headers)."<->".count($row),"<br />";
        //print_r($row);
        //echo4 "<br /><br />";
$sqlProd = "SELECT a.id,a.nombre,b.pvp FROM productos a inner join precios_producto_tienda b on a.id = b.idProducto where a.activo = 1";
$rsProd = mysqli_query($bd, $sqlProd);
$iProd = 0;   
while($dataProd = mysqli_fetch_array($rsProd))
{
        $producto = array ( 
                "ID" => $dataProd["id"],
                "Tipo" => "simple",
                "SKU" => $dataProd["id"], 
                "Nombre" => $dataProd["nombre"], 
                "Publicado" => "1",
                "¿Está destacado?" => "0", 
                "Visibilidad en el catálogo" => "visible",
                "Descripción corta" =>"", 
                "Descripción" => "\n \nRamón Bilbao Crianza  és un vi clàssic de La Rioja elaborat per Bodegas Ramón Bilbao, un celler que compta amb més d'un segle d'història. \n \nVa ser fundat en 1924 per Don Ramón Bilbao Murga, encara que des de 1896, Don Ramón regentava un petit celler a Haro des de on venia els seus primers vins. \n \nRamón Bilbao Crianza  és un monovarietal elaborat amb raïms de la varietat Ull de Llebre, varietat típica de La Rioja, procedent de diverses parcel·les. \nEl raïm es verema al punt òptim de maduració gràcies al seguiment que es realitza durant tot el cicle. \n \nEl raïm es despalilla i es fermenta cada parcel·la per separat a una temperatura controlada de 28-29ºC. \nEs realitza un trasbals ràpid, una vegada que s'aconsegueix l'extracció de color desitjada. \n \nRamón Bilbao Crianza  realitza una criança de 14 mesos en barriques de roure americà. Una vegada acabat l'envelliment, reposa 8 mesos en ampolla abans de sortir al mercat. \n \nRamón Bilbao Crianza  és un clàssic de La Rioja. Amb fruita, equilibrat i perfectament afinat pel treball realitzat amb les barriques de roure. \n \n",
                "Estado del impuesto" => "taxable",
                "Clase de impuesto" => "",
                "¿Existencias?" => "1",
                "Inventario" =>"1",
                "Cantidad de bajo inventario" =>"1",
                "¿Permitir reservas de productos agotados?" => "0",
                "¿Vendido individualmente?" => "0",
                "Peso (kg)" =>"1,5",
                "Longitud (cm)" =>"10",
                "Anchura (cm)" =>"10",
                "Altura (cm)" =>"35",
                "¿Permitir valoraciones de clientes?" => "1",
                "Precio normal" => $dataProd["pvp"],
                "Posición" => "0",
                );

        if ($iProd<3)
        {
        // Por ejemplo, para crear un producto
                $created_product = $woocommerce->post('products', $product_data);
                        if (isset($created_product->id)) {
                        echo 'Producto creado con ID: ' . $created_product->id . '<br>';
                } else {
                        echo 'Error al crear el producto.<br>';
                }
        }

        $iProd++;
}

// Cierra la conexión a la base de datos
$pdo = null;