<?PHP include("../conf.php"); ?>
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

    </head>
    <body>
        
        
<?php include("../includes/header.php"); 

        
if (isset($_POST["eliminar"])==1)
{
    unset($_SESSION["lineas_venta"]);
    unset($_SESSION["lineas_venta_desc"]);
}        
        

if (isset($_POST["idCliente"]))
{
    $sqlSel="SELECT * FROM clientes WHERE id =".$_POST["idCliente"];
    $rsSel = mysqli_query($bd,$sqlSel);
    if ($dataSel=  mysqli_fetch_array($rsSel))
    {
        $nombre=$dataSel["nombre"];
    }
    mysqli_free_result($rsSel);
}

if (isset($_POST["guardarVenta"]))    //Vol dir que entrem una compra
{
    
    //En primer lloc guardem la linea de compra.
    $sqlInsCompra="
        INSERT INTO ventas 
        (idCliente,precio_desc,coste,precio_coste) 
        VALUE 
        ('".$_POST["idCliente"]."','".$_POST["totalDescuento"]."','".$_POST["costes"]."','".$_POST["totalCostes"]."')";
    mysqli_query($bd,$sqlInsCompra);
    $idVenta= mysqli_insert_id($bd);
    
    //Ahora entramos las lineas de descuento si las hay;
    if (isset($_SESSION["lineas_venta_desc"]))
    {
        for($ii=0;$ii<count($_SESSION["lineas_venta_desc"]);$ii++)
        {
            $sqlInsDesc="
                INSERT INTO linea_venta_desc (idVenta,idProducto,idCliente,cantidad,precio_unidad) 
                VALUES 
                ('".$idVenta."','".$_SESSION["lineas_venta_desc"][$ii]["idProducto"]."','".$_POST["idCliente"]."','".$_SESSION["lineas_venta_desc"][$ii]["cantidad"]."','".$_SESSION["lineas_venta_desc"][$ii]["precio_medio"]."')";
                //echo $sqlInsDesc;
            mysqli_query($bd,$sqlInsDesc);
        }
    }
    
    
    //Ahora entramos las lineas de compra si las hay;
    if (isset($_SESSION["lineas_venta"]))
    {
        
        //Calculo del total de unidades de compra
        $totalUnidadesVenta=0;
        for($jj=0;$jj<count($_SESSION["lineas_venta"]);$jj++)
        {
            $totalUnidadesVenta+=$_SESSION["lineas_venta"][$jj]["cantidad"];
        }
        
        //Antes de introducir los productos, miramos si hay descuento por producto. Si lo hay lo dividimos entro los productos de la compra
        $precioVentaMedio=0;
        if ($_POST["totalDescuento"]>0)
        {
            $precioVentaMedio=number_format($_POST["totalDescuento"]/$totalUnidadesVenta,2);
            
        }
        
        for($ii=0;$ii<count($_SESSION["lineas_venta"]);$ii++)
        {
            
            //*******************HA D'AGAFAR EL PREU DE VENTA DEL CLIENT************************
            
            $precioVentaProd=$_SESSION["lineas_venta"][$ii]["precioVenta"];
            if ($precioVentaMedio>0)
            {
                $precioVentaProd = $_SESSION["lineas_venta"][$ii]["precioVenta"]-$precioVentaMedio;
            }
            elseif ($_SESSION["lineas_venta"][$ii]["descuento"]>0) 
            {
                $precioVentaProd = number_format($_SESSION["lineas_venta"][$ii]["precioVenta"] * (1-$_SESSION["lineas_venta"][$ii]["descuento"] / 100));
            }
                
                
            
            $sqlInsVenta="
                INSERT INTO linea_venta (idVenta,idProducto,idCliente,cantidad,precio_unidad,descuento,precio_unidad_sin_desc,precio_compra,iva) 
                VALUES 
                ('".$idVenta."','".$_SESSION["lineas_venta"][$ii]["idProducto"]."','".$_POST["idCliente"]."','".$_SESSION["lineas_venta"][$ii]["cantidad"]."','".($precioVentaProd)."','".$_SESSION["lineas_venta"][$ii]["descuento"]."','".$_SESSION["lineas_venta"][$ii]["precioVenta"]."','".$_SESSION["lineas_venta"][$ii]["precioCompra"]."','".$_SESSION["lineas_venta"][$ii]["iva"]."')";
            //echo $sqlInsVenta;
            mysqli_query($bd,$sqlInsVenta);
            
            
            //Calcul preu mig producte anual: Calculem el preu mig del producte de forma anual, multiplicant totes les existencies per el seu preu de compra y dividint per el el numero d'exitencies
            //$sqlPrecioMedio = "SELECT SUM(cantidad*precio_unidad)/SUM(cantidad) as precio_medio FROM linea_venta where fecha > '01-01-".date('Y')." 00:00:00' and fecha < '31-12-".date('Y')." 23:59:59' and idProducto = ".$_SESSION["lineas_compra"][$ii]["idProducto"];
            //echo $sqlPrecioMedio;
            //$rsPrecioMedio = mysqli_query($bd,$sqlPrecioMedio);
            //if ($dataPrecioMedio = mysqli_fetch_array($rsPrecioMedio))
            //{
            //    $sqlUpdProd="UPDATE productos SET precio_medio = ".$dataPrecioMedio["precio_medio"]." WHERE id = ".$_SESSION["lineas_compra"][$ii]["idProducto"];
            //    mysqli_query($bd,$sqlUpdProd);
            //}
        }
    }
    
    unset($_SESSION["lineas_venta"]);
    unset($_SESSION["lineas_venta_desc"]);
    
    echo "Venta introducida";
    
}

