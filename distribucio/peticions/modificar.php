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
    
    
    $sqlIns="UPDATE peticiones SET nombre='".addslashes($_POST["nombre"])."',telefono='".$_POST["telefono"]."',descripcion='".addslashes($_POST["descripcion"])."',observaciones='".addslashes($_POST["observaciones"])."',fecha_entrega='".addslashes($_POST["fecha_entrega"])."',estado=".$_POST["idEstado"]." WHERE id=".$_POST["idPeticion"]." ";
    mysqli_query($bd,$sqlIns);
    
    echo "Peticio modificada.<br /><br />";
}


$sqlSel = "SELECT * FROM peticiones WHERE id = ".$_POST["idPeticion"];
$rsSel = mysqli_query($bd,$sqlSel);
if ($dataP=  mysqli_fetch_array($rsSel))
{
    $nombre=$dataP["nombre"];
    $telefono=$dataP["telefono"];
    $descripcion=$dataP["descripcion"];
    $observaciones = $dataP["observaciones"];
    $fecha=$dataP["fecha"];
    $fecha_entrega=$dataP["fecha_entrega"];
    $idEstado=$dataP["estado"];
}

?>
        <h1>Nova petició</h1>
        <form name="altaForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" value="<?PHP echo $_POST["idPeticion"]; ?>" name="idPeticion">

            
            Nom del client: <input type="text" name="nombre" value="<?PHP echo $nombre; ?>"><br />
            Telefon del client: <input type="text" name="telefono" value="<?PHP echo $telefono; ?>"><br />
            Petició: <textarea name="descripcion" cols='30' rows='8'><?PHP echo $descripcion; ?></textarea><br />
            Obsevacions:<textarea name="observaciones"  cols='30' rows='8'><?PHP echo $observaciones; ?></textarea><br />
            Data d'entrega (dd/mm/aaaa): <input type="text" name="fecha_entrega" value="<?PHP echo $fecha_entrega; ?>"><br />
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
            <input type="submit" value="Modificar"><br />
        </form>    
    </body>
</html>
