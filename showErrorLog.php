<?php
// BLP 2023-02-25 - use new approach
// Show the PHP_ERRORS.log or PHP_ERRORS_CLI.log and allow it the be emptied.
// BLP 2025-02-09 - Reworked getting the lines.

$_site = require_once(getenv("SITELOADNAME"));
//$_site = require_once "/var/www/site-class/includes/autoload.php";

$_site->noGeo = true;

function parsedata($output) {
  $lines = "";
  $extra = "";
  
  // Loop through the lines of the file.

  foreach($output as $v) {
    // Don't display lines with showErrorLog.php or my ip address.
  
    if(preg_match("~page=/showErrorLog.php|ip=195.252.232.86, site=Bartonphillips~", $v) !== 0) {
      continue;
    }
    
    // If the line does not start with '[' then it is a continuation of the previous line.
    // Add it to $extra and continue.

    if(preg_match("~^\[~", $v) === 0) {
      $extra .= $v;
      continue;
    } else {
      // If there is data in $extra then add it to the table and reset $extra.

      if(!empty($extra)) {
        //echo "last: " . htmlspecialchars($tbl, ENT_QUOTES, 'UTF-8') . "<br>";
        $tbl .= "<td colspan='5'>$extra</td></tr>";
        $extra = '';
        $lines .= $tbl;
        // This then contines to parse the current $v.
      }
    }

    // Standard tracker or beacon with id, ip, site, page and rest.

    $err = preg_match("~\[(.*?) .*?\] (.*?): id=(.*?), ip=(.*?), site=(.*?), page=(.*?), (.*)$~", $v, $m);
    
    if($err === 0) { // The above patter does NOT match.
      // If there was no match then try both time, some item: and everything else.

      preg_match("~\[(.*?) .*?\] (.*?): (.*)$~", $v, $m);

      // Did we find the word 'exception' or 'error' in the third match (item)?
      // The \] .*? gathers the possible prefixes, like Pdo or Value. Then (?:?i:exception|error)
      // is a NON capture grouping that is caseless (?i:). I tried some of the other suggestion in
      // the PHP Subpatterns manual section but this is the only one that worked.

      if(preg_match("~\] .*?(?:?i:.*?exception|.*?error)~", $m[3]) === 0) { // (?:?i: works everything else seems to not work.
        // If we did not find 'error' then this is a tracker or beacon with no id or ip.

        $tbl = "<tr><td>{$m[1]}</td><td>{$m[2]}</td><td colspan='6'>{$m[3]}</td></tr>";
      } else {
        // This has 'error' with no id or ip.

        $tbl = "<tr><td>{$m[1]}</td><td>{$m[2]}</td>";
        $extra = ""; // this is all of the rest of this line.
        continue;
      }
    } else { // This is a tracker or beacon with id and ip.
      // $err was not zero and the pattern did match.
      
      $tbl = "<tr><td>{$m[1]}</td><td>{$m[2]}</td><td>{$m[3]}</td><td>{$m[4]}</td><td>{$m[5]}</td><td>{$m[6]}</td><td>{$m[7]}</td></tr>";
    }

    // Now fix the span for id and ip everywhere in the table.

    $tbl = preg_replace(["~(<td>|id=)(\d{7})~", "~(<td>|ip=)(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})~"] , ["$1<span class='id'>$2</span>","$1<span class='ip'>$2</span>"], $tbl);

    $lines .= $tbl;
  }

  // If there was not a line after the $extra data.
  // First remove spaces then check empty.
  
  if(!empty(trim($extra))) {
    // There is a valid line in $extra so that is the last thing in the error log.
    
    $lines .= $tbl . "<td colspan='5'>$extra</td></tr>"; // $tbl is the start of the line and $extra is the remainder.
  }

  return $lines;
}

$S = new SiteClass($_site);

// Ajax call from JavaScript

if($_POST['page'] == "newdata") {
  $output = file($_POST['file']);
  if($output) {
    $dataStr =  parsedata($output);
    $nodata = 'false';
  } else {
    $dataStr = "<h1>No Data in " . basename($_POST['file']) . "</h1>";
    $nodata = 'true';
  }

  $ret = json_encode([$dataStr, $nodata]);
  echo $ret;
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
  $dataStr = "<h1>No Data in $logname</h1>";
  $nodata = 'true';
} else {
  $dataStr = parsedata($output);
  $nodata = 'false';
}

$S->title = "Show Error Log";
$S->banner = "<h1>Show $logname</h1>";

$S->css =<<<EOF
#output { width: 100%; font-size: 18px; overflow-x: scroll; }
#table td { padding: 5px; }
#delete_button { border-radius: 5px; font-size: var(--blpFontSize); background: red; color: white; }
.ip, .id { cursor: pointer; };
EOF;

$S->noCounter = true;

$S->b_inlineScript = <<<EOF
let win = null;
const showErrorLogUrl = window.location.pathname;
console.log(showErrorLogUrl);
const del = "$del";
const file = "$page";
console.log("page=" + file);
console.log("Last Delete time: ", del);
let tbl;
let dataStr;
let hdr;
let nodata;

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
    url: showErrorLogUrl,
    data: { page: "newdata", file: file },
    type: "post",
    success: function(data) {
      // Post data to #output

      [data, nodata] = JSON.parse(data);

      if(nodata == "true") {
        tbl = data;
      } else {
        tbl = `
<table id="table" border="1">
<thead>
\${hdr}
</thead>
<tbody>
\${data}
</tbody>
</table>
`;
      }

      $("#output").html(tbl);
      if(nodata == 'true') {
        $("#table thead tr").remove();
      }
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
  hdr = "<th>Time</th><th>Item</th><th colspan='5'>Information<th></tr>";
} else {
  waitForPeriod(1);
  hdr = `
<tr><th>Time</th><th>Item</th><th>ID</th><th>IP</th><th>Site</th><th>Page</th><th>Rest</th></tr>
<tr><th>Time</th><th>Item</th><th colspan='5'>Rest</th></tr>
`;
}
console.log("nodata=$nodata");

nodata = "$nodata";

dataStr = `$dataStr`;

if(nodata == 'true') {
  tbl = dataStr;
} else {
  tbl = `
<table id="table" border="1">
<thead>
\${hdr}
</thead>
<tbody>
\${dataStr}
</tbody>
</table>
`;
}

$("#output").html(tbl);
if(nodata == "true") {
  $("#table thead tr").remove();
}
EOF;

[$top, $footer] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<form method='post'>
<input type="hidden" name="delname" value="$page">
<button id="delete_button" type='submit' name='delete' value="delete">Delete</button>
<div id="del-time"></div>
</form>
<hr>
<div id='output'>
</div>
<hr>
$footer
EOF;
