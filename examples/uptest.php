<?php
// AJAX for http://www.bartonphillips.con/promise.php
// We send test==yes.

if($_GET['test'] == 'yes') {
  $ret = array("TEST"=>"This is from uptest.php");
  echo json_encode($ret);
  //error_log("TEST: ". print_r($ret, true));
  exit();
}
echo "Go Away";
