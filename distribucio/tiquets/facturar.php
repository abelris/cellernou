<?PHP 
include("../conf.php"); 

//Mostrem la finestra previa al pass a facturar.
//Posem la data d'emisió de la factura i la data de venciment.


?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <link rel="stylesheet" type="text/css" href="/distribucio/css/estils.css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
        
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
        <script>
        $(function() {
            $("#datepicker1").datepicker({dateFormat: "dd-mm-yy"});
            $("#datepicker2").datepicker({dateFormat: "dd-mm-yy"});
        });
        </script>
    </head>
    <body>
Conversió a factura de la Venta #<?PHP echo $_GET["idVenta"]; ?>    
<form name="formFactura" action="pasar_factura.php" method="post">
    <input type="hidden" name="idVenta" value="<?PHP echo $_GET["idVenta"]; ?>">
    Data emisió: <input type="text" id="datepicker1" name="fecha_emision" value="<?PHP echo date('d-m-Y'); ?>"><br />
    Data venciment: <input type="text" id="datepicker2" name="fecha_vencimiento" value="<?PHP echo date('d-m-Y'); ?>"><br />
    <?PHP
        $sqlForma="Select * from formas_pago order by id";
        $rsForma=mysqli_query($bd,$sqlForma);
        ?>
        Forma pago: <select name="idFormaPago">
        <?PHP
        while($dataForma=mysql_fetch_array($rsForma))
        {
            ?>
            <option value="<?PHP echo $dataForma["id"]; ?>" <?PHP if ($dataForma["id"]==$data["idFormaPago"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataForma["id"]."-".$dataForma["nombre"]; ?></option>
            <?PHP
        }
        mysql_free_result($rsForma);
        ?>
        </select><br /><br />
    <input type="submit" value="Pasar a factura">
           
</form>
<?PHP

?>
</body>
</html>