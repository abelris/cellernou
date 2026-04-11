<?PHP 
$rootPath=$_SERVER["DOCUMENT_ROOT"];
include($rootPath."/bbdd/conexio.php"); 
//print_r($_POST);
if (isset($_POST["guardarPrecios"]))
{
    
    for ($iP=0; $iP<count($_POST["idProductos"]);$iP++)
    {
        /*/
        $sqlUpdProd="UPDATE productos SET precio_venta_dis = ".$_POST["precioVentaDis"][$iP]." WHERE id =".$_POST["idProductos"][$iP]." ";
        mysqli_query($bd,$sqlUpdProd);
        
        $sqlUpdProdTienda = "UPDATE precios_producto_tienda SET pvp = ".$_POST["pvp"][$iP]." WHERE idProducto =".$_POST["idProductos"][$iP]." AND idTienda = ".$_SESSION["idUsuario"];
        mysqli_query($bd,$sqlUpdProdTienda);
         * 
         */
        //Modificamos los sectores enviados
        //$sqlUpdProd="UPDATE productos SET idSector = ".$_POST["idSectorM"][$iP]." WHERE id =".$_POST["idProductos"][$iP]." ";
        $sqlUpdProd="UPDATE productos SET idCrianza='".$_POST["idCrianzaM"][$iP]."',idProcedencia='".$_POST["idProcedenciaM"][$iP]."',nombre='".addslashes($_POST["nombreProd"][$iP])."',activo = ".$_POST["activoM"][$iP].",idSector = ".$_POST["idSectorM"][$iP].",idTipo = ".$_POST["idTipoM"][$iP]." WHERE id =".$_POST["idProductos"][$iP]." ";
        //echo $sqlUpdProd;
        //$sqlUpdProd="UPDATE productos SET idTipo = ".$_POST["idTipoM"][$iP]." WHERE id =".$_POST["idProductos"][$iP]." ";
        mysqli_query($bd,$sqlUpdProd);
    }
    
}

$pagination=$_POST["pag"];
if (empty($pagination))
    $pagination=0;
