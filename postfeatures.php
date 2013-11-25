<?php
define('TOPFILE', $_SERVER['VIRTUALHOST_DOCUMENT_ROOT'] . "/siteautoload.php");
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else throw new Exception(TOPFILE . "not found");

$S = new Blp();

$create = <<<EOF
CREATE TABLE `browserfeatures` (
  `ip` varchar(20) NOT NULL default '',
  `agent` text NOT NULL,
  `features` text,
  `audio` text,
  `video` text,
  `lasttime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ip`,`agent`(100))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;

// $features = $_POST['features'];
// $audio = $_POST['audio'];
// $video = $_POST['video'];
$features = $_GET['features'];
$audio = $_GET['audio'];
$video = $_GET['video'];
$ip = $_SERVER['REMOTE_ADDR'];
$agent = $_SERVER['HTTP_USER_AGENT'];
$err = 'No Errors';

$query = "insert into browserfeatures (ip, agent, features, audio, video) " .
         "values('$ip', '$agent', '$features', '$audio', '$video') " .
         "on duplicate key update features='$features', audio='$audio', video='$video', lasttime=now()";

try {
  $S->query($query);
} catch(Exception $e) {
  if($e->getCode() == 1146) { // table does not exist
    try {
      $S->query($create);
      $S->query($query);
    } catch(Exception $e) {
      $err = "Error: " .$e->getMessage();
    }
  } else {
    $err = "Error: " .$e->getMessage();
  }
}

if($err != "No Errors") {
  mail("bartonphillips@gmail.com", "postfeatures.php", $err,
       "From: info@bartonphillips.com",
       "-f bartonphillips@gmail.com");
}

echo "{$_GET['callback']}({err: \"$err\"})";
?>