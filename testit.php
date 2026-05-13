<?php
$_site = require_once getenv("SITELOADNAME");

//******
// IMPORTANT Must use 'barton'. The mysitemap.jso is in 'bartonphillips' WE MUST CHANGE IT HERE
$_site->dbinfo->database = "barton"; // This is 'bartonphillips'in the mysitemap.json, make it barton.
//******

$_site->dbinfo->engine = "sqlite";

$db = new dbPdo($_site);

$site = "BartonphillipsOrg";
$ip = "195.252.232.86";
$agent = "TEST";

$query = "SELECT * FROM logagent where site='$site' and ip='$ip' and agent='$agent' ORDER BY lasttime DESC";
error_log("select SELECT=$query");
              
$n = $db->sql($query); //, [$site, $ip, $agent]);
    
error_log("select n=$n");
    
if(!$n) {
  error_log("select: select Error=$n");
  exit;
}

$data = [];

while($row = $db->fetchrow('assoc')) {
  $data[] = $row;
}
error_log("data: ". print_r($data, true));
echo json_encode([
                  'query' => $query,   // optional: remove in production
                  'count' => count($data),
                  'params'=> $params,
                  'data'  => $data,
                 ]);

$count = count($data);

echo "count=$count<br>";
