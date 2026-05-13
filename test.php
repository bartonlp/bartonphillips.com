<?php
$_site = require_once getenv("SITELOADNAME");
$_site->dbinfo->database = "barton";
$_site->dbinfo->engine = "sqlite";
//vardump("site", $_site);

$S = new dbPdo($_site);
$site = $S->siteName;
$ip = $S->ip;
$agent = $S->agent;

//$site = "Rpi";
//$ip = "192.168.4.20";
//$agent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36";

try {
  $S->sql("select * from logagent where site=? and ip=? and agent=? and
lasttime > datetime('now', '-1 day') order by lasttime",
         [$site, $ip, $agent]);
  
  while($tbl = $S->fetchrow("num")) {
    vardump("tbl", $tbl);
  }
} catch(\Excetion $e) {
  throw $e;
}

try {
$n = $S->sql(
  "insert into logagent (site, ip, agent, count, lasttime)
   values (?, ?, ?, 1, datetime('now','localtime'))
   on conflict(site, ip, agent)
   do update set
     count = count + 1,
     lasttime = datetime('now','localtime')",
  [$site, $ip, $agent]
);

  vardump("n", $n);
} catch(\Exception $e) {
  throw $e;
}
