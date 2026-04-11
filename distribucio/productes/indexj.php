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
        <h1>Productes</h1>
        <?php
            $sqlSel = "SELECT a.*,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia, e.pvp FROM productos a left join sectores b on a.idSector=b.id left join tipo_producto c on a.idTipo=c.id left join procedencia d on a.idProcedencia=d.id inner join precios_producto_tienda e on a.id=e.idProducto ".$cerca." order by b.nombre,c.nombre,a.nombre";
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
            <div>
                <form name='buscadorC' method='POST'>
                    <center>Codigo: <input type="text" name="codigo"></center>
                </form>
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
                Nombre: <input type="text" name="palabra" value="<?PHP echo $palabra; ?>">
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
                    <th>Precio Compra Medio</th>
                    <th>Ultimo precio compra</th>
                    
                    
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
                
                        $ultimaCompraTxt="";
                        $sqlCompra="SELECT a.precio_unidad,c.nombre,b.oferta FROM linea_compra a inner join compras b on a.idCompra=b.id inner join proveedores c on b.idProveedor=c.id where a.idProducto=".$data["id"]." order by b.fecha desc";
                        //echo $sqlCompra;
                        $rsCompra=mysqli_query($bd,$sqlCompra);
                        if ($dataCompra=mysqli_fetch_array($rsCompra))
                        {
                            $precioUltimaCompra=$dataCompra["precio_unidad"]; 
                            $ultimaCompraTxt = $precioUltimaCompra."<br>";
                            if (!empty($dataCompra["oferta"]))
                                $ultimaCompraTxt .= "<br>".$dataCompra["oferta"];
                        }
                        mysqli_free_result($rsCompra);
                    
                
                $liniaRoja="";
                
                if ($data["precio_venta_dis"]==0)
                    $margen = 0;
                else
                    $margen = number_format(($data["precio_venta_dis"] - $precioUltimaCompra) / $data["precio_venta_dis"] * 100,2);
                
                if ($margen<0)
                    $liniaRoja=' style="background-color:#ff6666"';
                if ($margen>=0 && $margen<8)
                    $liniaRoja=' style="background-color:orange"';
                
                ?>
                <input type="hidden" name="idProductos[]" value="<?PHP echo $data["id"]; ?>">
                <tr <?PHP echo $liniaRoja; ?>>
                    <td><?PHP echo $data["nombreSector"]; ?></td>
                    <td><?PHP echo $data["nombreTipo"]; ?></td>
                    <td><?PHP echo $data["nombreProcedencia"]; ?></td>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td><?PHP echo $data["descripcion"]; ?></td>
                    <td><?PHP echo $data["precio_medio"]; ?></td>
                    
                    
                    <td>
                    <?PHP
                        /*
                        $sqlCompra="SELECT a.precio_unidad,c.nombre,b.oferta FROM linea_compra a inner join compras b on a.idCompra=b.id inner join proveedores c on b.idProveedor=c.id where a.idProducto=".$data["id"]." order by a.id desc";
                        //echo $sqlCompra;
                        $rsCompra=mysqli_query($bd,$sqlCompra);
                        if ($dataCompra=mysqli_fetch_array($rsCompra))
                        {
                            echo $dataCompra["precio_unidad"]."<br>".$dataCompra["nombre"];
                            if (!empty($dataCompra["oferta"]))
                                echo "<br>".$dataCompra["oferta"];
                        }
                        mysqli_free_result($rsCompra);
                         
                         */
                    echo $ultimaCompraTxt;
                    ?>    
                    </td>
                    
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
        <script language="javascript">
            document.buscadorC.codigo.focus();
        </script>
    </body>
</html>
