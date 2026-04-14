<?PHP include("../conf.php"); ?>
<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
                <link rel="stylesheet" type="text/css" href="/distribucio/css/estils.css" />

    </head>
    <body>
        <?php

 include("../includes/header.php"); ?>
        <h1>Clients</h1>
        <?php
            $sqlSel = "SELECT * FROM clientes WHERE idUsuario=".$_SESSION["idUsuario"]." order by nombre";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST" action="../vendes/alta.php">
                <input type="hidden" name="idCliente">
            </form>
            <form name="productosCliente" method="POST" action="productos-cliente.php">
                <input type="hidden" name="idCliente">
                <input type="hidden" name="nombreCliente">
            </form>
            <form name="preciosVendaForm" method="POST" action="precios-venda.php">
                <input type="hidden" name="idCliente">
                <input type="hidden" name="nombreCliente">
            </form>
            <form name="eliminaForm" method="POST" action="borrar.php">
                <input type="hidden" name="idCliente">
            </form>
            <a href="alta.php">Alta Client</a><br/><br/>
            <table border="1" width="100%">
                <tr>
                    <th>Nom</th>
                    <th>Teléfon</th>
                    <th>Email</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            while ($data= mysqli_fetch_array($rsSel))
            {
                ?>
                <tr>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td><?PHP echo $data["telefono"]; ?></td>
                    <td><a href="mailto:<?PHP echo $data["email"]; ?>"><?PHP echo $data["email"]; ?></a></td>
                    <td>
                        <a href="javascript:document.vendaForm.idCliente.value=<?PHP echo $data["id"]; ?>; document.vendaForm.submit();">Nova venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.productosCliente.idCliente.value=<?PHP echo $data["id"]; ?>;document.productosCliente.nombreCliente.value='<?PHP echo str_replace("'","-",$data["nombre"]); ?>'; document.productosCliente.submit();">Productos Clietne</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.preciosVendaForm.idCliente.value=<?PHP echo $data["id"]; ?>;document.preciosVendaForm.nombreCliente.value='<?PHP echo str_replace("'","-",$data["nombre"]); ?>'; document.preciosVendaForm.submit();">Precios Venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.eliminaForm.idCliente.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a>
                        
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <!--a href="javascript:document.eliminaForm.idCliente.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();"-->Modificar<!--/a-->
                        
                    </td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           </table><br/><br/>
            <a href="alta.php">Alta Client</a>
    </body>
</html>