?>
        <h1>Nova Venta</h1>
        
        <div id="cabeceraCompra" style="background-color: activeborder;"> 
            <form name="compraForm" method="post">
            <?PHP
                        $sqlCliente="Select * from clientes order by nombre";
                        $rsCliente=mysqli_query($bd,$sqlCliente);
                        ?>
                        Cliente: <select name="idCliente" onchange="document.lineaVentaForm.idCliente.value=this.value">
                            <option value="0">-Seleccionar Cliente-</option>
                        <?PHP
                        while($dataCliente=mysqli_fetch_array($rsCliente))
                        {
                            ?>
                            <option value="<?PHP echo $dataCliente["id"]; ?>" <?PHP if ($dataCliente["id"]==$_POST["idCliente"]) { echo "selected='selected'"; } ?>><?PHP echo $dataCliente["nombre"]; ?></option>
                            <?PHP
                        }
                        mysqli_free_result($rsCliente);
                        ?>
                        </select><br />
                        <h2>Descompte</h2>
                        <div id="lineasCompraDescDiv">  <!--Div on cargarem les lines de compra amb ajax-->
                                Total descompte euros: <input type="text" value="0" name="totalDescuento" class="precioC"><br />
                        </div>
                        Total descompte %: <input type="text" value="0" name="totalDescuentoPorCiento" class="precioC">
                        <h2>Costos</h2>
                        Altres costos (Punt verd...):<input type="text" value="" name="costes">
                        Cost:<input type="text" value="0" name="totalCostes" class="precioC">
                        <br />
                        <br />
                        <center>
                            <input type="submit" value="Guardar venta" name="guardarVenta">
                        </center>
                        <br />
                        <br />
            </form>
            <form name="eliminaCompraForm" method="POST">
                <input type="hidden" name="eliminar" value="1">
                <input type="hidden" name="idCliente" value="<?PHP echo $_POST["idCliente"]; ?>">
                <center><input type="submit" value="Borrar venta"></center>
            </form>
        
        <form name="lineaCompraDescForm" method="POST" id="lineaCompraDescForm">
            <table width="100%;" style="border: 1px solid cornflowerblue;">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th></th>
                </tr>
                <tr>
                    <td align="center">
                        <?PHP
                        $sqlProducto="Select * from productos order by nombre";
                        $rsProducto=mysqli_query($bd,$sqlProducto);
                        ?>
                        <select name="idProducto">
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
                    <td align="center"><input type="text" name="cantidad" value="0"></td>
                    <td align="center"><input type="button" name="altaLinea" value="Introduir Producte" onclick="FAjax('alta_linea_desc.php','lineasCompraDescDiv','lineaCompraDescForm','POST')"></td>
                </tr>
            </table>
        </form>  
        </div>
        
        <br /><br />
        <h2>Lineas Venta</h2>
        <div id="lineasCompraDiv">  <!--Div on cargarem les lines de compra amb ajax-->
            
            
        </div>
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
                    <td align="center"><input type="text" name="cantidad" value="0"></td>
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
            });
        </script>
    </body>
</html>
