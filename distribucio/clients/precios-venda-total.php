<?PHP include("../conf.php"); ?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<?PHP 

if (isset($_POST["accion"]))
{
    if ($_POST["accion"]==1)
    {
        for($i=0;$i<count($_POST["productos"]);$i++)
        {
               
            $sqlUpdProdClient="UPDATE precios_cliente SET precio=".$_POST["precios"][$i]." WHERE idProducto=".$_POST["productos"][$i]." AND idCliente = ".$_POST["idClientes"][$i];
            //echo $sqlUpdProdClient."</br>";
            mysqli_query($bd,$sqlUpdProdClient);
        }   
    }
    echo "Productos introducidos";
}
$cerca=" WHERE 1 ";
if (isset($_POST["idCliente"]))
{
    $cerca=" AND f.id=".$_POST["idCliente"];
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
                        <link rel="stylesheet" type="text/css" href="/distribucio/css/estils.css" />

    </head>
    <body>
        <?php include("../includes/header.php"); ?>
        <h1>Preus venta client: <?PHP echo $_POST["nombreCliente"]; ?></h1>
        <?php
        
            $idCliente=0;
            //$sqlSel = "SELECT a.*,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia,e.precio as precio FROM productos a inner join sectores b on a.idSector=b.id inner join tipo_producto c on a.idTipo=c.id inner join procedencia d on a.idProcedencia=d.id inner join precios_cliente e on a.id=e.idProducto and e.idCliente = ".$_POST["idCliente"]." order by b.nombre,c.nombre,a.nombre";
            $sqlSel = "SELECT a.*,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia,e.precio as precio,f.id as idCliente, f.nombre as nombreCliente FROM productos a inner join sectores b on a.idSector=b.id inner join tipo_producto c on a.idTipo=c.id inner join procedencia d on a.idProcedencia=d.id inner join precios_cliente e on a.id=e.idProducto inner join clientes f on f.id=e.idCliente ".$cerca." order by e.idCliente,b.nombre,c.nombre,a.nombre";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="buscadorCliente" method="post">
            <?php
            $sqlCliente="Select * from clientes WHERE idUsuario=".$_SESSION["idUsuario"]." order by nombre";
                        $rsCliente=mysqli_query($bd,$sqlCliente);
                        ?>
                        Cliente: <select name="idCliente" onchange="document.buscadorCliente.submit()">
                            <option value="0">-Seleccionar Cliente-</option>
                        <?PHP
                        while($dataCliente=mysqli_fetch_array($rsCliente))
                        {
                            ?>
                            <option value="<?PHP echo $dataCliente["id"]; ?>" <?PHP if ($dataCliente["id"]==$_POST["idCliente"]) { echo "selected='selected'"; } ?>><?PHP echo $dataCliente["nombre"]; ?></option>
                            <?PHP
                        }
                        mysqli_free_result($rsCliente);
                        ?>
                        </select><br />
            </form>
            <form name="vendaForm" method="POST">
                <input type="hidden" value="1" name="accion"> 
                <input type="hidden" name="idCliente" value="<?PHP echo $_POST["idCliente"]; ?>">

            <table border="1" width="100%">
                <tr>
                    <th>Sector</th>
                    <th>Categoria</th>
                    <th>Procedencia</th>    
                    <th>Nom</th>
                    <th>Descripcio</th>
                    <th>Precio s/IVA</th>
                    <th>Precio compra s/IVA</th>
                    <th>Margen</th>
                    <th>Foto</th>
                    
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            while ($data= mysqli_fetch_array($rsSel))
            {
                $clase="";
                $margenProducto=0;
                
                
                $precioCompraProducto = $data["precio_medio"]; 
                //Seleccionamos el último precio de compra del producto.
                $sqlPrecioCompra="SELECT * FROM linea_compra WHERE idProducto=".$data["id"]." ORDER BY fecha DESC limit 0,1";
                //echo $sqlPrecioCompra; 
                $rsPrecioCompra= mysqli_query($bd,$sqlPrecioCompra);
                if ($dataPrecioCompra=  mysqli_fetch_array($rsPrecioCompra))
                {
                    $precioCompraProducto=$dataPrecioCompra["precio_unidad"];
                }
                mysqli_free_result($rsPrecioCompra);

                if ($data["precio"]!=0)
                {
                    $margenProducto=number_format(($data["precio"]-$precioCompraProducto)/$data["precio"]*100,3);
                }
                if ($margenProducto<10)
                {
                    $minPrice=$precioCompraProducto/(1-0.1);

                    $clase="background-color:red";
                    //$sqlUpdatePrecios="UPDATE precios_especiales_proveidor SET precio = ".$minPrice." WHERE idProducto = ".$data["id"];
                    //echo $precioCompraProducto."<-->".$sqlUpdatePrecios."</br>";
                    //mysqli_query($bd,$sqlUpdatePrecios);
                }
                
                
                ?>
                <input type='hidden' name='ids[]' value='<?PHP echo $data["id"]; ?>'>
                <input type="hidden" value="<?PHP echo $data["idCliente"]; ?>" name="idClientes[]">       
                <?PHP
               if ($idCliente!=$data["idCliente"])
               {
                   ?>
                    <tr >
                        <td style="font-size:30px; font-weight: bold; text-align: center;" colspan="10"><?PHP echo $data["nombreCliente"]; ?></td>
                    </tr>
                   <?PHP
                   $idCliente=$data["idCliente"];
               }
                
                
                ?>
                <tr>
                    <td><?PHP echo $data["nombreSector"]; ?></td>
                    <td><?PHP echo $data["nombreTipo"]; ?></td>
                    <td><?PHP echo $data["nombreProcedencia"]; ?></td>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td><?PHP echo $data["descripcion"]; ?></td>
                    <input type='hidden' value='<?PHP echo $data["id"]; ?>' name='productos[]'>
                    <td align='right' style="<?PHP echo $clase; ?>"><input type='text' value='<?PHP echo number_format($data["precio"],3); ?>' class='precioC' name='precios[]' size='5'> &euro;</td>
                    <td><?PHP echo $precioCompraProducto." (".$minPrice.")" ?></td>
                    <td><?PHP 
                            echo $margenProducto;    
                                ?></td>
                    <td align="center"><a href="../upload/<?PHP echo $data["foto"]; ?>" target="_blank"><img src="../upload/<?PHP echo $data["foto"]; ?>" height="30"></a></td>
                    <td>Eliminar
                        <!--a href="javascript:document.vendaForm.idProducto.value=<?PHP echo $data["id"]; ?>; document.vendaForm.submit();">Nova venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;-->
                        <!--a href="javascript:document.eliminaForm.idProducto.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a-->
                    </td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           </table><br/><br/>
           <center><input type="submit" value="Guardar productos"></center>
           
           </form>
        
        <script language='javascript'>
            $(document).ready(function () {
                $('.precioC').keypress(function(event) {
                    if(event.which == 8 || event.which == 0){
                        return true;
                    }
                    if(event.which < 46 || event.which > 59) {
                        return false;
                        //event.preventDefault();
                    } // prevent if not number/dot

                    if(event.which == 46 && $(this).val().indexOf('.') != -1) {
                        return false;
                        //event.preventDefault();
                    } // prevent if already dot
                });
            });
        </script>
    </body>
</html>
