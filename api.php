<?php
/*
 * Simple JSON API endpoint for SELECT queries only

 */

$_site = require_once getenv("SITELOADNAME");
$_site->dbinfo->database = "barton"; // This is 'bartonphillips'in the mysitemap.json, make it barton.
//$engine = $_site->dbinfo->engine = "sqlite";
$engine = $_site->dbinfo->engine = "mysql";
$db = new dbPdo($_site);

header('Content-Type: application/json');

// --- read JSON input ---
$input = json_decode(file_get_contents('php://input'), true);

// --- basic validation ---
if(!is_array($input)) {
  http_response_code(400);
  echo json_encode(['error'=>'invalid JSON']);
  exit;
}

// --- defaults ---
$table = $input['table'] ?? 'logagent';
$type = $input['type'];

// --- whitelist tables ---
$allowedTables = ['logagent'];

if(!in_array($table, $allowedTables)) {
  http_response_code(400);
  echo json_encode(['error'=>'invalid table']);
  exit;
}

switch($type) {
  case 'select':
    // --- build WHERE clause safely ---
    $where = '';
    $params = [];

    //vardump("input", $input);
    
    if(!empty($input['ip'])) {
      $where = "WHERE ip = ?";
      $params[] = $input['ip'];
    }

    if(!empty($input['agent'])) {
      $where .=  ' and agent = ?';
      $params[] = $input['agent'];
    }
    
    $query = "SELECT * FROM $table $where ORDER BY lasttime DESC"; 

    $n = $db->sql($query, $params);
    
    if(!$n) {
      //echo json_encode(['status' => "select Error=$n"]);
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
    // --- required fields ---
    $site  = $input['site']  ?? '';
    $ip    = $input['ip']    ?? '';
    $agent = $input['agent']; // agent may be empty (null).
      
    $params = [];
    $params = [$site,
               $ip,
               $agent,
              ];
    
    if(!$site || !$ip) { // if agent is not found ignore.
      http_response_code(400);
      echo json_encode(['error'=>'Error missing fields']);
      exit;
    }

    switch($engine) {
      case "mysql":
        // --- insert with upsert ---
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

        error_log("api.php: Create the file n=$n");

        $query = "insert into logagent (site, ip, agent, count, lasttime)
values (?, ?, ?, 1, datetime('now','localtime'))
on conflict(site, ip, agent)
do update set
count = count + 1,
lasttime = datetime('now','localtime')";
        break;
      default:
        echo json_encode(['error'=>"Error Switch: $type<br>"]);
        exit;
    }

    $n = $db->sql($query, $params);

    if(!$n) {
      echo json_encode(['error' => 'Error insert=0']);
      exit;
    }
    
    echo json_encode(['query' => $query, 'params' => [$site, $ip, $agent], 'num' => $n,]);
    
    break;
}

file_put_contents('/tmp/api_hits.log',
  date('c') . ", type=$type, n=$n, count=$count, query=$query, params=" . print_r($params, true) . ", data=". print_r($data, true) ."\n",
  FILE_APPEND
                 );
