<?php 

include('converttopdf.php'); 

//$html= --> Aquí pondriamos por ejemplo la consulta 
$html=' 
<img src="http://pxd.me/dompdf/www/images/title.gif"/> 

<table> 
    <tr> 
        <th>Nombre</th> 
        <th>Tipo</th> 
        <th>Imagen</th> 
        <th>Comentario</th> 
        <th>Unidades</th> 
        <th>Precio unidad</th>     
    </tr>     
    <tr> 
        <td>pensandoo</td> 
        <td>icono</td> 
        <td><img src="http://static.forosdelweb.com/fdwtheme/images/smilies/scratchchin.gif"/></td> 
        <td>iconito pensativo</td> 
        <td>3</td> 
        <td>10</td> 
    </tr> 
    <tr> 
        <td>fiesta</td> 
        <td>icono 3</td> 
        <td><img src="http://static.forosdelweb.com/fdwtheme/images/smilies/porra.gif"/></td> 
        <td>iconito festejando</td> 
        <td>1</td> 
        <td>24</td> 
    </tr> 
    <tr> 
        <td>silbando</td> 
        <td>icono</td> 
        <td><img src="http://static.forosdelweb.com/fdwtheme/images/smilies/silbar.gif"/></td> 
        <td>bombilla silbando</td> 
        <td>19</td> 
        <td>50</td> 
    </tr> 
    <tr> 
        <td>no no no</td> 
        <td>icono 2</td> 
        <td><img src="http://static.forosdelweb.com/fdwtheme/images/smilies/negar.gif"/></td> 
        <td>negacion</td> 
        <td>5</td> 
        <td>1</td> 
    </tr> 
</table> 
' 

?> 

<?php 

if ( isset($_POST['PDF_1']) ) 
    doPDF('ejemplo',$html,false); 

if ( isset($_POST['PDF_2']) ) 
    doPDF('ejemplo',$html,true,'style.css'); 

if ( isset($_POST['PDF_3']) ) 
    doPDF('',$html,true,'style.css'); 
             
if ( isset($_POST['PDF_4']) ) 
    doPDF('ejemplo',$html,true,'style.css',false,'letter','landscape');  
     
if ( isset($_POST['PDF_5']) ) 
    doPDF('ejemplo',$html,true,'',true); //asignamos los tags <html><head>... pero no tiene css 

if ( isset($_POST['PDF_6']) ) 
    doPDF('',$html,true,'style.css',true); 
     
if ( isset($_POST['PDF_7']) ) 
    doPDF('pdfs/nuevo-ejemplo',$html,true,'style.css',true); //lo guardamos en la carpeta pdfs     
?> 

<!doctype html> 
<html> 

<head> 
    <link rel="stylesheet" href="style.css" type="text/css" /> 
</head> 

<table class="header"> 
    <tr> 
        <td><a href="http://www.forosdelweb.com/f18/" target="_blank"><h1>PHP</h1></a></td> 
        <td><a href="http://www.forosdelweb.com/" target="_blank"><h2>FOROSDELWEB</h2></a></td> 
    </tr> 
</table> 

<table class="menu"> 
    <tr> 
        <td>Ejemplos para: <strong>dompdf</strong></td> 
        <td><a href="http://code.google.com/p/dompdf/wiki/Usage" target="_blank">Documentaci&oacute;n</a></td> 
        <td><a href="http://code.google.com/p/dompdf/source/browse/trunk/dompdf/dompdf_config.custom.inc.php?r=399" target="_blank">Define()</a></td> 
        <td><a href="http://pxd.me/dompdf/www/examples.php#samples" target="_blank">Ejemplos de dompdf</a></td> 
    </tr> 
</table> 

<body> 

<?php echo $html ?> 

<form  action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST"> 
<table> 
  <tr> 
    <td>Mostrar PDF sin CSS</td> 
    <td><input name="PDF_1" type="submit" value="CREAR" /></td> 
  </tr> 
  <tr> 
    <td>Mostrar PDF con CSS</td> 
    <td><input name="PDF_2" type="submit" value="CREAR" /></td> 
  </tr> 
  <tr> 
    <td>Mostrar PDF con CSS sin definir el nombre</td> 
    <td><input name="PDF_3" type="submit" value="CREAR" /></td> 
  </tr> 
  <tr> 
    <td>Mostrar PDF con CSS y cambiando el formato de la hoja</td> 
    <td><input name="PDF_4" type="submit" value="CREAR" /></td> 
  </tr> 
  <tr> 
    <td>Guardar y abrir PDF sin CSS</td> 
    <td><input name="PDF_5" type="submit" value="CREAR" /></td> 
  </tr> 
  <tr> 
    <td>Guardar y abrir PDF con CSS sin definir el nombre</td> 
    <td><input name="PDF_6" type="submit" value="CREAR" /></td> 
  </tr> 
  <tr> 
    <td>Guardar en otro directorio y abrir PDF con CSS</td> 
    <td><input name="PDF_7" type="submit" value="CREAR" /></td> 
  </tr>   
   
</table> 

</form> 

</body> 
</html>