<?PHP include("../conf.php"); 

if (isset($_GET["idProducto"]))
    $_POST["idProducto"]=$_GET["idProducto"];


?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <script language="javascript">
        function calculaPreuVenta()
        {
            preuCompra=document.altaForm.precioCompra.value;
            preuVenta=document.altaForm.precioVenta.value;
            preuVentaCli=document.altaForm.precioVentaCli.value;
            iva = document.altaForm.iva.value;
            
            if (preuVenta>0)
            {
                margenVenta=(preuVenta-preuCompra)/preuVenta * 100;
                document.getElementById("margenVenta").innerHTML=margenVenta+"%";
            } 
            if (preuVentaCli>0)
            {
                if (iva==0)
                {
                    alert("Debe Seleccionar un iva para el proucto.");
                    document.getElementById("margenVentaCli").innerHTML=0+"%";
                }
                else
                {
                    preuCompra=preuCompra * (1+(iva/100));
                    margenVentaCli=(preuVentaCli-preuCompra)/preuVentaCli * 100;
                    document.getElementById("margenVentaCli").innerHTML=margenVentaCli+"%";
                }
            }            
            
            
        }
        </script>
    </head>
    <body>
        <?php 
        if ($_GET["noMenu"]!=1)
            include("../includes/header.php"); ?>
        

        <?PHP 

if (isset($_POST["nombre"]))
{    
    //Miramos si hay imagen
    $uploaddir = $_SERVER['DOCUMENT_ROOT'].'/distribucio/upload/';
    $uploadfile = $uploaddir.basename($_FILES['foto']['name']);
    //echo $uploadfile;   
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadfile)) {
        echo "Foto pujada.<br /><br />.";
            $sqlIns="update productos set idSector='".$_POST["idSector"]."',idTipo='".$_POST["idTipo"]."',idCrianza='".$_POST["idCrianza"]."',idProcedencia='".$_POST["idProcedencia"]."',nombre='".addslashes($_POST["nombre"])."',descripcion='".addslashes($_POST["descripcion"])."',foto='".basename($_FILES['foto']['name'])."',iva=".$_POST["iva"].",precio_venta_dis='".$_POST["precioVenta"]."',codigo='".$_POST["codigo"]."' WHERE id = ".$_POST["idProducto"];
    }
    else
    {
             $sqlIns="update productos set idSector='".$_POST["idSector"]."',idTipo='".$_POST["idTipo"]."',idCrianza='".$_POST["idCrianza"]."',idProcedencia='".$_POST["idProcedencia"]."',nombre='".addslashes($_POST["nombre"])."',descripcion='".addslashes($_POST["descripcion"])."',iva=".$_POST["iva"].",precio_venta_dis='".$_POST["precioVenta"]."',codigo='".$_POST["codigo"]."' WHERE id = ".$_POST["idProducto"];
    }
    
    if ($_POST["precioVentaCli"]>0)
    {
        $sqlUpdatePvp="UPDATE precios_producto_tienda SET pvp='".$_POST["precioVentaCli"]."' WHERE idProducto=".$_POST["idProducto"]." AND idTienda=".$_SESSION["idUsuario"];
        //echo $sqlUpdatePvp;
        mysqli_query($bd,$sqlUpdatePvp);
        
    }    
        
    
    //echo $sqlIns;
    mysqli_query($bd,$sqlIns);
    
    echo "Producte modificat.<br /><br />";
}


if(isset($_POST["idProducto"]))
    $sqlSel = "SELECT a.*,b.pvp FROM productos a inner join precios_producto_tienda b on a.id=b.idProducto WHERE b.idTienda=".$_SESSION["idUsuario"]." AND a.id = ".$_POST["idProducto"];
if(isset($_GET["idProducto"]))
    $sqlSel = "SELECT a.*,b.pvp FROM productos a inner join precios_producto_tienda b on a.id=b.idProducto WHERE b.idTienda=".$_SESSION["idUsuario"]." AND a.id = ".$_GET["idProducto"];

$rsSel = mysqli_query($bd,$sqlSel);
if ($dataP=  mysqli_fetch_array($rsSel))
{
    $idSector=$dataP["idSector"];
    $idTipo=$dataP["idTipo"];
    $idCrianza=$dataP["idCrianza"];
    $iva=$dataP["iva"];
    $idProcedencia=$dataP["idProcedencia"];
    $nombre=$dataP["nombre"];
    $descripcion=$dataP["descripcion"];
    $foto=$dataP["foto"];
    $codigo=$dataP["codigo"];
}

