<?PHP include("../conf.php"); ?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<?PHP
//Detalles de la compra
$sqlFactura="SELECT a.comentarios,a.idCliente, a.id as idFactura,a.idVenta,a.fecha,a.fecha_vencimiento,a.idFormaPago,b.nombre as nombre, b.nombre_fiscal,b.cp,b.poblacion,b.direccion as direc,b.nif,b.provincia,b.recarrec_equiv FROM facturas a INNER JOIN clientes b ON a.idCliente=b.id WHERE a.id = ".$_POST["idFactura"];
//echo $sqlCompra;
$rsFactura=mysqli_query($bd,$sqlFactura);
if ($dataFactura=mysqli_fetch_array($rsFactura))
{
    
    //print_r($dataFactura);
    
    $id=$dataFactura["idFactura"];
    $idVenta = $dataFactura["idVenta"];
    $referencia = $dataFactura["referencia"];
    
    
    $nombreEmpresa = $dataFactura["nombre"];
    $nombreFiscal=$dataFactura["nombre_fiscal"];
    $cif=$dataFactura["nif"];
    $direccion=$dataFactura["direc"];
    $cp=$dataFactura["cp"];
    $poblacion=$dataFactura["poblacion"];
    $provincia=$dataFactura["provincia"];
    $fecha=$dataFactura["fecha"];
    $fechaV=$dataFactura["fecha_vencimiento"];
    $idFormaPago = $dataFactura["idFormaPago"];
    $comentarios = $dataFactura["comentarios"];

    $recarrecEquiv = $dataFactura["recarrec_equiv"];

    //Fechas del documento
    $fechaIni=new DateTime($fecha);
    $fecha=$fechaIni->format("d-m-Y H:i:s");
    //$first_date = new DateTime(date("Y-m-d H:i:s"));
    
    $fechaFin=new DateTime($fechaV);
    $fechaV=$fechaFin->format("d-m-Y H:i:s");
    //$first_date = new DateTime(date("Y-m-d H:i:s"));

    
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
                    <td width="50%">
                        
                    </td>
                    <td>
                        '.$nombreFiscal.'
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
                        <b>Numero Venta:</b> '.$idVenta.'
                    </td>
                    <td>
                       &nbsp;
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Numero Factura:</b> '."FA".str_pad(((date('Y')*100)."".$id),10,"0",STR_PAD_LEFT).'
                    </td>
                    <td>
                        &nbsp;
                    </td>    
                </tr>
                <tr>
                    <td colspan=2>
                        <b>Fecha Factura:</b> '.$fecha.'
                    </td>
                        
                </tr>
                <tr>
                    <td>
                        <b>Fecha Vencimiento:</b> '.$fechaV.'
                    </td>
                    <td>
                        <b>Comentario:</b> '.$comentarios.'
                    </td>    
                </tr>
                ';
                if ($idFormaPago==2)
                {
                $htmlText.='<tr>
                    <td colspan=2>
                        <b>Pago:</b> Transferencia a la cuenta: 0081-0379-85-0001264437
                    </td>
                       
                </tr>';
                }   
            $htmlText.='</table>
            <br><br><br><br><br><br>
            ';
            
            
            $htmlText.='
            <table border="0" width="100%">
                <tr>
                    <th width="5%">#</th>
                    <th width="36%" align="left"><b>Producto</b></th>
                    <th width="12%" align="right"><b>Cantidad</b></th>
                    <th width="12%" align="right"><b>Precio/unid.</b></th>
                    <th width="7%" align="right"><b>Desc.</b></th>
                    <th width="13%" align="right"><b>Precio</b></th>
                    <th width="7%" align="right"><b>Iva</b></th>
                    <th width="13%" align="right"><b>Total</b></th>
                    <th>&nbsp;</th>
                </tr>
            ';
            $iii=1;
            $totalCompra=0.0;
            
            $totalIva0=0.0;
            $totalIva4=0.0;
            $totalIva10=0.0;
            $totalIva21=0.0;
            
            $totalBase0=0.0;
            $totalBase4=0.0;
            $totalBase10=0.0;
            $totalBase21=0.0;
            
            $totalRecarrec0=0.0;
            $totalRecarrec4=0.0;
            $totalRecarrec10=0.0;
            $totalRecarrec21=0.0;
            
            while ($data= mysqli_fetch_array($rsSel))
            {
                if ($data["descuento"]==0)
                    $descuento='';
                else
                    $descuento=$data["descuento"];
                
                $htmlText.='
                <tr>
                    <td align="center">'.$iii.'</td>
                    <td align="left">'.str_replace("'"," ",$data["producto"]).'</td>
                    <td align="right">'.$data["cantidad"].'</td>
                    <td align="right">'.$data["precio_unidad_sin_desc"].'</td>
                    <td align="right">'.$descuento.'</td>
                    <td align="right">'.$data["precio_unidad"].'</td>
                    <td align="right">'.$data["iva"].'</td>    
                    <td align="right">
                ';
                     
                        $totalLineaCompra = $data["cantidad"]*$data["precio_unidad"];
                        $totalCompra += $data["cantidad"]*$data["precio_unidad"];
                        
                        //Calcul del IVA
                        
                        if ($data["ivav"]==0)
                        {
                            $iva0 = 0;
                            $totalIva0+=$iva0;
                            $totalBase0+=$totalLineaCompra;
                        }
                        
                        if ($data["ivav"]==4)
                        {
                            $iva4 = $totalLineaCompra * 0.04;
                            $totalIva4+=$iva4;
                            $totalBase4+=$totalLineaCompra;
                        }
                        
                        if ($data["ivav"]==10)
                        {
                            $iva10 = $totalLineaCompra * 0.10;
                            $totalIva10+=$iva10;
                            $totalBase10+=$totalLineaCompra;
                        }
                        
                        if ($data["ivav"]==21)
                        {
                            $iva21 = $totalLineaCompra * 0.21;
                            $totalIva21+=$iva21;
                            $totalBase21+=$totalLineaCompra;
                        }
                        
                        if($recarrecEquiv)
                        {
                            //Calcul del Recarrec d'equivalencia
                            if ($data["ivav"]==4)
                            {
                                $recarrec4 = $totalLineaCompra * 0.005;
                                $totalRecarrec4+=$recarrec4;
                            }

                            if ($data["ivav"]==10)
                            {
                                $recarrec10 = $totalLineaCompra * 0.014;
                                $totalRecarrec10+=$recarrec10;
                            }

                            if ($data["ivav"]==21)
                            {
                                $recarrec21 = $totalLineaCompra * 0.052;
                                $totalRecarrec21+=$recarrec21;
                            }
                        }
                        
                       
                    $htmlText.=number_format($totalLineaCompra,3).'
                    </td>
                    <td>
                        &nbsp;
                    </td>
                </tr>
                ';
                $iii++;
            }  
            mysqli_free_result($rsSel);
           
           $htmlText.='
            <tr>
               <td align="right" colspan="5">
                   &nbsp;
               </td>
               <td align="right" colspan="2">
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
               <td align="right" colspan="5">
                   <b>TOTAL:</b> 
               </td>
               <td align="right" colspan="2">
                   &nbsp;
               </td>
               <td align="right">
                   <b>'.number_format($totalCompra,2).' &euro;</b>
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>
           <tr>
               <td align="right" colspan="5">
                   <b>IVA 21%:</b> 
               </td>
               <td align="right" colspan="2">
                   <b>Base:'.$totalBase21.' &euro;</b>
               </td>
               <td align="right">
                   <b>'.number_format($totalIva21,2).' &euro;</b>
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>
           ';
            
            if ($totalRecarrec21>0)
            {    
                $htmlText.='
                    <tr>
                    <td align="right" colspan="5">
                        <b>Recarrec 5.2%:</b> 
                    </td>
                    <td align="right" colspan="2">
                        &nbsp;
                    </td>
                    <td align="right">
                        <b>'.number_format($totalRecarrec21,2).' &euro;</b>
                    </td>
                    <td align="right">
                        &nbsp;
                    </td>
                </tr>';  
            }
           
           if ($totalIva10>0) 
           {
           
           $htmlText.='
           <tr>
               <td align="right" colspan="5">
                   <b>IVA 10%:</b> 
               </td>
               <td align="right" colspan="2">
                   <b>Base: '.$totalBase10.' &euro;</b>
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
           
           if ($totalRecarrec10>0)
            {    
                $htmlText.='
                    <tr>
                    <td align="right" colspan="5">
                        <b>Recarrec 1.4%:</b> 
                    </td>
                    <td align="right" colspan="2">
                        &nbsp;
                    </td>
                    <td align="right">
                        <b>'.number_format($totalRecarrec10,2).' &euro;</b>
                    </td>
                    <td align="right">
                        &nbsp;
                    </td>
                </tr>';  
            }
           
            
           if ($totalBase0>0) 
           {
           
           $htmlText.='
           <tr>
               <td align="right" colspan="5">
                   <b>IVA 0%:</b> 
               </td>
               <td align="right" colspan="2">
                   <b>Base: '.number_format($totalBase0,2).' &euro;</b>
               </td>
               <td align="right">
                   <b>'.number_format($totalIva0,2).' &euro;</b>
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
               <td align="right" colspan="5">
                   <b>IVA 4%:</b> 
               </td>
               <td align="right" colspan="2">
                   <b>Base: '.$totalBase4.' &euro;</b>
               </td>
               <td align="right">
                   <b>'.number_format($totalIva4,2).' &euro;</b>
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>
           ';
           }
           
           $htmlText.='
           <tr>
               <td align="right" colspan="5">
                   <b>TOTAL (I.V.A.):</b> 
               </td>
               <td align="right" colspan="2">
                   &nbsp;
               </td>
               <td align="right">
                   <b>'.number_format($totalCompra+$totalIva4+$totalIva10+$totalIva21+$totalRecarrec21,2).' &euro;</b>
               </td>
               <td align="right">
                   &nbsp;
               </td>
           </tr>
           </table><br/><br/>
           ';
           
           $htmlText.='<br /><br />';
           if ($dataFactura["idCliente"]==530 || $dataFactura["idCliente"]==335 || $dataFactura["idCliente"]==468 || $dataFactura["idCliente"]==431 || $dataFactura["idCliente"]==389 || $dataFactura["idCliente"]==379 || $dataFactura["idCliente"]==418 || $dataFactura["idCliente"]==390 || $dataFactura["idCliente"]==341 || $dataFactura["idCliente"]==422)
           {
               $htmlText.= "TR: ES20 0081 0379 8500 0126 4437";
           }
           
           echo $htmlText;
           ?>
        <form name="myform" action="../includes/topdf.php" method="POST" target="_blank">
            <input type="hidden" value='<?PHP echo $htmlText; ?>' name="htmlText">
            <input type="hidden" value='1' name="factura">
            <input type="hidden" value='<?PHP echo "Factura ".$id; ?>' name="nombreFact">
            <input type="submit" value="Pasar a pdf">
        </form>
    </body>
</html>
