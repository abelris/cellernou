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
        <?php

 include("../includes/header.php"); ?>
        <h1>Clients</h1>
        <?php
            $sqlSel = "SELECT * FROM procedencia order by nombre";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST" action="venda.php">
                <input type="hidden" name="idProcedencia">
            </form>
            <form name="eliminaForm" method="POST" action="borrar.php">
                <input type="hidden" name="idProcedencia">
            </form>
            <a href="alta.php">Alta Procedencia</a><br/><br/>
            <table border="1" width="100%">
                <tr>
                    <th>Nom</th>
                    <th>Descripció</th>
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            while ($data= mysqli_fetch_array($rsSel))
            {
                ?>
                <tr>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td><?PHP echo $data["descripcion"]; ?></td>
                    <td>
                        <!--a href="javascript:document.vendaForm.idProcedencia.value=<?PHP echo $data["id"]; ?>; document.vendaForm.submit();">Nova venda</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;-->
                        <a href="javascript:document.eliminaForm.idProcedencia.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a>
                    </td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           </table><br/><br/>
            <a href="alta.php">Alta Procedencia</a>
    </body>
</html>
