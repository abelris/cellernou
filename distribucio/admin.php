<?php 

include("conf.php");



$sqlLogin = "SELECT * FROM usuarios WHERE usuario='".$_POST["usuario"]."' AND password='".$_POST["password"]."'";
$rsLogin=mysqli_query($bd,$sqlLogin);
if ($dataLogin=  mysqli_fetch_array($rsLogin))  //Si hemo encontrado el usuario
{
    $_SESSION["nombreUsuario"]=$dataLogin["nombre"];
    $_SESSION["idUsuario"]=$dataLogin["id"];
}
else
{
    header("Location: index.php");
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        
        <?php include("includes/header.php"); ?>
        Programa de distribucio.
        
    </body>
</html>
