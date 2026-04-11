<?PHP 

include("../conf.php"); 

$_SESSION["idUsuario"]=3;

/*
if (!isset($_SESSION["nombreUsuario"]))
{
?>
<script language="javascript">

location='http://www.cellernou.com/distribucio/';

</script>    
<?PHP    
    die();
}   
*/

//Cargamos todas las lineas en una array y en una variable de session.
if (!isset($_SESSION["lineas_venta_tiquet"]))
{
    $lineasVenta[0]=$_POST;
    $_SESSION["lineas_venta_tiquet"]=$lineasVenta;
    
}
else
{
    array_push($_SESSION["lineas_venta_tiquet"],$_POST);
}

//print_r($_POST);

//print_r($_SESSION["lineas_venta_tiquet"]);

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
    for($ii=0;$ii<count($_SESSION["lineas_venta_tiquet"]);$ii++)
    {
        
        //Seleccionem el preu propi del client i sino el te el general.
        
        $sqlProd="SELECT * from precios_cliente_tiquet a inner join productos b on a.idProducto=b.id WHERE b.id = ".$_SESSION["lineas_venta_tiquet"][$ii]["idProducto"]." AND idCliente = ".$_SESSION["lineas_venta_tiquet"][$ii]["idCliente"];
        //echo $sqlProd;
        $rsProd=mysqli_query($bd,$sqlProd);
        if ($dataProd=mysqli_fetch_array($rsProd))
        {
            $nombre=$dataProd["nombre"];
            $iva=$dataProd["iva"];
            $precioVenta = $dataProd["precio"];
            
            $sqlprecioCompraProd="SELECT precio_unidad FROM linea_compra WHERE idProducto = ".$_SESSION["lineas_venta_tiquet"][$ii]["idProducto"]." ORDER by fecha desc LIMIT 0,1";
            $rsprecioCompraProd=mysqli_query($bd,$sqlprecioCompraProd);
            if ($dataprecioCompraProd=mysqli_fetch_array($rsprecioCompraProd))
                $precioCompra = $dataprecioCompraProd["precio_unidad"];            
            else 
                $precioCompra = $dataProd["precio_medio"];  
            
            $procedencia = $dataProd["idProcedencia"];      //28 Granel
            $_SESSION["lineas_venta_tiquet"][$ii]["precioVenta"] = $precioVenta;
            $_SESSION["lineas_venta_tiquet"][$ii]["codigo"] = $dataProd["codigo"];
            $_SESSION["lineas_venta_tiquet"][$ii]["precioCompra"] = $precioCompra; 
            $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta_tiquet"][$ii]["descuento"] / 100));
            $_SESSION["lineas_venta_tiquet"][$ii]["iva"]=$iva;
            $_SESSION["lineas_venta_tiquet"][$ii]["procedencia"]=$procedencia;
            
            
            
            
        }
        else    //Sino trobem la linea a preu per client la busquem a la taula de productes general.
        {
            $sqlProd="SELECT * from productos a inner join precios_producto_tienda b on a.id=b.idProducto WHERE a.id = ".$_SESSION["lineas_venta_tiquet"][$ii]["idProducto"]." and b.idTienda = ".$_SESSION["idUsuario"];
            //echo $sqlProd;
            $rsProd=mysqli_query($bd,$sqlProd);
            if ($dataProd=mysqli_fetch_array($rsProd))
            {
                $nombre=$dataProd["nombre"];
                $iva=$dataProd["iva"];
                $precioVenta = $dataProd["pvp"];
                
                $sqlprecioCompraProd="SELECT precio_unidad FROM linea_compra WHERE idProducto = ".$_SESSION["lineas_venta_tiquet"][$ii]["idProducto"]." ORDER by fecha desc LIMIT 0,1";
                $rsprecioCompraProd=mysqli_query($bd,$sqlprecioCompraProd);
                if ($dataprecioCompraProd=mysqli_fetch_array($rsprecioCompraProd))
                    $precioCompra = $dataprecioCompraProd["precio_unidad"];            
                else 
                    $precioCompra = $dataProd["precio_medio"]; 
                
                $procedencia = $dataProd["idProcedencia"];      //28 Granel                
                $_SESSION["lineas_venta_tiquet"][$ii]["codigo"] = $dataProd["codigo"];
                $_SESSION["lineas_venta_tiquet"][$ii]["precioVenta"] = $precioVenta;
                $_SESSION["lineas_venta_tiquet"][$ii]["precioCompra"] = $precioCompra;
                $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta_tiquet"][$ii]["descuento"] / 100));
                $_SESSION["lineas_venta_tiquet"][$ii]["iva"]=$iva;
                $_SESSION["lineas_venta_tiquet"][$ii]["procedencia"]=$procedencia;
            }
        }
        mysqli_fetch_array($rsProd);
        
        $margeProdLinea=number_format(($precioVenta-($precioCompra*(1+($iva/100))))/$precioVenta*100,2);
        
        $color="";
        if ($margeProdLinea<20)
            $color="style='background-color:yellow;'";
        
        if ($margeProdLinea<15)
            $color="style='background-color:red;'";
        
        
        
        ?>
        <tr <?PHP echo $color; ?>>
            <td><?PHP echo $nombre; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta_tiquet"][$ii]["cantidad"]; ?></td>
            <td align="right"><?PHP echo $precioVenta; ?>(<?PHP echo $margeProdLinea; ?>)</td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta_tiquet"][$ii]["descuento"]; ?></td>
            <td align="right"><?PHP echo $precioDescuento; ?></td>
            <td align="right"><a href="javascript:document.borrarForm.idLineaVenta.value=<?PHP echo $ii; ?>;FAjax('borra_linea.php','lineasCompraDiv','borrarForm','POST')">Eliminar</a></td>
        </tr>    
        <?PHP
        $totalVenta+=$precioDescuento*$_SESSION["lineas_venta_tiquet"][$ii]["cantidad"];
        
    }
    
    ?>
        <tr style=" font-size: 20px; background-color: appworkspace;">
            <td style="background-color:#3d3" colspan="4"><strong>Total Venta</strong></td>
            <td style="background-color:#3d3" align="right"><?PHP echo $totalVenta; ?></td>
            <td style="background-color:#3d3">&nbsp;</td>
        </tr> 
</table>
