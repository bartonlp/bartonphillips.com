<?php
// BLP 2021-04-14 -- This lets me name a file I want the user to download:
// download.php?file=somefile&path=somepath
// If no path then use the directory where download lives.
// For example to download the index.php in the directory above this we would:
// download.php?file=index.php&path=../
// I have a test-Download.php in the 'test_examples' directory

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

$file = $_GET['file'];

// Note the path should be relative to the directory where download.php lives OR be absolute!
// OR no path if the file to download lives in the same directory as download.php.

$path = $_GET['path'];

header('Content-Type: text/html');

$referer = $_SERVER["HTTP_REFERER"];

error_log("download.php -- referer: $referer, file: $file, path: $path, ip: $S->ip, agent: $S->agent");

if(empty($referer) || strpos($referer, "bartonphillips.com") === false) {
  // BLP 2021-04-14 -- for testing make the href='' and click the 'Return to welcome page".
  echo "<h1>You got here by accident! <a href='https://www.bartonphillips.com'>Return to welcome page</a></h1>";
  exit();
}

if(empty($file)) {
  error_log("download.php: No file given");
  
  echo <<<EOF
<h1>No file given. The Webmaster has been notified. Sorry</h1>
EOF;
  exit();
}

$fp = @fopen("${path}${file}",'r');

if($fp === false) {
  error_log("download.php: File open error");

  echo <<<EOF
<h1>File ${path}${file}: open error. The Webmaster has been notified. Sorry</h1>
EOF;
  exit();
}

header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment;filename=$file");

fpassthru($fp);
fclose($fp);
