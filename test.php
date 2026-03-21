<?php
$_site = require_once getenv("SITELOADNAME");
//$_site->dbinfo->engine = "sqlite";
//vardump("site", $_site);

$S = new SiteClass($_site);
//echo "test<br>";

try {
  $S->sql("select * from $S->masterdb.logagent where lasttime > now() -interval 10 minute order by lasttime");
  while($tbl = $S->fetchrow("num")) {
    vardump("tbl", $tbl);
  }
} catch(\Excetion $e) {
  throw $e;
}
/*
[$top, $bottom] = $S->getPageTopBottom();
echo <<<EOF
$top
$bottom
EOF;
*/