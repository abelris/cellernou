<?PHP 

include("../conf.php"); 

//Hacemos que los precios del carrer de la merce, sean los mismos que los del carrer ramon llull.

$sqlProdLlull = "select * from precios_producto_tienda WHERE idTienda=1";   //Productos con precio Ramon Llull
$rsProdLlull = mysqli_query($bd,$sqlProdLlull);
while ($dataProdLlull= mysqli_fetch_array($rsProdLlull))
{
    //Ponemos el precio de Ramon Llull, al carrer de la merce.
    $sqlUpdProdMerce="UPDATE precios_producto_tienda SET pvp='".$dataProdLlull["pvp"]."' WHERE idProducto =".$dataProdLlull["idProducto"]." AND idTienda=3";
    echo $sqlUpdProdMerce."<br>";
    mysqli_query($bd,$sqlUpdProdMerce); 
}



?>