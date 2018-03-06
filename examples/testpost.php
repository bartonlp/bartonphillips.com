<?php
// This is a PHP side for
// curl -X POST "http://www.bartonphillips.com/examples/testpost.php" -d "test=something&something=test"
// This shows how to both use php://input and $_POST.

$data = file_get_contents("php://input");
var_dump("data", $data);

if($_POST['test']) {
  $test = $_POST['test'];
  $some = $_POST['something'];
  echo "test: $test, some=$some\n";
  exit();
}

echo "What\nCould be a GET or php://input without 'test'\n";
