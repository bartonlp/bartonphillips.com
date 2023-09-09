<?php
// Example goes with test-php-input.php. Shows getting a input via php://input and via $_POST.
// Note that the php://input has a raw input string so we must take it apart with parse_str().
// The $_POST has already had the raw string converted.
// The test-php-input.php file sends two values: 'test' and 'siteName'.
// ALSO goes with post_file_get_contents.php

$data = file_get_contents("php://input");

if($data) {
  // $data is a query like val=test&val2=test2
  parse_str($data, $ar);
  echo "ar['page']={$ar['page']}<br>";

  if($ar['page'] == 'beacon') {
    echo "page==beacon<br>";
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

if(!empty($_POST['submit'])) {
  echo "This is the POST date:<pre>" . print_r($_POST, true) . "</pre>";
  
  //error_log("exampleAjax.php POST: " . print_r($_POST, true));
  $json = $_POST['json'];

  if(is_string($json)) {
    $x = json_decode($json);
  } else {
    error_log("JSON not a string");
  }
  
  $test = $_POST['test'];
  $siteName = $_POST['siteName'];
  foreach($x as $k=>$v) {
    $str2 .= "$k: $v<br>";
  }
  echo <<<EOF
<p>test: $test<br>
siteName: $siteName<br>
json: $str2<br></p>
EOF;

  if(is_string($json)) {
    $json = json_decode($json, true);
  } 
  echo <<<EOF
JSON:<br>
EOF;
  foreach($json as $k=>$v) {
    echo "$k: $v<br>";
  }
}

echo <<<EOF
exampleAjax.php DONE<br>
EOF;
