<?PHP include("../../conf.php"); 


$cerca = " WHERE c.idProcedencia='28' ";
$dataB="";
        
if (!empty($_POST["mes"]))
{
        $cerca.=" AND MONTH(a.fecha) = ".$_POST["mes"];
        $dataB .= " mes:".$_POST["mes"]; 
                
}

if (!empty($_POST["mes"]))
{
    $cerca.=" AND YEAR(a.fecha) = ".$_POST["ano"];
    $dataB .= " Año:".$_POST["ano"];
}

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
    </head>
    <body>
        <?php include("../../includes/header.php"); ?>
        <h1>Granel <?PHP echo $dataB; ?></h1>
        <?php
            $sqlSel = "SELECT YEAR(a.fecha),MONTH(a.fecha),c.nombre,SUM(b.cantidad) as cantidad FROM ventas a inner join linea_venta b on a.id=b.idVenta inner join productos c on b.idProducto=c.id inner join clientes d on d.id=a.idCliente ".$cerca." and d.idUsuario='".$_SESSION["idUsuario"]."' group by YEAR(a.fecha),MONTH(a.fecha),b.idProducto ";
            $sqlSel .= " UNION ALL ";
            $sqlSel .= "SELECT YEAR(a.fecha),MONTH(a.fecha),c.nombre,SUM(b.cantidad) as cantidad FROM tiquets a inner join linea_tiquet b on a.id=b.idVenta inner join productos c on b.idProducto=c.id ".$cerca." and a.idTienda='".$_SESSION["idUsuario"]."' group by YEAR(a.fecha),MONTH(a.fecha),b.idProducto";
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
            </select></td>
            <td>Mes: <input type="text" size="10" name="mes"> Año:<input type="text" size="10" name="ano">
            <input type="submit" value="buscar">
            </td>
            </tr>
            </table>
            </form>
            <table border="1" width="100%">
                <tr>
                    <th>Id</th>
                    <th>Producto</th>
                    <th>litros</th>
                    <th>Garrafas</th>
                    <th>Ampolles</th>
                </tr>
            <?PHP
            $ventaTotal = 0;
            $ivaTotal = 0;
            $mes=0;
            $any=0;
            $garrafes5=0;
            $garrafes2=0;
            $ampolles=0;
            //$tiposGranel=array("Rioja","Ribera","Alella",);
            
            $granel=array("Rioja","Priorat","Priorat_D","Ribera","Daurat","Falset","Rosat","Verdejo","Somontano","Vermut","Ranci","Malaga","Alella","Penedes","Moscatel","Oporto","Jerez");
            $granelC=array();
            while ($data= mysqli_fetch_array($rsSel))
            {
                //Rioja
                if (strpos($data["nombre"], "Rioja")!=null)
                {
                    //print_r($granelC["Rioja"]);
                    //echo " ".$data["nombre"]."(".$data["cantidad"].")<br>";
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Rioja"]+=(5*$data["cantidad"]);
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Rioja"]+=(2*$data["cantidad"]);
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Rioja"]+=(0.75*$data["cantidad"]);
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Rioja"]+=$data["cantidad"];
                    }
                }
                
                //Ribera
                if (strpos($data["nombre"],"Ribera")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Ribera"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Ribera"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Ribera"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Ribera"]+=$data["cantidad"];
                    }
                }
                
                //Daurat
                if (strpos($data["nombre"],"Daurat")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Daurat"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Daurat"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Daurat"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Daurat"]+=$data["cantidad"];
                    }
                }
                
                //Rosat
                if (strpos($data["nombre"],"Rosat")!=null || strpos($data["nombre"],"Rosado")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Rosat"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Rosat"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Rosat"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Rosat"]+=$data["cantidad"];
                    }
                }
                 
                //Penedes
                if (strpos($data["nombre"],"Penedes")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Penedes"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Penedes"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Penedes"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Penedes"]+=$data["cantidad"];
                    }
                }
                
                
                //Alella
                if (strpos($data["nombre"],"Alella")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Alella"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Alella"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Alella"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Alella"]+=$data["cantidad"];
                    }
                }
                
                //Priorat Dolç
                if (strpos($data["nombre"],"Priorat D")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Priorat_D"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Priorat_D"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Priorat_D"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Priorat_D"]+=$data["cantidad"];
                    }
                }
                else
                {
                   //Priorat 
                   if (strpos($data["nombre"],"Priorat")!=null)
                    {
                        if (strpos($data["nombre"],"5L")!=null)
                        {
                                $granelC["Priorat"]+=5*$data["cantidad"];
                                $garrafes5+=1;
                        }
                        if (strpos($data["nombre"],"2L")!=null)
                        {
                                $granelC["Priorat"]+=2*$data["cantidad"];
                                $garrafes2+=1;
                        }

                        if (strpos($data["nombre"],"Ampolla")!=null)
                        {
                                $granelC["Priorat"]+=0.75*$data["cantidad"];
                                $ampolles+=1;
                        }
                        
                        if (strpos($data["nombre"],"itro")!=null)
                        {
                                $granelC["Priorat"]+=$data["cantidad"];
                        }
                    } 
                }
                
                
                //Falset
                if (strpos($data["nombre"],"Falset")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Falset"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Falset"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Falset"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Falset"]+=$data["cantidad"];
                    }
                }
                
                //Falset
                if (strpos($data["nombre"],"Somontano")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Somontano"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Somontano"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Somontano"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Somontano"]+=$data["cantidad"];
                    }
                }
                
                //Falset
                if (strpos($data["nombre"],"Verdejo")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Verdejo"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Verdejo"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Verdejo"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Verdejo"]+=$data["cantidad"];
                    }
                }
                
                //Vermut
                if (strpos($data["nombre"],"Vermut")!=null || strpos($data["nombre"],"Vermouth")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Vermut"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Vermut"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Vermut"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"BiBox")!=null)
                    {
                            $granelC["Vermut"]+=$data["cantidad"]*20;
                    }
                    
                    if (strpos($data["nombre"],"BagInBox")!=null)
                    {
                            $granelC["Vermut"]+=$data["cantidad"]*10;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Vermut"]+=$data["cantidad"];
                    }
                }
                
                //Ranci
                if (strpos($data["nombre"],"Ranci")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Ranci"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Ranci"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Ranci"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Ranci"]+=$data["cantidad"];
                    }
                }
                
                //Moscatel
                if (strpos($data["nombre"],"Moscatel")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Moscatel"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Moscatel"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Moscatel"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Moscatel"]+=$data["cantidad"];
                    }
                }
                
                //Oporto
                if (strpos($data["nombre"],"Oporto")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Oporto"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Oporto"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Oporto"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Oporto"]+=$data["cantidad"];
                    }
                }
                
                //Jerez
                if (strpos($data["nombre"],"Jerez")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Jerez"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Jerez"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Jerez"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Jerez"]+=$data["cantidad"];
                    }
                }
                
                //Malaga
                if (strpos($data["nombre"],"Malaga")!=null)
                {
                    if (strpos($data["nombre"],"5L")!=null)
                    {
                            $granelC["Malaga"]+=5*$data["cantidad"];
                            $garrafes5+=1;
                    }
                    if (strpos($data["nombre"],"2L")!=null)
                    {
                            $granelC["Malaga"]+=2*$data["cantidad"];
                            $garrafes2+=1;
                    }
                    
                    if (strpos($data["nombre"],"Ampolla")!=null)
                    {
                            $granelC["Malaga"]+=0.75*$data["cantidad"];
                            $ampolles+=1;
                    }
                    
                    if (strpos($data["nombre"],"itro")!=null)
                    {
                            $granelC["Malaga"]+=$data["cantidad"];
                    }
                }
                    
                    
                ?>
                <!--tr style="background-color: <?PHP echo $color; ?>">
                    <td><?PHP echo $data["id"]; ?></td>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td align="right"><?PHP echo number_format($data["cantidad"],2); ?></td>
                    <td align="right"></td>
                    <td></td>
                </tr-->
                <?PHP
            }  
            mysqli_free_result($rsSel);
            
            //print_r($granelC);
            $totalLitres=0;
            for($iG=0;$iG<count($granel);$iG++)
            {
            ?>
           <tr>
               <td></td>
               <td><?PHP echo $granel[$iG]; ?></td>
               <td align="right"><b><?PHP echo number_format($granelC[$granel[$iG]]); ?></b></td>
               <td align="right"><b><?PHP //echo number_format($ivaTotal,2); ?></b></td>
               <td align="right"><b><?PHP //echo number_format($ventaTotal+$ivaTotal,2); ?></b></td>
           </tr>
           <?PHP
                $totalLitres += $granelC[$granel[$iG]];
            }
           ?>
           <tr>
               <th>Total Granel </th>
               <th><?PHP echo $granel[$iG]; ?></th>
               <th align="right"><b><?PHP echo number_format($totalLitres); ?></b></th>
               <th align="right"><b><?PHP //echo number_format($ivaTotal,2); ?></b></th>
               <th align="right"><b><?PHP //echo number_format($ventaTotal+$ivaTotal,2); ?></b></th>
           </tr>
           </table><br/><br/>
    </body>
</html>
