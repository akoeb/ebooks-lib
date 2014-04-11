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
$dbh = new DatabaseConnection($database_host, $database, $database_user, $database_password);

$files_count = 0;
$epubs_count = 0;
$epubs_added = 0;

// we only save relative paths:
$basedir_len = strlen($basedir) + 1;

$files = find_all_files( $basedir );
foreach($files as $file ) {
    $files_count ++;
    // check only epubs
    if( preg_match('/\.epub$/', $file)) {
        $epubs_count ++;
        // if the file was not yet indexed:
        if(! $dbh->is_indexed(substr($file,$basedir_len))) {
            // can we open the file?
            if ($zip->open($file) === TRUE) {
                // load container.xml to find metadata file
                if($xml->loadXML($zip->getFromName( 'META-INF/container.xml' ))) {
                    // read metadata from file
                    $meta = $xml->getElementsByTagName( 'rootfile')->item(0)->getAttribute( 'full-path' );
                    if( $xml->loadXML( $zip->getFromName( $meta ))) {
                        $author = '';
                        if($xml->getElementsByTagName('creator') && $xml->getElementsByTagName('creator')->item(0)) {
                            $author = $xml->getElementsByTagName('creator')->item(0)->nodeValue;
                        }
                        $title = '';
                        if ($xml->getElementsByTagName('title') && $xml->getElementsByTagName('title')->item(0)) {
                            $title = $xml->getElementsByTagName('title')->item(0)->nodeValue;
                        }
                        $language = '';
                        if ($xml->getElementsByTagName('language') && $xml->getElementsByTagName('language')->item(0)) {
                            $language = $xml->getElementsByTagName('language')->item(0)->nodeValue;
                        }
                        $dbh->add_book($author, $title, $language, substr($file,$basedir_len));
                        $epubs_added ++;
                    }
                }
                $zip->close();
            } // end open file
        } // end is_indexed
    } // end preg match
}

print "Found $files_count files, $epubs_count of them were epubs and $epubs_added have been added to the database\n";


function find_all_files($dir)
{
    $root = scandir($dir);
    $result = array();
    foreach($root as $value)
    {
        if($value === '.' || $value === '..') {continue;}
        if(is_file("$dir/$value")) {
            $result[] = "$dir/$value"; 
            continue;
        }
        foreach(find_all_files("$dir/$value") as $value)
        {
            $result[] = $value;
        }
    }
    return $result;
} 

