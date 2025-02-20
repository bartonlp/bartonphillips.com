<?php
// BLP 2023-02-25 - use new approach
// Show the PHP_ERRORS.log or PHP_ERRORS_CLI.log and allow it the be emptied.
// BLP 2025-02-09 - Reworked getting the lines.

$_site = require_once(getenv("SITELOADNAME"));
//$_site = require_once "/var/www/site-class/includes/autoload.php";

$_site->noGeo = true;

function parsedata($output) {
  $lines = "";
  
  foreach($output as $v) {
    // Don't display lines with showErrorLog.php
    if(preg_match("~page=/showErrorLog.php|ip=195.252.232.86, site=Bartonphillips~", $v) !== 0) {
      continue;
    }
    //echo "v=$v<br>";
    $line = preg_replace(["~ America/New_York~", "~-2022~"], '', $v);

    // make home ip red.
    $line = preg_replace(["~(195\.252\.232\.86)~", "~(192\.241\.132\.229)~"], "<span style='color: red'>$1</span>", $line);
    $line = preg_replace("~(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})~", "<span class='ip'>$1</span>", $line);
    $line = preg_replace("~(tracker.*?:|beacon.*?:).*?(\d+),~", "$1 <span class='id'>$2</span>", $line);
    // BLP 2023-10-27 - 'id(value)' is from tracker when the ID_IS_NOT_NUMERIC. This does not happen
    // much.
    //$line = preg_replace("~(id\(value\)=)(\d+)~", "$1<span class='id'>$2</span>", $line);
    // BLP 2023-10-27 - 'id=' is from checktracker2.php.
    //$line = preg_replace("~(id=)(\d+)~", "$1<span class='id'>$2</span>", $line);

    $lines .= $line;
  }

  return "<pre>$lines</pre>";
}

$S = new SiteClass($_site);

// Ajax call from JavaScript

if($_POST['page'] == "newdata") {
  $output = file($_POST['file']);
  $output =  parsedata($output);
  if($output == "<pre></pre>") {
    echo "<h1>No Data in " . basename($_POST['file']) . "</h1>";
    exit();
  }
  echo $output;
  exit();
}

// Form POST. Delete all errors from delname.

if($_POST['delete']) {
  $delname = $_POST['delname'];
  
  if(file_put_contents($delname, '') === false) {
    vardump("error", error_get_last());
    exit();
  }

  $del = date("Y-m-d H:i:s");

  // Because this is a 'form' post I can just drop into the normal GET logic.
}

// If '$page' is set get the page data, else get either PHP_ERRORS.log or PHP_ERRORS_CLI.log
// NOTE if we drop through from the 'form' post page will still be set.

if($page = $_GET["page"]) {
  $output = file($page);
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
  $output = parsedata($output);
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
let win = null;
const del = "$del";
const file = "$page";
console.log("page=" + file);
console.log("Last Delete time: ", del);

$("#del-time").html("<p>Last Delete Time: " + del + "</p>");

$("body").on("click",".ip,.id", function(e) {
  // If we already have a findip.php opned by this function, close it.

  if(win && !win.closed) {
    win.close();
  }

  const idOrIp = $(this).text(); //.split("=")[1];
  const cl = e.currentTarget.className;

  const where = "where " +cl+"='" +idOrIp+ "'";
  const and = "and lasttime>current_date() -interval 5 day";
  const by = "order by lasttime desc";

  const data = JSON.stringify([where, and, by]); 
  
  win = window.open("findip.php?data=" + data, "_blank");

  $(this).css({ background: "green", color: "white"});
});

// This is the AJAX function that gets the data from 'newdata'

function doAjax() {
  $.ajax({
    url: "showErrorLog.php",
    data: { page: "newdata", file: file },
    type: "post",
    success: function(data) {
      // Post data to #output
      $("#output").html(data);
    },
    error: function(err) {
      console.log("ERROR:", err);
    }
  });
}

// Set up a timer
// Check which log we are using and set time accordingly.

function waitForPeriod(dly) {
  const now = new Date();
  const minutes = now.getMinutes();
  const seconds = now.getSeconds();

  // Calculate the delay until the next quarter-hour
  const delay = (dly - (minutes % dly)) * 60 * 1000 - seconds * 1000;

  console.log("Waiting for", delay / 1000, "seconds until the next period.");

  setTimeout(function() {
    doAjax();
    waitForPeriod(dly);
  }, delay);
}

if(file == "/var/www/PHP_ERRORS_CLI.log") {
  waitForPeriod(15);
} else {
  waitForPeriod(1);
}
EOF;

[$top, $footer] = $S->getPageTopBottom();

//header("refresh:1800; url=showErrorLog.php?page=$page"); // 30 min

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
