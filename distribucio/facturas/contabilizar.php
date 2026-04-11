<?PHP 

include("../conf.php"); 

//Cargamos todas las lineas en una array y en una variable de session.

if ($_POST["contabilizada"]==1)
    $contabilizada=1;
else {
    $contabilizada=0;
}

$sqlEstado="UPDATE facturas SET contabilizada = ".$contabilizada." WHERE id = ".$_POST["idFactura"];
mysqli_query($bd,$sqlEstado);

?>