<?php
// BLP 2021-04-14 -- This lets me name a file I want the user to download:
// download.php?file=somefile&path=somepath
// If no path then use the directory where download lives.
// For example to download the index.php in the directory above this we would:
// download.php?file=index.php&path=../
// I have a test-Download.php in the 'test_examples' directory

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

/*
// I was looking to see why I was getting here twice with an href=''.
// It was because the second time I had a referer.
session_start();

if(!isset($_SESSION['count'])) {
  $_SESSION['count'] = 0;
} else {
  $_SESSION['count']++;
}

echo $_SESSION['count'] . "<br>";
*/

$file = $_GET['file'];

// Note the path should be relative to the directory where download.php lives OR be absolute!
// OR no path if the file to download lives in the same directory as download.php.

$path = rtrim($_GET['path'], "/");

header('Content-Type: text/html');

$errorhdr = <<<EOF
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta name="robots" content="noindex">
</head>
EOF;

$referer = $_SERVER["HTTP_REFERER"];
error_log("download.php -- referer: $referer, ip: $S->ip, agent: $S->agent");

if(empty($referer) || strpos($referer, "bartonphillips.com") === false) {
  // BLP 2021-04-14 -- for testing make the href='' and click the 'Return to welcome page".
  echo "<h1>You got here by accident! <a href='https://www.bartonphillips.com'>Return to welcome page</a></h1>";
  exit();
}

if(empty($file)) {
  mail("bartonphillips@gmail.com",
       "$S->self, NO FILE GIVEN",
       "NO File given to download.php: Referrer={$_SERVER['HTTP_REFERER']}, IP={$_SERVER['REMOTE_ADDR']}, " .
       "AGENT={$_SERVER['HTTP_USER_AGENT']}",
       "From: download.php", "-f bartonphillips@gmail.com");

  echo <<<EOF
$errorhdr
<body>
An Error Has Occured. The Webmaster has been notified. Sorry
</body>
</html>
EOF;
  exit();
}

if($path) $path .= "/";

$fp = @fopen("${path}${file}",'r');

if($fp === false) {
  mail("bartonphillips@gmail.com", "$S->self,  File Open Error",
       "Errno=$ERRNO, $ERRSTR,\n" .
       "file info: ${path}${file},\n" .
       "Referrer={$_SERVER['HTTP_REFERER']},\nIP={$_SERVER['REMOTE_ADDR']}," .
       "AGENT={$_SERVER['HTTP_USER_AGENT']}",
       "From: download.php", "-f bartonphillips@gmail.com");

  echo <<<EOF
$errorhdr
<body>
An Error Has Occured. The Webmaster has been notified. Sorry
</body>
</html>
EOF;
  exit();
}

header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment;filename=$file");

fpassthru($fp);
fclose($fp);
