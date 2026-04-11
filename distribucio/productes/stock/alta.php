<?PHP include("../../conf.php"); ?>
<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <script language="javascript" src="/distribucio/js/ajax.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
        <link rel="stylesheet" href="/resources/demos/style.css">
        <script>    
        $(function() {
            $("#datepicker").datepicker({dateFormat: "dd-mm-yy"});
        });
        </script>
        
        
    </head>
    <body>
        
        
<?php include("../../includes/header.php"); 

        
if (isset($_POST["eliminar"])==1)
{
    unset($_SESSION["lineas_stock"]);
    unset($_SESSION["lineas_venta_desc"]);
}        
        



if (isset($_POST["guardarStock"]))    //Vol dir que entrem una compra
{
    
    
    $strMovimiento="<b>Movimientos de Estoc</b> </br></br>";
    
    //Por cada linea hacemos en cambio de tienda
    if (isset($_SESSION["lineas_stock"]))
    {
        
        
        //Guardamos el cambio en la base de datos.
        $sqlTraspas="INSERT into stock_traspasos (fecha,idTienda,idTiendaOrigen,idTiendaDestino) VALUES ('".date('Y-m-d H:i:s')."','".$_SESSION["idUsuario"]."','".$_POST["idTiendaOrigen"]."','".$_POST["idTiendaDestino"]."')";
        mysqli_query($bd,$sqlTraspas);
        $idTraspaso=mysqli_insert_id();
        
        //Para todas y cada una de las unidades seleccionadas, cambiamos su tienda y registramos el cambio
        $totalUnidadesVenta=0;
        for($jj=0;$jj<count($_SESSION["lineas_stock"]);$jj++)
        {
            
            $sqlTraspasosLineas="INSERT INTO stock_traspasos_lineas (idTraspaso,idProducto,cantidad) VALUES ('".$idTraspaso."','".$_SESSION["lineas_stock"][$jj]["idProducto"]."','".$_SESSION["lineas_stock"][$jj]["cantidad"]."')";
            mysqli_query($bd,$sqlTraspasosLineas);
            
            
            $sqlProd2="SELECT * FROM productos WHERE id=".$_SESSION["lineas_stock"][$jj]["idProducto"];
            $rsProd2=mysqli_query($bd,$sqlProd2);
            if ($dataProd2=mysqli_fetch_array($rsProd2))
            {
                $strMovimiento.=" ".$dataProd2["nombre"].": ".$_SESSION["lineas_stock"][$jj]["cantidad"]."<br>";
            }
            
            
            $totalUnidadesVenta+=$_SESSION["lineas_stock"][$jj]["cantidad"];
            
            $sqlLineaCompra="SELECT id,idProducto, precio_compra FROM stock_producto WHERE vendido=0  AND idProducto=".$_SESSION["lineas_stock"][$jj]["idProducto"]." AND idTienda=".$_POST["idTiendaOrigen"]." order by fecha_entrada limit 0,".$_SESSION["lineas_stock"][$jj]["cantidad"];
            //echo $sqlLineaCompra;
            $rsLineaCompra=mysqli_query($bd,$sqlLineaCompra);
            while ($dataLineaCompra=mysqli_fetch_array($rsLineaCompra))
            {
                $precioCompraTotal+=$dataLineaCompra["precio_compra"];
                $sqlUpdLineaCompra="UPDATE stock_producto SET idTienda=".$_POST["idTiendaDestino"]." WHERE vendido=0  AND id=".$dataLineaCompra["id"];
                mysqli_query($bd,$sqlUpdLineaCompra);
                $lineasP++;
            }
            mysqli_free_result($rsLineaCompra);
        }
        
    }
    
    unset($_SESSION["lineas_stock"]);
    
    echo "Movimiento de Stock Realizado. Mail Enviado ";
    
    $sCabeceras = "";
    $sCabeceras .= "MIME-version: 1.0\n";
    $sCabeceras .= "From: Cellernou <cellernou@cellernou.com>\r\n";
    //dirección de respuesta, si queremos que sea distinta que la del remitente
    //$sCabeceras .= "Bcc: abelris@hotmail.com\r\n";
    $sCabeceras .= "Content-type: text/html;\n";
    
    
    mail("cellernou@cellernou.com","Movimiento Stock",$strMovimiento,$sCabeceras);
    mail("abel@arismesa.com","Movimiento Stock",$strMovimiento,$sCabeceras);
}



