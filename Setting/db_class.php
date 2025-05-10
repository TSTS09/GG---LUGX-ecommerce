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
     **/
    private function db_connect()
    {
        try {
            // Log connection attempt
            error_log("Attempting database connection to " . DB_SERVER . " with user " . DB_USERNAME);

            //connection
            $this->db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

            //test the connection
            if (mysqli_connect_errno()) {
                error_log("Database connection failed: " . mysqli_connect_error());
                return false;
            } else {
                error_log("Database connection successful");
                return true;
            }
        } catch (Exception $e) {
            error_log("Exception during database connection: " . $e->getMessage());
            return false;
        }
    }

    function db_conn()
    {
        try {
            // Ensure the connection is established
            if ($this->db === null) {
                $connection_result = $this->db_connect();
                if (!$connection_result) {
                    error_log("Failed to establish database connection in db_conn()");
                }
            }

            // Test the connection is still alive
            if ($this->db && mysqli_ping($this->db) === false) {
                error_log("Database connection lost, attempting to reconnect");
                $this->db = null;
                $this->db_connect();
            }

            // Return the connection
            return $this->db;
        } catch (Exception $e) {
            error_log("Exception in db_conn: " . $e->getMessage());
            return null;
        }
    }

    //execute a query
    /**
     *Query the Database
     *@param string $sqlQuery (connection) and The SQL query to execute
     *@return boolean
     **/
    function db_query($sqlQuery)
    {
        try {
            // Ensure the connection is established
            if ($this->db === null) {
                $this->db_connect();
            }

            // Log query attempt (truncate very long queries)
            $log_query = strlen($sqlQuery) > 1000 ? substr($sqlQuery, 0, 1000) . "..." : $sqlQuery;
            error_log("Executing query: " . $log_query);

            //run query 
            $this->results = mysqli_query($this->db, $sqlQuery);

            if ($this->results == false) {
                error_log("Query failed: " . mysqli_error($this->db));
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            error_log("Exception in db_query: " . $e->getMessage());
            return false;
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
        try {
            // Ensure the connection is established
            if ($this->db === null) {
                $this->db_connect();
            }

            // Log query attempt
            $log_query = strlen($sqlQuery) > 1000 ? substr($sqlQuery, 0, 1000) . "..." : $sqlQuery;
            error_log("Executing escaped query: " . $log_query);

            //run query 
            $this->results = mysqli_query($this->db, $sqlQuery);

            if ($this->results == false) {
                error_log("Escaped query failed: " . mysqli_error($this->db));
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            error_log("Exception in db_query_escape_string: " . $e->getMessage());
            return false;
        }
    }

    //fetch a data
    /**
     *get select data
     *@return array|bool record
     **/
    function db_fetch_one($sql)
    {
        try {
            // if executing query returns false
            if (!$this->db_query($sql)) {
                error_log("db_fetch_one: Query execution failed");
                return false;
            }

            //return a record
            $result = mysqli_fetch_assoc($this->results);

            if ($result === null) {
                error_log("db_fetch_one: No records found");
            }

            return $result;
        } catch (Exception $e) {
            error_log("Exception in db_fetch_one: " . $e->getMessage());
            return false;
        }
    }

    //fetch all data
    /**
     *get select data
     *@return array|bool record
     **/
    function db_fetch_all($sql)
    {
        try {
            // if executing query returns false
            if (!$this->db_query($sql)) {
                error_log("db_fetch_all: Query execution failed");
                return false;
            }

            //return all record
            $results = mysqli_fetch_all($this->results, MYSQLI_ASSOC);

            error_log("db_fetch_all: Found " . count($results) . " records");
            return $results;
        } catch (Exception $e) {
            error_log("Exception in db_fetch_all: " . $e->getMessage());
            return false;
        }
    }

    //count data
    /**
     *get select data
     *@return bool count
     **/
    function db_count()
    {
        try {
            //check if result was set
            if ($this->results == null) {
                error_log("db_count: Results is null");
                return false;
            } elseif ($this->results == false) {
                error_log("db_count: Results is false");
                return false;
            }

            //return a record
            $count = mysqli_num_rows($this->results);
            error_log("db_count: Count is " . $count);
            return $count;
        } catch (Exception $e) {
            error_log("Exception in db_count: " . $e->getMessage());
            return false;
        }
    }

    // Destructor to close the connection
    public function __destruct()
    {
        if ($this->db !== null) {
            mysqli_close($this->db);
        }
    }
}
