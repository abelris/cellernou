<?PHP include("../conf.php");



$sqlUpd="UPDATE clientes SET profesional=0 WHERE id=82";
mysqli_query($bd,$sqlUpd);

$sqlUpd="SELECT * from clientes WHERE id=82";
$rsClients=mysqli_query($bd,$sqlUpd);
if ($dataClients=mysqli_fetch_array($rsClients))
    print_r($dataClients);




?>
