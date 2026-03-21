<?php
// This is a link in adminsites.php that says 'Show PHP Error.log'

$_site = require_once getenv("SITELOADNAME");
$_site->noTrack = true;
ErrorClass::setDevelopment(true);

function shouldSkipLine($line): bool {
  return preg_match("~(?:page=/showErrorLog.php|ip=195\.252\.232\.86, site=Bartonphillips)~", $line);
}

function isContinuationLine($line): bool {
  return !preg_match("~^\[~", $line);
}

function highlightIdIp($str): string {
  return preg_replace([
    "~(<td.*?>|id=)'?(\d{7})'?~i",
    "~(<td.*?>|ip=)'?(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})'?~i"
  ], [
    "$1<span class='id'>$2</span>",
    "$1<span class='ip'>$2</span>"
  ], $str);
}

function appendExtraLine(string $base, string $extra): string {
  return $base . "<td colspan='5'>" . highlightIdIp($extra) . "</td></tr>";
}

function parseLogLine($line): array {
  $line = preg_replace_callback("~(difftime=)(\d*\.?\d*)~", "fixdiff", $line);

  // We want the 'date time' but not the text after it.
  // There are two types of line.
  // A line with the date-time, expanation: id, ip, site, page and additional information.
  // A line with just date-time, expanation; and additional stuff

  // First type 'multi'
  
  if(preg_match("~\[(.*? .*?) .*?\] (.*?): (id=.*?), (ip=.*?), (site=.*?), (page=.*?), (.*)$~", $line, $m)) {
    return ['type' => 'multi', 'fields' => $m];
  }

  // Second type 'exception' or 'single'
  
  if(preg_match("~\[(.*? \d{2}:\d{2}:\d{2}) ?.*?\] (.*?): (.*)$~", $line, $m)) {
    if(preg_match("~\] (?i:.*?exception|.*?error)~", $m[2])) {
      return ['type' => 'exception', 'timestamp' => $m[1], 'label' => $m[2], 'message' => $m[3]];
    } else {
      return ['type' => 'single', 'timestamp' => $m[1], 'label' => $m[2], 'message' => $m[3]];
    }
  }

  return ['type' => 'unknown', 'raw' => $line];
}

function formatMultiFieldRow(array $fields): string {
  // $fields comes from: preg_match("~\[(.*? .*?) .*?\] (.*?): (id=.*?), (ip=.*?), (site=.*?), (page=.*?), (.*)$~", $line, $m);
  // So: $fields[1]=timestamp, $fields[2]=label, $fields[3]=id=..., $fields[4]=ip=..., etc.

  return "<tr>" .
           "<td>{$fields[1]}</td>" .
           "<td>{$fields[2]}</td>" .
           "<td>{$fields[3]}</td>" .
           "<td>{$fields[4]}</td>" .
           "<td>{$fields[5]}</td>" .
           "<td>{$fields[6]}</td>" .
           "<td>{$fields[7]}</td>" .
         "</tr>";
}

function formatSingleFieldRow(string $timestamp, string $label, string $message): string {
  return "<tr>" .
           "<td>$timestamp</td>" .
           "<td>$label</td>" .
           "<td colspan='5'>$message</td>" .
         "</tr>";
}

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
  $lines = '';
  $extra = '';

  foreach($output as $line) {
    if(shouldSkipLine($line)) continue;

    if(isContinuationLine($line)) {
      $extra .= highlightIdIp($line);
      continue;
    }

    if(!empty($extra)) {
      $lines .= appendExtraLine($tbl ?? '', $extra);
      $extra = '';
    }

    $parsed = parseLogLine($line);

    if($parsed['type'] === 'multi') {
      $tbl = formatMultiFieldRow($parsed['fields']);
    } elseif($parsed['type'] === 'single') {
      $tbl = formatSingleFieldRow($parsed['timestamp'], $parsed['label'], $parsed['message']);
    } elseif($parsed['type'] === 'exception') {
      $extra = $parsed['message'];
      $tbl = "<tr><td>{$parsed['timestamp']}</td><td>{$parsed['label']}</td>";
      continue;
    }
    
    $tbl= highlightIdIp($tbl);
    $colClasses = ['time', 'label', 'id', 'ip', 'site', 'page', 'message'];
    $tbl = addClassesToTableColumns($tbl, $colClasses, 'tbl-');
    $lines .= $tbl;
  }

  if(!empty(trim($extra))) {
    $lines .= appendExtraLine($tbl ?? '', $extra);
  }

  return $lines;
}

// GET action

