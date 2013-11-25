<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Blp; // count page

$file = $_GET['file'];

// Note the path should be relative to the directory where download.php lives OR be absolute!
// OR no path if the file to download lives in the same directory ad download.php.

$path = rtrim($_GET['path'], "/");

header('Content-Type: text/html');

$errorhdr = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<meta name="robots" content="noindex">
</head>
EOF;

if(empty($_SERVER["HTTP_REFERER"])) {
  echo "You got here by accident! <a href=''> Return to welcome page</a><br>";
  exit();
}

if(empty($file)) {
  mail("bartonphillips@gmail.com",
       "$S->self, NO FILE GIVEN",
       "NO File given: Referrer={$_SERVER['HTTP_REFERER']}, IP={$_SERVER['REMOTE_ADDR']}, " .
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
?>