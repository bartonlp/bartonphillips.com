<?php
// BLP 2023-02-25 - use new approach
// Show the PHP_ERRORS.log and allow it the be emptied.
// This also shows how to set 'localstorage' via PHP.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

if($_POST['delete']) {
  if(file_put_contents("/var/www/PHP_ERRORS.log", '') === false) {
    vardump("error", error_get_last());
    exit();
  }

  // Here I want to set the localstorage for this computer.
  // I can echo a <script>
  // Then I must wait a little while before reloading the program.
  // If I just do 'header("location: showErrorLog.php");'
  // it does not work. Even a small delay is sufficent as long as we get to the exit().
  
  $del = date("Y-m-d H:i:s");
  echo "<script>localStorage.setItem('ShowPhpErrorLog', '$del');</script>";
  header("refresh:0.02;url=showErrorLog.php");
  exit();
}

$output = file_get_contents("/var/www/PHP_ERRORS.log");

if(!$output) { 
  $output = "<h1>No Data in PHP_ERRORS.log</h1>";
} else {
  $output = preg_replace(["~ America/New_York~", "~-2022~"], '', $output);
  $output = preg_replace("~(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})~", "<span class='ip'>$1</span>", $output);
  $output = preg_replace("~(tracker: |beacon:  )(\d+),~", "$1<span class='id'>$2</span>", $output);
  $output = preg_replace("~(id\(value\)=)(\d+)~", "$1<span class='id'>$2</span>", $output);
  $output = "<pre>$output</pre>";
}

$S->title = "Show Error Log";
$S->banner = "<h1>Show PHP_ERRORS.log</h1>";

$S->css =<<<EOF
#output { width: 100%; font-size: 18px; overflow-x: scroll; }
#delete_button { border-radius: 5px; font-size: var(--blpFontSize); background: red; color: white; }
.ip, .id { cursor: pointer; };
EOF;

$S->noCounter = true;

$S->b_inlineScript = <<<EOF
let del = localStorage.getItem("ShowPhpErrorLog");
console.log("DEL: ", del);
$("#del-time").html("<p>Last Delete Time: " + del + "</p>");

$(".ip").on("click", function() {
  let thisIp = $(this).text();
  window.open("findip.php?ip="+thisIp, "_blank");
});
$(".id").on("click", function() {
  console.log("id clicked");
  let thisId = $(this).text();
  window.open("findip.php?id="+thisId, "_blank");
});
EOF;

[$top, $footer] = $S->getPageTopBottom();

header("refresh:1800; url=showErrorLog.php"); // 30 min

echo <<<EOF
$top
<hr>
<form method='post'>
<button id="delete_button" type='submit' name='delete' value="delete">Delete</button>
<div id="del-time"></div>
</form>
<hr>
<div id='output'>$output</div>
<hr>
$footer
EOF;
