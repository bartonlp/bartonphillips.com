<?php
/*
 * Simple JSON API endpoint for SELECT queries only

 */

$_site = require_once getenv("SITELOADNAME");

//******
// IMPORTANT Must use 'barton'. The mysitemap.jso is in 'bartonphillips' WE MUST CHANGE IT HERE
$_site->dbinfo->database = "barton"; // This is 'bartonphillips'in the mysitemap.json, make it barton.
//******

//$_site->dbinfo->engine = "sqlite";

$engine = $_site->dbinfo->engine;

$db = new dbPdo($_site);

header('Content-Type: application/json');

// --- read JSON input ---
$input = json_decode(file_get_contents('php://input'), true);

// --- basic validation ---
if(!is_array($input)) {
  http_response_code(400);
  error_log("bartonphillips.com api.php: error=invalid JSON");
  exit;
}

// --- defaults ---
$table = $input['table'] ?? 'logagent';
$type = $input['type'];

// --- whitelist tables ---
$allowedTables = ['logagent'];

if(!in_array($table, $allowedTables)) {
  http_response_code(400);
  error_log("bartonphillips.com api.php: error=invalid table, not logagent");
  exit;
}

switch($type) {
  case 'select':
    $site = $input['site'];
    $ip = $input['ip'];
    $agent = $input['agent'];

    $where = "where site=? and ip=? and agent=?";
    
    $query = "SELECT * FROM logagent $where ORDER BY lasttime DESC";
              
    $n = $db->sql($query, [$site, $ip, $agent]);
    
    if(!$n) {
      error_log("select: select Error=$n");
      exit;
    }

    $data = [];

    while($row = $db->fetchrow('assoc')) {
      $data[] = $row;
    }

    echo json_encode([
                      'query' => $query,   // optional: remove in production
                      'count' => count($data),
                      'params'=> $params,
                      'data'  => $data,
                     ]);

    $count = count($data);

    break;
  case 'insert':
    $site  = $input['site']  ?? '';
    $ip    = $input['ip']    ?? '';
    $agent = $input['agent'] ?? '';
      
    $params = [$site,
               $ip,
               $agent,
              ];
    
    if(!$site || !$ip || !$agent) { 
      http_response_code(400);
      error_log("bartonphillips.com api.php:error=Error missing fields. site or ip or agent");
      exit;
    }

    switch($engine) {
      case "mysql":
        $query = "INSERT INTO logagent (site, ip, agent, count, lasttime)
VALUES (?, ?, ?, 1, NOW())
ON DUPLICATE KEY UPDATE
count = count + 1,
lasttime = NOW()";
        break;
      case "sqlite":
        $query = "CREATE TABLE IF NOT EXISTS logagent (`site` varchar(25) NOT NULL DEFAULT '',
`ip` varchar(40) NOT NULL DEFAULT '',
`agent` varchar(254) NOT NULL,
`count` int DEFAULT NULL,
`created` text NOT NULL DEFAULT CURRENT_TIMESTAMP,
`lasttime` text DEFAULT NULL,
PRIMARY KEY (`site`,`ip`,`agent`))";

        $n = $db->sql($query);
        if(!$n) {
          error_log("bartonphillips.com api.php: create error");
          exit;
        }
        
        $query = "insert into logagent (site, ip, agent, count, lasttime)
values (?, ?, ?, 1, datetime('now','localtime'))
on conflict(site, ip, agent)
do update set
count = count + 1,
lasttime = datetime('now','localtime')";
        break;
      default:
        error_log("SWITCH: engine=$engine, type=$type");
        exit;
    }

    $n = $db->sql($query, $params);

    if(!$n) {
      error_log("bartonphillips.com api.php: Error insert=$n");
      exit;
    }
    
    echo json_encode(['query' => $query, 'params' => [$site, $ip, $agent], 'num' => $n,]);
    break;
  default:
    error_log("SWITCH ERROR: type=$type");
    exit;
}

//error_log("insert/select: type=$type, n=$n, count=$count, query=$query, params=" . print_r($params, true) .
//          ", data=". print_r($data, true) ."\n");


