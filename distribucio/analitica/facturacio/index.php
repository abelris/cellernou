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

$fechaIni=date('d-m-Y');
$fechaFin=date('d-m-Y');


if (isset($_POST["fechaDe"]))
{
    $fechaIni=$_POST["fechaDe"];
    $fechaFin=$_POST["fechaHasta"];
    
    $fechaA = explode("-",$_POST["fechaDe"]);
    $fecha_cambiada = mktime(00,00,00,$fechaA[1],$fechaA[0],$fechaA[2]);
    $fechaA = date("Y-m-d", $fecha_cambiada);
    
    $fechaB = explode("-",$_POST["fechaHasta"]);
    $fecha_cambiadaB = mktime(00,00,00,$fechaB[1],$fechaB[0],$fechaB[2]);
    $fechaB = date("Y-m-d", $fecha_cambiadaB);
    
    $cerca.=" AND a.fecha >= '".$fechaA."' AND a.fecha <= '".$fechaB."'";
}
else
    $cerca.=" and a.fecha > DATE_ADD(NOW(), INTERVAL -1 DAY)";


//echo $cerca;
?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;">
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
            $sqlSel = "SELECT c.nombre,c.nif,c.cp,c.direccion,c.poblacion,SUM(b.cantidad*b.precio_unidad) as total_venta, SUM(b.cantidad*b.precio_unidad*iva/100) as total_iva, a.idCliente FROM facturas a inner join linea_factura b on a.id=b.idFactura inner join clientes c on a.idCliente=c.id ".$cerca." AND a.idCliente in (8,9,12,24,39,41,50,61,64,85,97,135,144,157,175,183,202,206,220)  group by a.idCliente";
            $sqlSel = "SELECT c.nombre,SUM(b.cantidad*b.precio_unidad) as total_venta, SUM(b.cantidad*b.precio_unidad*iva/100) as total_iva FROM facturas a inner join linea_factura b on a.id=b.idFactura inner join clientes c on a.idCliente=c.id  ".$cerca." group by a.idCliente";
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
                
            De: <input type="text" name="fechaDe" id="datepickerDe" value="<?PHP echo $fechaIni; ?>">
            Hasta: <input type="text" name="fechaHasta" id="datepickerHasta" value="<?PHP echo $fechaFin; ?>">        
                    
                    <input type="submit" value="Buscar">
                    
                    </td>
            
            </tr>
            </table>
            </form>
            <table border="1" width="100%">
                <tr>
                    <th>Id</th>
                    <th>Cliente</th>
                    <th>Total Venta</th>
                    <th>Total Compra</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            $ventaTotal = 0;
            $ivaTotal = 0;
            while ($data= mysqli_fetch_array($rsSel))
            {
                
                if($data["idEstado"]==0)
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
                    <td><?PHP echo $data["idCliente"]; ?></td>
                    <td><?PHP echo $data["nombre"]." - ".$data["nif"]." - ".$data["direccion"]." - ".$data["cp"]." - ".$data["poblacion"]; ?></td>
                    <td align="right">
                    <?PHP 
                        
                    $totalVenta = $data["total_venta"];
                    $totalIVA = $data["total_iva"];
                    $ventaTotal += $totalVenta;
                    $ivaTotal +=$totalIVA;
                        
                    echo number_format($totalVenta,2);
                    
                    ?></td>
                    <td align="right">
                    <?PHP 
                        echo number_format($totalIVA,2);
                        /*$sqlTotalCompra="SELECT SUM(cantidad*precio_medio) as total_compra FROM linea_factura a inner join productos b on a.idProducto=b.id WHERE a.idFactura=".$data["id"]." group by idFactura";
                        $rsTotalCompra = mysqli_query($bd,$sqlTotalCompra);
                        if ($dataTotalCompra=  mysqli_fetch_array($rsTotalCompra))
                        {
                            $totalCompra = number_format($dataTotalCompra["total_compra"],2);
                            $compraTotal += $totalCompra;
                        }
                        mysqli_free_result($rsTotalCompra);
                        echo $totalCompra;*/
                    ?></td>
                    
                    <td>
                        <?PHP
                        
                        //echo $totalVenta;
                        //echo $totalIVA;
                        echo number_format($totalVenta+$totalIVA,2);
                        if ($pagosCuenta!=0)
                        {
                            
                        }
                        ?>
                        <!--a href="javascript:document.facturaForm.idFactura.value=<?PHP echo $data["id"]; ?>; document.facturaForm.submit();">Detalles Factura</a-->
                        <!--&nbsp;&nbsp;|&nbsp;&nbsp;-->
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
           <tr>
               <th>Total</th>
               <th></th>
               <th align="right"><b><?PHP echo number_format($ventaTotal,2); ?></b></th>
               <th align="right"><b><?PHP echo number_format($ivaTotal,2); ?></b></th>
               <th align="right"><b><?PHP echo number_format($ventaTotal+$ivaTotal,2); ?></b></th>
           </tr>
           </table><br/><br/>
    </body>
</html>
