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
    </head>
    <body>
        <?php include("../includes/header.php"); 
        

if (isset($_POST["nombre"]))
{
    $sqlIns="INSERT INTO clientes 
        (nombre,nombre_fiscal,telefono,email,nif,direccion,cp, poblacion,idUsuario,idFormaPago,idCondiciones,diaPago,profesional) 
        VALUES 
        ('".addslashes($_POST["nombre"])."','".addslashes($_POST["nombre_fiscal"])."','".$_POST["telefono"]."','".$_POST["email"]."','".$_POST["nif"]."','".$_POST["direccion"]."','".$_POST["cp"]."','".$_POST["poblacion"]."','".$_SESSION["idUsuario"]."','".$_POST["idFormaPago"]."','".$_POST["condiciones"]."','".$_POST["diaPago"]."','".$_POST["profesional"]."')";
    echo $sqlIns;
    mysqli_query($bd,$sqlIns);
    
    echo "Client donat d'alta.<br /><br />";
}

?>
        <h1>Nou Client</h1>
        <form name="altaForm" method="POST">
            Dades
            <hr>
            Profesional: <input type="checkbox" name="profesional" value="1"><br />
            Nom: <input type="text" name="nombre"><br />
            Nom Fiscal: <input type="text" name="nombre_fiscal"><br />
            Telefon: <input type="text" name="telefono"><br />
            Email:<input type="text" name="email"><br />
            NIF:<input type="text" name="nif"><br />
            Direcció:<input type="text" name="direccion"><br />
            CP:<input type="text" name="cp"><br />
            Població:<input type="text" name="poblacion"><br />
            
            Pagament
            <hr>
            <?PHP
            $sqlForma="Select * from formas_pago order by id";
            $rsForma=mysqli_query($bd,$sqlForma);
            ?>
            Forma pago: <select name="idFormaPago">
            <?PHP
            while($dataForma=mysqli_fetch_array($rsForma))
            {
                ?>
                <option value="<?PHP echo $dataForma["id"]; ?>" <?PHP if ($dataForma["id"]==$data["idFormaPago"]) { echo "selected='Selected'"; } ?>><?PHP echo $dataForma["id"]."-".$dataForma["nombre"]; ?></option>
                <?PHP
            }
            mysqli_free_result($rsForma);
            ?>
            </select><br /> 
            Condicions:
            <select name="condiciones">
                <option value="0">Pronto pago</option>
                <option value="0.15">15 Dias</option>
                <option value="1">1 Mes</option>
            </select><br />
            Dia de Pago:
            <select name="diaPago">
                <option value="0">Fecha Factura</option>
                <?PHP
                
                for ($i=1; $i<=30; $i++)
                {
                ?>
                <option value="<?PHP echo $i; ?>">Dia <?PHP echo $i; ?></option>
                <?PHP
                }
                ?>
            </select><br /><br />
            <input type="submit" value="Alta"><br /><br />
        </form>    
    </body>
</html>
