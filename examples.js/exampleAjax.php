<?php
// Example goes with test-php-input.php. Shows getting a input via php://input and via $_POST.
// Note that the php://input has a raw input string so we must take it apart with parse_str().
// The $_POST has already had the raw string converted.
// The test-php-input.php file sends two values: 'test' and 'siteName'.
// ALSO goes with post_file_get_contents.php

$data = file_get_contents("php://input");

if($data) {
  //error_log("data: " .print_r($data, true));
  // $data is a query like val=test&val2=test2
  parse_str($data, $ar);
  //error_log("data2: ".print_r($ar, true));
  if($ar['page'] == 'beacon') {
    foreach($ar['json'] as $k=>$v) {
      $str .= "$k: $v<br>";
    }
    echo <<<EOF
<p>This is beacon: </p>
{$ar['test']}<br>
$str<br>
EOF;
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

//error_log("POST: " . print_r($_POST, true));

if(!empty($_POST['test'])) {
  echo "test={$_POST['test']}<br>";
  
  //error_log("exampleAjax.php POST: " . print_r($_POST, true));
  $json = $_POST['json'];

  if(is_string($json)) {
    $x = json_decode($json);
  }
  $test = $_POST['test'];
  $siteName = $_POST['siteName'];
  foreach($x as $k=>$v) {
    $str2 .= "$k: $v<br>";
  }
  echo <<<EOF
<p>This is a test of POST:<br>
test: $test<br>
siteName: $siteName<br>
json: $str2<br><br>
EOF;

  if(!is_string($json)) {
    //error_log("examples.js/exampleAjax.php: \$json is not a string: type=". gettype($json) . ", " . print_r($json, true));
    echo "\$json not a string: type=" . gettype($json) . ", " . print_r($json, true) . "<br>";
    exit();
  }

  $j = json_decode($json, true);

  echo <<<EOF
json_decode of j:<br>
EOF;
  foreach($j as $k=>$v) {
    echo "$k: $v<br>";
  }
}

echo "exampleAjax.php DONE<br>";
