<?php
/*
** download the file, id param is handed in via get
*/

require('config.inc.php');
require('DatabaseConnection.inc.php');
$dbh = new DatabaseConnection;

$id = $_GET['id'];
if(empty($_GET['id'])) {
    $error = 'Which file do you want to download?';
}
elseif(!is_numeric($_GET['id']) {
    $error = "Aaaaah! Curse your sudden but inevitable betrayal!";
}
$file = $dbh->get_path_from_id($id);
if (!file_exists($file)) $error = "File '$file' is in the database, but it doesn't exist.";

if (! empty ($error)) {
    print "<html><head><title>ERROR</title></head><body><h1>ERROR</h1><br />$error<br/></body></html>\n";
    exit;
}

  
header("Content-type: application/octet-stream");
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header("Content-Length: ". filesize($file));
readfile($file);

?>