if(isset($_GET['action'])) {
  $_site->noTrack = $_site->noGeo = true;
  $S = new Database($_site);
  
  if($_GET['action'] === 'show_logs') {
    $logFile = $_GET['logFile'] ?? '/var/www/PHP_ERRORS.log';

    if($logFile === "TABLE") {
      $sql = "select time, id, ip, site, page, event, count
          from $S->masterdb.interaction where time>=current_date() order by lasttime";

      $T = new dbTables($S);
      $dataStr = $T->maketable($sql, ['attr' => ['id'=>'interaction', 'border'=>'1']], true)[0];
      $ret = json_encode([$dataStr, false]);
      echo $ret;
      exit();
    } else {
      $output = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
    
    if($output) {
      $dataStr =  parsedata($output, $getInteractionTable);

      if(empty($dataStr)) {
        $dataStr = "<h1>No Data in " . basename($logFile) . "</h1>";
        $nodata = 'true';
      } else {
        $nodata = 'false';

        $dataStr = "<div id='log-content'><table id='table' border='1'>$dataStr</table></div>";
      }
    } else {
      $dataStr = "<h1>No Data in " . basename($logFile) . "</h1>";
      $nodata = 'true';
    }

    $ret = json_encode([$dataStr, $nodata]);
    echo $ret;
    exit();
  } elseif($_GET['action'] === 'find_ip') {
    // This is the Ajax for 'action: "find_ip"' in logs.js '$("body").on("click",".ip,.id",
    // function(e)' and not a control key.
    
    $data = $_GET['data'];
    error_log("logs find_ip: data=$data");
    echo "OK";
    exit();
  }
  // Anything that isn't show_logs or find_ip just goes to the render for GET.
}

// Ajax call to post from logs.js.

if($_POST['delete']) {
  $_site->noTrack = $_site->noGeo = true;
  $S = new Database($_site);

  $delname = $_POST['delname'];

  if($delname === "TABLE") {
    $S->sql("delete from $S->masterdb.interaction where time>=current_date() order by lasttime");
  } else {
    if(file_put_contents($delname, '') === false) {
      // This vardump will be sent back to the JavaScript.
    
      vardump("error", error_get_last());
      exit();
    }
  }
  
  $del = date("Y-m-d H:i:s");
  echo $del;
  exit();
}

$_site->isMeFalse = true;
$S = new SiteClass($_site);

$S->title = "Log Viewer";
$S->banner = "<h1>$S->title</h1>";

// CSS

$S->css =<<<EOF
#scroll-wrapper {
  /*width: 100%;*/
  width: 98vw;
  box-sizing: border-box;
}

#log-content {
  width: 100%;
  max-width: 98vw;
  overflow: auto;
}

#table {
  width: 100%;
  background-color: hsla(144, 100%, 95%, 0.6);
  transition: background-color 0.3s ease;
}

/* Hover effect on table */
#table:hover {
  background-color: hsla(144, 100%, 93%, 0.8);
}

@media (max-width: 1600px) {
  #scroll-wrapper {
    max-width: 98vw;
    margin: 1vh auto;
    padding: 5px;
    border: 4px solid orange;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 80vh;
    box-sizing: border-box;
  }

  #log-content {
    max-height: 100%;
  }

  #table {
    width: 1600px;
    border: 1px solid black;
  }

  body.lock-scroll {
    overflow: hidden;
  }
}

#table td {
  word-break: break-word;
  white-space: normal;
}

#table td, #interaction td { padding: 5px; }
.tbl-time { width: 170px; }
.tbl-label { width: 190px; }
.tbl-id { width: 190px; }
.tbl-ip { width: 220px; }
.tbl-site { width: 170px; }
.tbl-page { width: 250px; }
.tbl-botAsBits { width: 300px; }
.tbl-finger { overflow-x: auto; max-width: 200px; white-space: pre; }

/**/
#interaction { width: 100%; max-width: 100%; }
.inter-page, .inter-event { overflow-x: auto; max-width: 350px; white-space: pre; }
.inter-count { width: 50px; }
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
<div>Select Log File:</div>
<select id="log-selector" onchange="switchLog()">
  <option value="/var/www/PHP_ERRORS.log">PHP_ERRORS.log</option>
  <option value="/var/www/PHP_ERRORS_CLI.log">PHP_ERRORS_CLI.log</option>
  <option value="/var/www/data/info.log">info.log</option>
  <option value="TABLE">interacton table</option>
</select>
<button id="select_button" onclick="switchLog()">Switch Log</button>
<div id="log_name"></div>
<button id="del_button" onclick="deleteLog()">Delete Log</button>
<div id="del-time"></div>
<!-- Auto-Refreshing Log Display -->
<div id="scroll-wrapper">Loading logs...</div>
<hr>
$bottom
EOF;
