<?php
// BLP 2023-02-25 - use new approach
// Show the PHP_ERRORS.log and allow it the be emptied.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

if($_POST['delete']) {
  if(file_put_contents("/var/www/PHP_ERRORS.log", '') === false) {
    vardump("error", error_get_last());
    exit();
  }
  header("location: showErrorLog.php");
  echo "<div style='text-align: center'><h1>File is now empty</h1><p>Returning to Show PHP_ERRORS.log</p></div>";
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
#output { width: 100%; font-size: 11px; overflow-x: scroll; }
#delete_button { border-radius: 5px; background: red; color: white; }
.ip, .id { cursor: pointer; };
EOF;

$S->noCounter = true;

$S->b_inlineScript = <<<EOF
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
</form>
<hr>
<div id='output'>$output</div>
<hr>
$footer
EOF;
