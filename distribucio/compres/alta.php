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
        <script language="javascript" src="/distribucio/js/lightboxOwn.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!--link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smo othness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script-->
        
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        
        
        <link rel="stylesheet" href="/resources/demos/style.css">
        <link rel="stylesheet" href="/distribucio/css/estils.css">
        <link rel="stylesheet" type="text/css" href="/distribucio/css/lightboxOwn.css" />        
        
        <script>
        $(function() {
            $("#datepicker").datepicker({dateFormat: "dd/mm/yy"});
            $("#datepicker2").datepicker({dateFormat: "dd/mm/yy"});
        });
        </script>
        <script language="javascript">
            function calculaDescuentoPorCiento()
            {
               
               precio = document.lineaCompraForm.precioCompra.value;
               descuento = document.lineaCompraForm.descuentoP.value;
               
               descuentoPorCiento = descuento * 100 / precio;
               document.lineaCompraForm.descuento.value=descuentoPorCiento;
               
            }
            
            function calculaDescuentoPrecio()
            {
               precio = document.lineaCompraForm.precioCompra.value;
               descuento = document.lineaCompraForm.descuento.value;
               
               descuentoPrecio = precio * ((100-(100-descuento))/100);
               document.lineaCompraForm.descuentoP.value=descuentoPrecio;
            }
            
            function compra()
            {
                document.compraForm.total.value=(parseFloat(document.compraForm.total_base.value)+parseFloat(document.compraForm.total_iva.value)).toFixed(2);
            }
        </script>
          <style>
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
 
        this.input = $( "<input  id='comboText'>" )
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
        
        
        
        <?php include("../includes/header.php"); 

        
if (isset($_POST["eliminar"])==1)
{
    unset($_SESSION["lineas_compra"]);
    unset($_SESSION["lineas_compra_desc"]);
}        
        

if (isset($_POST["idProveedor"]) && !empty($_POST["idProveedor"]) )
{
    $sqlSel="SELECT * FROM proveedores WHERE id =".$_POST["idProveedor"];
    $rsSel = mysqli_query($bd,$sqlSel);
    if ($dataSel=  mysqli_fetch_array($rsSel))
    {
        $nombre=$dataSel["nombre"];
    }
    mysqli_free_result($rsSel);
}

