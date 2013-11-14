<?php
/*
Loop over filesystem starting at base dir from config
find all epubs, read author, title and laguage from metadata
and insert them all in the database
*/

require('config.inc.php');
require('DatabaseConnection.inc.php');
$zip = new ZipArchive;
$xml = new DOMDocument;
$dbh = new DatabaseConnection;

$files = scandir( $basedir );
foreach($files as $file ) {
    // check only epubs
    if( preg_match('/\.epub$/', $file)) {
        // if the file was not yet indexed:
        if(! $db->is_indexed($file) {
            // can we open the file?
            if ($zip->open($file) === TRUE) {
                // load container.xml to find metadata file
                if($xml->loadXML($zip->getFromName( 'META-INF/container.xml' ))) {
                    // read metadata from file
                    $meta = $xml->getElementsByTagName( 'rootfile')->item(0)->getAttribute( 'full-path' );
                    if( $xml->loadXML( $zip->getFromName( $meta ))) {
                        $author = $xml->getElementsByTagName('creator')->item(0)->nodeValue;
                        $title = $xml->getElementsByTagName('title')->item(0)->nodeValue;
                        $language = $xml->getElementsByTagName('language')->item(0)->nodeValue;
                        $dbh->add_book($author, $title, $language, $file);
                    }
                }
                $zip->close();
            } // end open file
        } // end is_indexed
    } // end preg match
}

