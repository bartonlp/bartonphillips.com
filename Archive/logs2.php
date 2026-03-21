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

  if (preg_match("~\[(.*?) .*?\] (.*?): (id=.*?), (ip=.*?), (site=.*?), (page=.*?), (.*)$~", $line, $m)) {
    return ['type' => 'multi', 'fields' => $m];
  }

  if (preg_match("~\[(.*?) .*?\] (.*?): (.*)$~", $line, $m)) {
    if (preg_match("~\] (?i:.*?exception|.*?error)~", $m[2])) {
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

function getInteractionTable() {
  global $S;
  function interaction_callback(&$desc) {
    $desc = preg_replace("~^(.*?)<td>(\d{7})</td><td>(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})</td>(.*)~",
                         "$1<td class='id'>$2</td><td class='ip'>$3</td>$4", $desc);
    $colClasses = ['time', 'id', 'ip', 'site', 'page', 'event', 'count'];

    $desc = addClassesToTableColumns($desc, $colClasses, 'inter-');
  }

  $sql = "select time, id, ip, site, page, event, count from $S->masterdb.interaction where time>=current_date() order by lasttime";
  $T = new dbTables($S);
  $lines = $T->maketable($sql, ['callback2'=>'interaction_callback', 'attr' => ['id'=>'interaction', 'border'=>'1']])[0];
  //$lines = $T->makeresultrows($sql, "<tr><td>*</td>", ['return'=>false, 'callback2'=>'interaction_callback']);

  return $lines;
}

// parsedata in $output.

function parsedata($output) {
  if($output === "TABLE") return getInteractionTable();

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
  $_site->noTrack = true;
  $S = new Database($_site);

  if($_GET['action'] === 'show_logs') {
    $logFile = $_GET['logFile'] ?? '/var/www/PHP_ERRORS.log';
    if($logFile === "TABLE") {
      $output = "TABLE";
    } else {
      $output = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
    
    if($output) {
      $dataStr =  parsedata($output);

      if(empty($dataStr)) {
        $dataStr = "<h1>No Data in " . basename($logFile) . "</h1>";
        $nodata = 'true';
      } else {
        $nodata = 'false';

        $dataStr = "<table id='table' border='1'>$dataStr</table>";
      }
    } else {
      $dataStr = "<h1>No Data in " . basename($logFile) . "</h1>";
      $nodata = 'true';
    }

    $ret = json_encode([$dataStr, $nodata]);
    echo $ret;
    exit();
  } elseif ($_GET['action'] === 'find_ip') {
    // This is the Ajax for 'action: "find_ip"' in logs.js '$("body").on("click",".ip,.id",
    // function(e)' and not a control key.
    
    $data = $_GET['data'];
    echo "OK";
    exit();
  }
  // Anything that isn't show_logs or find_ip just goes to the render for GET.
}

// Ajax call to post from logs.js.

if($_POST['delete']) {
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

$S = new SiteClass($_site);

$S->title = "Log Viewer";
$S->banner = "<h1>$S->title</h1>";
$S->css =<<<EOF
#log-content {
  width: 100%;
  max-width: 98vw;
}
#table {
  width: 100%;
}
@media (max-width: 1600px) {
  #log-content {
    overflow: auto;
    max-height: 80vh;
  }

  #table {
    width: 1600px;
  }
}
#table td {
  word-break: break-word;
  white-space: normal;
}
#table td, #interaction td { padding: 5px; }
#table td:nth-of-type(1) { width: 190px; }
#table td:nth-of-type(2) { width: 220px; }
#table td:nth-of-type(3) { width: 170px; }
#table td:nth-of-type(4) { width: 250px; }
#table td:nth-of-type(5) { width: 300px; }
#table td:nth-of-type(6) { overflow-x: auto; max-width: 200px; white-space: pre; }
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
<label>Select Log File:</label>
<select id="log-selector" onchange="switchLog()">
  <option value="/var/www/PHP_ERRORS.log">PHP_ERRORS.log</option>
  <option value="/var/www/PHP_ERRORS_CLI.log">PHP_ERRORS_CLI.log</option>
  <option value="TABLE">interacton table</option>
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
