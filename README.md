# ebook library #

## description ##

This software creates a searchable database with all your epub books and offers a download link for the found items.

I used this thing privately, so do not expect a nice user interface or an installer or similar, this thing is pretty much reduced to only those functions:
* search filesystem for epubs
* import any new epubs to a database with author, title, language
* offer a web form to search the database
* download books from a search result.

## installation ##

* drop the php files to some web directory where apache and php can pick em up and execute em.
* fill in the database settings in the config.inc.php file
* create a database in mysql
* execute the create_database.sql file against this newly created database
* rebuild the database, see below

## rebuilding the database ##

The reuild_database.php script crawls the directory recursively from the starting point you have set in config.inc.php on. It looks for files with the extension ".epub", opens those files, reads the author, title and language flags from the files metadata and imports these fields together with the full path to the database.

This script won't touch database records for files that exist already (according to the path), so it can be savely executed multiple times. Execution of this script is preferred via command line, not via web interface as follows:

php ./rebuild_database.php

However, it requires the properly filled config.inc.php script in the same directory.

The underlying DOM parser that the script uses to find the epub metadata spits out a ton of warnings if the data is somewhat unclean or if fields are not found there, but you can ignore those warnings, I was simply to lazy to write a new DOM parser that is quieter.

## author ##

Alexander Köb <nerdkram@koeb.me>

## license ##

GPLv3 - see COPYING file for full license statement




