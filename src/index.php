<?php 

/* This is the main index page

  It can be called with following get parameters:
  * id => a book id
    show author, title and language of a book and the download link
  * term or author or => searchterm
    search for term in author and title or search for author or for title
    show author, title and language of all found books
    this can be filtered by language
*/
/*
This file is part of the ebook library project
Copyright (C) 2014, Alexander Köb <nerdkram@koeb.me>

Licensed under the GNU General Public License version 3. 
See the COPYING file for a full license statement.          

*/
?>
<!DOCTYPE html>
<html>
<head>
<title>ebooks library</title>
<meta charset="utf-8">
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>ebooks library</h1>
<form action="index.php" method="get">
Search for book:
<table>
<tr><td>author:</td><td><input type="text" name="author"></td></tr>
<tr><td>title:</td><td><input type="text" name="title"></td></tr>
<tr><td>both:</td><td><input type="text" name="term"></td></tr>
<tr><td>id:</td><td><input type="text" name="id"></td></tr>
</table>
<input type="submit" name="submit" value="Go">
</form>
<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/
require('config.inc.php');
require('DatabaseConnection.inc.php');
$dbh = new DatabaseConnection($database_host, $database, $database_user, $database_password);

$books = [];

if (! empty($_GET['id'])) {
    // show only one book
    $books = $dbh->show_book($_GET['id']);
}
elseif (!empty($_GET['term'])) {
    // search over author or title
    $lang = empty($_GET['lang']) ? '' : $_GET['lang'];
    $books = $dbh->search_author_title($_GET['term'], $lang);
}
elseif (!empty($_GET['author'])) {
    $lang = empty($_GET['lang']) ? '' : $_GET['lang'];
    $books = $dbh->search_author($_GET['author'], $lang);
}
elseif (!empty($_GET['title'])) {
    $lang = empty($_GET['lang']) ? '' : $_GET['lang'];
    $books = $dbh->search_title($_GET['title'], $lang);
}

if(count($books) > 0) {
   print '<strong>Found '.count($books)." results.</strong><br/>\n";
    print '<table border="1"> <tr><td>Autor</td><td>Titel</td><td>Sprache</td><td>Link</td></tr>'."\n";
    foreach ($books as $idx) {
        print "<tr><td>".$idx['author'].'</td><td>'.$idx['title'].'</td><td>'.$idx['language'].'</td><td><a href="download.php?id='.$idx['id'].'">download</a></td></tr>'."\n";
    }
    print "</table>\n";
}
else {
    print "No results!<br />\n";
}
/* <pre><?php print_r($books); ?></pre> */
?>
</body>
</html>
