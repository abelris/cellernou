<?PHP include("../conf.php"); ?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<?PHP
//Detalles de la compra
$sqlCompra="SELECT a.id as idCompra,a.referencia as referencia,b.nombre as nombre FROM compras a INNER JOIN proveedores b ON a.idProveedor=b.id WHERE a.id = ".$_POST["idCompra"];
//echo $sqlCompra;
$rsCompra=mysqli_query($bd,$sqlCompra);
if ($dataCompra=mysqli_fetch_array($rsCompra))
{
    $id=$dataCompra["idCompra"];
    $referencia = $dataCompra["referencia"];
    $nombre = $dataCompra["nombre"];
}
mysqli_free_result($rsCompra);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php include("../includes/header.php"); ?>
        <h1>Detalle de la Compra <?PHP echo $referencia." (".$id.")"; ?></h1>
        <?php
            $sqlSel = "SELECT a.*,b.nombre as producto FROM linea_compra a inner join productos b on a.idProducto=b.id WHERE a.idCompra=".$_POST["idCompra"]." order by a.id";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST" action="venda.php">
                <input type="hidden" name="idCompra">
            </form>
            <form name="productosCompra" method="POST" action="productos-cliente.php">
                <input type="hidden" name="idCompra">
            </form>
            <form name="eliminaForm" method="POST" action="borrar.php">
                <input type="hidden" name="idCompra">
            </form>
            <a href="alta.php">Alta Compra</a><br/><br/>
            <table border="1" width="100%">
                <tr>
                    <th>Id</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio de compra</th>
                    <th>Descuento</th>
                    <th>Precio final</th>
                    <th>Total</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            $totalCompra=0;
            while ($data= mysqli_fetch_array($rsSel))
            {
                ?>
                <tr>
                    <td><?PHP echo $data["id"]; ?></td>
                    <td><?PHP echo $data["producto"]; ?></td>
                    <td><?PHP echo $data["cantidad"]; ?></td>
                    <td><?PHP echo $data["precio_unidad_sin_desc"]; ?></td>
                    <td><?PHP echo $data["descuento"]; ?></td>
                    <td><?PHP echo $data["precio_unidad"]; ?></td>
                    <td>
                    <?PHP 
                        $totalLineaCompra = number_format($data["cantidad"]*$data["precio_unidad"],3);
                        $totalCompra += $data["cantidad"]*$data["precio_unidad"];
                        echo $totalLineaCompra;
                    ?></td>
                    <td>
                        <!--a href="javascript:document.vendaForm.idCliente.value=<?PHP echo $data["id"]; ?>; document.vendaForm.submit();">Nova venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.productosCliente.idCliente.value=<?PHP echo $data["id"]; ?>;document.productosCliente.nombreCliente.value='<?PHP echo $data["nombre"]; ?>'; document.productosCliente.submit();">Productos Clietne</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.preciosVendaForm.idCliente.value=<?PHP echo $data["id"]; ?>;document.preciosVendaForm.nombreCliente.value='<?PHP echo $data["nombre"]; ?>'; document.preciosVendaForm.submit();">Precios Venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.eliminaForm.idCliente.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a-->
                    </td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           <tr>
               <td align="right" colspan="8">
                   <?PHP echo $totalCompra; ?>
               </td>
           </tr>
           </table><br/><br/>
           
            <a href="alta.php">Alta Compra</a>
    </body>
</html>
