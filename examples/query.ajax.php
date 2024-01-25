<?php
// This is a little test script to do a sql query and fetch.
// It is used by query-test.php which does the new 'async' 'await' in javascript.
// The AJAX call is raw javascript no jQuery.
// Also used by https://www.bartonphillips.com/promise.php

$_site = require_once(getenv("SITELOADNAME"));
$S = new Database($_site);

if($sql = $_POST['sql']) {
  //error_log("sql" . print_r($_POST, true));
  $S->sql($sql);
  $rows = [];
  while($row = $S->fetchrow('assoc')) {
    //error_log("row: ". print_r($row, true));
    $rows[] = $row;
  }

  echo json_encode($rows);
  exit();
}

echo "<h1>GO AWAY</h1>";
