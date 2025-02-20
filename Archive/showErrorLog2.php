<?php
// BLP 2023-02-25 - use new approach
// Show the PHP_ERRORS.log or PHP_ERRORS_CLI.log and allow it the be emptied.
// BLP 2023-10-27 - This also shows how to set 'localstorage' via PHP and read it back via javascript

$_site = require_once(getenv("SITELOADNAME"));
//$_site = require_once "/var/www/site-class/includes/autoload.php";

$S = new SiteClass($_site);

// Delete all errors from delname.

if($_POST['delete']) {
  $delname = $_POST['delname'];
  
  if(file_put_contents($delname, '') === false) {
    vardump("error", error_get_last());
    exit();
  }

  // BLP 2023-10-27 - Here I want to set the localstorage for this computer.
  // I can echo a <script>
  // Then I must wait a little while before reloading the program.
  // If I just do 'header("location: showErrorLog.php");'
  // it does not work. Even a small delay is sufficent as long as we get to the exit().
  
  $del = date("Y-m-d H:i:s");
  echo "<script>localStorage.setItem('ShowPhpErrorLog', '$del');</script>";
  header("refresh:0.02;url=showErrorLog.php?page=$delname");
  exit();
}

// If '$page' is set get the page data, else get either PHP_ERRORS.log or PHP_ERRORS_CLI.log

if($page = $_GET["page"]) {
  $output = file_get_contents($page);
} else {
  $S->title = "Get Page";
  $S->banner = "<h1>Select Error Log</h1>";
  [$top, $footer] = $S->getPageTopBottom();
  
  echo <<<EOF
$top
<hr>
<a href="showErrorLog.php?page=/var/www/PHP_ERRORS.log">PHP_ERRORS.log</a><br>
<a href="showErrorLog.php?page=/var/www/PHP_ERRORS_CLI.log">PHP_ERRORS_CLI.log</a><br>
<hr>
$footer
EOF;
  exit();
}

$logname = basename($page);

if(!$output) { 
  $output = "<h1>No Data in $logname</h1>";
} else {
  $output = preg_replace(["~ America/New_York~", "~-2022~"], '', $output);
  // BLP 2024-11-15 - make home ip red.
  $output = preg_replace(["~(195\.252\.232\.86)~", "~(192\.241\.132\.229)~"], "<span style='color: red'>$1</span>", $output);

  $output = preg_replace("~(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})~", "<span class='ip'>$1</span>", $output);
  $output = preg_replace("~(tracker: |beacon:  )(\d+),~", "$1<span class='id'>$2</span>", $output);
  // BLP 2023-10-27 - 'id(value)' is from tracker when the ID_IS_NOT_NUMERIC. This does not happen
  // much.
  $output = preg_replace("~(id\(value\)=)(\d+)~", "$1<span class='id'>$2</span>", $output);
  // BLP 2023-10-27 - 'id=' is from checktracker2.php.
  $output = preg_replace("~(id=)(\d+)~", "$i<span class='id'>$2</span>", $output);
  $output = "<pre>$output</pre>";
}

$S->title = "Show Error Log";
$S->banner = "<h1>Show $logname</h1>";

$S->css =<<<EOF
#output { width: 100%; font-size: 18px; overflow-x: scroll; }
#delete_button { border-radius: 5px; font-size: var(--blpFontSize); background: red; color: white; }
.ip, .id { cursor: pointer; };
EOF;

$S->noCounter = true;

$S->b_inlineScript = <<<EOF
let del = localStorage.getItem("ShowPhpErrorLog"); // BLP 2023-10-27 - load the time from localStorage.
console.log("DEL: ", del);
$("#del-time").html("<p>Last Delete Time: " + del + "</p>");

$(".ip,.id").on("click", function(e) {
  const idOrIp = $(this).text();
  const cl = e.currentTarget.className;

  window.open("findip.php?where=" +encodeURIComponent("where " +cl+"='" +idOrIp+ "'")+"&and=" +encodeURIComponent("and lasttime>current_date() -interval 5 day")+
              "&by=" +encodeURIComponent("order by lasttime desc"), "_blank");

  $(this).css({ background: "green", color: "white"});
});
EOF;

[$top, $footer] = $S->getPageTopBottom();

header("refresh:1800; url=showErrorLog.php?page=$page"); // 30 min

echo <<<EOF
$top
<hr>
<form method='post'>
<input type="hidden" name="delname" value="$page">
<button id="delete_button" type='submit' name='delete' value="delete">Delete</button>
<div id="del-time"></div>
</form>
<hr>
<div id='output'>$output</div>
<hr>
$footer
EOF;
