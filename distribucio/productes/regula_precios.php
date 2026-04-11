<?PHP include("../conf.php"); 


$sql="SELECT * FROM productos WHERE id NOT IN (SELECT idProducto from linea_compra GROUP BY idProducto)";
$rs=mysqli_query($bd,$sql);
$i=0;
while ($data=mysqli_fetch_array($rs))
{
    //print_r($data);
    $insert="INSERT INTO stock_producto 
        (idTienda,idCompra,idProducto,fecha_entrada,precio_compra,vendido,fecha_vendido) 
        VALUES 
        ('1','683','".$data["id"]."','".date('Y-m-d H:i:s')."','".$data["precio_medio"]."','1','".date('Y-m-d H:i:s')."')";
    //echo $insert."<br>";
    mysqli_query($bd,$insert);
    $insertCompra="INSERT INTO linea_compra (idCompra,idProducto,idProveedor,fecha,cantidad,precio_unidad,precio_unidad_sin_desc,iva,vendido,fecha_venta) 
        VALUES 
        ('683','".$data["id"]."','56','".date('Y-m-d H:i:s')."','1','".$data["precio_medio"]."','".$data["precio_medio"]."','".$data["iva"]."','1','".date('Y-m-d H:i:s')."')";
    //echo $insertCompra."<br>";
    mysqli_query($bd,$insertCompra);
    //$i++;
    echo $data["nombre"]." regulado. </br>";
}
?>

Precios regulados