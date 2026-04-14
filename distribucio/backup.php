<?PHP //require_once('../includes/seguretat.php'); ?>
<?PHP //require_once('../includes/functions.php');?>
<?PHP

$hostname = "localhost"; 
$username = "cellerno_db";
$password = "Lucho888";
$dbname = "cellerno_db";
$hostname = "localhost"; 

$path = $_SERVER['DOCUMENT_ROOT'];

//echo $path."--------";


ob_start();


$sConnString = mysql_connect($hostname, $username, $password) 
or die("Unable to connect to MySQL");

//create a connection object 
$connection = mysql_select_db("cellerno_db",$sConnString) 
or die("Could not select DB");

$command = "mysqldump --add-drop-table --host=localhost --user='".$username."' --password='".$password."' ".$dbname." "; 
system($command);

$dump = ob_get_contents(); 
ob_end_clean();

//The database dump now exists in the $dump variable... write it to a file using...
$fp = fopen($path."/distribucio/backup/".date('Ymd')."dump.sql", "w"); 
fputs($fp, $dump); 
fclose($fp);
chmod($path."/distribucio/backup/dump.sql",0777);

?>

La base de datos se ha copiado correctamente.