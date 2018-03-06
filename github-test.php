<?php
// github-test.php
// This test the web hook at github.com/bartonlp/test
// I have a wildcard (*) to post everything that happens to 'test'
// This shouldn't be much as it is pretty much an unused repository.

//$_site = require_once(getenv("SITELOADNAME"));
//$S = new Database($_site);

// It is a json post so get the json and if there is something there

$json = file_get_contents("php://input");

if($json) {
  // Then decode it and write it to our PHP_ERROR.log file.
  
  $data = json_decode($json);
  error_log("github-test.php: " . print_r($data, true));
  exit();
}

error_log("github-test.php: Didn't get any 'json' data");
echo "Go Away";
