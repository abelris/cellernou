<?PHP include("../conf.php"); ?>
<?PHP

$_SESSION["idUsuario"]=3;

/*
if (!isset($_SESSION["nombreUsuario"]))
{
?>
<script language="javascript">

location='http://www.cellernou.com/distribucio/';

</script>    
<?PHP    
    die();
}   
*/

?>
<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <META HTTP-EQUIV="refresh" CONTENT="15000">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <script language="javascript" src="/distribucio/js/ajax.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

        <!--link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script-->
        
        
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        
        
        
        <link rel="stylesheet" href="/resources/demos/style.css">
        <script>    
        $(function() {
            $("#datepicker").datepicker({dateFormat: "dd-mm-yy"});
        });
        </script>
        
    
    <style>
  #menuSlide {
    border: 1px solid red;
    color: #fff;
    overflow: hidden;
}
        
  .custom-combobox {
    position: relative;
    display: inline-block;
  }
  .custom-combobox-toggle {
    position: absolute;
    top: 0;
    bottom: 0;
    margin-left: -1px;
    padding: 0;
  }
  .custom-combobox-input {
    margin: 0;
    padding: 5px 10px;
  }
  </style>
  <script>
  (function( $ ) {
    $.widget( "custom.combobox", {
      _create: function() {
        this.wrapper = $( "<span>" )
          .addClass( "custom-combobox" )
          .insertAfter( this.element );
 
        this.element.hide();
        this._createAutocomplete();
        this._createShowAllButton();
      },
 
      _createAutocomplete: function() {
        var selected = this.element.children( ":selected" ),
          value = selected.val() ? selected.text() : "";
 
        this.input = $( "<input id='comboText'>" )
          .appendTo( this.wrapper )
          .val( value )
          .attr( "title", "" )
          .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
          .autocomplete({
            delay: 0,
            minLength: 0,
            source: $.proxy( this, "_source" )
          })
          .tooltip({
            tooltipClass: "ui-state-highlight"
          });
 
        this._on( this.input, {
          autocompleteselect: function( event, ui ) {
            ui.item.option.selected = true;
            this._trigger( "select", event, {
              item: ui.item.option
            });
          },
 
          autocompletechange: "_removeIfInvalid"
        });
      },
 
      _createShowAllButton: function() {
        var input = this.input,
          wasOpen = false;
 
        $( "<a>" )
          .attr( "tabIndex", -1 )
          .attr( "title", "Show All Items" )
          .tooltip()
          .appendTo( this.wrapper )
          .button({
            icons: {
              primary: "ui-icon-triangle-1-s"
            },
            text: false
          })
          .removeClass( "ui-corner-all" )
          .addClass( "custom-combobox-toggle ui-corner-right" )
          .mousedown(function() {
            wasOpen = input.autocomplete( "widget" ).is( ":visible" );
          })
          .click(function() {
            input.focus();
 
            // Close if already visible
            if ( wasOpen ) {
              return;
            }
 
            // Pass empty string as value to search for, displaying all results
            input.autocomplete( "search", "" );
          });
      },
 
      _source: function( request, response ) {
        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
        response( this.element.children( "option" ).map(function() {
          var text = $( this ).text();
          if ( this.value && ( !request.term || matcher.test(text) ) )
            return {
              label: text,
              value: text,
              option: this
            };
        }) );
      },
 
      _removeIfInvalid: function( event, ui ) {
 
        // Selected an item, nothing to do
        if ( ui.item ) {
          return;
        }
 
        // Search for a match (case-insensitive)
        var value = this.input.val(),
          valueLowerCase = value.toLowerCase(),
          valid = false;
        this.element.children( "option" ).each(function() {
          if ( $( this ).text().toLowerCase() === valueLowerCase ) {
            this.selected = valid = true;
            return false;
          }
        });
 
        // Found a match, nothing to do
        if ( valid ) {
          return;
        }
 
        // Remove invalid value
        this.input
          .val( "" )
          .attr( "title", value + " didn't match any item" )
          .tooltip( "open" );
        this.element.val( "" );
        this._delay(function() {
          this.input.tooltip( "close" ).attr( "title", "" );
        }, 2500 );
        this.input.autocomplete( "instance" ).term = "";
      },
 
      _destroy: function() {
        this.wrapper.remove();
        this.element.show();
      }
    });
  })( jQuery );
 
  $(function() {
    $( "#combobox" ).combobox();
    $( "#toggle" ).click(function() {
      $( "#combobox" ).toggle();
    });
  });
  </script>    
        
        
    </head>
    <body>       
