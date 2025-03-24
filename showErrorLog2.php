<?php
// BLP 2023-02-25 - use new approach
// Show the PHP_ERRORS.log or PHP_ERRORS_CLI.log and allow it the be emptied.
// BLP 2025-02-09 - Reworked getting the lines.
// BLP 2025-02-20 - More rework. Add more comments and add a version

define('SHOWERRORLOG_VERSION', 'showErrorLog-1.0.2'); // BLP 2025-02-25 - add 'span' for all ip/id

$_site = require_once(getenv("SITELOADNAME"));
//$_site = require_once "/var/www/site-class/includes/autoload.php";
ErrorClass::setDevelopment(true);
$_site->noGeo = true;

// preg_replace_callback function

function fixdiff($m) {
  $seconds = (int)$m[2];
  $hours = floor($seconds / 3600);
  $minutes = floor(($seconds % 3600) / 60);
  $seconds = $seconds % 60;
  $time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

  return $m[1].$time;
}

// parsedata in $output.

function parsedata($output) {
  $lines = "";
  $extra = "";
  
  // Loop through the lines of the file.

  foreach($output as $v) {
    // Don't display lines with showErrorLog.php or my ip address.
  
    if(preg_match("~(?:page=/showErrorLog.php|ip=195.252.232.86, site=Bartonphillips)~", $v) !== 0) {
      continue;
    }

    // Look for difftime and convert seconds into h:m:s
    
    $v = preg_replace_callback("~(difftime=)(\d*\.?\d*)~", "fixdiff", $v);

    // If the line does not start with '[' then it is a continuation of the previous line.
    // Add it to $extra and continue.

    if(preg_match("~^\[~", $v) === 0) {
      // Add span for id/ip

      $extra .= htmlentities($v);
      continue;
    } else {
      // If there is data in $extra then add it to the table and reset $extra.

      if(!empty($extra)) {
        $tbl .= "<td colspan='5'>$extra</td></tr>";
        $tbl = preg_replace(["~(<td.*?>|id=)(\d{7})~i", "~(<td.*?>|ip=)(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})~i"] , ["$1<span class='id'>$2</span>","$1<span class='ip'>$2</span>"], $tbl);

        $extra = '';
        $lines .= $tbl; // Update lines here.
        // This then contines to parse the current $v.
      }
    }

    // Standard tracker or beacon with id, ip, site, page and rest.

    $err = preg_match("~\[(.*? .*?) .*?\] (.*?): (id=.*?), (ip=.*?), (site=.*?), (page=.*?), (.*)$~", $v, $m);
    
    if($err === 0) { // The above patter does NOT match.
      // If there was no match then 'some item:' and everything else.

      preg_match("~\[(.*? .*?) .*?\] (.*?): (.*)$~", $v, $m);

      // Now check if the word 'exception' or 'error' in the noncapture group.
      // The \] .*? gathers the possible prefixes, like Pdo or Value. Then (?:?i:exception|error)
      // is a NON capture grouping that is caseless (?i:). I tried some of the other suggestion in
      // the PHP Subpatterns manual section but this is the only one that worked.

      if(preg_match("~\] (?:?i:.*?exception|.*?error)~", $m[2]) === 0) { 
        // If we did not find 'exception' or 'error' then this is a tracker, beacon or something else with no id or ip.

        // BLP 2025-02-24 - Add span for id or ip.
        
        $tbl = preg_replace(["~(<td.*?>|id=)(\d{7})~i", "~(<td.*?>|ip=)(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})~i"] , ["$1<span class='id'>$2</span>","$1<span class='ip'>$2</span>"], $m[3]);        
        error_log("showErrorLog: span group, $tbl, line=". __LINE__);
        $tbl = "<tr><td>{$m[1]}</td><td>{$m[2]}</td><td colspan='5'>{$tbl}</td></tr>";
      } else {
        // This has 'exception' or 'error' with no id or ip.

        $tbl = "<tr><td>{$m[1]}</td><td>{$m[2]}</td>";
        $extra = htmlentities($m[3]) . " ";
        continue;
      }
    } else { // This is a tracker or beacon with id and ip.
      // $err was not zero and the pattern did match.
      
      $tbl = "<tr><td>{$m[1]}</td><td>{$m[2]}</td><td>{$m[3]}</td><td>{$m[4]}</td><td>{$m[5]}</td><td>{$m[6]}</td><td>{$m[7]}</td></tr>";
    }

    // Now add a span for id and ip everywhere in the $tbl.

    $tbl = preg_replace(["~(<td>|id=)(\d{7})~", "~(<td>|ip=)(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})~"] , ["$1<span class='id'>$2</span>","$1<span class='ip'>$2</span>"], $tbl);

    // Finally add $tbl to the accumulator $lines
    
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
    if(empty($dataStr)) {
      $dataStr = "<h1>No Data in " . basename($_POST['file']) . "</h1>";
      $nodata = 'true';
    } else {
      $nodata = 'false';
    }
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

  // I need to call the GET to keep from having $_POST still have the delete. $delname is the name
  // of the error log to use.

  header("Location: /showErrorLog2.php?page=$delname");
  exit();
}

// If '$page' is set get the page data, else get either PHP_ERRORS.log or PHP_ERRORS_CLI.log
// NOTE if we drop through from the 'form' post page will still be set.

if($page = $_GET["page"]) {
  $output = file($page);
  $logname = basename($page);

  if(!$output) { 
    $dataStr = "<h1>No Data in $logname</h1>";
    $nodata = 'true';
  } else {
    $dataStr = parsedata($output);
    if(empty($dataStr)) {
      $dataStr = "<h1>No Data in $logname</h1>";
      $nodata = 'true';
    } else {
      $nodata = 'false';
    }
  }
} else {
  $S->title = "Get Page";
  $S->banner = "<h1>Select Error Log</h1>";
  [$top, $footer] = $S->getPageTopBottom();
  
  echo <<<EOF
$top
<hr>
<a href="showErrorLog2.php?page=/var/www/PHP_ERRORS.log">PHP_ERRORS.log</a><br>
<a href="showErrorLog2.php?page=/var/www/PHP_ERRORS_CLI.log">PHP_ERRORS_CLI.log</a><br>
<hr>
$footer
EOF;
  exit();
}

$S->title = "Show Error Log";
$S->banner = "<h1>Show $logname</h1>";
$S->noCounter = true; // Don't show the counter.

$S->css =<<<EOF
#output { width: 100%; font-size: 18px; overflow-x: scroll; }
#table td { padding: 5px; }
#table td:nth-of-type(7) { word-break: break-word; }
#delete_button { border-radius: 5px; font-size: var(--blpFontSize); background: red; color: white; }
.ip, .id { cursor: pointer; };
EOF;

$S->b_script =<<<EOF
<script>
const del = "$del";
const file = "$page";
const dataStr = `$dataStr`;
</script>
<script src='showErrorLog2.js'></script>
EOF;

$S->msg = "Version: ". SHOWERRORLOG_VERSION . "<br>";

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
<div id='output'></div>
<hr>
$footer
EOF;
