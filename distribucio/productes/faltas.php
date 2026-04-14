<?PHP include("../conf.php"); 
//Document que mostra les faltes de totes les tendes ordendes per tenda i per producte.


//Muntem la cadena de Cerca
$cerca = " WHERE 1 ";
if (!empty($_POST["codigo"]))
{
    $cerca.=" AND a.codigo = ".$_POST["codigo"];
}
else
{
    if ($_POST["idSector"]!=0)
        $cerca.=" AND a.idSector = ".$_POST["idSector"];
    if ($_POST["idTipo"]!=0)
        $cerca.=" AND a.idTipo = ".$_POST["idTipo"];
    if ($_POST["idProcedencia"]!=0)
        $cerca.=" AND a.idProcedencia = ".$_POST["idProcedencia"];
    if ($_POST["palabra"]!="")
    {
        $palabra = $_POST["palabra"];
        $cerca.=" AND a.nombre like '%".$_POST["palabra"]."%'";
    }
    else
        $cerca.=" AND a.nombre like '%xxxxx%'";
}
//Cerquem en la tenda que toca.
//$cerca .= " AND e.idTienda = ".$_SESSION["idUsuario"];


?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

        <title></title>
        <link rel="stylesheet" type="text/css" href="/distribucio/css/estils.css" />
    </head>
    <body>
        <?php include("../includes/header.php"); ?>
        <h1>Productes</h1>
        <?php
            //$sqlSel = "SELECT a.*,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia, e.pvp, e.idTienda FROM productos a left join sectores b on a.idSector=b.id left join tipo_producto c on a.idTipo=c.id left join procedencia d on a.idProcedencia=d.id left join precios_producto_tienda e on a.id=e.idProducto left join stock_producto f on a.id=f.idProducto ".$cerca." AND (a.id IN (SELECT idProducto FROM stock_producto WHERE vendido=0 GROUP BY idProducto,idTienda HAVING COUNT(id) < 2) OR f.idProducto IS NULL) order by b.nombre,c.nombre,a.nombre";
            $sqlSel = "SELECT a.id as id,a.nombre as nombre, a.precio_medio as precio_medio,a.precio_venta_dis as precio_venta_dis,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia, e.pvp, e.idTienda FROM productos a left join sectores b on a.idSector=b.id left join tipo_producto c on a.idTipo=c.id left join procedencia d on a.idProcedencia=d.id left join precios_producto_tienda e on a.id=e.idProducto ".$cerca." AND e.idTienda=1 AND (a.id IN (SELECT idProducto FROM stock_producto WHERE vendido=0 AND idTienda=1 GROUP BY idProducto,idTienda HAVING COUNT(id) < 2) OR a.id NOT IN (SELECT idProducto FROM stock_producto WHERE vendido=0 and idTienda=1 GROUP BY idProducto,idTienda)) ";
            $sqlSel .= "UNION ALL SELECT a.id as id,a.nombre as nombre, a.precio_medio as precio_medio,a.precio_venta_dis as precio_venta_dis,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia, e.pvp, e.idTienda FROM productos a left join sectores b on a.idSector=b.id left join tipo_producto c on a.idTipo=c.id left join procedencia d on a.idProcedencia=d.id left join precios_producto_tienda e on a.id=e.idProducto ".$cerca." AND e.idTienda=2 AND (a.id IN (SELECT idProducto FROM stock_producto WHERE vendido=0 AND idTienda=2 GROUP BY idProducto,idTienda HAVING COUNT(id) < 2) OR a.id NOT IN (SELECT idProducto FROM stock_producto WHERE vendido=0 and idTienda=2 GROUP BY idProducto,idTienda))";
            $sqlSel .= "UNION ALL SELECT a.id as id,a.nombre as nombre, a.precio_medio as precio_medio,a.precio_venta_dis as precio_venta_dis,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia, e.pvp, e.idTienda FROM productos a left join sectores b on a.idSector=b.id left join tipo_producto c on a.idTipo=c.id left join procedencia d on a.idProcedencia=d.id left join precios_producto_tienda e on a.id=e.idProducto ".$cerca." AND e.idTienda=3 AND (a.id IN (SELECT idProducto FROM stock_producto WHERE vendido=0 AND idTienda=3 GROUP BY idProducto,idTienda HAVING COUNT(id) < 2) OR a.id NOT IN (SELECT idProducto FROM stock_producto WHERE vendido=0 and idTienda=3 GROUP BY idProducto,idTienda))";
            $sqlSel .= " order by nombreSector,nombreTipo,nombre";
            //$sqlSel = "SELECT a.*,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia, e.pvp FROM productos a inner join sectores b on a.idSector=b.id inner join tipo_producto c on a.idTipo=c.id inner join procedencia d on a.idProcedencia=d.id inner join precios_producto_tienda e on a.id=e.idProducto WHERE a.id IN (SELECT idProducto FROM ) order by b.nombre,c.nombre,a.nombre";
            //echo $sqlSel;
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST" action="venda.php">
                <input type="hidden" name="idProducto">
            </form>
            <form name="modificarForm" method="POST" action="modificar.php">
                <input type="hidden" name="idProducto">
            </form>
            <form name="eliminaForm" method="POST" action="borrar.php">
                <input type="hidden" name="idProducto">
            </form>
            <!--a href="alta.php">Alta Producte</a><br/><br/-->
            <div>
                <form name='buscador' method='POST'>
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
            <td>
                <input type="text" name="palabra" value="<?PHP echo $palabra; ?>">
            </td>
            </tr>
            
            </table>
                </form>   
            </div>
            <?PHP 
            $htmlText="<br><br><br><br><h2>Listado precios xxxxxxxxx</h2>";
            ?>
            <form name="guardarProductos" method="POST" onSubmit="document.guardarProductos.palabra.value=document.buscador.palabra.value;">
                <input type="hidden" name="palabra">
            <table border="1" width="100%">
                <tr>
                    <th>Sector</th>
                    <th>Categoria</th>
                    <th>Procedencia</th>    
                    <th>Nom</th>
                    <th>Descripcio</th>
                    <th>Tienda</th>
                    <th>Precio Compra Medio</th>
                    <th>Existencias</th>
                    
                    
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            
            $htmlText.='<table border="0" width="100%">
                        <tr>
                            <th align="left" width="30%">Categoria</th>
                            <th align="left" width="25%">Nom</th>
                            <th align="left" width="35%">Descripcio</th>
                            <th align="right" width="10%">Precio</th>
                            </tr>';
            
            $contP=1;
            while ($data= mysqli_fetch_array($rsSel))
            {
                $liniaRoja="";
                
                if ($data["precio_venta_dis"]==0)
                    $margen = 0;
                else
                    $margen = number_format(($data["precio_venta_dis"] - $data["precio_medio"]) / $data["precio_venta_dis"] * 100,2);
                
                if ($margen<0)
                    $liniaRoja=' style="background-color:#ff6666"';
                if ($margen>=0 && $margen<8)
                    $liniaRoja=' style="background-color:orange"';
                
                ?>
                <input type="hidden" name="idProductos[]" value="<?PHP echo $data["id"]; ?>">
                <input type="hidden" name="precioCompraMedio[]" value="<?PHP echo $data["precio_medio"]; ?>">

                <tr <?PHP //echo $liniaRoja; ?>>
                    <td><?PHP echo $data["nombreSector"]; ?></td>
                    <td><?PHP echo $data["nombreTipo"]; ?></td>
                    <td><?PHP echo $data["nombreProcedencia"]; ?></td>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td><?PHP echo $data["descripcion"]; ?></td>
                    <td>
                        <?PHP 
                        switch ($data["idTienda"])
                        {
                            case 1:
                                echo "Celler nou de Premia";
                                break;
                            case 2:
                                echo "Celler nou de Mataro";
                                break;
                            case 3:
                                echo "Celler nou de Premia II";
                                break;
                              
                            
                        }
                        ?>
                    </td>
                    <td><?PHP echo $data["precio_medio"]; ?></td>
                    <td><?PHP  
                        $sqlExistencies="SELECT COUNT(*) as existencias FROM stock_producto WHERE vendido=0 AND idProducto=".$data["id"]." AND idTienda=".$data["idTienda"];
                        $rsExistencies=mysqli_query($bd,$sqlExistencies);
                        if ($dataExistencies= mysqli_fetch_array($rsExistencies))
                        {
                            $existenciesP0=$dataExistencies["existencias"];
                        }
                        mysqli_free_result($rsExistencies);
                        echo $existenciesP0;
                        
                        
                        
                        ?></td>
                    
                    
                </tr>
                <?PHP
                
                if ($contP%2==0)
                    $liniaP=' style="background-color:#eeeeee"';
                else
                    $liniaP=' style="background-color:#ffffff"';
                
                
                $htmlText.='
                    <tr '.$liniaP.'>
                    <td>'.$data["nombreTipo"].'</td>
                    <td>'.str_replace("'"," ", $data["nombre"]).'</td>
                    <td>'.$data["descripcion"].'</td>
                    <td align="right">'.$data["precio_venta_dis"].' &euro;</td>
                    </tr>
                    ';
                
                $contP++;
            }  
            mysqli_free_result($rsSel);
            
            $htmlText.="</table>";
        ?>
           <tr>
               <td align="center" colspan="12">
                   <input type="submit" name="guardarPrecios" value="Guardar Precios" onclick="document.guardarProductos.submit();">
               </td>
           </tr>
           </table>
           </form>     
                
                <br/><br/>
            <!--a href="alta.php">Alta Producte</a-->
            
            
            <script language='javascript'>
            $(document).ready(function () {
                $('.precioC').keypress(function(event) {
                    if(event.which == 8 || event.which == 0){
                        return true;
                    }
                    if(event.which < 46 || event.which > 59) {
                        return false;
                        //event.preventDefault();
                    } // prevent if not number/dot

                    if(event.which == 46 && $(this).val().indexOf('.') != -1) {
                        return false;
                        //event.preventDefault();
                    } // prevent if already dot
                });
            });
        </script>
        <form name="myform" action="../includes/topdf.php" method="POST" target="_blank">
            <input type="hidden" value='<?PHP echo htmlentities($htmlText); ?>' name="htmlText">
            <input type="text" value='' name="titol">
            <input type="submit" value="Pasar a pdf">
        </form>
    </body>
</html>
