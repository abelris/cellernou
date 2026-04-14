<?PHP include("../conf.php"); 


$cerca = " WHERE b.idUsuario=".$_SESSION["idUsuario"]." ";

if ($_POST["idCliente"]!=0)
    $cerca.=" AND a.idCliente = ".$_POST["idCliente"];



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
    $cerca.=" and fecha > DATE_ADD(NOW(), INTERVAL -1 DAY)";


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
        <?php

 include("../includes/header.php"); ?>
        <h1>Facturas</h1>
        <?php
            $sqlSel = "SELECT a.*, b.nombre as cliente FROM facturas a inner join clientes b on a.idCliente=b.id ".$cerca." order by fecha desc";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="facturaForm" method="POST" action="detalle_ventas.php" target="_blank">
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
                    <th>IdVenta</th>
                    <th>Cliente</th>
                    <th>fecha</th>
                    <th>Vencimiento</th>
                    <th>Total Venta</th>
                    <th>Total IVA 21%</th>
                    <th>Total IVA 10%</th>
                    <th>Total IVA 4%</th>
                    <th>Total Venta IVA</th>
                    <th>Total Compra</th>
                    <th>Estado</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            $ventaTotal = 0;
            $compraTotal = 0;
            $ventaTotalIVA = 0;
            $compraTotalIVA = 0;
            
            $ivaVenta=0;
            $ivaVenta21=0;
            $ivaVenta10=0;
            $ivaVenta4=0;
            
            $ivaVenta21_t=0;
            $ivaVenta10_t=0;
            $ivaVenta4_t=0;
            
            
            $ivaCompra=0;
            $ivaCompra21=0;
            $ivaCompra10=0;
            $ivaCompra4=0;
            
            
            while ($data= mysqli_fetch_array($rsSel))
            {
                
            $ivaVenta21_t=0;
            $ivaVenta10_t=0;
            $ivaVenta4_t=0;
                
            $impVenta21_b=0;
            $impVenta10_b=0;
            $impVenta4_b=0;
            
                if($data["idEstado"]==0)
                    $color="#ff9999";
                if($data["idEstado"]==1)
                    $color="#66ff66";
                if($data["idEstado"]==2)
                    $color="orange";
                        
                
                ?>
                <tr style="background-color: <?PHP echo $color; ?>">
                    <td><?PHP echo $data["id"]; ?></td>
                    <td><?PHP echo $data["idVenta"]; ?></td>
                    <td><?PHP echo $data["cliente"]; ?></td>
                    <td><?PHP echo $data["fecha"]; ?></td>
                    <td><?PHP echo $data["fecha_vencimiento"]; ?></td>

                    <td>
                    <?PHP 
                        $sqlTotalVenta="SELECT SUM(cantidad*precio_unidad) as total_venta,SUM(cantidad*precio_unidad*iva/100) as total_iva_venta,iva FROM linea_factura WHERE idFactura=".$data["id"]." group by idFactura";
                        $rsTotalVenta = mysqli_query($bd,$sqlTotalVenta);
                        if ($dataTotalVenta=  mysqli_fetch_array($rsTotalVenta))
                        {
                            $totalVenta = $dataTotalVenta["total_venta"];
                            $ivaVenta += number_format($dataTotalVenta["total_iva_venta"],2);
                            
                            switch($dataTotalVenta["iva"])
                            {
                                case 21:
                                    $ivaVenta21+=number_format($dataTotalVenta["total_iva_venta"],2);
                                    //$ivaVenta21_t=number_format($dataTotalVenta["total_iva_venta"],2);
                                    break;
                                case 10:
                                    $ivaVenta10+=number_format($dataTotalVenta["total_iva_venta"],2);
                                    //$ivaVenta10_t=number_format($dataTotalVenta["total_iva_venta"],2);
                                    break;
                                case 4:
                                    $ivaVenta4+=number_format($dataTotalVenta["total_iva_venta"],2);
                                    //$ivaVenta4_t=number_format($dataTotalVenta["total_iva_venta"],2);
                                    break;
                                        
                            }
                            
                            $ventaTotal += $totalVenta;
                        }
                        mysqli_free_result($rsTotalVenta);
                        echo number_format($totalVenta,2);
                    ?></td>
                    <td style="background-color:#DDD;">
                        <?PHP 
                        $sqlIva21="SELECT SUM(cantidad*precio_unidad) as imp21,SUM(cantidad*precio_unidad*iva/100) as total_iva_venta FROM linea_factura WHERE idFactura=".$data["id"]." AND iva=21 group by idFactura ";
                        $rsIva21=mysqli_query($bd,$sqlIva21);
                        if ($dataIva21 = mysqli_fetch_array($rsIva21))
                        {
                            $ivaVenta21_t = $dataIva21["total_iva_venta"];
                            $impVenta21_b = $dataIva21["imp21"];
                        }
                        
                        if ($impVenta21_b!=0)
                        {
                            echo "(".number_format($impVenta21_b,2).") ";
                            echo number_format($ivaVenta21_t,2); 
                        }
                        ?></td>
                    <td style="background-color:#FFF;">
                        <?PHP 
                        $sqlIva10="SELECT SUM(cantidad*precio_unidad) as imp10,SUM(cantidad*precio_unidad*iva/100) as total_iva_venta FROM linea_factura WHERE idFactura=".$data["id"]." AND iva=10 group by idFactura ";
                        $rsIva10=mysqli_query($bd,$sqlIva10);
                        if ($dataIva10 = mysqli_fetch_array($rsIva10))
                        {
                            $ivaVenta10_t = $dataIva10["total_iva_venta"];
                            $impVenta10_b = $dataIva10["imp10"];
                        }
                        
                        if ($impVenta10_b!=0)
                        {
                            echo "(".number_format($impVenta10_b,2).") ";
                            echo number_format($ivaVenta10_t,2); 
                        }
                        ?>
                    </td>
                    <td  style="background-color:#DDD;">
                        <?PHP 
                        $sqlIva4="SELECT SUM(cantidad*precio_unidad) as imp4,SUM(cantidad*precio_unidad*iva/100) as total_iva_venta FROM linea_factura WHERE idFactura=".$data["id"]." AND iva=4 group by idFactura ";
                        $rsIva4=mysqli_query($bd,$sqlIva4);
                        if ($dataIva4 = mysqli_fetch_array($rsIva4))
                        {
                            $ivaVenta4_t = $dataIva4["total_iva_venta"];
                            $impVenta4_b = $dataIva4["imp4"];
                        }
                        
                        if ($impVenta4_b!=0)
                        {
                            echo "(".number_format($impVenta4_b,2).") ";
                            echo number_format($ivaVenta4_t,2); 
                        }    
                            ?></td>
                    <td><?PHP echo number_format($totalVenta+$ivaVenta21_t+$ivaVenta10_t+$ivaVenta4_t,2); ?></td>
                    <td>
                    <?PHP 
                        $sqlTotalCompra="SELECT SUM(cantidad*precio_medio) as total_compra,SUM(cantidad*precio_medio*a.iva/100) as total_iva_compra,a.iva FROM linea_factura a inner join productos b on a.idProducto=b.id WHERE a.idFactura=".$data["id"]." group by idFactura";
                        $rsTotalCompra = mysqli_query($bd,$sqlTotalCompra);
                        if ($dataTotalCompra=  mysqli_fetch_array($rsTotalCompra))
                        {
                            $totalCompra = number_format($dataTotalCompra["total_compra"],2);
                            $ivaCompra += number_format($dataTotalCompra["total_iva_compra"],2);
                            switch($dataTotalCompra["iva"])
                            {
                                case 21:
                                    $ivaCompra21+=number_format($dataTotalCompra["total_iva_compra"],2);
                                    break;
                                case 10:
                                    $ivaCompra10+=number_format($dataTotalCompra["total_iva_compra"],2);
                                    break;
                                case 4:
                                    $ivaCompra4+=number_format($dataTotalCompra["total_iva_compra"],2);
                                    break;
                                        
                            }
                            $compraTotal += $totalCompra;
                        }
                        mysqli_free_result($rsTotalCompra);
                        echo $totalCompra;
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
                        <a style="color:blue; cursor: pointer; text-decoration: underline;" onclick="document.facturaForm.idFactura.value=<?PHP echo $data["id"]; ?>; document.facturaForm.submit();" target="_blank">Detalles Factura</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:lightbox('<iframe src=pagos_cuenta.php?idFactura=<?PHP echo $data["id"]; ?> width=900px frameborder=0 height=500px scrolling=no></iframe>',null);">Pagos</a>
                        <!---&nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.preciosVendaForm.idCliente.value=<?PHP echo $data["id"]; ?>;document.preciosVendaForm.nombreCliente.value='<?PHP echo $data["nombre"]; ?>'; document.preciosVendaForm.submit();">Precios Venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.eliminaForm.idCliente.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a-->
                    </td>
                    <td>
                        <div id="contabilizadaDiv<?PHP echo $data["id"]; ?>"></div>
                        <input type="checkbox" name="contabilizada<?PHP echo $data["id"]; ?>" value="1" <?PHP if ($data["contabilizada"]==1){ echo "checked"; } ?> onchange="FAjaxParam('contabilizar.php','contabilizadaDiv<?PHP echo $data["id"]; ?>','idFactura=<?PHP echo $data["id"]; ?>&contabilizada='+this.value,'POST')">
                    </td>    
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           <tr>
               <th>Total Sin IVA</th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $ventaTotal; ?></b></th>
               <th></th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $compraTotal; ?></b></th>
               <th><b><?PHP //echo number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2); ?></b></th>
               <th>&nbsp;</th>
           </tr>
           <tr>
               <th>Total IVA</th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $ivaVenta; ?></b></th>
               <th></th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $ivaCompra; ?></b></th>
               <th><b><?PHP //echo number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2); ?></b></th>
               <th>&nbsp;</th>
           </tr>
           <tr>
               <th>IVA 21% Total</th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $ivaVenta21; ?></b></th>
               <th></th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $ivaCompra21; ?></b></th>
               <th><b><?PHP //echo number_format(($ventaTotal-$compraTotal),2)." (".number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2).")"; ?></b></th>
               <th>&nbsp;</th>
           </tr>
           <tr>
               <th>IVA 10% Total</th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $ivaVenta10; ?></b></th>
               <th></th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $ivaCompra10; ?></b></th>
               <th><b><?PHP //echo number_format(($ventaTotal-$compraTotal),2)." (".number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2).")"; ?></b></th>
               <th>&nbsp;</th>
           </tr>
           <tr>
               <th>IVA 4% Total</th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $ivaVenta4; ?></b></th>
               <th></th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $ivaCompra4; ?></b></th>
               <th><b><?PHP //echo number_format(($ventaTotal-$compraTotal),2)." (".number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2).")"; ?></b></th>
               <th>&nbsp;</th>
           </tr>
           </table><br/><br/>
    </body>
</html>
