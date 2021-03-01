<?php
// This is a PHP side for
// curl -X POST "http://www.bartonphillips.com/examples/testpost.php" -d "test=something&something=test"
// This shows how to both use php://input and $_POST.

require(getenv("SITELOADNAME"));
$data = file_get_contents("php://input");
vardumpNoEscape("data", $data);
vardumpNoEscape("POST", $_POST);
$post = json_decode($data);

if($_POST['test'] || $post->test) {
  // this is like x = a || b; in JavaScript. Use the ?? which says 'if a exists use it, otherwise
  // use b' (since php 7)
  $test = $_POST['test'] ?? $post->test;
  $some = $_POST['something'] ?? $post->something;
  echo "test: $test, some: $some\n";
  exit();
}

echo "What\nCould be a GET or php://input without 'test'\n";
