<?PHP 

include("../conf.php"); 

//Cargamos todas las lineas en una array y en una variable de session.
array_splice($_SESSION["lineas_venta_tiquet"],$_POST["idLineaVenta"],1);   //Borramos el elemento de la linea


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
            $precioCompra = $dataProd["precio_medio"];
            $_SESSION["lineas_venta_tiquet"][$ii]["precioVenta"] = $precioVenta;
            $_SESSION["lineas_venta_tiquet"][$ii]["precioCompra"] = $precioCompra; 
            $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta_tiquet"][$ii]["descuento"] / 100));
            $_SESSION["lineas_venta_tiquet"][$ii]["iva"]=$iva;
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
                $precioCompra = $dataProd["precio_medio"];
                $_SESSION["lineas_venta_tiquet"][$ii]["precioVenta"] = $precioVenta;
                $_SESSION["lineas_venta_tiquet"][$ii]["precioCompra"] = $precioCompra;
                $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta_tiquet"][$ii]["descuento"] / 100));
                $_SESSION["lineas_venta_tiquet"][$ii]["iva"]=$iva;
            }
        }
        mysqli_fetch_array($rsProd);
        
        
        
        
        ?>
        <tr>
            <td><?PHP echo $nombre; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta_tiquet"][$ii]["cantidad"]; ?></td>
            <td align="right"><?PHP echo $precioVenta; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta_tiquet"][$ii]["descuento"]; ?></td>
            <td align="right"><?PHP echo $precioDescuento; ?></td>
            <td align="right"><a href="javascript:document.borrarForm.idLineaVenta.value=<?PHP echo $ii; ?>;FAjax('borra_linea_t.php','lineasCompraDiv','borrarForm','POST')">Eliminar</a></td>
        </tr>    
        <?PHP
        $totalVenta+=$precioDescuento*$_SESSION["lineas_venta_tiquet"][$ii]["cantidad"];
        
    }
    
    ?>
        <tr style="background-color: appworkspace;">
            <td colspan="3"><strong>Total Venta</strong></td>
            <td align="right"><?PHP echo $totalVenta; ?></td>
            
        </tr> 
</table>
