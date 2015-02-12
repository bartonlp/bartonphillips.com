<?php
require_once("/var/www/includes/siteautoload.class.php");
$S = new Database($dbinfo);

// Options: id, val, or reset

if($_GET['reset'] == 'reset') {
  $S->query("update pokermembers set canplay='reset'");
  exit();
}

$id = $_GET['id'];
$val = $_GET['val'];

$S->query("update pokermembers set canplay='$val' where id='$id'");
