<?php
// input.ajax.php used with fancy-tab.html
require_once(getenv("SITELOADNAME"));

$php = file_get_contents("PHP://input");
vardump($php);
if($php) {
  vardump("\$php", $php);
}
if($_GET) {
  vardump("\$_GET", $_GET);
  exit();
}
if($_POST) {
  vardump("\$_POST", $_POST);
  exit();
}
echo "Go Away";