if (isset($_POST["guardarCompra"]))    //Vol dir que entrem una compra
{
    
    
    
    
    $fechaA = explode("/",$_POST["fecha"]);
    $fecha_cambiada = mktime(00,00,00,$fechaA[1],$fechaA[0],$fechaA[2]);
    $fecha = date("Y-m-d", $fecha_cambiada);
    
    
    if (empty($_POST["fecha"]))
    {
        $sqlInsCompra="
        INSERT INTO compras 
        (idProveedor,referencia,precio_desc,coste,precio_coste,oferta,total_base,total_iva,total) 
        VALUE 
        ('".$_POST["idProveedor"]."','".$_POST["referencia"]."',".$_POST["totalDescuento"]."','".$_POST["costes"]."','".$_POST["totalCostes"]."','".addslashes($_POST["oferta"])."','".$_POST["total_base"]."','".$_POST["total_iva"]."','".$_POST["total"]."')";
    }
    else 
    {
        $sqlInsCompra="
        INSERT INTO compras 
        (idProveedor,referencia,precio_desc,fecha,coste,precio_coste,oferta,total_base,total_iva,total) 
        VALUE 
        ('".$_POST["idProveedor"]."','".$_POST["referencia"]."','".$_POST["totalDescuento"]."','".$fecha."','".$_POST["costes"]."','".$_POST["totalCostes"]."','".addslashes($_POST["oferta"])."','".$_POST["total_base"]."','".$_POST["total_iva"]."','".$_POST["total"]."')";    
    }
    
    //echo $sqlInsCompra."<br /><br />";
    
    mysqli_query($bd,$sqlInsCompra);
    $idCompra= mysqli_insert_id($bd);
    
    //Ahora entramos las lineas de descuento si las hay;
    if (isset($_SESSION["lineas_compra_desc"]))
    {
        for($ii=0;$ii<count($_SESSION["lineas_compra_desc"]);$ii++)
        {
            $sqlInsDesc="
                INSERT INTO linea_compra_desc (idCompra,idProducto,idProveedor,cantidad,precio_unidad) 
                VALUES 
                ('".$idCompra."','".$_SESSION["lineas_compra_desc"][$ii]["idProducto"]."','".$_POST["idProveedor"]."','".$_SESSION["lineas_compra_desc"][$ii]["cantidad"]."','".str_replace(",",".",$_SESSION["lineas_compra_desc"][$ii]["precio_medio"])."')";
            //echo $sqlInsDesc;
            mysqli_query($bd,$sqlInsDesc);
            
        }
    }
    
    
    //Ahora entramos las lineas de compra si las hay;
    if (isset($_SESSION["lineas_compra"]))
    {
        
        //Calculo del total de unidades de compra
        $totalUnidadesCompra=0;
        for($jj=0;$jj<count($_SESSION["lineas_compra"]);$jj++)
        {
            $totalUnidadesCompra+=$_SESSION["lineas_compra"][$jj]["cantidad"];
        }
        
        //Antes de introducir los productos, miramos si hay descuento. Si lo hay lo dividimos entro los productos de la compra
        $precioCompraMedio=0;
        if ($_POST["totalDescuento"]>0)
        {
            $precioCompraMedio=number_format(($_POST["totalDescuento"]-$_POST["totalCostes"])/$totalUnidadesCompra,2);
            
        }
        
        for($ii=0;$ii<count($_SESSION["lineas_compra"]);$ii++)
        {
            $precioCompraProd=  str_replace(",", ".",$_SESSION["lineas_compra"][$ii]["precioCompra"]);
            if ($precioCompraMedio>0)
            {
                $precioCompraProd = $_SESSION["lineas_compra"][$ii]["precioCompra"]-$precioCompraMedio;
            }
            elseif ($_SESSION["lineas_compra"][$ii]["descuento"]>0) 
            {
                $precioCompraProd = number_format($_SESSION["lineas_compra"][$ii]["precioCompra"] * (1-($_SESSION["lineas_compra"][$ii]["descuento"] / 100)),3);
            }
                
                
            
            $sqlInsCompra="
                INSERT INTO linea_compra (idCompra,idProducto,idProveedor,cantidad,precio_unidad,descuento,descuentoPrecio,precio_unidad_sin_desc,iva) 
                VALUES 
                ('".$idCompra."','".$_SESSION["lineas_compra"][$ii]["idProducto"]."','".$_POST["idProveedor"]."','".$_SESSION["lineas_compra"][$ii]["cantidad"]."','".($precioCompraProd)."','".$_SESSION["lineas_compra"][$ii]["descuento"]."','".$_SESSION["lineas_compra"][$ii]["descuentoP"]."','".$_SESSION["lineas_compra"][$ii]["precioCompra"]."','".$_SESSION["lineas_compra"][$ii]["iva"]."')";
            //echo $sqlInsCompra."<br />";
            mysqli_query($bd,$sqlInsCompra);

            //$sqlUpdStock="UPDATE stock_producto SET existencias = (existencias + ".$_SESSION["lineas_compra"][$ii]["cantidad"].") WHERE idProducto=".$_SESSION["lineas_compra"][$ii]["idProducto"]." AND idTienda=".$_SESSION["idUsuario"];
            //mysqli_query($bd,$sqlUpdStock);
            
            //Per cada compra introduim totes les linies de producte de forma unica.
            for ($iLCT=0;$iLCT<$_SESSION["lineas_compra"][$ii]["cantidad"];$iLCT++)
            {
                $sqlLCT="INSERT INTO stock_producto (idTienda,idCompra,idProducto,fecha_entrada,precio_compra)VALUES('".$_SESSION["idUsuario"]."','".$idCompra."','".$_SESSION["lineas_compra"][$ii]["idProducto"]."',NOW(),'".$_SESSION["lineas_compra"][$ii]["precioCompra"]."')";
                //echo "---".$sqlLCT."<br />"; 
                mysqli_query($bd,$sqlLCT);
            }
            
            
            //Calcul preu mig producte anual: Calculem el preu mig del producte de forma anual, multiplicant totes les existencies per el seu preu de compra y dividint per el el numero d'exitencies
            $sqlPrecioMedio = "SELECT SUM(cantidad*precio_unidad)/SUM(cantidad) as precio_medio FROM linea_compra where fecha > '01-01-".date('Y')." 00:00:00' and fecha < '31-12-".date('Y')." 23:59:59' and precio_unidad>0  and idProducto = ".$_SESSION["lineas_compra"][$ii]["idProducto"];
            //echo $sqlPrecioMedio;
            $rsPrecioMedio = mysqli_query($bd,$sqlPrecioMedio);
            if ($dataPrecioMedio = mysqli_fetch_array($rsPrecioMedio))
            {
                //Asignamos el precio medio de compra anual
                $sqlUpdProd="UPDATE productos SET precio_medio = ".$dataPrecioMedio["precio_medio"]." WHERE id = ".$_SESSION["lineas_compra"][$ii]["idProducto"];
                //mysqli_query($bd,$sqlUpdProd);
            }
        }
    }
    
    
    
    echo "Compra introducida";
    
}
unset($_SESSION["lineas_compra"]);
unset($_SESSION["lineas_compra_desc"]);
?>
        <h1>Nova Compra</h1>
        
        <div id="cabeceraCompra" style="background-color:#FDCCCC;"> 
            <form name="compraForm" method="post">
            <?PHP
                        $sqlProveedor="Select * from proveedores order by nombre";
                        $rsProveedor=mysqli_query($bd,$sqlProveedor);
                        ?>
                        Proveidor: <select name="idProveedor">
                        <?PHP
                        while($dataProveedor=mysqli_fetch_array($rsProveedor))
                        {
                            ?>
                            <option value="<?PHP echo $dataProveedor["id"]; ?>" <?PHP if ($dataProveedor["id"]==$_POST["idProveedor"]) { echo "selected='selected'"; } ?>><?PHP echo $dataProveedor["nombre"]; ?></option>
                            <?PHP
                        }
                        mysqli_free_result($rsProveedor);
                        ?>
                        </select><br />
                        Referencia Proveidor: <input type="text" value="" name="referencia"><br />
                        Fecha: <input type="text    " value="<?PHP echo date('d/m/Y'); ?>" name="fecha" id="datepicker"><br />
                        Oferta: <input type="text" value="" name="oferta" style="width: 600px;"><br />
                        <!--Total Compra: <input type="text" value="" name="total_base" style="width: 60px;" onkeyup="compra();">+<input type="text" value="" name="total_iva" style="width: 60px;" onkeyup="compra();">=<input type="text" value="" name="total" style="width: 60px;">-->
                        <!--Estado: <select name="idEstado">
                            <option value="0">No pagada</option>
                            <option value="1">Pagada</option>
                        </select-->
                        <!--<h2>Descuento</h2>
                        <div id="lineasCompraDescDiv">  <!--Div on cargarem les lines de compra amb ajax>
                                Total descuento euros: <input type="text" value="0" name="totalDescuento" class="precioC"><br />
                        </div>
                        Total descuento %: <input type="text" value="0" name="totalDescuentoPorCiento" class="precioC">
                        <h2>Costes</h2>
                        Altres costos (Punt verd...):<input type="text" value="" name="costes">
                        Cost:<input type="text" value="0" name="totalCostes" class="precioC">
                        <br />
                        <br /-->
                        <center>
                            <input type="submit" value="Guardar compra" name="guardarCompra">
                        </center>
                        <br />
                        <br />
            </form>
            <form name="eliminaCompraForm" method="POST">
                <input type="hidden" name="eliminar" value="1">
                <input type="hidden" name="idProveedor" value="<?PHP echo $_POST["idProveedor"]; ?>">
                <center><input type="submit" value="Borrar compra"></center>
            </form>
        
        <!--form name="lineaCompraDescForm" method="POST" id="lineaCompraDescForm">
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
        </form-->  
        </div>
        
        <br /><br />
        <h2>Lineas Compra</h2>
        <div id="lineasCompraDiv">  <!--Div on cargarem les lines de compra amb ajax-->
            
            
        </div>
            <br /><br />
        <form name="lineaCompraFormT" method="POST" id="lineasCompraFormT">
            <input type="hidden" name="idCliente" value="<?PHP echo $_POST["idCliente"]; ?>">
            <table width="100%;" style="border: 1px solid cornflowerblue;">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad Real/Almab</th>
                    <th>Precio Compra S/IVA Real/Almab</th>
                    <th>Descuento</th>
                    <th></th>
                </tr>
                <tr>
                    <td align="center">
                        <input type="text" name="codigo" class="codeC" id="codigo">
                    </td>
                    <td align="center"><input type="text" name="cantidad" id="cantidad" class="precioC" value=""></td>
                    <td align="center"><input type="text" name="precioCompra" id="precioCompra" class="precioC" value=""></td>
                    <td align="center"><input type="text" name="descuento" class="precioC" onkeyup="calculaDescuentoPrecio()" value="0.00"> %/<input type="text" name="descuentoP" class="inputNumeric" onkeyup="calculaDescuentoPorCiento()" value="0.00"></td>                    
                    <td align="center"><input type="button" name="altaLinea" value="Introduir Producte" onclick="document.lineaCompraFormT.codigo.focus();FAjax('alta_linea_t.php','lineasCompraDiv','lineasCompraFormT','POST')"></td>
                </tr>
            </table>
        </form>
        <br /><br />
        <form name="lineaCompraForm" method="POST" id="lineasCompraForm">
            <table width="100%;" style="border: 1px solid cornflowerblue;">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad Real/Almab</th>
                    <th>Precio Compra S/IVA Real/Almab</th>
                    <th>Descuento</th>
                    <th></th>
                </tr>
                <tr>
                    <td align="center">
                        <?PHP
                        $sqlProducto="Select * from productos order by nombre";
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
                            <option value="<?PHP echo $dataProducto["id"]; ?>"><?PHP echo $dataProducto["nombre"]; ?></option>
                            <?PHP
                            }
                        }
                        mysqli_free_result($rsProducto);
                        ?>
                        </select>
                        </div>    
                            <br />
                    </td>
                    <td align="center"><input type="text" name="cantidad" class="inputNumeric" value="0"></td>
                    <td align="center"><input type="text" name="precioCompra" class="precioC" value="0.00"></td>
                    <td align="center"><input type="text" name="descuento" class="precioC" onkeyup="calculaDescuentoPrecio()" value="0.00"> %/<input type="text" name="descuentoP" class="precioC" onkeyup="calculaDescuentoPorCiento()" value="0.00"></td>                    
                    <td align="center"><input type="button" name="altaLinea" value="Introduir Producte" onclick="document.getElementById('comboText').focus();document.getElementById('comboText').select();FAjax('alta_linea.php','lineasCompraDiv','lineasCompraForm','POST')"></td>
                </tr>
            </table>
        </form>    
            <br /><br /><br /><br /><br />
            
            
            <script language='javascript'>
            $(document).ready(function () {
                
                $('#codigo').focus();
                
                $('.precioC').keypress(function(event) {
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
                        $('#cantidad').select();
                        return true;
                    }
                    // prevent if already dot
                });
                
                $('#cantidad').keypress(function(event) {
                    if(event.which == 13 || event.which == 13){
                        $('#precioCompra').focus();
                        $('#precioCompra').select();
                        //FAjax('alta_linea.php','lineasCompraDiv','lineaTiquetForm','POST')
                        return true;
                    }
                    // prevent if already dot
                });
                
                $('#precioCompra').keypress(function(event) {
                    
                    
                    var pC = $('#precioCompra').val();
                    pC=pC.replace(',','.');
                    $('#precioCompra').val(pC);
                    if(event.which == 13 || event.which == 13){
                        //document.getElementById('precioCompra').focus();
                        FAjax('alta_linea_t.php','lineasCompraDiv','lineasCompraFormT','POST');
                        $('#codigo').focus();
                        $('#codigo').select();
                        return true;
                    }
                    // prevent if already dot
                });
            });
        </script>
    </body>
</html>
