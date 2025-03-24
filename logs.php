<?php
$_site = require_once getenv("SITELOADNAME");
ErrorClass::setDevelopment(true);
$S = new SiteClass($_site);

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

// GET action

if (isset($_GET['action'])) {
  if ($_GET['action'] === 'show_logs') {
    $logFile = $_GET['logFile'] ?? '/var/www/PHP_ERRORS.log';
    $output = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if($output) {
      $dataStr =  parsedata($output);
      if(empty($dataStr)) {
        $dataStr = "<h1>No Data in " . basename($logFile) . "</h1>";
        $nodata = 'true';
      } else {
        $nodata = 'false';
      }
    } else {
      $dataStr = "<h1>No Data in " . basename($logFile) . "</h1>";
      $nodata = 'true';
    }

    $ret = json_encode([$dataStr, $nodata]);
    
    echo $ret;
    exit();
  } elseif ($_GET['action'] === 'find_ip') {
    $data = $_GET['data'];
    echo "OK";
    exit();
  }
}

// Ajax call to post from logs.js.

if ($_POST['delete']) {
  $delname = $_POST['delname'];
  //error_log("logs.php: delname=$delname");

  if (file_put_contents($delname, '') === false) {
    vardump("error", error_get_last());
    exit();
  }

  $del = date("Y-m-d H:i:s");
  echo $del;
  exit();
}

$S->title = "Log Viewer";
$S->banner = "<h1>$S->title</h1>";
$S->css =<<<EOF
#table td { padding: 5px; }
#table td:nth-of-type(7) { word-break: break-word; }
.id, .ip { cursor: pointer; }
#del-time { font-size: var(--blpFontSize); font-weight: bold; }
#log_name { font-size: var(--blpFontSize); font-weight: bold; }
#del_button { border-radius: 4px; font-size: var(--blpFontSize); background: red; color: white; }
#select_button { border-radius: 4px; font-size: var(--blpFontSize); background: white; color: black; }
EOF;
             
$S->b_script =<<<EOF
<script src='logs.js'></script>
EOF;

[$top, $bottom] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<!-- Log Selection -->
<label>Select Log File:</label>
<select id="log-selector">
  <option value="/var/www/PHP_ERRORS.log">PHP_ERRORS.log</option>
  <option value="/var/www/PHP_ERRORS_CLI.log">PHP_ERRORS_CLI.log</option>
</select>
<button id="select_button" onclick="switchLog()">Switch Log</button>
<div id="log_name"></div>
<button id="del_button" onclick="deleteLog()">Delete Log</button>
<div id="del-time"></div>
<!-- Auto-Refreshing Log Display -->
<div id="log-content">Loading logs...</div>
<hr>
$bottom
EOF;
