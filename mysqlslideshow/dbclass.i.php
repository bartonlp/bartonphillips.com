<?php

class Database {
  public $db = 0;
  private $host, $user, $password, $database;
  
  public function __construct($host, $user, $password, $database) {
    //echo "<br>host=$host, user=$user, password=$password, database=$database<br>\n";
    $this->host = $host;
    $this->user = $user;
    $this->password = $password;
    $this->database = $database;

    $this->opendb();  // Make sure we have a db even before we do a query!
  }
  
  //----------------------------------------------------------
  // resourceId opendb(<host>, <user>, <password>, <database>)
  // Opens the database, returns the resourceId
  //----------------------------------------------------------
  
  private function opendb() {
    // Only do one open
    
    if($this->db) {
      return $this->db;
    }
                        
    $db = mysql_connect($this->host, $this->user, $this->password, true);

    if(!$db) {
      echo __FILE__ ." Can't connect to database: host=$this->host, user=$this->user, password=$this->password\n";
      exit;
    }

    if(!mysql_select_db($this->database, $db)) {
      echo __FILE__ . " Can't select database: database=$this->database\n";
      exit;
    }
    $this->db = $db;
    return $db;
  }

  //-------------------------------
  // resultId query(<query string>)
  //  On error calls SqlError() which outputs error message.
  //   On error function exits.
  //     !! This could also return false if error.
  //  returns resultId
  //-------------------------------
  
  public function query($query) {
    //$db = $this->opendb();
    $db = $this->db;  // the constructor opens the database!
    
    $result = mysql_query($query, $db);

    if(!$result) {
      SqlError(mysql_error($db), $query);
      // or could return false
      exit();
    }
    return $result;
  }

  //--------------------------------
  // resourceId getDb()
  // returns the database resourceId
  // NOTE there is no setDb()!
  //--------------------------------
  
  public function getDb() {
    return $this->db;
  }
}

//-----------------
// Database errors
// Simple function not an exception
//-----------------

if(!function_exists('SqlError')) {
  function SqlError($msg, $query) {
    $PHP_SELF = $_SERVER['PHP_SELF'];

    echo "<h1>SQL DEBUG INFO: ERROR in $PHP_SELF:</h1>\n";
    echo "<p>$msg</p>\n";
    echo "<p>Query: $query</p>\n";
//  mail("info@granbyrotary.org", "PhpError in $PHP_SELF", "Message:
//  $msg\nQuery=$query");  
  }
}

//------------------------------------------
// Helper function
// Function to strip slashes from $row array
//------------------------------------------

if(!function_exists('stripSlashesDeep')) {
  function stripSlashesDeep($value) {
    $value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value); 
    return $value;
  }
}

// WARNING THERE MUST BE NOTHING AFTER THE CLOSING PHP TAG.
// Really nothing not even a space!!!!
?>