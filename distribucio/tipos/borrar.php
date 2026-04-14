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
        <h1>Nou Client</h1>
        <?PHP 

if (isset($_POST["idTipo"]))
{
    $sqlIns="DELETE FROM tipo_producto WHERE id = ".$_POST["idTipo"];
    mysqli_query($bd,$sqlIns);
    
    echo "Tipus Producte eliminat correctament.<br /><br />";
}

?>  
    </body>
</html>
