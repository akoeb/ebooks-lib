<?php
/**
* DatabaseConection - class to handle database requests
*/

/*
This file is part of the ebook library project
Copyright (C) 2013, Alexander Köb <nerdkram@koeb.me>

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
        $this->db_name = $db;
        $this->db_user = $user;
        $this->pw = $pw;

        $this->connect();
    }

    // open database connection
    function connect()
    {
        // initialize a persistent DB connection
        if (!$this->db_conn) {
            $this->db_conn = mysqli_connect ( $this->db_host, $this->db_user, $this->db_pw, $this->db_name);
            if (mysqli_connect_errno($this->db_conn)) {
                    die "Failed to connect to MySQL: " . mysqli_connect_error();
            }
            $this->books_count = $this->simple_query("SELECT count(*) FROM books");
        }
        return $this->db_conn;
    }

    // raise an exception if an error occurred
    function error($msg;) {
        throw new Exception($msg);
    }

    function simple_query($query)  {
        $this->connect();
        if (!($stmt = $this->db_conn->prepare($query))) {
                 $this->error("simple_query: Prepare of $query failed: (" . $mysqli->errno . ") " . $mysqli->error);
        }
        if (!$stmt->execute()) {
                $this->error("simple_query: Execute if $query failed: (" . $stmt->errno . ") " . $stmt->error);
        }
        $res = $stmt->get_result();
        $row = $res->fetch_all(MYSQLI_NUM);

        $stmt->close();
        // if only one element in array:
        if (count($row) == 1) {
            // and that eement has laso only one
            if (count($row[0]) == 1) {
                // return single string
                return $row[0][0];
            }
            else {
                // or return simple array
                return $row[0];
            }
        }
        else {
            // or return 2-dim array:
            return $row;
        }
    }

    function query($query, $params ) {
        $this->connect();

        // This will loop through params, and generate types. e.g. 'ss'
        $types = '';                        
        foreach($params as $param) {        
            if(is_int($param)) {
                $types .= 'i';              //integer
            } elseif (is_float($param)) {
                $types .= 'd';              //double
            } elseif (is_string($param)) {
                $types .= 's';              //string
            } else {
                $types .= 'b';              //blob and unknown
            }
        }
        array_unshift($params, $types);



        // Start stmt
        $stmt = $this->connection->stmt_init(); // $this->connection is the mysqli connection instance
        if($stmt->prepare($query)) {

            // Bind Params
            call_user_func_array(array($stmt,'bind_param'),$params);
            
            if (!$stmt->execute()) {
                $this->error("query: Execute if $stmt failed: (" . $stmt->errno . ") " . $stmt->error);
            }

            // Get metadata for field names
            $meta = $stmt->result_metadata();

            // initialise some empty arrays
            $fields = $results = array();

            // This is the tricky bit dynamically creating an array of variables to use
            // to bind the results
            while ($field = $meta->fetch_field()) { 
                $var = $field->name; 
                $$var = null; 
                $fields[$var] = &$$var; 
            }

            // Bind Results
            call_user_func_array(array($stmt,'bind_result'),$fields);

            // Fetch Results
            while ($stmt->fetch()){ $results[] = $fields; }

            $stmt->close();

            return $results);
        }
    }



    function add_book($author, $title, $language, $path) {
        $this->connect();
        $this->query("INSERT INTO books(author, title, language, path) VALUES (?, ?, ?, ?)", ($author, $title, $language, $path));
    }

    function delete_book($id) {
        $this->connect();
        $this->query("DELETE FROM books WHERE id = ?", ($id));
    }

    function list_books($min = 0, $max = 0) {
        if ($max === 0) {
            $max = $this->books_count;
        }
        $count = $max - $min; 
        $this->connect();
        return $this->query("SELECT id, author, title, language, path FROM books LIMIT (?, ?)",($min, $count))
    }

    function show_book($id) {
        $this->connect();
        return $this->query("SELECT id, author, title, language, path FROM books where id = ?",($id));
    }

    function all_languages() {
        $languages = $this->simple_query("SELECT DISTINCT language FROM books");
        return $languages;
    }

    function search_author($term, $lang = '') {
        if(empty($lang) {
            return $this->query("SELECT * FROM books  WHERE MATCH(author) AGAINST (?)",($term));
        }
        else {
            return $this->query("SELECT * FROM books  WHERE MATCH(author) AGAINST (?) and language = ?",($term, $lang));
        }
    }
    function search_title($term, $lang = '') {
        if(empty($lang) {
            return $this->query("SELECT * FROM books  WHERE MATCH(title) AGAINST (?)",($term));
        }
        else {
            return $this->query("SELECT * FROM books  WHERE MATCH(title) AGAINST (?) and language = ?",($term, $lang));
        }
    }
    function search_author_title($term, $lang = '') {
        if(empty($lang) {
            return $this->query("SELECT * FROM books  WHERE MATCH(author, title) AGAINST (?)",($term));
        }
        else {
            return $this->query("SELECT * FROM books  WHERE MATCH(author, title) AGAINST (?) AND language = ?",($term, $lang));
        }
    }
    function is_indexed($file) {
        $id = $this->query('SELECT id FROM books WHERE path = ?', ($file));
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
}
?>