<?PHP
        
if (isset($_POST["eliminar"])==1)
{
    unset($_SESSION["lineas_venta_tiquet"]);
    unset($_SESSION["lineas_venta_desc"]);
}        
        

if (isset($_POST["idCliente"]) && !empty($_POST["idCliente"]))
{
    $sqlSel="SELECT * FROM clientes_tiquet WHERE id =".$_POST["idCliente"];
    $rsSel = mysqli_query($bd,$sqlSel);
    if ($dataSel=mysqli_fetch_array($rsSel))
    {
        $nombre=$dataSel["nombre"];
    }
    mysqli_free_result($rsSel);
}
else
{
    $_POST["idCliente"]=0;
}


//Realizamos una compra porque apretamos el boton guardar compra.
if (isset($_POST["guardarVenta"]))    //Vol dir que entrem una compra
{
    
    
    if (!empty($_SESSION["lineas_venta_tiquet"]))
    {
        if ($_POST["modificar"]==1) //Vol dir que fem una modificacio
        {
            $idVenta = $_POST["idVenta"];
            //Eliminem la venta anterior
            $modVenta = "DELETE from linea_tiquet where idVenta = ".$idVenta;
            mysqli_query($bd,$modVenta);
        }
        else
        {

        $fechaA = explode("-",$_POST["fecha"]);
        $fecha_cambiada = mktime(date('H'),date('i'),date('s'),$fechaA[1],$fechaA[0],$fechaA[2]);
        $fecha = date("Y-m-d H:i:s", $fecha_cambiada);  

        
        //En primer lloc guardem la linea de compra.
        $sqlInsCompra="
            INSERT INTO tiquets  
            (idCliente,fecha,precio_desc,coste,precio_coste,idTienda,tipo_pago) 
            VALUE 
            ('".$_POST["idCliente"]."','".$fecha."','".$_POST["totalDescuento"]."','".$_POST["costes"]."','".$_POST["totalCostes"]."',".$_SESSION["idUsuario"].",'".$_POST["tipo_pago"]."')";
        mysqli_query($bd,$sqlInsCompra);
        $idVenta= mysqli_insert_id();
        }

        //echo $sqlInsCompra;

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
        if (isset($_SESSION["lineas_venta_tiquet"]))
        {

            //Calculo del total de unidades de compra
            $totalUnidadesVenta=0;
            for($jj=0;$jj<count($_SESSION["lineas_venta_tiquet"]);$jj++)
            {
                $totalUnidadesVenta+=$_SESSION["lineas_venta_tiquet"][$jj]["cantidad"];
            }

            //Antes de introducir los productos, miramos si hay descuento por producto. Si lo hay lo dividimos entro los productos de la compra
            $precioVentaMedio=0;
            if ($_POST["totalDescuento"]>0)
            {
                $precioVentaMedio=number_format($_POST["totalDescuento"]/$totalUnidadesVenta,2);

            }

            for($ii=0;$ii<count($_SESSION["lineas_venta_tiquet"]);$ii++)
            {

                //*******************HA D'AGAFAR EL PREU DE VENTA DEL CLIENT************************

                $precioVentaProd=$_SESSION["lineas_venta_tiquet"][$ii]["precioVenta"];
                if ($precioVentaMedio>0)
                {
                    $precioVentaProd = $_SESSION["lineas_venta_tiquet"][$ii]["precioVenta"]-$precioVentaMedio;
                }
                elseif ($_SESSION["lineas_venta_tiquet"][$ii]["descuento"]>0) 
                {
                    $precioVentaProd = number_format($_SESSION["lineas_venta_tiquet"][$ii]["precioVenta"] * (1-$_SESSION["lineas_venta_tiquet"][$ii]["descuento"] / 100),3);
                }

                $precioCompraTotal=0;
                $lineasP=0;
                $precioCompra=0;


                /*ESTE PROCEDIMIENTO NO TIENE SENTIDO EN LOS GRANELES*/
                if ($_SESSION["lineas_venta_tiquet"][$ii]["procedencia"]!=28)
                {
                    //El preu de compra ha de ser el dels productes mes antics.
                    $sqlLineaCompra="SELECT id,idProducto, precio_compra FROM stock_producto WHERE vendido=0  AND idProducto=".$_SESSION["lineas_venta_tiquet"][$ii]["idProducto"]." AND idTienda=".$_SESSION["idUsuario"]." order by fecha_entrada limit 0,".$_SESSION["lineas_venta_tiquet"][$ii]["cantidad"];
                    $rsLineaCompra=mysqli_query($bd,$sqlLineaCompra);
                    while ($dataLineaCompra=mysqli_fetch_array($rsLineaCompra))
                    {
                        $precioCompraTotal+=$dataLineaCompra["precio_compra"];
                        $sqlUpdLineaCompra="UPDATE stock_producto SET vendido=1, fecha_vendido=NOW() WHERE vendido=0  AND id=".$dataLineaCompra["id"];
                        mysqli_query($bd,$sqlUpdLineaCompra);
                        $lineasP++;
                    }
                    mysqli_free_result($rsLineaCompra);
                }


                //Si el preu de compra calculat en funcio de l'estock es diferent de 0 l'apliquem.
                //echo $precioCompraTotal."::".$lineasP;
                if ($precioCompraTotal!=0)
                {
                    $precioCompra=$precioCompraTotal/$lineasP;
                    $_SESSION["lineas_venta_tiquet"][$ii]["precioCompra"]=$precioCompra;
                }

                $sqlInsVenta="
                    INSERT INTO linea_tiquet (idVenta,idProducto,idCliente,cantidad,precio_unidad,precio_unidad_sin_imp,descuento,precio_unidad_sin_desc,precio_compra,iva) 
                    VALUES 
                    ('".$idVenta."','".$_SESSION["lineas_venta_tiquet"][$ii]["idProducto"]."','".$_POST["idCliente"]."','".$_SESSION["lineas_venta_tiquet"][$ii]["cantidad"]."','".($precioVentaProd)."','".($precioVentaProd/(1+$_SESSION["lineas_venta_tiquet"][$ii]["iva"]/100))."','".$_SESSION["lineas_venta_tiquet"][$ii]["descuento"]."','".$_SESSION["lineas_venta_tiquet"][$ii]["precioVenta"]."','".$_SESSION["lineas_venta_tiquet"][$ii]["precioCompra"]."','".$_SESSION["lineas_venta_tiquet"][$ii]["iva"]."')";
                //echo $sqlInsVenta;
                mysqli_query($bd,$sqlInsVenta);




                //$sqlUpdTiquet="UPDATE stock_producto SET existencias=(existencias-".$_SESSION["lineas_venta_tiquet"][$ii]["cantidad"].") WHERE idProducto = ".$_SESSION["lineas_venta_tiquet"][$ii]["idProducto"]." AND idTienda=".$_SESSION["idUsuario"];
                //echo $sqlUpdTiquet;
                //mysqli_query($bd,$sqlUpdTiquet);    

                //Calcul preu mig producte anual: Calculem el preu mig del producte de forma anual, multiplicant totes les existencies per el seu preu de compra y dividint per el el numero d'exitencies
                //$sqlPrecioMedio = "SELECT SUM(cantidad*precio_unidad)/SUM(cantidad) as precio_medio FROM linea_venta where fecha > '01-01-".date('Y')." 00:00:00' and fecha < '31-12-".date('Y')." 23:59:59' and idProducto = ".$_SESSION["lineas_compra"][$ii]["idProducto"];
                //echo $sqlPrecioMedio;
                //$rsPrecioMedio = mysqli_query($bd,$sqlPrecioMedio);
                //if ($dataPrecioMedio = mysql_fetch_array($rsPrecioMedio))
                //{
                //    $sqlUpdProd="UPDATE productos SET precio_medio = ".$dataPrecioMedio["precio_medio"]." WHERE id = ".$_SESSION["lineas_compra"][$ii]["idProducto"];
                //    mysqli_query($bd,$sqlUpdProd);
                //}
            }
        }

        unset($_SESSION["lineas_venta_tiquet"]);
        unset($_SESSION["lineas_venta_desc"]);

        echo "Tiquet introducida";
    }
    else
    {
        echo "No se ha guardado el tiquet porque no existe ningún producto.";
    }
    
}



