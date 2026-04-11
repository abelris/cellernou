<?PHP 

include("../conf.php"); 

//Borramos la linea seleccionada.

?>
<table width="100%" border="1">
    <tr>
        <th>Producte</th>
        <th>Quantitat</th>
        <th>Preu</th>
        <th>Descompte</th>
        <th>Preu Descompte</th>
        <th></th>
    </tr>
    <?PHP
    $totalCompra=0;
    for($ii=0;$ii<count($_SESSION["lineas_compra"]);$ii++)
    {
        
        
        $sqlProd="SELECT * from productos WHERE id = ".$_SESSION["lineas_compra"][$ii]["idProducto"];
        //echo $sqlProd;
        $rsProd=mysqli_query($bd,$sqlProd);
        if ($dataProd=mysqli_fetch_array($rsProd))
        {
            $nombre=$dataProd["nombre"];
            $iva=$dataProd["iva"];
            $_SESSION["lineas_compra"][$ii]["iva"]=$iva;
        }
        mysqli_fetch_array($rsProd);
        
        
        $preuCompraDesc=number_format($_SESSION["lineas_compra"][$ii]["precioCompra"] * (1-$_SESSION["lineas_compra"][$ii]["descuento"] / 100),2);
        
        ?>
        <tr>
            <td><?PHP echo $nombre; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_compra"][$ii]["cantidad"]; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_compra"][$ii]["precioCompra"]; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_compra"][$ii]["descuento"]; ?></td>
            <td align="right"><?PHP echo $preuCompraDesc; ?></td>
            <td align="right">Eliminar</td>
        </tr>    
        <?PHP
        $totalCompra+=$preuCompraDesc*$_SESSION["lineas_compra"][$ii]["cantidad"];
        
    }
    
    ?>
        <tr style="background-color: appworkspace;">
            <td colspan="3"><strong>Total compra</strong></td>
            <td align="right"><?PHP echo $totalCompra; ?></td>
            
        </tr> 
</table>
