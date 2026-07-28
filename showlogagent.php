<?php
// Display logagent table for today

$_site = require_once getenv("SITELOADNAME");
//vardump("site", $_site);
//$_site->doSiteClass = false;
//$_site->webServer = false;
//$_site->dbinfo->engine = 'sqlite';
$_site->dbinfo->database = 'barton';
//$_site->isMeFalse = true;
ErrorClass::setDevelopment(true);

$S = new SiteClass($_site);
//vardump("S", $S);

$S->title = "Display logagent";
$S->banner = "<h1>Display <i>logagent</i> table using $S->siteName</h1>";
$phpVersion = PHP_VERSION;

$sql = "select site, ip, count, agent from logagent where site='$S->siteName' and ip='$S->ip' and agent='$S->agent' ".
       "order by lasttime desc limit 1";

if($S->webServer === true) {
  $S->msg1 = "webServer and WebServerApi. From DigitalOcean server";
  $result = ($S->WebServerApi($sql)[0]);

  $result->count++;
  $server = [];
  foreach($result as $v) {
    $server[] = $v;
  }

  $serverStr = "<h3>This is <i>logagent</i> from DigitalOcean server using WebServerApi</h3>" .
               "<p>" . implode(", ", $server) .
               "<br><b>Count={$result->count}</p>" ;
} else {
  $doSiteClass = $S->doSiteClass === true ? ", doSiteClass" : null;
  $S->msg = "PhpVersion: $phpVersion<br>"; // This is the local phpVersion on the rpi.
  $S->msg1 = "{$S->__toString()} {$S->getVersion()}, engine={$S->dbinfo->engine}".
             "{$doSiteClass}<br>";

  // This is below the if()
  
  $result = $S->sql($sql);

  if(!$result) {
    $day = "Error: result is zero<br>";
  } else {
    [$site, $ip, $count, $agent] = $S->fetchrow('num');
    $count++;
    $mysqlcnt = "<p><b>logagent count={$count}</b> ($site, $ip, {$agent})</p>";
  }

  if($S->dbinfo->engine === "mysql" && $S->doSiteClass) {
    $S->sql("select count, realcnt from $S->masterdb.counter where site='$S->siteName' and filename='$S->self'
order by lasttime desc limit 1");

    [$count, $realcnt] = $S->fetchrow('num');

    $mycount = "When <b>counter</b>, <i>mysql</i> has everyone added ($count),
but only non-<i>ME</i> visits are couned ($realcnt).";
  }

  if($S->dbinfo->engine === "sqlite" && $S->doSiteClass === true) {
    $mycount .= "<br>When <b>sqlite</b> and <b>doSiteClass</b> can not be used in <i>counter</i>.";
  }
}

$sql = "select * from $S->masterdb.logagent where site='$S->siteName' order by lasttime desc limit 1";
$result = $S->WebServerApi($sql);
$info = print_r($result, true);

[$top, $bottom] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
<p style="color: red">Using: $S->msg1</p>
$serverStr
$mycount
$mysqlcnt
<hr>
$bottom
EOF;

