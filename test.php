<?php
$_site = require_once getenv("SITELOADNAME");
$_site->dbinfo->database = "barton";
$_site->dbinfo->engine = "sqlite";
//vardump("site", $_site);

$S = new dbPdo($_site);
//echo "test<br>";

$site = $S->siteName;
$ip = $S->ip;
$agent = $S->agent;

try {
  $S->sql("select * from logagent where lasttime > datetime('now', '-1 day') order by lasttime");
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
