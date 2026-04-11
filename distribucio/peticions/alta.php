<?PHP include("../conf.php"); ?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php include("../includes/header.php"); ?>
        <?PHP 
if (isset($_POST["nombre"]))
{    
    
    
    $sqlIns="INSERT INTO peticiones (nombre,telefono,descripcion,observaciones,fecha_entrega,estado) 
        VALUES
        ('".addslashes($_POST["nombre"])."','".$_POST["telefono"]."','".addslashes($_POST["descripcion"])."','".addslashes($_POST["observaciones"])."','".addslashes($_POST["fecha_entrega"])."',".$_POST["idEstado"].")";
    //echo $sqlIns;
    mysqli_query($bd,$sqlIns);
    
    echo "Peticio donat d'alta.<br /><br />";
}

?>
        <h1>Nova petició</h1>
        <form name="altaForm" method="POST" enctype="multipart/form-data">
            
            
            Nom del client: <input type="text" name="nombre"><br />
            Telefon del client: <input type="text" name="telefono"><br />
            Petició: <textarea name="descripcion" cols='30' rows='8'></textarea><br />
            Obsevacions:<textarea name="observaciones"  cols='30' rows='8'></textarea><br />
            Data d'entrega (dd/mm/aaaa): <input type="text" name="fecha_entrega"><br />
            <?PHP
            $sqlEstado="Select * from estados_peticiones order by id";
            $rsEstado=mysqli_query($bd,$sqlEstado);
            ?>
            Estat: <select name="idEstado">
            <?PHP
            while($dataEstado=mysqli_fetch_array($rsEstado))
            {
                ?>
                <option value="<?PHP echo $dataEstado["id"]; ?>" <?PHP if ($dataEstado["id"]==$_POST["idEstado"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataEstado["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsEstado);
            ?>
            </select><br />
            <input type="submit" value="Alta"><br />
        </form>    
    </body>
</html>
