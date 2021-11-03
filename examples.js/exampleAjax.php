<?php
// Example goes with test-php-input.php. Shows getting a input via php://input and via $_POST.
// Note that the php://input has a raw input string so we must take it apart with parse_str().
// The $_POST has already had the raw string converted.
// The test-php-input.php file sends two values: 'test' and 'siteName'.
// ALSO goes with example2.php

$data = file_get_contents("php://input");

if($data) {
  error_log("data: " .print_r($data, true));
  // $data is a query like val=test&val2=test2
  parse_str($data, $ar);
  error_log("data2: ".print_r($ar, true));
  if($ar['page'] == 'beacon') {
    foreach($ar['json'] as $k=>$v) {
      $str .= "$k: $v<br>";
    }
    echo <<<EOF
<p>This is beacon: </p>
{$ar['test']}<br>
$str<br>
EOF;
//    exit();
  }

  echo <<<EOF
<p>This is from the 'php://input':<br>
Raw: $data<br>
Parsed:<br>
test: {$ar['test']}<br>
siteName: {$ar['siteName']}<br>
json: $str<br><br>
EOF;
}

error_log("POST: " . print_r($_POST, true));

if($_POST) {
  $json = $_POST['json'];
  $test = $_POST['test'];
  $siteName = $_POST['siteName'];
  foreach($json as $k=>$v) {
    $str2 .= "$k: $v<br>";
  }
  echo <<<EOF
<p>This is a test of POST:<br>
test: $test<br>
siteName: $siteName<br>
json: $str2<br><br>
EOF;
}

$j = json_decode($json, true);

echo <<<EOF
json_decode of j:<br>
EOF;
foreach($j as $k=>$v) {
  echo "$k: $v<br>";
}
echo "\n";
