<?PHP 

include("../conf.php"); 

//Cargamos todas las lineas en una array y en una variable de session.
if (!isset($_SESSION["lineas_venta"]))
{
    $lineasVenta[0]=$_POST;
    $_SESSION["lineas_venta"]=$lineasVenta;
    
}
else
{
    array_push($_SESSION["lineas_venta"],$_POST);
}

?>
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
        
        $sqlProd="SELECT * from precios_cliente a inner join productos b on a.idProducto=b.id WHERE b.id = ".$_SESSION["lineas_venta"][$ii]["idProducto"]." AND idCliente = ".$_SESSION["lineas_venta"][$ii]["idCliente"];
        //echo $sqlProd;
        $rsProd=mysqli_query($bd,$sqlProd);
        if ($dataProd=mysqli_fetch_array($rsProd))
        {
            $nombre=$dataProd["nombre"];
            $iva=$dataProd["iva"];
            $precioVenta = $dataProd["precio"];
            $precioCompra = $dataProd["precio_medio"];
            $_SESSION["lineas_venta"][$ii]["precioVenta"] = $precioVenta;
            $_SESSION["lineas_venta"][$ii]["precioCompra"] = $precioCompra; 
            $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta"][$ii]["descuento"] / 100));
            $_SESSION["lineas_venta"][$ii]["iva"]=$iva;
        }
        else    //Sino trobem la linea a preu per client la busquem a la taula de productes general.
        {
            $sqlProd="SELECT * from productos WHERE id = ".$_SESSION["lineas_venta"][$ii]["idProducto"];
            //echo $sqlProd;
            $rsProd=mysqli_query($bd,$sqlProd);
            if ($dataProd=mysqli_fetch_array($rsProd))
            {
                $nombre=$dataProd["nombre"];
                $iva=$dataProd["iva"];
                $precioVenta = $dataProd["precio_venta_dis"];
                $precioCompra = $dataProd["precio_medio"];
                $_SESSION["lineas_venta"][$ii]["precioVenta"] = $precioVenta;
                $_SESSION["lineas_venta"][$ii]["precioCompra"] = $precioCompra;
                $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta"][$ii]["descuento"] / 100));
                $_SESSION["lineas_venta"][$ii]["iva"]=$iva;
            }
        }
        mysqli_fetch_array($rsProd);
        ?>
        <tr>
            <td><?PHP echo $nombre; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta"][$ii]["cantidad"]; ?></td>
            <td align="right"><?PHP echo $precioVenta; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta"][$ii]["descuento"]; ?></td>
            <td align="right"><?PHP echo $precioDescuento; ?></td>
            <td align="right">Eliminar</td>
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
