<?PHP
//echo $sqlSel;
?>
<form name="guardarProductos" method="POST" onSubmit="document.guardarProductos.idSector.value=document.buscador.idSector.value;document.guardarProductos.idTipo.value=document.buscador.idTipo.value;document.guardarProductos.idProcedencia.value=document.buscador.idProcedencia.value;document.guardarProductos.pag.value=document.buscador.pag.value;document.guardarProductos.palabra.value=document.buscador.palabra.value;">
    <input type="hidden" name="palabra">
    <input type="hidden" name="idSector">
    <input type="hidden" name="idTipo">
    <input type="hidden" name="idProcedencia">
    <input type="hidden" name="pag">
<table border="1" width="100%">
    <?PHP
    if ($_SESSION["idioma"]=="cat")
    {
    ?>
    <tr>
        <th>#</th>
        <th>Nom</th>
        <?PHP 
        if ($idSector=="0") 
        {
        ?>
        <th>Sector</th>
        <?PHP
        }
        ?>
        <?PHP
        if (empty($idTipo))
        {
        ?>
        <th>Categoria</th>
        <?PHP
        }
        ?>
        <?PHP 
        if ($idSector=="8") 
        {
        ?>
        <th>Criança</th>
        <?PHP
        }
        ?>
        <th>Procedència</th>    
        <th>Descripció</th>
        <th>PVP</th>
        <!--th>Foto</th-->
    </tr>
    <?PHP
    }
    else
    {
    ?>
    <tr>
        <th>#</th>
        <th>Nombre</th>
        <?PHP 
        if ($idSector=="0") 
        {
        ?>
        <th>Sector</th>
        <?PHP
        }
        ?>
        <?PHP
        if (empty($idTipo))
        {
        ?>
        <th>Categoría</th>
        <?PHP
        }
        ?>
        <?PHP 
        if ($idSector=="8") 
        {
        ?>
        <th>Crianza</th>
        <?PHP
        }
        ?>
        <th>Procedencia</th>    
        <th>Descripción</th>
        <th>PVP</th>
        <!--th>Foto</th-->
    </tr>
    <?PHP    
    }
$contP=1;
while ($data= mysqli_fetch_array($rsSel))
{
    ?>
    <input type="hidden" name="idProductos[]" value="<?PHP echo $data["id"]; ?>">
    <tr class="lineaProd" <?PHP echo $liniaRoja; ?>>
        <td><?PHP echo $contP; ?></td>
        <td><?PHP echo $data["nombre"]; ?></td>
        <?PHP 
        if ($idSector=="0") 
        {
        ?>
        <td><?PHP echo $data["nombreSector"]; ?></td>
        <?PHP
        }
        ?>
        <?PHP
        if (empty($idTipo))
        {
        ?>
        <td><?PHP echo $data["nombreTipo"]; ?></td>
        <?PHP
        }
        ?>
        <?PHP 
        if ($idSector=="8") 
        {
        ?>
        <td><?PHP echo utf8_encode($data["nombreCrianza"]); ?></td>
        <?PHP
        }
        ?>
        <td><?PHP echo $data["nombreProcedencia"]; ?></td>
        
        <td><?PHP echo $data["descripcion"]; ?></td>
        <td align="right" style="text-align: right;">
            <?PHP 
                if ($idSector==10)
                {
                    echo number_format($data["pvp"],2); 
                }
                else 
                {
                    echo number_format($data["pvp"]*0.90,2); 
                }
                ?>€</td>
    </tr>
<?PHP
    $contP++;
}
?>
</table>
</form>   