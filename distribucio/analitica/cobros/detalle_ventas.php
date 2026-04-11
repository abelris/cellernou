<?PHP include("../conf.php"); ?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<?PHP
//Detalles de la compra
$sqlFactura="SELECT a.id as idFactura,a.fecha,b.nombre as nombre,b.cp,b.direccion as direc,b.nif FROM facturas a INNER JOIN clientes b ON a.idCliente=b.id WHERE a.id = ".$_POST["idFactura"];
//echo $sqlCompra;
$rsFactura=mysqli_query($bd,$sqlFactura);
if ($dataFactura=mysqli_fetch_array($rsFactura))
{
    
    //print_r($dataFactura);
    
    $id=$dataFactura["idFactura"];
    $referencia = $dataFactura["referencia"];
    
    
    $nombreEmpresa = $dataFactura["nombre"];
    $cif=$dataFactura["nif"];
    $direccion=$dataFactura["direc"];
    $cp=$dataFactura["cp"];
    $poblacion=$dataFactura["poblacion"];
    $provincia="BARCELONA";
    $fecha=$dataFactura["fecha"];

    ereg( "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha);
    $fecha=$mifecha[3]."/".$mifecha[2]."/".$mifecha[1];

    
}
mysqli_free_result($rsFactura);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php include("../includes/header.php"); ?>
        <h1>Detalle de la Factura <?PHP echo " (".$id.")"; ?></h1>
        <?php
            $sqlSel = "SELECT a.*,b.nombre as producto,a.iva as ivav FROM linea_factura a inner join productos b on a.idProducto=b.id WHERE a.idFactura=".$_POST["idFactura"]." order by a.id";
            //echo $sqlSel;
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST" action="venda.php">
                <input type="hidden" name="idCompra">
            </form>
            <form name="productosCompra" method="POST" action="productos-cliente.php">
                <input type="hidden" name="idCompra">
            </form>
            <form name="eliminaForm" method="POST" action="borrar.php">
                <input type="hidden" name="idCompra">
            </form>
            <?PHP
            $htmlText='
            <table width="100%">
                <tr>
                    <td width="50%">
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td width="50%">
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td width="50%">
                        '.nombreEmpresa.'
                    </td>
                    <td>
                        '.$nombreEmpresa.'
                    </td>
                </tr>
                <tr>
                    <td>
                        '.cif.'
                    </td>
                    <td>
                        '.$cif.'
                    </td>
                </tr>
                <tr>
                    <td>
                        '.direccion.'
                    </td>
                    <td>
                        '.$direccion.'
                    </td>
                </tr>
                <tr>
                    <td>
                        '.cp." ".poblacion." (".provincia.")".'
                    </td>
                    <td>
                        '.$cp." ".$poblacion." (".$provincia.")".'
                    </td>
                </tr>
                <tr>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                       &nbsp;
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Numero Factura:</b> '."FA".str_pad((210400+$id),10,"0",STR_PAD_LEFT).'
                    </td>
                    <td>
                        &nbsp;
                    </td>    
                </tr>
                <tr>
                    <td>
                        <b>Fecha:</b> '.$fecha.'
                    </td>
                    <td>
                        &nbsp;
                    </td>    
                </tr>
            </table>
            <br><br><br><br><br><br>
            ';
            
            
            $htmlText.='
            <table border="0" width="100%">
                <tr>
                    <th width="5%">#</th>
                    <th width="30%" align="left"><b>Producto</b></th>
                    <th width="15%" align="right"><b>Cantidad</b></th>
                    <th width="15%" align="right"><b>Precio/unid.</b></th>
                    <th width="9%" align="right"><b>Desc.</b></th>
                    <th width="13%" align="right"><b>Precio</b></th>
                    <th width="13%" align="right"><b>Total</b></th>
                    <th>&nbsp;</th>
                </tr>
            ';
            
            $totalCompra=0.0;
            $totalIva4=0.0;
            $totalIva10=0.0;
            $totalIva21=0.0;
            while ($data= mysqli_fetch_array($rsSel))
            {
                if ($data["descuento"]==0)
                    $descuento='';
                else
                    $descuento=$data["descuento"];
                
                $htmlText.='
                <tr>
                    <td>'.$data["id"].'</td>
                    <td align="left">'.str_replace("'"," ",$data["producto"]).'</td>
                    <td align="right">'.$data["cantidad"].'</td>
                    <td align="right">'.$data["precio_unidad_sin_desc"].'</td>
                    <td align="right">'.$descuento.'</td>
                    <td align="right">'.$data["precio_unidad"].'</td>
                    <td align="right">
                ';
                     
                        $totalLineaCompra = number_format($data["cantidad"]*$data["precio_unidad"],3);
                        $totalCompra += $data["cantidad"]*$data["precio_unidad"];
                        
                        //Calcul del IVA
                        
                        if ($data["ivav"]==4)
                        {
                            $iva4 = $totalLineaCompra * 0.04;
                            $totalIva4+=$iva4;
                        }
                        
                        if ($data["ivav"]==10)
                        {
                            $iva10 = $totalLineaCompra * 0.10;
                            $totalIva10+=$iva10;
                        }
                        
                        if ($data["ivav"]==21)
                        {
                            $iva21 = $totalLineaCompra * 0.21;
                            $totalIva21+=$iva21;
                        }
                        
                       
                    $htmlText.=$totalLineaCompra.'
                    </td>
                    <td>
                        &nbsp;
                    </td>
                </tr>
                ';
                
            }  
            mysqli_free_result($rsSel);
           
           $htmlText.='
            <tr>
               <td align="right" colspan="6">
                   &nbsp;
               </td>
               <td align="right">
                   &nbsp;
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>    
           <tr>
               <td align="right" colspan="6">
                   <b>TOTAL:</b> 
               </td>
               <td align="right">
                   <b>'.number_format($totalCompra,2).' &euro;</b>
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>
           <tr>
               <td align="right" colspan="6">
                   <b>IVA 21%:</b> 
               </td>
               <td align="right">
                   <b>'.number_format($totalIva21,2).' &euro;</b>
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>
           ';
                   
           if ($totalIva10>0) 
           {
           
           $htmlText.='
           <tr>
               <td align="right" colspan="6">
                   <b>IVA 10%:</b> 
               </td>
               <td align="right">
                   <b>'.number_format($totalIva10,2).' &euro;</b>
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>
           ';
           }
           
           if ($totalIva4>0) 
           {
           
           $htmlText.='
           <tr>
               <td align="right" colspan="6">
                   <b>IVA 4%:</b> 
               </td>
               <td align="right">
                   <b>'.number_format($totalIva10,2).' &euro;</b>
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>
           ';
           }
           
           $htmlText.='
           <tr>
               <td align="right" colspan="6">
                   <b>TOTAL (I.V.A.):</b> 
               </td>
               <td align="right">
                   <b>'.number_format($totalCompra+$totalIva10+$totalIva21,2).' &euro;</b>
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>
           </table><br/><br/>
           ';
           
           echo $htmlText;
           ?>
        <form name="myform" action="../includes/topdf.php" method="POST" target="_blank">
            <input type="hidden" value='<?PHP echo $htmlText; ?>' name="htmlText">
            <input type="hidden" value='1' name="factura">
            <input type="submit" value="Pasar a pdf">
        </form>
    </body>
</html>
