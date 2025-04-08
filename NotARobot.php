<?php
/*
CREATE TABLE `bots3` (
  `ip` varchar(50) NOT NULL COMMENT 'big enough to handle IP6',
  `agent` text NOT NULL COMMENT 'big enough to handle anything',
  `count` int DEFAULT '1' COMMENT 'the number of time this has been updated',
  `robots` int DEFAULT NULL COMMENT 'bit mapped values as above see defines.php',
  `site` int DEFAULT NULL COMMENT 'bitmasked values of sites see defines.php',
  `page` varchar(255) DEFAULT NULL COMMENT 'the page on my site',
  `created` datetime DEFAULT NULL COMMENT 'when record created',
  `lasttime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'auto, the lasttime this was updated',
  UNIQUE KEY `ip_agent_page` (`ip`,`agent`(255),`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

bots3deleted has the exact same signature.
*/

$_site = require_once getenv("SITELOADNAME");
$S = new SiteClass($_site);

// This must come from my domain

$ref = $_SERVER['HTTP_REFERER'];

if(!str_contains($ref, 'bartonphillips.com')) {
  header("Location: https://bartonlp.com/otherpages/NotAuthorized.php?ip=$S->ip&site=$S->site");
  exit;
}

$S->banner = "<h1>Not A Robot?</h1>";

try {
  $n = $S->sql("insert into $S->masterdb.bots3deleted (ip, agent, count, robots, site, page, created, reason) ".
               "select ip, agent, count, robots, site, page, created, 'NotARobot'".
               "from $S->masterdb.bots3 where ip='$S->ip' and agent='$S->agent' and page='' ".
               "on duplicate key update reason='NotARobot2', count=bots3deleted.count+1");

  if(!$n) {
    error_log("NotARobot.php: insert into bots3deleted failed n=$n, ip=$S->ip, agent=$S->agent, page='', line=". __LINE__);
    $S->sql("delete from $S->masterdb.bots3 where ip='$S->ip' and agent='$S->agent'");
  }
} catch(Exception $e) {
  $err = $e->getCode();
  $errmsg = $e->getMessage();
  error_log("NotARobot.php ERROR: ip=$S->ip, agent=$S->agent, page=$S->self, err=$err, errmsg=$errmsg, line=". __LINE__);
  exit();
}

// Remove TRACKER_BOT from tracker.

$S->sql("update $S->masterdb.tracker set isjavascript=isjavascript &~". TRACKER_BOT . " where id=$S->LAST_ID");

$bot = $S->isBot($S->agent);

if($bot === true) {
  $botAsBits = $S->botAsBits;
  $msg =<<<EOF
<p>Your User Agent String is=<b>$S->agent</b> and my system still thinks you are a ROBOT.</p>
<p>If you are really not a ROBOT please send an email to <b>bartonphillips@gmail.com</b> and we will try to <i>White List</i> your IP Address.</p>
EOF;
} else {
  $msg =<<<EOF
<p>You are not a ROBOT right? We removed your IP Address from our <i>Black List</i>.</p>
<a href="/">Return to My Home Page</a>
EOF;
}

[$top, $bottom] = $S->getPageTopBottom();

echo <<<EOF
$top
<hr>
$msg
<hr>
$bottom
EOF;
