<?PHP 
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=abc.xls");  //File name extension was wrong
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

    $rootPath=$_SERVER["DOCUMENT_ROOT"];
    $_SESSION["idioma"]="es";
    include($rootPath."/bbdd/conexio.php"); 
    //print_r($_POST);
    $items=5000;
    $pagination=$_POST["pag"];
    if (empty($pagination))
        $pagination=0;
    $pagi=$pagination*$items;


    //Titulos y descripciones de cada página
    switch ($urlPrincipal)
    {
        case "vinos":
            $metaTitle="La mejor selección de vinos online";
            $metaDescription="Vino online. Descubre la mejor selección de vinos al mejor precio.";
            $h1="Vinos";
            $description="Listado de la mejor selección de vinos online";
            $sector="Vinos";
            $idSector="8";
            break;
    }


    //Muntem la cadena de Cerca
    $cerca = " WHERE 1 ";
    if (!empty($_POST["codigo"]))
    {
        $cerca.=" AND a.codigo = ".$_POST["codigo"];
    }
    else
    {
        if ($idSector!=0)
            $cerca.=" AND a.idSector = ".$idSector;
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
            $cerca.=" AND a.nombre like '%'";
    }
    //Cerquem en la tenda que toca.
    $cerca .= " AND e.idTienda = 1";





                $sqlSel = "SELECT a.*,cri.nombre_es as nombreCrianza,b.id as idSector, c.id as idTipo, b.nombre_".$_SESSION["idioma"]." as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia, e.pvp FROM productos a left join sectores b on a.idSector=b.id left join tipo_producto c on a.idTipo=c.id left join crianza cri on a.idCrianza=cri.id left join procedencia d on a.idProcedencia=d.id inner join precios_producto_tienda e on a.id=e.idProducto ".$cerca." and e.pvp>0 and a.activo!=0 order by activo desc,b.nombre,c.nombre,a.nombre limit $pagi,$items";
                $sqlSel = "SELECT a.id,a.nombre,b.pvp,c.nombre_cat FROM productos a inner join precios_producto_tienda b on a.id=b.idProducto inner join tipo_producto c on a.idTipo=c.id where a.activo=1 and a.nombre!='' and b.pvp>0 order by a.idTipo";
                $rsSel = mysqli_query($bd,$sqlSel);
                ?>
                    <table border="0" width="100%" cellspacing="0" style="border: solid 1px #555;">
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th>Publicado</th>    
                        <th><?php echo utf8_decode("Visibilidad en el catálogo"); ?></th>
                        <th><?php echo utf8_decode("Descripción corta"); ?></th>
                        <th><?php echo utf8_decode("Descripción"); ?></th>
                        <th>Peso (kg)</th>
                        <th>Longitud (cm)</th>
                        <th>Anchura (cm)</th>
                        <th>Altura (cm)</th>
                        <th>Precio normal</th>
                        <th><?php echo utf8_decode("Categorías"); ?></th>
                    </tr>
                <?PHP
                $contP=1;
                while ($data= mysqli_fetch_array($rsSel))
                {
                    
                    ?>
                    <input type="hidden" name="idProductos[]" value="<?PHP echo $data["id"]; ?>">
                    <tr class="lineaProd" <?PHP echo $liniaRoja; ?>>
                        <td style="border: solid 1px #555;"><?PHP echo $data["id"]; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo "simple"; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo $data["id"]; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo ucfirst(strtolower(utf8_decode($data["nombre"]))); ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo "1"; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo "visible"; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo ""; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo ""; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo "1,3"; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo "10"; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo "10"; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo "30"; ?></td>
                        <td style="border: solid 1px #555;"><?PHP echo $data["pvp"]; ?></td>
                        <td style="border: solid 1px #555;">
                            <?php
                            switch ($data["nombre_cat"])
                            {
                                case "Vi Negre":
                                    echo "Begudes alcohol > Vi negre, Begudes alcohol";
                                    break;
                                case "Vi Blanc":
                                    echo "Begudes alcohol > Vi blanc, Begudes alcohol";
                                    break;
                                case "Vi Rosat":
                                    echo "Begudes alcohol > Vi rosat, Begudes alcohol";
                                    break;
                                case "Ginebra":
                                    echo "Begudes alcohol > Licors / Destilats > Ginebra, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Whisky":
                                    echo "Begudes alcohol > Licors / Destilats > Whisky, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Orujo":
                                    echo "Begudes alcohol > Licors / Destilats > Orujo, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Vermut":
                                    echo "Begudes alcohol > Licors / Destilats > Vermut, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Vodka":
                                    echo "Begudes alcohol > Licors / Destilats > Vodka, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Bourbon":
                                    echo "Begudes alcohol > Licors / Destilats > Bourbon, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Ron":
                                    echo "Begudes alcohol > Licors / Destilats > Rom, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Licor de taronja (Pell Naranja)":
                                    echo "Begudes alcohol > Licors / Destilats > Licor de taronja, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Crema Whisky":
                                    echo "Begudes alcohol > Licors / Destilats > Cremas de licor, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Brandy":
                                    echo "Begudes alcohol > Licors / Destilats > Brandy, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Pacharan":
                                    echo "Begudes alcohol > Licors / Destilats > Pacharán, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Licor Poma":
                                    echo "Begudes alcohol > Licors / Destilats > Licor de poma, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Licor de Pressec":
                                    echo "Begudes alcohol > Licors / Destilats > Licor de pressec, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Crema Catalana":
                                    echo "Begudes alcohol > Licors / Destilats > Crema catalana, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Limoncello":
                                    echo "Begudes alcohol > Licors / Destilats > Limoncello, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Cava":
                                    echo "Begudes alcohol > Caves / Espumosos > Cava, Begudes alcohol, Begudes alcohol > Caves / Espumosos";
                                    break;
                                case "Cerveses":
                                    echo "Begudes alcohol > Cerveses, Begudes alcohol";
                                    break;
                                case "Ratafia":
                                    echo "Begudes alcohol > Licors / Destilats > Ratafia, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Aperitius":
                                    echo "Begudes alcohol > Licors / Destilats > Aperitius, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Cazalla":
                                    echo "Begudes alcohol > Licors / Destilats > Cazalla, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Tequila":
                                    echo "Begudes alcohol > Licors / Destilats > Tequila, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Moscatell":
                                    echo utf8_decode("Begudes alcohol > Vi dolç > Moscatell, Begudes alcohol, Begudes alcohol > Vi dolç");
                                    break;
                                case "Oporto":
                                    echo "Begudes alcohol > Vi Oporto, Begudes alcohol";
                                    break;
                                case "Vi ranci":
                                    echo "Begudes alcohol > Licors / Destilats > Vi ranci, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Conyac":
                                    echo "Begudes alcohol > Licors / Destilats > Cognac, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Ponche":
                                    echo "Begudes alcohol > Licors / Destilats > Ponche, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Manzanilla":
                                    echo "Begudes alcohol > Licors / Destilats > Manzanilla, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Jerez":
                                    echo "Begudes alcohol > Licors / Destilats > Jerez, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Pedro Ximenez":
                                    echo utf8_decode("Begudes alcohol > Vi dolç > Pedro Ximenez, Begudes alcohol, Begudes alcohol > Vi dolç");
                                    break;
                                case "Vino dolç o de Postre":
                                    echo utf8_decode("Begudes alcohol > Vi dolç, Begudes alcohol");
                                    break;
                                case "Malvasia":
                                    echo utf8_decode("Begudes alcohol > Vi dolç > Malvasia, Begudes alcohol, Begudes alcohol > Vi dolç");
                                    break;
                                case "Licor Herbes":
                                    echo "Begudes alcohol > Licors / Destilats > Licors herbes, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Licor":
                                    echo "Begudes alcohol > Licors / Destilats, Begudes alcohol";
                                    break;
                                case "Champagne":
                                    echo "Begudes alcohol > Caves / Espumosos > Champagne, Begudes alcohol, Begudes alcohol > Caves / Espumosos";
                                    break;
                                case "Lambruscos":
                                    echo "Begudes alcohol > Caves / Espumosos > Lambrusco, Begudes alcohol, Begudes alcohol > Caves / Espumosos";
                                    break;
                                case "Armagnac":
                                    echo "Begudes alcohol > Licors / Destilats > Armagnac, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Mezcal":
                                    echo "Begudes alcohol > Licors / Destilats > Mezcal, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Anis":
                                    echo "Begudes alcohol > Licors / Destilats > Anís, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Absenta":
                                    echo "Begudes alcohol > Licors / Destilats > Absenta, Begudes alcohol, Begudes alcohol > Licors / Destilats";
                                    break;
                                case "Mistela":
                                    echo utf8_decode("Begudes alcohol > Vi dolç > Mistela, Begudes alcohol, Begudes alcohol > Vi dolç");
                                    break;
                                default:
                                    echo "";
                                    break;
                            }   
                            ?>
                            <?PHP echo $data["nombre_cat"]; ?>
                        </td>
                    </tr>
                <?PHP
                }
                ?>
               </table>
                <!--Paginacion-->
                <!---->
 