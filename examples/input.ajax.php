<?php
// input.ajax.php used with fancy-tab.html
require_once(getenv("SITELOADNAME"));
$php = file_get_contents("PHP://input");
vardump("php", $php);

vardump("start", $_POST);
if($_POST) {
  vardump($_POST);
}
echo "DONE";