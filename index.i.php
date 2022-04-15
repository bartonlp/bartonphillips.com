<?php
// index.i.php
// This is the main php include file. It is included in index.php
// BLP 2022-01-16 -- Reworked several areas.
/*
CREATE TABLE `members` (
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3

  The myip table is in $S->masterdb (which should be 'bartonlp') database
  'myIp' will be all of the computers that I have used in the last THREE days.
  A cron job removes entries that are older than three days and not my HOME ip address.
  
CREATE TABLE `myip` (
  `myIp` varchar(40) NOT NULL DEFAULT '',
  `count` int DEFAULT NULL,
  `createtime` datetime DEFAULT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`myIp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 
*/

$BLP = $_GET['blp']; // Get the secret value if supplied.
date_default_timezone_set("America/New_York");
$date = date("l F j, Y H:i:s T");

// Check if any of my sites have items that need to be added to the git repository
// dogit() is called below to set the $GIT array. $GIT is used by adminsites.php
// (bartonphillipsnet) to indicate 1) items to commit, 2) items that need to be pushed

function dogit() {
  array($any);
  
  foreach(['/vendor/bartonlp/site-class', '/bartonlp', '/bartonphillips.com', 
           '/bartonphillipsnet', '/allnaturalcleaningcompany', '/tysonweb', '/newbernzig.com'] as $site) {

    chdir("/var/www/$site");
    exec("git status", $out); // put results into $out
    $out = implode("\n", $out);

    // If the phrase below is found then there is nothing to commit.
    
    if(preg_match('/nothing to commit, working tree clean/s', $out) === 0) {
      // Needs to be commited
      
      $any[0] = ' *';
    }

    // If the phrase below is found then we need to 'push' to github.
    
    if(preg_match("~'origin/master' by (\d+) commit~s", $out, $m) === 1) {
      // We need a push
      
      $any[1] = ' !';
    }
  }
  return $any;
}

// if this is a bot don't bother with getting a location. And it will not have a SiteId.

if($S->isBot) {
  $locstr = <<<EOF
<ul class="user-info">
  <li>IP Address: <i class='green'>$S->ip</i></li>
</ul>
EOF;
  return; // just return with what we have so far.
}

// Not a bot. Get ipinfo.io information
  
if($ref = $_SERVER['HTTP_REFERER']) {
  if(preg_match("~(.*?)\?~", $ref, $m)) $ref = $m[1];
  $ref =<<<EOF
<li>You came to this site from: <i class='green'>$ref</i></li>
EOF;
}
  
// Use ipinfo.io to get the country for the ip

$cmd = "http://ipinfo.io/$S->ip";
$ch = curl_init($cmd);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$loc = json_decode(curl_exec($ch));

$clientname = gethostbyaddr($S->ip);

$locstr = <<<EOF
<ul class="user-info">
  $ref
  <li>User Agent String is:<br>
    <i class='green'>$S->agent</i></li>
  <li>IP Address: <i class='green'>$S->ip</i></li>
  <li>Clientname: <i class='green'>$clientname</i></li>
  <li>Location: <i class='green'>$loc->city, $loc->region $loc->postal</i></li>
  <li>GPS Loc: <i class='green'>$loc->loc</i></li>
  <li>ISP: <i class='green'>$loc->org</i></li>
  <li id="geo">Your Location: <i class='green'></i></li>
  <li id="finger">Your fingerprint: <i class='green'></i></li>
</ul>
<span id="TrackerCount"></span>
EOF;

// Do we have a cookie? If not offer to register
// The 'SiteId' cookie is the ip and email address separated by a colen (:). 
// If we have a cookie and it is me then set $adminStuff
// The $cookieIp is the last ip address for this browser/CPU from $_COOKIE

