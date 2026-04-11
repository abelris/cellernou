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
//var_dump($_SESSION["lineas_venta"]);
//die("set");
//print_r($_SESSION["lineas_venta"]);

$preciosE=0;
$esProfesional=0;

//Miramos si el cliente tiene precios especiales
$sqlClienteEspecial="SELECT * FROM clientes WHERE id=".$_SESSION["lineas_venta"][0]["idCliente"];
$rsClienteEspecial=mysqli_query($bd,$sqlClienteEspecial);
if ($dataClienteEspecial= mysqli_fetch_array($rsClienteEspecial))
{
    if ($dataClienteEspecial["precios_especiales"]==1)
        $preciosE=1;
    if ($dataClienteEspecial["profesional"]==1)
        $esProfesional=1;
    
}
mysqli_free_result($rsClienteEspecial);
?>
<form name="borrarForm" id="borrarForm" method="POST">
    <input type="hidden" name="idLineaVenta">
</form>
<table width="100%" border="1">
    <tr>
        <th>#</th>        
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
        
        
        //Seleccionem el preu propi del client i sino el te el general   
        if ($preciosE==1)
            $sqlProd="SELECT * from precios_especiales_proveidor a inner join productos b on a.idProducto=b.id WHERE b.id = ".$_SESSION["lineas_venta"][$ii]["idProducto"];
        else
            $sqlProd="SELECT * from precios_cliente a inner join productos b on a.idProducto=b.id WHERE b.id = ".$_SESSION["lineas_venta"][$ii]["idProducto"]." AND idCliente = ".$_SESSION["lineas_venta"][$ii]["idCliente"];
        
        
        //echo $sqlProd;
        $rsProd=mysqli_query($bd,$sqlProd);
        if ($dataProd=mysqli_fetch_array($rsProd))
        {
            $nombre=$dataProd["nombre"];
            $iva=$dataProd["iva"];
            $precioVenta = $dataProd["precio"];
            
            //Seleccionamos el ultimo precio de compra que viene de las lineas de compra y no del precio medio.
            $sqlprecioCompraProd="SELECT precio_unidad FROM linea_compra WHERE idProducto = ".$_SESSION["lineas_venta"][$ii]["idProducto"]." ORDER by fecha desc LIMIT 0,1";
            $rsprecioCompraProd=mysqli_query($bd,$sqlprecioCompraProd);
            if ($dataprecioCompraProd=mysqli_fetch_array($rsprecioCompraProd))
                $precioCompra = $dataprecioCompraProd["precio_unidad"];            
            else 
                $precioCompra = $dataProd["precio_medio"];                
            
            $procedencia = $dataProd["idProcedencia"];
            $_SESSION["lineas_venta"][$ii]["precioVenta"] = $precioVenta;
            $_SESSION["lineas_venta"][$ii]["precioCompra"] = $precioCompra; 
            $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta"][$ii]["descuento"] / 100));
            $_SESSION["lineas_venta"][$ii]["iva"]=$iva;
            $_SESSION["lineas_venta"][$ii]["procedencia"]=$procedencia;
        }
        else    //Sino trobem la linea a preu per client la busquem a la taula de productes general.
        {
            if ($esProfesional)
                $sqlProd="SELECT * from productos WHERE id = ".$_SESSION["lineas_venta"][$ii]["idProducto"];
            else
                $sqlProd="SELECT * from productos a inner join precios_producto_tienda b on a.id=b.idProducto WHERE a.id = ".$_SESSION["lineas_venta"][$ii]["idProducto"]." and b.idTienda=".$_SESSION["idUsuario"];
            //echo $sqlProd;
            $rsProd=mysqli_query($bd,$sqlProd);
            if ($dataProd=mysqli_fetch_array($rsProd))
            {
                $nombre=$dataProd["nombre"];
                $iva=$dataProd["iva"];
                if ($esProfesional)
                    $precioVenta = $dataProd["precio_venta_dis"];
                else
                    $precioVenta = number_format($dataProd["pvp"]/(1+($iva/100)),3);
                $precioCompra = $dataProd["precio_compra"];
                
                
                
                $margen=($precioCompra-$precioVenta)/$precioVenta*100;
                
                $procedencia = $dataProd["idProcedencia"];
                $_SESSION["lineas_venta"][$ii]["precioVenta"] = $precioVenta;
                
                
                $sqlprecioCompraProd="SELECT precio_unidad FROM linea_compra WHERE idProducto = ".$_SESSION["lineas_venta"][$ii]["idProducto"]." ORDER by fecha desc LIMIT 0,1";
                $rsprecioCompraProd=mysqli_query($bd,$sqlprecioCompraProd);
                if ($dataprecioCompraProd=mysqli_fetch_array($rsprecioCompraProd))
                    $precioCompra = $dataprecioCompraProd["precio_unidad"];            
                else 
                    $precioCompra = $dataProd["precio_medio"];
                
                $_SESSION["lineas_venta"][$ii]["precioCompra"] = $precioCompra;
                $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta"][$ii]["descuento"] / 100));
                $_SESSION["lineas_venta"][$ii]["iva"]=$iva;
                $_SESSION["lineas_venta"][$ii]["procedencia"]=$procedencia;
            }
        }
        mysqli_fetch_array($rsProd);
        
        $margeProdLinea=number_format(($precioVenta-$precioCompra)/$precioVenta*100,2);
        
        $color="";
        if ($margeProdLinea<15)
            $color="style='background-color:yellow;'";
        
        if ($margeProdLinea<10)
            $color="style='background-color:red;'";
        
        ?>
        <tr <?PHP echo $color; ?>>
            <td><?PHP echo $ii+1; ?></td>
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
