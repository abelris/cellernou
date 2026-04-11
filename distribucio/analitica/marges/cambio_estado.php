<?PHP 

include("../conf.php"); 

//Cargamos todas las lineas en una array y en una variable de session.

$sqlEstado="UPDATE facturas SET idEstado = ".$_POST["idEstado"]." WHERE id = ".$_POST["idFactura"];
mysqli_query($bd,$sqlEstado);

$sqlEstado="Select * from estados_facturas order by id";
$rsEstado=mysqli_query($bd,$sqlEstado);
?>
<select name="idEstado<?PHP echo $_POST["idFactura"]; ?>" onchange="FAjaxParam('cambio_estado.php','estadoFacturasDiv<?PHP echo $_POST["idFactura"]; ?>','idFactura=<?PHP echo $_POST["idFactura"]; ?>&idEstado='+this.value,'POST')">
<?PHP
while($dataEstado=mysqli_fetch_array($rsEstado))
{
    ?>
    <option value="<?PHP echo $dataEstado["id"]; ?>" <?PHP if ($dataEstado["id"]==$_POST["idEstado"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataEstado["id"]."-".$dataEstado["nombre"]; ?></option>
    <?PHP
}
mysqli_free_result($rsEstado);
?>
</select>