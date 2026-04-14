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
        <h1>Eliminar Petició</h1>
        <?PHP 

if (isset($_POST["idPeticion"]))
{
    $sqlIns="DELETE FROM peticiones WHERE id = ".$_POST["idPeticion"];
    mysqli_query($bd,$sqlIns);
    
    echo "Petició eliminada correctament.<br /><br />";
}

?>  
    </body>
</html>
