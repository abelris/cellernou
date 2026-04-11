<?PHP 

include("../conf.php"); 

//Cargamos todas las lineas en una array y en una variable de session.
if (!isset($_SESSION["lineas_compra_desc"]))
{
    $lineasCompra[0]=$_POST;
    $_SESSION["lineas_compra_desc"]=$lineasCompra;
    
}
else
{
    array_push($_SESSION["lineas_compra_desc"],$_POST);
}

?>
<table width="100%" border="1">
    <tr>
        <th>Producte</th>
        <th>Quantitat</th>
        <th>Preu</th>
        <th></th>
    </tr>
    <?PHP
    $totalDescuento=0;
    for($ii=0;$ii<count($_SESSION["lineas_compra_desc"]);$ii++)
    {
        
        
        $sqlProd="SELECT * from productos WHERE id = ".$_SESSION["lineas_compra_desc"][$ii]["idProducto"];
        $rsProd=mysqli_query($bd,$sqlProd);
        if ($dataProd=mysqli_fetch_array($rsProd))
        {
            $nombre=$dataProd["nombre"];
            $precioMedio=$dataProd["precio_medio"];
            $_SESSION["lineas_compra_desc"][$ii]["precio_medio"]=$precioMedio;
        }
        mysqli_fetch_array($rsProd);
        ?>
        <tr>
            <td><?PHP echo $nombre; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_compra_desc"][$ii]["cantidad"]; ?></td>
            <td align="right"><?PHP echo $precioMedio; ?></td>
            <td align="right">Eliminar</td>
        </tr>    
        <?PHP
        $totalDescuento+=$_SESSION["lineas_compra_desc"][$ii]["precio_medio"]*$_SESSION["lineas_compra_desc"][$ii]["cantidad"];
        
    }
    
    ?>
        <tr style="background-color: appworkspace;">
            <td colspan="3"><strong>Total descuento</strong></td>
            <td align="right"><input type="text" value="<?PHP echo $totalDescuento; ?>" name="totalDescuento"></td>
        </tr> 
</table>
