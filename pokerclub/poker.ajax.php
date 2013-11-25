<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Database($dbinfo);

// Options: id, val, or reset

if($_GET['reset'] == 'reset') {
  $S->query("update pokermembers set canplay='reset'");
  exit();
}

$id = $_GET['id'];
$val = $_GET['val'];

$S->query("update pokermembers set canplay='$val' where id='$id'");

?>