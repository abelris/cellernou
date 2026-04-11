<?PHP 

include("../conf.php"); 

//Cargamos todas las lineas en una array y en una variable de session.
if (!isset($_SESSION["lineas_compra"]))
{
    $lineasCompra[0]=$_POST;
    $_SESSION["lineas_compra"]=$lineasCompra;
    
}
else
{
    array_push($_SESSION["lineas_compra"],$_POST);
}

//print_r($_SESSION["lineas_compra"]); 

?>
<form name="borrarForm" id="borrarForm" method="POST">
    <input type="hidden" name="idLineaCompra">
</form>
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
        
        
        //$sqlProd="SELECT * from productos WHERE id = ".$_SESSION["lineas_compra"][$ii]["idProducto"];
        $sqlProd="SELECT * from productos WHERE codigo = ".$_SESSION["lineas_compra"][$ii]["codigo"];
        //echo $sqlProd;
        $rsProd=mysqli_query($bd,$sqlProd);
        if ($dataProd=mysqli_fetch_array($rsProd))
        {
            $nombre=$dataProd["nombre"];
            $iva=$dataProd["iva"];
            $_SESSION["lineas_compra"][$ii]["iva"]=$iva;
            $_SESSION["lineas_compra"][$ii]["idProducto"]=$dataProd["id"];
            $precioCompraP = $dataProd["precio_medio"];
        
        
        
        $preuCompraDesc=number_format($_SESSION["lineas_compra"][$ii]["precioCompra"] * (1-$_SESSION["lineas_compra"][$ii]["descuento"] / 100),3);
        //echo $preuCompraDesc;
        //Cada vegada que introduim una linea de compra, comparem el preu de compra
        //amb l'últim preu de compra per veure si ha cambiat.
        //Si es mes alt, pintem la linea vermella
        //Si es mes baix, pintem la linea verda
        
        
        $sqlPreuCompra="SELECT precio_unidad from linea_compra a inner join compras b on a.idCompra=b.id WHERE a.idProducto = ".$_SESSION["lineas_compra"][$ii]["idProducto"]." order by b.fecha DESC";
        $rsPreuCompra=mysqli_query($bd,$sqlPreuCompra);
        if ($dataPreuCompra=mysqli_fetch_array($rsPreuCompra))
        {
            $precioCompraP=$dataPreuCompra["precio_unidad"];
        }
        
        $colorLineaC="";
        if ($precioCompraP>$preuCompraDesc)
            $colorLineaC = "style='background-color:green'";
        if ($precioCompraP<$preuCompraDesc)
            $colorLineaC = "style='background-color:red'";
            
        
        ?>
        <tr <?PHP echo $colorLineaC; ?>>
            <td><?PHP echo $nombre; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_compra"][$ii]["cantidad"]; ?></td>
            <td align="right"><?PHP echo $preuCompraDesc." (".$precioCompraP.")"; ?></td>
            <td align="right"><?PHP echo number_format($_SESSION["lineas_compra"][$ii]["descuento"],3); ?>/<?PHP echo $_SESSION["lineas_compra"][$ii]["descuentoP"]; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_compra"][$ii]["precioCompra"]; ?></td>
            <td align="right"><a href="javascript:document.borrarForm.idLineaCompra.value=<?PHP echo $ii; ?>;FAjax('borra_linea.php','lineasCompraDiv','borrarForm','POST')">Eliminar</a></td>
        </tr>    
        <?PHP
        $totalCompra+=$preuCompraDesc*$_SESSION["lineas_compra"][$ii]["cantidad"];
        
        
        }
        else    //Si el producte no exiteix, forço alta. 
        {
            
            ?>
            <script language="javascript">
                //lightbox('<iframe src=../productes/alta.php?codigo=<?PHP echo $_SESSION["lineas_compra"][$ii]["codigo"]; ?>&precioCompra=<?PHP echo $_SESSION["lineas_compra"][$ii]["precioCompra"]; ?> width=900px frameborder=0 height=500px scrolling=no></iframe>',null);
            </script>
            
                        <a href="javascript:lightbox('<iframe src=../productes/alta.php?noMenu=1&codigo=<?PHP echo $_SESSION["lineas_compra"][$ii]["codigo"]; ?>&precioCompra=<?PHP echo $_SESSION["lineas_compra"][$ii]["precioCompra"]; ?> width=900px frameborder=0 height=500px scrolling=no></iframe>',null);">Nuevo Producto</a>
                        
            <?PHP          
            array_pop($_SESSION["lineas_compra"]);
        }
        mysqli_fetch_array($rsProd);
    }
    
    ?>
        <tr style="background-color: appworkspace;">
            <td colspan="3"><strong>Total compra</strong></td>
            <td align="right"><?PHP echo $totalCompra; ?></td>
            
        </tr> 
</table>
