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
?>
<DOCTYPE html>
<html>
<head>
<title>Brezialisten Bibliothek</title>
</head>
<body>
<h1>Brezialisten-Bibliothek</h1>
<table>
<th><td>Autor</td><td>Titel</td><td>Sprache</td><td>Link</td></tr>
<?php
require('config.inc.php');
require('DatabaseConnection.inc.php');
$dbh = new DatabaseConnection;

$books = [];

if ($_GET['id']) {
    // show only one book
    array_push($books, $dbh->show_book($_GET['id']);
}
elseif ($_GET['term']) {
    // search over author or title
    array_push($books, $dbh->search_author_title($_GET['term'], $_GET['lang']);
}
elseif ($_GET['author']) {
    array_push($books, $dbh->search_author($_GET['author'], $_GET['lang']);
}
elseif ($_GET['title']) {
    array_push($books, $dbh->search_title($_GET['title'], $_GET['lang']);
}

foreach ($books as $idx) {
    print "<tr><td>".$books['author'].'</td><td>'.$books['title'].'</td><td>'.$books['language'].'</td><td><a href="download.php?id="'.$books['id'].'">download</a></td></tr>'."\n";
}
?>
</table>
</body>
</html>
