<?PHP 

include("../conf.php"); 

//Cargamos todas las lineas en una array y en una variable de session.
array_splice($_SESSION["lineas_venta"],$_POST["idLineaVenta"],1);   //Borramos el elemento de la linea


$preciosE=0;

//Miramos si el cliente tiene precios especiales
$sqlClienteEspecial="SELECT * FROM clientes WHERE id=".$_SESSION["lineas_venta"][0]["idCliente"];
$rsClienteEspecial=mysqli_query($bd,$sqlClienteEspecial);
if ($dataClienteEspecial= mysqli_fetch_array($rsClienteEspecial))
{
    if ($dataClienteEspecial["precios_especiales"]==1)
        $preciosE=1;
}
mysqli_free_result($rsClienteEspecial);

?>
<form name="borrarForm" id="borrarForm" method="POST">
    <input type="hidden" name="idLineaVenta">
</form>
<table width="100%" border="1">
    <tr>
        <th>Producte</th>
        <th>Quantitat</th>
        <th>Preu</th>
        <th>Desconte</th>
        <th>Preu Descompte</th>
        <th></th>
    </tr>
    <?PHP
    $totalVenta=0;
    for($ii=0;$ii<count($_SESSION["lineas_venta"]);$ii++)
    {
        //Seleccionem el preu propi del client i sino el te el general.        
        if ($preciosE==1)
            $sqlProd="SELECT * from precios_especiales_proveidor a inner join productos b on a.idProducto=b.id WHERE b.id = ".$_SESSION["lineas_venta"][$ii]["idProducto"];
        else
            $sqlProd="SELECT * from precios_cliente a inner join productos b on a.idProducto=b.id WHERE b.id = ".$_SESSION["lineas_venta"][$ii]["idProducto"]." AND idCliente = ".$_SESSION["lineas_venta"][$ii]["idCliente"];
        $rsProd=mysqli_query($bd,$sqlProd);
        if ($dataProd=mysqli_fetch_array($rsProd))
        {
            $nombre=$dataProd["nombre"];
            $iva=$dataProd["iva"];
            $precioVenta = $dataProd["precio"];
            
            $sqlprecioCompraProd="SELECT precio_unidad FROM linea_compra WHERE idProducto = ".$_SESSION["lineas_venta"][$ii]["idProducto"]." ORDER by fecha desc LIMIT 0,1";
            $rsprecioCompraProd=mysqli_query($bd,$sqlprecioCompraProd);
            if ($dataprecioCompraProd=mysqli_fetch_array($rsprecioCompraProd))
                $precioCompra = $dataprecioCompraProd["precio_unidad"];            
            else 
                $precioCompra = $dataProd["precio_medio"];  
            
            $_SESSION["lineas_venta"][$ii]["precioVenta"] = $precioVenta;
            $_SESSION["lineas_venta"][$ii]["precioCompra"] = $precioCompra; 
            $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta"][$ii]["descuento"] / 100));
            $_SESSION["lineas_venta"][$ii]["iva"]=$iva;
        }
        else    //Sino trobem la linea a preu per client la busquem a la taula de productes general.
        {
            $sqlProd="SELECT * from productos WHERE id = ".$_SESSION["lineas_venta"][$ii]["idProducto"];
            $rsProd=mysqli_query($bd,$sqlProd);
            if ($dataProd=mysqli_fetch_array($rsProd))
            {
                $nombre=$dataProd["nombre"];
                $iva=$dataProd["iva"];
                $precioVenta = $dataProd["precio_venta_dis"];
                
                $sqlprecioCompraProd="SELECT precio_unidad FROM linea_compra WHERE idProducto = ".$_SESSION["lineas_venta"][$ii]["idProducto"]." ORDER by fecha desc LIMIT 0,1";
                
                $rsprecioCompraProd=mysqli_query($bd,$sqlprecioCompraProd);
                if ($dataprecioCompraProd=mysqli_fetch_array($rsprecioCompraProd))
                    $precioCompra = $dataprecioCompraProd["precio_unidad"];            
                else 
                    $precioCompra = $dataProd["precio_medio"]; 
                
                $_SESSION["lineas_venta"][$ii]["precioVenta"] = $precioVenta;
                $_SESSION["lineas_venta"][$ii]["precioCompra"] = $precioCompra;
                $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta"][$ii]["descuento"] / 100));
                $_SESSION["lineas_venta"][$ii]["iva"]=$iva;
                
                
            }
        }
        mysqli_fetch_array($rsProd);
        
        if (!empty($precioVenta))
        {
            $margeProdLinea=number_format(($precioVenta-$precioCompra)/$precioVenta*100,2);
        }
        else
        {
            $margeProdLinea=0;
        }
        $color="";
        if ($margeProdLinea<15)
            $color="style='background-color:yellow;'";
        
        if ($margeProdLinea<10)
            $color="style='background-color:red;'";
        
        ?>
        <tr <?PHP echo $color; ?>>
            <td><?PHP echo $nombre; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta"][$ii]["cantidad"]; ?></td>
            <td align="right"><?PHP echo $precioVenta; ?> (<?PHP echo $margeProdLinea; ?>)</td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta"][$ii]["descuento"]; ?></td>
            <td align="right"><?PHP echo $precioDescuento; ?></td>
            <td align="right"><a href="javascript:document.borrarForm.idLineaVenta.value=<?PHP echo $ii; ?>;FAjax('borra_linea.php','lineasCompraDiv','borrarForm','POST')">Eliminar</a></td>
        </tr>    
        <?PHP
        
        $totalVenta+=$precioDescuento*$_SESSION["lineas_venta"][$ii]["cantidad"];
        
        
    }
    
    ?>
        <tr style="background-color: appworkspace;">
            <td colspan="3"><strong>Total Venta</strong></td>
            <td align="right"><?PHP echo $totalVenta; ?></td>
            
        </tr> 
</table>
