<?php
// This is part of the worker group.
// This is the AJAX server that is called from worker.worker.js
// The trio is worker.main.php, worker.worker.js and worker.ajax.php
/*
CREATE TABLE `test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(254) DEFAULT NULL,
  `lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

This table is in database "test" on bartonlp.com.
The database allows only 'select, update, insert and delete' and the code below maintains a max of 20 entries.
*/

$_site = require_once(getenv("SITELOADNAME"));
ErrorClass::setDevelopment(true);
$S = new Database($_site);

// allow both POST and GET
// If $_REQUEST['sql'] is '' it will look like zero
// so we will fail and fall through to GO AWAY.
// So check to see if $_REQUEST['sql'] is set which
// it will be and then get $sql once we know that
// the request has sql set (even if it is '').

if(isset($_REQUEST['sql'])) {
  $sql = $_REQUEST['sql'];
  
  if(!$sql) {
    echo "ERROR: No sql statment<br>";
    exit();
  }

  // We could be passed something is will not work

  try {
    $S->query($sql);

    if(preg_match("/insert/i", $sql)) {
      // We want to restrict the size of this table so check the TABLE_ROWS
      
      $S->query("select TABLE_ROWS from information_schema.TABLES where TABLE_NAME='test'");

      list($cnt) = $S->fetchrow('num');

      $nn = $cnt - 20; // This is the number to delete

      //error_log("worker.ajax.php, cnt: $cnt nn: $nn");

      if($cnt > 20) {
        $n = $S->query("delete from test order by id asc limit $nn"); // leave most resent 20
        echo "DONE $n<br>";
        exit();
      }
    }
    if(preg_match("/update|insert|delete/", $sql)) {
      echo "DONE<br>";
      exit();
    }
    
    $rows = array();

    while($row = $S->fetchrow('assoc')) {
      $rows[] = $row;
    }
    
    if(!count($rows)) {
      echo "ERROR: NO DATA<br>";
      exit();
    }
    //error_log("worker.ajax.php, rows:" . print_r($rows, true));
    
    echo json_encode($rows);
    exit();
  } catch(Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    exit();
  }
}

echo "ERROR: GO AWAY<br>";
