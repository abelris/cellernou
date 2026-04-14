<?PHP 

include("../conf.php"); 

//Cargamos todas las lineas en una array y en una variable de session.
array_splice($_SESSION["lineas_compra"],$_POST["idLineaCompra"],1);   //Borramos el elemento de la linea

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
        
        
        $sqlProd="SELECT * from productos WHERE id = ".$_SESSION["lineas_compra"][$ii]["idProducto"];
        //echo $sqlProd;
        $rsProd=mysqli_query($bd,$sqlProd);
        if ($dataProd=mysqli_fetch_array($rsProd))
        {
            $nombre=$dataProd["nombre"];
            $iva=$dataProd["iva"];
            $_SESSION["lineas_compra"][$ii]["iva"]=$iva;
            $precioCompraP = $dataProd["precio_medio"];            
        }
        mysqli_fetch_array($rsProd);
        
        
        $preuCompraDesc=number_format($_SESSION["lineas_compra"][$ii]["precioCompra"] * (1-$_SESSION["lineas_compra"][$ii]["descuento"] / 100),3);
        
        //Cada vegada que introduim una linea de compra, comparem el preu de compra
        //amb l'últim preu de compra per veure si ha cambiat.
        //Si es mes alt, pintem la linea vermella
        //Si es mes baix, pintem la linea verda
        $sqlPreuCompra="SELECT * from linea_compra WHERE idProducto = ".$_SESSION["lineas_compra"][$ii]["idProducto"]." order by id DESC";
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
            <td align="right"><?PHP echo $_SESSION["lineas_compra"][$ii]["descuento"]; ?></td>
            <td align="right"><?PHP echo $_SESSION["lineas_compra"][$ii]["precioCompra"]; ?></td>
            <td align="right"><a href="javascript:document.borrarForm.idLineaCompra.value=<?PHP echo $ii; ?>;FAjax('borra_linea.php','lineasCompraDiv','borrarForm','POST')">Eliminar</a></td>
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
