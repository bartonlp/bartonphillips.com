<?php
// This is part of the worker group.
// This is the AJAX that is called from worker.worker.js
// The trio is worker.main.php, worker.worker.js and worker.ajax.php

$_site = require_once(getenv("SITELOADNAME"));
$S = new Database($_site);

// allow both POST and GET
// If $_REQUEST['sql'] is '' it will look like zero
// so we will fail and fall through to GO AWAY.
// So check to see if $_REQUEST['sql'] is set which
// it will be and then get $sql once we know that
// the request has sql set (even if it is '').

$data = file_get_contents("PHP://input");
//error_log("data: ". print_r($data, true));
//error_log("post: ". print_r($_POST, true));

if(isset($_POST['sql'])) {
  $sql = $_POST['sql'];
  
  if(!$sql) {
    echo "ERROR: No sql statment<br>";
    exit();
  }

  // We could be passed something is will not work

  //error_log("sql: $sql");

  try {
    $S->query($sql);

    if(preg_match("/insert/i", $sql)) {
      $S->query("select TABLE_ROWS from information_schema.TABLES where TABLE_NAME='test'");
      list($cnt) = $S->fetchrow('num');
      //error_log("cnt: $cnt");
      $nn = $cnt - 20;
      //error_log("nn: $nn");
      if($cnt > 20) {
        $n = $S->query("delete from test order by id asc limit $nn");
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
      echo json_encode("ERROR: NO DATA");
      exit();
    }
    //error_log("rows:" . print_r($rows, true));
    
    echo json_encode($rows);
    exit();
  } catch(Exception $e) {
    echo json_encode("ERROR: " . $e->getMessage());
    exit();
  }
}

echo "ERROR: GO AWAY<br>";
