<?PHP include("../conf.php"); 


$cerca = "";

//WHERE b.idUsuario=".$_SESSION["idUsuario"]." ";

if ($_POST["idProveedor"]!=0)
    $cerca=" WHERE a.idProveedor = ".$_POST["idProveedor"];

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
        <h1>Facturas</h1>
        <?php
            $sqlSel = "SELECT a.*, b.nombre as proveedor FROM facturas_compra a inner join proveedores b on a.idProveedor=b.id ".$cerca." order by fecha desc";
            //echo $sqlSel; 
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="facturaForm" method="POST" action="detalle_ventas.php">
                <input type="hidden" name="idFactura">
            </form>
            <form name="productosVenta" method="POST" action="productos-cliente.php">
                <input type="hidden" name="idVenta">
            </form>
            <form name="eliminaForm" method="POST" action="borrar.php">
                <input type="hidden" name="idVenta">
            </form>
        
            <form name='buscador' method='POST'>
            <table align='center'>       
                <tr>
                    <td>
             <?PHP
            $sqlCliente="Select * from proveedores order by nombre";
            $rsCliente=mysqli_query($bd,$sqlCliente);
            ?>
            Proveidor: <select name="idProveedor" onchange='document.buscador.submit();'>
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
                    <th>Num. Factura</th>
                    <th>Proveedor</th>
                    <th>fecha</th>
                    <th>Vencimiento</th>
                    <th>Total Compra</th>
                    <th>Estado</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            $ventaTotal = 0;
            $compraTotal = 0;
            $ventaTotalIVA = 0;
            $compraTotalIVA = 0;
            while ($data= mysqli_fetch_array($rsSel))
            {
                
                if($data["idEstado"]==0)
                    $color="#ff9999";
                else
                    $color="#66ff66";
                        
                
                ?>
                <tr style="background-color: <?PHP echo $color; ?>">
                    <td><?PHP echo $data["id"]; ?></td>
                    <td><?PHP echo $data["numFactura"]; ?></td>
                    <td><?PHP echo $data["proveedor"]; ?></td>
                    <td><?PHP echo $data["fecha"]; ?></td>
                    <td><?PHP echo $data["fecha_vencimiento"]; ?></td>                    
                    
                    <td>
                    <?PHP 
                        echo $data["total"];
                    ?></td>
                    <td>
                        <div id='estadoFacturasDiv<?PHP echo $data["id"]; ?>'>
                        <?PHP
                        $sqlEstado="Select * from estados_facturas order by id";
                        $rsEstado=mysqli_query($bd,$sqlEstado);
                        ?>
                        <select name="idEstado<?PHP echo $data["id"]; ?>" onchange="FAjaxParam('cambio_estado.php','estadoFacturasDiv<?PHP echo $data["id"]; ?>','idFactura=<?PHP echo $data["id"]; ?>&idEstado='+this.value,'POST')">
                        <?PHP
                        while($dataEstado=mysqli_fetch_array($rsEstado))
                        {
                            ?>
                            <option value="<?PHP echo $dataEstado["id"]; ?>" <?PHP if ($dataEstado["id"]==$data["idEstado"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataEstado["id"]."-".$dataEstado["nombre"]; ?></option>
                            <?PHP
                        }
                        mysqli_free_result($rsEstado);
                        ?>
                        </select>    
                        </div>
                    </td>
                    <td>
                        <a href="javascript:document.facturaForm.idFactura.value=<?PHP echo $data["id"]; ?>; document.facturaForm.submit();" target="_blank">Detalles Factura</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:lightbox('<iframe src=pagos_cuenta.php?idFactura=<?PHP echo $data["id"]; ?> width=900px frameborder=0 height=500px scrolling=no></iframe>',null);">Pagos</a>
                        <!---&nbsp;&nbsp;|&nbsp;&nbsp;
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
               <th>&nbsp;</th>
               <th>&nbsp;</th>
               <th></th>
               <th></th>
               <th>&nbsp;</th>
               <th><b><?PHP echo $compraTotal; ?></b></th>
               <th>&nbsp;</th>
               <th>&nbsp;</th>
           </tr>
           </table><br/><br/>
    </body>
</html>
