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
    $cerca.=" and a.fecha > DATE_ADD(NOW(), INTERVAL -1 DAY)";


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
        <?php include("../includes/header.php"); ?>
        <h1>Historico de pagos</h1>
        <?php
            $sqlSel = "SELECT c.id as ident,c.importe,c.descripcion,c.fecha,c.idFactura,a.id, b.nombre as cliente FROM pagos_cuenta c inner join facturas a on c.idFactura=a.id inner join clientes b on a.idCliente=b.id ".$cerca." order by c.fecha desc";
            $rsSel = mysqli_query($bd,$sqlSel);
            //echo $sqlSel; 
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
                    <th>IdFactura</th>
                    <th>Cliente</th>
                    <th>fecha</th>
                    <th>Importe</th>
                    <th>Descripcion</th>
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
            
            $totalPagos=0;
            
            while ($data= mysqli_fetch_array($rsSel))
            {
                
            $totalPagos+=$data["importe"];
            
                if($data["idEstado"]==0)
                    $color="#ff9999";
                if($data["idEstado"]==1)
                    $color="#66ff66";
                if($data["idEstado"]==2)
                    $color="orange";
                        
                
                ?>
                <tr style="background-color: <?PHP echo $color; ?>">
                    <td><?PHP echo $data["ident"]; ?></td>
                    <td><?PHP echo $data["idFactura"]; ?></td>
                    <td><?PHP echo $data["cliente"]; ?></td>
                    <td><?PHP echo $data["fecha"]; ?></td>
                    <td><?PHP echo $data["importe"]; ?></td>
                    <td><?PHP echo $data["descripcion"]; ?></td>    
                    
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           <tr>
               <th>Total pagos</th>
               <th></th>
               <th></th>
               <th></th>
               <th><b><?PHP echo $totalPagos; ?></b></th>
               <th></th>
               
           </tr>
           </table><br/><br/>
    </body>
</html>
