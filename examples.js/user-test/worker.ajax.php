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

This table is in database "test" on bartonphillips.com.
The database allows only 'select, update, insert and delete' and the code below maintains a max of 20 entries.
The standard mysitemap.json has my normal database stuff. We change it here to ues the 'test'
user and the 'test' database which has the 'test' table.
*/

//exit("<h1>Not Authorized</h1>");

$_site = require_once(getenv("SITELOADNAME"));

//ErrorClass::setNoEmail(false);
ErrorClass::setErrlast(true);

$_site->dbinfo->user = "barton"; // use test user
$_site->dbinfo->database = "test"; // and test database
$_site->noTrack = true; // needed because user is test not barton.
$S = new Database($_site); // Database does not do any counting and sets noTrack true by default.

//error_log("POST: ".print_r($_POST, true));

if($_POST['page'] != 'start') {
  echo "ERROR: This program should not be run directly. Run 'worker.main.php' instead.<br>";
  error_log("worker.ajax.php, ERROR: This program should not be run directly. Run 'worker.main.php' instead. $S->ip, $S->self, $S->agent");
  exit();
}

if(empty($sql = $_POST['sql'])) {;
  echo json_encode(["ERROR"=>"No sql statment"]);
  exit();
} 

// We are using fetch() in worker.worker.js so we need to get the data from 'php://input'

//$sql = file_get_contents("php://input");
//error_log("worker.ajax.php, php://input=$sql");
//exit();

// We could be passed something that will not work

try {
  if(preg_match("/insert/i", $sql)) {
    // We want to restrict the size of this table so check the TABLE_ROWS

    $S->sql("select count(*) from test");

    $cnt = $S->fetchrow('num')[0];
    $nn = $cnt - 19; // This is the number to delete

    if($cnt > 20) {
      $n = $S->sql("delete from test order by lasttime asc limit $nn"); // leave most resent 20
      $del = "Deleted $n items";
    }
  }

  $n = $S->sql($sql);

  if(preg_match("/update|insert|delete/", $sql)) {
    echo json_encode(["DONE"=>"$del Rows Affected: $n"]);
    exit();
  }

  $rows = [];

  while($row = $S->fetchrow('assoc')) {
    $rows[] = $row;
  }

  if(!count($rows)) {
    echo json_encode(["ERROR"=>"NO DATA"]);
    exit();
  }

  echo json_encode($rows); // encode the data and send it.
  exit();
} catch(Exception $e) {
  $tmp = json_encode(["ERROR"=> $e->getMessage()]);
  echo $tmp;
  throw(new Exception($e));
}