?>
        <h1>Modificar Producte</h1>
        <form name="altaForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" value="<?PHP echo $_POST["idProducto"]; ?>" name="idProducto">
            <?PHP
            $sqlSector="Select * from sectores order by nombre";
            $rsSector=mysqli_query($bd,$sqlSector);
            ?>
            Sector: <select name="idSector">
            <?PHP
            while($dataSector=mysqli_fetch_array($rsSector))
            {
                ?>
                <option value="<?PHP echo $dataSector["id"]; ?>" <?PHP if ($dataSector["id"]==$idSector) { echo "selected='Selected'"; } ?>><?PHP echo $dataSector["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsSector);
            ?>
            </select><br />
            <?PHP
            $sqlTipo="Select * from tipo_producto order by nombre";
            $rsTipo=mysqli_query($bd,$sqlTipo);
            ?>
            Tipus: <select name="idTipo">
            <?PHP
            while($dataTipo=mysqli_fetch_array($rsTipo))
            {
                ?>
                <option value="<?PHP echo $dataTipo["id"]; ?>" <?PHP if ($dataTipo["id"]==$idTipo) { echo "selected='Selected'"; } ?>><?PHP echo $dataTipo["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsTipo);
            ?>
            </select><br />
            <?PHP
            $sqlCrianza="Select * from crianza order by id";
            $rsCrianza=mysqli_query($bd,$sqlCrianza);
            ?>
            Crianza: <select name="idCrianza">
            <?PHP
            while($dataCrianza=mysqli_fetch_array($rsCrianza))
            {
                ?>
                <option value="<?PHP echo $dataCrianza["id"]; ?>" <?PHP if ($dataCrianza["id"]==$idCrianza) { echo "selected='Selected'"; } ?>><?PHP echo $dataCrianza["nombre_es"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsCrianza);
            ?>
            </select><br />
            I.V.A.: <select name="iva">
                <option value="0"<?PHP if ($dataP["iva"]==0) { echo "selected='Selected'"; } ?>> </option>
                <option value="21"<?PHP if ($dataP["iva"]==21) { echo "selected='Selected'"; } ?>>21%</option>
                <option value="10"<?PHP if ($dataP["iva"]==10) { echo "selected='Selected'"; } ?>>10%</option>
                <option value="4"<?PHP if ($dataP["iva"]==4) { echo "selected='Selected'"; } ?>>4%</option>
            </select><br />
            <?PHP
            $sqlProcedencia="Select * from procedencia order by nombre";
            $rsProcedencia=mysqli_query($bd,$sqlProcedencia);
            ?>
            Procedencia: <select name="idProcedencia">
            <?PHP
            while($dataProcedencia=mysqli_fetch_array($rsProcedencia))
            {
                ?>
                <option value="<?PHP echo $dataProcedencia["id"]; ?>" <?PHP if ($dataProcedencia["id"]==$idProcedencia) { echo "selected='Selected'"; } ?>><?PHP echo $dataProcedencia["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsProcedencia);
            ?>
            </select><br />
            Nom: <input type="text" name="nombre" value="<?PHP echo $nombre; ?>"><br />
            Descripcio: <input type="text" name="descripcion" value="<?PHP echo $descripcion; ?>"><br />
            Foto<input type="file" name="foto"><?PHP echo $foto; ?><br />
            <b>PRECIOS</b><br>
            Preu de Compra Dis:<input type="text" style="width: 40px;" name="precioCompra" class="precioC" value="<?PHP echo $_GET["precioCompra"]; ?>"><br />
            Preu de Venta Dis:<input type="text" style="width: 40px;" name="precioVenta" class="precioC" onKeyUp="calculaPreuVenta()" value="<?PHP echo $dataP["precio_venta_dis"]; ?>"> <span id="margenVenta">0%</span><br />
            Preu de Venta Cliente:<input type="text" style="width: 40px;" name="precioVentaCli" class="precioC"  onKeyUp="calculaPreuVenta()" value="<?PHP echo $dataP["pvp"]; ?>"> <span id="margenVentaCli">0%</span><br />
            Codi Barres:<input type="text" style="width: 140px;" name="codigo" id="codigo" value="<?PHP echo $dataP["codigo"]; ?>"><br />
            <input type="submit" value="Modificar"><br />
        </form>    
        
        <script language="javascript">
            calculaPreuVenta();
        </script>
    </body>
</html>
