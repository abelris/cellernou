<?PHP include("../conf.php"); 

if (isset($_POST["eliminar"])) //Si volem eliminar una venta...
{
    
    $sqlSelDel="SELECT SUM(a.cantidad*a.precio_unidad) as totalTiquet,b.idTienda FROM linea_tiquet a inner join tiquets b on a.idVenta=b.id WHERE a.idVenta=".$_POST["idVenta"]." GROUP BY a.idVenta";
    $rsSel=  mysqli_query($bd,$sqlSelDel);
    if ($dataDelQ=mysqli_fetch_array($rsSel))
    {
        $TotalTiquet=$dataDelQ["totalTiquet"];
        $tienda=$dataDelQ["idTienda"];
    }
    
    $sqlIns="insert into tiquets2 (select * from tiquets where id = ".$_POST["idVenta"].")";
    mysqli_query($bd,$sqlIns);
    
    $sqlIns="insert into linea_tiquet2 (select * from linea_tiquet where idVenta = ".$_POST["idVenta"].")";
    mysqli_query($bd,$sqlIns);
    
    
    $delVenta = "DELETE FROM tiquets WHERE id = ".$_POST["idVenta"];
    //echo $delVenta;
    mysqli_query($bd,$delVenta);
    
    $delVenta = "DELETE FROM linea_tiquet WHERE idVenta = ".$_POST["idVenta"];
    //echo $delVenta;
    mysqli_query($bd,$delVenta);
    
    mail('abel@arismesa.com','tiquet '.$_POST["idVenta"].' eliminado de la tienda '.$tienda,'Eliminat tiquet '.$_POST["idVenta"]." amb total de venta: ".number_format($TotalTiquet,2));
    
}


