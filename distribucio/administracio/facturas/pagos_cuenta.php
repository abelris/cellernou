<?PHP 
include("../conf.php"); 

if (isset($_GET["idFactura"]))
    $idFactura = $_GET["idFactura"];

if (isset($_POST["idFactura"]))
    $idFactura = $_POST["idFactura"];


if (isset($_POST["nuevoPago"])) //Vol dir que fem la alta de un nou pagamente
{
    
    //Convertimos la fecha de Europa a EEUU
    $fechaA = explode("/",$_POST["fecha"]);
    $fecha_cambiada = mktime(00,00,00,$fechaA[1],$fechaA[0],$fechaA[2]);
    $fecha = date("Y-m-d", $fecha_cambiada); 
    
    $sqlIns="INSERT INTO pagos_cuenta (idFactura,fecha,importe,idFormaPago,descripcion) VALUES ('".$idFactura."','".$fecha."','".$_POST["importe"]."','".$_POST["idFormaPago"]."','".$_POST["descripcion"]."')";
    //echo $sqlIns;
    mysqli_query($bd,$sqlIns);
    
    
    //Comparamos la cantidad debida con el importe del pago
    if ($_POST["importe"]==$_POST["totalPendiente"])    //Vol dir que la factura queda pagada per tant modifiquem el estat de la factura a pagada
    {
        $sqlUdp="UPDATE facturas SET idEstado = 1, fecha_pago='".date('Y-m-d H:i:s')."' WHERE id = ".$idFactura;
        //echo $sqlUdp;
        mysqli_query($bd,$sqlUdp);
    }
    else
    {
        $sqlUdp="UPDATE facturas SET idEstado = 2 WHERE id = ".$idFactura;
        //echo $sqlUdp;
        mysqli_query($bd,$sqlUdp);
    }
}
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
        
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
        <link rel="stylesheet" href="/resources/demos/style.css">
        <script>
        $(function() {
            $("#datepicker").datepicker({dateFormat: "dd/mm/yy"});
        });
        </script>
        
        <script language="javascript" src="/distribucio/js/ajax.js"></script>
        <script language="javascript" src="/js/lightboxOwn.js"></script>
        
        
    </head>
    <body>
        <h1>Pagos Factura <?PHP echo $idFactura; ?></h1>
        <?php
            $sqlSelFac = "SELECT c.nombre,SUM(b.cantidad*b.precio_unidad) as total_venta, SUM(b.cantidad*b.precio_unidad*iva/100) as total_iva FROM facturas a inner join linea_factura b on a.id=b.idFactura inner join clientes c on a.idCliente=c.id WHERE a.id = ".$idFactura." group by a.id";
            //echo $sqlSelFac;
            $rsSelFac = mysqli_query($bd,$sqlSelFac);
            if ($dataFac=  mysqli_fetch_array($rsSelFac))
            {
                $totalFactura = number_format($dataFac["total_venta"]+$dataFac["total_iva"],2);
            }
            mysqli_free_result($rsSelFac);
            //echo $totalFactura;
            
            
            $sqlSel = "SELECT * FROM pagos_cuenta WHERE idFactura = ".$idFactura." order by fecha desc";
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
            
            
        
            
            <table border="1" width="100%">
                <tr>    
                    <th>Id</th>
                    <th>fecha</th>
                    <th>Importe</th>
                    <th>Forma Pago</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            $ventaTotal = 0;
            $compraTotal = 0;
            $ventaTotalIVA = 0;
            $compraTotalIVA = 0;
            $totalPagado = 0;
            while ($data= mysqli_fetch_array($rsSel))
            {
                                        
                $totalPagado += $data["importe"];
                
                ?>
                <tr style="background-color: <?PHP echo $color; ?>">
                    <td><?PHP echo $data["id"]; ?></td>
                    <td><?PHP echo $data["fecha"]; ?></td>
                    <td><?PHP echo $data["importe"]; ?></td>
                    <td>
                    <?PHP 
                        $sqlFormaPago="SELECT nombre FROM formas_pago WHERE id=".$data["idFormaPago"];
                        $rsFormaPago = mysqli_query($bd,$sqlFormaPago);
                        if ($dataFormaPago=  mysqli_fetch_array($rsFormaPago))
                        {
                            echo $dataFormaPago["nombre"];
                        }
                        mysqli_free_result($rsFormaPago);
                    ?></td>
                    <td>
                        <!--a href="javascript:document.facturaForm.idFactura.value=<?PHP echo $data["id"]; ?>; document.facturaForm.submit();" target="_blank">Detalles Factura</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:lightbox('<iframe src= width=900px frameborder=0 height=500px scrolling=no></iframe>',null);">Pagos a cuenta</a>
                        <&nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.preciosVendaForm.idCliente.value=<?PHP echo $data["id"]; ?>;document.preciosVendaForm.nombreCliente.value='<?PHP echo $data["nombre"]; ?>'; document.preciosVendaForm.submit();">Precios Venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.eliminaForm.idCliente.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a-->
                    </td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
            
            $totalPagado = number_format($totalPagado,2);
            $totalFactura = number_format($totalFactura,2);
            $totalPendiente = number_format($totalFactura-$totalPagado,2);
            
        ?>
           <tr>
               <th>&nbsp;</th>
               <th></th>
               <th><b>Total Pagado: <?PHP echo $totalPagado; ?></b></th>
               <th><b>Total Factura: <?PHP echo $totalFactura; ?></b></th>
               <th><b>Pendiente: <?PHP echo $totalPendiente; ?></b></th>
               <th>&nbsp;</th>
           </tr>
           </table><br/><br/>
           <h1>Nuevo Pago</h1>
           <form method="post" name="myform">
               <input type="hidden" value="<?PHP echo $idFactura; ?>" name="idFactura">
               <input type="hidden" value="<?PHP echo $totalPendiente; ?>" name="totalPendiente">
                Importe: <input type="text" name="importe" value="<?PHP echo $totalPendiente; ?>"><br />
                <?PHP
                        $sqlForma="Select * from formas_pago order by id";
                        $rsForma=mysqli_query($bd,$sqlForma);
                        ?>
                        Forma pago: <select name="idFormaPago">
                        <?PHP
                        while($dataForma=mysqli_fetch_array($rsForma))
                        {
                            ?>
                            <option value="<?PHP echo $dataForma["id"]; ?>" <?PHP if ($dataForma["id"]==$data["idFormaPago"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataForma["id"]."-".$dataForma["nombre"]; ?></option>
                            <?PHP
                        }
                        mysqli_free_result($rsForma);
                        ?>
                        </select><br />
                fecha del pago: <input type="text" name="fecha" id="datepicker" ><br />
                <textarea name="descripcion"></textarea>
                <input type="submit" value="Nuevo Pago" name="nuevoPago">
            </form>
           
    </body>
</html>
