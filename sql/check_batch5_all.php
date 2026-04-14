<?php
require dirname(__DIR__) . '/distribucio/connexio/connexio.php';
$ids = [220,302,361,444,446,456,531,537,576,577,612,628,680,748,932,992,1050,1183,223,589,630,676,720,743,856,457,22,109,192,197,211,232,309,360,401,447,582,620,633,637,881,986,988,990,1177,809,808,904,1095,1174];
$idList = implode(',', $ids);
$r = mysqli_query($bd, "SELECT pf.idProducto, pf.foto FROM producto_fotos pf WHERE pf.idProducto IN ($idList) ORDER BY pf.idProducto");
while ($row = mysqli_fetch_assoc($r)) {
    echo $row['idProducto'] . ' => ' . $row['foto'] . "\n";
}
