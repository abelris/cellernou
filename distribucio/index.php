<?php
header("Location: https://cellernou.com/distribucio/admin");
exit;

?> 
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
        <?php //include("includes/header.php"); ?>
        Programa de distribucio.
        <form name="myform" method="post" action="admin.php">
        usuari: <input type="text" name="usuario"><br />
        clau: <input type="password" name="password"><br />
        <input type="submit" value="Entrar">
        </form>
    </body>
</html>
