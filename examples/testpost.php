<?php
// This is a PHP side for
// curl -X POST "http://www.bartonphillips.com/examples/testpost.php" -d "test=something&something=test"
// This shows how to both use php://input and $_POST.
//
// This is ALSO used by testfetch.php!

require(getenv("SITELOADNAME"));
$data = file_get_contents("php://input");
//vardumpNoEscape("data", $data);
//echo PHP_EOL;
//vardumpNoEscape("POST", $_POST);

//error_log("testpost.php: " . print_r($data, true) . ", POST: " . print_r($_POST, true));

$post = json_decode($data);

if($_POST['test'] || $post->test) {
  // this is like x = a || b; in JavaScript. Use the ?? which says 'if a exists use it, otherwise
  // use b' (since php 7)
  $type = $_POST['test'] ? 'post' : 'data';
  $test = $_POST['test'] ?? $post->test;
  $some = $_POST['something'] ?? $post->something;
  echo json_encode(['type'=>$type, 'test'=>$test, 'something'=>$some]) . "\n"; // the \n is for curl.
  exit();
}

echo PHP_EOL . "What?\nCould be a GET or php://input without 'test'" . PHP_EOL;
