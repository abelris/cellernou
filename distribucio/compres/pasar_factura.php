<?PHP 

include("../conf.php"); 

$fechaE = explode("-",$_POST["fecha_emision"]);
$fecha_cambiadaE = mktime(00,00,00,$fechaE[1],$fechaE[0],$fechaE[2]);
$fechaE = date("Y-m-d", $fecha_cambiadaE);

$fechaV = explode("-",$_POST["fecha_vencimiento"]);
$fecha_cambiadaV = mktime(00,00,00,$fechaV[1],$fechaV[0],$fechaV[2]);
$fechaV = date("Y-m-d", $fecha_cambiadaV); 


//Copiamos la venta en la tabla de facturas.
$sqlFactura="INSERT INTO facturas_compra (numFactura,idProveedor,descuento,precio_desc,coste,precio_coste,idEstado,idCompra,fecha,fecha_vencimiento,total_base,total_iva,total) 
             SELECT '".$_POST["numFactura"]."',idProveedor,descuento,precio_desc,coste,precio_coste,0,id,'".$fechaE."','".$fechaV."',total_base,total_iva,total FROM compras WHERE id = ".$_POST["idCompra"];
//die($sqlFactura);
mysqli_query($bd,$sqlFactura);
$idFactura= mysqli_insert_id($bd);

$sqlLineaFactura="INSERT INTO linea_factura_compra (idFactura,idProducto,idProveedor,cantidad,precio_unidad,descuento,precio_unidad_sin_desc,iva)
                  SELECT ".$idFactura.",idProducto,idProveedor,cantidad,precio_unidad,descuento,precio_unidad_sin_desc,iva FROM linea_compra WHERE idCompra = ".$_POST["idCompra"];
//die($sqlLineaFactura);
mysqli_query($bd,$sqlLineaFactura);




//Modificamos el estado  
$sqlEstado="UPDATE compras SET idEstado = 3 WHERE id = ".$_POST["idCompra"];
mysqli_query($bd,$sqlEstado);

?>
La factura ha sido pasada!