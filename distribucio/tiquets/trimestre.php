<?PHP 

include("../conf.php"); 


$fechaIni="2017-01-01 00:00:00";
$fechaFin="2017-03-31 23:59:59";


//Contamos primero el total de tiquets entrados.
$sqlProdT="SELECT sum(b.cantidad*b.precio_unidad) as totalTiquets from tiquets a inner join linea_tiquet b on a.id=b.idVenta where a.idTienda = 1 and 
    a.fecha>='".$fechaIni."' and a.fecha<='".$fechaFin."'";
//echo $sqlProd;
$rsProdT=mysqli_query($bd,$sqlProdT);
if($dataTotalT=mysql_fetch_array($rsProdT))
   echo $dataTotalT["totalTiquets"]."<br><br>";

//Tenemos que buscar tiquets de forma aleatoria.
$sqlProd="SELECT * from tiquets a inner join linea_tiquet b on a.id=b.idVenta where a.idTienda = 1 and 
    a.fecha>='".$fechaIni."' and a.fecha<='".$fechaFin."'";
//echo $sqlProd;
$rsProd=mysqli_query($bd,$sqlProd);

while ($dataProd=mysql_fetch_array($rsProd))
{
    echo $dataProd["fecha"]." --> ".$dataProd["cantidad"]." --> ".$dataProd["precio_unidad"]." --> TOTAL:".$dataProd["cantidad"]*$dataProd["precio_unidad"]."<br />";
}


?>

