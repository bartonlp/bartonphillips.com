<?php
// This is a list of adminstuff. These are the links to all of my administration stuff
// This should not be run directly from the browser.

$ip = $_SERVER['REMOTE_ADDR'];
if($ip == "192.241.1332.229") $ip .= ":SERVER";
$agent = $_SERVER['HTTP_USER_AGENT'];
if(empty($agent)) $agent = "NO_AGENT";
$self = htmlentities($_SERVER['PHP_SELF']);

if(!class_exists("Database")) {
  header("location: https://bartonlp.com/otherpages/NotAuthorized.php?site=Bartonphillips&page=$self, ip=$ip, agent=$agent");
}

return <<<EOF
<!-- Admin text for all sights -->
<section id='adminstuff'>
<h2>Admin</h2>
<ul>
<li><a target="_blank" href="https://bartonlp.com/otherpages/webstats.php?blp=8653&site=$S->siteDomain">Webstats</a></li>
<li><a target="_blank" href="getcookie.php?blp=8653">Get/Reset Cookie</a></li>
<li><a target="_blank" href="https://bnai-sholem.com/rjwebbuilder">B'nai Sholem ADMIN</a></li>
<li><a target="_blank" href="https://bartonphillips.org:8000/">RPI on 8000</a></li>
<li><a target="_blank" href="/stocks/stock.getalpha.php">Alphavantage</a></li>
<li><a target="_blank" href="/findip.php">Find in Tracker by IP</a></li>
<li><a target="_blank" href="/myports.php">Port Numbers</a></li>
<li><a target="_blank" href="/showErrorLog.php">Show PHP_ERROR.log</a></li>
<li><a target="_blank" href="/showmodsec.php">Show Mod Sec log</a></li>
<li><a target="_blank" href="/examples/">examples</a></li>
<li><a target="_blank" href="/examples.js/">examples.js</a></li>
<li><a target="_blank" href="/test_examples/">examples just for testing</a></li>
</ul>
</section>
EOF;

