<?php
$bd = mysqli_connect('localhost', 'root', '', 'cellernodb');
if (!$bd) { echo "Connection failed\n"; exit(1); }
$sql = file_get_contents(__DIR__ . '/migration_ondigitalris.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $stmt) {
    if (empty($stmt) || strpos($stmt, '--') === 0) continue;
    if (mysqli_query($bd, $stmt)) {
        if (preg_match('/CREATE TABLE.*?`?(\w+)`?/', $stmt, $m)) echo "Created: " . $m[1] . "\n";
    } else {
        echo "Error: " . mysqli_error($bd) . "\n";
    }
}
echo "Done!\n";
