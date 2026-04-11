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

//Borrem les dades del any en curs per tornar-les a generar cada vegada que s'executi aquest PHP


$sqlProductsUpdate="DELETE FROM stats_productos WHERE (ano = '".date('Y')."' OR ano = '".(date('Y')-1)."' )";
$rsProductsUpdate=mysqli_query($bd,$sqlProductsUpdate);

//Una vez tenemos la última fecha de actuliazción, procedemos a actualizar desde la fecha encontrada, hasta la actual las ventas realizadas.
$sqlProductsUpdate="insert into stats_productos (idProducto,cantidad,mes,ano,idTienda) SELECT a.idProducto,SUM(a.cantidad),MONTH(a.fecha),YEAR(a.fecha),b.idTienda FROM linea_tiquet a inner join tiquets b on a.idVenta=b.id WHERE (YEAR(a.fecha) = '".date('Y')."' OR YEAR(a.fecha) = '".(date('Y')-1)."' ) GROUP by a.idProducto,MONTH(a.fecha),b.idTienda";
$rsProductsUpdate=mysqli_query($bd,$sqlProductsUpdate);

//echo $sqlProductsUpdate;
?>  
Tabla productos_stats actualizada        
        
    </body>
</html>
