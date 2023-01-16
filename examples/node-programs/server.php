<?php
// PHP is actually Much simpler than nodejs.

if($_GET) {
  error_log("server.php GET: ". print_r($_GET, true));
  $name = $_GET['name'];
  $test = $_GET['test'];
  echo "$name $test";
  exit();
}
if($_POST) {
  $name = $_POST['name'];
  $test = $_POST['test'];
  error_log("server.php data: name=$name, test=$test");
  $ret = "$name $test";
  echo $ret;
  exit();
}
