<?php
// Show the PHP_ERRORS.log and allow it the be emptied.

$_site = require_once(getenv("SITELOADNAME"));
$S = new $_site->className($_site);

if($_POST['delete']) {
  if(file_put_contents("/var/www/PHP_ERRORS.log", '') === false) {
    vardump("error", error_get_last());
    exit();
  }
  header("refresh:2;url=showErrorLog.php");
  echo "<div style='text-align: center'><h1>File is now empty</h1><p>Returning to Show PHP_ERRORS.log</p></div>";
  exit();
}

$output = file_get_contents("/var/www/PHP_ERRORS.log");
if(!$output) { 
  $output = "<h1>No Data in PHP_ERRORS.log</h1>";
} else {
  $output = preg_replace("~ America/New_York~", '', $output);
  $output = preg_replace("~(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})( --)~", "<span>$1</span>$2", $output);
  $output = "<pre>$output</pre>";
}

$h->banner = "<h1>Show PHP_ERRORS.log</h1>";
$h->css =<<<EOF
#output { width: 100%; font-size: 20px; overflow-x: scroll; }
#delete_button { border-radius: 5px; background: red; color: white; }
span { cursor: pointer; };
EOF;
$b->noCounter = true;
$b->inlineScript = <<<EOF
$("span").on("click", function() {
  let thisIp = $(this).text();
  window.open("findip.php?ip="+thisIp, "_blank");
});
EOF;

[$top, $footer] = $S->getPageTopBottom($h, $b);

header("refresh:300; url=showErrorLog.php");

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
