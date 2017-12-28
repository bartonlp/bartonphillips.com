<?php
// input.ajax.php used with fancy-tab.html
require_once(getenv("SITELOADNAME"));
$php = file_get_contents("PHP://input");
vardumpNoEscape("php", $php);

vardumpNoEscape("start", $_POST);
if($_POST) {
  vardumpNoEscape($_POST);
}
echo "DONE";