    <?PHP 
    $rootPath=$_SERVER["DOCUMENT_ROOT"];
    $_SESSION["idioma"]="es";
    include($rootPath."/bbdd/conexio.php"); 
    //print_r($_POST);
    $items=50;
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
                $rsSel = mysqli_query($bd,$sqlSel);
                ?>
                <div>
                <form name='buscador' method='POST'>
                    <input type="hidden" id="idSector" name="idSector" value="<?PHP echo $idSector; ?>">
                    <table align='center'>       
                        <tr>
                            <td>
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
                $contP=1;
                while ($data= mysqli_fetch_array($rsSel))
                {
                    ?>
                    <input type="hidden" name="idProductos[]" value="<?PHP echo $data["id"]; ?>">
                    <tr class="lineaProd" <?PHP echo $liniaRoja; ?>>
                        <td><?PHP echo $contP; ?></td>
                        <td><?PHP echo $data["nombreSector"]; ?></td>
                        <td><?PHP echo $data["nombreTipo"]; ?></td>
                        <td><?PHP echo $data["nombreCrianza"]; ?></td>
                        <td><?PHP echo $data["nombreProcedencia"]; ?></td>
                        <td><?PHP echo $data["nombre"]; ?></td>
                        <td><?PHP echo $data["descripcion"]; ?></td>
                        <td align="right"><?PHP echo $data["pvp"]; ?> €</td>
                    </tr>
                <?PHP
                }
                ?>
               </table>
               </form>     
                <!--Paginacion-->
                <!---->
 