?>
        <h1>
            <?PHP 
            if ($_POST["modificar"])
            {    
            ?>
            Modificar Movimientos Stock <?PHP echo $_POST["idVenta"]; ?>
            <?PHP
            }else{
            ?>
            Moviminetos Stock 
            <?PHP
            }
            ?>
        </h1>
        
        <div id="cabeceraCompra" style="background-color: #5f83ba;"> 
            <form name="eliminaCompraForm" method="POST">
                <input type="hidden" name="eliminar" value="1">
                <input type="hidden" name="idCliente" value="<?PHP echo $_POST["idCliente"]; ?>">
                <center><input type="submit" value="Borrar tiquet"></center>
            </form>
            <p>&nbsp;</p>
                        <p>&nbsp;</p>
                        <p>&nbsp;</p>
            <form name="compraForm" method="post">
            <?PHP
                
                        $sqlTienda="Select * from usuarios  order by nombre";
                        $rsTienda=mysqli_query($bd,$sqlTienda);
                        ?>
                        Tienda Origen: <select name="idTiendaOrigen">
                            <option value="0">-Seleccionar Tienda-</option>
                        <?PHP
                        while($dataTienda=mysqli_fetch_array($rsTienda))
                        {
                            ?>
                            <option value="<?PHP echo $dataTienda["id"]; ?>"><?PHP echo $dataTienda["nombre"]; ?></option>
                            <?PHP
                        }
                        mysqli_free_result($rsTienda);
                        ?>
                        </select>-------->
                        <?PHP
                        $sqlTienda="Select * from usuarios  order by nombre";
                        $rsTienda=mysqli_query($bd,$sqlTienda);
                        ?>
                        Tienda Destino: <select name="idTiendaDestino">
                            <option value="0">-Seleccionar Tienda-</option>
                        <?PHP
                        while($dataTienda=mysqli_fetch_array($rsTienda))
                        {
                            ?>
                            <option value="<?PHP echo $dataTienda["id"]; ?>"><?PHP echo $dataTienda["nombre"]; ?></option>
                            <?PHP
                        }
                        mysqli_free_result($rsTienda);
                        ?>
                        </select><br />
                        fecha movimiento: <input type="text" name="fecha" id="datepicker" value="<?PHP echo date('d-m-Y'); ?>">
                        <center>
                            <input type="submit" value="Realizar movimiento de Stock" name="guardarStock">
                        </center>
                        
            </form>
            
            
        
          
        </div>
        
        <br /><br />
        <h2>Lineas Venta</h2>
        <div id="lineasCompraDiv">  <!--Div on cargarem les lines de compra amb ajax-->
            
            
        </div>
        <br/><br/>
        <form name="lineaTiquetForm" method="POST" id="lineaTiquetForm">
            <input type="hidden" name="idCliente" value="<?PHP echo $_POST["idCliente"]; ?>">
            <table width="100%;" style="border: 1px solid cornflowerblue;">
                <tr>
                    <th>Producte</th>
                    <th>Quantitat</th>
                    <th>Descompte</th>
                    <th></th>
                </tr>
                <tr>
                    <td align="center">
                        <input type="text" name="codigo" class="codeC" id="codigo">
                    </td>
                    <td align="center"><input type="text" name="cantidad" value="" id="cantidad" class="cantidadC"></td>
                    <td align="center"><input type="text" name="descuento" class="precioC" value="0.00"></td>                    
                    <td align="center"><input type="button" name="altaLinea" value="Introduir Producte" onclick="document.getElementById('codigo').focus();FAjax('alta_linea_t.php','lineasCompraDiv','lineaTiquetForm','POST')"></td>
                </tr>
            </table>
        </form>
        <br /><br />
        <form name="lineaVentaForm" method="POST" id="lineasVentaForm">
            <input type="hidden" name="idCliente" value="<?PHP echo $_POST["idCliente"]; ?>">
            <table width="100%;" style="border: 1px solid cornflowerblue;">
                <tr>
                    <th>Producte</th>
                    <th>Quantitat</th>
                    <th>Descompte</th>
                    <th></th>
                </tr>
                <tr>
                    <td align="center">
                        <?PHP
                        $sqlProducto="Select * from productos order by nombre";
                        $rsProducto=mysqli_query($bd,$sqlProducto);
                        ?>
                        <select name="idProducto" id="idProducto">
                        <?PHP
                        while($dataProducto=mysqli_fetch_array($rsProducto))
                        {
                            ?>
                            <option value="<?PHP echo $dataProducto["id"]; ?>"><?PHP echo $dataProducto["nombre"]; ?></option>
                            <?PHP
                        }
                        mysqli_free_result($rsProducto);
                        ?>
                        </select><br />
                    </td>
                    <td align="center"><input type="text" name="cantidad" value="1"></td>
                    <td align="center"><input type="text" name="descuento" class="precioC" value="0.00"></td>                    
                    <td align="center"><input type="button" name="altaLinea" value="Introduir Producte" onclick="document.getElementById('idProducto').focus();FAjax('alta_linea.php','lineasCompraDiv','lineasVentaForm','POST')"></td>
                </tr>
            </table>
        </form>    
            <br /><br /><br /><br /><br />
            
            
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
                
                $('.codeC').keypress(function(event) {
                    if(event.which == 13 || event.which == 13){
                        document.getElementById('cantidad').focus();
                        document.getElementById('cantidad').value="1";
                        document.getElementById('cantidad').select();

                        return true;
                    }
                    // prevent if already dot
                });
                
                $('.cantidadC').keypress(function(event) {
                    if(event.which == 13 || event.which == 13){
                        document.getElementById('codigo').focus();
                        FAjax('alta_linea_t.php','lineasCompraDiv','lineaTiquetForm','POST')
                        return true;
                    }
                    // prevent if already dot
                });
                
            });
        </script>
        <?PHP
        //echo $_POST["modificar"]."jjjj";
        if ($_POST["modificar"]==1) //Si es una modificació...
        {
        ?>
        <form name="modVentaForm" id="modVentaForm" method="POST">
                <input type="hidden" name="idVenta">
        </form>
        <script language="javascript">
            document.modVentaForm.idVenta.value=<?PHP echo $_POST["idVenta"]; ?>;
            FAjax('modificar_lineas.php','lineasCompraDiv','modVentaForm','POST')
        </script>    
        <?    
        }
        ?>
        <script language="javascript">
            document.lineaTiquetForm.codigo.focus();
        </script>   
    </body>
</html>
