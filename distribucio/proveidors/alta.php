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
    $sqlIns="INSERT INTO proveedores (nombre,telefono,email) VALUES ('".addslashes($_POST["nombre"])."','".$_POST["telefono"]."','".$_POST["email"]."')";
    mysqli_query($bd,$sqlIns);
    
    echo "Client donat d'alta.<br /><br />";
}

?>
        <h1>Nou Proveidor</h1>
        <form name="altaForm" method="POST">
            Nom: <input type="text" name="nombre"><br />
            Telefon: <input type="text" name="telefono"><br />
            Email: <input type="text" name="email"><br />
            <input type="submit" value="Alta"><br />
        </form>    
    </body>
</html>
