<?php
//database credentials
require('db_cred.php');

/**
 *@version 0.0.1
 */
class db_connection
{
    //properties
    private $db = null;
    public $results = null;

    //connect
    /**
     *Database connection
     *@return boolean
     **/    //connect
     private function db_connect()
     {
       
         //connection
         $this->db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
         //test the connection
         if (mysqli_connect_errno()) {
         
             return false;
         } else {
           
             return true;
         }
     }
    
    function db_conn()
    {
        
        // Ensure the connection is established
        if ($this->db === null) {
            $this->db_connect();
        } else {
        }

        // Return the connection
        return $this->db; 
    }

    //execute a query
    /**
     *Query the Database
     *@param string $sqlQuery (connection) and The SQL query to execute
     *@return boolean
     **/
    function db_query($sqlQuery)
    {
        // Ensure the connection is established
        if ($this->db === null) {
            $this->db_connect();
        }

        //run query 
        $this->results = mysqli_query($this->db, $sqlQuery);

        if ($this->results == false) {
            return false;
        } else {
            return true;
        }
    }

    //execute a query with mysqli real escape string
    //to safeguard from sql injection
    /**
     *Query the Database
     *@param string $sqlQuery (connection) and The SQL query to execute
     *@return boolean
     **/
    function db_query_escape_string($sqlQuery)
    {
        // Ensure the connection is established
        if ($this->db === null) {
            $this->db_connect();
        }

        //run query 
        $this->results = mysqli_query($this->db, $sqlQuery);

        if ($this->results == false) {
            return false;
        } else {
            return true;
        }
    }

    //fetch a data
    /**
     *get select data
     *@return array|bool record
     **/
    function db_fetch_one($sql)
    {
        // if executing query returns false
        if (!$this->db_query($sql)) {
            return false;
        }
        //return a record
        return mysqli_fetch_assoc($this->results);
    }

    //fetch all data
    /**
     *get select data
     *@return array|bool record
     **/
    function db_fetch_all($sql)
    {
        // if executing query returns false
        if (!$this->db_query($sql)) {
            return false;
        }
        //return all record
        return mysqli_fetch_all($this->results, MYSQLI_ASSOC);
    }
    
    //count data
    /**
     *get select data
     *@return bool count
     **/
    function db_count()
    {
        //check if result was set
        if ($this->results == null) {
            return false;
        } elseif ($this->results == false) {
            return false;
        }

        //return a record
        return mysqli_num_rows($this->results);
    }

    // Destructor to close the connection
    public function __destruct()
    {
        if ($this->db !== null) {
            mysqli_close($this->db);
        }
    }
    
}