?>
        <h1>
            <?PHP 
            if ($_POST["modificar"])
            {    
            ?>
            Modificar Ticket <?PHP echo $_POST["idVenta"]; ?>
            <?PHP
            }else{
            ?>
            Nou Ticket 
            <?PHP
            }
            ?>
        </h1>
        
        
            
            
        
          
        </div>
            <!--form name="lineaTiquetForm" method="POST" id="lineaTiquetForm">
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
        </form-->
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
                        $sqlProducto="Select a.id,a.nombre,e.pvp from productos a inner join precios_producto_tienda e on a.id=e.idProducto WHERE e.idTienda = 1 order by a.nombre";
                        $rsProducto=mysqli_query($bd,$sqlProducto);
                        ?>
                        <div class="ui-widget">
                        <select name="idProducto" id="combobox">
                            <option value=""></option>
                        <?PHP
                        while($dataProducto=mysqli_fetch_array($rsProducto))
                        {
                            if (!empty($dataProducto["nombre"]))
                            {
                            ?>
                            <option value="<?PHP echo $dataProducto["id"]; ?>"><?PHP echo $dataProducto["nombre"]." (".$dataProducto["pvp"].")"; ?></option>
                            <?PHP
                            }
                        }
                        mysqli_free_result($rsProducto);
                        ?>
                        </select>
                        </div>
                            <br />
                    </td>
                    <td align="center"><input type="text" name="cantidad" value="1"></td>
                    <td align="center"><input type="text" name="descuento" class="precioC" value="0.00"></td>                    
                    <td align="center"><input style="font-size: 20px;" type="button" name="altaLinea" value="Introduir Producte" onclick="document.getElementById('comboText').focus();document.getElementById('comboText').select();FAjax('alta_linea_paca.php','lineasCompraDiv','lineasVentaForm','POST')"></td>
                </tr>
            </table>
        </form> 
        
        <div id="lineasCompraDiv">  <!--Div on cargarem les lines de compra amb ajax-->
            
            
        </div>
        <br/><br/>
        <div id="cabeceraCompra"> 
           
            <form name="compraForm" method="post">
            <input type="hidden" name="guardarVenta" value="1">
            <?PHP
                 
            if ($_POST["modificar"])
            { 
            ?>
            <input type="hidden" name="modificar" value="1">
            <input type="hidden" name="idVenta" value="<?PHP echo $_POST["idVenta"]; ?>">
            
            <?PHP    
            }
                
                        $sqlCliente="Select * from clientes_tiquet WHERE idUsuario=".$_SESSION["idUsuario"]." order by nombre";
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
                        fecha venta: <input type="text" name="fecha" id="datepicker" value="<?PHP echo date('d-m-Y'); ?>"></br>
                        Forma de Pago: 
                        <select name="tipo_pago">
                            <option value="C">Contado</option>
                            <option value="V">Visa</option>
                        </select>    
                        <center>
                            
                            <!--input type="button" onclick="alert(document.getElementById('numLinias').value);" value="Guardar tiquet" name="guardarVenta"-->
                            
                        </center>
                        
            </form>
        <form name="eliminaCompraForm" method="POST">
                <input type="hidden" name="eliminar" value="1">
                <input type="hidden" name="idCliente" value="<?PHP echo $_POST["idCliente"]; ?>">
                <center>
                    <input type="button" style="font-size: 20px;" onclick="document.compraForm.submit()"  value="Guardar tiquet" name="guardarVenta1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input onclick="if (confirm('¿Desea borrar el tiquet en curso?')) { document.eliminaCompraForm.submit(); } " style="font-size: 20px;" type="button" value="Borrar tiquet"></center>
            </form>
            <br /><br /><br /><br /><br />
            
            
            <script language='javascript'>
            $(document).ready(function () {
                $('.precioC').keypress(function(event) {
                    $(this).val($(this).val().replace(",","."));
                    if(event.which == 8 || event.which == 0 || event.which == 44){
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
                        $('#cantidad').focus();
                        $('#cantidad').val(1);
                        $('#cantidad').select();

                        return true;
                    }
                    // prevent if already dot
                });
                
                $('.cantidadC').keypress(function(event) {
                    if(event.which == 13 || event.which == 13){
                        $('#codigo').focus();
                        $('#codigo').select();
                        FAjax('alta_linea_paca.php','lineasCompraDiv','lineaTiquetForm','POST')
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
        <?PHP    
        }
        ?>
        <script language="javascript">
            document.lineaTiquetForm.codigo.focus();
           
        $('#menuBtt').click(function() {
    $('#menuSlide').animate({
        height: 'toggle'
    }, 1500, function() {
    });
});
</script>
    </body>
</html>
