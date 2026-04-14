<?PHP
include("../conf.php"); 

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
    
    //Omplim la variable de Sessio amb les dades corresponents
    $sqlVentaMod="SELECT a.idCliente,b.idProducto,b.cantidad,b.descuento,b.iva,b.precio_unidad,b.precio_compra,c.precio_medio,c.nombre FROM ventas a inner join linea_venta b on a.id=b.idVenta inner join productos c on b.idProducto=c.id WHERE a.id = ".$_POST["idVenta"];
    //echo $sqlVentaMod;
    $rsVentaMod=mysqli_query($bd,$sqlVentaMod);
    $ii=0;
    while ($dataVentaMod=mysqli_fetch_array($rsVentaMod))
    {
        //[idCliente] => 16 [idProducto] => 59 [cantidad] => 2 [descuento] => 0.00 [altaLinea] => Introduir Producte 
        $_SESSION["lineas_venta"][$ii]["idCliente"]=$dataVentaMod["idCliente"];
        $_SESSION["lineas_venta"][$ii]["idProducto"]=$dataVentaMod["idProducto"];
        $_SESSION["lineas_venta"][$ii]["cantidad"]=$dataVentaMod["cantidad"];
        $_SESSION["lineas_venta"][$ii]["descuento"]=$dataVentaMod["descuento"];
        $nombre=$dataVentaMod["nombre"];
        $iva=$dataVentaMod["iva"];
        $precioVenta = $dataVentaMod["precio_unidad"];
        $precioCompra = $dataVentaMod["precio_compra"];
        $_SESSION["lineas_venta"][$ii]["precioVenta"] = $precioVenta;
        $_SESSION["lineas_venta"][$ii]["precioCompra"] = $precioCompra; 
        $precioDescuento = ($precioVenta * (1-$_SESSION["lineas_venta"][$ii]["descuento"] / 100));
        $_SESSION["lineas_venta"][$ii]["iva"]=$iva;
        $_SESSION["lineas_venta"][$ii]["introducido"]=1;
        ?>
        <tr>
            <td><?PHP echo $nombre; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta"][$ii]["cantidad"]; ?></td>
            <td align="right"><?PHP echo $precioVenta; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_venta"][$ii]["descuento"]; ?></td>
            <td align="right"><?PHP echo $precioDescuento; ?></td>
            <td align="right"><a href="javascript:document.borrarForm.idLineaVenta.value=<?PHP echo $ii; ?>;FAjax('borra_linea.php','lineasCompraDiv','borrarForm','POST')">Eliminar</a></td>
        </tr>    
        <?PHP
        $totalVenta+=$precioDescuento*$_SESSION["lineas_venta"][$ii]["cantidad"];
        $ii++;
    }
    
    ?>
        <tr style="background-color: appworkspace;">
            <td colspan="3"><strong>Total Venta</strong></td>
            <td align="right"><?PHP echo $totalVenta; ?></td>
            
        </tr> 
</table>
