<?php

$input = file_get_contents("php://input");

if(empty($input)) {
  $ip = $_SERVER['REMOTE_ADDR'];
  error_log("examples.js/cspreport.php raw: input is null or empty, ip=$ip");
  echo "cspreport.php: No input, ip=$ip<br>";
} else { 
  $data = json_decode($input);

  if($data === null) {
    $msg = "json_decode returned NULL, ";
  } else {
    $msg = "json_decode OK, ";
  }

  error_log("examples.js/cspreport.php: $msg data: ". print_r($data, true). ", input=$input");
}