$pagi=$pagination*50;

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
        $cerca.=" AND a.nombre like '%xxxxxx%'";
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
  <style>
      .lineaProd:hover
      {
          background-color: #ccc;
      }
  </style>  
    </head>
    <body>
        <?php //include("../includes/header.php"); ?>
        <h1>Productes</h1>
        <?php
            $sqlSel = "SELECT a.*,cri.nombre_es as nombreCrianza,b.id as idSector, c.id as idTipo, b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia, e.pvp FROM productos a left join sectores b on a.idSector=b.id left join tipo_producto c on a.idTipo=c.id left join crianza cri on a.idCrianza=cri.id left join procedencia d on a.idProcedencia=d.id inner join precios_producto_tienda e on a.id=e.idProducto ".$cerca." and e.pvp>0 and a.activo!=0 order by activo desc,b.nombre,c.nombre,a.nombre limit $pagi,50";
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
                <form name='buscador' method='POST'>
            <table align='center'>       
                <tr>
                    <td>
             <?PHP
            $sqlSector="Select * from sectores WHERE id in (1,2,8,10,12,13,14,17,21) order by nombre";
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
            <td>
                <select name="pag" onchange='document.buscador.submit();'>
                    <?PHP
                    for ($i=0;$i<=120;$i++)
                    {
                        ?>
                        <option value="<?PHP echo $i; ?>" <?PHP if($i==$pagination) { echo "selected"; } ?>><?PHP echo $i; ?></option>
                        <?PHP
                    }
                    ?>
                </select>
            </td>
            </tr>
            
            </table>
                </form>   
            </div>
            <?PHP 
            $htmlText="<br><br><br><br><h2>Listado precios xxxxxxxxx</h2>";
            ?>
            <form name="guardarProductos" method="POST" onSubmit="document.guardarProductos.idSector.value=document.buscador.idSector.value;document.guardarProductos.idTipo.value=document.buscador.idTipo.value;document.guardarProductos.idProcedencia.value=document.buscador.idProcedencia.value;document.guardarProductos.pag.value=document.buscador.pag.value;document.guardarProductos.palabra.value=document.buscador.palabra.value;">
                <input type="hidden" name="palabra">
                <input type="hidden" name="idSector">
                <input type="hidden" name="idTipo">
                <input type="hidden" name="idProcedencia">
                <input type="hidden" name="pag">
            <table border="1" width="100%">
                <tr>
                    <th>#</th>
                    <th>Sector</th>
                    <th>Categoria</th>
                    <th>Crianza</th>
                    <th>Procedencia</th>    
                    <th>Nom</th>
                    <th>Descripcio</th>
                    <th>PVP</th>
                    <th>Foto</th>
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
                    $margen = number_format(($data["precio_venta_dis"] - $precioUltimaCompra) / $data["precio_venta_dis"] * 100,2);
                
                //Reactivar
                /*
                if ($margen<0)
                    $liniaRoja=' style="background-color:#ff6666"';
                if ($margen>=0 && $margen<8)
                    $liniaRoja=' style="background-color:orange"';
                 * 
                 */
                if ($data["activo"]==0)
                    $liniaRoja=' style="background-color:#ff6666"';
                if ($data["activo"]==2)
                    $liniaRoja=' style="background-color:orange"';
                ?>
                <input type="hidden" name="idProductos[]" value="<?PHP echo $data["id"]; ?>">
                <tr class="lineaProd" <?PHP echo $liniaRoja; ?>>
                    <td><?PHP echo $contP; ?></td>
                    <td>
                        <?PHP
                        $sqlSector="Select * from sectores WHERE id in (1,2,8,10,12,13,14,17,21,22) order by nombre";
                        $rsSector=mysqli_query($bd,$sqlSector);
                        ?>
                        Sector: <select name="idSectorM[]">
                            <option value='0'>Todos</option>
                        <?PHP
                        while($dataSector=mysqli_fetch_array($rsSector))
                        {
                            ?>
                            <option value="<?PHP echo $dataSector["id"]; ?>" <?PHP if ($dataSector["id"]==$data["idSector"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataSector["nombre"]; ?></option>
                            <?PHP
                        }
                        ?></select><?PHP             
                        mysqli_free_result($rsSector); 

                        ?>
                        </td>
                        <td><?PHP 
                        $sqlTipo="Select * from tipo_producto where id not in (0) order by nombre";
                        $rsTipo=mysqli_query($bd,$sqlTipo);
                        ?>
                        Tipus: <select name="idTipoM[]">
                            <option value='0'>Todos</option>
                        <?PHP
                        while($dataTipo=mysqli_fetch_array($rsTipo))
                        {
                            ?>
                            <option value="<?PHP echo $dataTipo["id"]; ?>" <?PHP if ($dataTipo["id"]==$data["idTipo"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataTipo["nombre"]; ?></option>
                            <?PHP
                        }
                        ?></select><?PHP
                        mysqli_free_result($rsTipo);
                    
                        ?>
                        <?PHP //echo $data["nombreTipo"]; ?></td>
                        <td>
                        <?PHP
                        $sqlCrianza="Select * from crianza WHERE 1 order by nombre_es";
                        $rsCrianza=mysqli_query($bd,$sqlCrianza);
                        ?>
                        Sector: <select name="idCrianzaM[]">
                            <option value='0'>Todos</option>
                        <?PHP
                        while($dataCrianza=mysqli_fetch_array($rsCrianza))
                        {
                            ?>
                            <option value="<?PHP echo $dataCrianza["id"]; ?>" <?PHP if ($dataCrianza["id"]==$data["idCrianza"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataCrianza["nombre_es"]; ?></option>
                            <?PHP
                        }
                        ?></select><?PHP             
                        mysqli_free_result($rsCrianza); 

                        ?>    
                            
                            <?PHP //echo $data["nombreCrianza"]; ?></td>
                        <td>
                        <?PHP
                        $sqlProcedencia="Select * from procedencia WHERE 1 order by nombre";
                        $rsProcedencia=mysqli_query($bd,$sqlProcedencia);
                        ?>
                        Sector: <select name="idProcedenciaM[]">
                            <option value='0'>Todos</option>
                        <?PHP
                        while($dataProcedencia=mysqli_fetch_array($rsProcedencia))
                        {
                            ?>
                            <option value="<?PHP echo $dataProcedencia["id"]; ?>" <?PHP if ($dataProcedencia["id"]==$data["idProcedencia"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataProcedencia["nombre"]; ?></option>
                            <?PHP
                        }
                        ?></select><?PHP             
                        mysqli_free_result($rsProcedencia); 

                        ?><?PHP //echo $data["nombreProcedencia"]; ?></td>
                        <td><input type="text" name="nombreProd[]" value="<?PHP echo $data["nombre"]; ?>"></td>
                        <td><?PHP echo $data["descripcion"]; ?></td>
                        <td align="right"><?PHP echo $data["pvp"]; ?> €</td>
                        <td align="center">
                            <select name="activoM[]">
                                <option value="0" <?PHP if ($data["activo"]==0) { echo "selected"; } ?>>No activo</option>
                                <option value="1" <?PHP if ($data["activo"]==1) { echo "selected"; } ?>>Activo</option>
                                <option value="2" <?PHP if ($data["activo"]==2) { echo "selected"; } ?>>????</option>
                            </select>
                            <a href="../upload/<?PHP echo $data["foto"]; ?>" target="_blank"><img src="../upload/<?PHP echo $data["foto"]; ?>" height="30"></a></td>
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
        <script language="javascript">
            document.buscadorC.codigo.focus();
        </script>
    </body>
</html>
