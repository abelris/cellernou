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
               
            $sqlUpdProdClient="UPDATE precios_cliente SET precio=".$_POST["precios"][$i]." WHERE idProducto=".$_POST["productos"][$i]." AND idCliente = ".$_POST["idCliente"];
            //echo $sqlUpdProdClient;
            mysqli_query($bd,$sqlUpdProdClient);
        }   
    }
    echo "Productos introducidos";
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
            $sqlSel = "SELECT a.*,b.nombre as nombreSector,c.nombre as nombreTipo, d.nombre as nombreProcedencia,e.precio as precio FROM productos a inner join sectores b on a.idSector=b.id inner join tipo_producto c on a.idTipo=c.id inner join procedencia d on a.idProcedencia=d.id inner join precios_cliente e on a.id=e.idProducto and e.idCliente = ".$_POST["idCliente"]." order by b.nombre,c.nombre,a.nombre";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST">
            <input type="hidden" value="<?PHP echo $_POST["idCliente"]; ?>" name="idCliente">
            <input type="hidden" value="1" name="accion"> 
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
                ?>
                <input type='hidden' name='ids[]' value='<?PHP echo $data["id"]; ?>'
                <tr>
                    <td><?PHP echo $data["nombreSector"]; ?></td>
                    <td><?PHP echo $data["nombreTipo"]; ?></td>
                    <td><?PHP echo $data["nombreProcedencia"]; ?></td>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td><?PHP echo $data["descripcion"]; ?></td>
                    <input type='hidden' value='<?PHP echo $data["id"]; ?>' name='productos[]'>
                    <td align='right'><input type='text' value='<?PHP echo number_format($data["precio"],3); ?>' class='precioC' name='precios[]' size='5'> &euro;</td>
                    <td>
                        <?PHP
                        $ultimaCompraTxt="";
                        $sqlCompra="SELECT a.precio_unidad,c.nombre,b.oferta FROM linea_compra a inner join compras b on a.idCompra=b.id inner join proveedores c on b.idProveedor=c.id where a.idProducto=".$data["id"]." order by a.id desc";
                        //echo $sqlCompra;
                        $rsCompra=mysqli_query($bd,$sqlCompra);
                        if ($dataCompra=mysqli_fetch_array($rsCompra))
                        {
                            $precioUltimaCompra=$dataCompra["precio_unidad"]; 
                            $ultimaCompraTxt = $precioUltimaCompra."<br>".$dataCompra["nombre"];
                            if (!empty($dataCompra["oferta"]))
                                $ultimaCompraTxt .= "<br>".$dataCompra["oferta"];
                        }
                        mysqli_free_result($rsCompra);
                        echo $ultimaCompraTxt;
                    ?>
                    </td>
                    <td><?PHP 
                            if ($data["precio_medio"]!=0)
                            {
                                echo number_format(($data["precio"]-$precioUltimaCompra)/$data["precio"]*100,3); 
                            }    
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
