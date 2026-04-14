<?PHP 

include("../conf.php"); 

$fechaE = explode("-",$_POST["fecha_emision"]);
$fecha_cambiadaE = mktime(00,00,00,$fechaE[1],$fechaE[0],$fechaE[2]);
$fechaE = date("Y-m-d", $fecha_cambiadaE);

$fechaV = explode("-",$_POST["fecha_vencimiento"]);
$fecha_cambiadaV = mktime(00,00,00,$fechaV[1],$fechaV[0],$fechaV[2]);
$fechaV = date("Y-m-d", $fecha_cambiadaV); 


//Primer de tot, mirem si l'albara ja ha estat facturat
$sqlVenta="SELECT * FROM facturas WHERE idVenta=".$_POST["idVenta"];
$rsVenta = mysqli_query($bd,$sqlVenta);
if ($dataVenta=mysqli_fetch_array($rsVenta)) //Si existe la factura
{
   $idFacturaAnterior = $dataVenta["id"];  
   $sqlDel="DELETE FROM linea_factura WHERE idFactura = ".$idFacturaAnterior;
   $rsDel=mysqli_query($bd,$sqlDel);
   
   //die($sqlDel);
   
   $idFactura=$idFacturaAnterior;
   
}
else
{
    //Copiamos la venta en la tabla de facturas.
    $sqlFactura="INSERT INTO facturas (idCliente,descuento,precio_desc,coste,precio_coste,idEstado,idVenta,fecha,fecha_vencimiento,idFormaPago) 
                                    SELECT idCliente,descuento,precio_desc,coste,precio_coste,0,id,'".$fechaE."','".$fechaV."','".$_POST["idFormaPago"]."' FROM ventas WHERE id = ".$_POST["idVenta"];

    mysqli_query($bd,$sqlFactura);
    $idFactura= mysqli_insert_id($bd);

    
}
mysqli_free_result($rsVenta);




$sqlLineaFactura="INSERT INTO linea_factura (idFactura,idProducto,idCliente,cantidad,precio_unidad,descuento,precio_unidad_sin_desc,precio_compra,iva)
                  SELECT ".$idFactura.",idProducto,idCliente,cantidad,precio_unidad,descuento,precio_unidad_sin_desc,precio_compra,iva FROM linea_venta WHERE idVenta = ".$_POST["idVenta"];
mysqli_query($bd,$sqlLineaFactura);




//Modificamos el estado  
$sqlEstado="UPDATE ventas SET idEstado = 5 WHERE id = ".$_POST["idVenta"];
mysqli_query($bd,$sqlEstado);

?>
La factura ha sido pasada!<br /><br />
<input type="button" onclick="closeLightbox();">