//Muntem la cadena de Cerca
$cerca = " WHERE b.idUsuario=".$_SESSION["idUsuario"]." ";
$cerca = " WHERE a.idTienda=".$_SESSION["idUsuario"]." ";


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
    $fecha_cambiadaB = mktime(23,59,59,$fechaB[1],$fechaB[0],$fechaB[2]);
    $fechaB = date("Y-m-d H:i:s", $fecha_cambiadaB);
    
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
        <script language="javascript" src="/distribucio/js/lightboxOwn.js"></script>
        
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
        <h1>Tiquets</h1>
        <?php
            $sqlSel = "SELECT a.*, b.nombre as cliente FROM tiquets a inner join clientes_tiquet b on a.idCliente=b.id ".$cerca." group by MONTH(),YEAR() order by fecha desc";
            $sqlSel = "SELECT a.*, b.nombre as cliente FROM tiquets a inner join clientes_tiquet b on a.idCliente=b.id ".$cerca." order by fecha desc";
            //echo $sqlSel; 
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST" action="detalle_ventas.php">
                <input type="hidden" name="idVenta">
            </form>
            <form name="modForm" method="POST" action="alta.php">
                <input type="hidden" name="idVenta">
                <input type="hidden" name="idCliente">
                <input type="hidden" name="modificar" value="1">
            </form>
            <form name="productosVenta" method="POST" action="productos-cliente.php">
                <input type="hidden" name="idVenta">
            </form>
            <form name="eliminaForm" method="POST">
                <input type="hidden" name="idVenta">
                <input type="hidden" name="eliminar" value="1">
            </form>
            <a href="alta.php">Alta Tiquets</a><br/><br/>
            
            <form name='buscador' method='POST'>
            <table align='center'>       
                <tr>
                    <td>
             <?PHP
            $sqlCliente="Select * from clientes_tiquet WHERE idUsuario=".$_SESSION["idUsuario"]." order by nombre";
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
            
                            <form name="ventas" metod="post" action="detalle_ventas.php">

            <table border="1" width="100%">
                <tr>
                    <th></th>
                    <th>Id</th>
                    <th>Cliente</th>
                    <th>fecha</th>
                    <th>Total Venta</th>
                    <th>Total Compra</th>
                    <th>Margen</th>
                    <th>Estado</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            $ventaTotal = 0;
            $compraTotal = 0;
            
            $ivaVenta=0;
            $ivaVenta21=0;
            $ivaVenta10=0;
            $ivaVenta4=0;
            
            $ivaCompra=0;
            $ivaCompra21=0;
            $ivaCompra10=0;
            $ivaCompra4=0;
            
            while ($data= mysqli_fetch_array($rsSel))
            {
                
                $totalVenta=0;
                $totalCompra=0;
                $totalVentaIva=0;
                $ventaIVA=0;
                
                if($data["idEstado"]==0)
                    $color="#ff9999";
                elseif ($data["idEstado"]==5)
                    $color="#66ff66";
                else
                    $color="#ff9900";
                ?>
                <tr style="background-color: <?PHP echo $color; ?>">
                    <td><input type="checkbox" name="ventaId[]" value="<?PHP echo $data["id"]; ?>"></td>
                    <td><?PHP echo $data["id"]; ?></td>
                    <td><?PHP echo $data["cliente"]; ?></td>
                    <td><?PHP echo $data["fecha"]; ?></td>
                    <td>
                    <?PHP 
                        //$sqlTotalVenta="SELECT SUM(cantidad*precio_unidad_sin_imp) as total_venta,SUM(cantidad*precio_unidad*iva/100) as total_iva_venta,iva FROM linea_tiquet WHERE idVenta=".$data["id"]." group by idVenta";
                        $sqlTotalVenta="SELECT cantidad*precio_unidad_sin_imp as total_venta,cantidad*precio_unidad_sin_imp*iva/100 as total_iva_venta,iva FROM linea_tiquet WHERE idVenta=".$data["id"]." ";
                        $rsTotalVenta = mysqli_query($bd,$sqlTotalVenta);
                        //echo $sqlTotalVenta;
                        while ($dataTotalVenta=mysqli_fetch_array($rsTotalVenta))
                        {
                            //echo ":::<br>";
                            $totalVenta += number_format($dataTotalVenta["total_venta"],2);
                            $ventaIVA+=number_format($dataTotalVenta["total_iva_venta"],2);
                            $ivaVenta += number_format($dataTotalVenta["total_iva_venta"],2);
                            switch($dataTotalVenta["iva"])
                            {
                                case 21:
                                    $ivaVenta21+=number_format($dataTotalVenta["total_iva_venta"],2);
                                    break;
                                case 10:
                                    $ivaVenta10+=number_format($dataTotalVenta["total_iva_venta"],2);
                                    break;
                                case 4:
                                    $ivaVenta4+=number_format($dataTotalVenta["total_iva_venta"],2);
                                    break;
                                        
                            }
                        }
                        $ventaTotal += $totalVenta;
                        $totalVentaIva = $totalVenta+$ventaIVA;
                        
                        echo $totalVenta."(".$totalVentaIva.")";                        
                        mysqli_free_result($rsTotalVenta);
                    ?></td>
                    <td>
                    <?PHP 
                        //$sqlTotalCompra="SELECT SUM(cantidad*precio_compra) as total_compra,SUM(cantidad*precio_compra*iva/100) as total_iva_compra,iva FROM linea_tiquet WHERE idVenta=".$data["id"]." group by idVenta";
                        $sqlTotalCompra="SELECT cantidad*precio_compra as total_compra,cantidad*precio_compra*iva/100 as total_iva_compra,iva FROM linea_tiquet WHERE idVenta=".$data["id"]." ";
                        $rsTotalCompra = mysqli_query($bd,$sqlTotalCompra);
                        while ($dataTotalCompra=  mysqli_fetch_array($rsTotalCompra))
                        {
                            $totalCompra += number_format($dataTotalCompra["total_compra"],2);
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
                            
                            
                        }
                        $compraTotal += $totalCompra;
                        mysqli_free_result($rsTotalCompra);
                        echo $totalCompra;
                    ?></td>
                    <td>
                        <?PHP
                        if ($totalVenta>0)
                        {
                            $margen = $totalVenta-$totalCompra;
                            $margen_por_cien = $margen / $totalVenta * 100;
                            echo $margen." (".$margen_por_cien.")";
                        }
                        else
                        {
                            echo "0";
                        }
                        ?>
                    </td>
                    <td>
                        <div id='estadoVentasDiv<?PHP echo $data["id"]; ?>'>
                        <?PHP
                        $sqlEstado="Select * from estados_ventas order by id";
                        $rsEstado=mysqli_query($bd,$sqlEstado);
                        ?>
                        <select name="idEstado<?PHP echo $data["id"]; ?>" onchange="FAjaxParam('cambio_estado.php','estadoVentasDiv<?PHP echo $data["id"]; ?>','idVenta=<?PHP echo $data["id"]; ?>&idEstado='+this.value,'POST')">
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
                        <a onclick="document.vendaForm.idVenta.value=<?PHP echo $data["id"]; ?>; document.vendaForm.submit();" target="_blank">Detalles venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <?PHP
                        if ($data["idEstado"]!=5)
                        {
                        ?>
                            <a href="javascript:lightbox('<iframe src=facturar.php?idVenta=<?PHP echo $data["id"]; ?> width=900px frameborder=0 height=500px scrolling=no></iframe>',null);">Facturar</a>

                            <!--a href="javascript:FAjaxParam('pasar_factura.php','estadoVentasDiv<?PHP echo $data["id"]; ?>','idVenta=<?PHP echo $data["id"]; ?>','POST')">Facturar</a-->
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <?PHP
                        }
                        ?>
                        <a href="javascript:if (confirm('Desea eliminar el tiquet <?PHP echo $data["id"]; ?> ')) { document.eliminaForm.idVenta.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit(); } ">Eliminar</a> | <a href="javascript:document.modForm.idVenta.value=<?PHP echo $data["id"]; ?>; document.modForm.idCliente.value=<?PHP echo $data["idCliente"]; ?>; document.modForm.submit();">Modificar</a>
                    </td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
                
           <tr>
               <th colspan="3">Total sin IVA</th>
               
               <th></th>
               <th><b><?PHP echo $ventaTotal; ?></b></th>
               <th><b><?PHP echo $compraTotal; ?></b></th>
               <th><b><?PHP echo number_format(($ventaTotal-$compraTotal),2)." (".number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2).")"; ?></b></th>
               <th>&nbsp;</th>
           </tr>
           <tr>
               <th colspan="3">IVA Total</th>
               
               <th></th>
               <th><b><?PHP echo $ivaVenta; ?></b></th>
               <th><b><?PHP echo $ivaCompra; ?></b></th>
               <th><b><?PHP //echo number_format(($ventaTotal-$compraTotal),2)." (".number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2).")"; ?></b></th>
               <th>&nbsp;</th>
           </tr>
           <tr>
               <th colspan="3">IVA 21% Total</th>
               
               <th></th>
               <th><b><?PHP echo $ivaVenta21; ?></b></th>
               <th><b><?PHP echo $ivaCompra21; ?></b></th>
               <th><b><?PHP //echo number_format(($ventaTotal-$compraTotal),2)." (".number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2).")"; ?></b></th>
               <th>&nbsp;</th>
           </tr>
           <tr>
               <th colspan="3">IVA 10% Total</th>
               
               <th></th>
               <th><b><?PHP echo $ivaVenta10; ?></b></th>
               <th><b><?PHP echo $ivaCompra10; ?></b></th>
               <th><b><?PHP //echo number_format(($ventaTotal-$compraTotal),2)." (".number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2).")"; ?></b></th>
               <th>&nbsp;</th>
           </tr>
           <tr>
               <th colspan="3">IVA 4% Total</th>
               
               <th></th>
               <th><b><?PHP echo $ivaVenta4; ?></b></th>
               <th><b><?PHP echo $ivaCompra4; ?></b></th>
               <th><b><?PHP //echo number_format(($ventaTotal-$compraTotal),2)." (".number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2).")"; ?></b></th>
               <th>&nbsp;</th>
           </tr>
           </table>
                                <center><input type="submit" value="facturar"></center>
                               </form> 
                                <br/><br/>
            <a href="alta.php">Alta Venda</a>
    </body>
</html>
