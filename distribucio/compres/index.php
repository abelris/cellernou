<?PHP include("../conf.php"); 

if (isset($_POST["eliminar"])) //Si volem eliminar una venta...
{
    $delVenta = "DELETE FROM compras WHERE id = ".$_POST["idCompra"];
    //echo $delVenta;
    mysqli_query($bd,$delVenta);
    
    $delVenta = "DELETE FROM linea_compra WHERE idCompra = ".$_POST["idCompra"];
    //echo $delVenta;
    mysqli_query($bd,$delVenta);
}


//Muntem la cadena de Cerca

if ($_POST["idProveedor"]!=0)
    $cerca=" AND a.idProveedor = ".$_POST["idProveedor"];



?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
                <link rel="stylesheet" type="text/css" href="/distribucio/css/estils.css" />
        <link rel="stylesheet" type="text/css" href="/distribucio/css/lightboxOwn.css" />        
        <script language="javascript" src="/distribucio/js/ajax.js"></script>
        <script language="javascript" src="/distribucio/js/lightboxOwn.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>

    </head>
    <body>
        <?php

 include("../includes/header.php"); ?>
        <h1>Compras</h1>
        <?php
            $sqlSel = "SELECT a.*, b.nombre as proveedor FROM compras a inner join proveedores b on a.idProveedor=b.id WHERE 1 ".$cerca." order by a.fecha desc";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="detalleForm" method="POST" action="detalle_compras.php">
                <input type="hidden" name="idCompra">
            </form>
            <form name="productosCompra" method="POST" action="productos-cliente.php">
                <input type="hidden" name="idCompra">
            </form>
            
            <form name="eliminaForm" method="POST">
                <input type="hidden" name="idCompra">
                <input type="hidden" name="eliminar" value="1">
            </form>
            <a href="alta.php">Alta Compra</a><br/><br/>
            <form name='buscador' method='POST'>
            <table align='center'>       
                <tr>
                    <td>
             <?PHP
            $sqlCliente="Select * from proveedores order by nombre";
            $rsCliente=mysqli_query($bd,$sqlCliente);
            ?>
            Proveedor: <select name="idProveedor" onchange='document.buscador.submit();'>
                <option value='0'>Todos</option>
            <?PHP
            while($dataCliente=mysqli_fetch_array($rsCliente))
            {
                ?>
                <option value="<?PHP echo $dataCliente["id"]; ?>" <?PHP if ($dataCliente["id"]==$_POST["idCliente"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataCliente["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsCliente);
            ?>
            </select></td>
            
            </tr>
            </table>
            </form>
            <table border="1" width="100%">
                <tr>
                    <th>Id</th>
                    <th>Referencia</th>
                    <th>Proveedor</th>
                    <th>fecha</th>
                    <th>fecha vencimiento</th>
                    <th>Total Compra IVA</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            while ($data= mysqli_fetch_array($rsSel))
            {
                
                
                if($data["idEstado"]==0)
                    $color="#ff9999";
                elseif ($data["idEstado"]==3)
                    $color="#66ff66";
                else
                    $color="#ff9900";
                
                ?>
                <tr style="background-color: <?PHP echo $color; ?>">
                    <td><?PHP echo $data["id"]; ?></td>
                    <td><?PHP echo $data["referencia"]; ?></td>
                    <td><?PHP echo $data["proveedor"]; ?></td>
                    <td><?PHP echo $data["fecha"]; ?></td>
                    <td><?PHP echo $data["fecha_vencimiento"]; ?></td>
                    <td>
                    <?PHP 
                        echo $data["total"];
                    ?></td>
                    <td>
                        <a href="javascript:document.detalleForm.idCompra.value=<?PHP echo $data["id"]; ?>; document.detalleForm.submit();">Ver detalle</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <?PHP
                        if ($data["idEstado"]!=3)
                        {
                        ?>
                            <a href="javascript:lightbox('<iframe src=facturar.php?idCompra=<?PHP echo $data["id"]; ?> width=900px frameborder=0 height=500px scrolling=no></iframe>',null);">Facturar</a>

                            <!--a href="javascript:FAjaxParam('pasar_factura.php','estadoVentasDiv<?PHP echo $data["id"]; ?>','idVenta=<?PHP echo $data["id"]; ?>','POST')">Facturar</a-->
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <?PHP
                        }
                        ?>
                        <a href="javascript:document.eliminaForm.idCompra.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a> 

                        <!--a href="javascript:document.productosCliente.idCliente.value=<?PHP echo $data["id"]; ?>;document.productosCliente.nombreCliente.value='<?PHP echo $data["nombre"]; ?>'; document.productosCliente.submit();">Productos Clietne</a>
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
           </table><br/><br/>
            <a href="alta.php">Alta Compra</a>
    </body>
</html>
