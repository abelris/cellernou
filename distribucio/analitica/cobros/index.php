<?PHP include("../../conf.php"); 



$cerca = " WHERE 1 ";


switch($_POST["tipo"])
{
    case 0:
        $cerca.="";
        break;
    case 1:
        $cerca.=" AND a.idEstado !=1";
        break;
    case 2:
        $cerca.=" AND a.idEstado = 1";
        break;
    
}


if (isset($_POST["fechaDe"]))
{
    $fechaA = explode("-",$_POST["fechaDe"]);
    $fecha_cambiada = mktime(00,00,00,$fechaA[1],$fechaA[0],$fechaA[2]);
    $fechaA = date("Y-m-d", $fecha_cambiada);
    
    $fechaB = explode("-",$_POST["fechaHasta"]);
    $fecha_cambiadaB = mktime(00,00,00,$fechaB[1],$fechaB[0],$fechaB[2]);
    $fechaB = date("Y-m-d", $fecha_cambiadaB);
    
    $cerca.=" AND a.fecha_pago >= '".$fechaA."' AND a.fecha_pago <= '".$fechaB."'";
}
else
    $cerca.=" and a.fecha_pago > DATE_ADD(NOW(), INTERVAL -1 DAY)";

//echo $cerca;
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
        <script language="javascript" src="/distribucio/js/ajax.js"></script>
        
        <script language="javascript" src="/distribucio/js/ajax.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
        <script>
        $(function() {
            $("#datepickerDe").datepicker({dateFormat: "dd-mm-yy"});
            $("#datepickerHasta").datepicker({dateFormat: "dd-mm-yy"});
        });
        </script>
        
    </head>
    <body>
        <?php include("../../includes/header.php"); ?>
        <h1>Facturas</h1>
        <?php
            $sqlSel = "SELECT a.fecha_pago as fecha, a.id, a.idEstado, a.idCliente,c.nombre FROM facturas a inner join clientes c on a.idCliente=c.id ".$cerca." and c.idUsuario='".$_SESSION["idUsuario"]."' and a.idEstado=1  group by a.id order by a.idCliente,a.fecha_pago";
            //$sqlSel = "SELECT c.nombre,SUM(b.cantidad*b.precio_unidad) as total_venta, SUM(b.cantidad*b.precio_unidad*iva/100) as total_iva FROM facturas a inner join linea_factura b on a.id=b.idFactura inner join clientes c on a.idCliente=c.id  ".$cerca." group by a.idCliente";
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
            $sqlCliente="Select * from clientes WHERE idUsuario=".$_SESSION["idUsuario"]." order by nombre";
            $rsCliente=mysqli_query($bd,$sqlCliente);
            ?>
            Cliente: <select name="idCliente" onchange='document.buscador.submit();'>
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
            </select>
            <select name="tipo" onchange='document.buscador.submit();'>
                <option value="0" <?PHP if ($_POST["tipo"]==0) { echo "selected='selected'"; } ?>>Todas</option>
                <option value="1" <?PHP if ($_POST["tipo"]==1) { echo "selected='selected'"; } ?>>Pendiente de cobro</option>
                <option value="2" <?PHP if ($_POST["tipo"]==2) { echo "selected='selected'"; } ?>>Cobrada</option>
            </select>
                   
            De: <input type="text" name="fechaDe" id="datepickerDe" value="<?PHP echo date('d-m-Y'); ?>">
            Hasta: <input type="text" name="fechaHasta" id="datepickerHasta" value="<?PHP echo date('d-m-Y'); ?>">        
                    
                    <input type="submit" value="Buscar">
                    
                   </td>
            
            </tr>
            </table>
            </form>
            
            <form name="cobrosForm" method="POST">
            <table border="1" width="100%">
                <tr>
                    <th style="width: 15px;">&nbsp;</th>
                    <th>Id</th>
                    <th>Cliente</th>
                    <th>Total Venta</th>
                    <th>Total Compra</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            $ventaTotal = 0;
            $compraTotal = 0;
            $ivaTotal = 0;
            $totalVentaFAC=0;
            while ($data= mysqli_fetch_array($rsSel))
            {
                if($data["idEstado"]==2)
                    $color="#ff9999";
                else
                    $color="#66ff66";
                        
                $pagosCuenta=0;
                if ($_POST["tipo"]==1) //Si queremos saber las facturas pendientes de cobro.
                {
                    $sqlPagos = "SELECT SUM(importe) as totalPagos FROM pagos_cuenta a inner join facturas b on a.idFactura=b.id WHERE a.idEstado=2 and b.idCliente = ".$data["idCliente"];
                    $rsPagos = mysqli_query($bd,$sqlPagos);
                    if($dataPagos=mysqli_fetch_array($rsPagos))
                    {
                        $pagosCuenta=$dataPagos["totalPagos"];
                    }
                    
                }
                    
                    
                ?>
                <tr style="background-color: <?PHP echo $color; ?>">
                    <td><input type="checkbox" name="cobros[]" value="<?PHP echo $data["id"]; ?>"></td>
                    <td><?PHP echo $data["id"]; ?></td>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td align="right">
                    <?PHP 
                    
                    
                    
                    
                    
                    
                    
                    
                    $sqlTotalVenta="SELECT SUM(cantidad*precio_unidad) as total_venta FROM linea_factura WHERE idFactura=".$data["id"]." group by idFactura";
                    $rsTotalVenta = mysqli_query($bd,$sqlTotalVenta);
                    if ($dataTotalVenta=  mysqli_fetch_array($rsTotalVenta))
                    {
                        $totalVentaFAC = number_format($dataTotalVenta["total_venta"],2);
                        //$ventaTotal += $totalVenta;
                    }
                    mysqli_free_result($rsTotalVenta);
                    echo number_format($totalVentaFAC,2);
                    $ventaTotal += $totalVentaFAC;
                    
                    ?></td>
                    
                    
                    <td>
                        <?PHP
                        
                        $sqlTotalCompra="SELECT SUM(cantidad*precio_medio) as total_compra FROM linea_factura a inner join productos b on a.idProducto=b.id WHERE a.idFactura=".$data["id"]." group by idFactura";
                        $rsTotalCompra = mysqli_query($bd,$sqlTotalCompra);
                        if ($dataTotalCompra=  mysqli_fetch_array($rsTotalCompra))
                        {
                            $totalCompra = number_format($dataTotalCompra["total_compra"],2);
                            $compraTotal += $totalCompra;
                        }
                        mysqli_free_result($rsTotalCompra);
                        echo $totalCompra;
                        
                        //$compraTotal += $totalCompra;
                        
                        echo " (".number_format((($totalVentaFAC-$totalCompra)*100/$totalVentaFAC),2).")";0                        
                        ?>
                        <!--a href="javascript:document.facturaForm.idFactura.value=<?PHP echo $data["id"]; ?>; document.facturaForm.submit();">Detalles Factura</a-->
                        <!--&nbsp;&nbsp;|&nbsp;&nbsp;-->
                        <!--a href="javascript:document.productosCliente.idCliente.value=<?PHP echo $data["id"]; ?>;document.productosCliente.nombreCliente.value='<?PHP echo $data["nombre"]; ?>'; document.productosCliente.submit();">Productos Clietne</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.preciosVendaForm.idCliente.value=<?PHP echo $data["id"]; ?>;document.preciosVendaForm.nombreCliente.value='<?PHP echo $data["nombre"]; ?>'; document.preciosVendaForm.submit();">Precios Venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.eliminaForm.idCliente.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a-->
                    </td>
                    <td align="right">
                    <?PHP 
                        echo number_format(($totalVentaFAC-$totalCompra),2);
                        
                    ?></td>
                    <td align="right">
                    <?PHP 
                        echo $data["fecha"];
                        
                    ?></td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           <tr>
               <th>Total</th>
               <th>&nbsp;</th>
               <th>&nbsp;</th>
               <th align="right"><b><?PHP echo number_format($ventaTotal,2); ?></b></th>
               <th align="right"><b><?PHP echo number_format($compraTotal,2)." (".number_format(($ventaTotal-$compraTotal)*100/$ventaTotal,2).")"; ?></b></th>
               <th align="right"><b><?PHP echo number_format($ventaTotal-$compraTotal,2); ?></b></th>
               <th>&nbsp;</th>
           </tr>
           <tr>
               <th colspan="6"><input type="submit" value="contabilizar"></th>
           </tr>
           </table>
           </form>     
                <br/><br/>
    </body>
</html>
