<?php
/**
* DatabaseConection - class to handle database requests
*/

/*
This file is part of the ebook library project
Copyright (C) 2014, Alexander Köb <nerdkram@koeb.me>

Licensed under the GNU General Public License version 3. 
See the COPYING file for a full license statement.          

*/
class DatabaseConnection
{


    // DATABASE STUFF
    private $db_host; // database host
    private $db_name; // database to connect to
    private $db_user; // database user
    private $db_pw; // database password
    private $db_conn;   // db connection
    private $errormsg;
    private $books_count = 0;

    // constructor
    function __construct($db_host, $db_name, $user, $pw)
    {

        // set the DB connection config
        $this->db_host = $db_host;
        $this->db_name = $db_name;
        $this->db_user = $user;
        $this->db_pw = $pw;

        $this->connect();
    }

    // open database connection
    function connect()
    {
        // initialize a persistent DB connection
        if (!$this->db_conn) {
            try {
                $this->db_conn = new PDO(
                    'mysql:host='.$this->db_host.';dbname='.$this->db_name, 
                    $this->db_user, 
                    $this->db_pw, 
                    array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                $this->db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die ("Failed to connect to MySQL: " . $e->getMessage()."<br/>\n");
            }
            $this->books_count = $this->query("SELECT count(*) FROM books");
        }
        return $this->db_conn;
    }


    function query($query, $params = FALSE)  {
        $this->connect();
        $stmt = $this->db_conn->prepare($query);
        
        if($params && is_array($params)) {
            $stmt->execute($params);
        }
        else {
            $stmt->execute();
        }

        $res = $stmt->fetchAll();
        $stmt = '';

        // if only one element in array:
        if (count($res) == 1) {
            // and that eement has laso only one
            if (count($res[0]) == 1) {
                // return single string
                return $res[0][0];
            }
            else {
                // or return simple array
                return $res[0];
            }
        }
        else {            // or return 2-dim array:
            return $res;
        }
    }

    function exec_stmt($query, $params = FALSE) {
        $this->connect();
        $stmt = $this->db_conn->prepare($query);

         if($params && is_array($params)) {
            $stmt->execute($params);
        }
        else {
            $stmt->execute();
        }
       
        if($stmt->execute($params)) {
            // ok, statement executed.
            // do we have a result set or was it an insert?
            $res = $stmt->rowCount();
        }
        $stmt = '';
 
        return $res;
    }



    function add_book($author, $title, $language, $path) {
        $this->exec_stmt("INSERT INTO books(author, title, language, path) VALUES (?, ?, ?, ?)", array($author, $title, $language, $path));
    }

    function delete_book($id) {
        $this->connect();
        $this->exec_stmt("DELETE FROM books WHERE id = ?", ($id));
    }

    function list_books($min = 0, $max = 0) {
        if ($max === 0) {
            $max = $this->books_count;
        }
        $count = $max - $min; 
        $this->connect();
        return $this->query("SELECT id, author, title, language FROM books LIMIT (?, ?)",array($min, $count));
    }

    function show_book($id) {
        $this->connect();
        $result = $this->query("SELECT id, author, title, language FROM books where id = ?",array($id));
        return array($result);
    }

    function all_languages() {
        $languages = $this->query("SELECT DISTINCT language FROM books");
        return $languages;
    }

    function search_author($term, $lang = '') {
        if(empty($lang)) {
            $result = $this->query("SELECT id, author, title, language FROM books  WHERE MATCH(author) AGAINST (?)",array($term));
        }
        else {
            $result = $this->query("SELECT id, author, title, language FROM books  WHERE MATCH(author) AGAINST (?) and language = ?",array($term, $lang));
        }
        return $result;
    }
    
    function search_title($term, $lang = '') {
        if(empty($lang)) {
            $result = $this->query("SELECT id, author, title, language FROM books  WHERE MATCH(title) AGAINST (?)",array($term));
        }
        else {
            $result = $this->query("SELECT id, author, title, language FROM books  WHERE MATCH(title) AGAINST (?) and language = ?",array($term, $lang));
        }
        return $result;
    }
    
    function search_author_title($term, $lang = '') {
        if(empty($lang)) {
            $result = $this->query("SELECT * FROM books  WHERE MATCH(author, title) AGAINST (?)",array($term));
        }
        else {
            $result = $this->query("SELECT * FROM books  WHERE MATCH(author, title) AGAINST (?) AND language = ?",array($term, $lang));
        }
        //print"<pre>";
        //print_r($result);
        //print"</pre>";
        return $result;
    }
    
    function is_indexed($file) {
        $id = $this->query('SELECT id FROM books WHERE path = ?', array($file));
        if (isset($id)) {
            if(is_array($id) && count($id) > 0) {
                return true;
            }
            elseif(is_numeric($id) && $id > 0) {
                return true;
            }
        }
        return false;
    }

    function get_path_from_id($id) {
        $path = $this->query('SELECT path FROM books WHERE id = ?', array($id));
        return $path[0];
    }

}
?>
