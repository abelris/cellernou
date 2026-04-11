<?PHP include("../conf.php"); ?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<?PHP 
$cerca="";
if (isset($_POST["mes"]))
{
    $cerca=" AND MONTH(fecha)=".$_POST["mes"]." AND YEAR(fecha)=".$_POST["ano"]."";
    $mes = $_POST["mes"];
    $ano = $_POST["ano"];
}
else
{
    $mes = number_format(date('m'));
    $ano = date('Y');
}
    
if (isset($_POST["accion"]))
{
    if ($_POST["accion"]==1)
    {
        for($i=0;$i<count($_POST["productos"]);$i++)
        {
               
            $sqlUpdProdClient="UPDATE precios_especiales_proveidor SET precio=".$_POST["precios"][$i].",precioGM=".$_POST["preciosGM"][$i].",precioAnma=".$_POST["preciosAnma"][$i].",precioEspinaler=".$_POST["preciosEspinaler"][$i].",precioDisterri=".$_POST["preciosDisterri"][$i].",precioKomKal=".$_POST["preciosKomKal"][$i].",precioJesamar=".$_POST["preciosJesamar"][$i]." WHERE idProducto=".$_POST["productos"][$i];
            //echo $sqlUpdProdClient;
            mysqli_query($bd,$sqlUpdProdClient);
        }       
    }
    echo "Productos introducidos";
}

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
        <h1>Preus especials venta proveidors:</h1>
        <center>
            <form name="formData" method="post">
                mes: <input type="text" name="mes" value="<?PHP echo $mes; ?>">
                A&ntilde;o: <input type="text" name="ano" value="<?PHP echo $ano; ?>">
                <input type="submit" value="enviar">
                <br /><br />
            </form>
        </center>
        <?php
            
        
            $cabecera = "";
            
            $sqlSel = "SELECT a.*,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia,e.precio as precio,e.precioGM,e.precioAnma,e.precioEspinaler,e.precioDisterri,e.precioKomKal,e.precioJesamar FROM productos a inner join sectores b on a.idSector=b.id inner join tipo_producto c on a.idTipo=c.id inner join procedencia d on a.idProcedencia=d.id inner join precios_especiales_proveidor e on a.id=e.idProducto order by c.nombre,a.nombre";
            $rsSel = mysqli_query($bd,$sqlSel);
            //echo $sqlSel;
            ?>
            <?PHP 
            $htmlText="<br><br><br><br><h2>Listado precios xxxxxxxxx</h2>";
            ?>
            <form name="vendaForm" method="POST">
            <input type="hidden" value="<?PHP echo $_POST["idCliente"]; ?>" name="idCliente">
            <input type="hidden" value="1" name="accion"> 
            <table border="1" width="100%">
                <tr>   
                    <th>Nom</th>
                    <th>Ventas/mes</th>
                    <th>Precio Venta</th>
                    <th>Precio compra</th>
                    <th>Precio GM</th>
                    <th>Precio Anma</th>
                    <th>Precio Espinaler</th>
                    <th>Precio Disterri</th>
                    <th>KomKal</th>
                    <th>Jesamar</th>
                    <th>Margen</th>
    
                </tr>
                <?PHP
                $htmlText.='<table border="1" width="100%">
                        <tr>
                            <th align="left" width="30%">Categoria</th>
                            <th align="left" width="60%">Nom</th>
                            <th align="right" width="10%">Precio</th>
                            </tr>';
                ?>
                
            <?PHP
            $totalUnidadesVentaMes=0;
            $totalImporteVentaMes=0;
            $totalImporteCompraMes=0;
            $totalImporteVentaMesIva=0;
            $contP=1;
            while ($data= mysqli_fetch_array($rsSel))
            {
                ?>
                <input type='hidden' name='ids[]' value='<?PHP echo $data["id"]; ?>'
                <?PHP 
                if ($data["nombreTipo"]!==$cabecera)
                {
                ?>
                <tr>
                    <td colspan="10" align="center" valign="center" height="40"><b><?PHP echo strtoupper($data["nombreTipo"]); ?></b></td>
                </tr>
                <?PHP
                
                    $htmlText.='
                    <tr>
                        <td colspan="4" align="center" valign="center"><b> </b></td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center" valign="center"><b>'.strtoupper($data["nombreTipo"]).'</b></td>
                    </tr>
                    
                    ';
                
                }
                ?>  
                <?PHP 
                    $sqlVentasMes = "select sum(cantidad) as unidades, sum(cantidad * precio_unidad) as totalVenta, sum(cantidad * precio_compra) as totalCompra, sum(cantidad * precio_unidad * (1+iva/100)) as totalVentaIva from linea_venta where idProducto = ".$data["id"]." ".$cerca;
                    $rsVentasMes=mysqli_query($bd,$sqlVentasMes);
                    if ($dataVentasMes = mysqli_fetch_array($rsVentasMes))
                    {
                         $unidadesVenta = $dataVentasMes["unidades"];
                         $totalUnidadesVentaMes+=$unidadesVenta;
                         
                         $totalUnidadesVenta = number_format($dataVentasMes["totalVenta"],2);
                         $totalImporteVentaMes+=$totalUnidadesVenta;
                         
                         $totalUnidadesCompra = number_format($dataVentasMes["totalCompra"],2);
                         $totalImporteCompraMes+=$totalUnidadesCompra;
                         
                         $totalUnidadesVentaIva = number_format($dataVentasMes["totalVentaIva"],2);
                         $totalImporteVentaMesIva+=$totalUnidadesVentaIva;
                    }
                    mysqli_free_result($rsVentasMes);
                   
                    $clase="";
                    $claseGM="";
                    $claseAnma="";
                    $claseEspinaler="";
                    $claseDisterri="";
                    $claseKomKal="";
                    $claseJesamar="";
                    $margenProducto=0;
                    //if (number_format($unidadesVenta)>0)
                    //    $clase="background-color:#3d3";
                    
                    
                    //Comprobo quin es el preu mes baix, donant preferencia a espinaler i a disterri.
                    $min=1000;
                    //$data["precioEspinaler"];
                    //$precioMin="Espinaler";
                    if($data["precioEspinaler"]<$min && $data["precioEspinaler"]>0)
                    {
                        $min=$data["precioEspinaler"];
                        $precioMin="Espinaler";
                    }
                    if($data["precioDisterri"]<$min && $data["precioDisterri"]>0)
                    {
                        $min=$data["precioDisterri"];
                        $precioMin="Disterri";
                    }
                    if($data["precioAnma"]<$min && $data["precioAnma"]>0)
                    {
                        $min=$data["precioAnma"];
                        $precioMin="Anma";
                    }
                    if($data["precioGM"]<$min && $data["precioGM"]>0)
                    {
                        $min=$data["precioGM"];
                        $precioMin="GM";
                    }
                    if($data["precioKomKal"]<$min && $data["precioKomKal"]>0)
                    {
                        $min=$data["precioKomKal"];
                        $precioMin="KomKal";
                    }
                    if($data["precioJesamar"]<$min && $data["precioJesamar"]>0)
                    {
                        $min=$data["precioJesamar"];
                        $precioMin="Jesamar";
                    }
                 
                    
                    if ($min>0 && $min<1000)
                    {
                        switch ($precioMin)
                        {
                            case 'Espinaler':
                                $claseEspinaler="background-color:#3d3";
                                break;
                            case 'Disterri':
                                $claseDisterri="background-color:#3d3";
                                break;
                            case 'Anma':
                                $claseAnma="background-color:#3d3";
                                break;
                            case 'GM':
                                $claseGM="background-color:#3d3";
                                break;
                            case 'KomKal':
                                $claseGM="background-color:#3d3";
                                break;
                            case 'Jesamar':
                                $claseJesamar="background-color:#3d3";
                                break;
                        }
                    }
                    
                    
                    
                    $precioCompraProducto = $data["precio_medio"]; 
                    //Seleccionamos el último precio de compra del producto.
                    $sqlPrecioCompra="SELECT * FROM linea_compra WHERE idProducto=".$data["id"]." ORDER BY fecha DESC limit 0,1";
                    //echo $sqlPrecioCompra; 
                    $rsPrecioCompra= mysqli_query($bd,$sqlPrecioCompra);
                    if ($dataPrecioCompra=  mysqli_fetch_array($rsPrecioCompra))
                    {
                        $precioCompraProducto=$dataPrecioCompra["precio_unidad"];
                    }
                    mysqli_free_result($rsPrecioCompra);
                    
                    if ($data["precio"]!=0)
                    {
                        $margenProducto=number_format(($data["precio"]-$precioCompraProducto)/$data["precio"]*100,3);
                    }
                    if ($margenProducto<7)
                    {
                        $minPrice=$precioCompraProducto/(1-0.07);
                        
                        $clase="background-color:red";
                        $sqlUpdatePrecios="UPDATE precios_especiales_proveidor SET precio = ".$minPrice." WHERE idProducto = ".$data["id"];
                        //echo $precioCompraProducto."<-->".$sqlUpdatePrecios."</br>";
                        //mysqli_query($bd,$sqlUpdatePrecios);
                    }
                ?>
                <tr>
                    
                    <td style="<?PHP echo $clase; ?>"><?PHP echo $data["nombre"]; ?></td>
                    <td style="<?PHP echo $clase; ?>">
                    <?PHP 
                        echo $unidadesVenta." (".$totalUnidadesVenta.")";
                    ?>
                    </td>
                    <input type='hidden' value='<?PHP echo $data["id"]; ?>' name='productos[]'>
                    <td style="<?PHP echo $clase; ?>" align='right'><input type='text' value='<?PHP echo number_format($data["precio"],3); ?>' class='precioC' name='precios[]' size='5'> &euro;</td>
                    <td style="<?PHP echo $clase; ?>">
                        <?PHP 
                            
                            
                            echo $precioCompraProducto." (".$minPrice.")";
                        ?>
                    </td>
                    <td style="<?PHP echo $claseGM; ?>" align='right'><input type='text' value='<?PHP echo number_format($data["precioGM"],3); ?>' class='precioC' name='preciosGM[]' size='5'> &euro;</td>
                    <td style="<?PHP echo $claseAnma; ?>" align='right'><input type='text' value='<?PHP echo number_format($data["precioAnma"],3); ?>' class='precioC' name='preciosAnma[]' size='5'> &euro;</td>
                    <td style="<?PHP echo $claseEspinaler; ?>" align='right'><input type='text' value='<?PHP echo number_format($data["precioEspinaler"],3); ?>' class='precioC' name='preciosEspinaler[]' size='5'> &euro;</td>
                    <td style="<?PHP echo $claseDisterri; ?>" align='right'><input type='text' value='<?PHP echo number_format($data["precioDisterri"],3); ?>' class='precioC' name='preciosDisterri[]' size='5'> &euro;</td>
                    <td style="<?PHP echo $claseKomKal; ?>" align='right'><input type='text' value='<?PHP echo number_format($data["precioKomKal"],3); ?>' class='precioC' name='preciosKomKal[]' size='5'> &euro;</td>    
                    <td style="<?PHP echo $claseJesamar; ?>" align='right'><input type='text' value='<?PHP echo number_format($data["precioJesamar"],3); ?>' class='precioC' name='preciosJesamar[]' size='5'> &euro;</td>    

                    
                    <td style="<?PHP echo $clase; ?>">
                        <?PHP 
                            
                                echo $margenProducto;  
                            
                        ?>
                    </td>
                    
                </tr>
                <?PHP
                
                if ($contP%2==0)
                    $liniaP=' style="background-color:#eeeeee"';
                else
                    $liniaP=' style="background-color:#ffffff"';
                if ($data["precio"]>0)
                {
                $htmlText.='
                    <tr '.$liniaP.'>
                    <td '.$liniaP.'>'.$data["nombreTipo"].'</td>
                    <td '.$liniaP.'>'.str_replace("'"," ", $data["nombre"]).'</td>
                    <td '.$liniaP.' align="right">'.number_format($data["precio"],3).' &euro;</td>
                    </tr>
                    ';
                }
                $contP++;
                
                $cabecera=$data["nombreTipo"];
            }  
            mysqli_free_result($rsSel);
        ?>
                <tr>
                    <td colspan="4" align="right" valign="center"><b>Total Unidades</b></td>
                    <td colspan="6" align="left" valign="center"><?PHP echo $totalUnidadesVentaMes; ?></td>
                </tr>
                <tr>
                    <td colspan="4" align="right" valign="center"><b>Total Venta Sin Iva</b></td>
                    <td colspan="6" align="left" valign="center"><?PHP echo $totalImporteVentaMes; ?></td>
                </tr>
                <tr>
                    <td colspan="4" align="right" valign="center"><b>Total Compra Sin Iva</b></td>
                    <td colspan="6" align="left" valign="center"><?PHP echo $totalImporteCompraMes; ?></td>
                </tr>
                <tr>
                    <td colspan="4" align="right" valign="center"><b>Total Venta Iva</b></td>
                    <td colspan="6" align="left" valign="center"><?PHP echo $totalImporteVentaMesIva; ?></td>
                </tr>
                <tr>
                    <td colspan="4" align="right" valign="center"><b>Margen</b></td>
                    <td colspan="6" align="left" valign="center"><?PHP echo $totalImporteVentaMes-$totalImporteCompraMes; ?> (<?PHP echo number_format(($totalImporteVentaMes-$totalImporteCompraMes)/$totalImporteVentaMes*100,2); ?>%)</td>
                </tr>
           </table><br/><br/>
           <center><input type="submit" value="Guardar productos"></center>
           
           </form>
        
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
