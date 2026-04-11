<?PHP include("../conf.php"); 

if (isset($_POST["guardarPrecios"]))
{
    for ($iP=0; $iP<count($_POST["idProductos"]);$iP++)
    {
        $sqlUpdProd="UPDATE productos SET precio_venta_dis = ".$_POST["precioVentaDis"][$iP]." WHERE id =".$_POST["idProductos"][$iP]." ";
        //echo $sqlUpdProd;
        mysqli_query($bd,$sqlUpdProd);
        
        $sqlUpdProdTienda = "UPDATE precios_producto_tienda SET pvp = ".$_POST["pvp"][$iP]." WHERE idProducto =".$_POST["idProductos"][$iP]." AND idTienda = ".$_SESSION["idUsuario"];
        //echo $sqlUpdProdTienda;
        mysqli_query($bd,$sqlUpdProdTienda);
    }
    
}
    
//Muntem la cadena de Cerca
$cerca = " WHERE 1 ";



    //echo "---".$_POST["idProducto"]."---";
    if (!empty($_POST["idProducto"]))
    {
        $cerca.=" AND a.id = ".$_POST["idProducto"];
    }
    else
        $cerca.=" AND a.nombre like '%xxxxxx%'";
    
    
    
    if ($_POST["idSector"]!=0)
        $cerca.=" AND a.idSector = ".$_POST["idSector"];
    if ($_POST["idTipo"]!=0)
        $cerca.=" AND a.idTipo = ".$_POST["idTipo"];
    if ($_POST["idProcedencia"]!=0)
        $cerca.=" AND a.idProcedencia = ".$_POST["idProcedencia"];
 



//Cerquem en la tenda que toca.
$cerca .= " AND e.idTienda = 1";


?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
                
                
                <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
                <script src="//code.jquery.com/jquery-1.10.2.js"></script>
                <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

        <title></title>
        <link rel="stylesheet" type="text/css" href="/distribucio/css/estils.css" />
        
        <style>
  .custom-combobox {
    position: relative;
    display: inline-block;
    width: 100%;
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
  .ui-widget {
	font-family: Trebuchet MS,Tahoma,Verdana,Arial,sans-serif;
	font-size: 1.8em;
        width: 80%;
}
.ui-widget .ui-widget {
	font-size: 1.3em;
}

.ui-corner-right
{
    width: 80px;
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
        <h1>Productes</h1>
        <?php
            $sqlSel = "SELECT a.*,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia, e.pvp FROM productos a left join sectores b on a.idSector=b.id left join tipo_producto c on a.idTipo=c.id left join procedencia d on a.idProcedencia=d.id inner join precios_producto_tienda e on a.id=e.idProducto ".$cerca." order by b.nombre,c.nombre,a.nombre";
            //echo $sqlSel;
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            
                <form name='buscador' method='POST'>
            
                 <table width="100%"> 
                 <tr>
                     <td>
                <?PHP
                        $sqlProducto="Select * from productos a inner join precios_producto_tienda e on a.id=e.idProducto WHERE e.idTienda = 1 order by nombre";
                        $rsProducto=mysqli_query($bd,$sqlProducto);
                        ?>
                        <div class="ui-widget">
                        <select name="idProducto" id="combobox">
                            <option value="0" selected="selected" ></option>    
                        <?PHP
                        while($dataProducto=mysqli_fetch_array($rsProducto))
                        {
                            ?>
                            <option value="<?PHP echo $dataProducto["id"]; ?>"><?PHP echo $dataProducto["nombre"]."  (".$dataProducto["pvp"].")"; ?></option>
                            <?PHP
                        }
                        mysqli_free_result($rsProducto);
                        ?>
                        </select>
                        </div>    
                     </td>    
                 
                     <td>
                        <!--input type="text" name="palabra" value="<?PHP echo $palabra; ?>" class="ui-widget"-->
                     </td>
                     <td>
                        <input type="submit" name="bucar" value="buscar">
                     </td>
                 </tr>
                </table>
                </form>   
            </div>
            <?PHP 
            $htmlText="<br><br><br><br><h2>Listado precios xxxxxxxxx</h2>";
            ?>
               
                
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
        <script language="javascript">
            document.buscadorC.codigo.focus();
        </script>
    </body>
</html>
