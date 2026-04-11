<?PHP 

include("../../conf.php"); 

//Cargamos todas las lineas en una array y en una variable de session.
if (!isset($_SESSION["lineas_stock"]))
{
    $lineasVenta[0]=$_POST;
    $_SESSION["lineas_stock"]=$lineasVenta;
    
}
else
{
    array_push($_SESSION["lineas_stock"],$_POST);
}

//print_r($_POST);

//print_r($_SESSION["lineas_stock"]);

?>

Encara no funciona!

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
    for($ii=0;$ii<count($_SESSION["lineas_stock"]);$ii++)
    {
        
        //Seleccionem el preu propi del client i sino el te el general.
        
        $sqlProd="SELECT a.precio,b.id,b.nombre,b.iva,b.precio_medio from precios_cliente_tiquet a inner join productos b on a.idProducto=b.id WHERE b.codigo = ".$_SESSION["lineas_stock"][$ii]["codigo"];
        //echo $sqlProd;
        $rsProd=mysqli_query($bd,$sqlProd);
        if ($dataProd=mysqli_fetch_array($rsProd))
        {
            $nombre=$dataProd["nombre"];
            $iva=$dataProd["iva"];
            $precioVenta = $dataProd["precio"];
            
            $precioCompra=0;
            //El preu de compra ha de ser el dels productes mes antics
            
            
            $precioCompra = $dataProd["precio_medio"];
            $_SESSION["lineas_stock"][$ii]["idProducto"] = $dataProd["id"];
            $_SESSION["lineas_stock"][$ii]["precioVenta"] = $precioVenta;
            $_SESSION["lineas_stock"][$ii]["precioCompra"] = $precioCompra; 
            $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_stock"][$ii]["descuento"] / 100));
            $_SESSION["lineas_stock"][$ii]["iva"]=$iva;
        }
        else    //Sino trobem la linea a preu per client la busquem a la taula de productes general.
        {
            $sqlProd="SELECT a.id,a.nombre,a.iva,b.pvp,a.precio_medio from productos a inner join precios_producto_tienda b on a.id=b.idProducto WHERE a.codigo = '".$_SESSION["lineas_stock"][$ii]["codigo"]."' and b.idTienda = ".$_SESSION["idUsuario"];
            //echo $sqlProd;
            $rsProd=mysqli_query($bd,$sqlProd);
            if ($dataProd=mysqli_fetch_array($rsProd))
            {
                $nombre=$dataProd["nombre"];
                $iva=$dataProd["iva"];
                $precioVenta = $dataProd["pvp"];
                $precioCompra = $dataProd["precio_medio"];
                $_SESSION["lineas_stock"][$ii]["idProducto"] = $dataProd["id"];
                $_SESSION["lineas_stock"][$ii]["precioVenta"] = $precioVenta;
                $_SESSION["lineas_stock"][$ii]["precioCompra"] = $precioCompra;
                $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_stock"][$ii]["descuento"] / 100));
                $_SESSION["lineas_stock"][$ii]["iva"]=$iva;
            }
        }
        mysqli_fetch_array($rsProd);
        ?>
        <tr>
            <td><?PHP echo $nombre; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_stock"][$ii]["cantidad"]; ?></td>
            <td align="right"><?PHP echo $precioVenta; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_stock"][$ii]["descuento"]; ?></td>
            <td align="right"><?PHP echo $precioDescuento; ?></td>
            <td align="right"><a href="javascript:document.borrarForm.idLineaVenta.value=<?PHP echo $ii; ?>;FAjax('borra_linea_t.php','lineasCompraDiv','borrarForm','POST')">Eliminar</a></td>
        </tr>    
        <?PHP
        $totalVenta+=$precioDescuento*$_SESSION["lineas_stock"][$ii]["cantidad"];
        
    }
    
    ?>
        <tr style="background-color: appworkspace;">
            <td colspan="3"><strong>Total Venta</strong></td>
            <td align="right"><?PHP echo $totalVenta; ?></td>
            
        </tr> 
</table>
