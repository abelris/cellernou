<?PHP include("../conf.php"); 

if (isset($_POST["eliminar"])) //Si volem eliminar una venta...
{
    $delVenta = "DELETE FROM ventas WHERE id = ".$_POST["idVenta"];
    //echo $delVenta;
    mysqli_query($bd,$delVenta);
    
    $delVenta = "DELETE FROM linea_venta WHERE idVenta = ".$_POST["idVenta"];
    //echo $delVenta;
    mysqli_query($bd,$delVenta);
}


//Muntem la cadena de Cerca
$cerca = " WHERE b.idUsuario=".$_SESSION["idUsuario"]." ";

if ($_POST["idCliente"]!=0)
    $cerca.=" AND a.idCliente = ".$_POST["idCliente"];


if (isset($_POST["fechaDe"]))
{
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
        
        <script language='javascript'>
            function facturarM()
            {
                var rows = document.getElementsByName('ventaCId[]');
                var selectedRows = [];
                var idVentas = "0";
                for (var i = 0; i<rows.length; i++) {
                    if (rows[i].checked) {
                        //alert(rows[i].value);
                        idVentas+=","+rows[i].value;
                        selectedRows.push(rows[i]);
                    }
                }
                
                lightbox('<iframe src=facturar.php?idVenta='+idVentas+' width=900px frameborder=0 height=500px scrolling=no></iframe>',null);
                //alert(selectedRows.length);
            }
        </script>    
        
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
        <h1>Ventas</h1>
        <?php
            $sqlSel = "SELECT a.*, b.nombre as cliente FROM ventas a inner join clientes b on a.idCliente=b.id ".$cerca." order by fecha desc";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST" action="detalle_ventas.php" target="_blank">
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
            <a href="alta.php">Alta Venda</a><br/><br/>
            
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
            De: <input type="text" name="fechaDe" id="datepickerDe" value="<?PHP echo date('d-m-Y'); ?>">
            Hasta: <input type="text" name="fechaHasta" id="datepickerHasta" value="<?PHP echo date('d-m-Y'); ?>">        
                    
                    <input type="submit" value="Buscar">        
                    </td>
            
            </tr>
            </table>
            </form>
           <form name="ventasForm" method="post" action="test.php" target="_blank">

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
            while ($data= mysqli_fetch_array($rsSel))
            {
                
                if($data["idEstado"]==0)
                    $color="#ff9999";
                elseif ($data["idEstado"]==5)
                    $color="#66ff66";
                else
                    $color="#ff9900";
                ?>
                <tr style="background-color: <?PHP echo $color; ?>">
                    <td><input type="checkbox" name="ventaCId[]" value="<?PHP echo $data["id"]; ?>"></td>
                    <td><?PHP echo $data["id"]; ?></td>
                    <td><?PHP echo $data["cliente"]; ?></td>
                    <td><?PHP echo $data["fecha"]; ?></td>
                    <td>
                    <?PHP 
                    
                        $sqlTotalVenta="SELECT SUM(cantidad*precio_unidad) as total_venta FROM linea_venta WHERE idVenta=".$data["id"]." group by idVenta";
                        $rsTotalVenta = mysqli_query($bd,$sqlTotalVenta);
                        if ($dataTotalVenta=  mysqli_fetch_array($rsTotalVenta))
                        {
                            $totalVenta = number_format($dataTotalVenta["total_venta"],2);
                            $ventaTotal += $totalVenta;
                        }
                        mysqli_free_result($rsTotalVenta);
                        echo $totalVenta;
                    ?></td>
                    <td>
                    <?PHP 
                        $sqlTotalCompra="SELECT SUM(cantidad*precio_compra) as total_compra FROM linea_venta WHERE idVenta=".$data["id"]." group by idVenta";
                        $rsTotalCompra = mysqli_query($bd,$sqlTotalCompra);
                        if ($dataTotalCompra=  mysqli_fetch_array($rsTotalCompra))
                        {
                            $totalCompra = number_format($dataTotalCompra["total_compra"],2);
                            $compraTotal += $totalCompra;
                        }
                        mysqli_free_result($rsTotalCompra);
                        echo $totalCompra;
                    ?></td>
                    <td>
                        <?PHP
                        if ($totalVenta!=0)
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
                        <a style="color:blue; cursor: pointer; text-decoration: underline;" onclick="document.vendaForm.idVenta.value=<?PHP echo $data["id"]; ?>; document.vendaForm.submit();" target="_blank">Detalles venda</a>
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
                        <a href="javascript:document.eliminaForm.idVenta.value=<?PHP echo $data["id"]; ?>; if(confirm('Desea borrar la venta numero <?PHP echo $data["id"]; ?>')) { document.eliminaForm.submit() } ;">Eliminar</a> | <a href="javascript:document.modForm.idVenta.value=<?PHP echo $data["id"]; ?>; document.modForm.idCliente.value=<?PHP echo $data["idCliente"]; ?>; document.modForm.submit();">Modificar</a>
                    </td>
                </tr>   
                <?PHP
            }  
            mysqli_free_result($rsSel);  
        ?>
                
           <tr>
               <th></th>
               <th></th>
               <th></th>    
               <th><b><?PHP echo $ventaTotal; ?></b></th>
               <th><b><?PHP echo $compraTotal; ?></b></th>
               <th><b><?PHP echo number_format(($ventaTotal-$compraTotal)/$ventaTotal*100,2); ?></b></th>
               <th>&nbsp;</th>
           </tr>    
           </table>
               <center><input type="button" value="facturar" onclick="facturarM()"></center>
                               </form> 
                                <br/><br/>
            <a href="alta.php">Alta Venda</a>
    </body>
</html>