if(!($ipEmail = $_COOKIE['SiteId'])) { // if no cookie
  // check logagent table to see how may times this ip has been here.
  
  $S->query("select count, date(created) from $S->masterdb.logagent ".
            "where ip='$S->ip' and agent='$S->agent' and site='$S->siteName'");

  [$hereCount, $created] = $S->fetchrow('num');

  if($hereCount > 1) {
    $hereMsg =<<<EOF
<div class="hereMsg">You have been to our site $hereCount times since $created<br>
Why not <a href="register.php">register</a>
</div>
EOF;
  }
} 

// Is this $S->ip in the $S->myIp (from the myip table).

if($S->isMe()) {
  // This ip is one of the $S->myIp entries.
  
  [$cookieIp, $cookieEmail] = explode(':', $ipEmail); // The cookie is 'IP:Email'

  // Is the email address in the cookie my email address?
  
  if($cookieEmail == "bartonphillips@gmail.com") {
    $GIT = dogit(); // This is an array, $GIT[0] could be an '*' while $GIT[1] could be an '!'. It is used by adminsites.php

    // Get the admin sites.
    
    $adminStuff = require("adminsites.php");

    // Get the ip address and agent that is in the members table and check to see if it is still
    // the same as before.

    $sql = "select ip, agent from members where email='bartonphillips@gmail.com'";
    $S->query($sql);
    [$memberIp, $memberAgent] = $S->fetchrow('num');

    // If memberIp is not our current ip  or $memberAgent is not the current agent then update the members table.

    if($memberIp !== $S->ip || $memberAgent !== $S->agent) {
      $sql = "insert into members (name, email, ip, agent, created, lasttime) ". // The insert should fail with duplicate
             "values('Barton Phillips', 'bartonphillips@gmail.com', '$S->ip', '$S->agent', now(), now()) ".
             "on duplicate key update ip='$S->ip', agent='$S->agent', lasttime=now()"; // Update the ip, agent and lasttime

      $S->query($sql);
    }

    // Check my cookie IP against the current ip 

    if($cookieIp !== $S->ip) {
      // Update my cookie with the current ip address

      if($S->setSiteCookie('SiteId', "$S->ip:$cookieEmail", date('U') + 31536000, '/') === false) {
        echo "Can't set cookie in index.i.php<br>";
        throw(new Exception("Can't set cookie index.i.php " . __LINE__));
      }
    }
  }

  // It is still Me because $S->ip was in $S->myIp ($S->isMe()) even if
  // $cookieEmail is not 'bartonphillips@gmail.com'.
  // The 'SiteId' cookie ($ipEmail) might even be null.
  
  $sql = "select name from members where email='$cookieEmail'";

  if($S->query($sql)) {
    // Found the record.

    [$memberName] = $S->fetchrow('num');

    $hereMsg =<<<EOF
<div class="hereMsg">Welcome $memberName</div>
EOF;
  }

  // The above should have found $adminStuff if we have a cookie, BUT we may not have one!
  // We should still check for the magic $BLP to see if is is set to the secret value.

  if($BLP == "8653" && !$adminStuff) {
    // if we didn't load adminsites above then who is this? I guess I will still let them see the
    // adminsite. I will review logs and decide.

    error_log("bartonphillips.com/index.i.php. The secret code was given as a query: $S->ip, $S->agent");
    $adminStuff = require("adminsites.php");
  }
} else {
  // This $S->ip was not in $S->isMe() which means $S->ip is not in the myip table.

  if($ipEmail) { // BLP 2022-01-15 -- only if something was set.
    error_log("$S->siteName/index.i.php $S->ip: '\$S->myIp()' returned false. Removing cookie 'SiteId' for $ipEmail. ".__LINE__);
  
    if($S->setSiteCookie('SiteId', '', -1) === false) {
      error_log("index.i.php $S->ip: remove cookie Error. " . __LINE__);
    }
  }

  // Check once again to see if my secret is set
  
  if($BLP == "8653" && !$adminStuff) {
    // if we didn't load adminsites above then who is this? I guess I will still let them see the
    // adminsite. I will review logs and decide.

    error_log("bartonphillips.com/index.i.php. The secret code was given as a query: $S->ip, $S->agent");
    $adminStuff = require("adminsites.php");
  }
}
