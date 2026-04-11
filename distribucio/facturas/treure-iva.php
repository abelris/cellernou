<?PHP 

include("../conf.php"); 


$idFactura=0;

if (isset($_GET["idFactura"]))
    $idFactura=$_GET["idFactura"];
        
        $sqlProd="UPDATE facturas SET idCliente=246 where id = 11717";
        //echo $sqlProd;
        $rsProd=mysqli_query($bd,$sqlProd);
        //echo mysqli_errno();
 ?>