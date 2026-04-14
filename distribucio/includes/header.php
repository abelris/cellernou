<?PHP

if (!isset($_SESSION["nombreUsuario"]))
{
?>
<script language="javascript">

location='http://www.cellernou.com/distribucio/';

</script>    
<?PHP    
    die();
}   

if ($_SESSION["idUsuario"]!=4)
{
?>
<ul style="float:left;">
    <li><strong>Productes</strong></li>
    <li><a href="/distribucio/productes">Productes</a></li>
    <li><a href="/distribucio/productes/inventario.php">Inventario</a></li>
    <li><a href="/distribucio/productes/stock/alta.php">Movimiento Stock</a></li>
    <li><a href="/distribucio/productes/sin_precio_compra.php">Productes sense preu de compra</a></li>
    <li>-------------------</li>
    <li><a href="/distribucio/productes/merce-llull.php">Actualitza preus</a></li>
    <li><a href="/distribucio/productes/actualizaStats.php">Actualitza Stats</a></li>
</ul>
<ul style="float:left;">
    <li><strong>Professionals</strong></li>
    <li><a href="/distribucio/clients">Clients</a></li>
    <li><a href="/distribucio/vendes">Ventas</a></li>
    <li><a href="/distribucio/facturas">Facturas</a></li>
    <li><a href="/distribucio/facturas/listado_pagos.php">Historico Pagos</a></li>
    <li>--------------------</li>
    <li><a href="/distribucio/peticions">Peticions clients</a></li>
    <li><a href="/distribucio/clients/precios-venda-total.php">Precios profesionales</a></li>
    <li><a href="/distribucio/productes/productos_margen_bajo.php">Margen Bajo</a></li>
</ul>
<ul style="float:left;">
    <li><strong>Proveidors</strong></li>
    <li><a href="/distribucio/proveidors">Proveidors</a></li>
    <li><a href="/distribucio/compres">Compres</a></li>
    <li><a href="/distribucio/facturas_compra">Facturas Compra</a></li>
    <li>--------------------</li>    
    <li><a href="/distribucio/proveidors/productos-proveidors.php">Seleccionar Productos Especiales</a></li>
    <li><a href="/distribucio/proveidors/precios-especiales.php">Precios Productos Especiales</a></li>
</ul>
<ul style="float:left;">
    <li><strong>Tiquets</strong></li>
    <li><a href="/distribucio/tiquets/alta.php">Alta</a></li>
    <li><a href="/distribucio/tiquets/index.php">Listado</a></li>
    <li><a href="/distribucio/productes/productos_margen_bajo_t.php">Margen Bajo</a></li>

</ul>
<ul style="float:left;">
    <li><strong>General</strong></li>
    <li><a href="/distribucio/sectores">Sectors</a></li>
    <li><a href="/distribucio/tipos">Tipos</a></li>
    <li><a href="/distribucio/procedencias">Procedencia</a></li>
</ul>
<ul style="float:left;">
    <li><strong>Estadística i control</strong></li>
    <li><a href="/distribucio/analitica/facturacio">Facturacio</a></li>
    <li><a href="/distribucio/analitica/cobros">Cobros</a></li>
    <li><a href="/distribucio/analitica/granel">Granel</a></li>
    <li><a href="/distribucio/administracio/pagos">Pagos</a></li>
    <li><a href="/distribucio/productes/topVentas.php">Top Ventas</a></li>
    <li>------MARGES--------</li>
    <li><a href="/distribucio/analitica/marges/index.php">Marges</a></li>

</ul>
<ul style="float:left;">
    <li><a href="/distribucio/productes/merce-llull.php">Preus merce</a></li>
    <li><a href="/distribucio/">Salir</a></li>
</ul>
<div style="display: inline-block; width: 100%; height: 10px;">
    Usuario: <?PHP echo $_SESSION["nombreUsuario"];  ?>
</div>
<?PHP
}
else
{
?>
<ul style="float:left;">
    <li><strong>Productes</strong></li>
    <li><a href="/distribucio/productes/indexj.php">Productes</a></li>
</ul>        
<?PHP
}
?>