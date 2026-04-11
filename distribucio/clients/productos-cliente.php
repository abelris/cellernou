<?PHP include("../conf.php"); ?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<?PHP 

if (isset($_POST["accion"]))
{
    if ($_POST["accion"]==1)
    {
        for($i=0;$i<count($_POST["productos"]);$i++)
        {
            $sqlSel = "SELECT * from precios_cliente WHERE idCliente=".$_POST["idCliente"]." and idProducto = ".$_POST["productos"][$i];
            $rsSel = mysqli_query($bd,$sqlSel);
            if (mysqli_num_rows($rsSel)==0)
            {    
                $sqlInsProdClient="INSERT INTO precios_cliente (idCliente,idProducto) VALUES (".$_POST["idCliente"].",".$_POST["productos"][$i].")";
                $rsInsProdClient = mysqli_query($bd,$sqlInsProdClient);
        }   }
    }
    echo "Productos introducidos";
}

//Muntem la cadena de Cerca
$cerca = "";

if ($_POST["idSector"]!=0)
    $cerca.=" AND a.idSector = ".$_POST["idSector"];
if ($_POST["idTipo"]!=0)
    $cerca.=" AND a.idTipo = ".$_POST["idTipo"];
if ($_POST["idProcedencia"]!=0)
    $cerca.=" AND a.idProcedencia = ".$_POST["idProcedencia"];

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="/distribucio/css/estils.css" />
    </head>
    <body>
        <?php include("../includes/header.php"); ?>
        <h1>Preus venta client: <?PHP echo $_POST["nombreCliente"]; ?></h1>
        
        <div>
                <form name='buscador' method='POST'>
                    <input type='hidden' value='<?PHP echo $_POST["idCliente"]; ?>' name='idCliente'>
            <table align='center'>       
                <tr>
                    <td>
             <?PHP
            $sqlSector="Select * from sectores order by nombre";
            $rsSector=mysqli_query($bd,$sqlSector);
            ?>
            Sector: <select name="idSector" onchange='document.buscador.submit();'>
                <option value='0'>Todos</option>
            <?PHP
            while($dataSector=mysqli_fetch_array($rsSector))
            {
                ?>
                <option value="<?PHP echo $dataSector["id"]; ?>" <?PHP if ($dataSector["id"]==$_POST["idSector"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataSector["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsSector);
            ?>
            </select></td><td>
            <?PHP
            $sqlTipo="Select * from tipo_producto order by nombre";
            $rsTipo=mysqli_query($bd,$sqlTipo);
            ?>
            Tipus: <select name="idTipo" onchange='document.buscador.submit();'>
                <option value='0'>Todos</option>
            <?PHP
            while($dataTipo=mysqli_fetch_array($rsTipo))
            {
                ?>
                <option value="<?PHP echo $dataTipo["id"]; ?>" <?PHP if ($dataTipo["id"]==$_POST["idTipo"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataTipo["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsTipo);
            ?>
            </select></td><td>
            <?PHP
            $sqlProcedencia="Select * from procedencia order by nombre";
            $rsProcedencia=mysqli_query($bd,$sqlProcedencia);
            ?>
            Procedencia: <select name="idProcedencia" onchange='document.buscador.submit();'>
                <option value='0'>Todos</option>
            <?PHP
            while($dataProcedencia=mysqli_fetch_array($rsProcedencia))
            {
                ?>
                <option value="<?PHP echo $dataProcedencia["id"]; ?>" <?PHP if ($dataProcedencia["id"]==$_POST["idProcedencia"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataProcedencia["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsProcedencia);
            ?>
            </select></td>
            </tr>
            </table>
                </form>   
            </div>
        <?php
            $sqlSel = "SELECT a.*,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia FROM productos a left join sectores b on a.idSector=b.id left join tipo_producto c on a.idTipo=c.id left join procedencia d on a.idProcedencia=d.id WHERE a.id NOT IN (SELECT idProducto from precios_cliente WHERE idCliente = ".$_POST["idCliente"].") ".$cerca." order by b.nombre,c.nombre,a.nombre";
            //echo $sqlSel; 
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST">
                <input type="hidden" value="<?PHP echo $_POST["idCliente"]; ?>" name="idCliente">
                <input type="hidden" value="1" name="accion">

                        <center><input type="submit" value="Guardar productos"></center>
                            
            <table border="1" width="100%">
                <tr>
                    <th>Sector</th>
                    <th>Categoria</th>
                    <th>Procedencia</th>    
                    <th>Nom</th>
                    <th>Descripcio</th>
                    <th>Precio Sin IVA</th>
                    <th>Foto</th>
                    
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            while ($data= mysqli_fetch_array($rsSel))
            {
                ?>
                <tr>
                    <td><?PHP echo $data["nombreSector"]; ?></td>
                    <td><?PHP echo $data["nombreTipo"]; ?></td>
                    <td><?PHP echo $data["nombreProcedencia"]; ?></td>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td><?PHP echo $data["descripcion"]; ?></td>
                    <td align='right'><input type='checkbox' value='<?PHP echo $data["id"]; ?>' name='productos[]'></td>
                    
                    <td align="center"><a href="../upload/<?PHP echo $data["foto"]; ?>" target="_blank"><img src="../upload/<?PHP echo $data["foto"]; ?>" height="30"></a></td>
                    <td>
                        <!--a href="javascript:document.vendaForm.idProducto.value=<?PHP echo $data["id"]; ?>; document.vendaForm.submit();">Nova venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;-->
                        <a href="javascript:document.eliminaForm.idProducto.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a>
                    </td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           </table><br/><br/>
           <center><input type="submit" value="Guardar productos"></center>
           </form>
       
    </body>
</html>
