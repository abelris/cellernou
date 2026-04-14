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
    $sqlIns="INSERT INTO sectores (nombre,descripcion) VALUES ('".$_POST["nombre"]."','".$_POST["descripcion"]."')";
    mysqli_query($bd,$sqlIns);
    
    echo "Sector donat d'alta.<br /><br />";
}

?>
        <h1>Nou Sector</h1>
        <form name="altaForm" method="POST">
            Nom sector: <input type="text" name="nombre"><br />
            Descripcio: <input type="text" name="descripcion"><br />
            <input type="submit" value="Alta"><br />
        </form>    
    </body>
</html>
