<?PHP include("../conf.php"); ?><!--
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
    echo $uploadfile;   
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadfile)) {
        echo "Foto pujada.<br /><br />.";
    }
    
    $sqlIns="INSERT INTO productos (idSector,idTipo,idProcedencia,nombre,descripcion,foto,iva,precio_medio,precio_venta_dis,codigo) VALUES ('".$_POST["idSector"]."','".$_POST["idTipo"]."','".$_POST["idProcedencia"]."','".addslashes($_POST["nombre"])."','".addslashes($_POST["descripcion"])."','".basename($_FILES['foto']['name'])."',".$_POST["iva"].",'".$_POST["precioCompra"]."','".$_POST["precioVenta"]."','".$_POST["codigo"]."')";
    //echo $sqlIns;
    mysqli_query($bd,$sqlIns);
    $idProducto= mysqli_insert_id($bd);
    
    
    //Entrem el producte en cada una de les tendes.
    $sqlTiendas = "SELECT * FROM usuarios";
    $rsTiendas=  mysqli_query($bd,$sqlTiendas);
    while($dataTiendas = mysqli_fetch_array($rsTiendas))
    {
        $insertStock="insert into precios_producto_tienda (idTienda,idProducto,pvp) VALUES ('".$dataTiendas["id"]."','".$idProducto."','".$_POST["precioVentaCli"]."')";
        mysqli_query($bd,$insertStock);
    }
    mysqli_free_result($rsTiendas);
    
    echo "Producte donat d'alta.<br /><br />";
}

?>
        <h1>Nou Producte</h1>
        <form name="altaForm" method="POST" enctype="multipart/form-data">
            <?PHP
            $sqlSector="Select * from sectores order by nombre";
            $rsSector=mysqli_query($bd,$sqlSector);
            ?>
            Sector: <select name="idSector">
            <?PHP
            while($dataSector=mysqli_fetch_array($rsSector))
            {
                ?>
                <option value="<?PHP echo $dataSector["id"]; ?>" <?PHP if ($dataSector["id"]==$_POST["idSector"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataSector["nombre"]; ?></option>
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
                <option value="<?PHP echo $dataTipo["id"]; ?>" <?PHP if ($dataTipo["id"]==$_POST["idTipo"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataTipo["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsTipo);
            ?>
            </select><br />
            I.V.A.: <select name="iva" onChange="calculaPreuVenta()">
                <option value="0"> </option>
                <option value="21" <?PHP if ($_POST["iva"]==21) { echo "selected='Selected'"; } ?>>21%</option>
                <option value="10" <?PHP if ($_POST["iva"]==10) { echo "selected='Selected'"; } ?>>10%</option>
                <option value="4" <?PHP if ($_POST["iva"]==4) { echo "selected='Selected'"; } ?>>4%</option>
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
                <option value="<?PHP echo $dataProcedencia["id"]; ?>" <?PHP if ($dataProcedencia["id"]==$_POST["idProcedencia"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataProcedencia["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsProcedencia);
            ?>
            </select><br />
            Nom: <input type="text" name="nombre"><br />
            Descripcio: <input type="text" name="descripcion"><br />
            Codigo: <input type="text" name="codigo" value="<?PHP echo $_GET["codigo"]?>"><br/>

            Foto<input type="file" name="foto"><br />
            <b>PRECIOS</b><br>
            Preu de Compra Dis:<input type="text" style="width: 40px;" name="precioCompra" class="precioC"><br />
            Preu de Venta Dis:<input type="text" style="width: 40px;" name="precioVenta" class="precioC" onKeyUp="calculaPreuVenta()"> <span id="margenVenta">0%</span><br />
            Preu de Venta Cliente:<input type="text" style="width: 40px;" name="precioVentaCli" class="precioC"  onKeyUp="calculaPreuVenta()"> <span id="margenVentaCli">0%</span><br />
            <input type="submit" value="Alta"><br />
        </form>    
    </body>
</html>
