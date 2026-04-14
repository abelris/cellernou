<?php



// Set the max lifetime
    ini_set("session.gc_maxlifetime", 36000);
    // Set the session cookie to timout
    ini_set("session.cookie_lifetime", 36000);
        
    session_start();
    
    if (!isset($_SESSION["idUsuario"]))
    {
        $_SESSION["nombreUsuario"]="Almab";
        $_SESSION["idUsuario"]=1;
    }

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//CONSTANTES
define("nombreEmpresa","ALMABRIS GROUP 2024 S.L.");
define("direccion","C/ Ramón Llull, nº 15");
define("cp","08330");
define("poblacion","Premià de Mar");
define("provincia","BARCELONA");
define("cif","B66318684");

$pathRoot = $_SERVER['DOCUMENT_ROOT'];
include($pathRoot."/distribucio/connexio/connexio.php");
?>
